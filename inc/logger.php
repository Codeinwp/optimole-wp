<?php
/**
 * Class Optml_Logger
 * Handles the logging operations.
 */
class Optml_Logger {

	const LOG_TYPE_OFFLOAD = 'offload';
	const LOG_TYPE_ROLLBACK = 'rollback';

	const LOG_SEPARATOR = '==========';

	/**
	 * The option that holds the random part of this site's log file names.
	 */
	const FILE_TOKEN_OPTION = 'optml_log_file_token';

	/**
	 * Upload directory.
	 *
	 * @var string
	 */
	private $upload_dir;

	/**
	 * The log types, with the fixed file name each was stored under before file names carried a token.
	 *
	 * @var array<string, string>
	 */
	private $log_filenames = [
		self::LOG_TYPE_OFFLOAD  => 'offload.log',
		self::LOG_TYPE_ROLLBACK => 'rollback.log',
	];

	/**
	 * The random part of this site's log file names.
	 *
	 * @var string
	 */
	private $file_token;

	/**
	 * The single instance of the class.
	 *
	 * @var Optml_Logger
	 */
	private static $instance = null;

	/**
	 * Ensures only one instance of the class is loaded.
	 *
	 * @return Optml_Logger An instance of the class.
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Optml_Logger constructor.
	 * Initializes the upload directory.
	 *
	 * @return void
	 */
	private function __construct() {
		$upload_dir       = wp_upload_dir();
		$this->upload_dir = trailingslashit( $upload_dir['basedir'] ) . 'optimole-logs/';
		// Read with the directory, so both belong to the same site of a network for as long as the logger lives.
		$stored_token     = get_option( self::FILE_TOKEN_OPTION );
		$this->file_token = is_string( $stored_token ) && '' !== $stored_token ? $stored_token : $this->create_file_token();

		if ( ! file_exists( $this->upload_dir ) ) {
			if ( false === wp_mkdir_p( $this->upload_dir ) ) {
				return;
			}
		}

		add_action( 'wp_ajax_optml_fetch_logs', [ $this, 'fetch_logs_ajax_handler' ] );
		add_action( 'admin_init', [ $this, 'protect_log_files' ] );

		// The site just got its token: logs still stored under the earlier fixed names are moved on this request.
		if ( $this->file_token !== $stored_token ) {
			$this->protect_log_files();
		}
	}

	/**
	 * Create and store the random part of this site's log file names.
	 *
	 * @return string
	 */
	private function create_file_token() {
		$token = bin2hex( random_bytes( 16 ) );
		update_option( self::FILE_TOKEN_OPTION, $token );

		return $token;
	}
	/**
	 * Get the path of a log file.
	 *
	 * The file name carries a random token stored for this site, so the log cannot be requested by guessing its
	 * URL. A log written under the earlier fixed name is moved to the new one.
	 *
	 * @param string $type Type of log (offload/rollback).
	 *
	 * @return string Absolute path of the log file.
	 */
	private function get_file_path( $type ) {
		$legacy_path = $this->upload_dir . $this->log_filenames[ $type ];
		$file_path   = $this->upload_dir . $type . '-' . $this->file_token . '.log';

		// A log that cannot be moved is deleted, so nothing stays under the old name.
		if ( file_exists( $legacy_path ) && ( file_exists( $file_path ) || ! @rename( $legacy_path, $file_path ) ) ) {
			wp_delete_file( $legacy_path );
		}

		return $file_path;
	}
	/**
	 * Move the logs written under the earlier fixed names, and add the files that keep a web server from listing
	 * or serving the log directory. A file that already exists is kept as it is.
	 *
	 * @return void
	 */
	public function protect_log_files() {
		if ( ! $this->has_permission() ) {
			return;
		}

		foreach ( array_keys( $this->log_filenames ) as $type ) {
			$this->get_file_path( $type );
		}

		$files = [
			'index.html' => '',
			'.htaccess'  => "<IfModule mod_authz_core.c>\n\tRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\n\tDeny from all\n</IfModule>\n",
		];
		foreach ( $files as $name => $content ) {
			if ( ! file_exists( $this->upload_dir . $name ) ) {
				file_put_contents( $this->upload_dir . $name, $content );
			}
		}
	}
	/**
	 * Adds a log entry.
	 *
	 * @param string $type Type of log (offload/rollback).
	 * @param string $message Log message.
	 * @return bool Returns true on success, false on failure.
	 */
	public function add_log( $type, $message ) {
		if ( ! array_key_exists( $type, $this->log_filenames ) ) {
			return false;
		}

		$this->send_log( $type, $message );

		if ( ! $this->has_permission() ) {
			return false;
		}

		$file_path = $this->get_file_path( $type );
		$handle    = @fopen( $file_path, 'a' );

		if ( $handle === false ) {
			return false;
		}

		fwrite( $handle, '[' . gmdate( 'M d H:i:s' ) . '] ' . $message . "\n" );
		fclose( $handle );
		return true;
	}

	/**
	 * Retrieves the log.
	 *
	 * @param string $type Type of log (offload/rollback).
	 * @param int    $lines Number of lines to return from the end of the log file.
	 * @param string $separator Separator line to start retrieving log entries from.
	 *
	 * @return false|string Returns log data or false on failure.
	 */
	public function get_log( $type, $lines = 500, $separator = '' ) {
		if ( ! array_key_exists( $type, $this->log_filenames ) ) {
			return false;
		}

		$file_path = $this->get_file_path( $type );

		if ( file_exists( $file_path ) ) {
			$data = $this->tailCustom( $file_path, $lines, true, $separator );
			return nl2br( esc_textarea( $data ) );
		}

		return false;
	}

	/**
	 * Checks if the log file exists.
	 *
	 * @param string $type Type of log (offload/rollback).
	 * @return bool Returns true if log file exists, false otherwise.
	 */
	public function has_logs( $type ) {
		if ( ! array_key_exists( $type, $this->log_filenames ) ) {
			return false;
		}

		$file_path = $this->get_file_path( $type );
		return file_exists( $file_path );
	}

	/**
	 * Checks for permission to write to log file.
	 *
	 * @return bool Returns true if permission is granted, false otherwise.
	 */
	public function has_permission() {
		return is_writable( $this->upload_dir );
	}

	/**
	 * Reads the last n lines from a file, or from a specific point defined by a separator.
	 *
	 * @param string $filepath Path to the file.
	 * @param int    $lines Number of lines to read from the end of the file.
	 * @param bool   $adaptive Use adaptive buffers.
	 * @param string $separator Separator line to start retrieving log entries from.
	 * @return false|string Returns last n lines from the file or false on failure.
	 */
	private function tailCustom( $filepath, $lines = 1, $adaptive = true, $separator = '' ) {
		$f = @fopen( $filepath, 'rb' );

		if ( $f === false ) {
			return false;
		}

		if ( ! $adaptive ) {
			$buffer = 4096;
		} else {
			$buffer = min( 64, filesize( $filepath ) );
		}

		fseek( $f, -1, SEEK_END );
		$output = '';
		$read = '';
		$separator_found = false;

		while ( $buffer > 0 && ftell( $f ) > 0 ) {
			$seek = min( ftell( $f ), $buffer );
			fseek( $f, -$seek, SEEK_CUR );
			$output = ( $read = fread( $f, $seek ) ) . $output;
			fseek( $f, -mb_strlen( $read, '8bit' ), SEEK_CUR );

			if ( ! empty( $separator ) && strpos( $output, $separator ) !== false ) {
				$separator_found = true;
				break;
			}

			$buffer = min( $buffer * 2, 4096 );
		}
		$pos = -1;
		if ( ! empty( $separator ) && $separator_found ) {
			$pos = strrpos( $output, $separator );
			if ( $pos !== false ) {
				$output = substr( $output, $pos + strlen( $separator ) );
				$pos = -1; // we reset the position to start from the beginning of the chunk when slicing.
			}
		}

		// Extract the desired number of lines
		$output_lines = explode( "\n", $output );
		if ( empty( $separator ) || ! $separator_found ) {
			$output = implode( "\n", array_slice( $output_lines, -$lines ) );  // fetch last n lines
		} else {
			// Calculate the start position for slicing
			$start_pos = max( $pos + 1, count( $output_lines ) - $lines );

			$output = implode( "\n", array_slice( $output_lines, $start_pos, $lines ) );  // fetch from separator
		}

		fclose( $f );
		return trim( $output );
	}

	/**
	 * Fetches the logs via AJAX.
	 *
	 * @return void
	 */
	public function fetch_logs_ajax_handler() {
		// Check for nonce security
		$nonce = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'wp_rest' ) || ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Security check failed', 'optimole-wp' ) );
		}

		// Check for necessary parameters
		if ( ! isset( $_REQUEST['type'] ) || ! in_array( $_REQUEST['type'], array_keys( $this->log_filenames ), true ) ) {
			wp_die( __( 'Invalid log type', 'optimole-wp' ) );
		}

		$lines = 10000;

		if ( isset( $_REQUEST['lines'] ) ) {
			$lines = intval( $_REQUEST['lines'] );
		}

		// Get log type
		$type = $_REQUEST['type'];

		// Fetch the logs
		$logs = $this->get_log( $type, $lines, self::LOG_SEPARATOR );

		// Return the logs
		echo $logs;

		wp_die();
	}

	/**
	 * Send logs to Axiom.
	 *
	 * @param string $type Type of log (offload/rollback).
	 * @param string $message Log message.

	 * @return void
	 */
	public function send_log( $type, $message ) {
		$request = new Optml_Api();
		$request->send_log( $type, $message );
	}
}
