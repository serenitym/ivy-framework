<?php
/**
 *  Overwriteuri la functile din CinitModule pentru varianta de admin
 */
class ACsetModule extends Ccore
{
    public function Set_incFiles($modName,$modType,$extension,$folder='',$template='',$adminFolder='')
    {
        parent::Set_incFiles($modName,$modType,$extension,$folder,$template,'');
        parent::Set_incFiles($modName,$modType,$extension,$folder,$template,'ADMIN/');
    }

    public function Module_Fs_configYamlProps(&$mod, $adminPrefix='', $template='')
    {
        parent::Module_Fs_configYamlProps($mod, '', $template);
        parent::Module_Fs_configYamlProps($mod, 'A', $template);
    }

    public function Module_Build($modName, $modType, $adminFolder='ADMIN/', $adminPrefix='AC')
    {

        if (isset($this->adminMods[$modName]) ) {
            parent::Module_Build($modName,$modType,$adminFolder,$adminPrefix);

        } else {
            parent::Module_Build($modName,$modType);
        }
    }

}