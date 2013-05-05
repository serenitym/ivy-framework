<?php
global $core;
define('tmpl_inc', publicPath . "fw/LOCALS/{$core->mainModel}/tmpl_{$core->mainTemplate}/tmpl/");
define('tmpl_url', publicURL . "fw/LOCALS/{$core->mainModel}/tmpl_{$core->mainTemplate}/");


require_once(fw_pubPath.'GENERAL/core/tmpl/header.php');
require_once(fw_pubPath.'GENERAL/core/tmpl/content.php');
require_once(fw_pubPath.'GENERAL/core/tmpl/footer.php');
