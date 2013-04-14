<?php



class ACcore extends Ccore
{
    /**
     * rescrisa in core pt admin se arata mesajul iar pentru guest - no mesage
     * @param $mess
     * @return string
     */
    static function debugMess($mess){ return $mess;}


    # deprecated
    /**
     * public function SET_REStree($idT)               {

        $this->GET_tree_fromDB(array($idT));
        echo 'ACcore SETtree pt tree-ul '.$idT;
        #var_dump($this->TMPtree);

        $tree_SER = serialize($this->TMPtree);
        umask(002);
        $succes  = file_put_contents(fw_pubPath.'GENERAL/core/RES_TREE/tree'.$idT.'.txt',$tree_SER);

        //if(defined('UMASK')) umask(UMASK);


        if(!$succes) echo "<b> Fail file_put_contents in </b>
                            ".fw_pubPath.'GENERAL/core/RES_TREE/tree'.$idT.'.txt'. '<br>';
        return $this->TMPtree;

    }*/

# deprecated
/**
 *    public function SET_tree()                      {


        if($this->idT)
        {
            // Nu inteleg conditia cu !isset($_SESSION['tree'.$this->idT])
            if( !file_exists(fw_pubPath.'GENERAL/core/RES_TREE/tree'.$this->idT.'.txt') )
            {
               $this->tree = $this->SET_REStree($this->idT); unset($this->TMPtree);

               echo 'Nu am gasit tree-ul '.fw_pubPath.'GENERAL/core/RES_TREE/tree'.$this->idT.'.txt';
            }

            else
                   $this->tree = unserialize(file_get_contents(fw_pubPath.'GENERAL/core/RES_TREE/tree'.$this->idT.'.txt'));



            return true;

        }
        else {  return false;}

    }*/


    public function SET_INC_ext($mod_name,$type_MOD,$extension,$folder='',$template='',$ADMINstr=''){
        parent::SET_INC_ext($mod_name,$type_MOD,$extension,$folder,$template,'');
        parent::SET_INC_ext($mod_name,$type_MOD,$extension,$folder,$template,'ADMIN/');
    }

    public function GET_objCONF(&$obj,$admin='',$template='') {

        parent::GET_objCONF($obj,'',$template);
        parent::GET_objCONF($obj,'A',$template);
    }

    public function SET_general_mod($mod_name, $type_MOD, $ADMINstr='ADMIN/', $ADMINpre='AC')  {

        if(isset($this->admin_MOD[$mod_name]) )
        {
            if( !isset($this->$mod_name) )
            parent::SET_general_mod($mod_name,$type_MOD,$ADMINstr,$ADMINpre);
        }
        else
            parent::SET_general_mod($mod_name,$type_MOD);
    }

    # functie cu denumire ambigua
    public function mergeArray(){

        $this->default_PLUGINS = array_merge($this->default_PLUGINS,$this->ADMIN_default_PLUGINS);
        $this->default_GENERAL = array_merge($this->default_GENERAL,$this->ADMIN_default_GENERAL);


    }

    public function CONTROL_setINI() {

       // $this->admin = true;

        $this->mergeArray();
        parent::CONTROL_setINI();

        $this->display = '';
       # $this->TOOLbar->ADDbuttons("<a href='".publicURL."assets/XOS-IDE/XOSIDE/index_EN.php' target='_blank'> IDE </a>");


    }

    /*____DEPRCATED __________________________________________________________________________________________________________  */
  /**
   *  function __construct()                      {

        if(PROFILER == 1)
            $this->profiler = new PhpQuickProfiler(PhpQuickProfiler::getMicroTime());


       $this->DB = new mysqli('p:'.dbHost,dbUser,dbPass,dbName);
       $this->DB->set_charset("utf8");

       # GET_objCONF($obj,$type_MOD, $mod_name,$admin='')
       $this->GET_objCONF($this,'GENERAL','core');
      // $this->GET_objCONF($this,'GENERAL','core','A');          #seteaza variabilele personalizate

       $this->modName = 'core';
       $this->modType = 'GENERAL';
       $this->SET_INC_extObj_jsCss($this);


       $this->CONTROL_setINI();

    }*/

    /*public function __destruct() {
        if(PROFILER == 1)
            $this->profiler->display($this->DB);
    }*/
}
