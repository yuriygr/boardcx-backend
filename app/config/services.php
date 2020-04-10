<?php

/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new \Phalcon\DI\FactoryDefault();

/**
 * For use in controllers
 */
$di->set('config', $config);

/**
 * Database connection is created based in the parameters defined in the configuration file
 * See more adapters: https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Db/Adapter
 */
$di->setShared('db', function() use ($config) {
	return new \Phalcon\Db\Adapter\Pdo\Mysql([
		'host' 			=> $config->database->host,
		'username' 		=> $config->database->username,
		'password' 		=> $config->database->password,
		'dbname' 		=> $config->database->name,
		'charset' 		=> $config->database->charset,
		'persistent' 	=> $config->database->persistent,
		'options' 		=> [
			\PDO::ATTR_ERRMODE 		=> \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_PERSISTENT 	=> true,
			\PDO::ATTR_AUTOCOMMIT 	=> false
		]
	]);
});

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 * See more adapters: https://github.com/phalcon/incubator/tree/master/Library/Phalcon/Mvc/Model/MetaData
 */
//$di->set('modelsMetadata', function () use ($config) {
//	return new \Phalcon\Mvc\Model\Metadata\Redis([
//		'host' 			=> $config->redis->host,
//		'port' 			=> $config->redis->port,
//		'persistent' 	=> $config->redis->persistent,
//		'statsKey' 		=> '_PHCM_MM',
//		'lifetime' 		=> $config->redis->lifetime
//	]);
//});

/**
 * Set the models cache service
 */
$di->set('modelsCache', function () use ($config) {

	// Cache data for one day by default
	$frontCache = new \Phalcon\Cache\Frontend\Data([
		'lifetime' => $config->redis->lifetime,
	]);

	// Memcached connection settings
	$cache = new \Phalcon\Cache\Backend\Redis($frontCache, [
		'host' 			=> $config->redis->host,
		'port' 			=> $config->redis->port,
		'index' 		=> $config->redis->index,
		'persistent' 	=> $config->redis->persistent
	]);

	return $cache;
});

/**
 * Start the session the first time some component request the session service
 */
$di->setShared('session', function() use ($config) {
	$session = new \Phalcon\Session\Adapter\Redis([
		'uniqueId'   => "boardcx-api",
		'host'       => $config->redis->host,
		'port'       => $config->redis->port,
		'lifetime'   => $config->redis->lifetime,
	]);
	$session->start();
	return $session;
});

/**
 * Request
 */
$di->set('request', function() {
	return new \Phalcon\Http\Request;
});

/**
 * Response
 */
$di->set('response', function() {
	$response = new \Phalcon\Http\Response;

	foreach (APP_HEADERS as $key => $value)
		$response->setHeader($key, $value);

	return $response;
});

/**
 * Request
 */
$di->set('fileUploader', function() {
	return new \Phalcon\FileUploader;
});

/**
 * Parse
 */
$di->set('parse', function() {
	return new \Phalcon\Utils\Parse;
});

/**
 * Security
 */
$di->set('security', function() {
	$security = new \Phalcon\Security;
	return $security;
});

/**
 * reCaptcha
 */
$di->set('recaptcha', function() use ($config) {
	$recaptcha = new \ReCaptcha\ReCaptcha($config->recaptcha->secret);
	return $recaptcha;
});