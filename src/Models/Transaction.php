<?php

namespace Codeable\ExpertStats\Models;


use Codeable\ExpertStats\Core\Model;

class Transaction extends Model {
	const NAME = 'codeable_transactions';

	/**
	 * @param $mode
	 *
	 * @return mixed
	 */
	public function get_schema( $mode ) {
		return [
			'fields'      => [
				'id'             => [
					'type'   => 'int',
					'length' => 11,
				],
				'description'    => [
					'type'   => 'varchar',
					'length' => 128,
				],
				'dateadded'      => [
					'type' => 'datetime',
				],
				'fee_percentage' => [
					'type'    => 'decimal',
					'length'  => [
						'digits'   => 10,
						'decimals' => 0,
					],
					'is_null' => true,
				],
				'fee_amount'     => [
					'type'    => 'decimal',
					'length'  => [
						'digits'   => 10,
						'decimals' => 0,
					],
					'is_null' => true,
				],
				'task_type'      => [
					'type'          => 'varchar',
					'length'        => 128,
					'is_null'       => true,
					'character_set' => 'utf8',
				],
				'task_id'        => [
					'type'    => 'int',
					'length'  => 11,
					'is_null' => true,
				],
				'task_title'     => [
					'type'    => 'text',
					'is_null' => true,
				],
				'parent_task_id' => [
					'type'    => 'int',
					'length'  => 11,
					'is_null' => true,
				],
				'preferred'      => [
					'type'    => 'int',
					'length'  => 4,
					'is_null' => true,
				],
				'client_id'      => [
					'type'    => 'int',
					'length'  => 4,
					'is_null' => true,
				],
			],
			'primary_key' => 'id',
		];
	}
}
