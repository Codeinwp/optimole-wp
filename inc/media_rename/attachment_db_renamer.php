<?php
/**
 * Attachment db renamer class.
 */

/**
 * URL Replacer for WordPress
 *
 * A standalone class to replace URLs in WordPress database,
 * including handling image size variations and scaled images.
 *
 * @since      4.0.0
 */
class Optml_Attachment_Db_Renamer {
	/**
	 * Tables to skip during replacement
	 *
	 * @var array
	 */
	private $skip_tables = [ 'users', 'terms', 'term_relationships', 'term_taxonomy' ];

	/**
	 * Columns to skip during replacement
	 *
	 * @var array
	 */
	private $skip_columns = [ 'user_pass' ];

	/**
	 * Handle image size variations
	 *
	 * @var bool
	 */
	private $handle_image_sizes = false;

	/**
	 * Constructor
	 */
	public function __construct( $skip_sizes = false ) {
		$this->handle_image_sizes = ! $skip_sizes;
	}

	/**
	 * Replace URLs in the WordPress database
	 *
	 * @param string $old_url The base URL to search for (e.g., http://domain.com/wp-content/uploads/2025/03/image.jpg).
	 * @param string $new_url The base URL to replace with (e.g., http://domain.com/wp-content/uploads/2025/03/new-name.jpg).
	 *
	 * @return int Number of replacements made
	 */
	public function replace( $old_url, $new_url ) {
		if ( $old_url === $new_url ) {
			return 0;
		}

		if ( empty( $old_url ) || empty( $new_url ) ) {
			return 0;
		}

		if ( ! is_string( $old_url ) || ! is_string( $new_url ) ) {
			return 0;
		}

		$tables = $this->get_tables();
		$total_replacements = 0;

		foreach ( $tables as $table ) {
			if ( in_array( $table, $this->skip_tables, true ) ) {
				continue;
			}

			list($primary_keys, $columns) = $this->get_columns( $table );

			// Skip tables with no primary keys
			if ( empty( $primary_keys ) ) {
				continue;
			}

			foreach ( $columns as $column ) {
				if ( in_array( $column, $this->skip_columns, true ) ) {
					continue;
				}

				$replacements = $this->process_column( $table, $column, $primary_keys, $old_url, $new_url );
				$total_replacements += $replacements;
			}
		}

		return $total_replacements;
	}

	/**
	 * Get WordPress tables
	 *
	 * @return array Table names
	 */
	private function get_tables() {
		global $wpdb;

		return array_values( $wpdb->tables() );
	}

	/**
	 * Get columns for a table
	 *
	 * @param string $table Table name.
	 *
	 * @return array Array containing primary keys and text columns
	 */
	private function get_columns( $table ) {
		global $wpdb;

		$primary_keys = [];
		$text_columns = [];

		// Get table information
		$results = $wpdb->get_results( $wpdb->prepare( 'DESCRIBE %i', $table ) );

		if ( ! empty( $results ) ) {
			// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			foreach ( $results as $col ) {
				if ( 'PRI' === $col->Key ) {
					$primary_keys[] = $col->Field;
				}
				if ( $this->is_text_col( $col->Type ) ) {
					$text_columns[] = $col->Field;
				}
			}
			// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		}

		return [ $primary_keys, $text_columns ];
	}

	/**
	 * Check if column is text type
	 *
	 * @param string $type Column type.
	 *
	 * @return bool True if text column
	 */
	private function is_text_col( $type ) {
		foreach ( [ 'text', 'varchar', 'longtext', 'mediumtext', 'char' ] as $token ) {
			if ( false !== stripos( $type, $token ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Process a single column for replacements
	 *
	 * @param string $table Table name.
	 * @param string $column Column name.
	 * @param array  $primary_keys Primary keys.
	 * @param string $old_url Old URL.
	 * @param string $new_url New URL.
	 *
	 * @return int Number of replacements
	 */
	private function process_column( $table, $column, $primary_keys, $old_url, $new_url ) {
		global $wpdb;

		$count = 0;

		// Check for serialized data
		$has_serialized = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(%i) FROM %i WHERE %i REGEXP '^[aiO]:[1-9]' LIMIT 1",
				$column,
				$table,
				$column
			)
		);

		// Process with PHP if serialized data is found
		if ( $has_serialized ) {
			$count = $this->php_handle_column( $table, $column, $primary_keys, $old_url, $new_url );
		} else {
			// Use direct SQL replacement for non-serialized data
			$count = $this->sql_handle_column( $table, $column, $old_url, $new_url );
		}

		return $count;
	}

	/**
	 * Handle column using SQL replacement
	 *
	 * @param string $table Table name.
	 * @param string $column Column name.
	 * @param string $old_url Old URL.
	 * @param string $new_url New URL.
	 *
	 * @return int Number of replacements
	 */
	private function sql_handle_column( $table, $column, $old_url, $new_url ) {
		global $wpdb;
		$count = 0;

		// Get the filename components
		$old_path_parts = parse_url( $old_url );
		if ( ! isset( $old_path_parts['path'] ) ) {
			return 0;
		}

		$old_path = $old_path_parts['path'];
		$old_file_info = pathinfo( $old_path );

		$old_base = $old_file_info['filename'];
		$old_dir = dirname( $old_path );
		$old_domain = isset( $old_path_parts['host'] ) ? 'http' . ( isset( $old_path_parts['scheme'] ) && $old_path_parts['scheme'] === 'https' ? 's' : '' ) . '://' . $old_path_parts['host'] : '';

		// Build pattern to match any URL containing the base filename
		$base_url = $old_domain . $old_dir . '/' . $old_base;

		// Get rows with regular URLs
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM %i WHERE %i LIKE %s',
				$table,
				$column,
				'%' . $wpdb->esc_like( $base_url ) . '%'
			)
		);

		// Also create a pattern for JSON-escaped version
		$json_base_url = str_replace( '/', '\/', $base_url );

		// Get rows with JSON-escaped URLs
		$json_rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM %i WHERE %i LIKE %s',
				$table,
				$column,
				'%' . $wpdb->esc_like( $json_base_url ) . '%'
			)
		);

		// Merge results, avoiding duplicates
		$processed_ids = [];
		$all_rows = array_merge( $rows, $json_rows );

		if ( empty( $all_rows ) ) {
			return 0;
		}

		foreach ( $all_rows as $row ) {
			$id_field = $row->ID ?? $row->id ?? null;
			if ( ! $id_field ) {
				foreach ( $row as $field => $value ) {
					if ( stripos( $field, 'id' ) !== false ) {
						$id_field = $value;
						break;
					}
				}
			}

			if ( ! $id_field ) {
				continue;
			}

			// Skip if we've already processed this row
			if ( isset( $processed_ids[ $id_field ] ) ) {
				continue;
			}
			$processed_ids[ $id_field ] = true;

			$content = $row->$column;
			$new_content = $this->replace_image_urls( $content, $old_url, $new_url );

			if ( $content !== $new_content ) {
				$wpdb->update(
					$table,
					[ $column => $new_content ],
					[ 'ID' => $id_field ]
				);
				++$count;
			}
		}

		return $count;
	}

	/**
	 * Handle column using PHP for serialized data
	 *
	 * @param string $table Table name.
	 * @param string $column Column name.
	 * @param array  $primary_keys Primary keys.
	 * @param string $old_url Old URL.
	 * @param string $new_url New URL.
	 *
	 * @return int Number of replacements
	 */
	private function php_handle_column( $table, $column, $primary_keys, $old_url, $new_url ) {
		global $wpdb;

		$count = 0;
		$json_old_url = str_replace( '/', '\/', $old_url );

		// Build the query and allow processing with multiple primary keys.
		$query = 'SELECT ';
		foreach ( $primary_keys as $key => $value ) {
			$query .= $wpdb->prepare( '%i, ', $value );
		}
		$query .= '%i FROM %i WHERE %s LIKE %s LIMIT 100';

		// Get the rows that need updating - first for regular URLs
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				$query, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$column,
				$table,
				$column,
				'%' . $wpdb->esc_like( $old_url ) . '%'
			)
		);

		// Also get rows with JSON-escaped URLs
		$json_rows = $wpdb->get_results(
			$wpdb->prepare(
				$query, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$column,
				$table,
				$column,
				'%' . $wpdb->esc_like( $json_old_url ) . '%'
			)
		);

		// Merge results, avoiding duplicates
		$processed_ids = [];
		$all_rows = array_merge( $rows, $json_rows );

		foreach ( $all_rows as $row ) {
			// Generate a unique identifier for this row based on primary keys
			$row_id = '';
			foreach ( $primary_keys as $key ) {
				$row_id .= $row->$key . '|';
			}

			// Skip if we've already processed this row
			if ( isset( $processed_ids[ $row_id ] ) ) {
				continue;
			}

			$processed_ids[ $row_id ] = true;
			$value = $row->$column;

			// Skip empty values
			if ( empty( $value ) ) {
				continue;
			}

			// Replace URLs in the value (handling serialized data)
			$new_value = $this->replace_urls_in_value( $value, $old_url, $new_url );

			// Skip if no change
			if ( $value === $new_value ) {
				continue;
			}

			// Build WHERE clause for this row
			$where_conditions = [];
			foreach ( $primary_keys as $key ) {
				$where_conditions[ $key ] = $row->$key;
			}

			// Update the row
			$updated = $wpdb->update(
				$table,
				[ $column => $new_value ],
				$where_conditions
			);

			if ( $updated ) {
				++$count;
			}
		}

		return $count;
	}

	/**
	 * Replace URLs in a value, handling serialized data
	 *
	 * @param string $value The value to process.
	 * @param string $old_url Old URL.
	 * @param string $new_url New URL.
	 *
	 * @return string The processed value
	 */
	private function replace_urls_in_value( $value, $old_url, $new_url ) {
		// Check if the value is serialized
		if ( $this->is_serialized( $value ) ) {
			$unserialized = @unserialize( $value );

			// If unserialize successful, process the data
			if ( $unserialized !== false ) {
				$replaced = $this->replace_in_data( $unserialized, $old_url, $new_url );
				return serialize( $replaced );
			}
		}

		// Handle image sizes for non-serialized content
		if ( $this->handle_image_sizes ) {
			return $this->replace_image_urls( $value, $old_url, $new_url );
		}

		// Simple string replacement for non-serialized data
		return str_replace( $old_url, $new_url, $value );
	}

	/**
	 * Replace image URLs including various WordPress size variations and scaled images
	 *
	 * @param string $content The content to process.
	 * @param string $old_url Old URL pattern.
	 * @param string $new_url New URL pattern.
	 *
	 * @return string The processed content
	 */
	private function replace_image_urls( $content, $old_url, $new_url ) {
		// Get the filename components
		$old_path_parts = parse_url( $old_url );
		$new_path_parts = parse_url( $new_url );

		if ( ! isset( $old_path_parts['path'] ) || ! isset( $new_path_parts['path'] ) ) {
			// If we can't parse the URLs, fallback to direct replacement
			return str_replace( $old_url, $new_url, $content );
		}

		// Extract file name info
		$old_path = $old_path_parts['path'];
		$new_path = $new_path_parts['path'];

		$old_file_info = pathinfo( $old_path );
		$new_file_info = pathinfo( $new_path );

		$old_base = $old_file_info['filename'];
		$new_base = $new_file_info['filename'];
		$old_ext = isset( $old_file_info['extension'] ) ? $old_file_info['extension'] : '';
		$new_ext = isset( $new_file_info['extension'] ) ? $new_file_info['extension'] : $old_ext;

		// Define domain parts
		$old_domain = isset( $old_path_parts['host'] ) ? 'http' . ( isset( $old_path_parts['scheme'] ) && $old_path_parts['scheme'] === 'https' ? 's' : '' ) . '://' . $old_path_parts['host'] : '';
		$new_domain = isset( $new_path_parts['host'] ) ? 'http' . ( isset( $new_path_parts['scheme'] ) && $new_path_parts['scheme'] === 'https' ? 's' : '' ) . '://' . $new_path_parts['host'] : '';

		// Replace original URLs
		$content = str_replace( $old_url, $new_url, $content );

		// Replace JSON-escaped URLs
		$json_old_url = str_replace( '/', '\/', $old_url );
		$json_new_url = str_replace( '/', '\/', $new_url );
		$content = str_replace( $json_old_url, $json_new_url, $content );

		// If we have a file with extension, handle variations
		if ( ! empty( $old_ext ) && $this->handle_image_sizes ) {
			$old_dir = dirname( $old_path );
			$new_dir = dirname( $new_path );

			// Replace WordPress image size variations (e.g., image-300x200.jpg)
			$size_pattern = '/' . preg_quote( $old_domain . $old_dir . '/' . $old_base, '/' ) . '-\d+x\d+\.' . preg_quote( $old_ext, '/' ) . '/';

			$content = preg_replace_callback(
				$size_pattern,
				function ( $matches ) use ( $old_base, $new_base, $old_domain, $new_domain, $old_dir, $new_dir, $old_ext, $new_ext ) {
					// Extract the size part (e.g., -300x200)
					$size_part = substr( $matches[0], strlen( $old_domain . $old_dir . '/' . $old_base ), -strlen( '.' . $old_ext ) );

					// Build the new URL with the same size
					return $new_domain . $new_dir . '/' . $new_base . $size_part . '.' . $new_ext;
				},
				$content
			);

			// Replace -scaled variations
			$scaled_pattern = '/' . preg_quote( $old_domain . $old_dir . '/' . $old_base, '/' ) . '-scaled\.' . preg_quote( $old_ext, '/' ) . '/';

			$content = preg_replace_callback(
				$scaled_pattern,
				function ( $matches ) use ( $new_base, $new_domain, $new_dir, $new_ext ) {
					return $new_domain . $new_dir . '/' . $new_base . '-scaled.' . $new_ext;
				},
				$content
			);

			// Replace JSON-escaped variations
			$json_size_pattern = '/' . preg_quote( str_replace( '/', '\/', $old_domain . $old_dir . '/' . $old_base ), '/' ) . '-\d+x\d+\.' . preg_quote( $old_ext, '/' ) . '/';

			$content = preg_replace_callback(
				$json_size_pattern,
				function ( $matches ) use ( $old_base, $new_base, $old_domain, $new_domain, $old_dir, $new_dir, $old_ext, $new_ext ) {
					// Extract the size part (e.g., -300x200)
					$size_part = substr( $matches[0], strlen( str_replace( '/', '\/', $old_domain . $old_dir . '/' . $old_base ) ), -strlen( '.' . $old_ext ) );

					// Build the new URL with the same size
					return str_replace( '/', '\/', $new_domain . $new_dir . '/' . $new_base . $size_part . '.' . $new_ext );
				},
				$content
			);

			// Replace JSON-escaped scaled variations
			$json_scaled_pattern = '/' . preg_quote( str_replace( '/', '\/', $old_domain . $old_dir . '/' . $old_base ), '/' ) . '-scaled\.' . preg_quote( $old_ext, '/' ) . '/';

			$content = preg_replace_callback(
				$json_scaled_pattern,
				function ( $matches ) use ( $new_base, $new_domain, $new_dir, $new_ext ) {
					return str_replace( '/', '\/', $new_domain . $new_dir . '/' . $new_base . '-scaled.' . $new_ext );
				},
				$content
			);
		}

		return $content;
	}

	/**
	 * Recursively replace URLs in data structure
	 *
	 * @param mixed  $data The data to process.
	 * @param string $old_url Old URL.
	 * @param string $new_url New URL.
	 *
	 * @return mixed The processed data
	 */
	private function replace_in_data( $data, $old_url, $new_url ) {
		if ( is_array( $data ) ) {
			// Process arrays recursively
			foreach ( $data as $key => $value ) {
				$data[ $key ] = $this->replace_in_data( $value, $old_url, $new_url );
			}
		} elseif ( is_object( $data ) ) {
			// Process objects recursively
			foreach ( $data as $key => $value ) {
				$data->$key = $this->replace_in_data( $value, $old_url, $new_url );
			}
		} elseif ( is_string( $data ) ) {
			// Replace URLs in strings
			if ( $this->handle_image_sizes ) {
				$data = $this->replace_image_urls( $data, $old_url, $new_url );
			} else {
				$data = str_replace( $old_url, $new_url, $data );
			}
		}

		return $data;
	}

	/**
	 * Check if a string is serialized
	 *
	 * @param string|mixed $data String to check.
	 *
	 * @return bool True if serialized
	 */
	private function is_serialized( $data ) {
		// If it isn't a string, it isn't serialized
		if ( ! is_string( $data ) ) {
			return false;
		}

		$data = trim( $data );
		if ( 'N;' === $data ) {
			return true;
		}

		if ( strlen( $data ) < 4 ) {
			return false;
		}

		if ( ':' !== $data[1] ) {
			return false;
		}

		$last_char = substr( $data, -1 );
		if ( ';' !== $last_char && '}' !== $last_char ) {
			return false;
		}

		$token = $data[0];
		switch ( $token ) {
			case 's':
				if ( '"' !== substr( $data, -2, 1 ) ) {
					return false;
				}
				// Fall through
			case 'a':
			case 'O':
			case 'i':
			case 'd':
				return (bool) preg_match( "/^{$token}:[0-9]+:/", $data );
			default:
				return false;
		}
	}
}

/**
 * Example usage:
 *
 * $replacer = new Optml_Attachment_Db_Renamer();
 *
 * // Replace all variations of the image
 * $count = $replacer->replace(
 *     'http://om-wp.test/wp-content/uploads/2025/03/image.jpg',
 *     'http://om-wp.test/wp-content/uploads/2025/03/new-name.jpg'
 * );
 *
 * echo "Replaced $count instances";
 */
