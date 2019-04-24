<?php


namespace Codeable\ExpertStats;


use Codeable\ExpertStats\Core\Component;

class Import extends Component {

	/**
	 * @param int $offset
	 * @param int $item_limit
	 *
	 * @return array|bool|\WP_Error
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\Plugin
	 * @throws \ComposePress\Models\Exceptions\InvalidData
	 * @throws \ReflectionException
	 */
	public function process_transactions( $offset = 0, $item_limit = 0 ) {
		$processed = 0;

		$response = $this->plugin->api->transactions( $offset );
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$total_items = $response['headers']['x-total-count'];
		$per_page    = $response['headers']['x-records-per-page'];
		$page        = $offset / $per_page;

		if ( $offset > 0 ) {
			$response = $this->plugin->api->transactions( $offset );

		}

		$initial     = true;
		$import_mode = $this->plugin->settings->get( 'import_mode' );

		$response = $response['response'];
		if ( empty( $response['transactions'] ) ) {
			return true;
		}

		do {
			$processed += $per_page;
			if ( ! $initial ) {
				$response = $this->plugin->api->transactions( $page );
				$response = $response['response'];
				if ( empty( $response['transactions'] ) ) {
					$response['transactions'] = array_filter( $response['transactions'], [
						$this,
						'process_transaction',
					] );
					foreach ( $response['transactions'] as $transaction ) {
						if ( 'stop_first' === $import_mode && $this->process_transaction( $transaction ) ) {
							return true;
						}
					}
					$clients = array_column( $response['transactions'], 'task_client' );
					array_walk( $clients, [ $this, 'process_transaction' ] );
					array_walk( $response['transactions'], [ $this, 'process_amount' ] );
				}
			}
			$initial = false;
		} while ( ( ( $processed < $item_limit ) || ( $processed <= $item_limit && ! $item_limit ) ) && $offset <= $total_items );

		if ( $offset < $total_items ) {
			return [ 'offset' => $offset, 'total' => $total_items ];
		}
	}

	/**
	 * @param $transaction
	 *
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\Plugin
	 * @throws \ComposePress\Models\Exceptions\InvalidData
	 * @throws \ReflectionException
	 */
	private function process_transaction( $transaction ) {
		$transaction_model = $this->plugin->models_manager->transaction;
		if ( $transaction_model->find( $transaction['id'] ) ) {
			return false;
		}

		$new_transaction = array(
			'id'             => $transaction['id'],
			'description'    => $transaction['description'],
			'dateadded'      => date( 'Y-m-d H:i:s', $transaction['timestamp'] ),
			'fee_percentage' => $transaction['fee_percentage'],
			'fee_amount'     => $transaction['fee_amount'],
			'task_type'      => $transaction['task']['kind'],
			'task_id'        => $transaction['task']['id'],
			'task_title'     => $transaction['task']['title'],
			'parent_task_id' => ( $transaction['task']['parent_task_id'] > 0 ? $transaction['task']['parent_task_id'] : 0 ),
			'preferred'      => $transaction['task']['current_user_is_preferred_contractor'],
			'client_id'      => $transaction['task_client']['id'],
		);
		$transaction_model->insert( $new_transaction, [
			'%d',
			'%d',
			'%s',
			'%f',
			'%f',
			'%s',
			'%d',
			'%s',
			'%d',
			'%d',
			'%d',
		] );

		return true;
	}

	/**
	 * @param $client
	 *
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\Plugin
	 * @throws \ComposePress\Models\Exceptions\InvalidData
	 * @throws \ReflectionException
	 */
	private function process_client( $client ) {
		$client_model = $this->plugin->models_manager->client;
		if ( $client_model->find( $client_model['id'] ) ) {
			return;
		}

		$new_client = array(
			'client_id'       => $client['id'],
			'full_name'       => $client['full_name'],
			'role'            => $client['role'],
			'last_sign_in_at' => date( 'Y-m-d H:i:s', strtotime( $client['last_sign_in_at'] ) ),
			'pro'             => $client['pro'],
			'timezone_offset' => $client['timezone_offset'],
			'tiny'            => $client['avatar']['tiny_url'],
			'small'           => $client['avatar']['small_url'],
			'medium'          => $client['avatar']['medium_url'],
			'large'           => $client['avatar']['large_url'],
		);

		$client_model->insert( $new_client, [
			'%d',
			'%s',
			'%s',
			'%s',
			'%d',
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
		] );
	}

	/**
	 * @param $amount
	 *
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\Plugin
	 * @throws \ComposePress\Models\Exceptions\InvalidData
	 * @throws \ReflectionException
	 */
	private function process_amount( $amount ) {
		$amount_model = $this->plugin->models_manager->amount;
		if ( $amount_model->find( $amount_model['id'] ) ) {
			return;
		}

		$new_amount = array(
			'task_id'               => $amount['task']['id'],
			'client_id'             => $amount['task_client']['id'],
			'credit_revenue_id'     => $amount['credit_amounts'][0]['id'],
			'credit_revenue_amount' => $amount['credit_amounts'][0]['amount'],
			'credit_fee_id'         => $amount['credit_amounts'][1]['id'],
			'credit_fee_amount'     => $amount['credit_amounts'][1]['amount'],
			'credit_user_id'        => $amount['credit_amounts'][2]['id'],
			'credit_user_amount'    => $amount['credit_amounts'][2]['amount'],
			'debit_cost_id'         => $amount['debit_amounts'][0]['id'],
			'debit_cost_amount'     => $amount['debit_amounts'][0]['amount'],
			'debit_user_id'         => $amount['debit_amounts'][1]['id'],
			'debit_user_amount'     => $amount['debit_amounts'][1]['amount'],
		);

		$amount_model->insert( $new_amount, [
			'%d',
			'%d',
			'%d',
			'%f',
			'%d',
			'%f',
			'%d',
			'%f',
			'%d',
			'%f',
			'%d',
			'%f',
		] );
	}
}
