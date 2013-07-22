<?php
/**
 * Usage:
 * **Create object Module**
 *
 * **proprietati magice**
 *  + resPath
 *  + displayPage
 *  + displayPathRes
 * * **metode magice**
 *  + _init_
 *  + _setRes_
 *  + _render_()
 *
 * **Setting properties for Module**
 *  - configCorePointers
 *      + DB
 *      + admin
 *      + LG
 *      + lang
 *      + nodeResFile
 *      + idTree
 *      + mgrName
 *
 *  - configAttributes
 *      + modName
 *      + modType
 *      + modDir  = modType / modName
 *
 *  - FS_configYamlProps
 *      + objREQ  = daca se doresc proprietati ale altor module
 *      + include = daca se doreste citirea unui alt yaml
 *      + template
 *      + template_file
 *
 *      + assetsInc['js'] = array ('paths to other js like: /assets/...js');
 *      + assetsInc['css']
 *          - alte js-uri , css-uri de inclus
 *
 *      + assetsInc_{tmplFileName}['js', 'css']
 *       - alte js-uri , css-uri de inclus in functie de un template_file
 *
 * automatizari speciale:
 *   - module->_init_()
 *      - apelarea unei metode "magice" daca exista , dupa ce modulul a fost setat
 *   -_setRes_($resPath)
 *        - care ar trebui sa seteze o variabila resPath
 *
 */
class CsetModule extends CgenTools
{

    #=============================================[ incHtmlTags ]===============
    # 1
    public function Get_IncTag_js($SrcPath)
    {
        //echo "Get_IncTag_js = $SrcPath <br>";
        return "<script type='text/javascript'  src='".$SrcPath."'></script>"."\n";
    }
    # 1
    public function Get_IncTag_css($SrcPath)
    {
        //echo "Get_IncTag_css = $SrcPath <br>";
        return "<link rel='stylesheet' href= '".$SrcPath."'  />"."\n";
    }
    # 2
    public function Get_incHtmlTags($extension, $extPath, $extSrcPath)
    {

        //if(method_exists($this,"GET_INCtag_".$extension))
        // echo "Get_incHtmlTags tring to get  =  $extSrcPath".'<br>';

        if (!method_exists($this,"Get_IncTag_".$extension)) {

           // echo 'Get_incHtmlTags no method = '."Get_IncTag_".$extension.'<br>';
            return '';

        } else {
            //echo "<br> Get_incHtmlTags cu extPath = $extPath <br>";
            $tags = '';
            if (is_dir($extPath)) {
                $dir = dir($extPath);
                while(false!== ($file=$dir->read()) )
                {
                    $arr_file = explode('.',$file);
                    //echo "file found in $file <br>";

                    if (end($arr_file) == $extension) {
                        $tags .= $this->{"Get_IncTag_".$extension}($extSrcPath.$file);
                       /* echo "$tags <br> <b>good tag </b> in ".$extSrcPath.$file
                            ." <br> <b>functie aplicata</b> =  "."Get_IncTag_".$extension.'<br>';*/
                    }
                }
                return $tags;
            }
        }

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
    public function Set_incFiles($modName,$modType,$extension,$folder,$template='',$adminFolder='')
    {
         if ($folder=='') {
            $folder = $extension;
         }
         # $tmpl =/ [tmpl_name] /
         $tmpl      =  $template ? 'tmpl_'.$template.'/' : '';  #daca s-a trimis un template modelul are un template
         // $adminFolder .=  $adminFolder ? '/' : '';

         $ext_PATH         =   FW_PUB_PATH.$modType.'/'.$modName.'/'.$tmpl.$adminFolder."$folder/";
         $ext_SRC_PATH     =   FW_PUB_URL.$modType.'/'.$modName.'/'.$tmpl.$adminFolder."$folder/";

         $htmlTag = $this->Get_incHtmlTags($extension,$ext_PATH,$ext_SRC_PATH);
         $this->{$extension."Inc"} .= $htmlTag;
        //  echo "Set_incFiles - modName =  $modName  && htmltag = ".$htmlTag.'<br>';


    }
    # 4
    /**
     * Automatic hmtl tag inclusion for an object
     * @param $obj
     * @param $extension
     * @param string $folder
     * @param string $adminFolder
     */
    public function Module_Set_incFiles(&$obj, $extension,$folder,$adminFolder='')
    {

        $template = isset($obj->template) ? $obj->template : '';

        $this->Set_incFiles($obj->modName,$obj->modType,$extension,$folder,$template,$adminFolder);

        /**
         * daca obiectul are setat un template file atunci se va cauta un
         * path = modType/ modName/ tmpl_tmplName/ js/ js_templateFileName/ ....js
        */
        if (isset($obj->template_file)) {
             $folder = $folder."/"."{$folder}_".$obj->template_file;
             $this->Set_incFiles($obj->modName,$obj->modType,$extension,$folder,$template,$adminFolder);
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
   public function Module_Set_incFilesJsCss(&$obj,$adminFolder='')
   {

        $this->Module_Set_incFiles($obj,'js','js',$adminFolder);
        $this->Module_Set_incFiles($obj,'css','css',$adminFolder);
   }

   #============================================[hard includes]=================
   #1
   public function Module_Set_incFilesHard($extension,$srcPath)
   {
       if (method_exists($this,"Get_IncTag_".$extension)) {
           $this->{$extension."Inc"} .= $this->{"Get_IncTag_".$extension}($srcPath);
       }
   }
   #2
   public function Module_Set_incFilesAssets($mod)
   {
      # default assets   $mod->INC_assets[js / css]
      if (isset($mod->assetsInc)) {
          foreach($mod->assetsInc AS $extension => $paths){
              foreach($paths AS $srcPath) {
                  $this->Module_Set_incFilesHard($extension, $srcPath);
              }
          }
      }

     /**
     * Assets for a template file  $mod->INC_assets_tmplF[ template_file ] [js / csss]
     */
     if ( isset($mod->template_file)
         && isset($mod->{'assetsInc_'.$mod->template_file})
     ) {
            $tmplFile_assets = &$mod->{'assetsInc_'.$mod->template_file};

            foreach($tmplFile_assets AS $extension => $paths)
                foreach($paths AS $srcPath)
                    $this->Module_Set_incFilesHard($extension, $srcPath);

     }



   }

    #=========================================[ Yaml config]====================
    # 1
    /**
     * **1.populeaza obiectul** $mod cu date dintr-un fisier de config yaml
     * daca obiectul are deja setat un array atunci configul va adauga la acel array
     * daca nu va adauga ca proprietate noua
     *
     * **2.include yaml IN yaml**
     * daca fisierul yaml contine un vector "include" cu path-uri atunci la el se adauga INC_PATH si se reapeleaza aceasta fct
     * configurile acelui yaml vor fii atribuite obiectului curent
     *
     * @param $mod obiectul modul
     * @param $filePathYml file path catre fisierul de config al yml-ului
     * @return bool
     */
    static function Module_configYamlProps(&$mod,$filePathYml)
    {
        if (file_exists($filePathYml)) {

            $yml_array = Spyc::YAMLLoad($filePathYml);
            #var_dump($yml_array);

            #1
            foreach($yml_array AS $var_name => $var_value)
            {
                $notEmptyArray = (  isset($mod->$var_name)
                                 && is_array($mod->$var_name)
                                 && count($mod->$var_name) > 0 );

                $mod->$var_name = $notEmptyArray
                                  ? array_merge($mod->$var_name,$var_value)
                                  : $var_value;
            }

            #2
            if (isset($yml_array['include']) && is_array($yml_array['include'])) {
                foreach($yml_array['include'] AS $incFile_yml)
                {
                    # echo 'inluded file '.$incFile_yml."<br>";
                    self::Module_configYamlProps($mod,INC_PATH.$incFile_yml);
                }
            }

            #===================================================================

            if (defined('DEBUG') && DEBUG == 1) {
                error_log("[ ivy ] ".'File is present: '.$filePathYml);
            }

            return true;

        } else {
            if (defined('DEBUG') && DEBUG == 1) {
                error_log("[ ivy ] ".'File is not present: '.$filePathYml);
            }
            return false;
        }


    }
    # 2 - A
    /**
     * **configurarea obiectelor via yaml**
     *
     * # 2
     *  daca in configul modelului gaseste declarat un template
     *  atunci incearca sa vada daca nu cumva acel template are si el un config - tmpl_[A][templateName].yml
     *
     * @param $mod
     * @param string $adminFolder      [A / '']
     * @param string $template  - numele templateului
     */
    public function Module_Fs_configYamlProps(&$mod, $adminPrefix='', $template='')
    {
        $modType = $mod->modType;
        $modName = $mod->modName;

        $filePathYml = INC_PATH . 'etc/'
                     . $modType . '/'
                     . $modName . '/'
                     . ($template == ''
                        ? $adminPrefix . $modName . '.yml'
                        : 'tmpl_' . $adminPrefix . $template . '.yml');

        $this->Module_configYamlProps($mod, $filePathYml);

        #2
        $templateExists = (  isset($mod->template)
                          && $mod->template!=''
                          && $template == '' );

        if ($templateExists) {
            self::Module_Fs_configYamlProps($mod,$adminPrefix,$mod->template);
        }

    }

    #=========================================[ Module Configuration ]==========
    # 1
    /**
     * proprietati adaugate la orice obiect [model]
     * @param $mod
     * @param $modType
     * @param $modName
     */
    public function Module_configCorePointers(&$mod)
    {

        $mod->C      =  &$this;
        # situatie core
        $mod->DB     =  &$this->DB;
        $mod->admin  =  &$this->admin;
        //$mod->LG     =  &$this->lang;
        $mod->lang   =  &$this->lang;
        $mod->tree   =  &$this->tree;


        # date ale modulului curent
        $mod->idNode      =  &$this->idNode;
        $mod->idTree      =  &$this->idTree;

        $mod->nodeLevel   =  &$this->nodeLevel;
        $mod->nodeResFile =  &$this->nodeResFile;
        // acelasi lucru cu modName
        $mod->mgrName =  &$this->mgrName;
        $mod->mgrType =  &$this->mgrType;


    }

    public function Module_configAttributes(&$mod,$modType,$modName)
    {
        #date despre acest modul
        $mod->modName = $modName;
        $mod->modType = $modType;
        $mod->modDir  = $modType.'/'.$modName.'/';


        #error_log("[ ivy ] ".'modName '.$modName."\n\n");

    }

    /**
     * Setarea proprietatilor in plus din core sau din alte module
     * @param $mod
     * @param $objREQ = ['modName': 'varName1', 0: 'varName2']
     *     array cu numele variabilelor dorite din CsetINI
     *     sau dintr-un anumit model ex model: nume variabila  sau model:[var1, var2]
     *     daca key-ul nu este string atunci se cauta variabila in core
     */
    public function Module_configExternalPointers(&$mod, $objREQ)
    {
        foreach($objREQ AS $key=>$propName)
        {
            if (is_string($key)) {
                # atunci se cere obiectul cu numele key si cu prop propName
                if (is_array($propName)) {
                    # daca $propName este un array atunci inseamna ca se doresc mai multe
                    # proprietati ale obiectului cu numele  $key
                    foreach($propName AS $subPropName)
                        $mod->$subPropName = &$this->$key->$subPropName;

                } else {
                    $mod->$propName = &$this->$key->$propName;
                }
            } else {
                $mod->$propName = &$this->$propName;
            }
            #echo $key.' '.var_dump($propName).'<br>';
        }

    }
    # 2 + #2 - objConf
    /**
     * standard confing of a model REQ, CONF, mod-> [ objREQ, _init_() ]
     *
     * STEPS:
     *  - setarea proprietatilor standard ( pointeri la prop ale modulului principal ( core )
     *  - citirea configului yml ( redirectat catre cel de adminFolder )
     *  - objREQ [opt] Setarea a proprietatilor in plus din core sau din alte module
     *
     * @param $mod
     * @param $modType
     * @param $modName
     */
    public function Module_config(&$mod,$modType,$modName)
    {
        # i dont know if this is really necessary
        /*if($res) $mod->RESpath = $this->GET_resPath($this->modType,
                                                    '',
                                                    $this->mgrName,
                                                    $this->nodeResFile,
                                                    $this->lang);*/

        $this->Module_configCorePointers($mod);
        $this->Module_configAttributes($mod,$modType,$modName);
        $this->Module_Fs_configYamlProps($mod);

        if (isset($mod->objREQ)) {
            $this->Module_configExternalPointers($mod,$mod->objREQ);
        }

        #TODO: atentie la adminFolder!!!

    }

    #=============================================[ Module initialization]======
    # 1
    /**
     * seteaza requierments pentru modulul
     * - config yml
     * - js / css tags
     *
     * @param $modName
     * @param $modType
     * @param string $adminFolder
     */
    public function Module_Set($modName,$modType,$adminFolder='')
    {
        $this->Module_config($this->$modName,$modType,$modName);
        # preia si seteaza toate cele necesare pentru respectivul model
        # exemplu: seteaza configurarea lui din etc, ii seteaza cateva variabile utile cum ar fii DB, lang, LG, nodeResFile
        # si incearca sa gaseasca o metoda set INI care actioneaza ca un al doilea construct
        $this->Module_Set_incFilesJsCss($this->$modName,$adminFolder);
        $this->Module_Set_incFilesAssets($this->$modName);

    }
    # 2  + incTags #5  - A
    /**
     * Instantierea unui modul - obiect cu tot ce ii trebuie + css, js html tags for inclusion
     *
     * **#1** testeaza daca modulul poate fi instantiat
     * - nu este deja instantziat
     * - nu este obiect
     * - exista fisierul la care ar trebui sa se afle clasa modulului
     *
     ***#2** instantiaza modulul
     *
     ***#3** seteaza requierments pentru acest modulul
     * - config yml
     * - js / css tags
     *
     ***#4** apeleaza daca exista un al doilea __construct   al obiectului (daca metoda exista)
     *       util pentru procesele care depind de configurilea modulului
     *
     * @param string $modName - numele modulului ce se doreste a fi creat
     * @param string $modType -  'LOCALS'/ 'GENERAL' / 'MODELS' / 'PLUGINS'
     * @param string $adminFolder -    '' / "ADMIN/"
     * @param string $adminPrefixfix - 'C' / 'AC'
     * @return bool obiectul creat / false daca nu a creat nimic
     */
    public function Module_Build($modName, $modType, $adminFolder='', $adminPrefix='C')
    {

        #1
        if (isset($this->$modName) && is_object($this->$modName) ) {
            //echo "Obiectul care exista deja $modName <br>";
            //var_dump($this->$modName);
            error_log("[ ivy ] "."CsetModule - Module_Build : Obiectul $modName este deja instantiat ");

        } else {

            $className        = $adminPrefix.$modName;
            $classPath = FW_INC_PATH . $modType . "/$modName/" . $adminFolder . $className . '.php';

            if (file_exists($classPath)) {

                #2
                //@todo: scos $this
                error_log("[ ivy ] "."CsetModule - Module_Build : Modul instantiat = $className");
                $this->$modName = new $className($this);
                #3
                $this->Module_Set($modName, $modType, $adminFolder);
                #4
                if (method_exists($this->$modName,"_init_")) {
                    $this->$modName->_init_();
                }
                #5
                return $this->$modName;
            } else {
                error_log("[ ivy ] "."CsetModule - Module_Build : Nu  exista fisierul: $classPath ");
                return false;
            }
        }

    }


}