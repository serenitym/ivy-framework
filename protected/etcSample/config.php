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

define('BASE_URL','http://'.$_SERVER['HTTP_HOST'].'/');
define('BASE_PATH',dirname($_SERVER['DOCUMENT_ROOT']).'/');

define('PUBLIC_URL',BASE_URL.'');
define('PUBLIC_PATH',BASE_PATH.'public/');
define('INC_PATH',BASE_PATH.'protected/');


define('FW_PUB_PATH',PUBLIC_PATH.'fw/');
define('FW_PUB_URL', PUBLIC_URL.'fw/');
define('FW_INC_PATH',INC_PATH.'fw/');

define('VAR_PATH',INC_PATH.'var/');
define('RES_PATH',PUBLIC_PATH.'RES/');
define('RES_URL',PUBLIC_URL.'RES/');

define('FW_RES_TREE',VAR_PATH.'trees/');

set_include_path(BASE_PATH.'protected/');

define('DB_HOST', '');
define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASS', '');

define('DB_RO_USER', '');
define('DB_RO_PASS', '');

# pt mail
define('SMTP_SERVER','');
define('SMTP_USER','');
define('SMTP_PASS','');


