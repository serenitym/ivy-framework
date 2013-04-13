<?php

trait tReadOnlyDB {

    public $rodb;

    public function readOnlyConnect() {
        $this->rodb = new mysqli(dbHost,dbroUser,dbroPass,dbName);
        $this->rodb->set_charset("utf8");
    }

}
