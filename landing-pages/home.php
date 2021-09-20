<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class DT_Porch_Landing_Home extends DT_Magic_Url_Base
{
    public $page_title = PORCH_LANDING_POST_TYPE_SINGLE;
    public $root = PORCH_LANDING_ROOT;
    public $type = PORCH_LANDING_TYPE;
    public $post_type = PORCH_LANDING_POST_TYPE;
    public $meta_key = PORCH_LANDING_META_KEY;

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        parent::__construct();

        $url = dt_get_url_path();
        if ( empty( $url ) && ! dt_is_rest() ) {
            require_once( 'enqueue.php' );

            // register url and access
            add_action( "template_redirect", [ $this, 'theme_redirect' ] );
            add_filter( 'dt_blank_access', function (){ return true;
            }, 100, 1 ); // allows non-logged in visit
            add_filter( 'dt_allow_non_login_access', function (){ return true;
            }, 100, 1 );

            // header content
            // register url and access
            add_filter( 'dt_blank_access', [ $this, '_has_access' ] ); // gives access once above tests are passed
            add_filter( 'dt_templates_for_urls', [ $this, 'register_url' ], 199, 1 ); // registers url as valid once tests are passed
            add_filter( 'dt_allow_non_login_access', function (){ // allows non-logged in visit
                return true;
            }, 100, 1 );
            add_filter( "dt_blank_title", [ $this, "page_tab_title" ] ); // adds basic title to browser tab
            add_action( 'wp_print_scripts', [ $this, 'print_scripts' ], 5 ); // authorizes scripts
            add_action( 'wp_print_footer_scripts', [ $this, 'print_scripts' ], 5 ); // authorizes scripts
            add_action( 'wp_print_styles', [ $this, 'print_styles' ], 1500 ); // authorizes styles

            add_action( 'dt_blank_head', [ $this, '_header' ] );
            add_action( 'dt_blank_footer', [ $this, '_footer' ] );


            // page content
            add_action( 'dt_blank_body', [ $this, 'body' ] ); // body for no post key

            add_filter( 'dt_magic_url_base_allowed_css', [ $this, 'dt_magic_url_base_allowed_css' ], 10, 1 );
            add_filter( 'dt_magic_url_base_allowed_js', [ $this, 'dt_magic_url_base_allowed_js' ], 10, 1 );
            add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ], 99 );
        }
    }

    public function dt_magic_url_base_allowed_js( $allowed_js ) {
        return DT_Porch_Landing_Enqueue::load_allowed_scripts( $allowed_js );
    }

    public function dt_magic_url_base_allowed_css( $allowed_css ) {
        return DT_Porch_Landing_Enqueue::load_allowed_styles( $allowed_css );
    }

    public function wp_enqueue_scripts() {
        DT_Porch_Landing_Enqueue::load_scripts();
    }

    public function body(){
        // body
        $my_postid = get_option( PORCH_LANDING_META_KEY . '_home_page' );
        $content_post = get_post( $my_postid );
        $content = $content_post->post_content;
        echo $content; // @phpcs:ignore
    }

    public function footer_javascript(){
        ?>
        <script>
            let jsObject = [<?php echo json_encode([
                'map_key' => DT_Mapbox_API::get_key(),
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'parts' => $this->parts,
                'translations' => [
                    'add' => __( 'Add Magic', 'disciple_tools' ),
                ],
            ]) ?>][0]

            jQuery(document).ready(function(){
                jQuery(document).foundation(); /* important. required when not loading site-js */
            })
        </script>
        <?php
        return true;
    }


}
DT_Porch_Landing_Home::instance();
