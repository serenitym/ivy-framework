ivyMods.user = {
    templates: {
        deactivate      : "GENERAL/user/tmpl/deactivateAccount.php",
        recoverPassword : "GENERAL/user/tmpl/recoverPassword.php?id="+fmw.getData['id']+"&token="+fmw.getData['token'],
        loginForm       : "GENERAL/user/tmpl/loginform.html",
        changePass      : "GENERAL/user/tmpl/changePassword.php",
        inviteMember    : "GENERAL/user/tmpl/inviteMember.php?sid="+$.cookie('PHPSESSID'),
        inviteConfirm   : "GENERAL/user/tmpl/inviteConfirm.php"
    },
    popupwidth: {
        deactivate: '350',
        loginForm: '400',
        changePass: '300',
        inviteMember: '250',
        inviteConfirm: '400'
    },
    forgotPassword : function(){
        fmw.toggle('#recover-pass');
        fmw.toggle("#loginForm");
        fmw.toggle("#p-login");
        fmw.toggle("#p-recover");
    },
    loginForm : function(){
        fmw.toggle('#recover-pass');
        fmw.toggle("#loginForm");
        fmw.toggle("#p-login");
        fmw.toggle("#p-recover");
    },
    popup :function(pubUrl,template,  headerName, uid) {
        fmw.popUp.init({
            pathGet: pubUrl + this.templates[template],
            headerName: headerName,
            widthPop: this.popupwidth[template],
            dataSend: {uid: uid}
        });
    }
};
