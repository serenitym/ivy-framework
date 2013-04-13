<?php
/**
 * ADMIN_vars
 *
 * @uses Ccore
 * @package Core
 * @version 1.0
 * @copyright Copyright (c) 2012 Serenity Media
 * @author  Ioana Cristea
 * @license AGPLv3 {@link http://www.gnu.org/licenses/agpl-3.0.txt}
 */
class ADMIN_vars extends Ccore
{

    var $TMPtree=array();
    var $user;                   #NOT IMPLEMENTED obj de tip Perms;
    var $HTML_GEN_edit = '';     #????

    var $admin_MOD             = array();
    var $ADMIN_default_PLUGINS = array();
    var $ADMIN_default_GENERAL = array();
/*  var $admin_MOD     = array('products'=>true, 'page'=>true,'portof'=>true,'news'=>true,'TOOLbar'=>true,'GEN_edit'=>true,'EDITmode'=>true);
    var $ADMIN_default_PLUGINS = array('TOOLbar','GEN_edit','EDITmode');*/



    public function mergeArray(){

        $this->default_PLUGINS = array_merge($this->default_PLUGINS,$this->ADMIN_default_PLUGINS);
        $this->default_GENERAL = array_merge($this->default_GENERAL,$this->ADMIN_default_GENERAL);


    }
}