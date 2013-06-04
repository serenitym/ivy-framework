<?php


/**
 * Aceasta clasa ar trebui sa stea in folderul LOCALS
 * din motive de compatibilitate este lasat in core
 * -- aceasta metoda trebuie REGANDITA si este doar provizorie
 */
class CLcore extends CgenTools{

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