<?php
/**
 * Plugin Name: Conepatrol Bot
 */

 class ConepatrolBot {
    protected static $instance = null;
    
    // @TODO make interface to set this

    /**
     * plugin anticipates the token to be persisted in associative array
     * 
     * array(
     *  'bot_id' => 'TELEGRAM_BOT_TOKEN',
     * );
     * 
     * bot_id is configured as part of the webhook for telegram, and used
     * in validation of the request
     */
    protected static $bot_tokens = array();

    function __construct() {
        require_once( plugin_dir_path( __FILE__ ) . '/libraries/action-scheduler/action-scheduler.php' );
        include_once( dirname( __FILE__ ) . '/inc/conepatrol-bot-webhook-controller.php' );
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
        add_action( 'conepatrol_process_telegram_repsone', array( $this, 'send_message' ), 10, 3 );

        // rando
        add_action( 'admin_init', array( $this, 'do_random_stuff' ) );
    }

    /**
	 * Regiser REST routes
	 */
    public function register_routes() {
        $controller_instance = new ConepatrolBotWebhookController;
        $controller_instance->register_routes();
    }

    /**
     * you know for random things and stuff
     */
    public function do_random_stuff() {
        if ( isset( $_GET[ 'bbq' ] ) ) {
            $sekretz = array(
                'bot_id' => 'telegram-bot-token'
            );
            //update_option( 'conepatrol_telegram_bot_key', $sekretz );
            //$this->send_nick( 'note_id', '' );
        }
    }

    /**
	 * Get Telegram token from bot_id
	 *
	 * @param  string $bot_id
	 * @return string|boolean associated telegram token
	 */
    public function get_bot_token( $bot_id ) {
        if ( ! $bot_id || empty( $bot_id ) || ! array_key_exists( $bot_id, self::$bot_tokens ) ) {
            return false;
        }

        return self::$bot_tokens[ $bot_id ];
    }

     /**
	 * Send a message to a telegram chat from a particular bot
	 *
	 * @param  string $bot_id  Id of the bot
	 * @param  string $chat_id the chat identifier from telegram
	 * @param  string $message message to send to the chat
	 */
    public function send_message( $bot_id, $chat_id, $message ) {
        $bot_token = $this->get_bot_token( $bot_id );
        if ( ! $bot_token ) {
            error_log( 'No bot token stored, bye bye' );
            return;
        }

        if ( empty( $chat_id ) || empty( $message ) ) {
            error_log( 'No sending message, missing chat_id or message' );
            return;
        }

        error_log( 'Sending message to: ' . $chat_id . ' message: ' . $message );
        $telegram_url = 'https://api.telegram.org/bot' . $bot_token . '/sendMessage?chat_id=' . $chat_id;
        $telegram_url = $telegram_url . '&text=' . urlencode( $message );
        $result = wp_remote_request( $telegram_url );

        error_log( 'Telegram Message Result:');
        error_log( print_r( $result, true ) );
    }

    /**
	 * Send some nick cage from a bot
     * to the given chat_id
	 *
	 * @param  string $bot_id  Id of the bot
	 * @param  string $chat_id the chat identifier from telegram
	 */
    public function send_nick( $bot_id, $chat_id ) {
        $bot_token = $this->get_bot_token( $bot_id );
        if ( ! $bot_token ) {
            error_log( 'No bot token stored, bye bye' );
            return;
        }

        $nick = array(
            'https://media2.giphy.com/media/glvyCVWYJ21fq/giphy.webp',
            'https://media3.giphy.com/media/10uct1aSFT7QiY/giphy.webp',
            'https://media0.giphy.com/media/3oEdv2NNoFaujmHV84/giphy.webp',
            'https://media2.giphy.com/media/ErToLK2uuAAF2/200.webp',
            'https://media1.giphy.com/media/bn0zlGb4LOyo8/giphy.webp',
        );
        $random_number = rand( 0, 4 );
        as_schedule_single_action( time(), 'conepatrol_process_telegram_repsone', array( $bot_id, $chat_id, $nick[ $random_number ] ) );
    }
    
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();

            // Tokens are stored in an options array, default to an empty array
            self::$bot_tokens = get_option( 'conepatrol_telegram_bot_key', array() );
        }
        return self::$instance;
    }

 }

 ConePatrolBot::instance();
