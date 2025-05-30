<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Class DT_Porch_Menu
 */
class DT_Porch_Landing_Menu {

    public $token = 'dt_porch';
    public $title = 'Settings';

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        if ( ! is_admin() ) {
            return;
        }

        add_action( "admin_menu", array( $this, "register_menu" ) );

    } // End __construct()

    /**
     * Loads the subnav page
     * @since 0.1
     */
    public function register_menu() {
        add_submenu_page( 'edit.php?post_type='.PORCH_LANDING_POST_TYPE, $this->title, $this->title, 'manage_dt', $this->token, [ $this, 'content' ] );
    }

    /**
     * Menu stub. Replaced when Disciple Tools Theme fully loads.
     */
    public function extensions_menu() {}

    /**
     * Builds page contents
     * @since 0.1
     */
    public function content() {

        if ( !current_user_can( 'manage_dt' ) ) { // manage dt is a permission that is specific to Disciple Tools and allows admins, strategists and dispatchers into the wp-admin
            wp_die( 'You do not have sufficient permissions to access this page.' );
        }

        if ( isset( $_GET["tab"] ) ) {
            $tab = sanitize_key( wp_unslash( $_GET["tab"] ) );
        } else {
            $tab = 'general';
        }

        $link = 'admin.php?page='.$this->token.'&tab=';

        ?>
        <div class="wrap">
            <h2><?php echo esc_html( PORCH_LANDING_POST_TYPE_SINGLE ) ?></h2>
            <h2 class="nav-tab-wrapper">
                <a href="<?php echo esc_attr( $link ) . 'general' ?>"
                   class="nav-tab <?php echo esc_html( ( $tab == 'general' || !isset( $tab ) ) ? 'nav-tab-active' : '' ); ?>">General</a>
            </h2>

            <?php
            switch ($tab) {
                case "general":
                    $object = new DT_Porch_Landing_Tab_General();
                    $object->content();
                    break;
                default:
                    break;
            }
            ?>

        </div><!-- End wrap -->

        <?php
    }
}
DT_Porch_Landing_Menu::instance();


/**
 * Class DT_Porch_Tab_General
 */
class DT_Porch_Landing_Tab_General {
    public function content() {
        ?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <!-- Main Column -->

                        <?php $this->main_column() ?>
                        <?php $this->google_analytics_box() ?>

                        <!-- End Main Column -->
                    </div><!-- end post-body-content -->
                    <div id="postbox-container-1" class="postbox-container">
                        <!-- Right Column -->

                        <?php $this->right_column() ?>

                        <!-- End Right Column -->
                    </div><!-- postbox-container 1 -->
                    <div id="postbox-container-2" class="postbox-container">
                    </div><!-- postbox-container 2 -->
                </div><!-- post-body meta box container -->
            </div><!--poststuff end -->
        </div><!-- wrap end -->
        <?php
    }

    public function main_column() {

        if ( isset( $_POST['landing_home'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['landing_home'] ) ), 'landing_home'. get_current_user_id() ) && isset( $_POST['selected_home_page'] ) ) {
            $id = sanitize_text_field( wp_unslash( $_POST['selected_home_page'] ) );
            update_option( PORCH_LANDING_META_KEY . '_home_page', $id, true );
        }

        $selected = get_option( PORCH_LANDING_META_KEY . '_home_page' );
        $args = array(
            'post_type' => PORCH_LANDING_POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'post_title',
            'order' => 'ASC'
        );
        $list = new WP_Query( $args );
        ?>
        <!-- Box -->
        <form method="post">
            <?php wp_nonce_field( 'landing_home'. get_current_user_id(), 'landing_home' ) ?>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Home Page</th>
                </tr>
            </thead>
            <tbody>

            <tr>
                <td>
                    <select name="selected_home_page">
                        <option value="0">No Home Page</option>
                        <option disabled>---</option>
                        <?php
                        if ( ! empty( $list->posts ) ) {
                            foreach ( $list->posts as $post_object ) {
                                if ( $selected == $post_object->ID ) {
                                    ?>
                                    <option value="<?php echo esc_attr( $post_object->ID ) ?>" selected><?php echo esc_html( $post_object->post_title ) ?></option>
                                    <?php
                                } else {
                                    ?>
                                    <option value="<?php echo esc_attr( $post_object->ID ) ?>"><?php echo esc_html( $post_object->post_title ) ?></option>
                                    <?php
                                }
                            }
                        }
                        ?>
                    </select>

                    <button type="submit" class="button">Update</button>
                </td>
            </tr>
            </tbody>
        </table>
        </form>
        <br>
        <!-- End Box -->
        <?php
    }
    public function google_analytics_box() {

        if ( isset( $_POST['google_analytics_id'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['google_analytics_nonce'] ) ), 'google_analytics'. get_current_user_id() ) ) {
            $id = sanitize_text_field( wp_unslash( $_POST['google_analytics_id'] ) );
            update_option( PORCH_LANDING_META_KEY . '_google_analytics_id', $id, true );
        }

        $selected = get_option( PORCH_LANDING_META_KEY . '_google_analytics_id' );
        $args = array(
            'post_type' => PORCH_LANDING_POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'post_title',
            'order' => 'ASC'
        );
        $list = new WP_Query( $args );
        ?>
        <!-- Box -->
        <form method="post">
            <?php wp_nonce_field( 'google_analytics'. get_current_user_id(), 'google_analytics_nonce' ) ?>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Google Analytics Script</th>
                </tr>
            </thead>
            <tbody>

            <tr>
                <td>
                    <input type="text" name="google_analytics_id" placeholder="ex. UA-1234543490" value="<?php echo esc_attr( get_option( PORCH_LANDING_META_KEY . '_google_analytics_id' ) ) ?>">

                    <button type="submit" class="button">Update</button>
                </td>
            </tr>
            </tbody>
        </table>
        </form>
        <br>
        <!-- End Box -->
        <?php
    }

    public function right_column() {
        ?>
        <!-- Box -->
        <table class="widefat striped">
            <thead>
                <tr>
                    <th>Supported Editor Plugins</th>
                </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                   <p>
                       Because of the security structure of Disciple.Tools, some features of standard Wordpress are not available and not
                        all plugins will be compatible with this porch plugin.
                   </p>
                    <p>
                        A few top editor/site builder plugins are supported by this porch plugin. The list below are those we recommend and
                        support in this plugin.
                    </p>
                </td>
            </tr>
            <tr>
                <td>
                    <h3><a href="https://wordpress.org/plugins/genesis-blocks/">Genesis Blocks</a></h3>
                    <p>Genesis Blocks is a collection of page building blocks for the Gutenberg block editor.</p>
                    <p><a href="https://wordpress.org/plugins/genesis-blocks/">Go to Plugin Website</a></p>
                </td>
            </tr>
            <tr>
                <td>
                    <h3><a href="https://wordpress.org/plugins/blocks-animation/">Blocks Animation: CSS Animations for Gutenberg Blocks</a></h3>
                    <p>Blocks Animation allows you to add CSS Animations to all of your Gutenberg blocks in the most elegant way.</p>
                </td>
            </tr>
            <tr>
                <td>
                    <h3><a href="https://wordpress.org/plugins/ultimate-addons-for-gutenberg/">Ultimate Addons for Gutenberg</a></h3>
                    <p>Power-up the Gutenberg editor with advanced and powerful blocks that help you build websites in no time!</p>
                    <p><a href="https://wordpress.org/plugins/ultimate-addons-for-gutenberg/">Go to Plugin Website</a></p>
                </td>
            </tr>
            </tbody>
        </table>
        <br>
        <!-- End Box -->
        <?php
    }
}


