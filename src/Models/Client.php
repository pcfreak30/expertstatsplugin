<?php

namespace Codeable\ExpertStats\Models;

use Codeable\ExpertStats\Core\Model;

class Client extends Model {
	const NAME = 'codeable_clients';

	/**
	 * @param $mode
	 *
	 * @return mixed
	 */
	public function get_schema( $mode ) {
		return [
			'fields'      => [
				'client_id'       => [
					'type'   => 'int',
					'length' => 11,
				],
				'full_name'       => [
					'type'   => 'varchar',
					'length' => 255,
				],
				'role'            => [
					'type'    => 'varchar',
					'length'  => 255,
					'is_null' => true,
				],
				'last_sign_in_at' => [
					'type'    => 'datetime',
					'is_null' => true,
				],
				'pro'             => [
					'type'    => 'int',
					'length'  => 11,
					'is_null' => true,
				],
				'timezone_offset' => [
					'type'    => 'int',
					'length'  => 11,
					'is_null' => true,
				],
				'tiny'            => [
					'type'    => 'varchar',
					'length'  => 255,
					'is_null' => true,
				],
				'small'           => [
					'type'    => 'varchar',
					'length'  => 255,
					'is_null' => true,
				],
				'medium'          => [
					'type'    => 'varchar',
					'length'  => 255,
					'is_null' => true,
				],
				'large'           => [
					'type'    => 'varchar',
					'length'  => 255,
					'is_null' => true,
				],

			],
			'primary_key' => 'client_id',
		];
	}

	public function get( $from_month = '', $from_year = '', $to_month = '', $to_year = '' ) {

	}
}
