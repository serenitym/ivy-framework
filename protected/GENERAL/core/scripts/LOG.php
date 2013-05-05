<?php


// ------[ get the class loader ]-------
require fw_incPath.'GENERAL/core/scripts/classLoader.inc';
#=======================================

// ----[ look for the login cookie ] ---
//if(isset($_COOKIE['auth']))
    //sessionManager::attemptCookieLogin();

// ------[ or just go as usual ] -------

    // --------[ start the session ]--------
    sessionManager::startSession();
    #=======================================


    // ----------[ unset cookies ]----------
    //sessionManager::unsetCookies();
    #=======================================


    // ------[ load environment vars ]------
   // require_once(fw_incPath.'GENERAL/core/Cvars.php');
    #=======================================

    // ---------[ admin.php login ]---------
    # /!\ DEPRECATED
    if(isset($_POST['password'])) {
        if($_POST['password']=='adminNOVA') $_SESSION['admin']=1;
    }
    #=======================================

    // ----------[ destroy session ]----------
    if(isset($_GET['logOUT'])) {
        sessionManager::destroySession();
        sessionManager::unsetCookies();
        header("Location: http://".$_SERVER['SERVER_NAME']);
    }
    #=======================================


    // -------[ create the auth object ]-------
    //var_dump(CauthManager::getInstance());
    $auth = CauthManager::getInstance();
    //var_dump($auth->userData->uname);
    #=======================================


    // ---------[ load the base class ]---------
    if(isset($_SESSION['auth'])) {
        $core = new ACcore($auth);
    }
    else {
        $core = new Ccore($auth);
    }
//    $_SESSION['core'] = &$core;
    #=======================================

    /*
     * Pentru a putea sa ma refer la core
     * din interiorul lui procesSCRIPT.php*/

    $sercore     = serialize($core);
    //$serSESSION = session_encode();

    file_put_contents(varPath.'tmp/sercore.txt', $sercore);
    //file_put_contents(fw_pubPath.'GENERAL/core/RES/serSESSION.txt', $serSESSION);

    //var_dump(Toolbox::http_response_code('http://google.ro'));
    //var_dump($_SESSION['auth']->user);

    $p = new Permissions($_SESSION['auth']->uid);

//    Console::logMemory($core,'core');
//    Console::logMemory($auth,'auth');
//    Console::logMemory();

    //file_put_contents('serial.txt', serialize($core));
