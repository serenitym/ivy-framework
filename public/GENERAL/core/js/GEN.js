var procesSCRIPT_file = 'procesSCRIPT.php';                 // intermediaza requesturile scripurilor .js
//     UTILIZARE:
// $.post(procesSCRIPT_file,  { parsePOSTfile : parsePOSTfile ,$_POST_array  } )

//DOCUMENTEAZA URGENT !!!!

//==============================[ jQuery - extensions ]=====================================

$.fn.collectData = function(){

     var selector = arguments.length  > 0  ? arguments[0]
                      :"input[type=text] ,input[type=hidden] , textarea, select" ;

    var data = {};
     if(this.length == 1){

         this.find(selector).map(function(){
             var  input_Name  = $(this).attr('name');
             var  input_Value = $(this).val();
             data[input_Name] = input_Value;
         });
         console.log("Am apelat pluginul de jquery collectData ");
     }
    else
     {
         console.log("jquery - plugin  collectData dar au fost selectate 2 formuri");

     }

    return data;
}

$.fn.maxHeight = function(){

    var max = 0;
    this.each(
        function(){
            max = Math.max(max,$(this).height());
        }
    );
    return max;
}

$.fn.minHeight = function(){

    var min = 3000;
    this.each(
        function(){
            min = Math.min(min,$(this).height());
        }
    );
    return min;
}

$.fn.exists = function () {
    return this.length !== 0;
}


//==============================[ ivyMods ]====================================================
/* Aici vor sta functiile modulelor */
// pus in core / footer
if(typeof ivyMods == 'undefined') {

    var ivyMods = {
         set_iEdit:{
         //modName : function(){}
         }
     };
}

//=============================[ framework related functions ]==============================
var fmw = {};

fmw.admin = 0;
fmw.idT = 0;
fmw.idC = 0;
fmw.lg = 'ro';
fmw.ajaxProxy = 'procesSCRIPT.php';
fmw.ajaxReqFile = 'ajaxReqFile';

/**
 * Use case :
 *  <input type='button'  value='more settings' onclick='fmw.toggle(\"form[id^=EDITform] .admin-extras\"); return false;' />
 *
 * @param selection - will toggle the selection requested
 * @return {Boolean}
 */
fmw.toggle = function(selection, opt){
    /**
     * opt : {
     *  caller : '',
     *  class: [classClick,  class ],
     *  value: [valueClick, valueDef]
     *  }
     */
    opt = fmw.isset(opt) ? opt : {};
    var defaults = {
        caller: '',
        class : ['', ''],
        value : ['', '']

    };


    $(selection).toggle();
    $.extend(true, opt, defaults);

    if(opt.caller != '') {
        var visible = $(selection).is(":visible");
        //console.log("element is "+ (visible ? "visible" : "notvisible"));

        if(opt.class != '') {
            opt.caller.attr('class', (visible ? opt.class[1]: opt.class[0]));
            //console.log( opt.selector.attr('class') + " "+ opt.class[1]);
        }

        if(opt.value != '') {
            opt.caller.attr('class', (visible ? opt.value[1]: opt.value[0]));
        }

    }
    return false;
}
fmw.isset = function(variable) {
    if(typeof  variable == 'undefined') {
        return false;
    } else {

        return true;
    }
}

// not working good dont know yet why
fmw.notempty = function(variable) {
    if(!fmw.isset(variable)) {
        return false;
    }
    if(variable == 0 || variable == '') {
        return false;
    }
    return true;
}

fmw.asyncConf = function(options, callBack){

  /**
   * options:
      - parsePOSTfile
       - modName
      - methName
      callBack_fn :  callbackTest
     * */
  var defaults = {
      procesSCRIPT : 'procesSCRIPT.php',
      restoreCore : 1
  };
  var prop = $.extend(true,{},defaults, options,callBack);

  var fns = {
      neApelata : function(apel){
          console.log("Aceasta functie nu ar trebui niciodata apelata "+apel);
      },
      callBackFn_dummy: function(data, func){
          console.log('Nu a fost setata sau trimisa nici o functie de callback '+data + ' chemat de '+ func);
      },
      fnpost:  function(sendData, callBack_fn){

          console.log("Am apelat functia fnpost");

          $.extend(sendData, defaults,options);

          $.post(prop.procesSCRIPT, sendData, function(data)
          {
              if(typeof callBack_fn == 'function')
              {
                  callBack_fn.call(this, data);
                  console.log("fnpost - a intrat in primul if");
              }

              else if(typeof prop.callBack_fn == 'function')
              {
                  console.log("fnpost - a intrat in 2 if si "+(callBack_fn.constructor) );

                  if(typeof callBack_fn != 'undefined' && callBack_fn.constructor == Array)
                  {
                      console.log("fnpost - callBack_fn este un array");
                      prop.callBack_fn.apply(this, callBack_fn);
                  }
                  else
                  {
                     // console.log("fnpost - callBack_fn NU este un array");
                      prop.callBack_fn.call(this, data);
                  }


              }

              else
              {
                  fns.callBackFn_dummy.call(this, data,"fnpost");
              }

          });

      },
      fnload : function(jQmod, sendData, callBack_fn){

          console.log("Am apelat functia fnload");
          if(typeof jQmod !='undefined')
          {
              $.extend(sendData,defaults, options);

              jQmod.load(prop.procesSCRIPT, sendData, function()
              {
                    if(typeof callBack_fn == 'function')
                    {
                        callBack_fn.call();
                        console.log("fnload - a intrat in primul if");
                    }

                    else if(typeof prop.callBack_fn == 'function')
                    {
                        prop.callBack_fn.call();
                        console.log("fnload - a intrat in 2 if");
                    }

                    else
                        fns.callBackFn_dummy.call(this, "","fnload");

              });
          }
          else{ console.log("Selectorul folosit nu a fost bun"); }

      }

  };

  return $.extend(prop, fns);

}
fmw.KCFinder_popUp = function(options){

    /**
     * opt= { callBackFn, jqmod_img }
     * */
    var defaults = {
        callBackFn: '',
        jqmod_img: ''
    }
    var opt=$.extend(true,{},defaults, options);

    window.KCFinder = {
        callBack: function(url) {
            //field.value = url;
            console.log("fmw.KCFinder_popUp - "+url);
            if (typeof opt.callBackFn == 'function') {
                opt.newUrl = url;
                opt.callBackFn.call(this, opt);
            }  else {
                // carrousel callback function
                console.log("fmw.KCFinder_popUp - "+'functia cu numele '
                    +opt.callBackFn+' nu exista'
                );
                /*   if(opt.jqmod_img != '')
                 {
                 //alert(opt.jqmod_img.attr('src'));
                 opt.jqmod_img.attr('src',url);
                 }*/
                popUp_remove();
                window.KCFinder = null;
            }
        }
    };

    //@todo: schimbat pe noul fmw.popup
    var popUpKCF = new popUp_call(
                { content:
                    "<div id='kcfinder_div'>" +
                        '<iframe name="kcfinder_iframe" src="/assets/kcfinder/browse.php?type=images" ' +
                                'frameborder="0" width="100%" height="450px" marginwidth="0" marginheight="0" scrolling="no" />'+
                    "</div>",

                    widthPop:'900'
                    ,heightPop : '500'
                });

    // variabila popUPKCF - poate ar trebui sa ii dau unset somehow

}
fmw.openKCFinder_popUp = function(callBackFn){

    window.KCFinder = {
           callBack: function(url) {
            //field.value = url;
            console.log("fmw.openKCFinder_popUp"+url);
            if (callBackFn != '') {
                if (typeof callBackFn == 'function') {
                    callBackFn.call(this, url);  // carrousel callback function

                } else {
                    console.log("fmw.openKCFinder_popUp"+' - functia cu numele '
                        + callBackFn+' nu pare sa fie o functie declarata'
                    );
                }
            }
            popUp_remove();
            window.KCFinder = null;
        }
    };
    //@todo: pune pe noul pop-ul al frameworkului
    var popUpKCF = new popUp_call(
                { content:
                    "<div id='kcfinder_div'>" +
                        '<iframe name="kcfinder_iframe" src="/assets/kcfinder/browse.php?type=images" ' +
                                'frameborder="0" width="100%" height="450px" marginwidth="0" marginheight="0" scrolling="no" />'+
                    "</div>",

                    widthPop:'900'
                    ,heightPop : '500'
                });

    // variabila popUPKCF - poate ar trebui sa ii dau unset somehow

}

fmw.popUp = {

    /**
     * CALL it like this
     *
     *  fmw.popUp.init(
     *    {
     *
     *     // optional
     *
     *      pathGet
     *       pathLoad: ''
     *       dataSend: ''
     *       procesSCRIPT : ''
     *
     *     // properties
     *
     *       headerName  : ''
     *       widthPop    : ''
     *       heightPop   : ''
     *       completeFunc: ''
     *
     *       content     : ''
     *  })
     * */

    init: function(opt){
        /*MAN's
        *
        * // un template care are nevoie de o randare complexa
        * // ex: acces la alte module, la BD etc...
        * // de aceea e posibil sa fie nevoie de core => procesSCRIPT
        *
        * opt.pathLoad
        * opt.dataSend {ajaxReqFile : '', alteVars: '', modName: '', methName: ''}
        * opt.ajaxProxy
        *
        * //un template care trebuie sa ii fie luat asa cum este
        * // ii se poate trimite si un dataSend
        *
        * opt.pathGet
        * opt.dataSend
        *
        * opt.callbackFn: {
        *   fn: 'functia',
        *   context: 'obiectul in care sa fie cotextul',
        *   args: 'lista/ array cu argumentele trimise catre functie'
        *   }
        *
        * */

        // properties
/*
        this.headerName   = opt.headerName;
        this.widthPop     = opt.widthPop;
        this.heightPop    = opt.heightPop;
        this.callbackFn  = opt.callbackFn;
        this.content      = opt.content;
*/

        //this.popUp_loadContent ;//???
        // defaults
        this.ajaxProxy = fmw.ajaxProxy;
        this.dataSend = { sessionId : $.cookie("PHPSESSID")};

        // incorporate options
        $.extend(true, this, opt);

        //______________________________________[ set html TMPL]_________________________________________________

        this.popUp_set_htmlTml();
         //console.log(this.popUpContent);
        // ini stuff
        if (typeof this.content !='undefined') {
            this.popUp_content(this.content);
            //console.log(this.content);
        } else if(typeof opt.pathGet != 'undefined') {

            var dataSend = typeof opt.dataSend == 'undefined' ? '' :
                           opt.dataSend;
            //console.log('GEN.js - Datele trimise'+ dataSend);

            $.get(opt.pathGet , dataSend, function(data) {
                fmw.popUp.popUp_content(data);
            });

        } else {

            //Daca scriptul meu de process este acelasi cu cel default atunci facem aranjamentele necesare
             /*  this.ajaxProxy = (typeof opt.ajaxProxy != 'undefined' || typeof opt.ajaxProxy == 'null' )
                                ? opt.ajaxProxy
                                : fmw.ajaxProxy;

            this.dataSend = { sessionId : $.cookie("PHPSESSID")};
            this.dataSend     = opt.dataSend instanceof object
                                ? opt.dataSend
                                : {};

            this.dataSend     = (this.ajaxProxy == fmw.ajaxProxy && typeof opt.ajaxReqFile != 'undefined')
                                ? $.extend(this.dataSend, {ajaxReqFile : opt.ajaxReqFile})
                                : this.dataSend;*/

            this.popUp_load();
        }
    }
    ,
    popUp_load        : function(){

       /* var testSendData = '';
        for(var i in this.dataSend) {
            testSendData += " name = "+ i + "value = "+ this.dataSend[i] + '\n';
        }*/
        //_____________________________________[ set load ]__________________________________________________
        /* console.log('ajaxProxy '+this.ajaxProxy
               + '\n\n dataSend '+ testSendData
               + '\n\n ajaxReqFile '+this.ajaxReqFile
           //  + '\n\n completeFunc '+this.completeFunc + ' length'+this.completeFunc.length
          //   + '\n\n type '+(typeof this.completeFunc)
        );*/

        // pentru ca this is not in the scope inside setTimeout function
        var mod      = this;
        setTimeout(function(){
            $('#popUp #popUp-content')
                  .load(
                      mod.ajaxProxy,
                      mod.dataSend,
                      function(){
                            mod.popUp_callback();
                      }
                  );
        },250);
       // alert( this.completeFunc);
    }
    ,
    popUp_content     : function(content){
        $.when(
            $('#popUp #popUp-content')
                .html(content)
        ).then(
            this.popUp_callback()
        );

        // this.popUp_callback();
        //aceaasta procedura ar trebui pusa si ea intr-o metoda
        // callback function?
    }
    ,
    popUp_set_htmlTml : function(){
        var popUp =
        $('body').prepend
                 ("<div id='popUp-canvas'>" +
                     "<div id='popUp'>" +
                        "<div id='popUp-header'>" +
                            "<span>"+this.headerName+"</span>" +
                             "<input  type='button' value='x' id='popUp-close' onclick='fmw.popUp.popUp_remove();' class='close ivy'>" +

                        "</div>" +
                        "<div id='popUp-content'></div>" +
                     "</div>" +
                  "</div>")
                 .find('#popUp');
        var popUpContent = popUp.find('#popUp-content');

        if (this.widthPop) {
            popUp.css('width',this.widthPop+'px');
        }
        if (this.heightPop) {
            popUp.css('height',this.heightPop+'px');
        }

        var popupContent_height = popUpContent.height() /2;
        var topPopup = ($(window).height() - popUp.height())/2 -50;
        var margin_left =  popUp.width()/2;

        popUp.css('top',topPopup+'px');
        popUp.css('margin-left','-'+margin_left+'px');


        //console.log(topPopup);
        popUpContent.append(
            "<img alt='preloader' src='fw/GENERAL/core/css/img/ajax-loader.gif' " +
                   "style='display: block; margin: 0px auto; padding-top:"+popupContent_height+"px;'>");

        popUp.draggable();

         //return popUpContent;
    }
    ,
    popUp_remove      : function(){
         //alert('in Remove popUp');
        //alert('Am reusit sa selectez '+$('body #popUP-canvas').attr('id'));
        $('body #popUp-canvas').remove();
    }
    ,
    popUp_callback    : function(){
        // alert('this is the callBack '+ this.completeFunc);
        if (typeof this.callbackFn !='undefined'
            && typeof this.callbackFn.fn == 'function')  {
            var context = this;
            var args = [];
            if(typeof this.callbackFn.context != 'undefined') {
                context = this.callbackFn.context;
            }
            if(typeof this.callbackFn.args != 'undefined') {
                args = this.callbackFn.args;
            }


            this.callbackFn.fn.apply(context, args);
        }
    }

};

// testing stuff
/*function callbackTest(varTest, varTest2){ console.log("a fost apelata functia de callBack " + varTest +' si ' + varTest2);}

var testAsync = new asyncConf({
    parsePOSTfile : 'testProcesScript.php',
    restoreCore : 0
    },
    {callBack_fn : callbackTest}
);
//testAsync.callBack_fn = callbackTest;
//testJq =  $('body').prepend("<div id='loadTest'></div>").find('#loadTest');
var testName = 'Un nume';
//testAsync.fnpost({test : 'variabila de test'},
//    function(testName){console.log("A fost apelata o functie nonName cu "+testName);});
// sau
//testAsync.fnpost({test : 'variabila de test'},callbackTest);
//sau
testAsync.fnpost({test : 'variabila de test'},['variabila1','variabila2']);*/


