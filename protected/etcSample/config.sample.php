<?php

//ini_set('unserialize_callback_func', array(ClassLoader::getInstance(),
    //'loadClass'));

//session_set_save_handler("SessionManager::open",
                         //"SessionManager::close",
                         //"SessionManager::read",
                         //"SessionManager::write",
                         //"SessionManager::destroy",
                         //"SessionManager::gc"
                        //);

//define('PROFILER', '1');
define('UMASK', '0755');
//define('AVATAR',FALSE);

define('baseURL', 'http://'.$_SERVER['HTTP_HOST'].'/');
//define('basePath',dirname($_SERVER['DOCUMENT_ROOT']).'/');
define('basePath', '/srv/http/blacksea.eu/blacksea.eu/');

define('publicURL', baseURL . '');
define('publicPath', basePath . 'public_html/');
define('incPath', basePath . 'protected/');


define('fw_pubPath', publicPath . 'fw/');
define('fw_pubURL', publicURL . 'fw/');
define('fw_incPath', incPath . 'fw/');

define('resPath', publicPath . 'RES/');
define('resURL', publicURL . 'RES/');



set_include_path(basePath.'protected/');



define('dbHost', '');
define('dbName', '');
define('dbUser', '');
define('dbPass', '');
define('dbroUser', '');
define('dbroPass', '');

# pt mail
define('smtpServer', '');
define('smtpUser', '');
define('smtpPass', '');

