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
class CManage extends CmethDB
{
    function Fs_deleteContentRes($dir, $prefix='')
    {
        foreach (glob("$dir/$prefix*.html") as $file) {
            #echo $file."<br>";
            unlink($file);
        }
    }
    function solveAffectedModules($affectedMods, $modType='PLUGINS')
    {
        #var_dump($affectedMods);
        if (is_array($affectedMods)) {
            foreach($affectedMods AS $modNAME)
            {
                $resPath = resPath.$modType.'/'.$modNAME;
                $this->Fs_deleteContentRes($resPath);
            }
        }
    }
    function resetAffectedModules($affectedMods, $resetTreeMethod='')
    {
        if (isset($affectedMods)) {
           foreach($affectedMods AS $modType =>$mods)
            {
                $this->solveAffectedModules($mods,$modType);
            }
        }

        if ($resetTreeMethod && method_exists($this, $resetTreeMethod)) {
            $this->{$resetTreeMethod}();
        }
    }
    /**
    * Regenereaza toate tree-urile deletate de Build_masterTree
    */
    function regenerateAllTrees()
    {
        $queryRES = $this->DB->query("SELECT Cid AS idT from TREE WHERE Pid='0' ");
        while($row = $queryRES->fetch_assoc())
        {
            $this->Set_Fs_Tree(fw_resTree.'tree'.$row['idT'].'.txt',  $row['idT']);
            unset($this->tempTree);
        }
    }
    function resetTree($treeId)
    {
        unlink(fw_resTree."tree{$treeId}.txt");
    }
    function resetCurrentTree()
    {
           unlink(fw_resTree."tree{$this->idTree}.txt");
    }
    function resetAllTrees()
    {
        foreach(glob(fw_resTree.'*.txt') as $treeFile)
        {
            unlink($treeFile);
        }
    }
    /**
     * - Creaza un master un array multidimensional cu toate tree-urile
     * - deleteaza in acelasi timp toate tree-urile
     * - urmand sa fie regenerate de metoda regenerateAllTrees()
     * @param bool $unlinkTrees
     *
     * @return array
     */
    function Build_masterTree($unlinkTrees = true)
    {
        $resTree = fw_resTree ;

        if (is_dir($resTree)) {
            $dir = dir($resTree);
            $masterTREE = array();

            while(false !== ($file=$dir->read()))
            {
                $arr_file = explode('.',$file);
                if(end($arr_file) == 'txt') {
                    $file_path  = $resTree.$file;
                    $tree       = unserialize(file_get_contents($file_path));
                    $masterTREE = $masterTREE + $tree;
                    if ($unlinkTrees) {
                        //stergem toate TREE-urile;
                        unlink($file_path);
                    }
                }
            }
           /* if($this->masterTREE)
                foreach($this->masterTREE AS $id=>$item)
                    echo 'id='.$id.' nameF='.$item->nameF.' type='.$item->type."<br/>";*/
            return $masterTREE;
        }
    }

}