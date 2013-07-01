<?php
/**
 * Ccore
 *
 * @uses CManage
 * @package Core
 * @version 1.0
 * @copyright Copyright (c) 2012 Serenity Media
 * @author  Ioana Cristea
 * @license AGPLv3 {@link http://www.gnu.org/licenses/agpl-3.0.txt}
 */
trait TManage
{
    function delete_contentRES($dir, $prefix='') {
        foreach (glob("$dir/$prefix*.html") as $file) {
            #echo $file."<br>";
            unlink($file);
        }
    }
    function solveAffectedModules($affectedMOD,$typeMOD='PLUGINS') {
        #var_dump($affectedMOD);
        if(is_array($affectedMOD))
            foreach($affectedMOD AS $modNAME)
            {

                $RESpath = resPath.$typeMOD.'/'.$modNAME;
                $this->delete_contentRES($RESpath);
            }
        }

    function reset_affected($affected_mods, $resetTree_method=''){

            if( isset($affected_mods)){
               foreach($affected_mods AS $modType =>$mods)
                {
                    $this->solveAffectedModules($mods,$modType);
                }
            }

            if($resetTree_method) $this->{$resetTree_method}();

        }

    function regenerateAllTrees()    {

        /**
         * Regenereaza toate tree-urile deletate de Build_masterTree
         */
        $queryRES = $this->DB->query("SELECT Cid AS idT from TREE WHERE Pid='0' ");

          while($row = $queryRES->fetch_assoc())
          {

              $this->Set_Fs_Tree(fw_resTree.'tree'.$row['idT'].'.txt',  $row['idT']);
              unset($this->tempTree);
          }
    }

    function reset_tree($treeId){

        unlink(fw_resTree."tree{$treeId}.txt");

    }

    function reset_currentTree(){

           unlink(fw_resTree."tree{$this->idTree}.txt");

    }

    function reset_allTrees(){

        foreach(glob(fw_resTree.'*.txt') as $treeFile)
             unlink($treeFile);
    }

    function Build_masterTree($unlinkTrees = true)     {

        /**
         *  - Creaza un master un array multidimensional cu toate tree-urile
         *  - deleteaza in acelasi timp toate tree-urile
         *  - urmand sa fie regenerate de metoda regenerateAllTrees()
          */

        $RES_TREE = fw_resTree ;

        if(  is_dir($RES_TREE) )
        {
            $dir = dir($RES_TREE);
            $masterTREE = array();

            while(false!== ($file=$dir->read()) )
            {
                $arr_file = explode('.',$file);
                if( end($arr_file) =='txt'  )
                {
                    $file_path = $RES_TREE.$file;
                    $tree = unserialize(file_get_contents($file_path));

                    $masterTREE = $masterTREE + $tree;
                    if($unlinkTrees)
                        unlink($file_path);   //stergem toate TREE-urile;
                }
            }

           /* if($this->masterTREE)
                foreach($this->masterTREE AS $id=>$item)
                    echo 'id='.$id.' nameF='.$item->nameF.' type='.$item->type."<br/>";*/
            return $masterTREE;
        }

      }


}