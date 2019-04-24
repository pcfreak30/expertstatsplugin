<?php


namespace Codeable\ExpertStats;


use Codeable\ExpertStats\Core\Component;

/**
 * Class Admin
 *
 * @package Codeable\ExpertStats
 */
class Admin extends Component {
	/**
	 * @var \Codeable\ExpertStats\Stats
	 */
	private $stats;


	/**
	 * @var \Codeable\ExpertStats\AJAX
	 */
	private $ajax;

	/**
	 * Admin constructor.
	 *
	 * @param \Codeable\ExpertStats\Stats $stats
	 * @param \Codeable\ExpertStats\AJAX  $ajax
	 */
	public function __construct( Stats $stats, AJAX $ajax ) {
		$this->stats = $stats;
		$this->ajax  = $ajax;
	}

	/**
	 * @return bool
	 */
	public function setup() {
		add_action( 'admin_menu', [ $this, 'register_pages' ] );

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		//add_action( "pre_update_option_{$this->plugin->safe_slug}", [ $this, 'save_settings' ], 10, 2 );


		return true;
	}

	/**
	 * @param $value
	 * @param $old_value
	 *
	 * @return array
	 */
	public function save_settings( $value, $old_value ) {
		/** @noinspection SuspiciousAssignmentsInspection */
		$value = $old_value;
		if ( empty( $value ) ) {
			$value = [];
		}

		foreach ( $this->settings as $setting ) {
			if ( isset( $_POST[ $setting ] ) ) {
				$value[ $setting ] = sanitize_text_field( $_POST[ $setting ] );
			}
		}

		remove_action( "pre_update_option_{$this->plugin->safe_slug}", [ $this, 'save_settings' ], 10 );

		if ( ! $this->verify_settings( $value ) ) {
			return $old_value;
		}

		$value = $this->plugin->settings->undotify( $value );

		add_action( "pre_update_option_{$this->plugin->safe_slug}", [ $this, 'save_settings' ], 10, 2 );

		return $value;
	}

	/**
	 * @param $value
	 *
	 * @return bool|\WP_Error
	 */
	public function verify_settings( $value, $option ) {
		$errors = [];
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
		}

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
	 * @param $hook
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'toplevel_page_codeable_transaction_stats' === $hook ) {
			wp_enqueue_style( 'gridcss', $this->plugin->get_asset_url( 'assets/css/grid12.css' ) );
			wp_enqueue_style( 'wpcablecss', $this->plugin->get_asset_url( 'assets/css/wpcable.css' ) );
			wp_enqueue_style( 'ratycss', $this->plugin->get_asset_url( 'assets/css/jquery.raty.css' ) );
			wp_enqueue_style( 'datatablecss', $this->plugin->get_asset_url( 'assets/css/jquery.dataTables.min.css' ) );

			wp_enqueue_script(
				'highchartsjs',
				plugins_url( 'assets/js/highcharts.js' ),
				array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' ),
				time(),
				true
			);
			wp_enqueue_script(
				'highcharts_export_js',
				plugins_url( 'assets/js/exporting.js' ),
				array( 'jquery', 'highchartsjs' ),
				time(),
				true
			);
			wp_enqueue_script(
				'highcharts_offline_export_js',
				plugins_url( 'assets/js/offline-exporting.js' ),
				array( 'jquery', 'highcharts_export_js' ),
				time(),
				true
			);
			wp_enqueue_style( 'jquery-ui-datepicker' );

			wp_enqueue_script( 'highcharts3djs', $this->plugin->get_asset_url( 'assets/js/highcharts-3d.js' ), array( 'highchartsjs' ) );
			wp_enqueue_script( 'ratyjs', $this->plugin->get_asset_url( 'assets/js/jquery.raty.js' ) );
			wp_enqueue_script( 'datatablesjs', $this->plugin->get_asset_url( 'assets/js/jquery.dataTables.min.js' ) );
			wp_enqueue_script( 'matchheightjs', $this->plugin->get_asset_url( 'assets/js/jquery.matchHeight-min.js' ) );
			wp_enqueue_script( 'wpcablejs', $this->plugin->get_asset_url( 'assets/js/wpcable.js' ) );
		}
		if ( 'codeable-stats_page_codeable_settings' === $hook ) {
			wp_enqueue_script( 'wp-element' );
			wp_enqueue_script( 'wp-api-fetch' );
			wp_enqueue_script( 'wp-dom-ready' );
			wp_enqueue_style( $this->plugin->safe_slug . '-settings', $this->plugin->get_asset_url( 'assets/css/settings.css' ) );
			wp_enqueue_style( 'jquery-ui-progressbar' );
			wp_enqueue_script( 'jquery-ui-progressbar' );
			wp_localize_script( 'jquery-core', 'import_config', [ 'ajax_action' => "{$this->plugin->safe_slug}_import" ] );
			$ui = wp_scripts()->query( 'jquery-ui-core' );
			wp_enqueue_style( 'jquery-ui-smoothness', "//ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css", false, null );

			wp_enqueue_script( $this->plugin->safe_slug . '-settings', $this->plugin->get_asset_url( 'assets/js/dist/settings.js' ), [
				'wp-element',
				'wp-api-fetch',
				'wp-dom-ready',
			] );
			$settings = $this->plugin->settings->batch_get( array_keys( $this->plugin->settings->fields ), array_values( $this->plugin->settings->fields ) );
			wp_localize_script( $this->plugin->safe_slug . '-settings', 'codeable_stats_settings', $settings );
			wp_set_script_translations( $this->plugin->safe_slug . '-settings', $this->plugin->safe_slug );
		}
	}

	/**
	 *
	 */
	public function register_pages() {
		add_menu_page(
			__( 'Codeable Stats', $this->plugin->safe_slug ),
			__( 'Codeable Stats', $this->plugin->safe_slug ),
			'manage_options',
			'codeable_transactions_stats',
			[ $this, 'stats_page' ],
			$this->plugin->get_asset_url( 'assets/images/codeable_16x16.png' ),
			85
		);
		add_submenu_page( 'codeable_transactions_stats', 'Settings', 'Settings', 'manage_options', 'codeable_settings', [
			$this,
			'settings_page',
		] );
		add_submenu_page( 'codeable_transactions_stats', 'Estimate', 'Estimate', 'manage_options', 'codeable_estimate', [
			$this,
			'estimate_page',
		] );
	}

	/**
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\Plugin
	 * @throws \ComposePress\Models\Exceptions\InvalidData
	 * @throws \ComposePress\Models\Exceptions\QueryException
	 * @throws \ReflectionException
	 * @throws \Exception
	 */
	public function stats_page() {
		add_thickbox();
		$first_task = $this->stats->get_first_task();
		$last_task  = $this->stats->get_last_task();

		$first_day   = date( 'd', strtotime( $first_task['dateadded'] ) );
		$first_month = date( 'm', strtotime( $first_task['dateadded'] ) );
		$first_year  = date( 'Y', strtotime( $first_task['dateadded'] ) );
		$last_day    = date( 'd', strtotime( $last_task['dateadded'] ) );
		$last_month  = date( 'm', strtotime( $last_task['dateadded'] ) );
		$last_year   = date( 'Y', strtotime( $last_task['dateadded'] ) );

		if ( ! isset( $_GET['date_from'] ) ) {
			$_GET['date_from'] = $first_year . '-' . $first_month;
			$from_day          = $first_day;
			$from_month        = $first_month;
			$from_year         = $first_year;
		} else {
			$from_day   = '01';
			$from_month = date( 'm', strtotime( $_GET['date_from'] . '-01' ) );
			$from_year  = date( 'Y', strtotime( $_GET['date_from'] . '-01' ) );
		}

		if ( ! isset( $_GET['date_to'] ) ) {
			$_GET['date_to'] = $last_year . '-' . $last_month;
			$to_day          = $last_day;
			$to_month        = $last_month;
			$to_year         = $last_year;
		} else {
			$to_day   = date( 't', strtotime( $_GET['date_to'] . '-01' ) );
			$to_month = date( 'm', strtotime( $_GET['date_to'] . '-01' ) );
			$to_year  = date( 'Y', strtotime( $_GET['date_to'] . '-01' ) );
		}

		$is_compare = '';

		if ( isset( $_GET['compare_date_from'] ) ) {
			$compare_from_day   = '01';
			$compare_from_month = date( 'm', strtotime( $_GET['compare_date_from'] . '-01' ) );
			$compare_from_year  = date( 'Y', strtotime( $_GET['compare_date_from'] . '-01' ) );
			$is_compare         = 'is_compare';
		}

		if ( isset( $_GET['compare_date_to'] ) ) {
			$compare_to_day   = date( 't', strtotime( $_GET['compare_date_to'] . '-01' ) );
			$compare_to_month = date( 'm', strtotime( $_GET['compare_date_to'] . '-01' ) );
			$compare_to_year  = date( 'Y', strtotime( $_GET['compare_date_to'] . '-01' ) );
			$is_compare       = 'is_compare';
		}


		if ( ! isset( $_GET['chart_display_method'] ) ) {
			$_GET['chart_display_method'] = 'months';
			$chart_display_method         = 'months';
		} else {
			$chart_display_method = $_GET['chart_display_method'];
		}
		$all_stats = $this->stats->get_all_stats( $from_day, $from_month, $from_year, $to_day, $to_month, $to_year, $chart_display_method );

		$compare_stats = [];

		if ( $is_compare ) {
			$compare_stats = $this->stats->get_all_stats( $compare_from_day, $compare_from_month, $compare_from_year, $compare_to_day, $compare_to_month, $compare_to_year, $chart_display_method );
			$compare_stats = array_combine(
				array_map( function ( $key ) {
					return 'compare_' . $key;
				}, array_keys( $compare_stats ) ),
				$compare_stats
			);
		}

		$all_averages = $this->stats->get_dates_average( $first_day, $first_month, $first_year, $last_day, $last_month, $last_year );

		$clients_data = $this->stats->get_clients();

		$this->plugin->view->render( 'stats', array_merge( $all_stats, $compare_stats, [
			'clients_data' => $clients_data,
			'first_task'   => $first_task,
			'last_task'    => $last_task,
			'is_compare'   => $is_compare,
			'all_averages' => $all_averages,
		] ) );

	}

	/**
	 * @throws \ComposePress\Core\Exception\ComponentInitFailure
	 * @throws \ComposePress\Core\Exception\Plugin
	 * @throws \ComposePress\Models\Exceptions\InvalidData
	 * @throws \ReflectionException
	 * @throws \Exception
	 */
	public function settings_page() {

		if ( isset( $_GET['flushdata'] ) ) {
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

		$this->plugin->view->render( 'settings', [
			'import_mode' => $this->plugin->settings->get( 'import_mode', 'all' ),
		] );
	}

	/**
	 *
	 */
	public
	function estimate_page() {

	}

	/**
	 * @return \Codeable\ExpertStats\Stats
	 */
	public
	function get_stats() {
		return $this->stats;
	}

	/**
	 * @return \Codeable\ExpertStats\AJAX
	 */
	public function get_ajax() {
		return $this->ajax;
	}
}
