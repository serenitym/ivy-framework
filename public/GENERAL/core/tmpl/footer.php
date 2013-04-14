
<!-- ========================================================================================================= -->

<?php
  echo (isset($core->admin)
             ? '  <script type="text/javascript"  src="assets/jquery-ui-1.8.19.custom/js/jquery-ui-1.8.19.custom.min.js"></script> '
               . ' <script type="text/javascript"  src="assets/nestedSortable/jquery.ui.nestedSortable.js"></script>'
             : '');
?>

<script type="text/javascript"  src="assets/ckeditor/ckeditor.js" type="text/javascript"></script>
<script type="text/javascript"  src="fw/GENERAL/core/js/GEN.js" type="text/javascript"></script>
<!-- <script type="text/javascript"  src="fw/GENERAL/core/js/rect.js"></script>-->
<!-- <script type="text/javascript"  src="GENERAL/js/kcfinder"></script>-->
<!-- <script type="text/javascript"  src="fw/GENERAL/core/js/GEN.js"></script>-->


 <?php
   echo $core->INC_js;
   $footer_TMPL = tmpl_inc.'footer.php';
   if(is_file($footer_TMPL)) require_once($footer_TMPL);
 ?>

<!-- ========================================================================================================= -->
</body>
</html>
