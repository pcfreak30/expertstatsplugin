<?php

namespace Codeable\ExpertStats\Managers;


use Codeable\ExpertStats\Core\Manager;

/**
 * Class Models
 *
 * @package Codeable\ExpertStats\Managers
 * @property \Codeable\ExpertStats\Models\Amount      $amount
 * @property \Codeable\ExpertStats\Models\Client      $client
 * @property \Codeable\ExpertStats\Models\Transaction $transaction
 */
class Models extends Manager {
	const MODULE_NAMESPACE = '\Codeable\ExpertStats\Models';

	protected $modules = [
		'Amount',
		'Client',
		'Transaction',
	];
}
