<?php


namespace Codeable\ExpertStats\Core;


/**
 * Class Settings
 *
 * @package Codeable\ExpertStats\Core
 * @property array $fields
 */
class Settings extends \ComposePress\Settings\Abstracts\Settings {
	/**
	 * @var array
	 */
	private $fields = [
		'email'       => '',
		'password'    => '',
		'import_mode' => 'all',

	];

	/**
	 * @return bool
	 */
	public function setup() {
		register_setting( $this->plugin->safe_slug, $this->plugin->safe_slug, [
			'show_in_rest' => true,
		] );
		add_action( 'rest_dispatch_request', [ $this, 'rest_includes' ], 10, 3 );
		add_action( 'rest_request_after_callbacks', [ $this, 'rest_verify_settings' ], 10, 3 );
		add_filter( "sanitize_option_{$this->plugin->safe_slug}", [ $this, 'verify_settings' ], 10, 2 );

		return true;
	}

	public function rest_includes( $dispatch_result, \WP_REST_Request $request, $route ) {
		if ( '/wp/v2/settings' === $route && 'POST' === $request->get_method() && $request->get_param( $this->plugin->safe_slug ) ) {
			require_once ABSPATH . 'wp-admin/includes/template.php';
		}

		return $dispatch_result;
	}

	/**
	 * @param \WP_HTTP_Response $response
	 * @param array             $handler
	 * @param \WP_REST_Request  $request
	 *
	 * @return \WP_Error
	 */
	public function rest_verify_settings( $response, array $handler, \WP_REST_Request $request ) {
		if ( '/wp/v2/settings' === $request->get_route() && 'POST' === $request->get_method() && $request->get_param( $this->plugin->safe_slug ) ) {
			require_once ABSPATH . 'wp-admin/includes/template.php';
			$errors = get_settings_errors( $this->plugin->safe_slug );
			/** @var \WP_Error $wp_error */
			$wp_error = null;

			if ( ! empty( $errors ) ) {
				$wp_error = new \WP_Error( 'invalid_settings', array_shift( $errors ), [ 'status' => 400 ] );
			}
			if ( ! empty( $errors ) ) {
				foreach ( $errors as $error ) {
					$wp_error->add( 400, $error );
				}
			}

			if ( $wp_error ) {
				$response = $wp_error;
			}
		}

		return $response;
	}

	/**
	 * @param $value
	 *
	 * @return bool|\WP_Error
	 */
	public function verify_settings( $value, $option ) {
		$errors = [];
		$value  = is_array( $value ) ?: json_decode( base64_decode( $value ), true );

		remove_filter( "sanitize_option_{$this->plugin->safe_slug}", [ $this, 'verify_settings' ] );

		if ( isset( $value['email'] ) && ! filter_var( $value['email'], FILTER_VALIDATE_EMAIL ) ) {
			$errors[] = [
				"invalid_{$this->plugin->safe_slug}_email" =>
					__( 'Email input is not a valid email address', $this->plugin->safe_slug ),
			];
		}

		if ( isset( $value['email'] ) && ! isset( $value['password'] ) ) {
			$errors[] = [
				"invalid_{$this->plugin->safe_slug}_password" =>
					__( 'Password is required', $this->plugin->safe_slug ),
			];
		}

		$this->plugin->api->email    = $value['email'];
		$this->plugin->api->password = $value['password'];

		if ( is_wp_error( $login_result = $this->plugin->api->login() ) ) {
			$errors[] = [ "invalid_{$this->plugin->safe_slug}_login" => $error = $login_result->get_error_message() ];;
			$errors[] = [ "invalid_{$this->plugin->safe_slug}_login2" => $error = $login_result->get_error_message() ];;
		}

		add_filter( "sanitize_option_{$this->plugin->safe_slug}", [ $this, 'verify_settings' ], 10, 2 );

		/** @var string $error */
		if ( ! empty( $errors ) ) {
			foreach ( $errors as $error_code => $error ) {
				add_settings_error( $this->plugin->safe_slug, $error_code, $error );
			}

			$value = get_option( $option );
		}

		return $value;
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		return $this->fields;
	}
}
