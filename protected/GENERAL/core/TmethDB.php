<?php

/**
 * TmethDB
 * yet another trait that does something
 *
 * @package Core
 * @version 1.0
 * @copyright Copyright (c) 2012 Serenity Media
 * @author  Ioana Cristea
 * @license AGPLv3 {@link http://www.gnu.org/licenses/agpl-3.0.txt}
 */
trait TmethDB {

    use _dep_TmethDB;


    /**
         * Array multidimesional cu datele ret de query si procesate de $obj->processResMethod
         *
         * @param $obj                      - obiectul care cheama metoda
         * @param $query                    - queryul cerut
         * @param string $processResMethod  - metoda care proceseaza fiecare rand returnat de query
         * @param bool $onlyArr  = false    - (nu) va seta proprietati in cadrul obiectului daca doar un rand este returnat
         * @return array                    - array-ul multidimens cu datele ret de $query
         */

    public static function GET_resultArray ($result, $method = 'fetch_assoc') {
            $output = array();
            while ($row = $result->{$method}())
                array_push($output, $row);
            return $output;
        }

    /**
         *
         * @param $obj                        - obiectul care a apelat metoda
         * @param $query                      - query-ul de procesat
         * @param string $processResMethod    - metoda a $obj care proceseaza orice rand returnat
         * @param bool $onlyArr               - daca queryul ret un singur record
         *                                              false - va seta valoriile ret la obj
         *                                              true - va returna un array[0] = array(colum=>value);
         * @return array                      - array muldimensional cu toate recordurile returnate de query
         *                                      si procesate de processResMethod
         */
    public function GET_objProperties(&$obj,$query,$processResMethod='', $onlyArr = false) {


            /**
             * DESCRIERE
             *
             * RET:
             *  - returneaza un array multiDimensional cu toate in registrarile gasite pe $query-ul dat
             *  - pt un singur record returnat si onlyArr = false
             *      => variabilele vor fii setate ca proprietati ale obiectului
             *      => deasemenea se returneaza un array cu un singur record
             *
             * OPT: $processResMethod
             *  - metoda a obiectului , apelata pentru fiecare record inparte
             *  - poate altera un rand sau returna false in cazul in care randul este invalid
             */
            $allRecords = array();
            $res = $this->DB->query($query);

            if($res->num_rows > 0)
            {
                if($res->num_rows == 1 && !$onlyArr )
                {

                    $row = $res->fetch_assoc();

                    if($processResMethod!='' && method_exists($obj,$processResMethod) )
                        $row = $obj->{$processResMethod}($row);

                    if($row)
                    {
                        if(is_array($row) && count($row) > 0)
                        {
                            foreach($row AS $recordName => $recordValue)         # atribuim valori direct in prop obiectului
                                $obj->$recordName = $recordValue;

                        }
                        $allRecords[0] = $row;
                    }
                    # else   error_log('eroare la query-ul '.$query);



                }
                else
                {
                    if($processResMethod!='' && method_exists($obj,$processResMethod)){
                        while($row = $res->fetch_assoc())
                        {
                              $row = $obj->{$processResMethod}($row);
                              if($row)
                                     array_push($allRecords, $row);
                        }
                    }
                    else{
                        #TODO: GET_resultArray from a future commit from piu
                        while($row = $res->fetch_assoc())
                            array_push($allRecords, $row);
                    }

                }
            }
            return $allRecords;




        }
    public function GET_objProperties_byCat(&$obj,$query,$Col_name,$processResMethod=''){

            # va returna un array de genul allRecords[Cat_name][0,1,2...] = array(children array);
            # hmm..daca avem mai multe coloane atunci $allRecords[col][0,,1...]



            $allRecords = array();
            $res = $this->DB->query($query);

            if($res->num_rows > 0)
            {
                while($row = $res->fetch_assoc())
                {

                    $col = $row[$Col_name];

                    if($processResMethod!='' && method_exists($obj,$processResMethod) )
                    {

                        $row = $obj->{$processResMethod}($row);
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
    public function GETtree_objProperties(&$obj,$query,$idC_name, $idP_name,$processResMethod='')   {

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
                    if($processResMethod!='') $row = $obj->{$processResMethod}($row);

                    $allRecords[$parentID][$ID] = $row;

                    # am impresia ca asta imi va da peste cap sortarile din query - ramane de vazut


                }

                return $allRecords;
            }

        }


    #relocare remote ...sunt situatii cand e nevoie
    public function reLocate($location='', $ANCORA='',$paramAdd='') {

         unset($_POST);
         $location = ($location=='' ? $_SERVER['REQUEST_URI'] :$location);
         header("Location: ".$location.$paramAdd.$ANCORA);
         exit;
    }

    public function
    DMLsql($query,$reset=true,$ANCORA='',$location='',$paramAdd='', $errorMessage='') {

        $this->DB->query($query);

        /**
         * Daca se cere reset si queryul are succes
         */

        /**
         * Aceasta linie este comentata pentru ca daca de exemplu
         * un update care nu modifica cu nimic o linie va returna affected_rows = 0
         * => lucru care nu inseamna o eroare
         */
        //if($reset && $this->DB->affected_rows)  $this->reLocate($location,$ANCORA,$paramAdd);
        if($reset)  $this->reLocate($location,$ANCORA,$paramAdd);
        else return $errorMessage;

        # poate ca pe viitor as  vrea sa am ceva pentru affected

    }

    public function
    DMLsql_bulk($queries,$reset=true,$ANCORA='',$location='',$paramAdd='', $errorMessage='') {

        foreach($queries AS $query){

            $this->DB->query($query);
        }

        if($reset)  $this->reLocate($location,$ANCORA,$paramAdd);
        else return $errorMessage;


    }

    #=============================[ Staging ]=======================================


     /**
     *  DB_table         = prefix + origin + postfix
     *
     * @param        $obj           - obiectul pentru care se fac setarile
     * @param        $extKname      - numele cheii externe
     * @param        $extKvalue     - valoarea cheii externe
     * @param        $tbOrigin      - numele tabelului de origine
     * @param string $tbPostfix
     * @param string $tbPrefix
     * @param string $bond          - concatenare nume DB_table
     */
    public function SET_tableRelations_settings
             (&$obj,$extKname, $extKvalue, $tbOrigin, $tbPostfix='', $tbPrefix='', $bond='_') {


        $obj->DB_extKey_name  = $extKname  ;
        $obj->DB_extKey_value = $extKvalue ;
        $obj->DB_table_origin = $tbOrigin  ;
        $obj->DB_table_postfix = $tbPostfix ;
        $obj->DB_table_prefix  = $tbPrefix  ;

        $obj->DB_table = ($tbPrefix!='' ? $tbPrefix.$bond : '').
                         ($tbOrigin!='' ? $tbOrigin : '').
                         ($tbPostfix!='' ? $bond.$tbPostfix      : '');

    }


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
     * @param string $obj
     * @param string $processPOST
     * @param bool $LG
     * @return string
     */
    public function DMLsql_setValues
    ($varNames,$varValues=array(),&$obj='',$processPOST='', $LG=true){


        # daca exista undeva un array definit cu numele coloanelor aprox identice cu numele variabilelor de post
        # se poate crea usor stringul de update sau INSERT
        # atentie aceasta functie e foarte specifica


        if($LG) $concatLG = '_'.$this->lang;           #hmm...?
        if($processPOST!='') $obj->$processPOST();     #in cazul in care se doreste o preprocesare, o preprocesare oblibatorie ar trebui sa se creeze oricum



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


    public function DMLsql_setValues_fromArr($values){

        $set = '';
        foreach($values AS $varName=>$varValue) {
            $varValue = $this->DB->real_escape_string($varValue);
            $set .= "$varName = '".$varValue."', ";
        }

        $set = substr($set,0,-2);

        return $set;
    }

}
