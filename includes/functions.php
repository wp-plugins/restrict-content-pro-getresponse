<?php
/**
 * Helper functions
 *
 * @package     RCP\GetResponse\Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Get GetResponse subscription lists
 *
 * @since       1.0.0
 * @return      array The list of available campaigns
 */
function rcp_getresponse_get_lists() {
    $settings   = get_option( 'rcp_getresponse_settings' );
    $lists      = false;

    if( ! empty( $settings['api_key'] ) ) {
        $key    = trim( $settings['api_key'] );

        if( ! class_exists( 'jsonRPCClient' ) ) {
            require_once RCP_GETRESPONSE_DIR . 'includes/libraries/jsonRPCClient.php';
        }

        try {
            $api    = new jsonRPCClient( 'http://api2.getresponse.com' );
            $return = $api->get_campaigns( $key );
        } catch( Exception $e ) {
            $return = false;
        }

        if( is_array( $return ) ) {
            foreach( $return as $campaign_id => $campaign_info ) {
                $lists[$campaign_id] = $campaign_info['name'];
            }
        }
    }

    return $lists;
}


/**
 * Subscribes an email to GetResponse
 *
 * @since       1.0.0
 * @param       string $email The email address to subscribe
 * @return      bool True if added successfully, false otherwise
 */
function rcp_getresponse_subscribe_email( $email = '' ) {
    $settings   = get_option( 'rcp_getresponse_settings' );
    $return     = false;

    if( ! empty( $settings['api_key'] ) && ! empty( $settings['saved_list'] ) ) {
        $key    = trim( $settings['api_key'] );

        if( ! class_exists( 'jsonRPCClient' ) ) {
            require_once RCP_GETRESPONSE_DIR . 'includes/libraries/jsonRPCClient.php';
        }

        $fname = isset( $_POST['rcp_user_first'] ) ? sanitize_text_field( $_POST['rcp_user_first'] ) : '';
        $lname = isset( $_POST['rcp_user_last'] ) ? sanitize_text_field( $_POST['rcp_user_last'] ) : '';
        $name  = $fname . ' ' . $lname;

        try {
            $api = new jsonRPCClient( 'http://api2.getresponse.com' );

            $params = array(
                'campaign'  => $settings['saved_list'],
                'name'      => $name,
                'email'     => $email,
                'ip'        => $_SERVER['REMOTE_ADDR'],
                'cycle_day' => 0
            );

            $return = $api->add_contact( $key, $params );
        } catch( Exception $e ) {
            $return = false;
        }
    }

    if( $return === false ) {
        return false;
    } else {
        return true;
    }
}
