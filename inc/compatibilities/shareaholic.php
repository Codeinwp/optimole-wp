<?php

/**
 * Class Optml_sassy_social_share.
 *
 * @reason Shareaholic picks eco images to share
 */
class Optml_shareaholic extends Optml_compatibility {
	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		if ( ! is_plugin_active( 'shareaholic/shareaholic.php' ) ) {
			return false;
		}
		return true;
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
						let p=d.querySelectorAll( "li.shareaholic-share-button[data-service=\'pinterest\']" );
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
