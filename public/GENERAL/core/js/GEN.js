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

fmw.admin = 0;
fmw.idT = 0;
fmw.idC = 0;
fmw.lg = 'ro';

fmw.popUp = {

    /**
     * CALL it like this
     *
     *  fmw.popUp.init(
     *    {
     *
     *     // optional
     *
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

   , popUp_load        : function(){

         //_____________________________________[ set load ]__________________________________________________
          /* alert('procesSCRIPT '+this.procesSCRIPT
                + '\n\n dataSend '+this.dataSend
                + '\n\n pathLoad '+this.pathLoad
              //  + '\n\n completeFunc '+this.completeFunc + ' length'+this.completeFunc.length
             //   + '\n\n type '+(typeof this.completeFunc)
           );*/

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

   , popUp_content     : function(){
               $.when($('#popUp #popUp-content').html(this.content))
                         .then(this.popUp_callback());

               // this.popUp_callback();
                //aceaasta procedura ar trebui pusa si ea intr-o metoda

                // callback function?
        }

   , popUp_set_htmlTml : function(){
                var popUp =
                $('body').prepend
                         ("<div id='popUp-canvas'>" +
                             "<div id='popUp'>" +
                                "<div id='popUp-header'>" +
                                    "<span>"+this.headerName+"</span>" +
                                     "<input  type='button' value='x' id='popUp-close' onclick='fmw.popUp.popUp_remove();' class='close'>" +

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

   , popUp_remove      : function(){
             //alert('in Remove popUp');
            //alert('Am reusit sa selectez '+$('body #popUP-canvas').attr('id'));
            $('body #popUp-canvas').remove();
        }
   , popUp_callback    : function(){
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


