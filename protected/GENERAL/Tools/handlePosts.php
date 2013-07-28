<?php

/**
 * BASED ON:
 * - expectedPots is an array that can take many forms
 * - elemente trebuie sa fie declarate totusi in acelasi fel
 * - iata cateva exemple de combinatii
 *
 *--------------------------------------------------------------------------
 * ## Exemplu 1
 *--------------------------------------------------------------------------
 *
 * ** array-ul furnizat **
 * expectedPots :
 *   propName1 :
 *       postName: 'someValue'
 *       [fbk_something: {type: 'error, warning, mess'}]
 *       [validate : [noEmpty, etc..]]
 *  propName2 :
 *      postName: ''
 *
 *  propName3: []
 *
 *
 * ** Nota **
 * - numele proprietatilor (propName) vor avea mereu ca valoare un array
 * chiar daca acesta este gol
 *
 * ** Returned result **
 *
 * => posts (object)
 *      ->propName1 = $_POST[array['postName']]
 *      ->propName2 = $_POST[propName2]
 *      ->propName3 = $_POST[propName3]
 *
 *--------------------------------------------------------------------------
 * ## Exemplu2
 *--------------------------------------------------------------------------
 *
 * ** array-ul furnizat **
 *
 * expectedPots :
 *   propName1: 'postName1'
 *   propName2: ''
 *
 * ** Nota **
 * - propName vor avea mereu ca valoare un string , reprezentand posibilul
 * nume al postului
 *
 * ** Returned result **
 *
 * => posts (object)
 *      ->propName1 = $_POST[postName1]
 *      ->propName2 = $_POST[propName2]
 *
 *--------------------------------------------------------------------------
 * ## Exemplu3
 *--------------------------------------------------------------------------
 *
 * ** array-ul furnizat **
 *
 * expectedPosts: [ propName1, propName2, ...]
 *
 * ** Nota **
 *
 * - postName vor fi = propName
 *
 * ** Returned result **
 *
 * => posts (object)
 *      ->propName1 = $_POST[propName1]
 *      ->propName2 = $_POST[propName2]
 *
 *
 */
class handlePosts
{
    /**
     * Returneaza un obiect cu toate posturile asociate ca proprietati
     * @return posts
     */
    static function Get_post()
    {
        $posts = new stdClass();
        foreach($_POST AS $key => $val){
            $posts->$key = trim($val);
        }

        return $posts;

    }

    function Set_postStrict(&$propName, &$postName)
    {
        $postName =  $propName;
    }
    function Set_postSelectiv(&$propName, &$postName)
    {
        $postName =  $postName ? $postName : $propName;
    }
    function Set_postDescription(&$propName, &$postName)
    {

        $postName =  isset($postName['postName']) && $postName['postName']
                     ? $postName['postName']
                     : $propName;

    }

    /**
     * ## Returneaza numele metodei de determinare a propName & postName
     *--------------------------------------------------------------------------
     *
     * ** determinarea tipului de exepectedPosts **
     *
     * propName poate sa fie
     *  - number
     *      => este un vector numeric
     *      => metoda = Strict
     *  - string
     *      => este un vector asociativ
     *         - daca $postName == array
     *              => postName = array('postName' => 'valuePostName')
     *              => metodata = Description
     *          - daca $postName == string
     *              => postName = numele postului
     *              => metodata = Selective

     *
     * @param $expectedPots
     *
     * @return string - numele metodei de determinare a propName & postName
     */
    static function Get_postMethod($expectedPots)
    {
        reset($expectedPots);
        $firstPropName = key($expectedPots);
        $firstPostName = current($expectedPots);

        // method like = Get_postNameStrict / Get_postNameSelectiv / Get_postNameDesction
        $postMethod ='Set_post'.(is_numeric($firstPropName)
                                    ? "Strict"
                                    : is_array($firstPostName)
                                        ? "Description"
                                        : "Selectiv"
                                );
        // echo "postMethod {$postMethod} <br>";
        return $postMethod;
    }
    /**
     * ## Stepts - Get_postsFlexy
     *--------------------------------------------------------------------------
     *
     * + seteaza numele metodei de determinare a propName & postName
     * + Apelare metoda asociata tipului de array pentru fiecare element in parte
     * + daca avem concat => vom concatena postName si testa daca exista asa
     *                  , daca nu testam direct cu postName
     *
     * + retinem postValue
     * + daca postValue nu este empty  sau putem sa adaugam si empty (!$notEmpty )
     *   o adaugam in obiectul de posts
     * + la starsit returnam obiectul de posts
     *
     *
     * @param        $expectedPots
     * @param string $concat
     * @param bool   $notEmpty
     *
     * @return stdClass
     */
    static function Get_postsFlexy($expectedPots, $concat='', $notEmpty = false)
    {
        $postMethod = handlePosts::Get_postMethod($expectedPots);
        $concat     = $concat ? "_".$concat : '';
        $posts      = new stdClass();

        foreach( $expectedPots  AS $propName => $postName ) {

           handlePosts::$postMethod($propName, $postName);
           $postValue = isset($_POST[$postName.$concat])
                               ? $_POST[$postName.$concat]
                               : (isset($_POST[$postName])
                                  ? $_POST[$postName]
                                  : ''
                                 );

            $postValue = trim($postValue);
            if($postValue || !$notEmpty) {
                $posts->$propName = $postValue;
            }
        }

        return $posts;
    }
}