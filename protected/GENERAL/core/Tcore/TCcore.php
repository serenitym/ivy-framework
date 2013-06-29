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




//class TCcore extends item
class TCcore
{
    use TrenderTmpl;
    use TmethDB;
    use TgenTools;
    use TManage

# manage function - should be put in TgenTools respectiv create ATgenTools
    static function debugMess($mess){ return '';}

#=============================================[ incTags ]===============================================================

    # incTags

    # 1
    public function GET_INCtag_js($SRC_path)  {
        return "<script type='text/javascript'  src='".$SRC_path."'></script>"."\n";}
    # 1
    public function GET_INCtag_css($SRC_path) {
        return "<link rel='stylesheet' href= '".$SRC_path."'  />"."\n";}

    # 2
    public function GET_INC_htmlTags($extension, $ext_PATH, $ext_SRC_PATH){

        //if(method_exists($this,"GET_INCtag_".$extension))
         $tags = '';


         if(  is_dir($ext_PATH) )
         {
             $dir = dir($ext_PATH);

             while(false!== ($file=$dir->read()) )
             {
                 $arr_file = explode('.',$file);
                 if( end($arr_file) ==$extension  )
                        $tags .= $this->{"GET_INCtag_".$extension}($ext_SRC_PATH.$file);
             }

             return $tags;
         }

        return '';
    }

    # 3 - A
    /**
     * utilizata cand se doreste css-ul sau js-ul unui anumit model (istantiat sau NEinstantiat)
     *  - tagul de includere a fisierului va fi retinut in $this->INC_[extension]
     *
     *
     * @param $modName        - modulul de la care se doresc preluate fisierule cu extensia ceruta
     * @param $modType        - tipul modelului GENERAL / MODELS /PLUGINS
     * @param $extension       - extensia ex: js/ css
     * @param string $folder   - folderul din cadrul caruia sa fie preluate fisierele cu extensia ceruta
     * @param string $template - templateul daca este necesar
     * @param string $adminFolder - ADMIN
     */
    public function SET_INC_ext($modName,$modType,$extension,$folder,$template='',$adminFolder='') {


            if($folder=='') $folder = $extension;
            # $tmpl =/ [tmpl_name] /
            $tmpl      =  $template ? 'tmpl_'.$template.'/' : '';  #daca s-a trimis un template modelul are un template
           // $adminFolder .=  $adminFolder ? '/' : '';

            $ext_PATH         =  fw_pubPath.$modType.'/'.$modName.'/'.$tmpl.$adminFolder."$folder/";
            $ext_SRC_PATH     =   fw_pubURL.$modType.'/'.$modName.'/'.$tmpl.$adminFolder."$folder/";

            #echo $ext_SRC_PATH.'<br>';
            $this->{"INC_".$extension} .= $this->GET_INC_htmlTags($extension,$ext_PATH,$ext_SRC_PATH);



    }

    # 4
    /**
     * Automatic hmtl tag inclusion for an object
     * @param $obj
     * @param $extension
     * @param string $folder
     * @param string $adminFolder
     */
    public function SET_INC_extObj(&$obj, $extension,$folder,$adminFolder=''){

        $template = isset($obj->template) ? $obj->template : '';

        $this->SET_INC_ext($obj->modName,$obj->modType,$extension,$folder,$template,$adminFolder);

        /**
         * daca obiectul are setat un template file atunci se va cauta un
         * path = modType/ modName/ tmpl_tmplName/ js/ js_templateFileName/ ....js
        */
        if(isset($obj->template_file))
        {
             $folder = $folder."/"."{$folder}_".$obj->template_file;
             $this->SET_INC_ext($obj->modName,$obj->modType,$extension,$folder,$template,$adminFolder);
        }

    }

    # 5
   /**
     * Automatic html tag css/js inclusion for object
     *
     * @param $obj
     * @param string $folder
     * @param string $adminFolder
     */
   public function SET_INC_extObj_jsCss(&$obj,$adminFolder=''){

        $this->SET_INC_extObj($obj,'js','js',$adminFolder);
        $this->SET_INC_extObj($obj,'css','css',$adminFolder);
    }

   //=====================[hard INCS]============================

   #1
   public function SET_INC_hardAdd($extension,$srcPath){

        $this->{"INC_".$extension} .= $this->{"GET_INCtag_".$extension}($srcPath);
    }

   #2
   public function SET_INC_assetsObj($obj){

       # default assets   $obj->INC_assets[js / css]
      if( isset($obj->assetsIC) )
      foreach($obj->assetsInc AS $extension)
           foreach($extension AS $srcPath)
                $this->SET_INC_hardAdd($extension, $srcPath);

     /**
     * Assets for a template file  $obj->INC_assets_tmplF[ template_file ] [js / csss]
     */
     if( isset($obj->template_file)
          && isset($obj->{'assetsInc_'.$obj->template_file}) )
     {
             $tmplFile_assets = &$obj->{'assetsInc_'.$obj->template_file};

             foreach($tmplFile_assets AS $extension => $paths)
                 foreach($paths AS $srcPath)
                     $this->SET_INC_hardAdd($extension, $srcPath);

     }



   }




#============================================[ objConf ]================================================================

    # 1
    static function GETconf(&$obj,$file_yml, $modName='' ){

        // ATENTIE!!! -- La ce mai este nevoie de $modName?
        # functie pentru popularea unui obiect cu date dintr-un fisier de config yaml

        if(file_exists($file_yml))
        {
            #===========================================================================================================
            /**
             * setarea proprietatilor pentru obiectul $obj
             * daca obiectul are deja setat un array atunci configul va adauga la acel array
             * daca nu va adauga ca proprietate noua
             */
            $yml_array = Spyc::YAMLLoad($file_yml);
            #var_dump($yml_array);

            foreach($yml_array AS $var_name => $var_value)
            {
                if(isset($obj->$var_name) && is_array($obj->$var_name) && count($obj->$var_name) > 0)
                    $obj->$var_name = array_merge($obj->$var_name,$var_value);

                else $obj->$var_name = $var_value;

            }


            #===========================================================================================================
            /**
             * include yaml IN yaml
             * daca fisierul yaml contine un vector "include" cu path-uri atunci la el se adauga incPath si se reapeleaza aceasta fct
             * configurile acelui yaml vor fii atribuite obiectului curent
             */


            $incFile_yml = 'incFisier';
            if(isset($yml_array['include']) && is_array($yml_array['include']))
            {
                foreach($yml_array['include'] AS $incFile_yml) {
                    # echo 'inluded file '.$incFile_yml."<br>";
                    self::GETconf($obj,incPath.$incFile_yml, $modName );
                }
            }

            #===========================================================================================================


            error_log('fisierul EXISTA '.$file_yml."\n\n"); #echo 'fisierul '.$file_yml.' EXISTA <br>';
            return true;
        }

        else {
            error_log('fisierul nu exista '.$file_yml."\n\n"); # echo 'fisierul '.$file_yml.' nu exista <br>';
            return false;
        }


    }

    # deprecated
    /**
       * public function Module_Fs_configYamlProps(&$obj,$modType, $modName,$admin='',$template='')     {
       *

           $file_yml =  incPath.'etc/'
                               .$modType.'/'
                                   .$modName.'/'
                                       .($template=='' ?
                                                   $admin.$modName.'.yml':
                                           'tmpl_'.$admin.$template.'.yml');


           $this->Module_configYamlProps($obj, $file_yml, $modName);

           #===========================================================================================================
           # 2
           if(isset($obj->template) && $obj->template!=''  && $template == '' )
               self::Module_Fs_configYamlProps($obj,$modType,$modName,$admin,$obj->template);

       }*/

    # 2 - A
    /**
     * configurarea obiectelor via yaml
     *
     * # 2
     *  daca in configul modelului gaseste declarat un template
     *  atunci incearca sa vada daca nu cumva acel template are si el un config - tmpl_[A][templateName].yml
     *
     * @param $obj
     * @param string $admin      [A / '']
     * @param string $template  - numele templateului
     */
    public function GET_objCONF(&$obj,$admin='',$template='')     {

        $modType = $obj->modType;
        $modName = $obj->modName;
        #===========================================================================================================

        $file_yml =  incPath.'etc/'
                            .$modType.'/'
                                .$modName.'/'
                                    .($template=='' ?
                                                $admin.$modName.'.yml':
                                        'tmpl_'.$admin.$template.'.yml');


        $this->GETconf($obj, $file_yml, $modName);

        #===========================================================================================================
        # 2
        if(isset($obj->template) && $obj->template!=''  && $template == '' )
            self::GET_objCONF($obj,$admin,$obj->template);

    }



#============================================[ objReq ]================================================================

    # 1
    /**
     * proprietati adaugate la orice obiect [model]
     * @param $obj
     * @param $modType
     * @param $modName
     */
    public function SET_objStandardREQ(&$obj,$modType,$modName){

        $obj->C      =  &$this;
        # situatie core
        $obj->DB     =  &$this->DB;
        $obj->admin  =  &$this->admin;
        $obj->LG     =  &$this->lang;
        $obj->lang   =  &$this->lang;
        $obj->nameF  =  &$this->nameF;


        # date ale modulului curent
        $obj->idC    =  &$this->idC;
        $obj->idT    =  &$this->idT;
        $obj->level  =  &$this->level;
        $obj->type   =  &$this->type;


        #date despre acest modul
        $obj->modName = $modName;
        $obj->modType = $modType;


        #error_log('modName '.$modName."\n\n");
    }

    /**
     * Setarea proprietatilor in plus din core sau din alte module
     * @param $obj
     * @param $objREQ = ['modName': 'varName1', 0: 'varName2']
     *     array cu numele variabilelor dorite din CsetINI
     *     sau dintr-un anumit model ex model: nume variabila  sau model:[var1, var2]
     *     daca key-ul nu este string atunci se cauta variabila in core
     */
    public function SET_objExternalREQ(&$obj, $objREQ){


        foreach($objREQ AS $key=>$propName){
            if(is_string($key)) {
                # atunci se cere obiectul cu numele key si cu prop propName
                if(is_array($propName)){
                    # daca $propName este un array atunci inseamna ca se doresc mai multe
                    # proprietati ale obiectului cu numele  $key
                    foreach($propName AS $subPropName)
                        $obj->$subPropName = &$this->$key->$subPropName;
                }
                else{
                    $obj->$propName = &$this->$key->$propName;
                }
            }
            else $obj->$propName = &$this->$propName;

            #echo $key.' '.var_dump($propName).'<br>';

        }

    }

    # 2 + #2 - objConf
    /**
     * standard confing of a model REQ, CONF, obj-> [ objREQ, _setINI() ]
     *
     * STEPS:
     *  - setarea proprietatilor standard
     *  - citirea configului yml ( redirectat catre cel de admin so we are all right)
     *  - [opt] Setarea a proprietatilor in plus din core sau din alte module
     *  - [opt] Apelarea unui second construct _setINI al obiectului (daca metoda exista)
     *          util pentru procesele care depind de configurilea modulului
     *
     * @param $obj
     * @param $modType
     * @param $modName
     */
    public function GET_objREQ(&$obj,$modType,$modName)   {


        # i dont know if this is really necessary
        /*if($res) $obj->RESpath = $this->GET_resPath($this->modType,
                                                    '',
                                                    $this->type,
                                                    $this->nameF,
                                                    $this->lang);*/

        $this->SET_objStandardREQ($obj,$modType,$modName);

        $this->GET_objCONF($obj);

        if(isset($obj->objREQ))
         $this->SET_objExternalREQ($obj,$obj->objREQ);

        if(method_exists($obj,"_setINI"))  $obj->_setINI();
        elseif(method_exists($obj,"_init"))  $obj->_init();
         //__init
     /*   else{
            if($obj->modName == 'single')
            echo "Module_config obiectul $obj->modName nu are _setINI()";
        }*/


        #TODO: atentie la admin!!!

    }



#================================================[ objIni]==============================================================

    # 1 + #2objReq
    /**
     * SET:  $this->modName;
     *
     * USE: GENERAL: $this->modName->display();
     *      CURRENT: $this->{$this->type}->display();
     */
    public function SET_OBJ_mod($modName,$modType,$adminPrefix='C',$adminFolder='')      {

        # set REQUIERD objects   $OB_name = CmodName or $OB_name= CAmodName (admin, if it has one);


        $OB_name = $adminPrefix.$modName;


        if(file_exists(fw_incPath.$modType."/$modName/".$adminFolder.$OB_name.'.php'))
        {
            $this->$modName = new $OB_name($this);
            # echo fw_incPath.$modType."/$modName".$adminFolder."/".$OB_name.'.php'."<br/>";
            $this->GET_objREQ($this->$modName,$modType,$modName);
            # preia si seteaza toate cele necesare pentru respectivul model
            # exemplu: seteaza configurarea lui din etc, ii seteaza cateva variabile utile cum ar fii DB, lang, LG, nameF
            # si incearca sa gaseasca o metoda set INI care actioneaza ca un al doilea construct


            # return $this->$modName;

            Console::logSpeed($OB_name);
            return true;  #obiectul a fost creat
        }

        else
        {
            Console::logSpeed($OB_name);
            return false;
        }
        /*elseif( file_exists(fw_pubPath.'MODELS/'.$modName.'/RES/TMPL_'.$modName.'.html') )
            $this->$modName = new Cmodel($modName,$this);*/


    }

    # 2  + incTags #5  - A
    /**
     * Instantierea unui obiect cu tot ce ii trebuie + css, js html tags for inclusion
     * @param $modName
     * @param $modType
     * @param string $adminFolder
     * @param string $adminPrefix
     * @return bool
     */
    public function SET_general_mod($modName,$modType,$adminFolder='',$adminPrefix='C')   {

        # daca obiectul nu a fost setat

        if(!isset($this->$modName)){

            $objectCreat_stat =  $this->SET_OBJ_mod($modName,$modType,$adminPrefix,$adminFolder);

            if( $objectCreat_stat )
            {
                $this->SET_INC_extObj_jsCss($this->$modName,$adminFolder);
                $this->SET_INC_assetsObj($this->$modName);
            }

            return $objectCreat_stat; #daca a fost sau nu creat obiectul
        }
        else
            return false;



       /* var_dump($modName);*/
}

    # 3
    /**
    * Seteaza obiectul curent
    *  - obiect curent = requested by type || idC=> type
    */
    public function SET_current()        {

        if(!is_object($this->type))
            $this->SET_general_mod($this->type,$this->modType) ;

       }

    # 3
    /**
     * Sets default objects (modules) declared in yml files of core AND tmpl_core
     */
    public function SET_default()        {



        foreach($this->mods AS $modType)
            foreach($this->{'default_'.$modType} AS $modName)
            {
                # error_log("SET_default ".'$modType = '.$modType.' $modName = '.$modName."\n\n");
                $this->SET_general_mod($modName,$modType);
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

            if(is_object($this->$moduleName) && method_exists($this->$moduleName,$methName))
            {
                $obj = &$this->$moduleName;
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
                if(method_exists($obj,'_hook_'.$methName))
                {
                    $validData =$obj->{'_hook_'.$methName}();
                    if($validData)
                    {

                        $obj->{$methName}();

                        unset($_POST);
                        // =================[refresh page]======================

                        $this->reLocate();
                    }
                    //safty reasons
                    unset($_POST);

                } else{
                    $obj->{$methName}();
                    unset($_POST);
                     // =================[refresh page]======================
                    $this->reLocate();
                }

            }
            else{

             /*   if(!is_object($this->$moduleName))
                    echo "There is no object ".$moduleName;
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
    public function CONTROL_setINI()     {

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
        $this->GET_objCONF($this);          #seteaza variabilele personalizate
        $this->SET_INC_extObj_jsCss($this);


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
            $this->SET_INC_ext($this->mainModel, 'LOCALS', 'css','', $this->mainTemplate);
            $this->SET_INC_ext($this->mainModel, 'LOCALS', 'js','',  $this->mainTemplate);
        }




        $this->CONTROL_setINI();

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


    public function __clone  () { }
}
