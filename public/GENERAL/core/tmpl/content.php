
    <?php  if($core->admin) echo $core->TOOLbar->DISPLAY(); ?>
    <form action="" method="post" style="position: absolute;">
       <input type='hidden' name='current_idT' value='<?php echo $core->idT; ?>'  />
       <input type='hidden' name='current_idC' value='<?php echo $core->idC; ?>'  />
       <input type='hidden' name='lang'        value='<?php echo $core->lang; ?>' />
       <input type='hidden' name='lang2'       value='<?php echo $core->lang2; ?>'/>
       <input type='hidden' name='cat'         value='<?php echo str_replace(' ','_',$core->tree[$core->idT]->{'name_'.$core->lang}); ?>'/>
    </form>

    <?php

    $content_TMPL = tmpl_inc.'content.php';

    if(is_file($content_TMPL)) require_once($content_TMPL);
    else
        echo 'There is no Template at '.$content_TMPL;
    ?>



