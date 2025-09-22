<?php

/**
 * Class Optml_Litespeed
 *
 * Handles conflict with LiteSpeed Cache lazy loading feature.
 */
class Optml_Litespeed extends Optml_Abstract_Conflict {

	/**
	 * Optml_Litespeed_Cache constructor.
	 */
	public function __construct() {
		$this->severity = self::SEVERITY_MEDIUM;
		parent::__construct();
	}

	/**
	 * Set the message property
	 *
	 * @since   2.0.6
	 * @access  public
	 */
	public function define_message() {
		$this->message = sprintf(
			/* translators: 1 is the settings path link */
			__( 'LiteSpeed Cache has <strong>Lazy Loading</strong> enabled. Optimole already provides its own lazy loading mechanism, which may conflict with LiteSpeed Cache\'s. To continue using Optimole\'s lazy loading feature, please disable lazy loading in %1$s.', 'optimole-wp' ),
			'<a href="' . admin_url( 'admin.php?page=litespeed-page_optm' ) . '">LiteSpeed Cache → Page Optimization → Media Settings</a>'
		);
	}

	/**
	 * Determine if conflict is applicable.
	 *
	 * @return bool
	 * @since   2.0.6
	 * @access  public
	 */
	public function is_conflict_valid() {
		if ( ! is_plugin_active( 'litespeed-cache/litespeed-cache.php' ) ) {
			return false;
		}

		if ( ! Optml_Main::instance()->admin->settings->use_lazyload() ) {
			return false;
		}

		if ( ! class_exists( 'LiteSpeed\Base', false ) || ! class_exists( 'LiteSpeed\Conf', false ) ) {
			return false;
		}

		try {
			$litespeed_lazy_enabled = false;

			if ( class_exists( 'LiteSpeed\Conf', false ) && class_exists( 'LiteSpeed\Base', false ) ) {
				$conf_instance = \LiteSpeed\Conf::cls();
				$lazy_setting = $conf_instance->conf( \LiteSpeed\Base::O_MEDIA_LAZY );

				if ( $lazy_setting ) {
					if ( class_exists( 'LiteSpeed\Metabox', false ) ) {
						$metabox = \LiteSpeed\Metabox::cls();
						$no_lazy_setting = $metabox->setting( 'litespeed_no_image_lazy' );

						$litespeed_lazy_enabled = ! $no_lazy_setting;
					} else {
						$litespeed_lazy_enabled = true;
					}
				}
			}

			if ( $litespeed_lazy_enabled ) {
				return true;
			}
		} catch ( Exception $e ) {
			error_log( 'Optml_Litespeed: Exception while checking LiteSpeed settings: ' . $e->getMessage() );
		}

		return false;
	}
}
