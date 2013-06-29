<?php

trait _dep_TmethDB {

        // deprecateded
    #=============================[ MANAGE wheres ]=======================================
    public function CONCAT_queryWheres(&$mod1, &$mod2,$where='WHERE '){


        $RETwhere = '';
        if(isset($mod1->queryWheres) && isset($mod2->queryWheres))
        {
            $queryWheres = array_merge($mod1->queryWheres,$mod2->queryWheres );

            if(count($queryWheres) > 0)
                $RETwhere = $where.implode(" AND ", $queryWheres);

        }
        return $RETwhere;

    }

    /**
     * STRINGUL DE WHERE pentru un query bazat pe arrayul queryWheres
     *
     * @param $mod
     * @return string  - returneaza un string de forma [WHERE] / [AND] + conditii din arrayul queryWheres
     *                   al obiectului $mod furnizat
     */
    public function SET_queryWheres(&$mod, $where='WHERE '){

        $RETwhere = '';

        if(isset($mod->queryWheres) && count($mod->queryWheres) > 0)
            $RETwhere = $where. implode(" AND ", $mod->queryWheres);

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
     * adauga conditii in arrayul queryWheres ale obiectului $mod
     * daca arrayul nu este setat inca metoda il va seta
     *
     * @param $mod      - obiectul pentru care se seteaza wherurile
     * @param $where    - conditia care se adauga
     */
    public function ADD_queryWheres(&$mod,$where){


        if(!isset($mod->queryWheres)) $mod->queryWheres = array();

        array_push($mod->queryWheres," ".$where." " );

    }




}