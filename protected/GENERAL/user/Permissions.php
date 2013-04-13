<?php

class Permissions {

    use tReadOnlyDB;

    public $uid;
    public $cid = 0;
    public $permissions;

    private $_sets = array();
    private $prefix = 'auth_permissions';
    private $dbName;
    private $jsonFile = 'permissionSets.json';

    private $currentPermission = NULL;


    private function readPermissionSets () {
        $dbName = &$this->dbName;
        $prefix = &$this->prefix;
        $query = "SHOW TABLES WHERE Tables_in_$dbName
                              LIKE '$prefix%';";
        try {
            $result = $this->rodb->query($query);
        } catch (Exception $e) {
//            trigger_error($e->message, E_USER_ERROR); #or warning? Not sure...
            return NULL;
        }

        while ($a = $result->fetch_row()) {
            //array_push($this->sets,$a);
            $this->sets[$a[0]] = array();
        }
        return 0;
    }

    private function readPermissionNames () {
        $dbName = &$this->dbName;
        $sets = &$this->sets;
        $uid = &$this->uid;

        foreach ($this->sets as $set => $value) {
            //Toolbox::dump($se
            $query = "DESCRIBE $set";
            $result = $this->rodb->query($query);
            while ($p = $result->fetch_assoc()) {
                if ($p['Field'] != 'gid') {
                    $this->sets[$set][$p['Field']] = NULL;
                }
            }
            $result->free();
        }
    }

    private function readGroupPermission ($gid) {
        $query = "SELECT * FROM ";
            foreach ($this->sets as $key => $set) {
                $query .= "`$key`, ";
                $queryEnd = " WHERE `$key`.`gid` = '$gid';";
            }
        $query = substr($query, 0, -2);
        $query .= $queryEnd;

        $this->permissions = $this->rodb->query($query)->fetch_assoc();

        return 0;
    }

    private function markPermissions ($gid) {
        //$query = "SELECT * FROM ";
            //foreach ($this->sets as $key => $set) {
                //$query .= "`$key`, ";
                //$queryEnd = " WHERE `$key`.`gid` = '$gid';";
            //}
        //$query = substr($query, 0, -2);
        //$query .= $queryEnd;

        $query = "SELECT * FROM auth_permissions_sys ";
            foreach ($this->sets as $key => $set) {
                if ($key != 'auth_permissions_sys') {
                    $query .= "JOIN $key ON (auth_permissions_sys.gid = $key.gid) ";
                    $queryEnd = "WHERE auth_permissions_sys.gid = '$gid'";
                }
            }
        $query .= $queryEnd;

        //Toolbox::dump($query, 'Query');

        $permissions = $this->rodb->query($query)->fetch_assoc();

        //Toolbox::dump($permissions, 'permissions');

        return $permissions;
    }

    private function permissionSet ($value = 1) {
        $this->currentPermission = $value;
        return 0;
    }

    private function selectPermission (&$name) {
        $this->currentPermission = &$name;
    }

    private function jsonSave () {
        file_put_contents($this->jsonFile, json_encode($this->sets));
        return 0;
    }

    public function __construct($uid) {
        $this->uid = $uid;
        $this->dbName = dbName;

        $this->readOnlyConnect();

        global $core;

        $this->user = &$core->user;

        $this->jsonFile = tmp_path . $this->jsonFile;

        $this->readOnlyConnect();

        $this->readPermissionSets();
        $this->readPermissionNames();
        $this->readGroupPermission($this->uid);

        //Toolbox::dump($this->permissions, 'Permissions');

        foreach($this->user->groups as $gid) {
            $this->markPermissions($gid);
        }

    }
}
