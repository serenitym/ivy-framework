<?php

class renderData {


    var $tmpl_file;         # numele fisierului pt template
    var $tmpl_name = '';    # numele templateului
    var $tmpl_vars;         # vector asociativ cu variabilele utilizate in tmpl
    var $typeMode ;         # GENERAL / PLUGINS / MODELS

    var $setStat = false;   # daca un set sau setDe variabile a fost cerut


    # 1
    /**
     * RET: tmpl_vars['propName'] = $mod->$tmpl_varNames[0,1,2]
     *
     * @param $tmpl_varSource = obiectul modelului
     * @param $tmpl_varNames = vector asociativ cu denumirile variabilelor folosite in templateurile modelului
     * @return array - array asociativ cu proprietatile cerute / necesare din cadrul obiectului
     */
    static function
    get_tmplVars (&$tmpl_varSource,$tmpl_varNames) {

        $tmpl_vars = array();
        foreach($tmpl_varNames AS $var_name){

            if(isset($tmpl_varSource->$var_name) )
                $tmpl_vars[$var_name] = $tmpl_varSource->$var_name;
            else
                $tmpl_vars[$var_name] = "";
        }

        return $tmpl_vars;
    }

    # 2
    /**
     * OBIECTUL pentru metodele de render + LOGISTICS
     *
     * @param $mod                      = pointer la obiectul care doreste randarea unui template
     * @param string $setName           = setul de variabile utilizate
     * @param string $set_varNames      = numele variabilelor utilizate in randare (luate ca set)
     * @return array|string
     *
     * (3) tmpl_vars
     *                - create dupa un array cu numele variabilelor si un obiect care are ca propritati acele nume
     *                - ex: tmpl_varsName = [ nume , prenume, varsta ]  si mod_vars->[ nume , prenume, varsta ]
     *                - motivul este acela ca mod_vars poate contine mai multe proprietati inutile iar tmpl_varsName actioneaza
     *                  ca un filtru pentru obiect luand doar variabilele necesare
     *
     */
    static function
    get_modTmplVars(&$mod, $setName='', $set_varNames=''){


        $tmpl_varNames     =  $set_varNames == ''
                              ? (isset($mod->template_vars) ? $mod->template_vars : '')
                              : (isset($mod->tmpl_varsSet[$set_varNames])
                                  ? $mod->tmpl_varsSet[$set_varNames]
                                  : ''
                                );

        $mod_vars          =  $setName == ''
                              ? $mod
                              : (isset($mod->$setName)
                                  ? $mod->$setName
                                  : ''
                                );


        return          $tmpl_varNames && $mod_vars
                              ? renderData::get_tmplVars($mod_vars, $tmpl_varNames)
                              : $mod_vars;

        /**
   * from the looks of it tmpl_vars ar putea fii si " "
   * now is that a good thing? i am not sure maybe it is
    */
    }

    #===================================================================================

    # 3
    /**
     * set OBIECTUL pentru metodele de render + LOGISTICS
     *
     * @param $mod                      = pointer la obiectul care doreste randarea unui template
     * @param string $modName           = numele obiectului
     * @param string $setName           = setul de variabile utilizate
     * @param string $vars_setName      = numele variabilelor utilizate in randare (luate ca set)
     *
     *  SETS: - utilizate pentru randarea templateurilor
     *
     *  tmpl_file   -  numele fisierului de template
     *  tmpl_name   -  numele templateului
     *  tmpl_vars   -  array asociativ cu variabilele pentru template
     *  typeMode    -  DEPREACATED ? GENERAL /MODELS /PLUGINS
     *
     * (1) - daca nu este transmis numele obiectului acesta se va prelua din proprietatea lui modName -> setata de Ccore
     *
     *
     * (2) tmpl_file  - daca template_file nu este definit in yml atunci va lua numele obiectului
     *
     * (3) tmpl_vars
     *                - create dupa un array cu numele variabilelor si un obiect care are ca propritati acele nume
     *                - ex: tmpl_varsName = [ nume , prenume, varsta ]  si mod_vars->[ nume , prenume, varsta ]
     *                - motivul este acela ca mod_vars poate contine mai multe proprietati inutile iar tmpl_varsName actioneaza
     *                  ca un filtru pentru obiect luand doar variabilele necesare
     *
     *                - !!! ATENTIE !!!-- poate ca  ar fii o idee buna ca tmpl_vars sa se stearga dupa randarea templateului!!!
     *                             pentru ca inseamna ca avem duplicate de variabile
     */
    public function
    SET_rendermod
        (&$mod, $modName='', $setName='', $vars_setName=''){
    #__________________________________________________________________


        if($modName=='' && isset($mod->modName) )
              $modName = $mod->modName;


              $this->tmpl_file = isset($mod->template_file)
                                          ? $mod->template_file
                                          : $modName ;  #daca nu s-a declarat in ini fisierul de template ar trebui sa aiba numele modelului


              $this->tmpl_name = isset($mod->template) ? $mod->template : '' ;

              $this->tmpl_vars = $this->get_modTmplVars($mod,$setName,$vars_setName);

              $this->setStat = $setName || $vars_setName
                                 ? true
                                 : false;

        #================================================================================================================
        /**
               * MODELS / PLUGINS /GENERAL
               *
               * modType = core SET - Module_Fs_configYamlProps   <- Module_Build <-  Module_Build
               */
              $this->typeMode  = isset($mod->modType)
                                           ? $mod->modType
                                           :(in_array($modName,$this->models) ? 'MODELS' :
                                              (in_array($modName,$this->plugins) ?  'PLUGINS'
                                              : 'GENERAL'));


        #==========================================[ TESTING STUFF ]=====================================================

        /*if($modName == 'masterBlog')
            echo "<b>renderData __construct -info :</b>"." tmpl_varNames".var_dump($tmpl_varNames)."<br><br> mod_vars".var_dump($mod_vars)."<br>";*/

        #echo "<b>$modName</b><br>";
        #var_dump($this->tmpl_vars);

    }

    # 4
    public function
    __construct
        (&$mod, $modName='', $setName='', $vars_setName=''){

        $this->SET_rendermod($mod, $modName, $setName, $vars_setName);
    }

    # 5
    /**
     * singleton
     *
     * @param $mod
     * @param string $modName           = numele obiectului
     * @param string $setName           = setul de variabile utilizate
     * @param string $vars_setName      = numele variabilelor utilizate in randare (luate ca set)
     * @param string $renderName        = numele obiectului de render
     * @return mixed
     */
    static function
    getInstance
        (&$mod, $modName='', $setName='', $vars_setName='', $renderName='rendermod') {


      # modName = core SET - Module_Fs_configYamlProps   <- Module_Build <-  Module_Build
      # echo "<b>SET_rendermod </b>".(is_modect($mod) ? " by modect ": " by not modect").$modName."<br>";
                        /*PENTRU A PUTEA revenii la defaulturi*/

       if(!isset($mod->$renderName))
       {
           $mod->{$renderName} = new renderData($mod, $modName, $setName, $vars_setName);
       } else{
            if($mod->{$renderName}->setStat)
                 $mod->{$renderName}->tmpl_vars =  renderData::get_modTmplVars($mod,$setName,$vars_setName);
        }


       return  $mod->{$renderName};
    }

}
trait deprecated_TrenderTmpl {




    #==========================================[ deprecated ]====================================
    #==========================================[ primary meth.s ]================================================



    # 1
    /**
     * primary RET: display - render string tmpl with $mod['propName'] = value
     *
     * @param $tmpl_vars  ['propName'] = value
     * @param $tmpl  eval string Tmpl
     * @return string
     */
    public function
    renderDisplay
        ($tmpl_vars, $tmpl)      {


       foreach($tmpl_vars as $var_name => $var_value)
       {
            $$var_name = $var_value;
           # echo $var_name.' '.$var_value."<br/>";
       }

       $display = '';
       eval("\$display = \"$tmpl\";");
       //throw new Exception("Eroare in $tmpl_vars");

       return $display;

   }

    # 2
    /**
     *  primary RET: display - render filePath tmpl with $mod['propName'] = value
     *
     *  # se extrage continutul templateului
     *  # randarea stringului
     *
     * @param $tmpl_vars
     * @param $tmplPath
     * @return string
     */
    public function
    getDisplay_byTmplPath
        ($tmpl_vars, $tmplPath){

       if(file_exists(fw_pubPath.$tmplPath))
       {
           $content =  file_get_contents(fw_pubPath.$tmplPath);
           return $this->renderDisplay($tmpl_vars , $content);

       } else {

           return  "getDisplay_byTmplPath - error : Nu exista obiect sau template pt acest model la calea $tmplPath <br>";
       }

    }

    # 3
    public function
    getDisplay
        ($tmpl_vars, $typeMode,$modelName,$tmpl_name='',$tmpl_file='') {

       if($tmpl_file == '')  $tmpl_file =  $modelName ;
       $tmpl_dir = ( $tmpl_name!='' ? '/tmpl_'.$tmpl_name : '' );                           #daca nu exista nici un template name => nu exista nici un tmpl_dir
       $path     = $typeMode.'/'.$modelName.$tmpl_dir.'/tmpl/'.$tmpl_file.'.html';

      return
          $this->getDisplay_byTmplPath($tmpl_vars, $path);

   }



    #=========================================[ mod CONTROL methods ]=======================================================

    # 1  - primary meth.s  #3  +  renderData #5
    /**
     * display render from modect
     *
     *  # daca nu exista obiectul rendermod sau daca se cere un set de variabile pt randarea templateul
     *  # obiectul trebuie sa aiba definit un array de tipul sets[setName][template_vars]
     *  # in acest mod un anumit obiect poate sa isi seteze mai multe seturi de valori
     *
     * @param $modName
     * @param string $setName
     * @param string $vars_setName
     * @param string $renderName
     * @return string
     */
    public function
    get_renderDISPLAY
        ($modName,  $setName='', $vars_setName='', $renderName='rendermod') {
    #______________________________________________________________________________

        # echo '<b>get_renderDISPLAY -info</b>: called by '.$modName."<br>";
        if(isset($this->$modName))
        {
            $modRender = renderData::getInstance($this->$modName, $modName, $setName, $vars_setName, $renderName);

            if(isset($modRender))
            {
                return $this->getDisplay(
                                $modRender->tmpl_vars,
                                $modRender->typeMode,
                                $modName,
                                $modRender->tmpl_name,
                                $modRender->tmpl_file);

            } else {

                return "get_renderDISPLAY: error - obiectul renderData nu a fost creat";
            }

        } else {
            return "get_renderDISPLAY: error - obiectul cu numele ".$modName." nu exista";
        }

    }

    # 2  -  renderData #5
    /**
     *  # resetarea renderului pentru un anumit obiect + display randat
     *  # ex: daca se declara un setName se vor reseta tmpl_vars
     *  # in cazul acestei functii mai speciale se reseteaza templateFile-ul
     *
     * @param $modName
     * @param $templateFile
     * @param string $setName
     * @param string $vars_setName
     * @param string $renderName
     * @return string
     */
    public function
    get_renderDISPLAY_byTmplFile
        ($modName, $templateFile, $setName='', $vars_setName='', $renderName='rendermod'){


        if(is_modect($this->$modName))
        {
             $mod = &$this->$modName;

             $modRender = renderData::getInstance($mod, $modName, $setName, $vars_setName, $renderName);

             $templateFile_old     = $modRender->tmpl_file;
             $modRender->tmpl_file = $templateFile;

             $display = $this->get_renderDISPLAY($modName,$setName, $vars_setName, $renderName);

             $modRender->tmpl_file =  $templateFile_old;


             return  $display;

        } else {
            return "Obiectul ".$modName." nu este instantiat <br>";
        }

}

    # 2
    /**
     * general DISPLAY controler
     * LOGISTICS
     *
     *  #metoda testeaza daca obiectul are vreo metoda DISPLAY sau vreo propietate presetata pe DIPSLAY_page
     *  #daca nu incearca sa gaseasca templateul modelului
     *  #daca nu are variabile de template definite va trimite tot obiectul spre render in speranta ca va fii de ajuns
    */
    public function
    ctrlDISPLAY
        ($modName, $setName='', $vars_setName='', $renderName='rendermod'){


           if(isset($this->$modName) && is_modect($this->$modName))
           {
               #echo 'ctrlDISPLAY -info: Render '.$modect_name."<br>";
               $mod = &$this->$modName;

               if(method_exists($mod,"DISPLAY")) {
                   return $mod->DISPLAY();
               }
               elseif(isset($mod->DISPLAY_page)) {
                   return $mod->DISPLAY_page;
               }
               else                              {
                                                      #echo 'ctrlDISPLAY -info: call- get_renderDISPLAY for '.$modect_name.' with '.$modect_name."<br>";
                                                      return $this->get_renderDISPLAY($modName,  $setName, $vars_setName, $renderName);
                                                 }

           } else {

               return "TrenderTmpl: Nu exista obiectul ".$modName." name";
           }
     }



    #================ [ Specific DISPLAY's]===========================================================

    # 1 -  primary meth.s #3   +  renderData #5
    /**
     *  # ofera posibilitatea integrarii unui template_file in cadrul atuia din templateul curent
     *  # sau din alt obiect deja instantiat sau nu
     *  #free_model inseamna ca este un obiect neinstantiat deci nelegat
     *  #               deasemenea asta inseamna ca depinde de setarile unui anumit model $modelName
     *  #               care ar trebui sa fie in prealabil instantiat
     *
     *
     *  # 2
     *  - trebuie sa ne bazam pe variabilele unui anumit obiect si anume al modelName
     *  - cu aceste variabile vom randa templateul obiectului free
     *  - setarile obiectului free vor fii date
     *      - pe baza arrayului free_model care va contine
     *          - modelName - numeleModelului
     *          - typeMode  - tipul modelului (MODELS, PLUGINS, GENERAL)
     *      - prin parametrii deja existenti
     *           - tmpl_file - obligatoriu
     *           - tmpl_name - numele templateului  care poate fii lasat gol si atunci
     *                          va fii luat din folderul tmpl al modelului free cerut
     *
     * @param $modelName
     * @param $tmpl_file
     * @param string $tmpl_name
     * @param array $free_model
     * @return string
     */
    public function
    incTemplate
        ($modelName, $tmpl_file, $tmpl_name='',$free_model=array()) {

        $mod = &$this->$modelName;


        if(is_modect($mod))
        {

            #daca nu exista nici un obiect de render pentru acest obiect atunci cream unul
            $modRend   = renderData::getInstance($mod,$modelName);

            $tmpl_vars = $modRend->tmpl_vars;
            $typeMode  = $modRend->typeMode;


            # 2
            # daca dorim templateul unui obiect neinstantiat
            if(count($free_model) > 0){

                if(isset($free_model['modelName'])) $modelName = $free_model['modelName'];
                if(isset($free_model['typeMode']))  $typeMode  = $free_model['typeMode'];

            }
            else{
                if($tmpl_name == '')  $tmpl_name = $mod->template;
            }


            return $this->getDisplay($tmpl_vars,$typeMode ,$modelName,$tmpl_name, $tmpl_file);

        } else {
            return "incTemplate -error: Obiectul <b>$modelName</b> nu este instantiat <br>";
        }




    }

    # 1 -  primary meth.s #1
    /**
     * randare display repetitiv dupa un fisier de tmpl
     *
     * @param $items
     * @param $tmplPath
     * @return string
     */
    public function
    getDisplay_Items_byTmplPath
        ($items, $tmplPath){
        if(is_array($items))
        {
                #var_dump($items);
                $display = '';
                foreach($items AS $item)
                    $display .= $this->renderDisplay($item,file_get_contents(fw_pubPath.$tmplPath));

                return $display;
        }
        else {
            return "getDisplay_Items_byTmplPath -error: There are no items <br>";
        }
    }

    # 1 -  primary meth.s #1
    /**
     * randare display repetitiv dupa un string  de tmpl
     *
     * @param $items    - vector asociativ cu itemuri, pregatit in prealabil de model
     * @param $tmpl     - string cu template
     *                          * se foloseste ~ for $
     *                          * se foloseste ` for "
     * @return string
     */
    public function
    getDisplay_Items
        ($items, $tmpl) {

        if(is_array($items))
        {
               $tmpl = str_replace('~','$',$tmpl);
               $tmpl = str_replace('`','"',$tmpl);


                $display = '';
                foreach($items AS $item) $display .= $this->renderDisplay($item,$tmpl);

                return $display;
        }
        else {
                    return "getDisplay_Items -error: There are no items <br>";
             }




    }


    # ???
    public function getDIsplay_ItemsTree($itemsTree, $tmplArr, $idP=0, $level=0, $display = ''){


        # de comentat pentru ca am uitat ce am gandit aici
        foreach($itemsTree[$idP] AS $id => $children){

            $display .=$this->renderDisplay($children, $tmplArr[$level]);
            if(is_array($itemsTree[$id]))
            {
                if(isset($tmplArr[$level+1])) $level++;
                $this->getDIsplay_ItemsTree($itemsTree,$tmplArr,$id,$level,$display);
            }

            return $display;
        }

    }




   #=======================================================[ DISPLAY functions ]========================================
    # utilizat de Ccore pentru a randa templateurile modelelor sau pluginurilor
    # independent
   #====================================================================================================================

    /**
     * # metoda utilizata pentru a afisa extra optiuni pentru administrarea unui anumit model / plugin
     * # deoarece teoretic variabilele necesare tmpl de ADMIN exista deja
     * # deci tmpl de admin a fost deja randat
     *
     *
     * @param $tmpl
     * @param string $AdmClass
     * @param string $condition
     * @return string
     */
    public function getDisplay_Admin($tmpl, $AdmClass = 'ATmpl', $condition='') {

        $permss = true;
        if($condition)
            eval("\$permss = $condition ? true : false;");


        if($this->admin && $permss)
        {
            if($AdmClass!='')
                return "<div class='{$AdmClass}'>".$tmpl."</div>";
            else
                return $tmpl;
        }

         else return "";

    }
}