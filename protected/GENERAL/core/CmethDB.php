<?php

/**
 * CmethDB
 *
 * @package Core
 * @version 1.0
 * @copyright Copyright (c) 2012 Serenity Media
 * @author  Ioana Cristea
 * @license AGPLv3 {@link http://www.gnu.org/licenses/agpl-3.0.txt}
 */
class CmethDB extends CrenderTmpl {

    #=============================[ select ]====================================

    public function Db_Get_queryRes($query)
    {
        // daca query este obiect atunci probrabil este o resursa
        // daca nu atunci probabil ca este un sql
        $queryRes = is_object($query)
                    ?  $query : $this->DB->query($query);

        return $queryRes;
    }

    public function Db_Set_procModProps(&$mod,$processResMethod, $query)
    {
        $queryRes = $this->Db_Get_queryRes($query);

        $allRecords = array();
        $row = $queryRes->fetch_assoc();

        $row = $mod->{$processResMethod}($row);

        if ($row) {
            if (is_array($row) && count($row) > 0) {
                # atribuim valori direct in prop obiectului
                foreach($row AS $recordName => $recordValue) {
                    $mod->$recordName = $recordValue;
                }
            }
            $allRecords[0] = $row;
        }

        return $allRecords;

    }
    public function Db_Set_modProps(&$mod, $query)
    {
        $queryRes = $this->Db_Get_queryRes($query);

        $allRecords = array();
        $row = $queryRes->fetch_assoc();

        # atribuim valori direct in prop obiectului
        foreach($row AS $recordName => $recordValue) {
            $mod->$recordName = $recordValue;
        }

        $allRecords[0] = $row;

        return $allRecords;
    }
    public function Db_Get_procRows(&$mod,$processResMethod, $query)
    {
        $queryRes = $this->Db_Get_queryRes($query);

        $allRecords = array();

        if ($queryRes->num_rows > 0) {
            while ($row = $queryRes->fetch_assoc()) {
                $row = $mod->{$processResMethod}($row);
                if ($row) {
                    array_push($allRecords, $row);
                }
            }
        }
        return $allRecords;

    }
    public function Db_Get_rows($query, $method = 'fetch_assoc')
    {
        $queryRes = $this->Db_Get_queryRes($query);

        $allRecords = array();
        while ($row = $queryRes->{$method}()){
            array_push($allRecords, $row);
        }
        return $allRecords;
    }

    public function Handle_Db_fetch(&$mod,$query,$processResMethod='', $onlyArr = false)
    {
        $queryRes = $this->DB->query($query);
        $numRows  = $queryRes->num_rows;
        /**
         * Daca avem un singur rand returnat && pentru aceasta varianta atribuim
         * rezultatele modulului pasat + un array[0]- cu randul returnat
         *  - avem si o metoda de procesare / sau nu
         *
         * Daca avem mai multe randuri returnate => rezultatul va fi returnat
         * doar sub forma de array multidimensional procesat sau nu
         */
        if ($numRows) {

            if ($queryRes->num_rows == 1 && !$onlyArr) {
                if ($processResMethod && method_exists($mod,$processResMethod)) {
                    return $this->Db_Set_procModProps($mod, $processResMethod,  $queryRes);
                } else {
                    return $this->Db_Set_modProps($mod, $queryRes);
                }

            } else {
                if ($processResMethod && method_exists($mod,$processResMethod)) {
                    return $this->Db_Get_procRows($mod, $processResMethod, $queryRes);
                } else {
                    return $this->Db_Get_rows( $queryRes);
                }
            }
        }
    }

   #=============================[ update / Insert ]==============================


    //relocare remote ...sunt situatii cand e nevoie
    public function reLocate($location='', $ANCORA='',$paramAdd='')
    {
         unset($_POST);
         $location = ($location=='' ? $_SERVER['REQUEST_URI'] :$location);
         //header("Location: ".$location.$paramAdd.$ANCORA);

         echo "<script type='text/javascript'>window.location = '$location';</script>";
         echo "<a href='$location'>Click here if the browser does not redirect you automatically</a>";
         exit;
    }

    //sql_query
    public function Db_query($query, $reset=true, $ANCORA='', $location='',
        $paramAdd='', $errorMessage=''
    ) {

        $this->DB->query($query);
        //echo $query."<br>";
        // daca se cere reset
        if ($reset) {
            $this->reLocate($location,$ANCORA,$paramAdd);
        } else {
            return $errorMessage;
        }
    }

    //Db_queryBulk
    public function Db_queryBulk($queries, $reset=true, $ANCORA='', $location='',
        $paramAdd='', $errorMessage=''
    ) {

        foreach($queries AS $query){
            $this->DB->query($query);
        }

        if ($reset) {
            $this->reLocate($location,$ANCORA,$paramAdd);
        } else {
            return $errorMessage;
        }

    }


    /**
     *  Seteaza stringul pt sql UPDATE / INSERT de genul
     *  varName1 = 'varValue1', varName2 = 'varValue2', ...
     *
     * dintr-un vector asociativ trimis
     *
     * @param $values - vectorul asociativ de genul varName => varValue
     *
     * @return string - 'varValue1', varName2 = 'varValue2', ...
     */
    public function Db_setFromAssoc($values)
    {
        $sets = array();
        foreach($values AS $varName=>$varValue) {
            $varValue = $this->DB->real_escape_string($varValue);
            array_push($sets, "$varName = '".$varValue."'");
        }

        $set = implode(', ', $sets);
        return $set;
    }




}
