<?php

session_start();

require_once dirname(__DIR__) . '/config/config.php';

/*
|--------------------------------------------------------------------------
| Composer Autoloader
|--------------------------------------------------------------------------
*/

require_once dirname(__DIR__) . '/vendor/autoload.php';


/*
|--------------------------------------------------------------------------
| Core Classes
|--------------------------------------------------------------------------
*/

require_once APPROOT . '/core/Database.php';
require_once APPROOT . '/core/Model.php';
require_once APPROOT . '/core/Controller.php';
require_once APPROOT . '/core/Mailer.php';


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

require_once APPROOT . '/helpers/Flash.php';
require_once APPROOT . '/helpers/Auth.php';
require_once APPROOT . '/helpers/Csrf.php';


/*
|--------------------------------------------------------------------------
| Application
|--------------------------------------------------------------------------
*/

require_once APPROOT . '/core/App.php';

$app = new App();