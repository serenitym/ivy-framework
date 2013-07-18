<?php
/**
 * Used to:
 * - metodele "staging" din cadrul claselor din core
 * nestandardizate, nesigure dar generale
 */
class Cunstable extends CsetModule{


    // Get_path methods
    public function Module_Get_pathTmpl(&$mod, $templateDir, $templateFile)
    {
        return $mod->modDir
                    . $templateDir.'/tmpl/'
                        . $templateFile.'.html';
    }

    // autonoma
    public function Get_resPath($modType, $modName, $resName, $lang='')
    {

        $lang        = $lang ? $lang : $this->lang;
        $mod_resDir  = RES_PATH."{$modType}/{$modName}/";
        $mod_resPath = $mod_resDir."{$lang}_{$resName}.html";

        if (!is_dir($mod_resDir)) {
            mkdir($mod_resDir,0777,true);
        }

       return $mod_resPath;
    }


    /**
    * resName poate fi aflat in 2 moduri
    *  1. din numele modulului
    *  2. sau din numele resFile ( declarat in tabelul ITEMS )
    *
    * 1 - este valabil pentru orice mdoul
    * 2 - este valabil doar pentru acel modul care este manager curent al paginii
    * adica $mod->modName = $this->mgrName;
    */
    public function Module_Get_pathRes(&$mod, $resName='', $lang='')
    {
        if (!$resName) {
           // daca modulul este managerul curent al paginii
           // see Ccore->_Set_currentNode()
            //if ($this->mgrName == $mod->modName)
            $resName = $this->mgrName == $mod->modName
                     ? $this->nodeResFile
                     : $mod->modName;
        }
        if (!$lang) {
            $lang = $this->lang;
        }

        $modResDir = RES_PATH.$mod->modDir;
         if (!is_dir($modResDir)) {
            mkdir($modResDir,0777,true);
        }

        $resPath = $modResDir."{$lang}_{$resName}.html";

        return $resPath;

    }

    /*==========================================================*/
    /*=================[from CrenderTmpl]==========================*/
    /*==========================================================*/

    #=====deprecated / not sure where they are used

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



    /*==========================================================*/
    /*=================[from CmethDB]==========================*/
    /*==========================================================*/

    //used in BlackSea
    // Db_setValsAuto
    /**
     * ??? debatable
     *
     * while this function provides a lot of automaticity?
     * it's very obscure because
     *  1. datele care trebuie parsate stau intr-un yml...
     *  2. care evident trebuie citit
     *  3. in metodata care compune queriul trebuie sa deschizi yaml-ul
     *    ca sa vezi ce se intampla...
     *
     * 4. deci desi pare automata si e mai putin cod de scris
     *  este obscura si mai greu de procesat
     *
     * @param $varNames
     * @param array $varValues
     * @param string $mod
     * @param string $processPOST
     * @param bool $LG
     * @return string
     */
    public function DMLsql_setValues($varNames, $varValues=array(), &$mod='',
        $processPOST='', $LG=true
    ){


        # daca exista undeva un array definit cu numele coloanelor aprox identice cu numele variabilelor de post
        # se poate crea usor stringul de update sau INSERT
        # atentie aceasta functie e foarte specifica


        if($LG) $concatLG = '_'.$this->lang;           #hmm...?
        if($processPOST!='') $mod->$processPOST();     #in cazul in care se doreste o preprocesare, o preprocesare oblibatorie ar trebui sa se creeze oricum


        #vectorul varValues poate venii cu variabile aditionale postului necesare
        #din vectorul de post se vor lua doar variabilele relevante pentru query

        foreach($varNames AS $varName)
        {

                if(isset($_POST[$varName.$concatLG]))                   # in general variabilele vin cu lang concatenat ca postfix
                    $varValues[$varName] = $_POST[$varName.$concatLG] ;

                elseif(isset($_POST[$varName]))                         # dar exista si cazuri cand nu au lang pentru ca nu este nevoie de lang
                    $varValues[$varName] = $_POST[$varName] ;

                #echo $varName.' = '.$varValues[$varName]."</br>";
        }

        # varValues = poate contine si alte variabile inafara celor trimise de POST, astfel se adauga la el
        #astfel vectorul varValues va contine doar variabilele necesare
        $set = '';
        foreach($varValues AS $varName=>$varValue) {
            $varValue = $this->DB->real_escape_string($varValue);
            $set .= "$varName = '".$varValue."', ";
        }

        $set = substr($set,0,-2);

        return $set;

    }

    // to complicated ???
     /**
     *  DB_table         = prefix + origin + postfix
     *
     * @param        $mod           - obiectul pentru care se fac setarile
     * @param        $extKname      - numele cheii externe
     * @param        $extKvalue     - valoarea cheii externe
     * @param        $tbOrigin      - numele tabelului de origine
     * @param string $tbPostfix
     * @param string $tbPrefix
     * @param string $bond          - concatenare nume DB_table
     */
    public function SET_tableRelations_settings(
        &$mod,$extKname, $extKvalue,
        $tbOrigin, $tbPostfix='',
        $tbPrefix='', $bond='_'
    ) {

        $mod->DB_extKey_name  = $extKname  ;
        $mod->DB_extKey_value = $extKvalue ;
        $mod->DB_table_origin = $tbOrigin  ;
        $mod->DB_table_postfix = $tbPostfix ;
        $mod->DB_table_prefix  = $tbPrefix  ;

        $mod->DB_table = ($tbPrefix!='' ? $tbPrefix.$bond : '').
                         ($tbOrigin!='' ? $tbOrigin : '').
                         ($tbPostfix!='' ? $bond.$tbPostfix      : '');

    }



    //Sql_Get_queryRowsByCat
    //Not sure were it is used???
    public function GET_modProperties_byCat(&$mod,$query,$Col_name,$processResMethod='')
    {
            # va returna un array de genul allRecords[Cat_name][0,1,2...] = array(children array);
            # hmm..daca avem mai multe coloane atunci $allRecords[col][0,,1...]

            $allRecords = array();
            $res = $this->DB->query($query);

            if($res->num_rows > 0)
            {
                while($row = $res->fetch_assoc())
                {

                    $col = $row[$Col_name];

                    if($processResMethod!='' && method_exists($mod,$processResMethod) )
                    {

                        $row = $mod->{$processResMethod}($row);
                    }

                    $allRecords[$col] = array();
                    array_push($allRecords[$col], $row);

                    #var_dump($allRecords[$col]);
                    # am impresia ca asta imi va da peste cap sortarile din query - ramane de vazut


                }

                #var_dump($allRecords);

                return $allRecords;
            }


        }
    //Not sure were it is used???
    public function GETtree_modProperties(
        &$mod, $query, $idC_name,
        $idP_name,$processResMethod=''
    ) {

            # va returna un array de genul allRecords[idP][idC] = array(children array);
            # idC_name / idP_name = numele campurilor pt child / parent

            $allRecords = array();
            $res = $this->DB->query($query);

            if($res->num_rows > 0)
            {
                while($row = $res->fetch_assoc())
                {
                    $parentID = $row[$idP_name];
                    $ID       = $row[$idC_name];
                    if($processResMethod!='') $row = $mod->{$processResMethod}($row);

                    $allRecords[$parentID][$ID] = $row;

                    # am impresia ca asta imi va da peste cap sortarile din query - ramane de vazut


                }

                return $allRecords;
            }

        }






  /*=================[from CgenTools]==========================*/
  //   - for Prographic - in news
  #============================================[proces POSTS]=============================

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

  function validatePosts(&$expectedPosts,&$psts){

      foreach($psts->vars AS $prop => $pst){
     // foreach($expectedPosts AS $prop => $det){

          $det = $expectedPosts[$prop];

          // vezi daca are reguli de validare
          if(isset($det['validRules']))
              foreach($det['validRules'] AS $validRule => $validDet){

                  $psts->validation = $this->{'valid_'.$validRule}( $pst, $validDet['fbk'] );

              }
      }



  }

  function processPosts( $expectedPosts, $notEmpty = true, $validation = true){

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

      $lg = $this->lang;

      $psts = new stdClass();
      $psts->validation = true;
      $psts->vars = new stdClass();

      foreach($expectedPosts AS $prop => $det){


          // preia valoarea din post
          $varName = isset($det['pstVal']) ? $det['pstVal'] : $prop;
          $varName_lg = $varName.'_'.$lg;

          $pst = isset($_POST[$varName_lg])
                    ?  $_POST[$varName_lg]
                    : ($_POST[$varName] ? $_POST[$varName] : '' );

          $pst = trim($pst);

          if($pst || !$notEmpty)
              // retine postul in obiect
              $psts->vars->$prop =$pst;


      }

      if($validation)
          $this->validatePosts($expectedPosts,$psts);

      return $psts;

  }




    // - my gues is blackSea

    # Testing - not sure if this are usefull anymore...???DEPRECATED ???
    static function error_ech($message, $from='', $var_dump=''){

            echo "<p class='text-error '><b> $from :</b> $message </p>";
            if($var_dump)
                var_dump($var_dump);
        }
    static function info_ech($message, $from=''){

                echo "<p class='text-success '><b> $from :</b> $message </p>";
        }

    static function error_ech_modMod($message,&$mod, $meth='', $var_dump=''){

            echo "<p class='text-error '><b> {$mod->modName}->  $meth :</b> $message </p>";
            if($var_dump)
                var_dump($var_dump);
        }
    static function info_ech_modMod($message,&$mod, $meth='', $var_dump=''){

                echo "<p class='text-success '><b> {$mod->modName} -> $meth :</b> $message </p>";
               if($var_dump)
                  var_dump($var_dump);
        }

 #===================================================================================================================




}