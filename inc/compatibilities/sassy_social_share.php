<?php

/**
 * Class Optml_cache_enabler.
 *
 * @reason Cache_enabler stores the content of the page before Optimole starts replacing url's
 */
class Optml_sassy_social_share extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		if ( is_plugin_active( 'sassy-social-share/sassy-social-share.php' ) ) {
			$ss_options = get_option( 'heateor_sss' );
			$ss_bars = ['vertical_re_providers', 'horizontal_re_providers'];
			foreach ( $ss_bars as $key => $bar ) {
				foreach ( $ss_options[ $bar ] as $index => $value ) {
					if ( isset( $value ) && is_string( $value ) ) {
						if ( strpos( $value, 'pinterest' ) !== false ) {
							return true;
						}
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
			'wp_head',
			function () {
				$output = '
		<script type="application/javascript">
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
		</script>';
				echo $output;
			}
		);
	}
}
