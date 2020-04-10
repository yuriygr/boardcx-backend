<?php
/**
 * @package     API
 *
 * @copyright   Copyright (C) 2005 - 2017 Yuriy Grinev. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Show all error
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

/**
 * Timezone
 */
date_default_timezone_set('Australia/Darwin');

/**
 * Define App directory
 */
define('BASE_DIR', realpath('../'));
define('APP_DIR', realpath('../app'));
define('PUB_DIR', realpath('../public'));

/**
 * Define App headers
 */
define('APP_HEADERS', [
	//'Access-Control-Allow-Origin' 		=> 'https://board.cx',
	'Access-Control-Allow-Origin' 		=> '*',
	'Access-Control-Allow-Methods' 		=> 'GET, POST, DELETE',
	'Access-Control-Allow-Headers' 		=> 'Origin, X-Session, Content-Type, Accept',
	'Access-Control-Expose-Headers' 	=> 'X-Session',
	'Cache-Control' 					=> 'public, must-revalidate, proxy-revalidate',
	'Content-Type' 						=> 'application/json'
]);

/**
 * Load vendors from composer
 */
include(APP_DIR . '/vendor/autoload.php');

/**
 * Read environment 
 */
include(APP_DIR . '/config/environment.php');

/**
 * Read the configuration
 */
include(APP_DIR . '/config/config.php');

/**
 * Read auto-loader
 */
include(APP_DIR . '/config/loader.php');

/**
 * Read services
 */
include(APP_DIR . '/config/services.php');

/**
 * Create micro applicateion
 */
$app = new \Phalcon\Mvc\Micro($di);

/**
 * Session Collection
 */
$session = new \Phalcon\Mvc\Micro\Collection();
$session->setHandler(new SessionController());
$session->setPrefix('/session');
$session->get('.get', 'get');
$session->get('.delete', 'get');
$app->mount($session);

/**
 * Topic Collection
 */
$topic = new \Phalcon\Mvc\Micro\Collection();
$topic->setHandler(new TopicsController());
$topic->setPrefix('/topics');
$topic->get('.list', 'list');
$topic->get('.item', 'item');
$topic->get('.refresh', 'refresh');
$topic->options('.add', 'add');
$topic->post('.add', 'add');
$topic->post('.report', 'report');
$topic->post('.password', 'password');
$topic->get('.logs', 'logs');
$app->mount($topic);

/**
 * Comments Collection
 */
$comment = new \Phalcon\Mvc\Micro\Collection();
$comment->setHandler(new CommentsController());
$comment->setPrefix('/comments');
$comment->get('.item', 'item');
$comment->get('.list', 'list');
$comment->options('.add', 'add');
$comment->post('.add', 'add');
$comment->post('.report', 'report');
$app->mount($comment);


/**
 * Gallery Collection
 */
$gallery = new \Phalcon\Mvc\Micro\Collection();
$gallery->setHandler(new GalleryController());
$gallery->setPrefix('/gallery');
$gallery->get('.list', 'list');
$app->mount($gallery);

/**
 * Pages Collection
 */
$pages = new \Phalcon\Mvc\Micro\Collection();
$pages->setHandler(new PagesController());
$pages->setPrefix('/pages');
$pages->get('.list', 'list');
$pages->get('.item', 'item');
$app->mount($pages);

/**
 * Tags Collection
 */
$tags = new \Phalcon\Mvc\Micro\Collection();
$tags->setHandler(new TagsController());
$tags->setPrefix('/tags');
$tags->get('.list', 'list');
$tags->get('.stats', 'stats');
$app->mount($tags);

/**
 * Moderation
 */
$mod = new \Phalcon\Mvc\Micro\Collection();
$mod->setHandler(new ModerationController());
$mod->setPrefix('/mod');
$mod->post('.edit', 'edit');
$mod->post('.ban', 'ban');
$mod->post('.pin', 'pin');
$mod->post('.close', 'close');
$mod->post('.delete', 'delete');
$mod->post('.settings', 'settings');
$app->mount($mod);

/**
 * Bans
 */
$bans = new \Phalcon\Mvc\Micro\Collection();
$bans->setHandler(new BansController());
$bans->setPrefix('/bans');
$bans->post('.check', 'check');
$app->mount($bans);

/**
 * Settings
 */
$settings = new \Phalcon\Mvc\Micro\Collection();
$settings->setHandler(new SettingsController());
$settings->setPrefix('/settings');
$settings->post('.export', 'export');
$settings->post('.import', 'import');
$app->mount($settings);

/**
 * Catch Throw error
 */
$app->error(function ($e) use ($app) {
	$res = [
		'error' => [
			'error_type' => 'Exception',
			'error_message' => $e->getMessage()
		]
	];
	$app->response->setStatusCode(400, 'Bad Request');
	return $app->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
});

/**
 * Not found
 */
$app->notFound(function () use ($app) {
	$res = [
		'error' => [
			'error_type' => 'Not found',
			'error_message' => 'Ooops..!'
		]
	];
	$app->response->setStatusCode(404, 'Not Found');
	return $app->response->setJsonContent($res, JSON_UNESCAPED_UNICODE);
});

$app->handle();