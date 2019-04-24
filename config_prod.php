<?php

/* @var $container \ComposePress\Dice\Dice */

$container->addRule( 'ComposePress\Starter\Plugin', [
	'shared' => true,
] );
$container->addRule( 'ComposePress\Settings\Managers\Page', [
	'instanceOf' => 'ComposePress\Starter\Managers\Page',
] );
$container->addRule( '\Codeable\ExpertStats\Core\View', [
	'substitutions' => [
		'ComposePress\Views\Interfaces\ViewEngine' => '\ComposePress\Views\Engine\WordPress',
	],
] );
