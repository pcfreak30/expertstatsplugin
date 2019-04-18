<?php

/*
 * Plugin Name: Codeable Expert Stats
 * Plugin URI: https://github.com/codeablehq/blackbook/wiki/Expert-Stats-Plugin
 * Description: Get your Codeable data
 * Version: 0.1.0
 * Author: Derrick Hammer
 * Contributors: Spyros Vlachopoulos, Panagiotis Synetos, John Leskas, Justin Frydman, Jonathan Bossenger, Rob Scott
 * Author URI: https://www.derrickhammer.com
 * License: GPL3
 * GitHub Plugin URI: https://github.com/codeablehq/expertstatsplugin
*/


use ComposePress\Dice\Dice;


/**
 * Singleton instance function. We will not use a global at all as that defeats the purpose of a singleton and is a bad design overall
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @return \Codeable\ExpertStats\Plugin
 */
function expertstatsplugin() {
	return expertstatsplugin_container()->create( '\Codeable\ExpertStats\Plugin' );
}

/**
 * This container singleton enables you to setup unit testing by passing an environment filw to map classes in Dice
 *
 * @param string $env
 *
 * @return \ComposePress\Dice\Dice
 */
function expertstatsplugin_container( $env = 'prod' ) {
	static $container;
	if ( empty( $container ) ) {
		$container = new Dice();
		include __DIR__ . "/config_{$env}.php";
	}

	return $container;
}

/**
 * Init function shortcut
 */
function expertstatsplugin_init() {
	expertstatsplugin()->init();
}

/**
 * Activate function shortcut
 */
function expertstatsplugin_activate( $network_wide ) {
	register_uninstall_hook( __FILE__, 'expertstatsplugin_uninstall' );
	expertstatsplugin()->init();
	expertstatsplugin()->activate( $network_wide );
}

/**
 * Deactivate function shortcut
 */
function expertstatsplugin_deactivate( $network_wide ) {
	expertstatsplugin()->deactivate( $network_wide );
}

/**
 * Uninstall function shortcut
 */
function expertstatsplugin_uninstall() {
	expertstatsplugin()->uninstall();
}

/**
 * Error for older php
 */
function expertstatsplugin_php_upgrade_notice() {
	$info = get_plugin_data( __FILE__ );
	_e(
		sprintf(
			'
	<div class="error notice">
		<p>Opps! %s requires a minimum PHP version of 5.5.0. Your current version is: %s. Please contact your host to upgrade.</p>
	</div>', $info['Name'], PHP_VERSION
		)
	);
}

/**
 * Error if vendors autoload is missing
 */
function expertstatsplugin_php_vendor_missing() {
	$info = get_plugin_data( __FILE__ );
	_e(
		sprintf(
			'
	<div class="error notice">
		<p>Opps! %s is corrupted it seems, please re-install the plugin.</p>
	</div>', $info['Name']
		)
	);
}

/*
 * We want to use a fairly modern php version, feel free to increase the minimum requirement
 */
if ( version_compare( PHP_VERSION, '5.5.0' ) < 0 ) {
	add_action( 'admin_notices', 'expertstatsplugin_php_upgrade_notice' );
} else {
	if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
		include_once __DIR__ . '/vendor/autoload.php';
		add_action( 'plugins_loaded', 'expertstatsplugin_init', 11 );
		register_activation_hook( __FILE__, 'expertstatsplugin_activate' );
		register_deactivation_hook( __FILE__, 'expertstatsplugin_deactivate' );
	} else {
		add_action( 'admin_notices', 'expertstatsplugin_php_vendor_missing' );
	}
}
