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
    static function debugMess($mess)
    {
        return '';
    }

    # 3
    /**
    * Seteaza modulul curent
    *  - modulul curent = requested by type/moduleName || idNode=> type
    */
    public function Set_currentModule()
    {
        //$this->Module_Build($this->type,$this->modType) ;
        $this->Module_Build($this->modName,$this->modType) ;
    }

    # 3
    /**
     * Sets default objects (modules) declared in yml files of core AND tmpl_core
     */
    public function Set_defaultModules()
    {
        foreach($this->mods AS $modType)
        {
            foreach($this->{'default_'.$modType} AS $modName)
            {
                # error_log("Set_defaultModules ".'$modType = '.$modType.' $modName = '.$modName."\n\n");
                $this->Module_Build($modName,$modType);
            }
        }
    }

#=============================================[ set - TREE ]====================

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
     *      parentId        - id-ul parintelui
     *      nameF       - numele fisierului de resursa
     *      children    = array( [poz] => [Cid],... );
     *
     *
     * @param $children
     * @param string $parentId
     */
    public function Build_Db_tree($children, $parentId='', $idTree, $level=0)
    {
         foreach($children AS $idCh)
         {
             $this->tempTree[$idCh] = new item();
             //$node  = &$this->tempTree[$idCh];

             $query = "SELECT name_ro,name_en,type from ITEMS where id='$idCh' ;";
             $qArr  = $this->DB->query($query)->fetch_assoc();

             $this->tempTree[$idCh]->name    = $qArr['name_'.$this->langs[0]];
             $this->tempTree[$idCh]->name_ro = $qArr['name_ro'];
             $this->tempTree[$idCh]->name_en = $qArr['name_en'];
             // deprecated
             $this->tempTree[$idCh]->type    = $qArr['type'];
             $this->tempTree[$idCh]->modName = $qArr['type'];
             $this->tempTree[$idCh]->id      = $idCh;
             $this->tempTree[$idCh]->p_id    = $parentId;
             $this->tempTree[$idCh]->idTree  = $idTree;
             $this->tempTree[$idCh]->idT     = $idTree;
             $this->tempTree[$idCh]->level   = $level;
             $this->tempTree[$idCh]->nameF   = str_replace(' ','_',$this->tempTree[$idCh]->name) ;

             // retine copii acestui nod
             $query    = " SELECT Pid,Cid,poz FROM TREE where Pid='$idCh' ORDER BY poz ASC ;";
             $queryRes = $this->DB->query($query);

             while ($ch_arr = $queryRes->fetch_assoc()) {
                 $this->tempTree[$idCh]->children[ $ch_arr['poz'] ] = $ch_arr['Cid'];
             }
             var_dump($this->tempTree[$idCh]);
             // pentru fiecare copil al acestui node reapeleaza functia
             if ($queryRes->num_rows) {
                 $this->Build_Db_tree($this->tempTree[$idCh]->children,$idCh, $idTree, $level+1);
             }

         }
    }

    # 2
    /**
     * Returneaza un vector temporar al tree-ului cerut din BD
     * @param $pathTree     - calea unde ar trebui sa stea tree-ul
     * @param $idT          - id-ul treeului
     * @return mixed
     */
    public function Set_Fs_tree($pathTree, $idT)
    {
          //try si catch
          # echo 'ACcore SETtree pt tree-ul '.$idTree;  #var_dump($this->tempTree);
          $treeSer = serialize($this->tempTree);
          #umask(0777);
          $succes  = file_put_contents($pathTree,$treeSer);

          //if(defined('UMASK')) umask(UMASK);
          if (!$succes) {
              error_log( "<b>Set_Fs_tree -  Fail file_put_contents in </b> $pathTree <br>" );
          }
          return $this->tempTree;

      }

    # 3
    /**
     * Returneaza un vector deserializat al tree-ului curent
     *
     * STEPS:
     *  - daca se gaseste fisierul cu tree-ul serializat
     *  - daca nu se preia din BD(care creaza un vector temporar - deaceea trebuie unset)
     *
     * @param $idTree - id-ul treeului curent
     * @return mixed
     */
    public function Get_tree($idTree)
    {
          $pathTree = fw_resTree.'tree'.$idTree.'.txt';

          if (is_file($pathTree)) {
              return  unserialize(file_get_contents($pathTree));

          } else {
	          // Build_Db_tree
	          // scrie tree-ul in res Set_Fs_tree
              $this->Build_Db_tree(array($idTree), '', $idTree);
              //var_dump($this->tempTree);
              $tree =  $this->Set_Fs_tree($pathTree, $idTree);

              unset($this->tempTree);
              return $tree;
          }
    }

    # 4
    /**
     * Seteaza tree-ul curent bazat pe idTree - requested
     * @return bool - daca a reusit sau nu sa returneze tree-ul
     */
    public function Set_currentTree()
    {
        if ($this->idTree) {
            $this->tree = $this->Get_tree($this->idTree);

            if (is_array($this->tree)) {
                return true;

            } else {
                $this->tree = array();
                error_log('core->Set_currentTree : Nu am reusit sa creez treeul');
                return false;
            }
        } else {
            error_log('core->Set_currentTree : Nu am nici un idTree');
            return false;
        }

    }

#=============================================[ ESETIALS ]======================

    # 1
    public function Set_currentModName()
    {
        // deprecated "modName" should be used instead
        if(isset($_REQUEST['type']))$this->type = $_REQUEST['type'];
        // pentru a nu se confunda cu modName trimis asincron de JS
        if(isset($_REQUEST['moduleName']))$this->modName = $_REQUEST['moduleName'];
    }
    # 1
    /**
     * SET: idTree = primary parent, idNode = id ITEMS / page
     * @return bool
     */
    public function Set_currentTreeNode()
    {
        if (isset($_GET['idT'])) {
               $this->idTree =   $_GET['idT'];
               $this->idNode = ( $_GET['idC'] ?  $_GET['idC'] : $this->idTree );

               return true;

        } elseif ($this->idNode) {
            return true;

        } else {
            error_log('core-> Set_currentTreeNode : Nu am reusit sa identific
                un node pentru tree');
            return false;
        }

    }
    # 1
    /**
     * Seteaza nodul curent
     *
     * @todo: ma gandesc ca toate aceste proprietati poate ar trebui sa
     * stea intr-un obiect gen $this->current
     */
    public function Set_currentNode()
    {
        $curentNode      = &$this->tree[$this->idNode];
        $this->name_ro   = &$curentNode->name_ro;
        $this->name_en   = &$curentNode->name_en;

        $this->nameF     = &$curentNode->nameF;
        $this->name      = &$curentNode->name;

        // deprecated use modName instead
        $this->type      = &$curentNode->type;
        $this->modName   = &$curentNode->modName;
        $this->children  = &$curentNode->children;
       /* $this->new       = &$this->tree[$this->idNode]->new;*/
        $this->id        = &$curentNode->id;
        $this->p_id      = &$curentNode->p_id;
        $this->level     = &$curentNode->level;

         if(    in_array($this->type,$this->models )) $this->modType = 'MODELS';
         elseif(in_array($this->type,$this->plugins)) $this->modType = 'PLUGINS';
         elseif(in_array($this->type,$this->locals))  $this->modType = 'LOCALS';

    }

#===============================================================================

    public function _handle_postRequest()
    {

       // var_dump($_POST);
        if(isset($_POST['modName']) && isset($_POST['methName']))
        {

            $modName    = $_POST['modName'];
            $methName   = $_POST['methName'];
            $relocate   = isset($_POST['relocate']) ? $_POST['relocate'] : true ;

            if(is_object($this->$modName) && method_exists($this->$modName,$methName))
            {
                $mod = &$this->$modName;
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

             /*   if(!is_object($this->$modName))
                    echo "There is no object ".$modName;
                if(! method_exists($this->$modName,$methName))
                    echo " with method ".$methName;*/

            }

        }
        else {
         //   echo "No post modName or methName";
        }

       // echo "<b>modName</b> ".$_POST['modName']." <b>methName</b> ".$_POST['methName'];
    }

    #1  - A | use:
    /**
     *LOGISTICS
     *
       *
       *  - try to set the type property
       *  - if idTree & idC exists => a tree[idTree].txt should exist in /public/GENERAL/core/RES_TREE
       *  - from that tree we should be albe to determine the current item with all of its properties
       *
       *  - if a type is set - set requested module
       *
       *  - sets the default mod.'s     => le instantiaza obiectele si seteaza tagurile  js/css aferente ;
      */
    public function _init_modules()
    {
        #================[ set current tree & module ]==========================

        if ($this->Set_currentTreeNode() &&  $this->Set_currentTree()) {
            $this->Set_currentNode();
        }
        // pentru a initia un modul putem trimite "modName" nu neapara idC || idTree
        $this->Set_currentModName();


        #================[ ini All ]============================================

        $this->Set_defaultModules();
        #pus aici pentru ca intai trebuie initializata limba
        $this->SET_HISTORY($this->idNode);
        //if($this->type)  $this->Set_currentModule();
        if ($this->modName) {
            $this->Set_currentModule();
        }

        $this->_handle_postRequest();

    }

#===============================================================================

    # COMMENT THIS!!!
    function __construct($auth=NULL)
    {
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
        if ($auth) {
            $this->user = &$auth->user;
            $this->user->modName = 'user';
            $this->user->modType = 'GENERAL';
        }

        /**
         * Local project settings
         */
        if (isset($this->mainModel) && isset($this->mainTemplate)) {
            #  Set_incFiles($modName,$modType,$extension,$folder='',$template='',$adminFolder='')
            $this->Set_incFiles($this->mainModel, 'LOCALS', 'css','', $this->mainTemplate);
            $this->Set_incFiles($this->mainModel, 'LOCALS', 'js','',  $this->mainTemplate);
        }

        $this->_init_modules();
        #echo 'Ccore: __construct';
        #var_dump($this);
    }


    /**
     * ATENTIE
     *  - aceasta functie este apelata din interiorul __wakeup-ului altor module
     * de ce nu se creaza o metoda de wakeup a core-ului care sa apeleze aceasta metoda??
     *
    */
    public function DB_reConnect ()
    {
        /**
         * DE ce nu unset($this->DB) - explicatie
         * nu am dat unset pentru ca se va pierde locatia din memorie a lui DB
         * => obiectele care au pointer la $this->DB vor da in gol
         * deci degeaba recreez eu conexiunea pentru ca aceasta ar fii
         * valabila doar pentru core*/
        $DBstat = $this->DB->ping();
        if ($DBstat == FALSE) {
            $this->DB = '';
            $this->DB = new mysqli(dbHost,dbUser,dbPass,dbName);
            #echo "A fost apelat core wakeup si DB NU este connectat <br>";

        } else {
            echo "A fost apelat  este connectat <br>";
        }


    }
    public function __wakeup()
    {
        $this->DB_reConnect();
        $this->ctrl_postRequest(false);
    }
    public function __destruct()
    {
        if(PROFILER == 1)
            $this->profiler->display($this->DB);
    }
    public function __clone  ()
    { }
}
