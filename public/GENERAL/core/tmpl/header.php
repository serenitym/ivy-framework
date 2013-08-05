<!DOCTYPE html>
<html>
<head>
    <meta charset=utf-8>

    <?php
        echo
           //'<base href="'.PUBLIC_URL.'" />'.
           (isset($core->admin)
                   ? ' <link rel="stylesheet" href="assets/jquery-ui-1.8.19.custom/development-bundle/themes/base/jquery.ui.all.css">'
                   : '')
           .'
            <!-- <link rel="stylesheet" href="fw/GENERAL/core/css/core.css"> -->
            <script type="text/javascript"  src="assets/jquery/jquery-1.7.2.min.js"></script>';


            $header_TMPL = TMPL_INC . 'header.php';
            if(is_file($header_TMPL)) require_once($header_TMPL);

           echo  $core->cssInc;
    ?>

    <?php
      echo (isset($core->admin)
                 ? '  <script type="text/javascript"  src="assets/jquery-ui-1.8.19.custom/js/jquery-ui-1.8.19.custom.min.js"></script> '
                   . ' <script type="text/javascript"  src="assets/nestedSortable/jquery.ui.nestedSortable.js"></script>'
                   . '<script type="text/javascript"  src="assets/ckeditor/ckeditor.js" type="text/javascript"></script>'
                 : '');

    if(defined("FAV_ICON")) {
        echo '<link rel="icon" type="image/png" href="' . FAV_ICON . '">';
    } else {
        echo '<link rel="icon" type="image/png" href="' . TMPL_URL . 'favico.png">';
    }

    ?>

</head>
<body >
