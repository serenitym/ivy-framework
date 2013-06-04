<?php
class CgetPosts{

    var
    $expectedPosts,
    $concat,
    $psts,
    $valid = true;

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

    function valid_string($val, $fbk=''){

        if(is_string($val)) {
            return true;
        }
        else{

          // daca are un feedback setat
          if($fbk && is_object($this->C->feedback)){
              $this->C->feedback->setFb($fbk['type'], $fbk['name'], $fbk['mess']);

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
          if($fbk && is_object($this->C->feedback)){
              $this->C->feedback->setFb($fbk['type'], $fbk['name'], $fbk['mess']);

              // daca acest feedback este de eroare
              if($fbk['type'] != 'error') return true;

          }
          return false;
        }
    }

    function validate_posts(){

        foreach($this->psts AS $prop => $pst){
       // foreach($expectedPosts AS $prop => $det){

            $det = $this->expectedPosts[$prop];

            // vezi daca are reguli de validare
            if(isset($det['validRules']))
                foreach($det['validRules'] AS $validRule => $validDet){

                    $this->validation = $this->{'valid_'.$validRule}( $pst, $validDet['fbk'] );

                }
        }



    }

    static function set_allPsts($obj){

        foreach($_POST AS $key => $val)
        {
            $obj->$key = trim($val);
        }

    }

    function set_strict_postNames( $notEmpty = true){

        foreach($this->expectedPosts AS $prop => $det){

            // preia valoarea din post
            $pst =  (isset($_POST[$prop])  ? trim($_POST[$prop]) : '' );

            if($pst || !$notEmpty)
                // retine postul in obiect
                $this->psts->$prop =$pst;

        }

    }

    function set_flexy_postNames( $notEmpty = true){

        foreach($this->expectedPosts AS $prop => $det){


            // preia valoarea din post
            $varName = isset($det['pstVal']) ? $det['pstVal'] : $prop;
            $varName_lg = $varName.$this->concat;

            $pst = isset($_POST[$varName_lg])
                      ?  $_POST[$varName_lg]
                      : ($_POST[$varName] ? $_POST[$varName] : '' );

            $pst = trim($pst);

            if($pst || !$notEmpty)
                // retine postul in obiect
                $this->psts->$prop =$pst;


        }
    }


    function set_psts($postNames_type = 'flexy' ,  $notEmpty = false){

        $this->{"set_".$postNames_type."_postNames"}($notEmpty);
    }

    /**
     * C poate sa fie gol daca nu se face validare cu feedback
     * concat poate si el sa fie gol daca nu sunt formulare prin EDITmode
     * expectedPosts - conform commenturilor demonstrative
    */
    function __construct( $expectedPosts = '', $concat = '',&$C = ''){

        $this->C = &$C;
        $this->expectedPosts = $expectedPosts;
        $this->concat = $concat ? '_'.$concat : '';

    }
}