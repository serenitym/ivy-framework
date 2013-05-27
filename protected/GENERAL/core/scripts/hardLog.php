<?php

//session_start();
// ------[ get the class loader ]-------
require fw_incPath.'GENERAL/core/scripts/classLoader.inc';
require incPath.'etc/hardLogKey.php';

//$psw = 'adminPro';
#=======================================


    // ---------[ admin.php login ]---------
    # /!\ DEPRECATED
    if (isset($_POST['password'])) {
        if($_POST['password']== $psw) $_SESSION['admin']=1;
    }
    #=======================================

    // ----------[ destroy session ]----------
    if (isset($_GET['logOUT'])) {
      unset($_SESSION['admin']);
    }
    #=======================================

    // ---------[ load the base class ]---------
    if (isset($_SESSION['admin'])
        || $_POST['password'] === $psw
    ) {
        $core = new ACcore();
    }
    else {
        $core = new Ccore();
    }

    /*
     * Pentru a putea sa ma refer la core
     * din interiorul lui procesSCRIPT.php*/

    $sercore     = serialize($core);
    //$serSESSION = session_encode();

    file_put_contents(varPath.'tmp/sercore.txt', $sercore);
