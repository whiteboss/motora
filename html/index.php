<?php

date_default_timezone_set('America/Santiago');

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$mailConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/mail.ini');
/**
* Настройка почты. работаем через SMTP с авторизацией
*/
$tr = new Zend_Mail_Transport_Smtp( $mailConfig->mail->host, $mailConfig->mail->toArray() );
Zend_Mail::setDefaultTransport($tr);

$application->bootstrap()
            ->run();