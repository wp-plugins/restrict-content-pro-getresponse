<?php
/**
 * Plugin Name:     Restrict Content Pro - GetResponse
 * Plugin URI:      http://section214.com
 * Description:     Include a GetResponse signup option with your RCP registration form
 * Version:         1.0.0
 * Author:          Daniel J Griffiths
 * Author URI:      http://section214.com
 * Text Domain:     rcp-getresponse
 *
 * @package         RCP\GetResponse
 * @author          Daniel J Griffiths <dgriffiths@section214.com>
 * @copyright       Copyright (c) 2015, Daniel J Griffiths
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
    exit;
}


if( ! class_exists( 'RCP_GetResponse' ) ) {


    /**
     * Main RCP_GetResponse class
     *
     * @since       1.0.0
     */
    class RCP_GetResponse {


        /**
         * @var         RCP_GetResponse $instance The one true RCP_GetResponse
         * @since       1.0.0
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      object self::$instance The one true RCP_GetResponse
         */
        public static function instance() {
            if( ! self::$instance ) {
                self::$instance = new RCP_GetResponse();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
                self::$instance->hooks();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        public function setup_constants() {
            // Plugin version
            define( 'RCP_GETRESPONSE_VER', '1.0.0' );

            // Plugin path
            define( 'RCP_GETRESPONSE_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'RCP_GETRESPONSE_URL', plugin_dir_path( __FILE__ ) );
        }


        /**
         * Include necessary files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {
            require_once RCP_GETRESPONSE_DIR . 'includes/functions.php';
        }


        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function hooks() {
            // Add the settings page
            add_action( 'admin_init', array( $this, 'register_settings' ), 100 );
            add_action( 'admin_menu', array( $this, 'admin_menu' ), 100 );

            // Add the subscription checkbox
            add_action( 'rcp_before_registration_submit_field', array( $this, 'add_fields' ), 100 );

            // Check if a user should be signed up
            add_action( 'rcp_form_processing', array( $this, 'check_for_signup' ), 10, 2 );

            // Display the signed up notice
            add_action( 'rcp_edit_member_after', array( $this, 'display_signup_notice' ) );
        }


        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public static function load_textdomain() {
            // Set filter for language directory
            $lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
            $lang_dir = apply_filters( 'rcp_getresponse_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'rcp-getresponse' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'rcp-getresponse', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/rcp-getresponse/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/rcp-getresponse/ folder
                load_textdomain( 'rcp-getresponse', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/rcp-getresponse/languages/ folder
                load_textdomain( 'rcp-getresponse', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'rcp-getresponse', false, $lang_dir );
            }
        }


        /**
         * Register the GetResponse settings
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function register_settings() {
            register_setting( 'rcp_getresponse_settings_group', 'rcp_getresponse_settings' );
        }


        /**
         * Add the GetResponse menu item
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function admin_menu() {
            add_submenu_page(
                'rcp-members',
                __( 'Restrict Content Pro GetResponse Settings', 'rcp-getresponse' ),
                __( 'GetResponse', 'rcp-getresponse' ),
                'manage_options',
                'rcp-getresponse',
                array( $this, 'render_settings_page' )
            );
        }


        /**
         * Render the settings page
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function render_settings_page() {
            $settings   = get_option( 'rcp_getresponse_settings' );
            $saved_list = isset( $settings['saved_list'] ) ? $settings['saved_list'] : false;
            
            echo '<div class="wrap">';
            echo '<h2>' . esc_html( get_admin_page_title() ) . '</h2>';

            if( isset( $_REQUEST['updated'] ) && $_REQUEST['updated'] !== false ) {
                echo '<div class="updated fade"><p><strong>' . __( 'Options saved', 'rcp-getresponse' ) . '</strong></p></div>';
            }
            ?>
            
            <form method="post" action="options.php" class="rcp_options_form">
                <?php settings_fields( 'rcp_getresponse_settings_group' ); ?>
                <?php $lists = rcp_getresponse_get_lists(); ?>

                <table class="form-table">
                    <tr>
                        <th>
                            <label for="rcp_getresponse_settings[api_key]"><?php _e( 'GetResponse API Key', 'rcp-getresponse' ); ?></label>
                        </th>
                        <td>
                            <input class="regular-text" type="text" id="rcp_getresponse_settings[api_key]" name="rcp_getresponse_settings[api_key]" value="<?php echo ( isset( $settings['api_key'] ) ? $settings['api_key'] : '' ); ?>" />
                            <div class="description"><?php _e( 'Enter your GetResponse API key to enable a newsletter signup option with the registration form.', 'rcp-getresponse' ); ?></div>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="rcp_getresponse_settings[saved_list]"><?php _e( 'Newsletter List', 'rcp-getresponse' ); ?></label>
                        </th>
                        <td>
                            <select id="rcp_getresponse_settings[saved_list]" name="rcp_getresponse_settings[saved_list]">
                                <?php
                                if( $lists ) {
                                    foreach( $lists as $list_id => $list_name ) {
                                        echo '<option value="' . esc_attr( $list_id ) . '"' . selected( $saved_list, $list_id, false ) . '>' . esc_html( $list_name ) . '</option>';
                                    }
                                } else {
                                    echo '<option value="no list">' . __( 'No lists', 'rcp-getresponse' ) . '</option>';
                                }
                                ?>
                            </select>
                            <div class="description"><?php _e( 'Choose the list to subscribe users to.', 'rcp-getresponse' ); ?></div>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="rcp_getresponse_settings[signup_label]"><?php _e( 'Form Label', 'rcp-getresponse' ); ?></label>
                        </th>
                        <td>
                            <input class="regular-text" type="text" id="rcp_getresponse_settings[signup_label]" name="rcp_getresponse_settings[signup_label]" value="<?php echo ( isset( $settings['signup_label'] ) && ! empty( $settings['signup_label'] ) ? $settings['signup_label'] : __( 'Signup for Newsletter', 'rcp-getresponse' ) ); ?>" />
                            <div class="description"><?php _e( 'Enter the label to be used for the "Signup for Newsletter" checkbox.', 'rcp-getresponse' ); ?></div>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e( 'Save Options', 'rcp-getresponse' ); ?>" />
                </p>
            </form>
            <?php
            echo '</div>';
        }


        /**
         * Add the subscription field to the registration form
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function add_fields() {
            $settings = get_option( 'rcp_getresponse_settings' );

            ob_start();
            if( ! empty( $settings['api_key'] ) && ! empty( $settings['saved_list'] ) ) {
                echo '<p>';
                echo '<input id="rcp_getresponse_signup" name="rcp_getresponse_signup" type="checkbox" checked="checked" />';
                echo '<label for="rcp_getresponse_signup">' . ( isset( $settings['signup_label'] ) && ! empty( $settings['signup_label'] ) ? $settings['signup_label'] : __( 'Signup for Newsletter', 'rcp-getresponse' ) ) . '</label>';
                echo '</p>';
            }
            echo ob_get_clean();
        }


        /**
         * Check if a user should be signed up
         *
         * @access      public
         * @since       1.0.0
         * @param       array $posted The fields posted by the submission form
         * @param       int $user_id The user ID of this user
         * @return      void
         */
        public function check_for_signup( $posted, $user_id ) {
            if( isset( $posted['rcp_getresponse_signup'] ) ) {
                if( is_user_logged_in() ) {
                    $user_data  = get_userdata( $user_id );
                    $email      = $user_data->user_email;
                } else {
                    $email      = $posted['rcp_user_email'];
                }

                rcp_getresponse_subscribe_email( $email );
                update_user_meta( $user_id, 'rcp_subscribed_to_getresponse', 'yes' );
            }
        }


        /**
         * Display a signed up notice if user has subscribed
         *
         * @access      public
         * @since       1.0.0
         * @param       int $user_id The user ID of this user
         * @return      void
         */
        public function display_signup_notice( $user_id ) {
            $signed_up = get_user_meta( $user_id, 'rcp_subscribed_to_getresponse', true );
            $signed_up = ( $signed_up ? __( 'Yes', 'rcp-getresponse' ) : __( 'No', 'rcp-getresponse' ) );

            echo '<tr class="form-field">';
            echo '<th scope="row" valign="top">' . __( 'GetResponse', 'rcp-getresponse' ) . '</th>';
            echo '<td>' . $signed_up . '</td>';
            echo '</tr>';
        }
    }
}


/**
 * The main function responsible for returning the one true RCP_GetResponse
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      RCP_GetResponse The one true RCP_GetResponse
 */
function rcp_getresponse() {
    return RCP_GetResponse::instance();
}
add_action( 'plugins_loaded', 'rcp_getresponse' );
