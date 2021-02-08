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
	 * @return WP_CLI::error If it fails.
	 */
	private function update_images_template( $action ) {
		$strings = [
			'offload' => [
				'info' => __( 'Moving all images to our servers', 'optimole-wp' ),
				'success' => __( 'All images have been uploaded to our servers', 'optimole-wp' ),
			],
			'rollback' => [
				'info' => __( 'Moving all images back to your media library', 'optimole-wp' ),
				'success' => __( 'All images have been uploaded to your media library', 'optimole-wp' ),
			],
		];
		$settings = new Optml_Settings();
		if ( $settings->get( 'offload_media' ) === 'disabled' ) {
			return \WP_CLI::error( __( 'You need to have the offload_media option enabled in order to use this command', 'optimole-wp' ) );
		}
		WP_CLI::line( $strings[ $action ]['info'] );
		$number_of_images_for = 'offload_images';
		if ( $action === 'rollback' ) {
			$number_of_images_for = 'rollback_images';
		}
		$number_of_images = Optml_Media_Offload::number_of_library_images( $number_of_images_for );
		$batch = 5;
		$possible_batch = ceil( $number_of_images / 10 );
		if ( $possible_batch < $batch ) {
			$batch  = $possible_batch;
		}
		$total_progress = ceil( $number_of_images / $batch );
		$progress = \WP_CLI\Utils\make_progress_bar( __( 'Progress bar', 'optimole-wp' ), $total_progress );
		$tick = 0;
		while ( $tick < $total_progress ) {
			$action === 'rollback' ? Optml_Media_Offload::rollback_images( $batch ) : Optml_Media_Offload::upload_images( $batch );
			$progress->tick();
			$tick++;
		}
		$progress->finish();
		WP_CLI::line( $strings[ $action ]['success'] );
	}
}

