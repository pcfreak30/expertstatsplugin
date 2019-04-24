<?php


namespace Codeable\ExpertStats;


use Codeable\ExpertStats\Core\Component;

class AJAX extends Component {
	public function setup() {
		add_action( "wp_ajax_{$this->plugin->safe_slug}_import", [ $this, 'import' ] );

		return true;
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
