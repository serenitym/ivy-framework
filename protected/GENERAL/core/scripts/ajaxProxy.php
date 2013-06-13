<?php

   trigger_error($_POST['parsePOSTfile'],E_USER_NOTICE);

   require fw_incPath.'GENERAL/core/scripts/classLoader.inc';

   if((!isset($_POST['restoreCore']) || $_POST['restoreCore'] === true ) && file_exists(varPath.'tmp/sercore.txt'))
   {

       error_log("Restore Core restoreCore = ".$_POST['restoreCore']);
       $sercore  = file_get_contents(varPath.'tmp/sercore.txt');
       $core     = unserialize($sercore);

   }
   else
       echo "Fisierul sercore.txt nu exista sau POST['restoreCore'] = false ";

#=========================================================================
   if(isset($_POST['parsePOSTfile']))
       require_once(fw_incPath.$_POST['parsePOSTfile']);

 /* echo fw_incPath.$_POST['parsePOSTfile'];*/
