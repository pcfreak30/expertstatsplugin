<?php

namespace Codeable\ExpertStats;

use Codeable\ExpertStats\Core\Component;

/**
 * Class API
 *
 * @package Codeable\ExpertStats
 * @property string $email
 * @property string $password
 */
class API extends Component {

	/**
	 *
	 */
	const PROD_ENDPOINT = 'https://api.codeable.io';
	/**
	 * @var
	 */
	private $email;

	/**
	 * @var
	 */
	private $password;

	/**
	 * @var string
	 */
	private $auth_token;
	/**
	 * @var int
	 */
	private $auth_token_timestamp;

	const TOKEN_EXPIRE = 120000;

	/**
	 * @return bool|\WP_Error
	 */
	public function login() {
		$args     = [ 'email' => $this->email, 'password' => $this->password ];
		$response = $this->request( '/users/login', $args );

		if ( $error = $this->check_error( $response ) ) {
			return $error;
		}

		$json = json_decode( $response['body'] );

		$this->auth_token           = $json->auth_token;
		$this->auth_token_timestamp = time() + self::TOKEN_EXPIRE;

		$this->plugin->settings->set( 'auth_token', $this->auth_token );
		$this->plugin->settings->set( 'auth_token_timestamp', $this->auth_token_timestamp );

		return true;
	}

	/**
	 * @param string  $url
	 * @param   array $data
	 * @param string  $method
	 * @param array   $headers
	 *
	 * @return array|mixed|object
	 */
	private function request( $endpoint, $data = [], $method = 'post', $auth = true, $headers = [] ) {
		$func = 'wp_remote_post';
		if ( $auth ) {
			$headers['Authorization'] = "Bearer {$this->auth_token}";
		}

		$args = [ 'headers' => $headers ];
		$url  = self::PROD_ENDPOINT . $endpoint;

		if ( 'get' === $method ) {
			$func = 'wp_remote_get';
			$url  = add_query_arg( $data, $url );
		}

		if ( 'post' === $method ) {
			$args['body'] = $data;
		}

		return $func( $url, $args );
	}

	public function transactions( $page = 0 ) {
		if ( $error = $this->check_error( $this->maybe_renew_auth() ) ) {
			return $error;
		};

		$response = $this->request( '/users/me/transactions', [ 'page' => $page ] );

		if ( $error = $this->check_error( $response ) ) {
			return $error;
		};

		return [
			'headers'  => $response['headers']['x-page-count'],
			'response' => json_decode( $response['body'] ),
		];
	}

	private function maybe_renew_auth() {
		if ( ! empty( $this->auth_token_timestamp ) && time() < 1000 - $this->auth_token_timestamp ) {
			return true;
		}

		if ( $error = $this->check_error( $this->renew_auth() ) ) {
			return $this->login();
		};

		return true;

	}

	private function renew_auth() {
		$response = $this->request( '/users/auth_tokens' );
		$json     = json_decode( $response['body'] );

		if ( $error = $this->check_error( $response ) ) {
			return $error;
		}

		$this->auth_token           = $json->auth_token;
		$this->auth_token_timestamp = time() + $json->auth_token_expiration;

		$this->plugin->settings->set( 'auth_token', $this->auth_token );
		$this->plugin->settings->set( 'auth_token_timestamp', $this->auth_token_timestamp );
	}

	private function check_error( $response ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response = json_decode( $response['body'] );

		if ( isset( $response->errors ) ) {
			if ( ! empty( $response->errors [0]->message ) && $response->errors [0]->message ) {
				return new \WP_Error( 'api_error', $response->errors [0]->message );
			}

			return new \WP_Error( 'api_error', null );
		}

		return false;
	}

	/**
	 *
	 */
	public function setup() {
		$this->email                = $this->plugin->settings->get( 'email' );
		$this->password             = $this->plugin->settings->get( 'password' );
		$this->auth_token_timestamp = $this->plugin->settings->get( 'auth_token_timestamp' );

		return true;
	}

	/**
	 * @param mixed $password
	 */
	public function set_password( $password ) {
		$this->password = $password;
	}

	/**
	 * @param mixed $email
	 */
	public function set_email( $email ) {
		$this->email = $email;
	}
}
