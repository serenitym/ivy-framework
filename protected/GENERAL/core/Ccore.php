<?php
class Ccore extends Cunstable
{

    public $cssInc;
    public $jsInc;

    public $menus;  //array(idMenu= >'MenuName', ...)
    public $lang;
    public $lang2;
    public $langs = array();

    public $dsn = '';

    //===================[ modules ]============================================

    /**
     *  modulele utilizate  in cadrul proiectului
     *  retinute serializat in var/ tmp/ serUsedModules.txt
     * @var array mods['modName']['modType', 'admin', 'default', 'defaultAdmin', ];
     *
     */
    public $mods = array();
    public $modTypes = array();   // array('LOCATION1', 'LOCATION2', ...);
    public $adminMods = array();  // array('modName' =>  1, ..);

    public $GENERAL = array();   // array('modName');
    //public $MODELS = array();
    //public $PULGINS = array();
    //public $LOCALS = array();

    public $default_GENERAL = array(); // array('modName');
    //public $default_MODELS = array();
    //public $default_PULGINS = array();
    //public $default_LOCALS = array();

    public $defaultAdmin_GENERAL = array(); // array('modName');
    //public $defaultAdmin_MODELS = array();
    //public $defaultAdmin_PULGINS = array();
    //public $defaultAdmin_LOCALS = array();

    //==========================================================================
    // public $admin = false;

    public $mainTemplate = '';
    public $mainModel = '';

    public $tree;
    //public $tempTree;

    //======================================[ current node ]====================
    public $id;
    public $idTree;
    public $idNode;
    public $mgrName;
    public $mgrtype;
    public $nodeName;
    public $nodeName_ro;
    public $nodeName_en;
    public $nodeResFile;
    public $nodeChildren;
    public $nodeId;
    public $nodeIdParent;
    public $nodeLevel;


    //======================[ Methods ]=========================================

    /**
    * Instantziaza si seteaza /configureaza modulul curent
    *  - modulul curent = requested by type/moduleName || idNode=> type
    */
    public function Set_currentModule()
    {
        //$this->Module_Build($this->mgrName,$this->modType) ;
        //$this->Module_Build($this->modName,$this->modType) ;
        $this->Module_Build($this->mgrName, $this->mgrType);
    }

    /**
     * Sets default objects (modules) declared in yml files of core AND tmpl_core
     */
    public function Set_defaultModules()
    {
        foreach($this->modTypes AS $modType)
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
     *      modName        -
     *      id
     *      parentId        - id-ul parintelui
     *      resFile       - numele fisierului de resursa
     *      children    = array( [poz] => [Cid],... );
     *
     *
     * @param $children
     * @param string $parentId
     */
    public function Build_Db_tree($children, $idTree, $parentId='', $level=0)
    {
        foreach ($children AS $idCh) {
             $this->tempTree[$idCh] = new item();
             $node  = &$this->tempTree[$idCh];

             $query = "SELECT name_ro, name_en, type, opt
                       FROM ITEMS
                       WHERE id='$idCh' ";
             $qArr  = $this->DB->query($query)->fetch_assoc();

             $node->name    = $qArr['name_'.$this->langs[0]];
             $node->name_ro = $qArr['name_ro'];
             $node->name_en = $qArr['name_en'];
             // deprecated type
             $node->modName = $qArr['type'];

             // to be handled by the manager if it choses it to do so...
             $node->modOpt  = !$qArr['opt'] ? '' : json_decode($qArr['opt']);
             $node->id      = $idCh;
             $node->idParent= $parentId;

             // deprecated idT
             $node->idTree  = $idTree;
             $node->level   = $level;
             $node->resFile = str_replace(' ', '_', $node->name);

            //deprecated
            $node->type    = $qArr['type'];
            $node->idT     = $idTree;

             // afla modType pentru acest node
            if (in_array($node->modName, $this->MODELS)) {
                $node->modType = 'MODELS';
            } elseif (in_array($node->modName, $this->PLUGINS)) {
                $node->modType = 'PLUGINS';
            } elseif (in_array($node->modName, $this->LOCALS)) {
                 $node->modType = 'LOCALS';
            }


             // retine copii acestui nod
             $query    = " SELECT Pid,Cid,poz FROM TREE where Pid='$idCh' ORDER BY poz ASC ;";
             $queryRes = $this->DB->query($query);

             while ($ch_arr = $queryRes->fetch_assoc()) {
                 $node->children[ $ch_arr['poz'] ] = $ch_arr['Cid'];
             }
             //var_dump($node);
             // pentru fiecare copil al acestui node reapeleaza functia
             if ($queryRes->num_rows) {
                 $this->Build_Db_tree($node->children, $idTree, $idCh, $level+1);
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
          // echo 'ACcore SETtree pt tree-ul '.$idTree;  #var_dump($this->tempTree);
          $treeSer = serialize($this->tempTree);
          #umask(0777);
          //$succes  = file_put_contents($pathTree,$treeSer);
          $succes  = Toolbox::Fs_writeTo($pathTree, $treeSer);

          //if(defined('UMASK')) umask(UMASK);
          if (!$succes) {
              error_log( "[ ivy ]"
                      . "<b>Core - Set_Fs_tree :  Fail file_put_contents in </b>"
                      . " $pathTree <br>" );
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
          $pathTree = FW_RES_TREE.'tree'.$idTree.'.txt';

          if (is_file($pathTree)) {
              return  unserialize(file_get_contents($pathTree));

          } else {
	          // Build_Db_tree
	          // scrie tree-ul in res Set_Fs_tree
              $this->Build_Db_tree(array($idTree), $idTree);
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
                //echo "Ccore - Set_currentTree() : <br>";var_dump($this->tree);
                return true;

            } else {
                $this->tree = array();
                error_log("[ ivy ] ".'Ccore - Set_currentTree : Nu am reusit sa creez treeul');
                return false;
            }
        } else {
            error_log("[ ivy ] ".'core - Set_currentTree : Nu am nici un idTree');
            return false;
        }

    }

#=============================================[ ESETIALS ]======================

    # 1
    /**
     * Seteaza modulul curent (manager / mgr ) bazat pe $_REQUEST['mgrName']
     */
    private function _Set_currentModName()
    {
        // deprecated "modName" should be used instead
        if (isset($_REQUEST['mgrName'])) {
            $this->mgrName = $_REQUEST['mgrName'];
            // afla modType pentru acest node
            if(    in_array($this->mgrName,$this->MODELS )) $this->mgrType = 'MODELS';
            elseif(in_array($this->mgrName,$this->PLUGINS)) $this->mgrType = 'PLUGINS';
            elseif(in_array($this->mgrName,$this->LOCALS))  $this->mgrType = 'LOCALS';
        }
    }
    # 1
    /**
     * SET: idTree = primary parent, idNode = id ITEMS / page
     * @return bool
     */
    private function _Set_currentTreeNode()
    {
        if (isset($_GET['idT'])) {
               $this->idTree =   $_GET['idT'];
               $this->idNode = ( $_GET['idC'] ?  $_GET['idC'] : $this->idTree );
               error_log("[ ivy ] ".'Ccore - _Set_currentTreeNode :' .
                       "Se incearca setarea currentNode cu "
                        ."idTree = {$this->idTree} si idNode = {$this->idNode}"
               );
               return true;

        } elseif ($this->idNode) {
            return true;

        } else {
            error_log("[ ivy ] ".'Ccore - _Set_currentTreeNode :' .
                       ' Nu am reusit sa identific un node pentru tree');
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
    private function _Set_currentNode()
    {
        $curentNode         =  &$this->tree[$this->idNode];
        $this->nodeName_ro  =  &$curentNode->name_ro;  /*$this->name_ro*/
        $this->nodeName_en  =  &$curentNode->name_en;  /*$this->name_en*/

        $this->nodeName     =  &$curentNode->name;     /*$this->name*/
        $this->nodeResFile  =  &$curentNode->resFile;  /*$this->nameF*/


        $this->nodeChildren =  &$curentNode->children; /*$this->children*/
        $this->nodeId       =  &$curentNode->id;       /*$this->id*/
        $this->nodeIdParent =  &$curentNode->idParent; /*$this->idParent*/
        $this->nodeLevel    =  &$curentNode->level;    /*$this->level*/

        $this->mgrName      =  &$curentNode->modName;
        error_log("[ ivy ] "."Ccore - _Set_currentNode mgrName = $this->mgrName");
        $this->mgrType      =  &$curentNode->modType;
        error_log("[ ivy ] "."Ccore - _Set_currentNode mgrType = $this->mgrType");

    }

#===============================================================================
    /**
     * @todo: trebuie sa ma mai gandesc la integrarea ei
     *
     * Seteaza vectorul de module utilizare $this->mods
     * utilizat astfel:
     *
     * $this->mods[$modName]->modType
     * $this->mods[$modName]->admin
     * $this->mods[$modName]->default       - nu foarte utilizata
     * $this->mods[$modName]->defaultAdmin  - nu foarte utilizata
     */
    private function _Set_Fs_usedModules()
    {
        $path =    VAR_PATH.'/tmp/serUsedModules.txt';

        if (file_exists($path)) {
            $serMods = file_get_contents($path);
            $this->mods = unserialize($serMods);
            // daca vectorul nu a fost creat in modul admin => ii lipsesc setarile
            // din Acore.yml => trebuie recreat

            if ($this->admin && !isset($this->mods['adminYml'])) {
                unlink($path);
                $this->mods = array();
                $this->_Set_Fs_usedModules();
            }

        } else {
            // daca suntem in modul admin si nu este creat vectorul $this->mods
            // atunci in cream si inregistram ca este complet cu partea de admin
            if ($this->admin) {
                $this->mods['adminYml'] = true;
            }
             // construieste vectorul $mods
            if (isset($this->modTypes) && is_array($this->modTypes)) {
                foreach($this->modTypes AS $modType){
                    //seteaza modulele folosite in proiect
                    foreach($this->$modType AS $modName){
                        $this->mods[$modName] = new module($modType);

                        // seteaza daca modulul are sau nu admin
                        if (isset($this->adminMods[$modName])) {
                            $this->mods[$modName]->admin = 1;
                        }
                    }
                    // seteaza modulele default
                    foreach($this->{'default_'.$modType} AS $modName) {
                        if (isset($this->mods[$modName])) {
                            $this->mods[$modName]->default = 1;
                        }
                    }
                    foreach($this->{'defaultAdmin_'.$modType} AS $modName) {
                        if (isset($this->mods[$modName])) {
                            $this->mods[$modName]->defaultAdmin = 1;
                        }
                    }
                }
            } else {
                // cauta in baza de date dupa aceste setari
                // daca nu reuseste sa gaseasca nimic in BD ar trebui returnat un
                // error_log cu eroare
            }

            // scrie vectorul serializat in fisier
            if (count($this->mods) > 0) {
                $serMods = serialize($this->mods);
                $succes = file_put_contents($path, $serMods);
                if (!$succes) {
                    error_log("[ ivy ] "."Ccore - _Set_Fs_usedModules :" .
                            " Nu a putut scrie serializarea modulelor in fisier");
                }
            }
        }

       // var_dump($this->mods);
    }

    /**
     * Apel direct la un modul->metoda pentru rezolvarea requesturilor
     */
    public function Handle_postRequest()
    {

       // var_dump($_POST);
        if (isset($_POST['modName']) && isset($_POST['methName'])) {

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
                    if ($validData) {
                        $mod->{$methName}();

                        // =================[refresh page]======================
                        // nu imi e foarte clar de ce e pusa aceasta procedura
                        // in doua locuri
                        if($relocate){
                            $this->reLocate();
                        }
                    }
                    //safty reasons
                    unset($_POST);

                } else{
                    $mod->{$methName}();
                     // =================[refresh page]======================
                    if ($relocate) {
                        $this->reLocate();
                    }
                }

            } else {

                if (!is_object($this->$modName)) {
                    error_log("[ ivy ] "." Ccore - Handle_postRequest"
                            ."There is no object ".$modName);
                }
                if (!method_exists($this->$modName,$methName)) {
                    error_log("[ ivy ] "." Ccore - Handle_postRequest"
                            . " Object has no method ".$methName);
                }

            }

        } else {
         //   echo "No post modName or methName";
        }

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

        if ($this->_Set_currentTreeNode() &&  $this->Set_currentTree()) {
            $this->_Set_currentNode();
        } else {
            // pentru a initia un modul putem trimite "modName"
            //nu neapara idC || idTree
            $this->_Set_currentModName();
        }


        #================[ init All ]============================================

        $this->Set_defaultModules();
        #pus aici pentru ca intai trebuie initializata limba
        $this->SET_HISTORY($this->idNode);
        if ($this->mgrName) {
            //echo "Ccore - _init_modules : Current ModuleName is $this->mgrName <br>";
            $this->Set_currentModule();
        } else {
            error_log("[ ivy ] "."Ccore - _init_modules : "
                    . " Atentie nu este definit nici un modul manager!!!");
        }
        //var_dump($this->cssInc);
        //var_dump($this->jsInc);
        $this->Handle_postRequest();

    }

#===============================================================================

    # COMMENT THIS!!!
    function __construct($userData=NULL)
    {
        if(PROFILER == 1)
            $this->profiler = new PhpQuickProfiler(PhpQuickProfiler::getMicroTime());

       /**
         * DataBase connection
        */

        $this->mdb =& MDB2::singleton(DSN)
            ->setCharset('utf8');

        $this->DB = new IvyDb(DSN);

        //$this->DB = new mysqli('p:'.DB_HOST, DB_USER, DB_PASS, DB_NAME);

        echo  $this->DB->error;

        /**
         * GENERAL settings
         */
        #atentie daca nu are template o sa includa tot din core/js si core/css
        $this->modName = 'core';
        $this->modType = 'GENERAL';
        $this->Module_Fs_configYamlProps($this);          #seteaza variabilele personalizate
        $this->Module_Set_incFilesJsCss($this);
        //var_dump($this);

        /**
         * Local project settings
         */
       if (isset($this->mainModel) && isset($this->mainTemplate)) {
            #  Set_incFiles($modName,$modType,$extension,$folder='',$template='',$adminFolder='')
            $this->Set_incFiles($this->mainModel, 'LOCALS', 'css','', $this->mainTemplate);
            $this->Set_incFiles($this->mainModel, 'LOCALS', 'js','',  $this->mainTemplate);
       }



        /**
         * Set modul user
         * pentru ca obiectul nu este instantiat de core deci
         * nu are proprietatile necesare pentru templatind
         * modName, modType
        */
       if ($userData) {
            //$this->user = &$userData->user;
            if (isset($_SESSION['user'])) {
                error_log("[ ivy ] Ccore - _init_ exista SESSION['user']");
                $this->user = $_SESSION['user'];
                //echo "User prin session";
            } else {
                array_push($this->default_GENERAL,'user');
                //$this->Module_Build('user','GENERAL');
            }

        }


        // instantiaza modulele default + current
        $this->_init_modules();


        // set vectorul de module utilizate
        $this->_Set_Fs_usedModules();
        //var_dump($_SESSION['userData']);
        //var_dump($_SESSION['user']);

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
            $this->DB = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
            #echo "A fost apelat core wakeup si DB NU este connectat <br>";

        } else {
           // echo "A fost apelat core -> DB_reConnect este connectat <br>";
        }


    }
    public function __wakeup()
    {
       // echo "wakeup core ";
        $this->DB_reConnect();
        $this->Handle_postRequest(false);
    }
    public function __destruct()
    {
        if(PROFILER == 1)
            $this->profiler->display($this->DB);
    }
    public function __clone  ()
    { }
}
