<?php

/* vim: set fdm=marker: */

/** {{{
 * Script containing the basic login and rights management procedure
 *
 * PHP Version 5.3+
 *
 * @category Accounts
 * @package  Auth
 * @author   Victor Nițu <victor@serenitymedia.ro>
 * @license  http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 * @link
 *     http://redmine.usr.sh/projects/ivy-framework/wiki/Authentication_system
 * }}}
 */


// ------[ get the class loader ]-------
require FW_INC_PATH.'GENERAL/core/scripts/classLoader.inc';
// =====================================

// ----[ look for the login cookie ] ---
// if(isset($_COOKIE['auth']))
    // sessionManager::attemptCookieLogin();

    // ----[ or just go as usual ] -----

// Require the HTTP_Session2 PEAR file
//require_once 'HTTP/Session2.php';

HTTP_Session2::useTransSID(false);
//HTTP_Session2::useCookies(false);

// enter your DSN
// HTTP_Session2::setContainer(
//     'MDB2',
//     array('dsn' => DSN,
//         'table' => 'sessiondata')
// );
//
// // --------[ start the session ]--------
// HTTP_Session2::start('s');
// HTTP_Session2::setExpire(time() + 60); // set expire to 60 seconds
// HTTP_Session2::setIdle(time() + 5);    // set idle to 5 seconds
//
// if (HTTP_Session2::isExpired()) {
//     HTTP_Session2::destroy();
// }
//
// if (HTTP_Session2::isIdle()) {
//     HTTP_Session2::destroy();
// }
//
// HTTP_Session2::updateIdle();
// =====================================

session_start();

// ----------[ unset cookies ]----------
// sessionManager::unsetCookies();
// =====================================


// ------[ load environment vars ]------
// require_once(FW_INC_PATH.'GENERAL/core/Cvars.php');
// =====================================


// ----------[ destroy session ]--------
if (isset($_GET['logOUT'])) {
    sessionManager::destroySession();
    sessionManager::unsetCookies();
    header("Location: http://".$_SERVER['SERVER_NAME']);
}
// =====================================


// ------[ create the auth object ]------
// var_dump(CauthManager::getInstance());
$auth = CauthManager::getInstance();
// var_dump($auth->userData->uname);
// ======================================


// ---------[ load the base class ]------
if (isset($_SESSION['auth'])) {
    $core = new ACLcore($auth);
} else {
    $core = new CLcore($auth);
}
//var_dump($_SESSION);

// $_SESSION['core'] = &$core;
// ======================================

/*
 * Pentru a putea sa ma refer la core
 * din interiorul lui procesSCRIPT.php
 * */

$sercore     = serialize($core);
// $serSESSION = session_encode();

file_put_contents(VAR_PATH.'tmp/sercore.txt', $sercore);
// file_put_contents(FW_PUB_PATH.'GENERAL/core/RES/serSESSION.txt', $serSESSION);

// var_dump(Toolbox::http_response_code('http://google.ro'));
// var_dump($_SESSION['auth']->user);


//???
// $p = new Permissions($_SESSION['auth']->uid);



// Console::logMemory($core,'core');
// Console::logMemory($auth,'auth');
// Console::logMemory();

// file_put_contents('serial.txt', serialize($core));
