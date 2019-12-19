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

		return is_plugin_active( 'sassy-social-share/sassy-social-share.php' );
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
								if ( images[i].hasAttribute( "data-opt-src" ) ) {
										images[i].src = images[i].dataset.optSrc ;			
								}
								//the below variant is also good I\'ll look into the speed of it vs above
								// if ( "optSrc" in images[i].dataset ) {			
								// 	images[i].src = images[i].dataset.optSrc ;
								// }
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
