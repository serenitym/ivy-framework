<?php
/**
 * CrenderTmpl
 * @desc Class for templating system
 *
 * @package Core
 * @version 1.0
 * @copyright Copyright (c) 2012 Serenity Media
 * @author  Ioana Cristea
 * @license AGPLv3 {@link http://www.gnu.org/licenses/agpl-3.0.txt}
 */
class CrenderTmpl extends item {

    var $tmplType_object      = 'path';
    var $tmplType_simpleArray = 'str';
    var $tmplType_items       = 'str';
    var $tmplType_asocArray   = 'str';


    #==========================================[ independente meth.s ]================================================
    public function
    Render_type(&$data, &$OPTobj, $tmplType= 'path,"" ' ,$tmpl  )
    {}

    public function
    Get_templateFromPath($tmplPath)
    {
         if( file_exists(FW_PUB_PATH.$tmplPath)) {
            return file_get_contents(FW_PUB_PATH.$tmplPath);
         } else {
             error_log("Nu exista template la calea ".FW_PUB_PATH.$tmplPath);
             return false;
         }
    }

    public function
    Get_template($renderType, $tmpl, $tmplType='path')
    {
        if ($tmplType == '') {
           $tmplType =  $this->{'tmplType_'.$renderType};
        }

        if ($tmplType == 'path') {
            return $this->Get_templateFromPath($tmpl);
        } else {
            return $tmpl;
        }

    }

    # 1
    public function
    Render_assoc( &$aR, $obj='', $tmplType='path', $tmpl)
    {

        if(is_array($aR))
        {
           /* if(isset($renderFun))unset($renderFun);
            $renderFun = create_function('&$ao, &$o, &$core','  return "'.$content.' ";');
            $display   = $renderFun($ao, $mod, $this);*/
             $content = $this->Get_template('asocArray',$tmpl, $tmplType);
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
            return $this->debugMess("obiectul nu exista pt templateul $tmpl sau $tmpl <br>");
        }

    }

    # 2
    /**
     * Parsare array muldimensional
     *
     * @param array $items           - array-ul de forma array(0 =>[title=>'', lead=> '' , pictureUrl=> '' ] )
     * @param string $tmplStr       - template trimis direct ca string in acest template ne vom referii la
     *                                  -  itemul unui array ca ~ao->propName
     *                                  - optional la $o cu ~o (adta daca este trimis prin $mod)
     *                                  - ghilimelele duble se vor scrie cu `
     * @param string $tmplPath      - path-ul la orice alt template care va folosii acest array
     *                                  - in acest template ne putem referii la variabile in modul normal $ si concatenari cu "
     * @param string $obj           - daca se doreste sa se faca referire in interiorul templateului de array la obiectul principal
     * @return string               - returneaza templateul randat
     */
    public function
    Render_items ($items, $obj='' , $tmplType='path', $tmpl)
    {
        if(is_array($items))
        {
            $content = $this->Get_template('items',$tmpl, $tmplType);
            # $content   = str_replace('$this','$core',$content);
            if ($content) {

                if($tmplType!='path'){
                    $content = str_replace('~','$',$content);
                    $content = str_replace('`','"',$content);
                }

                $display = '';
                foreach($items AS $item)
                {
                    $display .= $this->Render_assoc($item,$obj,'str',$content);
                }

                return $display;
            } else {

                 return $this->debugMess("getDisplay_Items -error: Nu exista continut de template <br>");
            }
        } else {
                return $this->debugMess("getDisplay_Items -error: There are no items <br>");
        }

    }

    #2
    /**
     * Parsare array unidensional
     *  - similar cu Render_items
     *
     * @param $items
     * @param string $tmplStr
     * @param string $tmplPath
     * @param string $obj
     * @return string
     */
    public function
    Render_array($items, $obj='', $tmplType='path', $tmpl)
    {

        if(is_array($items) && count($items) > 0)
        {
            $content = $this->Get_template('simpleArray',$tmpl, $tmplType);
            # $content   = str_replace('$this','$core',$content);
            if ($content) {

                if ($tmplType != 'path') {
                    $content = str_replace('~','$',$content);
                    $content = str_replace('`','"',$content);
                }

                $display = '';
                # pentrua a se putea face referinta din cadrul templateului la obiectul principal chemat acest fromArr
                $o  = &$obj;
                foreach($items AS $key => $i)
                {
                    $displayItem = '';
                    eval("\$displayItem = \"$content\";");
                    $display .=$displayItem;
                }

                return $display;
            } else {
                 return $this->debugMess("getDisplay_Items_uniDimens -error: Nu exista continut de template <br>");
            }
        } else {
                return $this->debugMess("getDisplay_Items_uniDimens -error: There are no items <br>");
        }

    }

    public function Render_arrayTmplContent($items, $tmplContent, $obj='')
    {
        $display = '';
        # pentrua a se putea face referinta din cadrul templateului la obiectul principal chemat acest fromArr
        $o  = &$obj;
        foreach($items AS $key => $i)
        {
            $displayItem = '';
            eval("\$displayItem = \"$tmplContent\";");
            $display .=$displayItem;
        }

        return $display;
    }

    public function Render_arrayFromPath($items, $obj='', $tmpl)
    {

    }
    public function Render_arrayFromStr(){}

    /**
     * render Template with mod properties
     * @param $obj
     * @param string $tmplStr
     * @param string $tmplPath
     * @return string
     */
    public function
    Render_object($obj, $tmplType='path', $tmpl)
    {
        // use in template $mod->varName or $o->varName
        if(is_object($obj))
        {
             $content = $this->Get_template('object',$tmpl, $tmplType);
             if($content)
             {
                 # use $o in templates for easy edit end reading
                 $o = &$obj;
                 $display = '';
                 eval("\$display = \"$content\";");
                 return $display;

             }
             else {
                return $this->debugMess("$obj->modName Nu exista continut de template pt <b>$tmpl</b> <br>");
            }
        } else{
                return $this->debugMess("obiectul nu exista pt templateul $tmpl <br>");
        }

    }


    public function
    Render_Module($mod, $tmplFile='')
    {

        /**
         * Se poate seta un template si cere un anumit fisier de tmpl
         * */
        if (is_object($mod)) {


            if (!$tmplFile) {
                $tmplFile = isset($mod->template_file)
                            ? $mod->template_file
                            : $mod->modName ;
            }

            #daca nu exista nici un template name => nu exista nici un tmpl_dir
            $tmpl_dir = isset($mod->template) &&  $mod->template!=''
                        ? 'tmpl_'.$mod->template
                        : '' ;

            $tmplPath = $this->Module_Get_pathTmpl($mod, $tmpl_dir, $tmplFile);

            $display = $this->Render_object($mod, 'path',$tmplPath);;


        } else {
           $display = 'There is no mod';
        }

        return $display;

    }

    #=====================================[ fromRES / static content ]============================================

    /**
     * Incearca sa returneze continutul static al unui modul
     *  -> daca exista fisier ii returneaza continutul
     *  -> daca nu exista fisier testeaza daca mod este obiect
     *          - daca nu este obiect inseamna ca functia a fost apelata recursiv
     *          - si daca are o metoda de setat continut '_setRes_'
     *                  - daca conditiile sunt indeplinite atunci se seteaza variabila resPath
     *                  - se apeleaza metoda standard _setRes_($resPath)
     *                      daca aceasta metoda returneaza true inseamna ca s-a pus continut  la
     * acea locatie si atunci reapelam get_resContent
     * daca nu inseamna ca a fost o problema si trimitem un mesaj de eroare
     *
     *
     *
    */
    public function get_resContent($resPath, $obj='')
    {
         if(is_file($resPath))
         {
             return file_get_contents($resPath);

         } else {

             if(is_object($obj) && method_exists($obj, '_setRes_'))
             {
                $obj->resPath = $resPath;
                $obj->_setRes_($resPath);
                if (is_file($resPath)) {

                    return file_get_contents($resPath);
                } else {
                    return 'CrenderTmpl - get_resContent : Nu exista continut la pagina <b>'.$resPath.'</b>';
                }

             }
             else {
                 return 'Nu exista continut la pagina <b>'.$resPath.'</b>';
             }
         }


    }

    public function
    Render_ModulefromRes($obj, $resName='')
    {
        $mod_resPath = isset($obj->resPath)
                       ? $obj->resPath
                       : $this->Module_Get_pathRes($obj, $resName);
        return $this->get_resContent($mod_resPath,$obj);
    }

    #===================[ general controler ]==========================================

    /**
     * INCEARCA SA SOLUTIONEZE display-ul pentru un obiect
     *  - mod->_render_()
     *  - mod->Render_Module($mod)
     *
     * @param $obj
     * @return string
     */
    public function Handle_Render($obj)
    {
           if(is_object($obj))
           {
               if(method_exists($obj,"_render_")) {
                   // pentru solutzionarea inhouse a displayului
                   return $obj->_render_();

               } else  {
                  return $this-> Render_Module($obj);
               }

           } else {

               return "CrenderTmpl- Handle_Render: Nu exista obiectul";
           }
     }


}
