<?php
/**
 * TgenTools
 * Trait tha definitely does something :-)
 *
 * @package Core
 * @version 1.0
 * @copyright Copyright (c) 2012 Serenity Media
 * @author  Ioana Cristea
 * @license AGPLv3 {@link http://www.gnu.org/licenses/agpl-3.0.txt}
 */
class CgenTools extends CManage{

   var $renderObj;
   var $historyArgs;   #array setat de model


   public function SET_HISTORYargs($id, $concat='',$argString=''){

        $end_ul = '';

         if($argString=='' && is_array($this->historyArgs)){

             $this->simpleHref_history = '<ul class="breadcrumb">';                               #nu cred ca ar trebui sa trebuiasca sa fac neaparat asta , functia SET_HISTORY nu ar mai trebui apelata
             foreach($this->historyArgs AS $argName=>$argValue) $argString .=  '&'.$argName.'='.$argValue;
             $ul = '</ul>';
         }


         if(isset($this->tree[$id]->p_id)) $this->SET_HISTORYargs($this->tree[$id]->p_id,"<span class='divider'>/</span>", $argString);

         if($id)
         {
             $backName  = $this->tree[$id]->name;

             $simpleHref = "href='index.php?idT={$this->idT}&idC={$id}{$argString}'";
             $this->simpleHref_history .= "<li><a {$simpleHref}> $backName </a> $concat </li>".$end_ul;


         }





       }
   public function SET_HISTORY($id,$concat='')    {

           if(!isset($this->simpleHref_history) || $this->simpleHref_history==''){

               # concat este un simplu caracter cu care se concateneaza
               if(isset($this->tree[$id]->p_id))$trueLEVEL = $this->SET_HISTORY($this->tree[$id]->p_id,"<span class='small9'>&gt;&gt;</span>");
               if($id)
               {

                   array_push($this->history,$id);
                   $backName  = $this->tree[$id]->name;
                   $backNameF = str_replace(' ','_',$backName);

                   $this->history_TITLE_keywords .= $backName.' ';
                   $this->history_TITLE          .= $backNameF.'/';

                   #old href:    index.php?idC={$idT}&idT={$idT}&level=nr
                   #newFormat:   idT-idC-L{level} / backName;

                   $href = ($trueLEVEL==2 || $this->idT==1  ? '': "href='".$this->idT."-{$id}-L{$trueLEVEL}/$this->history_TITLE'");
                   $this->history_HREF .="<a {$href}> $backName $concat </a>";

                   $simpleHref = "href='index.php?idT={$this->idT}&idC={$id}'";
                   $this->simpleHref_history .= "<a {$simpleHref}> $backName $concat </a>";

                   return $trueLEVEL+1;
               }
               else return 1;
           }


       }  /**
            * daca level=3 => 4-level = 1;
            *      level=2 => 4-2 = 2;
            *      level=1 => 4-1 = 3
            *
            * if(!p_id) =>level=1;
            * if(!$id) = > am ajuns la sfarsit
            */
   public function SET_HTML_headerIMG()           {

           $this->HTML_headerIMG = file_get_contents(fw_pubPath.'/GENERAL/RES/headerIMG.html');
       }
   public function GET_idT_from_idC($Cid)         {

               $res = $this->DB->query("SELECT Pid from TREE  where Cid='{$Cid}'");
               if($res->num_rows > 0)
               {
                   $row=$res->fetch_assoc();

                   if($row['Pid'] > 0)  { /* echo $row['Pid']; */  $this->GET_idT_from_idC($row['Pid']);}
                   else $this->idT =  $Cid;
               }


           }

   public function GET_pagination($query,$nrItems,$GETargs,$uniq,&$obj='', $ANCORA='')   {

          #echo '<b>pagination Query</b> '.$query."<br>";
           //@todo: probabil ca hreful ar trebui cumva refacut

           $CURRENTpage = (isset($_GET['Pn']) ? $_GET['Pn'] : 1);

          if(!isset($_SESSION['NR_pages'][$uniq]) || $this->admin) #daca nu este setata pagination sau daca ne aflamin admin
           {

               $NRrows = $this->DB->query($query)->num_rows;
               $pages =  ceil($NRrows / $nrItems);

               unset($_SESSION['NR_pages'][$uniq]);
               $_SESSION['NR_pages'][$uniq] = $pages;

               #echo 'GET_pagination '.$NRrows;
           }
           else
           {
               $pages = $_SESSION['NR_pages'][$uniq];
           }
           #echo 'Nr pages '.$pages.'<br>';


           #=========================================================================================
           # daca se trimite un pointer al obiectului care cere paginarea atunci pentru acest obiect
           # se vor seta urmatoarele variabile
           # LimitStart poate sa aiba presetata limita
               if(is_object($obj))
               {

                    $obj->LimitStart += ($CURRENTpage - 1)*$nrItems;
                    #$obj->LimitEnd   = $obj->LimitStart + $nrItems;
                    $obj->Pn = $CURRENTpage;

               }

           #=========================================================================================

           if(isset($pages) && $pages>1)   #altfel nu mai are rost sa se faca o paginare
           {
               $argString = '';
               foreach($GETargs AS $argName=>$argValue) $argString .=$argName.'='.$argValue.'&';

               $pagination = "<div class='pagination'><ul>";

                   for($i=1;$i<=$pages;$i++) {

                       $classCURRENT  = ($i==$CURRENTpage ? " class='active' " : '');
                       $pagination .=" <li $classCURRENT><a href='index.php?{$argString}Pn=$i{$ANCORA}'> $i </a></li> ";
                   }

               $pagination .="</ul></div>";

               return $pagination;
           }
           else return ' ';

       }

   public function get_modType($modName){
       $modType = '';
        if(    in_array($modName,$this->models )) $modType = 'MODELS';
        elseif(in_array($modName,$this->plugins)) $modType = 'PLUGINS';
        elseif(in_array($modName,$this->locals)) $modType = 'LOCALS';

       return $modType;
   }


    public function get_resImages_paths($modName){
        /**
         * USE
         *
         * $results=glob("{includes/*.php,core/*.php}",GLOB_BRACE);
         * GLOB_BRACE - Expands {a,b,c} to match 'a', 'b', or 'c'
         *
         * foreach (glob("$dir/$prefix*.html") as $file) {   #echo $file."<br>";  }
         *
         * <?php
             $path_parts = pathinfo('/www/htdocs/inc/lib.inc.php');

             echo $path_parts['dirname'], "\n";    =>  /www/htdocs/inc
             echo $path_parts['basename'], "\n";   =>  lib.inc.php
             echo $path_parts['extension'], "\n";  =>  php
             echo $path_parts['filename'], "\n";   => lib.inc
             ?>
         *
         *
         *  'resPath'
         *  'resURL'
         *
        */
        $resDir = resPath."uploads/images/{$modName}";
        $imgFiles_Paths = glob("$resDir/*.jpg");
        return $imgFiles_Paths;

    }

    public function get_resImages_urls($modName){

        $imgFiles_Paths = $this->get_resImages_paths($modName);
        $imgFiles_Urls = array();
        foreach($imgFiles_Paths AS $key=>$filePath){

            $imgFiles_Urls[$key] = str_replace(resPath,resURL,$filePath);
        }

        return $imgFiles_Urls;
    }



    static function READyml($file_yml, &$obj =''){

        if(!$obj)
        {
            $obj = new stdClass();
            $RETobj = true;
        }
        Ccore::GETconf($obj,$file_yml);
        if(isset($RETobj)) return $obj;

    }

    public function GET_asincronMeth($objName, $methName){


        if(is_object($this->$objName))
        {
            if(method_exists($this->$objName, $methName)){

                return $this->$objName->{$methName}();
            }
            else {
                return " Metoda $methName nu exista sau nu poate fii accesata <br>";

            }

        } else {
            return " Obiectul $objName nu este instantiat <br>";
        }
    }




 #============================================[proces POSTS]=======================================================================

  function valid_string($val, $fbk=''){

      if(is_string($val)) {
          return true;
      }
      else{

        // daca are un feedback setat
        if($fbk){
            $this->feedback->setFb($fbk['type'], $fbk['name'], $fbk['mess']);

            // daca acest feedback este de eroare
            if($fbk['type'] != 'error') return true;

        }

        return false;
      }
  }
  function valid_notEmpty($val, $fbk=''){

      if(!empty($val)) {
          return true;
      }
      else{

        echo 'valid_notEmpty '.$val;
        // daca are un feedback setat
        if($fbk){
            $this->feedback->setFb($fbk['type'], $fbk['name'], $fbk['mess']);

            // daca acest feedback este de eroare
            if($fbk['type'] != 'error') return true;

        }
        return false;
      }
  }
  function processPosts( $expectedPosts){

      /**
       *
       * updatePrev:
              title:

                  validRules:
                    string:
                      fbk: {type: "warning", name: "News Title", mess: "Your title should be a string" }

                    notEmpty:
                       fbk: {type: "error", name: "News Title", mess: "Your news must have a title" }

       *
                newsDate: ""

                extLink: ""

                lead:
                  fbk: {type: "error", name: "News Lead", mess: "Your news must have a Lead" }
                  varType: "string"

                newsPic: ""
      */

      $psts = new stdClass();
      $lg = $this->lang;

      $psts->validation = true;
      $psts->vars = new stdClass();

      foreach($expectedPosts AS $prop => $det){

          // preia valoarea din post
          $varName = isset($det['pstVal']) ? $det['pstVal'] : $prop;

          $pst = isset($_POST[$varName."_".$lg])
                 ?  $_POST[$varName."_".$lg]
                 : ($_POST[$varName] ? $_POST[$varName] : '' );

          // retine postul in obiect
          $psts->vars->$prop = $pst = trim($pst);


          //echo $varName." = ".$pst."<br>";
          // vezi daca are reguli de validare
          if(isset($det['validRules']))
              foreach($det['validRules'] AS $validRule => $validDet){

                  $psts->validation = $this->{'valid_'.$validRule}( $pst, $validDet['fbk'] );

              }
      }

      return $psts;

  }

 #============================================[staging -> _dep_]=======================================================================

    # Testing - not sure if this are usefull anymore...???DEPRECATED ???
    static function error_ech($message, $from='', $var_dump=''){

            echo "<p class='text-error '><b> $from :</b> $message </p>";
            if($var_dump)
                var_dump($var_dump);
        }
    static function info_ech($message, $from=''){

                echo "<p class='text-success '><b> $from :</b> $message </p>";
        }

    static function error_ech_ObjMod($message,&$obj, $meth='', $var_dump=''){

            echo "<p class='text-error '><b> {$obj->modName}->  $meth :</b> $message </p>";
            if($var_dump)
                var_dump($var_dump);
        }
    static function info_ech_ObjMod($message,&$obj, $meth='', $var_dump=''){

                echo "<p class='text-success '><b> {$obj->modName} -> $meth :</b> $message </p>";
               if($var_dump)
                  var_dump($var_dump);
        }

 #===================================================================================================================


}