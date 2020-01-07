<?php

/**
 * Class Optml_sassy_social_share.
 *
 * @reason Sassy social share picks eco images to share
 */
class Optml_sassy_social_share extends Optml_compatibility {
	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		if ( ! is_plugin_active( 'sassy-social-share/sassy-social-share.php' ) ) {
			return false;
		}
		$ss_options = get_option( 'heateor_sss' );
		$ss_bars = ['vertical_re_providers', 'horizontal_re_providers'];
		if ( ! is_array( $ss_options ) ) {
			return false;
		}
		foreach ( $ss_bars as $key => $bar ) {
			if ( ! isset( $ss_options[ $bar ] ) ) {
				continue;
			}
			foreach ( $ss_options[ $bar ] as $index => $value ) {
				if ( isset( $value ) && is_string( $value ) ) {
					if ( strpos( $value, 'pinterest' ) !== false ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_action(
			'wp_enqueue_scripts',
			function () {
				wp_register_script( 'optml-typekit', false );
				wp_enqueue_script( 'optml-typekit' );
				$script = '
			(function(w, d){
					w.addEventListener("load", function(){
						let p=d.querySelectorAll( ".heateorSssSharing.heateorSssPinterestBackground" );
						p.forEach ( function (button,index)  {
							button.addEventListener( "mouseover", () => {
							let images = d.getElementsByTagName( "img" );
							for ( let i = 0; i < images.length; i++ ) {
								 if ( "optSrc" in images[i].dataset ) {
								 	images[i].src = images[i].dataset.optSrc ;
								 }
								}
							},{once:true} );
						});
						
					});
			}(window, document));
		';
				wp_add_inline_script( 'optml-typekit', $script );
			}
		);
	}
}
