<?php
/**
 * CLI commands responsible for the Optimole media.
 */

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

/**
 * Class Optml_Cli_Media
 */
class Optml_Cli_Media extends WP_CLI_Command {
	/**
	 * Move all existing images to our servers.
	 */
	public function move_to_optimole() {
		$this->update_images_template( 'offload' );
	}
	/**
	 * Move all existing images from our servers to your media library.
	 */
	public function rollback_images() {
		$this->update_images_template( 'rollback' );
	}

	/**
	 *   Template for bulk image processing to avoid duplicate code.
	 *
	 * @param string $action The action to perform rollback/offload.
	 * @return mixed WP_CLI::error If it fails.
	 */
	private function update_images_template( $action ) {
		$strings = [
			'offload' => [
				'info' => __( 'Moving all images to Optimole Cloud', 'optimole-wp' ),
				'success' => __( 'All images have been uploaded to Optimole Cloud', 'optimole-wp' ),
			],
			'rollback' => [
				'info' => __( 'Moving all images back to your media library', 'optimole-wp' ),
				'success' => __( 'All images have been uploaded to your media library', 'optimole-wp' ),
			],
			'limit_exceeded' => __( 'You have reached the maximum offloading limit of images. To increase the offload limit and for more information, contact our support.', 'optimole-wp' ),
		];
		$settings = new Optml_Settings();
		if ( $settings->get( 'offload_media' ) === 'disabled' ) {
			return \WP_CLI::error( __( 'You need to have the offload_media option enabled in order to use this command', 'optimole-wp' ) );
		}

		Optml_Media_Offload::clear_offload_errors_meta();
		WP_CLI::line( $strings[ $action ]['info'] );
		$number_of_images_for = 'offload_images';
		if ( $action === 'rollback' ) {
			$number_of_images_for = 'rollback_images';
		}
		$number_of_images = Optml_Media_Offload::number_of_images_and_pages( $number_of_images_for );
		Optml_Media_Offload::record_process_meta( $number_of_images );
		$batch = 5;
		if ( $number_of_images === 0 ) {
			WP_CLI::success( $strings[ $action ]['success'] );

			return;
		}
		$possible_batch = ceil( $number_of_images / 10 );
		if ( $possible_batch < $batch ) {
			$batch  = $possible_batch;
		}
		$total_progress = ceil( $number_of_images / $batch );
		$progress = \WP_CLI\Utils\make_progress_bar( __( 'Progress bar', 'optimole-wp' ), (int) $total_progress );
		$tick = 0;
		$page = 1;
		while ( $tick < $total_progress ) {
			$posts_to_update = $action === 'offload' ? [] : Optml_Media_Offload::instance()->update_content( $page, $number_of_images_for, $batch );

			// Kept for backward compatibility with old offloading mechanism where pages were modified.
			if ( $action === 'rollback' && isset( $posts_to_update['page'] ) && $posts_to_update['page'] > $page ) {
				$page = $posts_to_update['page'];
				if ( isset( $posts_to_update['imagesToUpdate'] ) && count( $posts_to_update['imagesToUpdate'] ) ) {
					foreach ( $posts_to_update['imagesToUpdate'] as $post_id => $images ) {
						Optml_Media_Offload::instance()->rollback_and_update_images( $images );
						Optml_Media_Offload::instance()->update_page( $post_id );
					}
				}
			} else {
				$action === 'rollback' ? Optml_Media_Offload::instance()->rollback_images( $batch ) : Optml_Media_Offload::instance()->upload_images( $batch );
			}

			if ( Optml_Media_Offload::instance()->settings->is_offload_limit_reached() ) {
				WP_CLI::error( $strings['limit_exceeded'], true );
			}

			$progress->tick();
			++$tick;
		}
		$progress->finish();
		WP_CLI::line( $strings[ $action ]['success'] );
	}
}
