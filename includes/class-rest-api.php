<?php

/**
 * This is a partial implementation of the REST API version 2 and may be subject to change.
 *
 *
 * todo: cover all functionality to provide complete headless access.
 *
 * @beta
 *
 * @since 1.4.3
 *
 * @copyright   Copyright (c) 2015-2017, Steven Henty S.L.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 *
 * Class Gravity_Flow_REST_API
 */
class Gravity_Flow_REST_API {

	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'action_rest_api_init' ) );
	}

	public function action_rest_api_init() {
		register_rest_route( 'gf/v2', '/entries/(?P<id>\d+)/workflow/(?P<base>[\S]+)', array(
			'methods' => 'POST',
			'callback' => array( $this, 'handle_rest_request' ),
		) );
	}

	public function handle_rest_request( $request ) {
		$entry_id = $request['id'];

		if ( empty( $entry_id ) ) {
			return new WP_Error( 'entry_missing', __( 'Entry ID missing', 'gravityflow', array( 'status' => 404 ) ) );
		}

		$rest_base = $request['base'];

		if ( empty( $rest_base ) ) {
			return new WP_Error( 'base_missing', __( 'Workflow base missing', 'gravityflow', array( 'status' => 404 ) ) );
		}

		$entry = GFAPI::get_entry( $entry_id );

		if ( empty( $entry_id ) ) {
			return new WP_Error( 'not_found', __( 'Entry not found', 'gravityflow', array( 'status' => 404 ) ) );
		}

		$api = new Gravity_Flow_API( $entry['form_id'] );

		$step = $api->get_current_step( $entry );

		if ( empty( $step ) ) {
			return new WP_Error( 'not_found', __( 'Entry not found', 'gravityflow', array( 'status' => 404 ) ) );
		}

		if ( $step->get_rest_base() != $rest_base ) {
			return new WP_Error( 'base_incorrect', __( 'The entry is not on the expected step.', 'gravityflow' ) );
		}

		$response = $step->handle_rest_request( $request );

		$api->process_workflow( $entry_id );

		return $response;
	}
}

new Gravity_Flow_REST_API();