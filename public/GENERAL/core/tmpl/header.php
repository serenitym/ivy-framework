<!DOCTYPE html>
<html>
<head>
    <meta charset=utf-8>



    <?php

        echo

           //$core->SEO->DISPLAY().
           //'<base href="'.PUBLIC_URL.'" />'.

           (isset($core->admin)
                   ? ' <link rel="stylesheet" href="assets/jquery-ui-1.8.19.custom/development-bundle/themes/base/jquery.ui.all.css">'
                   : '')
           .'
            <!-- <link rel="stylesheet" href="fw/GENERAL/core/css/core.css"> -->
            <script type="text/javascript"  src="assets/jquery/jquery-1.7.2.min.js"></script>';


            $header_TMPL = tmpl_inc.'header.php';
            if(is_file($header_TMPL)) require_once($header_TMPL);

           echo  $core->cssInc;

    ?>

        <link rel="icon" type="image/png"
        href="<?php echo FAV_ICON; ?>">



</head>

<body >
