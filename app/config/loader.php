<?php

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerDirs([
	$config->application->controllersDir,
	$config->application->modelsDir,
	$config->application->libraryDir
]);
$loader->registerNamespaces([
	'Phalcon' => $config->application->libraryDir,
]);

$loader->register();