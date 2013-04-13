<?php
class ACManage extends  Ccore
{
    function delete_contentRES($dir, $prefix='') {
        foreach (glob("$dir/$prefix*.html") as $file) {
            #echo $file."<br>";
            unlink($file);
        }
    }
    function solve_affectedMOD($affectedMOD,$typeMOD='PLUGINS') {
        #var_dump($affectedMOD);
        if(is_array($affectedMOD))
            foreach($affectedMOD AS $modNAME)
            {

                $RESpath = resPath.$typeMOD.'/'.$modNAME;
                $this->delete_contentRES($RESpath);
            }
        }


    function regenerateALLtrees()    {

        /**
         * Regenereaza toate tree-urile deletate de create_masterTREE
         */
        $queryRES = $this->DB->query("SELECT Cid AS idT from TREE WHERE Pid='0' ");

          while($row = $queryRES->fetch_assoc())
          {

              $this->SET_REStree(fw_resTree.'tree'.$row['idT'].'.txt',  $row['idT']);
              unset($this->TMPtree);
          }
    }
    function create_masterTREE($unlinkTrees = true)     {

        /**
         *  - Creaza un master un array multidimensional cu toate tree-urile
         *  - deleteaza in acelasi timp toate tree-urile
         *  - urmand sa fie regenerate de metoda regenerateALLtrees()
          */

        $RES_TREE = fw_resTree ;

        if(  is_dir($RES_TREE) )
        {
            $dir = dir($RES_TREE);
            $masterTREE = array();

            while(false!== ($file=$dir->read()) )
            {
                $arr_file = explode('.',$file);
                if( end($arr_file) =='txt'  )
                {
                    $file_path = $RES_TREE.$file;
                    $tree = unserialize(file_get_contents($file_path));

                    $masterTREE = $masterTREE + $tree;
                    if($unlinkTrees)
                        unlink($file_path);   //stergem toate TREE-urile;
                }
            }

           /* if($this->masterTREE)
                foreach($this->masterTREE AS $id=>$item)
                    echo 'id='.$id.' nameF='.$item->nameF.' type='.$item->type."<br/>";*/
            return $masterTREE;
        }

      }


}


class ACcore extends ACManage
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
