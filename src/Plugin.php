<?php

namespace Codeable\ExpertStats;


use Codeable\ExpertStats\Core\Plugin as PluginBase;
use Codeable\ExpertStats\Core\Settings;
use Codeable\ExpertStats\Core\View;
use Codeable\ExpertStats\Managers\Models;

/**
 * Class Plugin
 *
 * @package Codeable\ExpertStats
 * @property \Codeable\ExpertStats\API
 * @property \Codeable\ExpertStats\Managers\Models $models_manager
 * @property \Codeable\ExpertStats\Core\View       $view
 * @property \Codeable\ExpertStats\Core\Settings   $settings
 */
class Plugin extends PluginBase {
	/**
	 *
	 */
	const PLUGIN_SLUG = 'expertstatsplugin';

	/**
	 *
	 */
	const VERSION = '0.1.0';

	/**
	 *
	 */
	const PLUGIN_NAMESPACE = '\Codeable\ExpertStats';
	/**
	 * @var \Codeable\ExpertStats\UI\Admin
	 */
	protected $admin = '\Codeable\ExpertStats\Admin';
	/**
	 * @var \Codeable\ExpertStats\Managers\Models
	 */
	private $models_manager;
	/**
	 * @var \Codeable\ExpertStats\API
	 */
	private $api;
	/**
	 * @var \Codeable\ExpertStats\Core\View
	 */
	private $view;

	/**
	 * @var \Codeable\ExpertStats\Core\Settings
	 */
	private $settings;

	/**
	 * Plugin constructor.
	 *
	 * @param \Codeable\ExpertStats\Managers\Models $models_manager
	 * @param \Codeable\ExpertStats\API             $api
	 * @param \Codeable\ExpertStats\Core\View       $view
	 *
	 * @param \Codeable\ExpertStats\Core\Settings   $settings
	 *
	 * @throws \ComposePress\Core\Exception\ContainerInvalid
	 * @throws \ComposePress\Core\Exception\ContainerNotExists
	 */
	public function __construct( Models $models_manager, API $api, View $view, Settings $settings ) {
		$this->models_manager = $models_manager;
		$this->api            = $api;
		$this->view           = $view;
		$this->settings       = $settings;
		parent::__construct();
	}

	/**
	 * @return bool
	 */
	public function setup() {
		load_plugin_textdomain( 'wpcable', false, dirname( $this->plugin_file ) . '/languages/' );

		return true;
	}

	/**
	 * @return \Codeable\ExpertStats\Managers\Models
	 */
	public function get_models_manager() {
		return $this->models_manager;
	}

	/**
	 * @return \Codeable\ExpertStats\API
	 */
	public function get_api() {
		return $this->api;
	}

	/**
	 * @param bool $network_wide
	 *
	 * @return void
	 */
	public function activate( $network_wide ) {
		// TODO: Implement activate() method.
	}

	/**
	 * @param bool $network_wide
	 *
	 * @return  void
	 */
	public function deactivate( $network_wide ) {
		// TODO: Implement deactivate() method.
	}

	/**
	 * @return void
	 */
	public function uninstall() {
		// TODO: Implement uninstall() method.
	}

	/**
	 * @return \Codeable\ExpertStats\UI\Admin
	 */
	public function get_admin() {
		return $this->admin;
	}


	/**
	 * @return \Codeable\ExpertStats\Core\View
	 */
	public function get_view() {
		return $this->view;
	}

	/**
	 * @return mixed
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * @return bool
	 * @throws \Exception
	 */
	protected function load_components() {
		if ( is_admin() ) {
			$this->load( 'admin' );
		}

		return true;
	}
}
