<?php

/**
 * Class Pinterest.
 *
 * @reason Pinterest plugins picks eco images to share
 */
class Optml_pinterest extends Optml_compatibility {
	/**
	 * String with all css selectors to target
	 *
	 * @var string
	 */
	private $selectors;
	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {

		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$load = false;
		$selectorsArray = array();
		if ( $this->isShareaholic() ) {
			$selectorsArray[] = 'li.shareaholic-share-button[data-service=\"pinterest\"]';
			$load = true;
		}
		if ( $this->isSassySocialShare() ) {
			$selectorsArray[] = '.heateorSssSharing.heateorSssPinterestBackground';
			$load = true;
		}
		$this->selectors = implode( ', ', array_filter( $selectorsArray ) );
		return $load;
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_action(
			'wp_enqueue_scripts',
			function () {
				wp_register_script( 'optml-pinterest', false );
				wp_enqueue_script( 'optml-pinterest' );
				$script = sprintf(
					'
			(function(w, d){
			w.addEventListener("load", function() {
					const addCustomEventListener = function (selector, event, handler) {
		                let rootElement = document.querySelector(\'body\');
		                  rootElement.addEventListener(event, function (evt) {
			                  var targetElement = evt.target;
			                  while (targetElement != null) {
				                  if (targetElement.matches(selector)) {
					                  handler(evt);
					                  return;
				                  }
			                      targetElement = targetElement.parentElement;
			                  }
		                  },
		                  true
		                   );
                   };
					addCustomEventListener(\'%s\',\'mouseover\',function(){
						let images = d.getElementsByTagName( "img" );
						for ( let i = 0; i < images.length; i++ ) {
							if ( "optSrc" in images[i].dataset ) {
								images[i].src = images[i].dataset.optSrc ;
							}
						}
					});
			});		
			}(window, document));
		',
					$this->selectors
				);
				wp_add_inline_script( 'optml-pinterest', $script );
			}
		);
	}
	/**
	 Check if plugin is active.

	 @return bool
	 */
	private function isShareaholic() {

		if ( ! is_plugin_active( 'shareaholic/shareaholic.php' ) ) {
			return false;
		}
		return true;

	}
	/**
	 * Check if plugin is active.
	 *
	 * @return bool
	 */
	private function isSassySocialShare() {

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
}
