<?php

   trigger_error($_POST['parsePOSTfile'],E_USER_NOTICE);

   require fw_incPath.'GENERAL/core/scripts/classLoader.inc';
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
   $_POST['restoreCore'] = !isset($_POST['restoreCore']) ? 1 : intval($_POST['restoreCore']);
   if($_POST['restoreCore'] && file_exists(varPath.'tmp/sercore.txt'))
   {

       error_log("Restore Core restoreCore = ".$_POST['restoreCore']);
       $sercore  = file_get_contents(varPath.'tmp/sercore.txt');
       $core     = unserialize($sercore);

   }
   else
       echo "
       <div class='formFBK'>
            Fisierul sercore.txt nu exista sau POST['restoreCore'] = ".$_POST['restoreCore'].
        " cu tipul ".gettype($_POST['restoreCore']).
        "</div>"
       ;

#=========================================================================
   if(isset($_POST['parsePOSTfile']))
       require_once(fw_incPath.$_POST['parsePOSTfile']);

 /* echo fw_incPath.$_POST['parsePOSTfile'];*/
