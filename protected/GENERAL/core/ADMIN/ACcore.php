<?php
class ACcore extends ACunstable
{
    /**
     * rescrisa in core pt adminFolder se arata mesajul iar pentru guest - no mesage
     * @param $mess
     * @return string
     */
    static function debugMess($mess){ return $mess;}

    # functie cu denumire ambigua
    public function mergeArray(){

        $this->default_PLUGINS = array_merge($this->default_PLUGINS,$this->defaultAdmin_PLUGINS);
        $this->default_GENERAL = array_merge($this->default_GENERAL,$this->defaultAdmin_GENERAL);


    }

    public function _init_modules() {

       // $this->adminFolder = true;

        $this->mergeArray();
        parent::_init_modules();

        $this->display = '';
       # $this->TOOLbar->ADDbuttons("<a href='".PUBLIC_URL."assets/XOS-IDE/XOSIDE/index_EN.php' target='_blank'> IDE </a>");


    }

}
