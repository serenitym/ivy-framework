<?php
/**
 * setarile default pentru un anumit modul
 * reflecta setarile din core.yml si Acore.yml
 * sau dintr-un eventual tabel
 *
 * useCase: $core[$modName]->modType etc...
 *
 * $modType - GENERAL / PLUGINS / MODELS / LOCALS
 * $admin   - 0/1 (nu) are clasa de admin
 * $default - 0/1 (nu) este instantziat default
 * $defaultAdmin -0/1 (nu) este instantziat default in modul admin
 */

class module
{
    var $modType;
    var $admin = 0;
    var $default = 0;
    var $defaultAdmin = 0;

    public function __construct($modType){
        $this->modType = $modType;
    }
}