<?php

namespace Codeable\ExpertStats\Models;


use Codeable\ExpertStats\Core\Model;

class Amount extends Model {
	const NAME = 'codeable_amounts';

	/**
	 * @param $mode
	 *
	 * @return mixed
	 */
	public function get_schema( $mode ) {
		return [
			'fields'      => [
				'task_id'               => [
					'type'   => 'int',
					'length' => 11,
				],
				'client_id'             => [
					'type'   => 'int',
					'length' => 11,
				],
				'credit_revenue_id'     => [
					'type'    => 'int',
					'length'  => 11,
					'is_null' => true,
				],
				'credit_revenue_amount' => [
					'type'    => 'int',
					'length'  => 11,
					'is_null' => true,
				],
				'credit_fee_id'         => [
					'type'    => 'int',
					'length'  => 11,
					'is_null' => true,
				],
				'credit_fee_amount'     => [
					'type'    => 'int',
					'length'  => 11,
					'is_null' => true,
				],
				'credit_user_id'        => [
					'type'    => 'int',
					'length'  => 11,
					'is_null' => true,
				],
				'credit_user_amount'    => [
					'type'    => 'int',
					'length'  => 11,
					'is_null' => true,
				],
				'debit_cost_id'         => [
					'type'    => 'int',
					'length'  => 11,
					'is_null' => true,
				],
				'debit_cost_amount'     => [
					'type'    => 'int',
					'length'  => 11,
					'is_null' => true,
				],
				'debit_user_id'         => [
					'type'    => 'int',
					'length'  => 11,
					'is_null' => true,
				],
				'debit_user_amount'     => [
					'type'    => 'int',
					'length'  => 11,
					'is_null' => true,
				],
			],
			'primary_key' => 'task_id',
			'keys'        => [
				'client_id' => [ 'client_id' ],
			],

		];
	}
}
