<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ConepatrolBotSubscribersController {
    public function __construct() {
        $this->namespace = 'conepatrol/v1';
        $this->resource_name = 'subscribers';
    }

    /**
	 * Register webhook route
	 */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->resource_name . '/(?P<inches>\w+)', array(
            array(
                'methods'   => 'GET',
                'callback'  => array( $this, 'get_subscribers' ),
                'permission_callback' => array( $this, 'subscribers_permissions_check' ),
            ),
        ) );
    }

    /**
     *  Handle subscribers request
     * * @param  WP_REST_Request $request Full details about the request.
     */
    public function get_subscribers( $request ) {
        $tag_list = array();
        $inches = (int) $request['inches'];
        // Default to 1 inch
        if ( ! is_numeric( $inches ) || $inches === 0 ) {
            $inches = 1;
        }

        for ( $i = 1; $i <= $inches; $i++ ) {
            $tag_list[] = $i . 'in';
        }

        // Get matching subscriber posts
        $posts = get_posts( array(
            'fields'       => array( 'post_name' ),
            'post_status'  => 'private',
            'tag_slug__in' => $tag_list,
            'numberposts'  => '-1'
        ) );

        // Response Data
        $data = array(
            'count' => sizeof( $posts ),
            'tags'  => $tag_list,
            'data'  => $posts,
        );
        return rest_ensure_response( $data );
    }

    /**
     * Ensure the request has a valid "token" pair
	 *
	 * @return WP_Error|boolean
	 */
    public function subscribers_permissions_check( $request ) {
        return current_user_can( 'edit_others_posts' );
    }
}

