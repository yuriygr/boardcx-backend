<?php

$config = new \Phalcon\Config([
	'site' => [
		// Кол-во тредов на странице
		'topicsPerPage' 	=> getenv('SITE_TOPICS_PER_PAGE'),
		// Кол-во постов в треде
		'commentsLimit' 	=> getenv('SITE_COMMENTS_LIMIT'),
		// Кол-во тредов на странице
		'filesPerPage' 		=> getenv('SITE_FILES_PER_PAGE'),
		// Максимальное кол-во символов в заголовке
		'subjectLimit' 		=> getenv('SITE_SUBJECT_LENGTH'),
		// Максимальное число тегов
		'tagsLimit' 		=> getenv('SITE_TAGS_LIMIT'),
		// Через какой промежуток можно сделать новый комментарий и топик
		'topicsTimeLimit' 	=> getenv('SITE_TOPICS_TIME_LIMIT'),
		'commentsTimeLimit' => getenv('SITE_COMMENTS_TIME_LIMIT'),
		'reportsTimeLimit' 	=> getenv('SITE_REPORTS_TIME_LIMIT'),
		// Имя по умолчанию
		'defalutName'		=> getenv('SITE_DEFALUT_NAME'),
		// Разрешённые к загрузке файлы
		'allowedFiles' 		=> ['image/jpg', 'image/jpeg', 'image/png', 'image/gif'],
		// Сколько файлов можно аттачить к посту
		'maxFiles' 			=> '2',
	],
	'database' => [
		'host' 				=> getenv('DATABASE_HOST'),
		'name' 				=> getenv('DATABASE_NAME'),
		'username' 			=> getenv('DATABASE_USER'),
		'password' 			=> getenv('DATABASE_PASS'),
		'charset' 			=> getenv('DATABASE_CHARSET'),
		'persistent'		=> getenv('DATABASE_PERSISTENT')
	],
	'redis' => [
		'host'				=> getenv('REDIS_HOST'),
		'port'				=> getenv('REDIS_PORT'),
		'lifetime'			=> getenv('REDIS_LIFETIME'),
		'index'				=> getenv('REDIS_INDEX'),
		'persistent'		=> getenv('REDIS_PERSISTENT')
	],
	'application' => [
		'baseUri'			=> '/',
		'version'			=> '1.1.0',
		'offline'			=> getenv('APP_OFFLINE'),
		'debug'				=> getenv('APP_DEBUG'),
		'log_lvl'			=> getenv('APP_LOG_LVL'),
		'cryptSalt'			=> getenv('APP_SALT'),
		// Folders path
		'configDir'			=> APP_DIR  . '/config/',
		'controllersDir'	=> APP_DIR  . '/controllers/',
		'modelsDir'			=> APP_DIR  . '/models/',
		'libraryDir'		=> APP_DIR  . '/library/',
		'cacheDir'			=> BASE_DIR . '/cache/',
	],
	'recaptcha' => [
		'enabled'			=> true,
		'secret' 			=> getenv('RC_SECRET')
	]
]);