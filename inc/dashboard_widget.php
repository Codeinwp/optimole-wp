<?php

/**
 * Dashboard widget class.
 *
 * @package Optimole\Dashboard
 *
 * @since 4.0.0
 */
class Optml_Dashboard_Widget {

	/**
	 * Handle for the widget & assets.
	 *
	 * @var string
	 */
	private $handle = OPTML_NAMESPACE . '-dashboard-widget';

	/**
	 * Initialize the dashboard widget.
	 */
	public function init() {
		if ( defined( 'OPTIOMLE_HIDE_ADMIN_AREA' ) && OPTIOMLE_HIDE_ADMIN_AREA ) {
			return;
		}
		add_action( 'wp_dashboard_setup', [ $this, 'add_dashboard_widget' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_widget' ] );
	}

	/**
	 * Add the dashboard widget.
	 */
	public function add_dashboard_widget() {
		if ( ! $this->has_at_least_ten_visits() ) {
			return;
		}

		wp_add_dashboard_widget( $this->handle, sprintf( 'Optimole - %s', __( 'Image Optimization Stats', 'optimole-wp' ) ), [ $this, 'render_widget' ] );
	}

	/**
	 * Enqueue the widget assets.
	 */
	public function enqueue_widget() {
		if ( ! $this->is_main_dashboard_page() || ! $this->has_at_least_ten_visits() ) {
			return;
		}

		$asset_file = include OPTML_PATH . 'assets/build/widget/index.asset.php';

		wp_register_script( $this->handle, OPTML_URL . 'assets/build/widget/index.js', $asset_file['dependencies'], $asset_file['version'], true );
		wp_localize_script( $this->handle, 'optimoleDashboardWidget', $this->get_script_localization() );
		wp_enqueue_script( $this->handle );
		wp_enqueue_style( $this->handle, OPTML_URL . 'assets/build/widget/style-index.css', [], $asset_file['version'] );
	}

	/**
	 * Render the widget.
	 */
	public function render_widget() {
		echo '<div id="optimole-dashboard-widget-root">' . $this->get_skeleton_loader() . '</div>';
	}

	/**
	 * Check if the current page is the main dashboard page.
	 *
	 * @return bool
	 */
	private function is_main_dashboard_page() {
		global $pagenow;

		return is_admin() && $pagenow === 'index.php';
	}

	/**
	 * Check if the user has at least 10 visits.
	 *
	 * @return bool
	 */
	private function has_at_least_ten_visits() {
		$settings = new Optml_Settings();

		if ( OPTML_DEBUG && $settings->is_connected() ) {
			return true;
		}

		$service_data = $this->get_service_data();

		if ( ! isset( $service_data['visitors'] ) ) {
			return false;
		}

		$visits = $service_data['visitors'];

		return $visits >= 10;
	}

	/**
	 * Get the script localization.
	 *
	 * @return array
	 */
	private function get_script_localization() {
		return [
			'i18n' => [
				'averageCompression' => __( 'Average compression', 'optimole-wp' ),
				'traffic' => __( 'Traffic', 'optimole-wp' ),
				'duringLastMonth' => __( 'During last month', 'optimole-wp' ),
				'viewAllStats' => __( 'View all stats', 'optimole-wp' ),
				'monthlyVisitsQuota' => __( 'Monthly visits quota', 'optimole-wp' ),
				'upgrade' => __( 'Upgrade', 'optimole-wp' ),
			],
			'skeletonLoader' => $this->get_skeleton_loader(),
			'billingURL' => tsdk_translate_link( 'https://dashboard.optimole.com/settings/billing', 'query' ),
			'serviceData' => $this->get_service_data(),
			'assetsURL' => OPTML_URL . 'assets/',
			'dashboardMetricsURL' => esc_url( 'https://dashboard.optimole.com/metrics' ),
			'dashboardURL' => esc_url( tsdk_translate_link( 'https://dashboard.optimole.com' ) ),
		];
	}

	/**
	 * Get the service data.
	 *
	 * @return array
	 */
	private function get_service_data() {
		$settings = new Optml_Settings();

		return $settings->get( 'service_data' );
	}

	/**
	 * Get the skeleton loader markup.
	 *
	 * @return string
	 */
	private function get_skeleton_loader() {
		return '
    <div class="antialiased">
			<div class="p-4 flex flex-col gap-4 animate-pulse">
				<div class="flex items-center gap-4 p-4 bg-light-blue border border-gray-300 rounded-md">
          <div class="flex items-center">
					  <span class="w-12 h-12 bg-info/30 rounded-full"></span>
          </div>
					<div class="flex flex-col w-full gap-3">
						<div class="flex items-center gap-2">
							<div class="h-6 bg-gray-300 rounded w-24"></div>
							<div class="h-4 bg-gray-300 rounded w-32"></div>
						</div>
						<div class="w-full relative flex items-center gap-2">
							<div class="w-full h-3 rounded-md bg-gray-300 relative overflow-hidden"></div>
							<div class="h-5 bg-gray-300 rounded w-10"></div>
						</div>
					</div>
				</div>
				<div class="grid grid-cols-2 gap-4">
					<div class="flex bg-light-blue border border-gray-300 rounded-md basis-1/4 flex-col items-start p-4">
						<div class="w-12 h-12 bg-info/30 rounded-full mb-2"></div>
						<div class="flex w-full flex-col">
							<div class="h-6 bg-gray-300 rounded w-24 my-3"></div>
							<div class="h-4 bg-gray-300 rounded w-40 mb-1"></div>
							<div class="h-3 bg-gray-300 rounded w-32"></div>
						</div>
					</div>
					<div class="flex bg-light-blue border border-gray-300 rounded-md basis-1/4 flex-col items-start p-4">
						<div class="w-12 h-12 bg-info/30 rounded-full mb-2"></div>
						<div class="flex w-full flex-col">
							<div class="h-6 bg-gray-300 rounded w-24 my-3"></div>
							<div class="h-4 bg-gray-300 rounded w-20 mb-1"></div>
							<div class="h-3 bg-gray-300 rounded w-36"></div>
						</div>
					</div>
				</div>
			</div>
			<hr class="border-gray-200 m-0 border-0 border-b" />
			<div class="flex justify-between gap-4 items-center px-4 py-2 bg-gray-50">
				<div class="flex items-center gap-2">
					<div class="w-8 h-8 bg-gray-200 rounded"></div>
					<div class="h-5 bg-gray-200 rounded w-20"></div>
				</div>
				<div class="h-4 bg-gray-200 rounded w-24"></div>
			</div>
		</div>';
	}
}
