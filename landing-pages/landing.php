<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class DT_Porch_Landing extends DT_Magic_Url_Base
{
    public $page_title = '';
    public $meta_description = '';
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

        /**
         * tests if other URL
         */
        $url = dt_get_url_path();
        $length = strlen( $this->root . '/' . $this->type );
        if ( substr( $url, 0, $length ) !== $this->root . '/' . $this->type ) {
            return;
        }
        /**
         * tests magic link parts are registered and have valid elements
         */
        if ( !$this->check_parts_match( false ) ){
            return;
        }

        // Get the post ID from the URL
        $post_id = $this->get_post_id_from_url();
        if ($post_id) {
            // Load the page title from post meta, fallback to post title if not set
            $this->page_title = get_post_meta($post_id, 'porch_page_title', true);
            if (empty($this->page_title)) {
                $post = get_post($post_id);
                $this->page_title = $post ? $post->post_title : PORCH_LANDING_POST_TYPE_SINGLE;
            }

            // Load the meta description
            $this->meta_description = get_post_meta($post_id, 'porch_meta_description', true);
            if (empty($this->meta_description)) {
                $post = get_post($post_id);
                $this->meta_description = $post ? wp_trim_words($post->post_content, 25, '...') : '';
            }
        }

        // load if valid url
        add_action( 'dt_blank_body', [ $this, 'body' ] ); // body for no post key
        add_action( 'wp_head', [ $this, 'add_meta_tags' ] ); // Add meta tags to head

        require_once( 'enqueue.php' );
        add_filter( 'dt_magic_url_base_allowed_css', [ $this, 'dt_magic_url_base_allowed_css' ], 10, 1 );
        add_filter( 'dt_magic_url_base_allowed_js', [ $this, 'dt_magic_url_base_allowed_js' ], 10, 1 );
        add_action( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ], 99 );
    }

    /**
     * Get the post ID from the URL parameters
     * 
     * @return int|false The post ID if found, false otherwise
     */
    private function get_post_id_from_url() {
        $url = dt_get_url_path();
        $parts = explode('/', $url);
        
        // The URL format should be: root/type/public_key
        if (count($parts) >= 3) {
            $public_key = $parts[2];
            
            // Find the post with this public key
            $args = array(
                'post_type' => $this->post_type,
                'meta_key' => $this->meta_key,
                'meta_value' => $public_key,
                'posts_per_page' => 1
            );
            
            $query = new WP_Query($args);
            if ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                wp_reset_postdata();
                return $post_id;
            }
        }
        
        return false;
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
        require_once( 'body.php' );
    }

    public function footer_javascript(){
        require_once( 'footer.php' );
    }

    public function header_javascript(){
        require_once( 'header.php' );
    }

    /**
     * Add meta tags to the head section
     */
    public function add_meta_tags() {
        if (!empty($this->meta_description)) {
            echo '<meta name="description" content="' . esc_attr($this->meta_description) . '">' . "\n";
        }
    }
}
DT_Porch_Landing::instance();
