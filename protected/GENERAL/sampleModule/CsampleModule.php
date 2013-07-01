<?php

class CsampleModule{
/**
 * ======[ This module has access to ]=======================
 *
 *  $mod->C                     # obiectul principal core
 *
    # situatie core
    $mod->DB     =              # pointer la BD
    $mod->admin  =              # true / false - daca sunt pe admin
    $mod->LG     =              # limba curenta
    $mod->lang   =              # limba curenta
    $mod->nameF  =              # numele de RES al paginii/ categoriei curente in limba curenta
 *                                 ex: name: Categorie Noua = Categorie_noua


    # date ale modu
    $mod->idNode    =             # id-ul categoriei curente
    $mod->idTree    =             # id-ul parintelui originar
    $mod->level  =             # levelul din tree la care se afla cat
    $mod->type   =             # tipul categoriei curente ex: MODELS / LOCALS


    #date despre acest modul
    $mod->modName =            # numele modulului
    $mod->modType =            # tipul acestuia : GENERAL/ LOCALS /MODELS/ PLUGINS
 *
 *
 *
 * =====[ USABLE DB - methods ]=================================
 *
 *    GET_resultArray ($result, $method = 'fetch_assoc')
 *        * returneaza un array multdimensional cu datele returnate de $result
 *
 *
 *    GET_modProperties(&$mod,$query,$processResMethod='', $onlyArr = false)
 *
 *      *  $mod                              - obiectul care a apelat metoda
        *  $query                            - query-ul de procesat
        *  string $processResMethod($row)    - metoda a $mod care proceseaza orice rand returnat
        *  bool $onlyArr                     - daca queryul ret un singur record
        *                                              false - va seta valoriile ret la mod
        *                                              true - va returna un array[0] = array(colum=>value);
        * return array                     - array muldimensional cu toate recordurile returnate de query
        *                                      si procesate de processResMethod
 *
 *   USE LIKE this
 *      $this->news = $this->C->GET_modProperties($this, $query, 'procesNews');
 *
 *   =>$this->news = array(0=> [title=>'', content=>'', idNews=>'' ], 1=> [], ...);
 *
 *
*/
    /**
     * Apelata imediat dupa instantierea modulului
     * like a second __construct()
     */
    function _setINI(){

    }
    function DISPLAY(){

        return 'Acesta ar trebui sa fie un sample';
    }

    function __construct(&$C){}
}