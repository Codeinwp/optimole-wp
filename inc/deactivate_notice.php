<?php


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Uninstall feedback module for ThemeIsle SDK.
 */
class Optml_Deactivate_Notice {

	private static $_instance = null;

	/**
	 * Default options for plugins.
	 *
	 * @var array $options_plugin The main options list for plugins.
	 */
	private $options_plugin = array(
		'Yes'            => array(
			'id'          => 1,
		),
		'No'            => array(
			'id'          => 0,
		),
	);


	/**
	 * Default heading for plugin.
	 *
	 * @var string $heading_plugin The heading of the modal
	 */
	private $heading_plugin = 'Delete all plugin data on deactivation?';
	/**
	 * Default submit button action text.
	 *
	 * @var string $button_submit The text of the deactivate button
	 */
	private $button_submit = 'Submit &amp; Deactivate';
	/**
	 * Default cancel button.
	 *
	 * @var string $button_cancel The text of the cancel button
	 */
	private $button_cancel = 'Skip &amp; Deactivate';

	/**
	 * Loads the additional resources
	 */
	function load_resources() {
		$screen = get_current_screen();

		if ( ! $screen || ! in_array( $screen->id, array( 'plugins' ) ) ) {
			return;
		}

		$this->add_feedback_popup_style();
		$this->add_plugin_feedback_popup_js();
		$this->render_plugin_feedback_popup();
	}
	/**
	 *  Constructor, hook all we need
	 */
	private function __construct() {
		add_action( 'admin_head', array( $this, 'load_resources' ) );
		add_action( 'wp_ajax_optml_uninstall_feedback', array( $this, 'post_deactivate' ) );

		return $this;
	}
	/**
	 * Create class instance, only if it has not been created
	 */
	public static function instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	/**
	 * Add feedback styles.
	 */
	private function add_feedback_popup_style() {
		?>
		<style>
			.optml-feedback {
				background: #fff;
				max-width: 400px;
				z-index: 10000;
				box-shadow: 0 0 15px -5px rgba(0, 0, 0, .5);
				transition: all .3s ease-out;
			}

			.optml-feedback .popup--header {
				position: relative;
				background-color: #23A1CE;
			}

			.optml-feedback .popup--header h5 {
				margin: 0;
				font-size: 16px;
				padding: 15px;
				color: #fff;
				font-weight: 600;
				text-align: center;
				letter-spacing: .3px;
			}

			.optml-feedback .popup--body {
				padding: 15px;
			}

			.optml-feedback .popup--form {
				margin: 0;
				font-size: 13px;
			}

			.optml-feedback .popup--form input[type="radio"] {
				margin: 0 10px 0 0;
			}

			.optml-feedback .popup--form input[type="radio"]:checked ~ textarea {
				display: block;
			}

			.optml-feedback .popup--form textarea {
				width: 100%;
				margin: 10px 0 0;
				display: none;
				max-height: 150px;
			}

			.optml-feedback li {
				display: flex;
				align-items: center;
				margin-bottom: 15px;
				flex-wrap: wrap;
			}

			.optml-feedback li label {
				max-width: 90%;
			}

			.optml-feedback li:last-child {
				margin-bottom: 0;
			}

			.optml-feedback .popup--footer {
				padding: 0 15px 15px;
			}

			.optml-feedback .actions {
				display: flex;
				flex-wrap: wrap;
			}

			.optml-feedback .info-disclosure-content p {
				margin: 0;
			}

			.optml-feedback .info-disclosure-content ul {
				margin: 10px 0;
				border-radius: 3px;
			}

			.optml-feedback .info-disclosure-content ul li {
				display: flex;
				align-items: center;
				justify-content: space-between;
				margin-bottom: 0;
				padding: 5px 0;
				border-bottom: 1px solid #ccc;
			}

			.optml-feedback .buttons {
				display: flex;
				width: 100%;
			}

			.optml-feedback .buttons input:last-child {
				margin-left: auto;
			}

			.optml-plugin-uninstall-feedback-popup .popup--header:before {
				content: "";
				display: block;
				position: absolute;
				border: 20px solid #23A1CE;
				left: -10px;
				top: 50%;
				border-top: 20px solid transparent;
				border-bottom: 20px solid transparent;
				border-left: 0;
				transform: translateY(-50%);
			}

			.optml-plugin-uninstall-feedback-popup {
				display: none;
				position: absolute;
				white-space: normal;
				width: 400px;
				left: 100%;
				top: -15px;
			}

			.optml-plugin-uninstall-feedback-popup.sending-feedback .popup--body i {
				animation: rotation 2s infinite linear;
				display: block;
				float: none;
				align-items: center;
				width: 100%;
				margin: 0 auto;
				height: 100%;
				background: transparent;
				padding: 0;
			}

			.optml-plugin-uninstall-feedback-popup.sending-feedback .popup--body i:before {
				padding: 0;
				background: transparent;
				box-shadow: none;
				color: #b4b9be
			}


			.optml-plugin-uninstall-feedback-popup.active {
				display: block;
			}

			tr[data-plugin^="optimole-wp/optimole-wp.php"] .deactivate {
				position: relative;
			}

			body.optml-feedback-open .optml-feedback-overlay {
				content: "";
				display: block;
				background-color: rgba(0, 0, 0, 0.5);
				top: 0;
				bottom: 0;
				right: 0;
				left: 0;
				z-index: 10000;
				position: fixed;
			}

			@media (max-width: 768px) {
				.optml-plugin-uninstall-feedback-popup {
					position: fixed;
					max-width: 100%;
					margin: 0 auto;
					left: 50%;
					top: 50px;
					transform: translateX(-50%);
				}

				.optml-plugin-uninstall-feedback-popup .popup--header:before {
					display: none;
				}
			}
		</style>
		<?php
	}



	/**
	 * Render the options list.
	 *
	 * @param array $options the options for the feedback form.
	 */
	private function render_options_list( $options ) {
		$key            = 'optml';
		?>
		<ul class="popup--form">
			<?php foreach ( $options as $title => $attributes ) { ?>
				<li optml-option-id="<?php echo esc_attr( $attributes['id'] ); ?>">
					<input type="radio" name="optml-deactivate-option" id="<?php echo esc_attr( $key . $attributes['id'] ); ?>">
					<label for="<?php echo esc_attr( $key . $attributes['id'] ); ?>">
						<?php echo $title; ?>
					</label>

				</li>
			<?php } ?>
		</ul>
		<?php
	}

	/**
	 * Render plugin feedback popup.
	 */
	private function render_plugin_feedback_popup() {
		$button_cancel        = $this->button_cancel;
		$button_submit        = $this->button_submit;
		$options              = $this->options_plugin;

		?>
		<div class="optml-plugin-uninstall-feedback-popup optml-feedback" id="<?php echo esc_attr( 'optml_uninstall_feedback_popup' ); ?>">
			<div class="popup--header">
				<h5><?php echo wp_kses( $this->heading_plugin, array( 'span' => true ) ); ?> </h5>
			</div><!--/.popup--header-->
			<div class="popup--body">
				<?php $this->render_options_list( $options ); ?>
			</div><!--/.popup--body-->
			<div class="popup--footer">
				<div class="actions">
					<?php

					echo '<div class="buttons">';
					echo get_submit_button(
						$button_cancel,
						'secondary',
						'optml-deactivate-no',
						false
					);
					echo get_submit_button(
						$button_submit,
						'primary',
						'optml-deactivate-yes',
						false,
						array(
							'data-after-text' => $button_submit,
							'disabled'        => true,
						)
					);
					echo '</div>';
					?>
				</div><!--/.actions-->
			</div><!--/.popup--footer-->
		</div>

		<?php
	}

	/**
	 * Add plugin feedback popup JS
	 */
	private function add_plugin_feedback_popup_js() {
		$popup_id = '#optml_uninstall_feedback_popup';
		$key      = 'optml';
		?>
		<script type="text/javascript" id="optml-deactivate-js">
			(function ($) {
				$(document).ready(function () {
					var targetElement = 'tr[data-plugin="optimole-wp/optimole-wp.php"] span.deactivate a';
					var redirectUrl = $(targetElement).attr('href');
					if ($('.optml-feedback-overlay').length === 0) {
						$('body').prepend('<div class="optml-feedback-overlay"></div>');
					}
					$('<?php echo esc_attr( $popup_id ); ?> ').appendTo($(targetElement).parent());

					$(targetElement).on('click', function (e) {
						e.preventDefault();
						$('<?php echo esc_attr( $popup_id ); ?> ').addClass('active');
						$('body').addClass('optml-feedback-open');
						$('.optml-feedback-overlay').on('click', function () {
							$('<?php echo esc_attr( $popup_id ); ?> ').removeClass('active');
							$('body').removeClass('optml-feedback-open');
						});
					});

					$('<?php echo esc_attr( $popup_id ); ?> input[type="radio"]').on('change', function () {
						var radio = $(this);
						$('<?php echo esc_attr( $popup_id ); ?> #optml-deactivate-yes').removeAttr('disabled');
					});
					$('<?php echo esc_attr( $popup_id ); ?> #optml-deactivate-no').on('click', function (e) {
						e.preventDefault();
						e.stopPropagation();
						$(targetElement).unbind('click');
						$('body').removeClass('optml-feedback-open');
						$('<?php echo esc_attr( $popup_id ); ?>').remove();
						if (redirectUrl !== '') {
							location.href = redirectUrl;
						}
					});
					$('<?php echo esc_attr( $popup_id ); ?> #optml-deactivate-yes').on('click', function (e) {
						e.preventDefault();
						e.stopPropagation();
						$(targetElement).unbind('click');
						var selectedOption = $(
							'<?php echo esc_attr( $popup_id ); ?> input[name="optml-deactivate-option"]:checked');
						var data = {
							'action': '<?php echo 'optml_uninstall_feedback'; ?>',
							'nonce': '<?php echo wp_create_nonce( (string) __CLASS__ ); ?>',
							'id': selectedOption.parent().attr('optml-option-id'),
						};
						$.ajax({
							type: 'POST',
							url: ajaxurl,
							data: data,
							complete() {
								$('body').removeClass('optml-feedback-open');
								$('<?php echo esc_attr( $popup_id ); ?>').remove();
								if (redirectUrl !== '') {
									location.href = redirectUrl;
								}
							},
							beforeSend() {
								$('<?php echo esc_attr( $popup_id ); ?>').addClass('sending-feedback');
								$('<?php echo esc_attr( $popup_id ); ?> .popup--footer').remove();
								$('<?php echo esc_attr( $popup_id ); ?> .popup--body').html('<i class="dashicons dashicons-update-alt"></i>');
							}
						});
					});
				});
			})(jQuery);

		</script>
		<?php

	}




	/**
	 * Called when the deactivate button is clicked.
	 */
	function post_deactivate() {
		check_ajax_referer( (string) __CLASS__, 'nonce' );

		if ( empty( $_POST['id'] ) ) {

			wp_send_json( [] );

			return;
		}
		if ( $_POST['id'] == 1 ) {
			$option_name = array('optml-version', 'optml_dismissed_conflicts', 'optml_settings', 'optml_notice_optin');
			foreach ( $option_name as $index => $option ) {
				delete_option( $option );
			}
		}
		wp_send_json( [] );

	}
}
