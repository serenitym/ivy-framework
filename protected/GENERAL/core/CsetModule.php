<?php
class CsetModule extends CLcore
{

    #=============================================[ incHtmlTags ]===============
    # 1
    public function Get_IncTag_js($SrcPath)
    {
        return "<script type='text/javascript'  src='".$SrcPath."'></script>"."\n";
    }
    # 1
    public function Get_IncTag_css($SrcPath)
    {
        return "<link rel='stylesheet' href= '".$SrcPath."'  />"."\n";
    }
    # 2
    public function Get_incHtmlTags($extension, $extPath, $extSrcPath)
    {

        //if(method_exists($this,"GET_INCtag_".$extension))
        if (!method_exists($this,"Get_IncTag_".$extension)) {
            return '';

        } else {
            $tags = '';
            if (is_dir($extPath)) {
                $dir = dir($extPath);
                while(false!== ($file=$dir->read()) )
                {
                    $arr_file = explode('.',$file);
                    if (end($arr_file) ==$extension) {
                        $tags .= $this->{"Get_IncTag_".$extension}($extSrcPath.$file);
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

            $ext_PATH         =   fw_pubPath.$modType.'/'.$modName.'/'.$tmpl.$adminFolder."$folder/";
            $ext_SRC_PATH     =   fw_pubURL.$modType.'/'.$modName.'/'.$tmpl.$adminFolder."$folder/";

            #echo $extSrcPath.'<br>';
            $this->{"INC_".$extension} .= $this->Get_incHtmlTags($extension,$ext_PATH,$ext_SRC_PATH);



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
           $this->{"INC_".$extension} .= $this->{"Get_IncTag_".$extension}($srcPath);
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
     * daca fisierul yaml contine un vector "include" cu path-uri atunci la el se adauga incPath si se reapeleaza aceasta fct
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
                    self::Module_configYamlProps($mod,incPath.$incFile_yml);
                }
            }

            #===================================================================

            if (defined('DEBUG') && DEBUG == 1) {
                error_log('File is present: '.$filePathYml);
            }

            return true;

        } else {
            if (defined('DEBUG') && DEBUG == 1) {
                error_log('File is not present: '.$filePathYml);
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
    public function Module_Fs_configYamlProps(&$mod,$adminFolder='',$template='')
    {
        $modType = $mod->modType;
        $modName = $mod->modName;

        $filePathYml = incPath . 'etc/'
                     . $modType . '/'
                     . $modName . '/'
                     . ($template == ''
                        ? $adminFolder . $modName . '.yml'
                        : 'tmpl_' . $adminFolder . $template . '.yml');

        $this->Module_configYamlProps($mod, $filePathYml);

        #2
        $templateExists = (  isset($mod->template)
                          && $mod->template!=''
                          && $template == '' );

        if ($templateExists) {
            self::Module_Fs_configYamlProps($mod,$adminFolder,$mod->template);
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
    public function Module_configCorePointers(&$mod,$modType,$modName)
    {

        $mod->C      =  &$this;
        # situatie core
        $mod->DB     =  &$this->DB;
        $mod->admin  =  &$this->admin;
        $mod->LG     =  &$this->lang;
        $mod->lang   =  &$this->lang;
        $mod->nameF  =  &$this->nameF;


        # date ale modulului curent
        $mod->idNode =  &$this->idNode;
        $mod->idTree =  &$this->idTree;
        $mod->level  =  &$this->level;
        // acelasi lucru cu modName
        $mod->type   =  &$this->type;


        #date despre acest modul
        $mod->modName = $modName;
        $mod->modType = $modType;


        #error_log('modName '.$modName."\n\n");
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
     * standard confing of a model REQ, CONF, mod-> [ objREQ, _setINI() ]
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
                                                    $this->type,
                                                    $this->nameF,
                                                    $this->lang);*/

        $this->Module_configCorePointers($mod,$modType,$modName);  //MARKER
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
        # exemplu: seteaza configurarea lui din etc, ii seteaza cateva variabile utile cum ar fii DB, lang, LG, nameF
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
        // daca obiectul nu a fost setat
        $className = $adminPrefix.$modName;
        #1
        if (!isset($this->$modName)
            && file_exists(fw_incPath . $modType . "/$modName/" . $adminFolder . $className . '.php')
        ) {
            #2
            //@todo: scos $this
            $this->$modName = new $className($this);
            #3
            $this->Module_Set($modName,$modType,$adminPrefix,$adminFolder);
            #4
            if (method_exists($this->$modName,"_setINI")) {
                $this->$modName->_setINI();

            } elseif (method_exists($this->$modName,"_init")) {
                $this->$modName->_init();
            }
            #5
            return $this->$modName;

        } else {
            return false;
        }

    }


}