<?php
class ACcore extends ACsetModule
{
    /**
     * rescrisa in core pt adminFolder se arata mesajul iar pentru guest - no mesage
     * @param $mess
     * @return string
     */
    static function debugMess($mess){ return $mess;}

    # functie cu denumire ambigua
    public function mergeArray(){

        $this->default_PLUGINS = array_merge($this->default_PLUGINS,$this->ADMIN_default_PLUGINS);
        $this->default_GENERAL = array_merge($this->default_GENERAL,$this->ADMIN_default_GENERAL);


    }

    public function _init_modules() {

       // $this->adminFolder = true;

        $this->mergeArray();
        parent::_init_modules();

        $this->display = '';
       # $this->TOOLbar->ADDbuttons("<a href='".publicURL."assets/XOS-IDE/XOSIDE/index_EN.php' target='_blank'> IDE </a>");


    }

}
