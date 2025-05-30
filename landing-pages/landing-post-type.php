<?php
/**
 * Post Type Template
 */

if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.


/**
 * DT_Porch_Landing_Post_Type Class
 * All functionality pertaining to project update post types in DT_Porch_Landing_Post_Type.
 *
 * @package  Disciple_Tools
 * @since    0.1.0
 */
class DT_Porch_Landing_Post_Type
{

    public $post_type;
    public $singular;
    public $plural;
    public $args;
    public $taxonomies;
    private static $_instance = null;
    public static function instance() {
        if (is_null( self::$_instance )) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Disciple_Tools_Prayer_Post_Type constructor.
     *
     * @param array $args
     * @param array $taxonomies
     */
    public function __construct( $args = [], $taxonomies = []) {
        $this->post_type = PORCH_LANDING_POST_TYPE;
        $this->singular = PORCH_LANDING_POST_TYPE_SINGLE;
        $this->plural = PORCH_LANDING_POST_TYPE_PLURAL;
        $this->args = $args;
        $this->taxonomies = $taxonomies;

        add_action( 'init', [ $this, 'register_post_type' ] );
        add_action( 'transition_post_status', [ $this, 'transition_post' ], 10, 3 );
        add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
        add_action( 'save_post', [ $this, 'save_meta_box' ], 10, 2 );

        if ( is_admin() && isset( $_GET['post_type'] ) && PORCH_LANDING_POST_TYPE === $_GET['post_type'] ){

            add_filter( 'manage_'.$this->post_type.'_posts_columns', [ $this, 'set_custom_edit_columns' ] );
            add_action( 'manage_'.$this->post_type.'_posts_custom_column', [ $this, 'custom_column' ], 10, 2 );
        }

    } // End __construct()

    public function add_meta_box( $post_type ) {
        if ( PORCH_LANDING_POST_TYPE === $post_type ) {
            add_meta_box( PORCH_LANDING_POST_TYPE . '_custom_permalink', PORCH_LANDING_POST_TYPE_SINGLE . ' Url', [ $this, 'meta_box_custom_permalink' ], PORCH_LANDING_POST_TYPE, 'side', 'default' );
            
            add_meta_box(
                'porch_page_title_meta_box',
                'Page Title',
                [ $this, 'meta_box_page_title' ],
                PORCH_LANDING_POST_TYPE,
                'normal',
                'high'
            );

            add_meta_box(
                'porch_meta_description_meta_box',
                'Meta Description',
                [ $this, 'meta_box_meta_description' ],
                PORCH_LANDING_POST_TYPE,
                'normal',
                'high'
            );
        add_meta_box(
            'porch_admin_notes_meta_box',
            'Admin Notes',
            [ $this, 'meta_box_admin_notes' ],
            PORCH_LANDING_POST_TYPE,
            'side',
            'default'
        );
        }
    }

    public function meta_box_custom_permalink( $post ) {
        $public_key = get_post_meta( $post->ID, PORCH_LANDING_META_KEY, true );
        echo '<a href="' . esc_url( trailingslashit( site_url() ) ) . esc_attr( PORCH_LANDING_ROOT ) . '/' . esc_attr( PORCH_LANDING_TYPE ) . '/' . esc_attr( $public_key ) . '">'. esc_url( trailingslashit( site_url() ) ) . esc_attr( PORCH_LANDING_ROOT ) . '/' . esc_attr( PORCH_LANDING_TYPE ) . '/' . esc_attr( $public_key ) .'</a>';
    }

    public function meta_box_page_title( $post ) {
        // Add nonce for security
        wp_nonce_field( 'porch_page_title_nonce', 'porch_page_title_nonce' );
        
        // Get existing value
        $page_title = get_post_meta( $post->ID, 'porch_page_title', true );
        
        // Output the field
        ?>
        <p>
            <label for="porch_page_title">Custom Page Title:</label><br>
            <input type="text" id="porch_page_title" name="porch_page_title" value="<?php echo esc_attr( $page_title ); ?>" style="width: 100%;">
            <span class="description">This title will be used when viewing the landing page. If left empty, the post title will be used instead.</span>
        </p>
        <?php
    }

    public function meta_box_meta_description( $post ) {
        // Add nonce for security
        wp_nonce_field( 'porch_meta_description_nonce', 'porch_meta_description_nonce' );
        
        // Get existing value
        $meta_description = get_post_meta( $post->ID, 'porch_meta_description', true );
        
        // Output the field
        ?>
        <p>
            <label for="porch_meta_description">Meta Description:</label><br>
            <textarea id="porch_meta_description" name="porch_meta_description" rows="3" style="width: 100%;"><?php echo esc_textarea( $meta_description ); ?></textarea>
            <span class="description">This description will be used in the meta tags for SEO purposes. Recommended length is 150-160 characters.</span>
        </p>
        <?php
    }

    public function meta_box_admin_notes( $post ) {
        // Add nonce for security
        wp_nonce_field( 'porch_admin_notes_nonce', 'porch_admin_notes_nonce' );
        
        // Get existing value
        $admin_notes = get_post_meta( $post->ID, 'porch_admin_notes', true );
        
        // Output the field
        ?>
        <p>
            <label for="porch_admin_notes">Admin Notes:</label><br>
            <textarea id="porch_admin_notes" name="porch_admin_notes" rows="3" style="width: 100%;"><?php echo esc_textarea( $admin_notes ); ?></textarea>
            <span class="description">This is a private note for the admin to keep track of any special instructions or notes.</span>   
        </p>
        <?php
    }

    public function save_meta_box( $post_id, $post ) {
        // Check if our nonces are set
        if ( ! isset( $_POST['porch_page_title_nonce'] ) || 
             ! isset( $_POST['porch_meta_description_nonce'] ) ||
             ! isset( $_POST['porch_admin_notes_nonce'] ) ) {
            return;
        }

        // Verify that the nonces are valid
        if ( ! wp_verify_nonce( $_POST['porch_page_title_nonce'], 'porch_page_title_nonce' ) ||
             ! wp_verify_nonce( $_POST['porch_meta_description_nonce'], 'porch_meta_description_nonce' ) ||
             ! wp_verify_nonce( $_POST['porch_admin_notes_nonce'], 'porch_admin_notes_nonce' ) ) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Check the user's permissions
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Sanitize and save the page title
        if ( isset( $_POST['porch_page_title'] ) ) {
            $page_title = sanitize_text_field( wp_unslash( $_POST['porch_page_title'] ) );
            update_post_meta( $post_id, 'porch_page_title', $page_title );
        }

        // Sanitize and save the meta description
        if ( isset( $_POST['porch_meta_description'] ) ) {
            $meta_description = sanitize_textarea_field( wp_unslash( $_POST['porch_meta_description'] ) );
            update_post_meta( $post_id, 'porch_meta_description', $meta_description );
        }

        // Sanitize and save the admin notes
        if ( isset( $_POST['porch_admin_notes'] ) ) {
            $admin_notes = sanitize_textarea_field( wp_unslash( $_POST['porch_admin_notes'] ) );
            update_post_meta( $post_id, 'porch_admin_notes', $admin_notes );
        }
    }

    /**
     * Register the post type.
     *
     * @access public
     * @return void
     */
    public function register_post_type() {
        register_post_type($this->post_type, /* (http://codex.wordpress.org/Function_Reference/register_post_type) */
            // let's now add all the options for this post type
            array(
                'labels' => array(
                    'name' => $this->plural, /* This is the Title of the Group */
                    'singular_name' => $this->singular, /* This is the individual type */
                    'all_items' => 'All '.$this->plural, /* the all items menu item */
                    'add_new' => 'Add New', /* The add new menu item */
                    'add_new_item' => 'Add New '.$this->singular, /* Add New Display Title */
                    'edit' => 'Edit', /* Edit Dialog */
                    'edit_item' => 'Edit '.$this->singular, /* Edit Display Title */
                    'new_item' => 'New '.$this->singular, /* New Display Title */
                    'view_item' => 'View '.$this->singular, /* View Display Title */
                    'search_items' => 'Search '.$this->plural, /* Search Custom Type Title */
                    'not_found' => 'Nothing found in the Database.', /* This displays if there are no entries yet */
                    'not_found_in_trash' => 'Nothing found in Trash', /* This displays if there is nothing in the trash */
                    'parent_item_colon' => ''
                ), /* end of arrays */
                'description' => $this->singular, /* Custom Type Description */
                'public' => true,
                'publicly_queryable' => false,
                'exclude_from_search' => true,
                'show_ui' => true,
                'query_var' => false,
                'show_in_nav_menus' => true,
                'menu_position' => 60, /* this is what order you want it to appear in on the left hand side menu */
                'menu_icon' => 'dashicons-book', /* the icon for the custom post type menu. uses built-in dashicons (CSS class name) */
                'rewrite' => false, /* you can specify its url slug */
                'has_archive' => false, /* you can rename the slug here */
                'capabilities' => [
                    'create_posts'        => 'create_'.$this->post_type,
                    'edit_post'           => 'edit_'.$this->post_type, // needed for bulk edit
                    'read_post'           => 'read_'.$this->post_type,
                    'delete_post'         => 'delete_'.$this->post_type, // delete individual post
                    'delete_others_posts' => 'delete_others_'.$this->post_type.'s',
                    'delete_posts'        => 'delete_'.$this->post_type.'s', // bulk delete posts
                    'edit_posts'          => 'edit'.$this->post_type.'s', //menu link in WP Admin
                    'edit_others_posts'   => 'edit_others_'.$this->post_type.'s',
                    'publish_posts'       => 'publish_'.$this->post_type.'s',
                    'read_private_posts'  => 'read_private_'.$this->post_type.'s',
                ],
                'capability_type' => $this->post_type,
                'hierarchical' => true,
                'show_in_rest' => true,
                'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt',  'wp-block-styles' , 'align-wide' )
            ) /* end of options */
        ); /* end of register post type */
    } // End register_post_type()


    public function transition_post( $new_status, $old_status, $post ) {
        if ( 'publish' == $new_status && $post->post_type == PORCH_LANDING_POST_TYPE ) {

            $post_id = $post->ID;
            $slug = trim( strtolower( $post->post_title ) );
            $slug = str_replace( ' ', '-', $slug );
            $slug = str_replace( '"', '', $slug );
            $slug = str_replace( '&', '', $slug );
            $slug = str_replace( "'", '', $slug );
            $slug = str_replace( ",", '', $slug );
            $slug = str_replace( ":", '', $slug );
            $slug = str_replace( ";", '', $slug );
            $slug = str_replace( ".", '', $slug );
            $slug = str_replace( "/", '', $slug );
            $slug = urlencode( $slug );

            $current_public_key = get_post_meta( $post_id, PORCH_LANDING_META_KEY, true );
            if ( $slug !== $current_public_key ) {
                update_post_meta( $post_id, PORCH_LANDING_META_KEY, $slug );
                global $wpdb;
                $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET guid = %s WHERE ID = %s;", trailingslashit( site_url() ) . PORCH_LANDING_ROOT . '/' . PORCH_LANDING_TYPE . '/' . $slug, $post_id ) );
            }
        }
    }

    // Add the custom columns to the book post type:
    public function set_custom_edit_columns( $columns) {
        unset( $columns['author'] );
        $columns['url'] = 'URL';
        $columns['porch_admin_notes'] = 'Admin Notes';

        return $columns;
    }

    // Add the data to the custom columns for the book post type:
    public function custom_column( $column, $post_id ) {
        switch ( $column ) {
            case 'url' :
                $public_key = get_post_meta( $post_id, PORCH_LANDING_META_KEY, true );
                echo '<a href="' . esc_url( trailingslashit( site_url() ) ) . esc_attr( PORCH_LANDING_ROOT ) . '/' . esc_attr( PORCH_LANDING_TYPE ) . '/' . esc_attr( $public_key ) . '">'. esc_url( trailingslashit( site_url() ) ) . esc_attr( PORCH_LANDING_ROOT ) . '/' . esc_attr( PORCH_LANDING_TYPE ) . '/' . esc_attr( $public_key ) .'</a>';
                break;
            case 'porch_admin_notes' :
                $admin_notes = get_post_meta( $post_id, 'porch_admin_notes', true );
                echo $admin_notes;
                break;
        }
    }
} // End Class
DT_Porch_Landing_Post_Type::instance();
