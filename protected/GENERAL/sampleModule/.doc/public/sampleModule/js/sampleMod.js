ivyMods.set_iEdit.sampleMod = function(){

    iEdit.add_bttsConf(
        {
            'ENTname':
            {
                modName: 'sampleModule'
                ,edit: {}
                ,deleteBt: {status:false, methName: 'sampleModule->deleteMethName'}
                ,addBt: {
                    atrValue: 'un nume',
                    style : 'oric',
                    class : '',
                    status: '',
                    methName: '',
                    async : new fmw.asyncConf({
                                    modName: 'modName',
                                    methName: 'methName',
                                    parsePOSTfile : 'filePath.php' ,
                                    callBack_fn : (typeof fnName != 'undefined'  ? fnName : ''),
                                    restoreCore : 0
                                })
                    }
                ,saveBt:  {methName: 'sampleModule->updateMethName'}
                  // pentru partea de add
                  ,extraBts:
                  {
                      manageGroup: {
                          callBack: "ivyMods.team.showManageGroups();",
                          attrValue: 'manage Groups',
                          attrName: 'manage Groups',
                          attrType: 'submit/ button',
                          class: ''
                      },
                      showAllmembers:{
                          callBack: "ivyMods.team.showAllMembers();",
                          atrValue: 'show all Members',
                          class: ''
                      }
                  }
                // pentru orice element, trebuie un refactoring aici
                ,extraButtons: {}
            },
            'SINGname':
            {
                // pentru mai multe despre setarea butoanelor in EDITmode see EDITmode.js -> var bttConf
            }
        });
};

ivyMods.sampleMod = {

    otherFunction : function (){}
    ,
    init : function (){}
};


$(document).ready(function(){


    ivyMods.sampleMod.init();

});