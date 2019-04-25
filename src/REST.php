<?php


namespace Codeable\ExpertStats;


use Codeable\ExpertStats\Core\Component;

class REST extends Component {
	public function setup() {
		add_action( 'rest_api_init', [ $this, 'setup_rest' ] );

		return true;
	}

	public function setup_rest() {
		register_rest_route( "{$this->plugin->safe_slug}", '/delete_data', [
			'methods'             => \WP_REST_Server::EDITABLE,
			'callback'            => [ $this, 'delete_data' ],
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
		] );
	}

	/**
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\Plugin
	 * @throws \ComposePress\Models\Exceptions\InvalidData
	 * @throws \ReflectionException
	 * @throws \Exception
	 */
	public function delete_data() {
		$tables = array(
			__( 'Transactions', $this->plugin->safe_slug ) => $this->plugin->models_manager->transaction,
			__( 'Clients', $this->plugin->safe_slug )      => $this->plugin->models_manager->client,
			__( 'Amounts', $this->plugin->safe_slug )      => $this->plugin->models_manager->amount,
		);

		/** @var \Codeable\ExpertStats\Core\Model $model */
		foreach ( $tables as $db_label => $model ) {
			$model->sql( 'TRUNCATE {table}' );
			if ( empty( $this->wpdb->last_error ) ) {
				$message = __( 'table truncated!', $this->plugin->safe_slug );
				$type    = 'updated';
			} else {
				$message = __( 'table could not be truncated!', $this->plugin->safe_slug );
				$type    = 'notice';
			}
			$this->plugin->view->render( '_partial/db_notice', [
				'db_label' => $db_label,
				'db_table' => $model->table->full_name,
				'message'  => $message,
				'type'     => $type,
			] );
		}
	}

	/**
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\Plugin
	 * @throws \ComposePress\Models\Exceptions\InvalidData
	 * @throws \ReflectionException
	 */
	public function import() {
		if ( ! check_ajax_referer( "{$this->plugin->safe_slug}_import", false, false ) ) {
			wp_send_json( [ 'done' => true ] );
		}
		if ( isset( $_POST['offset'] ) ) {
			$offset     = (int) $_POST['offset'];
			$new_offset = $this->plugin->import->process_transactions( $offset, $this->plugin->settings->get( 'import_batch_size', 20 ) );
			if ( null === $new_offset || true === $offset ) {
				wp_send_json( [ 'done' => true ] );
			}
			if ( is_wp_error( $new_offset ) ) {
				/** @var \WP_Error $new_offset */
				wp_send_json( [ 'error' => $new_offset->get_error_message() ] );
			}
			if ( is_array( $new_offset ) ) {
				wp_send_json( $new_offset );
			}
		}
	}
}
