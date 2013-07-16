<?php
global $core;
define('tmpl_inc', PUBLIC_PATH . "fw/LOCALS/{$core->mainModel}/tmpl_{$core->mainTemplate}/tmpl/");
define('tmpl_url', PUBLIC_URL . "fw/LOCALS/{$core->mainModel}/tmpl_{$core->mainTemplate}/");


require_once(FW_PUB_PATH.'GENERAL/core/tmpl/header.php');
require_once(FW_PUB_PATH.'GENERAL/core/tmpl/content.php');
require_once(FW_PUB_PATH.'GENERAL/core/tmpl/footer.php');
