<?php

namespace Codeable\ExpertStats;

use Codeable\ExpertStats\Core\Component;

/**
 * Class API
 *
 * @package Codeable\ExpertStats
 */
class API extends Component {

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
	private $auth_token = '';

	/**
	 * @return string|\WP_Error
	 */
	public function login() {
		$args       = [ 'email' => $this->email, 'password' => $this->password ];
		$url        = 'https://api.codeable.io/users/login';
		$login_call = $this->request( $url, $args );

		// credential error checking
		if ( isset( $login_call->errors ) ) {
			if ( ! empty( $login_call->errors [0]->message ) && $login_call->errors [0]->message ) {
				return new \WP_Error( 'api_error', $login_call->errors [0]->message );
			}

			return new \WP_Error( 'api_error', null );
		}

		$this->auth_token = $login_call->auth_token;

		return $this->auth_token;
	}

	/**
	 * @param        $url
	 * @param        $data
	 * @param string $method
	 * @param array  $headers
	 *
	 * @return array|mixed|object
	 */
	private function request( $url, $data, $method = 'post', $headers = [] ) {
		$func = 'wp_remote_post';
		$args = [ 'headers' => $headers ];
		if ( 'get' === $method ) {
			$func = 'wp_remote_get';
			$url  = add_query_arg( $data, $url );
		}

		if ( 'post' === $method ) {
			$args['body'] = $data;
		}
		$response = $func( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return json_decode( $response['body'] );
	}

	/**
	 *
	 */
	public function init() {
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
