<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ConepatrolBotWebhookController {
    public function __construct() {
        $this->namespace = 'conepatrol/v1';
        $this->resource_name = 'webhook';
    }

    /**
	 * Register webhook route
	 */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->resource_name . '/(?P<botId>\w+)', array(
            array(
                'methods'   => 'POST',
                'callback'  => array( $this, 'process_webhook' ),
                'permission_callback' => array( $this, 'webhook_permissions_check' ),
            ),
        ) );
    }

    /**
	 * Parse chat_id from incoming message body
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return string|boolean $chat_id from request if present
	 */
    public function get_chat_id( $request ) {
        if ( ! $request[ 'message'] || ! $request[ 'message' ][ 'chat' ] ) {
            return false;
        }

        return $request[ 'message' ][ 'chat' ][ 'id' ];
    }

    /**
     *  Handle incoming Webhook Payload
     * 
     * Example JSON webhook POST payload
     * {
     *   "update_id":646911460,
     *   "message":{
     *       "message_id":9999,
     *       "from":{
     *          "id":xxx,
     *          "is_bot":false,
     *          "first_name":"blah",
     *          "username":"meh",
     *          "language_code":"en-US"
     *       },
     *      "chat":{
     *          "id":10000xxxx,
     *          "first_name":"meh",
     *          "username":"meh",
     *          "type":"private"
     *      },
     *      "date":1509641174,
     *      "text":"i <3 wapuu"
     *   }
     * }
     * 
     * * @param  WP_REST_Request $request Full details about the request.
     */
    public function process_webhook( $request ) {
        $response = 'OK';
        
        $request_body = $request->get_json_params();
        error_log( print_r( $request_body, true ) );
        $chat_id = $this->get_chat_id( $request_body );
       
        $cone_patrol_bot = ConePatrolBot::instance();
        error_log( 'chat_id ' . $chat_id );

        $cone_patrol_bot->send_nick( $request['botId'], $chat_id );
        return rest_ensure_response( 'OK' );
    }

    /**
     * Ensure the request has a valid "token" pair
	 *
	 * @return WP_Error|boolean
	 */
    public function webhook_permissions_check( $request ) {
        $cone_patrol_bot = ConePatrolBot::instance();
        if ( $cone_patrol_bot->get_bot_token( $request['botId'] ) ) {
            return true;
        }

        return false;
    }
}

