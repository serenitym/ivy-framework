<?php

class renderData {


    var $tmpl_file;         # numele fisierului pt template
    var $tmpl_name = '';    # numele templateului
    var $tmpl_vars;         # vector asociativ cu variabilele utilizate in tmpl
    var $typeMode ;         # GENERAL / PLUGINS / MODELS

    var $setStat = false;   # daca un set sau setDe variabile a fost cerut


    # 1
    /**
     * RET: tmpl_vars['propName'] = $obj->$tmpl_varNames[0,1,2]
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
     * @param $obj                      = pointer la obiectul care doreste randarea unui template
     * @param string $setName           = setul de variabile utilizate
     * @param string $set_varNames      = numele variabilelor utilizate in randare (luate ca set)
     * @return array|string
     *
     * (3) tmpl_vars
     *                - create dupa un array cu numele variabilelor si un obiect care are ca propritati acele nume
     *                - ex: tmpl_varsName = [ nume , prenume, varsta ]  si obj_vars->[ nume , prenume, varsta ]
     *                - motivul este acela ca obj_vars poate contine mai multe proprietati inutile iar tmpl_varsName actioneaza
     *                  ca un filtru pentru obiect luand doar variabilele necesare
     *
     */
    static function
    get_objTmplVars(&$obj, $setName='', $set_varNames=''){


        $tmpl_varNames     =  $set_varNames == ''
                              ? (isset($obj->template_vars) ? $obj->template_vars : '')
                              : (isset($obj->tmpl_varsSet[$set_varNames])
                                  ? $obj->tmpl_varsSet[$set_varNames]
                                  : ''
                                );

        $obj_vars          =  $setName == ''
                              ? $obj
                              : (isset($obj->$setName)
                                  ? $obj->$setName
                                  : ''
                                );


        return          $tmpl_varNames && $obj_vars
                              ? renderData::get_tmplVars($obj_vars, $tmpl_varNames)
                              : $obj_vars;

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
     * @param $obj                      = pointer la obiectul care doreste randarea unui template
     * @param string $objName           = numele obiectului
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
     *                - ex: tmpl_varsName = [ nume , prenume, varsta ]  si obj_vars->[ nume , prenume, varsta ]
     *                - motivul este acela ca obj_vars poate contine mai multe proprietati inutile iar tmpl_varsName actioneaza
     *                  ca un filtru pentru obiect luand doar variabilele necesare
     *
     *                - !!! ATENTIE !!!-- poate ca  ar fii o idee buna ca tmpl_vars sa se stearga dupa randarea templateului!!!
     *                             pentru ca inseamna ca avem duplicate de variabile
     */
    public function
    SET_renderObj
        (&$obj, $objName='', $setName='', $vars_setName=''){
    #__________________________________________________________________


        if($objName=='' && isset($obj->modName) )
              $objName = $obj->modName;


              $this->tmpl_file = isset($obj->template_file)
                                          ? $obj->template_file
                                          : $objName ;  #daca nu s-a declarat in ini fisierul de template ar trebui sa aiba numele modelului


              $this->tmpl_name = isset($obj->template) ? $obj->template : '' ;

              $this->tmpl_vars = $this->get_objTmplVars($obj,$setName,$vars_setName);

              $this->setStat = $setName || $vars_setName
                                 ? true
                                 : false;

        #================================================================================================================
        /**
               * MODELS / PLUGINS /GENERAL
               *
               * modType = core SET - GET_objCONF   <- SET_OBJ_mod <-  SET_general_mod
               */
              $this->typeMode  = isset($obj->modType)
                                           ? $obj->modType
                                           :(in_array($objName,$this->models) ? 'MODELS' :
                                              (in_array($objName,$this->plugins) ?  'PLUGINS'
                                              : 'GENERAL'));


        #==========================================[ TESTING STUFF ]=====================================================

        /*if($objName == 'masterBlog')
            echo "<b>renderData __construct -info :</b>"." tmpl_varNames".var_dump($tmpl_varNames)."<br><br> obj_vars".var_dump($obj_vars)."<br>";*/

        #echo "<b>$objName</b><br>";
        #var_dump($this->tmpl_vars);

    }

    # 4
    public function
    __construct
        (&$obj, $objName='', $setName='', $vars_setName=''){

        $this->SET_renderObj($obj, $objName, $setName, $vars_setName);
    }

    # 5
    /**
     * singleton
     *
     * @param $obj
     * @param string $objName           = numele obiectului
     * @param string $setName           = setul de variabile utilizate
     * @param string $vars_setName      = numele variabilelor utilizate in randare (luate ca set)
     * @param string $renderName        = numele obiectului de render
     * @return mixed
     */
    static function
    getInstance
        (&$obj, $objName='', $setName='', $vars_setName='', $renderName='renderObj') {


      # modName = core SET - GET_objCONF   <- SET_OBJ_mod <-  SET_general_mod
      # echo "<b>SET_renderObj </b>".(is_object($obj) ? " by object ": " by not Object").$objName."<br>";
                        /*PENTRU A PUTEA revenii la defaulturi*/

       if(!isset($obj->$renderName))
       {
           $obj->{$renderName} = new renderData($obj, $objName, $setName, $vars_setName);
       } else{
            if($obj->{$renderName}->setStat)
                 $obj->{$renderName}->tmpl_vars =  renderData::get_objTmplVars($obj,$setName,$vars_setName);
        }


       return  $obj->{$renderName};
    }

}
trait deprecated_TrenderTmpl {




    #==========================================[ deprecated ]====================================
    #==========================================[ primary meth.s ]================================================



    # 1
    /**
     * primary RET: display - render string tmpl with $obj['propName'] = value
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
     *  primary RET: display - render filePath tmpl with $obj['propName'] = value
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



    #=========================================[ obj CONTROL methods ]=======================================================

    # 1  - primary meth.s  #3  +  renderData #5
    /**
     * display render from object
     *
     *  # daca nu exista obiectul renderObj sau daca se cere un set de variabile pt randarea templateul
     *  # obiectul trebuie sa aiba definit un array de tipul sets[setName][template_vars]
     *  # in acest mod un anumit obiect poate sa isi seteze mai multe seturi de valori
     *
     * @param $objName
     * @param string $setName
     * @param string $vars_setName
     * @param string $renderName
     * @return string
     */
    public function
    get_renderDISPLAY
        ($objName,  $setName='', $vars_setName='', $renderName='renderObj') {
    #______________________________________________________________________________

        # echo '<b>get_renderDISPLAY -info</b>: called by '.$objName."<br>";
        if(isset($this->$objName))
        {
            $objRender = renderData::getInstance($this->$objName, $objName, $setName, $vars_setName, $renderName);

            if(isset($objRender))
            {
                return $this->getDisplay(
                                $objRender->tmpl_vars,
                                $objRender->typeMode,
                                $objName,
                                $objRender->tmpl_name,
                                $objRender->tmpl_file);

            } else {

                return "get_renderDISPLAY: error - obiectul renderData nu a fost creat";
            }

        } else {
            return "get_renderDISPLAY: error - obiectul cu numele ".$objName." nu exista";
        }

    }

    # 2  -  renderData #5
    /**
     *  # resetarea renderului pentru un anumit obiect + display randat
     *  # ex: daca se declara un setName se vor reseta tmpl_vars
     *  # in cazul acestei functii mai speciale se reseteaza templateFile-ul
     *
     * @param $objName
     * @param $templateFile
     * @param string $setName
     * @param string $vars_setName
     * @param string $renderName
     * @return string
     */
    public function
    get_renderDISPLAY_byTmplFile
        ($objName, $templateFile, $setName='', $vars_setName='', $renderName='renderObj'){


        if(is_object($this->$objName))
        {
             $obj = &$this->$objName;

             $objRender = renderData::getInstance($obj, $objName, $setName, $vars_setName, $renderName);

             $templateFile_old     = $objRender->tmpl_file;
             $objRender->tmpl_file = $templateFile;

             $display = $this->get_renderDISPLAY($objName,$setName, $vars_setName, $renderName);

             $objRender->tmpl_file =  $templateFile_old;


             return  $display;

        } else {
            return "Obiectul ".$objName." nu este instantiat <br>";
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
        ($objName, $setName='', $vars_setName='', $renderName='renderObj'){


           if(isset($this->$objName) && is_object($this->$objName))
           {
               #echo 'ctrlDISPLAY -info: Render '.$object_name."<br>";
               $obj = &$this->$objName;

               if(method_exists($obj,"DISPLAY")) {
                   return $obj->DISPLAY();
               }
               elseif(isset($obj->DISPLAY_page)) {
                   return $obj->DISPLAY_page;
               }
               else                              {
                                                      #echo 'ctrlDISPLAY -info: call- get_renderDISPLAY for '.$object_name.' with '.$object_name."<br>";
                                                      return $this->get_renderDISPLAY($objName,  $setName, $vars_setName, $renderName);
                                                 }

           } else {

               return "TrenderTmpl: Nu exista obiectul ".$objName." name";
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

        $obj = &$this->$modelName;


        if(is_object($obj))
        {

            #daca nu exista nici un obiect de render pentru acest obiect atunci cream unul
            $objRend   = renderData::getInstance($obj,$modelName);

            $tmpl_vars = $objRend->tmpl_vars;
            $typeMode  = $objRend->typeMode;


            # 2
            # daca dorim templateul unui obiect neinstantiat
            if(count($free_model) > 0){

                if(isset($free_model['modelName'])) $modelName = $free_model['modelName'];
                if(isset($free_model['typeMode']))  $typeMode  = $free_model['typeMode'];

            }
            else{
                if($tmpl_name == '')  $tmpl_name = $obj->template;
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