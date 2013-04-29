var procesSCRIPT_file = 'procesSCRIPT.php';                 // intermediaza requesturile scripurilor .js
//     UTILIZARE:
// $.post(procesSCRIPT_file,  { parsePOSTfile : parsePOSTfile ,$_POST_array  } )

/* GENERAL purpuse functions */

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


//==============================[ ivyMods ]====================================================
/* Aici vor sta functiile modulelor */
var ivyMods = {
    set_iEdit:{
        //moduleName : function(){}
    }
};

//=============================[ framework related functions ]==============================
var fmw = function(){
// all this functions should be refered from outside as fmw.FunctionName



   function openKCFinder_popUp(callBackFn){
       window.KCFinder = {
              callBack: function(url) {
               //field.value = url;
               alert(url);
               if(callBackFn != '')
               {

                   if(typeof callBackFn == 'function')
                       callBackFn.call(this, url);  // carrousel callback function
                   else
                       alert('functia cu numele '+callBackFn+' nu pare sa fie o functie declarata');

               }
               popUp_remove();
               window.KCFinder = null;
           }
       };

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

   function KCFinder_popUp(options){
           /**
            * opt= {
            *     callBackFn,
            *     jqObj_img
             *
            * }
            * */
            var defaults = {
               callBackFn: '',
               jqObj_img: ''
           }
           var opt=$.extend(true,{},defaults, options);

            window.KCFinder = {
                  callBack: function(url) {
                       //field.value = url;
                       alert(url);


                       if(typeof opt.callBackFn == 'function')
                       {
                           opt.newUrl = url;
                           opt.callBackFn.call(this, opt);
                       }  // carrousel callback function
                       else
                           alert('functia cu numele '+opt.callBackFn+' nu pare sa fie o functie declarata');


                    /*   if(opt.jqObj_img != '')
                       {
                           //alert(opt.jqObj_img.attr('src'));
                           opt.jqObj_img.attr('src',url);
                       }*/

                       popUp_remove();
                       window.KCFinder = null;
               }
           };

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


    /**
     * RET a confing object for async actions
     *
     * @param options
     * @param callBack
     * @return {*}
     */
   function asyncConf(options, callBack){
        /*
          options:
           - parsePOSTfile

           - moduleName
           - methName

          callBack_fn :  callbackTest
          * */
          var defaults = {
              procesSCRIPT : 'procesSCRIPT.php',
              restoreCore : true
          };

          var prop = $.extend(true,{},defaults, options,callBack);



          var fns = {
                  neApelata : function(apel){
                      console.log("Aceasta functie nu ar trebui niciodata apelata "+apel);
                  },

                  callBackFn_dummy: function(data, func){
                      console.log('Nu a fost setata sau trimisa nici o functie de callback '+data + ' chemat de '+ func);
                  },

                  fnpost: function(sendData, callBack_fn){

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
                  fnload : function(jQobj, sendData, callBack_fn){

                      console.log("Am apelat functia fnload");
                      if(typeof jQobj !='undefined')
                      {
                          $.extend(sendData,defaults, options);

                          jQobj.load(prop.procesSCRIPT, sendData, function()
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
  //=========================================================================
    return {
        asyncConf :asyncConf,
        openKCFinder_popUp: openKCFinder_popUp,
        KCFinder_popUp: KCFinder_popUp
    };


}();


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


//PLEASE ENCAPSULATE
function hideShow(){


    $('button[class^=showHidden]').on(

        'click',
        function(){
            $(this).siblings('*[class^=hidden]')
                    .toggle()
                        .css({visibility: "visible", display: "block"});;
            $(this).hide();
            $(this).siblings('*[class^=hiddeHidden]').show();

            return false;
        }
    );

    $('button[class^=hiddeHidden]').on(

        'click',
        function(){
            $(this).siblings('*[class^=hidden]')
                        .toggle()
                            .css({visibility: "hidden", display: ""});;
            $(this).hide();
            $(this).siblings('*[class^=showHidden]').show();

            return false;
        }
    );
}




/*_____________________[labels]________________________________________*/

/**
 * un element care contine taguri este de forma
 * <* class="[EDtags] labels"> label1, label2 , labe3...etc </*>
 *
 * LOGISTICA:
 *  - functia creaza array-ul cu taguri/ labeluri
 *  - daca elemntul cu taguri este editabil atunci la noile splited-labels se va adauga clasa noATmpl
 *          - clasa noATmpl - nu se va afisa acest element in cadrul from#EDITform
 *
 *  - dupa labels => after(  .splited-labels  )
 */
//******************ENCAPSULATE
function convertLabels(){

    //
    $(".labels").map(function(){

        var text_arr = $(this).text().split(', ');

        if(text_arr.length > 0 && text_arr[0]!=' ')
        {

            var noATmpl = $(this).hasClass('EDtags') ? 'noATmpl' : '';

            var splited_labels = '';
            for(var i in text_arr)
                splited_labels +="<span class='label ptb0 r5 label-inverse'>" +
                                    "<small>" +
                                        text_arr[i]+
                                     "</small>"+
                                  "</span>";

            $(this).after
                ("<span class='pull-left splited-labels "+noATmpl+"'>" + splited_labels +"</span> ")
                .hide() ;

        }
    });

}

//todo: extend for multimensional JSON
function jsonConcat(json1, json2) {
    for (var key in json2) {
     json1[key] = json2[key];
    }

 return json1;
}


/*___________________________________________[popUp]________________________________________*/
// si aici e de lucru...
/**
 * WORK popUP - tmpl
 * <div id='popUp-canvas'>
      <div id='popUp'>
         <div id='popUp-header'>
              <span>
                   [ header ]
               </span>
              <button id='popUp-close' onclick='popUp_remove();' class='close'>&times;</button>

         </div>
         <div id='popUp-content'>
                [ loading - container]
         </div>
      </div>
   </div>
 */
function popUp_remove(){
    //alert('in Remove popUp');
    //alert('Am reusit sa selectez '+$('body #popUP-canvas').attr('id'));
    $('body #popUp-canvas').remove();
}
/**
 * SET popUp- HTML template + centralizare in window (1)
 *
 *
 * @param header -  (title) pentru popup
 * @param width  -  optional pt #popUp
 * @param height
 * @return {*}   - returneaza pointer la selectia #popUp-content
 */
function popUp_set_htmlTml(){

    var popUp =
    $('body').prepend
             ("<div id='popUp-canvas'>" +
                 "<div id='popUp'>" +
                    "<div id='popUp-header'>" +
                        "<span>"+this.headerName+"</span>" +
                         "<button id='popUp-close' onclick='popUp_remove();' class='close'>&times;</button>" +

                    "</div>" +
                    "<div id='popUp-content'></div>" +
                 "</div>" +
              "</div>")
             .find('#popUp');
    var popUpContent = popUp.find('#popUp-content');
  //________________________________________________________________________________________________



    if(this.widthPop)
        popUp.css('width',this.widthPop+'px');
    if(this.heightPop)
        popUp.css('height',this.heightPop+'px');

    // 1
    var popupContent_height = popUpContent.height() /2;
    var topPopup = ($(window).height() - popUp.height())/2 -50;
    var margin_left =  popUp.width()/2;

    popUp.css('top',topPopup+'px');
    popUp.css('margin-left','-'+margin_left+'px');




  //________________________________________________________________________________________________

    //alert(topPopup);
    popUpContent.append(
        "<img alt='preloader' src='fw/GENERAL/core/css/img/ajax-loader.gif' " +
               "style='display: block; margin: 0px auto; padding-top:"+popupContent_height+"px;'>");


    popUp.draggable();


    //return popUpContent;


}
/**
 * SET popUp - call setTMPL + .load() settup
 *
 * @param pathLoad      - scriptul chemat de load via  procesSCRIPT_file = procesSCRIPT.php
 * @param dataSend      - JSON - date trimise la script + parsePOSTfile = pathLoad (pt procesSCRIPT.php)
 * @param completeFunc  - numele functiei apelate dupa ce s-a efectuat loadul
 *
 * Parametrii utilizati pentru crearea templateului - vezi popUP_set_htmlTmpl
 *      @param header
 *      @param width
 *      @param height
 */
function popUp_ini(pathLoad, dataSend, completeFunc, header, width, height){


    //______________________________________[ set html TMPL]_________________________________________________

    popUpContent = popUp_set_htmlTml( header, width, height);

    //______________________________________[ set dataSend ]_________________________________________________
    //alert('In popUp_ini');
    // alert(typeof  dataSend);
    if(dataSend instanceof Object) {
        //alert('Este Object');
        dataSend = jsonConcat(dataSend,{parsePOSTfile : pathLoad});
    }
    else {
        //alert('nu este Object');
        dataSend = {parsePOSTfile : pathLoad};
    }

    //_____________________________________[ set load ]__________________________________________________

    setTimeout(function(){
        popUpContent
            .load(
                procesSCRIPT_file,
                dataSend,
                function(){

                    if(completeFunc.length > 0)
                    {
                        if (typeof completeFunc == 'string' &&
                            eval('typeof ' + completeFunc) == 'function')
                        {
                            //alert('Considera ca s-a gasit o functie');
                            eval(completeFunc+'()');

                        }

                        else
                            alert('there is no function named '+completeFunc);
                    }
                    else  alert('there was no function name sent');
                }

             );
    },250);

}

function popUp_load(){

  //_____________________________________[ set load ]__________________________________________________
  /*  alert('procesSCRIPT '+this.procesSCRIPT
        + '\n\n dataSend '+this.dataSend
        + '\n\n pathLoad '+this.pathLoad
        + '\n\n completeFunc '+this.completeFunc + ' length'+this.completeFunc.length
        + '\n\n type '+(typeof this.completeFunc));*/

    var obj      = this;
   // pentru ca this is not in the scope inside setTimeout function

    setTimeout(function(){

        $('#popUp #popUp-content')
              .load(
                  obj.procesSCRIPT,
                  obj.dataSend,
                  function(){
                        obj.popUp_callback();
                  }

               );
    },250);
   // alert( this.completeFunc);
}


function popUp_content(){

    $.when($('#popUp #popUp-content').html(this.content))
        .then(this.popUp_callback());

   // this.popUp_callback();
    //aceaasta procedura ar trebui pusa si ea intr-o metoda

    // callback function?
}
function popUp_callback(){
   // alert('this is the callBack '+ this.completeFunc);
    if(typeof this.completeFunc !='undefined' && this.completeFunc.length > 0)
          {
              if (typeof this.completeFunc == 'string' &&
                  eval('typeof ' + this.completeFunc) == 'function')
              {
                  //alert('Considera ca s-a gasit o functie');
                  eval(this.completeFunc+'()');

              }

              else
                  alert('there is no function named '+this.completeFunc);
          }

}
function popUp_call(opt){

    /*MAN's
    *
    * opt.pathLoad
    * opt.dataSend
    * opt.procesSCRIPT
    *
    * */

    // properties

    this.headerName   = opt.headerName;
    this.widthPop     = opt.widthPop;
    this.heightPop    = opt.heightPop;
    this.completeFunc = opt.completeFunc;


    this.content      = opt.content;


    //methods
    this.popUp_load        = popUp_load;
    this.popUp_content     = popUp_content;
    this.popUp_set_htmlTml = popUp_set_htmlTml;
    this.popUp_remove      = popUp_remove;
    this.popUp_callback    = popUp_callback;
    //this.popUp_loadContent ;//???

    //______________________________________[ set html TMPL]_________________________________________________

    this.popUp_set_htmlTml();
    //alert(this.popUpContent);

    // ini stuf
    if(typeof this.content !='undefined')
    {
        this.popUp_content();
        //alert(this.content);
    }
    else
    {
       /**
        * Daca scriptul meu de process este acelasi cu cel default atunci facem aranjamentele necesare*/
        this.procesSCRIPT = (typeof opt.procesSCRIPT != 'undefined' || typeof opt.procesSCRIPT == 'null' )
                            ? opt.procesSCRIPT
                            : procesSCRIPT_file;

        this.dataSend     = opt.dataSend instanceof Object
                            ? opt.dataSend
                            : {};

        this.dataSend     = (this.procesSCRIPT == procesSCRIPT_file && typeof opt.pathLoad != 'undefined')
                            ? jsonConcat(this.dataSend,{parsePOSTfile : opt.pathLoad})
                            : this.dataSend;


       this.popUp_load();
    }





}


/*=====================[ KCFinder ]=================================*/
function openKCFinder() {
    window.KCFinder = {
        callBack: function(url) {
            //field.value = url;
            alert(url);
            window.KCFinder = null;
        }
    };
    window.open('/fw/GENERAL/core/js/kcfinder/browse.php?type=images', 'kcfinder_textbox',
        'status=0, toolbar=0, location=0, menubar=0, directories=0, ' +
        'resizable=1, scrollbars=0, width=800, height=600'
    );
}

function openKCFinder_IFR() {
    alert('in _IFR');
    var div = document.getElementById('kcfinder_div');
    if (div.style.display == "block") {
        div.style.display = 'none';
        div.innerHTML = '';
        return;
    }
    window.KCFinder = {
        callBack: function(url) {
            window.KCFinder = null;
            //field.value = url;
            div.style.display = 'none';
            div.innerHTML = '';
        }
    };
    div.innerHTML = '<iframe name="kcfinder_iframe" src="/fw/GENERAL/core/js/kcfinder/browse.php?type=images" ' +
        'frameborder="0" width="100%" height="100%" marginwidth="0" marginheight="0" scrolling="no" />';
    div.style.display = 'block';

}


/*_____________________[SETING CURRENT BUTTONs]________________________________________*/

// not sure if this is needed anymore
    current_idT = $('input[name=current_idT]').attr('value');
    current_idC = $('input[name=current_idC]').attr('value');
    $('ul.MENUhorizontal1>li a[id$='+current_idT+']').addClass('current');
    $('div#children_display > ul > li > a#'+current_idC).addClass('current');
/*_____________________________________________________________________________________*/


$(document).ready(function(){

    hideShow();
    convertLabels();


});
