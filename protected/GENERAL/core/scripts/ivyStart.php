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


$dbLink = new mysqli('p:'.DB_HOST, DB_USER, DB_PASS, DB_NAME);
$dbLink->set_charset('utf8');

ini_set("session.gc_probability", 100);
ini_set("session.gc_divisor", 100);
ini_set("session.gc_maxlifetime", 14400);

$session = new Zebra_Session($dbLink, 'JbBJSvgtAdZY');
//session_start();

// print_r('<pre><strong>Current session settings:</strong><br><br>');
// print_r($session->get_settings());
// print_r('</pre>');
// exit();

// ----------[ unset cookies ]----------
// sessionManager::unsetCookies();
// =====================================


// ------[ create the auth object ]------
// var_dump(CauthManager::getInstance());
$auth = CauthManager::getInstance();
// var_dump($auth->userData->uname);
// ======================================


// ---------[ load the base class ]------
if (isset($_SESSION['auth'])) {
    $core = new ACLcore($dbLink);
    //$auth->Set_toolbarButtons($core);
} else {
    $core = new CLcore($dbLink);
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

Toolbox::Fs_writeTo(
    VAR_PATH.'tmp/sessions/' . session_id() . '/sercore.txt',
    $sercore
);
// file_put_contents(FW_PUB_PATH.'GENERAL/core/RES/serSESSION.txt', $serSESSION);

// var_dump(Toolbox::http_response_code('http://google.ro'));
// var_dump($_SESSION['auth']->user);


//???
// $p = new Permissions($_SESSION['auth']->uid);



// Console::logMemory($core,'core');
// Console::logMemory($auth,'auth');
// Console::logMemory();

// file_put_contents('serial.txt', serialize($core));

//var_dump($_POST);
