<?php
/**
 * ATENTIE:
 *
 * $_POST['restoreCore']
 *  - poate veni via $.post() dintr-un js
 *      dar atentie : restoreCore : true => valoarea aceasta va fii privita ca string
 *  - poate veni dintr-un formular dar aici iar s-ar putea sa existe probleme de tipul informatiei
 *
 * => se vor folosi doar numere 0 / 1
*/
trigger_error($_POST['parsePOSTfile'],E_USER_NOTICE);

require FW_INC_PATH.'GENERAL/core/scripts/classLoader.inc';

$_POST['restoreCore'] = !isset($_POST['restoreCore']) ? 1 : intval($_POST['restoreCore']);
if ($_POST['restoreCore'] && file_exists(VAR_PATH.'tmp/sercore.txt')) {

    error_log("[ ivy ] "."Restore Core restoreCore = ".$_POST['restoreCore']);
    $sercore  = file_get_contents(VAR_PATH.'tmp/sercore.txt');
    $core     = unserialize($sercore);
    $core->wakeup();

} else {

    echo "
    <div class='formFBK'>
        Fisierul sercore.txt nu exista sau POST['restoreCore'] = "
        .$_POST['restoreCore']." cu tipul ".gettype($_POST['restoreCore'])
    ."</div>";
}

//=========================================================================
if (isset($_POST['parsePOSTfile'])) {
    include_once FW_INC_PATH.$_POST['parsePOSTfile'];
}

 /* echo FW_INC_PATH.$_POST['parsePOSTfile'];*/
