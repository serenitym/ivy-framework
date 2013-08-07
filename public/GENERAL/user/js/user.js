ivyMods.user = {
    templates :{
        loginForm : "GENERAL/user/tmpl/loginform.html",
        changePass: "GENERAL/user/tmpl/changePassword.html",
        inviteMember: "GENERAL/user/tmpl/inviteMember.html"
    },
    forgotPassword : function(){
        fmw.toggle('#recover-pass');
        fmw.toggle("#loginForm");
    },
    loginForm : function(){
        fmw.toggle('#recover-pass');
        fmw.toggle("#loginForm");
    },
    popup :function(pubUrl,template,  headerName) {
        fmw.popUp.init({
            pathGet: pubUrl + this.templates[template],
            headerName: headerName,
            widthPop: 300
        });
    }
};