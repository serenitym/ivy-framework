<?php

/**
 * CExternalManage
 *
 * @uses vars
 * @package Core
 * @version 1.0
 * @copyright Copyright (c) 2012 Serenity Media
 * @author  Ioana Cristea
 * @license AGPLv3 {@link http://www.gnu.org/licenses/agpl-3.0.txt}
 */




class Ccore extends CsetModule
{


# manage function - should be put in TgenTools respectiv create ATgenTools
    static function debugMess($mess){ return '';}


    # 3
    /**
    * Seteaza obiectul curent
    *  - obiect curent = requested by type || idC=> type
    */
    public function SET_current()        {

        $this->Module_Build($this->type,$this->modType) ;
    }

    # 3
    /**
     * Sets default modects (modules) declared in yml files of core AND tmpl_core
     */
    public function SET_default()        {

        foreach($this->mods AS $modType)
        {
            foreach($this->{'default_'.$modType} AS $modName)
            {
                # error_log("SET_default ".'$modType = '.$modType.' $modName = '.$modName."\n\n");
                $this->Module_Build($modName,$modType);
            }
        }
    }




#=============================================[ set - TREE ]============================================================

    # 1
    /**
     * Functie recursiva care creaza un tree al paginilor
     *
     * WORKING with
     *
     *   TB: ITEMS
     *      id
     *      name_ro     - numele paginii in engleza / romana
     *      name_en
     *      type        - numele modulului care gestioneaza pagina
     *      SEO         - array serioalizat cu tagurile de SEO
     *
     *   TB: TREE
     *      Pid         - id-ul parintelui
     *      Cid         - id-ul copiilor
     *      poz         - pozitia copilului
     *
     *
     *
     * LOGISTICS
     *  - orice pagina care nu are un parinte are un tree serializat
     *  - orice pagina este un obiect item cu
     *  - functia este reapelata pentro orice pagina/ item care are copii
     *
     *      name        - numele curent bazat pe limba curenta
     *      name_ro
     *      name_en
     *      type        -
     *      id
     *      p_id        - id-ul parintelui
     *      nameF       - numele fisierului de resursa
     *      children    = array( [poz] => [Cid],... );
     *
     *
     * @param $ch
     * @param string $p_id
     */
    public function GET_tree_fromDB($ch,$p_id='', $idT,$level=0) {

            foreach($ch AS $id_ch)
            {
                $this->TMPtree[$id_ch] = new item();

                $q = "SELECT name_ro,name_en,type from ITEMS where id='$id_ch' ;";
                $q_arr = $this->DB->query($q)->fetch_assoc();


                $this->TMPtree[$id_ch]->name    = $q_arr['name_'.$this->langs[0]];
                $this->TMPtree[$id_ch]->name_ro = $q_arr['name_ro'];
                $this->TMPtree[$id_ch]->name_en = $q_arr['name_en'];
                $this->TMPtree[$id_ch]->type    = $q_arr['type'];
                $this->TMPtree[$id_ch]->id      = $id_ch;
                $this->TMPtree[$id_ch]->p_id    = $p_id;
                # idT si level 2 noi concepte
                $this->TMPtree[$id_ch]->idT     = $idT;
                $this->TMPtree[$id_ch]->level   = $level;
                $this->TMPtree[$id_ch]->nameF   = str_replace(' ','_',$this->TMPtree[$id_ch]->name) ;
                /*  $this->TMPtree[$id_ch]->new     = $q_arr['new'];*/



                $q = " SELECT Pid,Cid,poz FROM TREE where Pid='$id_ch' ORDER BY poz ASC ;";
                $q_res = $this->DB->query($q);

                while($ch_arr = $q_res->fetch_assoc() )
                    $this->TMPtree[$id_ch]->children[ $ch_arr['poz'] ] = $ch_arr['Cid'];


                if($q_res->num_rows)
                    $this->GET_tree_fromDB($this->TMPtree[$id_ch]->children,$id_ch, $idT, $level+1);

            }

        }

    # 2
    /**
     * Returneaza un vector temporar al tree-ului cerut din BD
     * @param $pathTree     - calea unde ar trebui sa stea tree-ul
     * @param $idT          - id-ul treeului
     * @return mixed
     */
    public function SET_REStree($pathTree, $idT)  {

          $this->GET_tree_fromDB(array($idT),'',$idT);

        #  echo 'ACcore SETtree pt tree-ul '.$idT;  #var_dump($this->TMPtree);

          $tree_SER = serialize($this->TMPtree);
          #umask(0777);
          $succes  = file_put_contents($pathTree,$tree_SER);

          //if(defined('UMASK')) umask(UMASK);
          if(!$succes)
              echo "<b>SET_REStree -  Fail file_put_contents in </b> $pathTree <br>";
          return $this->TMPtree;

      }

    # 3
    /**
     * Returneaza un vector deserializat al tree-ului curent
     *
     * STEPS:
     *  - daca se gaseste fisierul cu tree-ul serializat
     *  - daca nu se preia din BD(care creaza un vector temporar - deaceea trebuie unset)
     *
     * @param $idT - id-ul treeului curent
     * @return mixed
     */
    public function GET_tree($idT)                {

          $pathTree = fw_resTree.'tree'.$idT.'.txt';

          if(is_file($pathTree))
              return  unserialize(file_get_contents($pathTree));
          else{

	      // GET_tree_fromDB
	      // scrie tree-ul in res SET_REStree
              $tree =  $this->SET_REStree($pathTree, $idT);
              unset($this->TMPtree);
              return $tree;

          }

      }

    # 4
    /**
     * Seteaza tree-ul curent bazat pe idT - requested
     * @return bool - daca a reusit sau nu sa returneze tree-ul
     */
    public function SET_tree()                    {

        if($this->idT)
        {
            $this->tree = $this->GET_tree($this->idT);
            if(is_array($this->tree)){
                return true;
            }
            else{
                $this->tree = array();
                return false;
            }
        }
        else
            return false;

    }


#=============================================[ ESETIALS ]==============================================================

    # 1
    public function SET_type()          {


           if($_REQUEST['type'])$this->type = $_REQUEST['type'];

       }

    # 1 --> EXCEPTIE!!! ???
    /**
     * SET: idT = primary parent, idC = id ITEMS / page
     * @return bool
     */
    public function SET_idTC()          {


        if(isset($_GET['idT']))
        {
               $this->idT =   $_GET['idT'];
               $this->idC = ( $_GET['idC'] ?  $_GET['idC'] : $this->idT );

                #======== ATENTIE !!! - EXCEPTIE???  ================================================
                //    if($this->idT == 1 && $this->idC!=1) $this->GET_idT_from_idC($this->idC);
                #======== ATENTIE !!! - EXCEPTIE  ================================================
               return true;
        }

        elseif($this->idC)  return true;

        else {return false; echo 'Nu am reusit sa iau treeul';}
    }

    # 1
    # ma gandesc ca toate aceste proprietati poate ar trebui sa stea intr-un obiect gen $this->current
    /**
     * Seteaza itemul curent
     */
    public function SET_ID_item()       {


        $this->name_ro   = &$this->tree[$this->idC]->name_ro;
        $this->name_en   = &$this->tree[$this->idC]->name_en;

        $this->nameF     = &$this->tree[$this->idC]->nameF;
        $this->name      = &$this->tree[$this->idC]->name;

        $this->type      =  $this->tree[$this->idC]->type;
        $this->children  = &$this->tree[$this->idC]->children;
       /* $this->new       = &$this->tree[$this->idC]->new;*/
        $this->id        = &$this->tree[$this->idC]->id;
        $this->p_id      = &$this->tree[$this->idC]->p_id;
        $this->level     = &$this->tree[$this->idC]->level;

         if(    in_array($this->type,$this->models )) $this->modType = 'MODELS';
         elseif(in_array($this->type,$this->plugins)) $this->modType = 'PLUGINS';
         elseif(in_array($this->type,$this->locals)) $this->modType = 'LOCALS';

    }

#=======================================================================================================================


    public function ctrl_postRequest(){


       // var_dump($_POST);
        if(isset($_POST['moduleName']) && isset($_POST['methName']))
        {

            $moduleName = $_POST['moduleName'];
            $methName   = $_POST['methName'];
            $relocate   = isset($_POST['relocate']) ? $_POST['relocate'] : true ;

            if(is_modect($this->$moduleName) && method_exists($this->$moduleName,$methName))
            {
                $mod = &$this->$moduleName;
                //===============[solve request Modules ]==========================

                /**
                 * Daca exista o metoda care sa parseze posturile
                 *  - exemplu validare , trim-uire
                 *  - aruncarea de erori
                 *
                 * => atunci va fii apelata aceasta metoda intai
                 * daca returneaza true => datele sunt valide si se poate procesa introducerea lor
                 * daca nu se mai intampla nimic
                */
                if(method_exists($mod,'_hook_'.$methName))
                {
                    $validData =$mod->{'_hook_'.$methName}();
                    if($validData)
                    {

                        $mod->{$methName}();

                        // =================[refresh page]======================
                        if($relocate){

                            unset($_POST);
                            $this->reLocate();
                        }
                    }
                    //safty reasons
                    unset($_POST);

                } else{
                    $mod->{$methName}();
                     // =================[refresh page]======================
                    if($relocate)
                    {
                        unset($_POST);
                        $this->reLocate();
                    }
                }

            }
            else{

             /*   if(!is_modect($this->$moduleName))
                    echo "There is no modect ".$moduleName;
                if(! method_exists($this->$moduleName,$methName))
                    echo " with method ".$methName;*/

            }

        }
        else {
         //   echo "No post moduleName or methName";
        }

       // echo "<b>moduleName</b> ".$_POST['moduleName']." <b>methName</b> ".$_POST['methName'];
    }

    #1  - A | use:
    /**
     *LOGISTICS
     *
       *
       *  - try to set the type property
       *  - if idT & idC exists => a tree[idT].txt should exist in /public/GENERAL/core/RES_TREE
       *  - from that tree we should be albe to determine the current item with all of its properties
       *
       *  - if a type is set - set requested module
       *
       *  - sets the default mod.'s     => le instantiaza obiectele si seteaza tagurile  js/css aferente ;
      */
    public function _init_modules()     {

       #================[ set current tree & module ]==================================

        if( $this->SET_idTC() &&  $this->SET_tree())
            $this->SET_ID_item();

       # astfel putem avea bizara situatie de a trimite un idC & idT => type dar totusi sa avem un alt type...
       if(isset($_REQUEST['type']))
            $this->SET_type();


       #================[ ini All ]===================================================

        $this->SET_default();
        $this->SET_HISTORY($this->idC);                #pus aici pentru ca intai trebuie initializata limba
        if($this->type)  $this->SET_current();


        $this->ctrl_postRequest();

      }

#=======================================================================================================================


    # COMMENT THIS!!!
    function __construct($auth=NULL)                {

        if(PROFILER == 1)
            $this->profiler = new PhpQuickProfiler(PhpQuickProfiler::getMicroTime());

       /**
         * DataBase connection
        */
       $this->DB = new mysqli('p:'.dbHost,dbUser,dbPass,dbName);
       //$this->DB = new PDO('mysql:host=' . dbHost . ';dbname=' . dbName . ';charset=utf8',
                            //dbUser, dbPass);
       echo  $this->DB->error;
       $this->DB->set_charset("utf8");



        /**
         * GENERAL settings
        */
        #atentie daca nu are template o sa includa tot din core/js si core/css
        $this->modName = 'core';
        $this->modType = 'GENERAL';
        $this->Module_Fs_configYamlProps($this);          #seteaza variabilele personalizate
        $this->Module_Set_incFilesJsCss($this);


        /**
         * Set modul user
         * pentru ca obiectul nu este instantiat de core deci
         * nu are proprietatile necesare pentru templatind
         * modName, modType
        */
        if($auth)
        {
            $this->user = &$auth->user;
            $this->user->modName = 'user';
            $this->user->modType = 'GENERAL';
        }


        /**
         * Local project settings
         */
        if(isset($this->mainModel) && isset($this->mainTemplate))
        {
            #  Set_incFiles($modName,$modType,$extension,$folder='',$template='',$adminFolder='')
            $this->Set_incFiles($this->mainModel, 'LOCALS', 'css','', $this->mainTemplate);
            $this->Set_incFiles($this->mainModel, 'LOCALS', 'js','',  $this->mainTemplate);
        }




        $this->_init_modules();

        #echo 'Ccore: __construct';
        #var_dump($this);
    }

    public function __destruct() {
        if(PROFILER == 1)
            $this->profiler->display($this->DB);
    }
    /**
     * ATENTIE
     *  - aceasta functie este apelata din interiorul __wakeup-ului altor module
     * de ce nu se creaza o metoda de wakeup a core-ului care sa apeleze aceasta metoda??
     *
    */
    public function DB_reConnect () {
        /**
         * DE ce nu unset($this->DB) - explicatie
         * nu am dat unset pentru ca se va pierde locatia din memorie a lui DB
         * => obiectele care au pointer la $this->DB vor da in gol
         * deci degeaba recreez eu conexiunea pentru ca aceasta ar fii
         * valabila doar pentru core*/

        $DBstat = $this->DB->ping();
        if($DBstat == FALSE)
        {
            $this->DB = '';
            $this->DB = new mysqli(dbHost,dbUser,dbPass,dbName);
            #echo "A fost apelat core wakeup si DB NU este connectat <br>";
        }
        else
        {
            echo "A fost apelat  este connectat <br>";

        }


    }

    public function __wakeup(){
        $this->DB_reConnect();
        $this->ctrl_postRequest(false);
    }


    public function __clone  () { }
}
