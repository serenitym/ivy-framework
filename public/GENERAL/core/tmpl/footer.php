
<!-- ======================================================================= -->



<!--
    <script type="text/javascript"
        src="fw/GENERAL/core/js/GEN.js" type="text/javascript"></script>
    <script type="text/javascript"
        src="fw/GENERAL/core/js/_dep_GEN.js" type="text/javascript"></script>
-->

<!--
    <script type="text/javascript"  src="fw/GENERAL/core/js/rect.js"></script>
    <script type="text/javascript"  src="GENERAL/js/kcfinder"></script>
    <script type="text/javascript"  src="fw/GENERAL/core/js/GEN.js"></script>
-->
    <script type="text/javascript">
        var ivyMods = {
            set_iEdit:{
                  //modName : function(){}
              }
        };
        $(document).ready(function(){

            <?php
                if(isset($core->admin) && $core->admin) {
                    echo "fmw.admin = 1;";
                }
                 echo "fmw.idC = $core->idNode;
                       fmw.idT = $core->idTree;";

                 echo $core->jsTalk;
            ?>
        });


    </script>

 <?php

echo $core->jsInc;
$footer_TMPL = TMPL_INC.'footer.php';
if (is_file($footer_TMPL)) {
    include_once $footer_TMPL;
}
 ?>

<!-- ======================================================================= -->
</body>
</html>
