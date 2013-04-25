<?php
/**
 * renderTmpl
 * @desc Small class for rendering templates.
 *
 * @package Core
 * @version 1.0
 * @copyright Copyright (c) 2012 Serenity Media
 * @author  Ioana Cristea
 * @license AGPLv3 {@link http://www.gnu.org/licenses/agpl-3.0.txt}
 */


/**
 * TrenderTmpl
 * @desc Trait for templating system
 *
 * @package Core
 * @version 1.0
 * @copyright Copyright (c) 2012 Serenity Media
 * @author  Ioana Cristea
 * @license AGPLv3 {@link http://www.gnu.org/licenses/agpl-3.0.txt}
 */
trait TrenderTmpl {



    #==========================================[ independente meth.s ]================================================

    public function get_renderContent($tmplStr='', $tmplPath=''){
        $content = '';
        if($tmplStr)
            $content = &$tmplStr;

        if($tmplPath && file_exists(fw_pubPath.$tmplPath))
            $content .= file_get_contents(fw_pubPath.$tmplPath);

        return $content;
    }

     //obj este pus pentru folosiri viitoare
    # render a single itemOfArr -- this function should be renamed
    # 1
    public function
    renderDisplay_fromArr( &$aR, $tmplStr='', $tmplPath='',&$obj=''){

        if(is_array($aR))
        {
           /* if(isset($renderFun))unset($renderFun);
            $renderFun = create_function('&$ao, &$o, &$core','  return "'.$content.' ";');
            $display   = $renderFun($ao, $obj, $this);*/
             $content = $this->get_renderContent($tmplStr, $tmplPath);
            #~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
            if($content)
            {
                # nu stiu daca asta este o varianta prea profitabila
                # use $ao in templates for easy edit end reading
                $ao = (object) $aR;
                # pentrua a se putea face referinta din cadrul templateului la obiectul principal chemat acest fromArr
                $o  = &$obj;
                $display = '';
                eval("\$display = \"$content\";");
                return $display;

            }
            else {
                return $this->debugMess("$obj->modName Nu exista continut de template <br>");
            }
        } else {
            return $this->debugMess("obiectul nu exista pt templateul $tmplStr sau $tmplPath <br>");
        }

    }

    # 2
    /**
     * Parsare array muldimensional
     *
     * @param $items                - array-ul de forma array(0 =>[title=>'', lead=> '' , pictureUrl=> '' ] )
     * @param string $tmplStr       - template trimis direct ca string in acest template ne vom referii la
     *                                  -  itemul unui array ca ~ao->propName
     *                                  - optional la $o cu ~o (adta daca este trimis prin $obj)
     *                                  - ghilimelele duble se vor scrie cu `
     * @param string $tmplPath      - path-ul la orice alt template care va folosii acest array
     *                                  - in acest template ne putem referii la variabile in modul normal $ si concatenari cu "
     * @param string $obj           - daca se doreste sa se faca referire in interiorul templateului de array la obiectul principal
     * @return string               - returneaza templateul randat
     */
    public function
    renderDisplay_Items ($items, $tmplStr='', $tmplPath='', &$obj='') {


        if(is_array($items))
        {
            $content = $this->get_renderContent($tmplStr, $tmplPath);
            # $content   = str_replace('$this','$core',$content);
            if($content)
            {

                if($tmplStr){
                    $content = str_replace('~','$',$content);
                    $content = str_replace('`','"',$content);
                }

                $display = '';
                foreach($items AS $item)
                {
                    $display .= $this->renderDisplay_fromArr($item,$content,'',$obj);
                }

                return $display;
            }
            else {

                 return $this->debugMess("getDisplay_Items -error: Nu exista continut de template <br>");
            }
        } else {
                return $this->debugMess("getDisplay_Items -error: There are no items <br>");
        }

    }

    #2
    /**
     * Parsare array unidensional
     *  - similar cu renderDisplay_Items
     *
     * @param $items
     * @param string $tmplStr
     * @param string $tmplPath
     * @param string $obj
     * @return string
     */
    public function
    renderDisplay_Items_uniDimens($items, $tmplStr='', $tmplPath='', &$obj='') {


        if(is_array($items) && count($items) > 0)
        {
            $content = $this->get_renderContent($tmplStr, $tmplPath);
            # $content   = str_replace('$this','$core',$content);
            if($content)
            {

                if($tmplStr){
                    $content = str_replace('~','$',$content);
                    $content = str_replace('`','"',$content);
                }

                $display = '';
                # pentrua a se putea face referinta din cadrul templateului la obiectul principal chemat acest fromArr
                $o  = &$obj;
                foreach($items AS $i)
                {
                    $displayItem = '';
                    eval("\$displayItem = \"$content\";");
                    $display .=$displayItem;
                }

                return $display;
            }
            else {

                 return $this->debugMess("getDisplay_Items_uniDimens -error: Nu exista continut de template <br>");
            }
        } else {
                return $this->debugMess("getDisplay_Items_uniDimens -error: There are no items <br>");
        }

    }


    /**
     * render Template with obj properties
     * @param $obj
     * @param string $tmplStr
     * @param string $tmplPath
     * @return string
     */
    public function
    renderDisplay_fromObj(&$obj, $tmplStr='', $tmplPath=''){

        // use in template $obj->varName or $o->varName
        if(is_object($obj))
        {
            /*if(isset($renderFun))unset($renderFun);
            $content   = str_replace('$this','$core',$content);
            $renderFun = create_function('&$obj, &$o, &$core','  return "'.$content.' ";');
            $display   = $renderFun($obj, $obj, $this);*/
             $content = $this->get_renderContent($tmplStr, $tmplPath);
             if($content)
             {
                 # use $o in templates for easy edit end reading
                 $o = &$obj;
                 $display = '';
                 eval("\$display = \"$content\";");
                 return $display;

             }
             else {
                return $this->debugMess("$obj->modName Nu exista continut de template pt <b>$tmplPath</b> <br>");
            }
        } else{
                return $this->debugMess("obiectul nu exista pt templateul $tmplStr sau $tmplPath <br>");
        }

    }


    public function
    ctrlDisplay_fromObj(&$obj, $tmplFile=''){

        /**
         * Se poate seta un template si cere un anumit fisier de tmpl
         * */
      if(is_object($obj))
      {

          $o = &$obj;

          $tmpl_file = $tmplFile
                  ? $tmplFile
                  :( isset($obj->template_file)
                          ? $obj->template_file
                          : $obj->modName );  #daca nu s-a declarat in ini fisierul de template ar trebui sa aiba numele modelului



          $modType = $obj->modType;
          $modName = $obj->modName;
          # echo "ctrlDisplay_fromObj ".$modName.' '.$modType."<br>";

          $tmpl_dir = isset($obj->template) &&  $obj->template!=''
                  ? '/tmpl_'.$obj->template
                  : '' ;                           #daca nu exista nici un template name => nu exista nici un tmpl_dir

          $tmplPath     = $modType.'/'.$modName.$tmpl_dir.'/tmpl/'.$tmpl_file.'.html';



          /* if($obj->modName == 'comments')
             Console::logSpeed('randare Start '.$obj->modName);*/

          $display = $this->renderDisplay_fromObj($obj, '',$tmplPath);;

          /* if($obj->modName == 'comments')
             Console::logSpeed('randare End '.$obj->modName);*/

      } else {

          $display = 'There is no obj';
      }



      return $display;

      #return $this->renderDisplay_fromObj($obj, '',$tmplPath);



    }

    #=====================================[ fromRES / static content ]============================================
    # poate ca aceasta functie ar trebui sa stea in TgenTools, fiind mult mai generala
    public function
    get_resPath($modType, $modName,$lang='', $resName=''){
        /**
         * resPath = $modType / modName/ lang_nameF
        */
        $lang        = $lang ? $lang : $this->lang;
        $resName     = $resName ? $resName : $modName;

        $mod_resDir  =resPath."{$modType}/{$modName}/";
        $mod_resPath = $mod_resDir."{$lang}_{$resName}.html";

        if(!is_dir($mod_resDir))
            mkdir($mod_resDir,0777,true);

        return $mod_resPath;
    }

    public function
    get_resPath_forObj(&$obj, $resName=''){

        # nu cred ca aceasta metoda de determinare mod_resName e foarte buna, scalabila
        $mod_resName = $resName ? $resName : $this->nameF;
        $mod_resPath = $this->get_resPath($obj->modType, $obj->modName, $this->lang,$mod_resName );
        return $mod_resPath;
    }

    # !!!! NEW RULE $obj->_setRes($resPath) true sau false
    /**
     * Incearca sa returneze continutul static al unui modul
     *  -> daca exista fisier ii returneaza continutul
     *  -> daca nu exista fisier testeaza daca obj este obiect
     *          - daca nu este obiect inseamna ca functia a fost apelata recursiv
     *          - si daca are o metoda de setat continut '_setRes'
     *                  - daca conditiile sunt indeplinite atunci se seteaza variabila resPath
     *                  - se apeleaza metoda standard _setRes($resPath)
     *                      daca aceasta metoda returneaza true inseamna ca s-a pus continut  la
     * acea locatie si atunci reapelam get_resContent
     * daca nu inseamna ca a fost o problema si trimitem un mesaj de eroare
     *
     *
     *
    */
    public function
    get_resContent($resPath, &$obj=''){

         if(is_file($resPath))
         {
             return file_get_contents($resPath);

         } else {

             if(is_object($obj) && method_exists($obj, '_setRes'))
             {
                $obj->resPath = $resPath;
                $obj->_setRes($resPath);
                if(is_file($resPath))
                    return file_get_contents($resPath);

               else
                   return 'Nu exista continut la pagina <b>'.$resPath.'</b>';

             }
             else {
                 return 'Nu exista continut la pagina <b>'.$resPath.'</b>';
             }
         }


    }

    public function
    ctrlDisplay_fromObjRes(&$obj, $resName=''){


        $mod_resPath = isset($obj->resPath)
                       ? $obj->resPath
                       : $this->get_resPath_forObj($obj, $resName);
        return $this->get_resContent($mod_resPath,$obj);


    }

    #===================[ general controler ]==========================================

    /**
     * INCEARCA SA SOLUTIONEZE display-ul pentru un obiect
     *  - obj->DISPLAY()
     *  - obj->DISPLAY_page
     *  - obj->DISPLAY_ResPath
     *  - obj->ctrlDisplay_fromObj($obj)
     *
     * @param $obj
     * @return string
     */
    public function
    ctrlDisplay(&$obj){


           if(is_object($obj))
           {
               if(method_exists($obj,"DISPLAY")) {
                   return $obj->DISPLAY();

               }  elseif(isset($obj->DISPLAY_page)) {
                   return $obj->DISPLAY_page;

               }  elseif(isset($obj->display_ResPath))  {
                   return $this->get_renderContent($obj->display_ResPath);

               }  else  {
                          return $this-> ctrlDisplay_fromObj($obj);
               }

           } else {

               return "TrenderTmpl- ctrlDisplay: Nu exista obiectul";
           }
     }






    #=====deprecated

    /**
     * CHECK condition by eval()
     * @param $condition    - string condition
     * @param $tmpl
     * @param string $alterTmpl - if false return this
     * @return string
     */
    public function check($condition, $tmpl, $alterTmpl = '')      {

        $returnRES = 'check';

        $string_eval = "\$returnRES = $condition ? \"$tmpl\" : \"$alterTmpl\";";
        #echo 'check tmpl '.$tmpl."<br>";
        #echo 'check eval '.$string_eval."<br>";
        eval($string_eval);

        return $returnRES;

    }
    public function Acheck($condition, $tmpl, $alterTmpl = 'orice'){

        $returnRES = '';
        if($this->admin)
        {
            eval("\$returnRES = $condition ? \"$tmpl\" : \"$alterTmpl\";");

        }
        return $returnRES;

    }

    public function isZero($testVar, $tmpl,$alterTmpl='')          {

        if($testVar!='' && $testVar!='0' && $testVar!=0)
            return $tmpl;
        else
            return $alterTmpl;
    }
    public function isEmpty($testVar, $tmpl,$alterTmpl='')         {

        if($testVar && $testVar!='')
            return $tmpl;
        else
            return $alterTmpl;
    }
    # plus test ADMIN
    public function AisEmpty($testVar, $tmpl,$alterTmpl='')        {

        if($this->admin && $testVar && $testVar!='')
            return $tmpl;
        else
            return $alterTmpl;
    }







}
