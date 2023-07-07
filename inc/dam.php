<?php
/**
 * Dam class.
 *
 * Author:          Andrei Baicus <andrei@themeisle.com>
 * Created on:      04/07/2023
 *
 * @package    \Optimole\Inc
 * @author     Optimole <friends@optimole.com>
 */

/**
 * Class Optml_Dam
 */
class Optml_Dam {
    /**
     * Hold the settings object.
     *
     * @var Optml_Settings Settings object.
     */
    public $settings;

    private $dam_endpoint = 'https://dashboard.optimole.com/dam';

    public function __construct() {
        $this->settings = Optml_Main::instance()->admin->settings;

        if ( ! $this->settings->is_connected() ) {
            return;
        }

        if ( defined( 'OPTML_DAM_ENDPOINT' ) && constant( 'OPTML_DAM_ENDPOINT' ) ) {
            $this->dam_endpoint = constant( 'OPTML_DAM_ENDPOINT' );
        }

        add_action( 'admin_menu', [ $this, 'add_menu' ] );
        add_action( 'print_media_templates', [ $this, 'print_media_template' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'add_cloud_script' ] );
        add_action( 'elementor/editor/after_enqueue_scripts', [ $this, 'add_cloud_script' ] );
    }

    public function add_menu() {
        if ( defined( 'OPTIOMLE_HIDE_ADMIN_AREA' ) && OPTIOMLE_HIDE_ADMIN_AREA ) {
            return;
        }
        add_media_page( 'Optimole Assets', 'Optimole Assets', 'manage_options', 'optimole-dam', [
                $this,
                'render_dashboard_page'
        ] );
    }

    /**
     * Add media template to be used in the media library.
     *
     * @return void
     */
    public function print_media_template() {
        $iframeSrc = $this->build_iframe_url();
        $site_url  = get_site_url();

        ?>
        <style>
            .om-dam-wrap {
                height: 100%;
            }

            [aria-labelledby=menu-item-optimole] {
                overflow: hidden;
            }

            #om-dam {
                width: 100%;
                height: 100%;
                border: none;
                position: relative;
            }
        </style>
        <script type="text/html" id="tmpl-optimole-dam">
            <style>
                .media-frame-content {
                    bottom: 0;
                }
                .media-frame-toolbar {
                    display: none;
                }
            </style>
            <iframe id="om-dam" src="<?php echo( $iframeSrc ); ?>"></iframe>
        </script>
        <?php
    }

    /**
     * Render the dashboard page.
     *
     * @return void
     */
    public function render_dashboard_page() {
        $iframeSrc = $this->build_iframe_url();
        $site_url  = get_site_url();

        ?>
        <style>
            :root {
                --om-admin-bar-height: 32px;
            }

            @media screen and (max-width: 782px) {
                :root {
                    --om-admin-bar-height: 46px;
                }
            }

            #om-dam {
                height: calc(100vh - var(--om-admin-bar-height));
                width: 100%;
                border: none;
                z-index: 1;
                position: relative;
            }

            #wpcontent, #wpbody-content {
                padding: 0;
            }
        </style>
        <iframe id="om-dam" src="<?php echo( $iframeSrc ); ?>"></iframe>
        <script>
            const siteUrl = '<?php echo esc_url( $site_url ); ?>';
            const iframe = document.getElementById('om-dam');

            window.addEventListener('message', function (event) {
                if (!event.data) {
                    return;
                }

                if (!event.data.type) {
                    return;
                }

                if (event.data.type !== 'om-dam') {
                    return;
                }

                if (!event.data.action) {
                    return;
                }

                if (event.data.action !== 'getUrl') {
                    return;
                }

                iframe.contentWindow.postMessage({siteUrl, type: 'om-dam', context: 'browse'}, '*');
            });
        </script>
        <?php
    }

    /**
     * Build the iFrame URL.
     *
     * @return string
     */
    private function build_iframe_url() {
        $api_key         = $this->settings->get( 'api_key' );
        $connected_sites = $this->settings->get( 'cloud_sites' );

        if ( empty( $api_key ) ) {
            return '';
        }


        if ( isset( $connected_sites['all'] ) && $connected_sites['all'] === 'true' ) {
            $connected_sites = [];
        } else {
            foreach ( $connected_sites as $site => $status ) {
                if ( $status !== true ) {
                    unset( $connected_sites[ $site ] );
                }
            }
        }

        $data = array(
                'site'  => get_site_url(),
                'token' => $api_key,
                'sites' => array_keys( $connected_sites ),
        );

        $data = json_encode( $data );
        $data = base64_encode( $data );

        return add_query_arg( array(
                'data' => $data,
        ), $this->dam_endpoint );
    }

    /**
     * Enqueue script for generating cloud media tab.
     *
     * @param string $hook The current admin page.
     */
    public function add_cloud_script( $hook ) {
        // TODO: review condition here:
//        if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) {
//            return;
//        }

        $asset_file = include OPTML_PATH . 'assets/build/media/index.asset.php';

        wp_register_script(
                OPTML_NAMESPACE . '-media-modal',
                OPTML_URL . 'assets/build/media/index.js',
                $asset_file['dependencies'],
                $asset_file['version'],
                true
        );

//        wp_localize_script( OPTML_NAMESPACE . '-media-modal', 'optmlMediaModal', $this->get_localized_vars() );
        wp_enqueue_script( OPTML_NAMESPACE . '-media-modal' );
    }
}
