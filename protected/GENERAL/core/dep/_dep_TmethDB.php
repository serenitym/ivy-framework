<?php

trait _dep_TmethDB {

        // deprecateded
    #=============================[ MANAGE wheres ]=======================================
    public function CONCAT_queryWheres(&$obj1, &$obj2,$where='WHERE '){


        $RETwhere = '';
        if(isset($obj1->queryWheres) && isset($obj2->queryWheres))
        {
            $queryWheres = array_merge($obj1->queryWheres,$obj2->queryWheres );

            if(count($queryWheres) > 0)
                $RETwhere = $where.implode(" AND ", $queryWheres);

        }
        return $RETwhere;

    }

    /**
     * STRINGUL DE WHERE pentru un query bazat pe arrayul queryWheres
     *
     * @param $obj
     * @return string  - returneaza un string de forma [WHERE] / [AND] + conditii din arrayul queryWheres
     *                   al obiectului $obj furnizat
     */
    public function SET_queryWheres(&$obj, $where='WHERE '){

        $RETwhere = '';

        if(isset($obj->queryWheres) && count($obj->queryWheres) > 0)
            $RETwhere = $where. implode(" AND ", $obj->queryWheres);

        return $RETwhere;
    }

    /**
     * Adauga o conditie la un string de conditii deja existent
     *
     * @param $wheresSTR - stringul de conditii la care se adauga
     * @param $where     - conditia adaugat
     * @return string    - stringul de conditii returnat
     */
    static function ADD_toStr_queryWhere($wheresSTR, $where ){

        $whereConcat = $wheresSTR ? ' AND ' : ' WHERE ';

        return $wheresSTR.$whereConcat.$where;
    }

    /**
     * adauga conditii in arrayul queryWheres ale obiectului $obj
     * daca arrayul nu este setat inca metoda il va seta
     *
     * @param $obj      - obiectul pentru care se seteaza wherurile
     * @param $where    - conditia care se adauga
     */
    public function ADD_queryWheres(&$obj,$where){


        if(!isset($obj->queryWheres)) $obj->queryWheres = array();

        array_push($obj->queryWheres," ".$where." " );

    }




}