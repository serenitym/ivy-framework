
<!-- ========================================================================================================= -->



<!--<script type="text/javascript"  src="fw/GENERAL/core/js/GEN.js" type="text/javascript"></script>
<script type="text/javascript"  src="fw/GENERAL/core/js/_dep_GEN.js" type="text/javascript"></script>-->

<!-- <script type="text/javascript"  src="fw/GENERAL/core/js/rect.js"></script>-->
<!-- <script type="text/javascript"  src="GENERAL/js/kcfinder"></script>-->
<!-- <script type="text/javascript"  src="fw/GENERAL/core/js/GEN.js"></script>-->
    <script type="text/javascript">
        var ivyMods = {
            set_iEdit:{
                  //modName : function(){}
              }
        };
        $(document).ready(function(){

            <?php
                if(isset($core->admin) && $core->admin)
                    echo "fmw.admin = 1;";
                 echo "fmw.idC = $core->idNode;
                       fmw.idT = $core->idTree;";
            ?>
        });


    </script>

 <?php
   echo $core->jsTalk;
   echo $core->jsInc;
   $footer_TMPL = tmpl_inc.'footer.php';
   if(is_file($footer_TMPL)) require_once($footer_TMPL);
 ?>

<!-- ========================================================================================================= -->
</body>
</html>
