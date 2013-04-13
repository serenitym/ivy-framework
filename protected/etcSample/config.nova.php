<?php

define('baseURL','http://'.$_SERVER['HTTP_HOST'].'/');
//define('baseURL','http://zero.nnsb.ro/novafood/');
define('basePath',dirname($_SERVER['DOCUMENT_ROOT']).'/');
//define('basePath','/var/www/novafood/');

define('publicURL',baseURL.'');
define('publicPath',basePath.'public_html/');

define('incPath',basePath.'protected/');

set_include_path(basePath.'protected/');



/*//define('dbHost', '192.168.5.150'); // zero
define('dbHost', '192.168.5.1'); // phobos
//define('dbHost', 'localhost');
define('dbName', 'ACE');
define('dbUser', 'ace');
define('dbPass', 'ace');*/


//define('dbHost', '192.168.5.150'); // zero
//define('dbHost', '192.168.5.1'); // phobos
//define('dbHost', 'dev.linuxd.net'); // phobos
//define('dbHost', 'localhost');
define('dbHost', 'my11644.d18213.myhost.ro');
define('dbName', 'novafood_ro_web');
define('dbUser', '18213ioana');
define('dbPass', '03061987');

# pt mail
define('smtpServer','mail.novafood.ro');
define('smtpUser','noreply@novafood.ro');
define('smtpPass','D0notreply');