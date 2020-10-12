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
		$settings = new Optml_Settings();
		if ( $settings->get( 'offload_media' ) === 'disabled' ) {
			return \WP_CLI::error( __( 'You need to have the offload_media option enabled in order to use this command', 'optimole-wp' ) );
		}
		WP_CLI::line( __( 'Moving all images to our servers', 'optimole-wp' ) );
		$number_of_images = Optml_Media_Offload::number_of_library_images();
		$batch = 100;
		$possible_batch = ceil( $number_of_images / 10 );
		if ( $possible_batch < 100 ) {
			$batch  = $possible_batch;
		}
		$total_progress = ceil( $number_of_images / $batch );
		$progress = \WP_CLI\Utils\make_progress_bar( __( 'Progress bar', 'optimole-wp' ), $total_progress );
		$tick = 0;
		while ( $tick < $total_progress ) {
			Optml_Media_Offload::upload_images( $batch );
			$progress->tick();
			$tick++;
		}
		$progress->finish();
		WP_CLI::line( __( 'All images have been uploaded to our servers', 'optimole-wp' ) );
	}
}

