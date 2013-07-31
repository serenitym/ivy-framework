/**
 * UTILIZARE GENERALA EDITmode.js
 *
 * < * class='allENTS [otherClasses] [entSName]' id = '[entSName]_[LG]' >
 *     - add new ent
 *
 *     <class='ENT [otherClasses] [entName]' id = '[entName]_[id]_[LG]' >
 *
 *  - delete
 *  - edit
 *  - save
 *  - exit edit (cancel)
 *
 *  < * class='SING [otherClasses] [singName]' id = '[singName]_[id]_[LG]' >
 *      - edit
 *      - save
 *      - exit edit (cancel)
 */
/**
 *  UTILIZARE:
   $.post(procesSCRIPT_file,  { parsePOSTfile : parsePOSTfile ,$_POST_array  } )
 */
// DOCUMENTEAZA TOT editMODEUL URGENT

var LG = 'ro';
var procesSCRIPT_file     = 'procesSCRIPT.php';                   // intermediaza requesturile scripurilor .js
var parsePOSTfile_getTags = 'MODELS/blog/ADMIN/getTags.php';


var iEdit = function(){

    var LG = 'ro';

    /**
     * Config ex:
     *
     *  elemName_ex: {
     *            modName : '',
     *            addBt: {
     *                atrValue: 'un nume',
     *                style : 'oric',
     *                class : '',
     *                status: '',
     *                methName: '',
     *                async : new asyncConf({
     *                                modName: 'modName',
     *                                methName: 'methName',
     *                                parsePOSTfile : 'filePath.php' ,
     *                                callBack_fn : (typeof fnName != 'undefined'  ? fnName : ''),
     *                                restoreCore : 0
     *                            })
     *                },
     *            edit: {},
     *            deleteBt : {methName:'', status : 1, atrName:"delete_"+Name, atrValue: 'd' },
                  saveBt : {methName:'',status : 1, atrName:"save_"+Name, atrValue: 's' },

                  // pentru partea de add
                  extraBts:
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
                  extraButtons: {}
     *       },
     *
     *  Utilizarea configului
     *
     *  bttConf[elemName].add['name']
     *
     *  bttConf[elemName].add['async']
     *      .  fnload( jQobj, sendData, callBack_fn)
     *      .  fnpost( sendData, callBack_fn)
     *      .  allProperties
     */
    var bttConf =    {
            comment : {
                addBt : {status :false},
                saveBt: {status : false}
            }
    };

    //========================================[ PROTECTED FUNCTIONS ]====================================
    /**
     * ret : JSON - button config of an editable element
     *
     * @param Name      - the name of the editable element
     * @param defaults  - a JSON object of defaults in case no confing was made
     * @return {*}      - JSON - button config of an editable element
     */
    function getBtt(Name, defaults){

           if(typeof bttConf[Name] !='undefined') return  $.extend(true,defaults, bttConf[Name]);
           else return defaults;
    }

    function split( val )        {
        return val.split( /,\s*/ );
    }

    function extractLast( term ) {
        return split( term ).pop();
    }

    function actionBtns_binds(BLOCK,Name,id){

         var TOOLSbtn = BLOCK.next('form').find('.TOOLSbtn');
         if(TOOLSbtn.length > 0  && typeof bttConf[Name] != 'undefined')
         {
             var BTT = bttConf[Name];

              //=============================[set moduleControler for action ]==========================================

             if(typeof BTT.modName !='undefined'){
                 TOOLSbtn.parent().before(
                     "<input type='hidden' name='modName' value='"+BTT.modName+"' />" +
                      "<input type='hidden' name='methName' value='' />");
                 var jq_ctrlmodName = TOOLSbtn.parent().prev("input[name=methName]");
                // console.log(' jq_ctrlmodName '+jq_ctrlmodName.attr('name'));
             }

             //=============================[delete button ]============================================================
             //if( typeof BTTdelete.asincr[Name]!='undefined')
             if(typeof BTT.deleteBt !='undefined')
             {
                 if(typeof BTT.deleteBt.async !='undefined')
                 {
                     TOOLSbtn
                         .find('input[class^=editModeBTT][name^=delete_]')
                         .attr('onclick',"iEdit.evCallback.async_delete('"+Name+"','"+id+"'); return false;");
                 }
                 else if(typeof BTT.modName !='undefined' && typeof BTT.deleteBt.methName != 'undefined' )
                 {
                      TOOLSbtn
                         .find('input[class^=editModeBTT][name^=delete_]')
                          .on('click', function(){
                              jq_ctrlmodName.attr('value',BTT.deleteBt.methName);
                          });
                 }
             }
             // save button
             if( typeof BTT.saveBt != 'undefined')
             {
                 if(typeof BTT.modName !='undefined' && typeof BTT.saveBt.methName != 'undefined' )
                 {
                     /**
                      * ATENTIE
                      *     - nu stiu de ce nu merge cu live
                      * */
                     TOOLSbtn
                      .find('input[class^=editModeBTT][name^=save_]')
                         .on('click', function(){
                              jq_ctrlmodName.attr('value',BTT.saveBt.methName);
                             // alert('Click on save button ' + jq_ctrlmodName.attr('value'));

                          });

                 } else if( typeof BTT.saveBt.async != 'undefined') {
                      TOOLSbtn
                          .find('input[class^=editModeBTT][name^=save_]')
                          .attr('onclick',"iEdit.evCallback.async_save('"+Name+"','"+id+"'); return false;");

                      /* console.log(' pentru '+Name
                       +' avem callback-ul ' +  BTT.saveBt.async.callBack_fn
                       + ' si file-ul de procesare ' + BTT.saveBt.async.parsePOSTfile);*/
                 }


             }



         }


    }

    function async_save_reconstruct(Name ,id, postData){

        var test = '';
            $("*[id^="+Name+"_"+id+"_] *[class^=ED]").map(function(){

                var EDname =  $(this).attr('class').split(' ').pop();
                $(this).html(postData[EDname+'_'+LG]);

                test +=EDname + ' = '+ postData[EDname+'_'+LG]+' \n \n';
            });
            alert("iEdit - async_save_reconstruct "+test);


    }

    function transform(BLOCK,formSelector, elmName){

        $('*[classs$=hoverZoomLink]').removeClass('hoverZoomLink');

         BLOCK.find('*[class^=ED]').map(function()
         {
              // selELEM =  $(this).attr('class')+' ';
              if($(this).parents('*[class^=allENTS]').length <= 1)
              {

                  var EDclass  = $(this).attr('class');
                  //    EDclass  = $.trim(EDclass);

                   console.log(EDclass);
                   //var desc   = ($(this).attr('class')+' ').split(' ');
                   var desc   = (EDclass).split(' ');
                   var EDtype  = desc[0];
                    /**
                     * (-2)  1- este ca sa imi ajunga la 0 si inca 1 pentru ca
                     * pune un elem in plus , nu stiu de ce
                     * @type {*}
                     */
                   var EDname  = desc[desc.length-1];
                   var EDvalue = ( EDtype=='EDeditor' || EDtype=='EDpic' )
                                ? $.trim($(this).html())
                                : $.trim($(this).text());


                   console.log('EDtype '+EDtype+'  EDname '+EDname+' value '+EDvalue);
                   var EDtag  = formSelector + ' *[class^='+EDtype+'][class$='+EDname+']';
                   var jqEDtag = $(EDtag);

                  // perform the actual transform of the element
                   if (jqEDtag.length > 0) {
                        replace(EDtype, EDname, EDvalue,formSelector, jqEDtag);
                   }  else {
                        console.log(EDtag);
                   }

              }

         });
    }

    function replace(EDtype, EDname, EDvalue,formSelector, jqEDtag){

          //alert(EDtype+' '+EDname+" "+EDvalue+" "+formSelector);

       // var EDtag        = formSelector + ' *[class^='+EDtype+'][class$='+EDname+']';
        //var INPUTname    = EDname+"_"+LG;
        var INPUTname    = EDname;
        var INPUTclass   = 'EDITOR '+EDname;

        var INPUTclasses =jqEDtag.attr('class').replace(EDtype, 'EDITOR');
        var EDtag_height =jqEDtag.height();
        var EDtag_width  =jqEDtag.width();


        var EDreplace ={
            EDtxtp   : function (){
                        return "<input type='text' name='"+INPUTname+"'  class='"+INPUTclasses+"' value='"+EDvalue+"' placeholder='"+EDname+"' />";
                      },

            EDtxt    : function (){
                       return "<input type='text' name='"+INPUTname+"'  class='"+INPUTclasses+"' value='"+EDvalue+"' />";},
            EDdate   : function (){
                       return "<input type='text' name='"+INPUTname+"'  class='"+INPUTclasses+"' value='"+EDvalue+"' />";},
            EDtags   : function (){
                       return "<input type='text' name='"+INPUTname+"'  class='"+INPUTclass+"' value='"+EDvalue+"' />";},

            EDtxa    : function (){
                     return "<textarea   name='"+INPUTname+"'  class='"+INPUTclasses+"' >"+EDvalue+"</textarea>"; },
            EDeditor : function (){
                     return "<textarea   name='"+INPUTname+"'  class='"+INPUTclasses+"'  id='editor_"+EDname+'_'+LG+"' >"+EDvalue+"</textarea>";},
            EDaddEditor : function (){
                     return "<textarea   name='"+INPUTname+"'  class='"+INPUTclasses+"'  id='editorAdd_"+EDname+'_'+LG+"' >"+EDvalue+"</textarea>";},
            EDpic    : function (){
                             //var  IDpr = $('input[name=IDpr]').val();
                             // if(typeof IDpr!='undefined') INPUTname='';
                             //(form,url_action,id_element,html_show_loading,html_error_http)
                            /* $('form[id^=EDITform]').attr('enctype','multipart/form-data');
                             $('form[id^=EDITform]').attr('encoding','multipart/form-data');
                            return "<div class='"+INPUTclass+"' id='frontpic'>" +
                                                     EDvalue+
                                                    "<div  id='formUPL' >"+
                                                         "<input type='file' id='fileUPL' name='filename_"+INPUTname+"' class='fileinput'  />" +
                                                         "<input type='hidden' name='id' value='"+IDpr+"'>" +
                                                        *//* "<input type='submit' name='UPLDimg' value='UP'>"+*//*
                                                     "</div>" +
                                                 "</div>"
                                                 ;*/
                            var imgSrc =jqEDtag.attr('src');

                            /*alert(EDtag + ' '+ $(EDtag+"[src*=placehold.it]").attr('src') );*/
                            var hiddenValue = imgSrc.search("placehold") > 0
                                              ? ''
                                              : imgSrc;

                           // alert('hiddenValue is '+hiddenValue+ ' '+imgSrc.search("placehold"));
                            //return  "<img class='"+imgClasses+"' src='"+imgSrc+"'>";

                            return   "<img class='"+INPUTclasses+"' src='"+imgSrc+"' id='editImg_"+INPUTname+"'>" +
                                     "<input type='hidden' name='"+INPUTname+"' value='"+hiddenValue+"' />" +
                                     "<input type='button' name='replaceImg' value='loadImg' " +
                                                          " style='left: 0;position: absolute;'" +
                                                          " onclick='iEdit.evCallback.loadPic(\"editImg_"+INPUTname+"\")'>";


                 },
            EDaddPic : function(){
                            var imgSrc =jqEDtag.attr('src');

                            //return  "<img class='"+imgClasses+"' src='"+imgSrc+"'>";

                            return   "<img class='"+INPUTclasses+"' src='"+imgSrc+"' id='editAddImg_"+INPUTname+"'>" +
                                     "<input type='hidden' name='"+INPUTname+"' value='' />" +
                                     "<input type='button' name='replaceImg' value='loadImg' " +
                                                           " style='left: 0;position: absolute;'" +
                                                           " onclick='iEdit.evCallback.loadPic(\"editAddImg_"+INPUTname+"\")'>" ;
            },
            EDsel    : function (){
                            if(eval('typeof '+EDname+'!= "undefined" ')) var options = eval(EDname+'.getHTMLoptions("'+EDvalue+'")');
                            else alert('nu l-a recunoscut ca obiect');
                            return "<select name='"+INPUTname+"' class='"+INPUTclass+"'>"+options+"</select>";
                            }
        };


        var EDcallback = {
             EDeditor : function(){
                   var toolbar_conf = (EDtag_width < 500 ? 'EXTRAsmallTOOL' : 'smallTOOL' );
                   CKEDITOR.replace( 'editor_'+EDname+'_'+LG,
                                         {
                                             toolbar : toolbar_conf,
                                             height : EDtag_height+'px'
                                           ,width : EDtag_width
                                         });
                                 //$("textarea[id=editor_"+EDname+'_'+LG+"]").ckeditor();

             },
             EDaddEditor : function(){
                   var toolbar_conf = (EDtag_width < 500 ? 'EXTRAsmallTOOL' : 'smallTOOL' );
                   CKEDITOR.replace( 'editorAdd_'+EDname+'_'+LG,
                                         {
                                             toolbar : toolbar_conf,
                                             height : EDtag_height
                                           ,width : EDtag_width
                                         });
                                 //$("textarea[id=editor_"+EDname+'_'+LG+"]").ckeditor();

             },
             EDtxa: function(){
                  $(formSelector+' textarea[name='+INPUTname+']').css('height',EDtag_height);
             },
             EDdate   : function(){
                            $(formSelector+' input[name='+INPUTname+']').datepicker({dateFormat: 'yy-mm-dd'});
             },
             EDtags   : function(){
             //Alt exemplu cu array predefinit
            /*$('input[name='+INPUTname+']')
         // don't navigate away from the field on tab when selecting an item
           .bind( "keydown", function( event ) {
               if ( event.keyCode === $.ui.keyCode.TAB &&
                       $( this ).data( "autocomplete" ).menu.active ) {
                   event.preventDefault();
               }
           })
           .autocomplete({
               minLength: 0,
               source: function( request, response ) {
                   // delegate back to autocomplete, but extract the last term
                   response( $.ui.autocomplete.filter(
                       availableTags, extractLast( request.term ) ) );
               },
               focus: function() {
                   // prevent value inserted on focus
                   return false;
               },
               select: function( event, ui ) {
                   var terms = split( this.value );
                   // remove the current input
                   terms.pop();
                   // add the selected item
                   terms.push( ui.item.value );
                   // add placeholder to get the comma-and-space at the end
                   terms.push( "" );
                   this.value = terms.join( ", " );
                   return false;
               }
           });*/

                    $('input[name='+INPUTname+']')
                   // don't navigate away from the field on tab when selecting an item
                     .bind( "keydown", function( event ) {
                         if ( event.keyCode === $.ui.keyCode.TAB &&
                                 $( this ).data( "autocomplete" ).menu.active ) {
                             event.preventDefault();
                         }
                     })
                     .autocomplete
                     ({
                         minLength: 0,
                         /*source:
                                function( request, response )
                                {
                                    $.post(
                                        procesSCRIPT_file,
                                        {parsePOSTfile : parsePOSTfile_getTags},
                                         function(data)
                                         {
                                             // delegate back to autocomplete, but extract the last term
                                               response( $.ui.autocomplete.filter(
                                                   data, extractLast( request.term ) ) );
                                         },
                                        "json"
                                    );
extraBts
                                },*/
                         source:
                                function( request, response )
                                {
                                    // wwant to do it like this
                                   /* if(typeof bttConf[elmName] !='undefinder' &&  typeof bttConf[elmName].EDs[EDname] !='undefinder' ){

                                        var jsonPath = bttConf[elmName].EDs[EDname].getJson;
                                        $.getJSON(jsonPath, function(data) {
                                             response( $.ui.autocomplete.filter(
                                             data, extractLast( request.term ) ) );
                                        });
                                    }*/
                                    $.post(
                                        procesSCRIPT_file,
                                        {parsePOSTfile : parsePOSTfile_getTags},
                                         function(data)
                                         {
                                             // delegate back to autocomplete, but extract the last term
                                               response( $.ui.autocomplete.filter(
                                                   data, extractLast( request.term ) ) );
                                         },
                                        "json"
                                    );
                                },

                         focus:
                                function() { return false; /* prevent value inserted on focus */},

                         select:
                                function( event, ui )
                                {
                                    var terms = split( this.value );
                                    // remove the current input
                                    terms.pop();
                                    // add the selected item
                                    terms.push( ui.item.value );
                                    // add placeholder to get the comma-and-space at the end
                                    terms.push( "" );
                                    this.value = terms.join( ", " );
                                    return false;
                                }
                     });
              }
        };

        //alert(typeof EDtags[EDtype]);
        if( eval('typeof ' +EDreplace[EDtype]) == 'function' )
        {

           jqEDtag.replaceWith( EDreplace[EDtype]() );
            if(eval('typeof ' +EDcallback[EDtype]) == 'function')
            EDcallback[EDtype]();

        }  else {
            alert('EDITmode nu s-a gasit functie pentru EDtype '+EDtype+'\n EDname = '+EDname);
        }


    }


    function test(){
        console.log("Am apelat functia test si LG = "+LG);
    }

    //========================================[ PUBLIC FUNCTIONS ]====================================
    return {

        bttConf : bttConf,

        add_bttConf : function(bttName,bttName_conf ){

            /**
            * ATENTIE  trebuie sa ridic un semn de intrebare? oare aceasta variabila nu ar fii mai bine sa fie publica
             * */
            /**
             * ma refer la o variabila locala a obiectului (privata) iEdit
             * aceasta variabila nu poate fii accesata din afara lui
             * */

           /*// tests
            console.log('bttName = ' + bttName +' elemente = '+ bttName_conf.length);
            for(var optType in bttName_conf) {
                console.log('optType = '+ optType  + ' cu elemente = '+ bttName_conf[optType].length);
            }
            console.log(' ');*/
            bttConf[bttName] = bttName_conf;
        },
        add_bttsConf: function(btts){

            /**
             * Deci ma pot referii la cine?*/
           // alert(typeof this.add_bttConf);   // = function
            for(var bttName in btts)  this.add_bttConf(bttName, btts[bttName]);
        },

        init :{
            // we can refer to init as this. because is a named object
            set_iEdit :    function(){

                LG =  $("input[name=lang]").val();  //Need to get the current LG;

                // *** ATENTIE -nu mai sunt in dubii :D
                /**
                 * daca un anumit modul are de facut setari pentru editMode
                 * poate sa appenduiasca functile care fac setariile la fmw.set_iEdit_localSetting
                 *
                 * */
                if(typeof ivyMods.set_iEdit != 'undefined'){


                    var ivyModsFns = ivyMods.set_iEdit;
                    for(var fnKey in ivyModsFns)
                    {
                        if(typeof ivyModsFns[fnKey] == 'function')
                        {

                            ivyModsFns[fnKey].call();
                        }
                    }
                }

                //===============[set tools]=============================
                this.tools();
                this.tools_addEnt();

                $("*[class^=ENT] , *[class^=SING]").live({
                    mouseover   : function() { $(this).not('form').find('.TOOLSem:first').show();},
                    mouseout    : function() { $(this).not('form').find('.TOOLSem').hide();  }
                });
            },
            tools :        function(){

                 // vizibilitate pentru variabilele locale (ex: LG ) si functiile locale (ex: replace)
                function get_elmDet(elm)    {

                   /**
                    * UTILIZARE GENERALA EDITmode.js
                    *
                    * < * class='allENTS [otherClasses] [entSName]' id = '[entSName]_[LG]' >
                    *     - add new ent
                    *
                    *     <class='ENT [otherClasses] [entName]' id = '[entName]_[id]_[LG]' >
                    *
                    *  - delete
                    *  - edit
                    *  - save
                    *  - exit edit (cancel)
                    *
                    *  < * class='SING [otherClasses] [singName]' id = '[singName]_[id]_[LG]' >
                    *      - edit
                    *      - save
                    *      - exit edit (cancel)
                    */

                      var desc    =  elm.attr('id').split('_');
                      var classes =  elm.attr('class');
                      var TYPEarr =  classes.split(' ');
                      var BTT     =  {atrValue : 'e', style : ''};
                      var Name    =  desc[0];

                       if(typeof bttConf[Name] !='undefined' && typeof bttConf[Name].edit !='undefined')
                       $.extend(BTT,  bttConf[Name].edit);

                      return {
                          BTT     : BTT,
                          TYPE    : TYPEarr[0],     //ENT || SING
                          cls     : classes.replace(this.TYPE,''),
                          id      : desc[1],
                          Name    : Name      //ENTname || SINGname,
                       }

                }
                function get_htmlToolsem(elDetails){
                    var elD = elDetails;
                    return  "<div class='TOOLSem' style='display: none;'>" +
                               "<div class='TOOLSbtn'>" +
                               "   <span>" +
                               "       <input type='button' class='editModeBTT' "+elD.BTT.style+" name='EDIT' value='"+elD.BTT.atrValue+"'" +
                               "                            onclick=\"iEdit.evCallback.editContent('"+elD.id+"','"+elD.Name+"','"+elD.TYPE+"','"+elD.cls+"'); return false;\">" +
                               "       <i>Edit Content</i>" +
                               "   </span>" +
                               "</div>" +
                            "</div>"
                }

                $("*[class^=SING], *[class^=ENT]").map(function()
                {
                    var elD   = get_elmDet($(this)); //from element Details
                    var tools = get_htmlToolsem(elD);
                    $(this).wrapInner("<div class='ELMcontent' />");          // pentru a putea recupera continutul
                    $(this).prepend(tools);
                });

            },
            tools_addEnt : function(){
                /**
               * UTILIZARE GENERALA EDITmode.js
               *
               * < * class='allENTS [otherClasses] [entSName]' id = '[entSName]_[LG]' >
               *     - add new ent
               *
               *     <class='ENT [otherClasses] [entName]' id = '[entName]_[id]_[LG]' >
               *
               *  - delete
               *  - edit
               *  - save
               *  - exit edit (cancel)
               *
               *  < * class='SING [otherClasses] [singName]' id = '[singName]_[id]_[LG]' >
               *      - edit
               *      - save
               *      - exit edit (cancel)
               */
                function Get_firstEntProps(firstENT){

                    var classes         = firstENT.attr('class');
                    var TYPEarr         = classes.split(' ');
                    var cls             = classes.replace('ENT','');   //ENT || SING - restul claselor fara denumirea de ENT sau SING
                    var nameENT         = TYPEarr[TYPEarr.length - 1]; //ENTname || SINGname - numele ENT-ului se afla la pus ca ultima clasa a Elementului
                    var BTT             = {status : 1, style : '', atrValue: '+', methName: '' };

                    if (typeof bttConf[nameENT] !='undefined') {
                        $.extend(BTT, bttConf[nameENT].addBt);
                    }

                    return {
                        FORM_content    : firstENT.find('.ELMcontent').html(),
                        FORM_class      : cls+" addForm",
                        nameENT         : nameENT,
                        FORM_id         : "new_"+nameENT+'_'+LG,
                        html_extraBTTS  : function(){

                            if(typeof bttConf[nameENT] == 'undefined'
                               || typeof bttConf[nameENT].extraBts =='undefined'
                            ) {
                                console.log('NU Avem extra butoane');
                                return '';
                            } else {

                                console.log('Avem extra butoane');
                                var extraBts =  bttConf[nameENT].extraBts;
                                var htmlButtons = '';
                                for(var key in extraBts){

                                    var extraBtt = {
                                        callBack : '',
                                        atrValue : key,
                                        atrName: key,
                                        atrType:  'button'
                                    };
                                    $.extend(extraBtt, extraBts[key]);
                                    htmlButtons += "" +
                                        "<span>" +
                                            "<input type='"+extraBtt.atrType+"' " +
                                                  " class='editModeBTT' " +
                                                  " name='"+extraBtt.atrName+"' " +
                                                  " value='"+extraBtt.atrValue+"' " +
                                                  " onclick=\""+extraBtt.callBack+"\">" +
                                        "</span>" ;
                                }

                                return htmlButtons;
                            }

                        }(),
                        html_ctrlAction : function(){
                               if (typeof bttConf[nameENT] !='undefined'
                                   && typeof bttConf[nameENT].modName != 'undefined') {
                                   return  "<input type='hidden' name='modName' value='"+bttConf[nameENT].modName+"' />" +
                                           "<input type='hidden' name='methName' value='"+BTT.methName+"' />";

                               }
                               return '';
                        }(),
                        BTT             : BTT
                    };

                }
                function Get_htmlToolsem(elD){

                    var buttons  =  elD.html_extraBTTS ;
                        buttons +=  !elD.BTT.status ? '' :
                                    "<span>" +
                                           "<input type='button'  class='editModeBTT' "+elD.BTT.style+"  name='addNewENT' value='"+elD.BTT.atrValue+"' " +
                                                             " onclick=\"iEdit.evCallback.addEnt('"+elD.nameENT+"')\">" +
                                           "<i>Add new</i>" +
                                    "</span>";

                    var toolsEm =  buttons == '' ? '' :
                                    "<div class='TOOLSem'>" +
                                       "<div class='TOOLSbtn'>" +
                                          buttons +

                                        "</div>" +
                                    "</div>";
                    return toolsEm;
                }
                function Get_htmlAddForm(elD){

                    var form = "<form action='' method='post' class='"+elD.FORM_class+"'   id='"+elD.FORM_id+"' style='display: none;'>" +
                                     elD.html_ctrlAction+
                                    "<div class='TOOLSem'>" +
                                          "<div class='TOOLSbtn'>                                                                        " +
                                          "     <span>" +
                                                  "<input type='submit' class='editModeBTT'   name='save_add"+elD.nameENT+"' value='s' />" +
                                                  "<i>save</i>" +
                                              "</span>                         " +
                                          "     <span>" +
                                                  "<input type='button' class='editModeBTT'   name='EXIT' value='x' onclick=\"iEdit.evCallback.remove_addNew('"+elD.nameENT+"'); return false;\">" +
                                                  "<i>Exit</i>" +
                                              "</span>       " +



                                    "     </div>          " +
                                    "</div> " +
                                    "<div class='ELMcontent'>" +
                                          elD.FORM_content +
                                    "</div>"+
                                "</form>";
                    return form;
                }
                function Prepare_addForm(elD, addForm){
                       // *** ATENTIE trebuie golita si imaginea
                       /**
                       * Se golesc toate fieldurile editabile
                       * apoi se pune o alta clasa fieldurilor editabile - cu CKeditor pentru a
                       * nu mai trebui sa distrug instantele de CKeditor pentru add-uri
                       * */
                        // probabil ca cele 2 ar trebuii inlantuite

                      addForm
                      .find('*[class^=ED]')
                      .empty();
                       //  .find('*[class^=ED]').not('*[class=EDpic]').empty();


                      addForm
                      .find('*[class^=EDeditor]')
                      .map(function(){
                           var classEditor = $(this).attr('class');
                           var classEditor = classEditor.replace('EDeditor', 'EDaddEditor');
                           $(this).attr('class', classEditor);

                       });

                      /**
                       * Pentru formularul de adaugare de pic se creeaza un alt tip de ED
                       * EDaddPic
                       * */
                      addForm
                      .find('*[class^=EDpic]')
                      .map(function(){
                           var classEditor = $(this).attr('class');
                           var classEditor = classEditor.replace('EDpic', 'EDaddPic');

                           var imgHeight = $(this).height();
                           var imgWidth = $(this).width();

                          /**
                           * daca imaginea nu are width sau height atunci se presupune ca parintele care
                           * contine imaginea va avea unul din cele doua
                            */
                           if(!imgHeight) imgHeight =  $(this).parent().height();
                           if(!imgWidth) imgWidth = $(this).parent().width();

                           $(this)
                               .attr('class', classEditor)
                               .attr('src',"http://placehold.it/"+imgWidth+"x"+imgHeight+'&text=add%20Image');
                         // $(this).css('background','gray');
                      });

                     //  alert( $('form[class$=addForm]').find('a').length);
                      // nu imi e foarte clar de ce nu se foloseste selecta addForm
                      $('form[class$=addForm][id='+elD.FORM_id+']')
                      .find('a')
                      .on('click', function(){ return false;});
                      // KK
                      $('form[class$=addForm][id='+elD.FORM_id+']')
                      .find('a[rel=alterPics_group] >img')
                      .unwrap();

                }

                // pentru ca in interiorul lui map nu ma mai pot referi la init
                // cu this
                $('*[class^=allENTS]').map(function()
                {
                      var allENTS   = $(this);
                      var firstENT  = $(this).find('*[class^=ENT]:first');

                      if(firstENT.length > 0) // daca sunt elemente in cadrul allENTS
                      {
                          var elD = Get_firstEntProps(firstENT); //from element Details

                          allENTS.prepend(Get_htmlToolsem(elD));
                          //alert(BTTstatus);
                           if(elD.BTT.status)
                           {
                                firstENT.before(Get_htmlAddForm(elD));
                                var addForm =  $("#"+elD.FORM_id);
                                //console.log('addForm id = '+ addForm.attr('id'));
                                Prepare_addForm(elD, addForm);
                                //transform($("#"+elD.FORM_id),'form[class$=addForm]', elD.nameENT);
                                transform(addForm,'form[class$=addForm]', elD.nameENT);

                               /**
                                * Daca nu exista ENTS visible inseamna ca nu a fost adaugat nici un ENT
                                * => trebuie sa apara TOOLSem din start , fara mouse over
                                * */
                               var countENTS = allENTS
                                                .find('*[class^=ENT]:visible')
                                                .length;
                               if(countENTS == 0) {
                                   addForm
                                       .prev('.TOOLSem')
                                       .css('visibility','visible');
                               }
                          }
                      }
                });

            }
        },
        evCallback : {

            // ============[ EDpic - events ]=================================
            changePic: function(param){

                // pentru mai multa docum vezi functia de mai jos "loadPic"
                /**
                 * param = {
                 *     callBackFn,
                 *     jqObj_img,
                  *    newUrl
                 * }
                 * */
                /**
                 * WORKING ON
                 *
                 return   "<img class='"+INPUTclass+"' src='"+imgSrc+"' id='editImg_"+INPUTname+"'>" +
                          "<input type='hidden' name='"+INPUTname+"' value='' />" +
                          "<input type='button' name='replaceImg' value='loadImg' " +
                                               " style='left: 0;position: absolute;'" +
                                               " onclick='iEdit.evCallback.loadPic(\"editImg_"+INPUTname+"\")'>";

                 [ OR ]

                 return   "<img class='"+INPUTclass+"' src='"+imgSrc+"' id='editAddImg_"+INPUTname+"'>" +
                           "<input type='hidden' name='"+INPUTname+"' value='' />" +
                           "<input type='button' name='replaceImg' value='loadImg' " +
                                                 " style='left: 0;position: absolute;'" +
                                                 " onclick='iEdit.evCallback.loadPic(\"editAddImg_"+INPUTname+"\")'>" ;
                 * */
                param.jqObj_img.attr('src',param.newUrl);
                param.jqObj_img.next('input[type=hidden]').val(param.newUrl);


              },

            loadPic:   function(id){

                //alert($('img#'+id).attr('src'));
                //var jqImg = $('img#'+id);
                fmw.KCFinder_popUp({
                    jqObj_img: $('img#'+id),                  //img-ul al carui url va fi schimbat cu noua imagine aleasa
                    callBackFn: iEdit.evCallback.changePic   //functie apelata dupa ce a fost selectata o imagine
                })

            },

            generalRemove_addNew: function(){

                      //  this.exitEditContent_byType('ENT');

                        $('.TOOLSem > input[name=addNewENT]').parent().show();
                        $("form[id^=new_]").hide();
            },

            exitEditContent_byType: function(TYPE){

                $('textarea[id^=editor_]').map(function(){

                    var  id = $(this).attr('id');
                    if (CKEDITOR.instances[id]) CKEDITOR.instances[id].destroy(true);
                });

                var editForm =$('form[id^=EDITform]');
                if(editForm.length > 0){
                    editForm.prev().show();
                    editForm.remove();
                    // $('.'+TYPE).not('*[id*=_new_]').show();
                }


            },

            //*** posibly deprecated since generalRemove_addNew = exists
            remove_addNew: function(nameENT){
                       // this.exitEditContent_byType('ENT');
                        var addFORM_id    = "new_"+nameENT+'_'+LG;

                        $('.TOOLSem > input[name=addNewENT]').parent().show();
                        $("#"+addFORM_id).hide();
                return false;
            },

            // *** de investigat - posibil deprecated
            exitEditContent_byName : function(Name,id){

                var jqForm = $('form[id=EDITform_'+id+'][class$='+Name+']');
                jqForm.find('textarea[id^=editor_]').map(function(){

                    var  idTxa = $(this).attr('id');
                     if (CKEDITOR.instances[idTxa]) CKEDITOR.instances[idTxa].destroy(true);
                });

               jqForm.remove();
               $('*[id^='+Name+'_'+id+'_]').not('*[id*=_new_]').show();

                // alert('ExitEditContent_byName inchide '+'form[id=EDITform_'+id+'][class$='+Name+']');
                //$('*[class$='+Name+']').not('*[id*=_new_]').show();


            },

            general_ExitEditContent: function(){},

             // set by init.tools_addEnt
            addEnt: function(nameENT){

                var addFORM_id    = "new_"+nameENT+'_'+LG;

                this.exitEditContent_byType('ENT');
                this.exitEditContent_byType('SING');
                //this.remove_addNew(nameENT);
                $('.TOOLSem > input[name=addNewENT]').parent().hide();
                $("#"+addFORM_id).show();

              //  $("#"+addFORM_id).find('.PRDpic > img').replaceWith("<img src='./MODELS/products/RES/small_img/site_produs_slice_pisici.jpg' alt='placeholder_img'>");


            },

            // set by init.tools
            editContent : function(id,Name,TYPE,cls){

                /*
                   this.generalRemove_addNew();
                   if(TYPE == 'SING')
                        this.exitEditContent_byType(TYPE);
                */
                this.generalRemove_addNew();
                this.exitEditContent_byType('ENT');
                this.exitEditContent_byType('SING');

                function Get_elmDet(){

                   var BLOCK   = $('*[id^='+Name+'_'+id+'_]');

                  /* atentie atrName - depind lucruri de el deci nu ar trebui schimbat*/
                   var BTT = getBtt(Name, {
                       deleteBt : {status : 1, atrName:"delete_"+Name, atrValue: 'd'},
                       saveBt   : {status : 1, atrName:"save_"+Name, atrValue: 's'},
                       extraButtons:{},
                       modName: ''
                   });

                   var DELETE_tag     = function(){

                        var tag = '';
                        if (TYPE == 'ENT' && BTT.deleteBt.status)
                        {
                           tag =
                           "<span>" +
                           "     <input type='submit'  class='editModeBTT' " +
                                       " name='"+BTT.deleteBt.atrName+"' value='"+BTT.deleteBt.atrValue+"' />" +
                           "     <i>Delete</i>" +
                            "</span>";
                        }
                        return tag;
                   }();
                   var SAVE_tag       = function(){

                      var tag = '';
                      if ( BTT.saveBt.status )
                          tag =
                            "<span>" +
                            "   <input type='submit'  class='editModeBTT editM-save' " +
                                        " name='"+BTT.saveBt.atrName+"' value='"+BTT.saveBt.atrValue+"' />" +
                            "   <i>Save</i>" +
                            "</span>";

                       return tag;
                   }();
                   var EXTRA_tags     = function(){

                      if(typeof bttConf[Name] == 'undefined'
                         || typeof bttConf[Name].extraButtons =='undefined'
                      ) {
                          console.log('NU Avem extra butoane');
                          return '';
                      } else {

                          console.log('Avem extra butoane');
                          var extraBts =  bttConf[Name].extraButtons;
                          var htmlButtons = '';
                          for(var key in extraBts){

                              var extraBtt = {
                                  callBack : '',
                                  attrValue : key,
                                  attrName: key,
                                  attrType:  'button'
                              };
                              $.extend(extraBtt, extraBts[key]);
                              htmlButtons += "" +
                                  "<span>" +
                                      "<input type='"+extraBtt.attrType+"' " +
                                            " class='editModeBTT' " +
                                            " name='"+extraBtt.attrName+"' " +
                                            " value='"+extraBtt.attrValue+"' " +
                                            " onclick=\""+extraBtt.callBack+"\">" +
                                  "</span>" ;
                          }
                          return htmlButtons;

                      }


                   }();
                   var EXTRA_htmlTags = function(){
                       /**
                        ATENTIE!!! POATE AR TREBUI SA GASESC O METODA MAI PUTIN COSTISITOARE
                      *
                      * DESCRIERE -
                      * daca inaintea unui element avem definit un elemnt.addTOOLSbtn
                      * - acesta va contine butoanele EXTRA pentru TOOLSbtn
                      * se adauc butoanele la forma standard pentru TOOLSbtn
                      *
                        * Utilitate:
                        *  - este util sa las butoanele in cadrul templateului pentru
                        *  cazuri in care butoanele sunt conditionate de php prin template
                      *
                      * */
                      var tag = '';
                      var  EXTRA_btns = BLOCK.prevAll('.addTOOLSbtn');
                      if(!EXTRA_btns.length)                          //daca nu gaseste butoane extra sa zicem la inceputul lui allEnts
                           EXTRA_btns = BLOCK.prev('.addTOOLSbtn');   // incearca sa caute butoane inaintea entului curent

                      if(EXTRA_btns.length ){

                             EXTRA_btns.find('input').addClass('editModeBTT').wrap("<span>");
                             tag = EXTRA_btns.html();
                      }

                      return tag;


                   }();

                    return {
                        BLOCK      : BLOCK,
                        BTT        : BTT,
                        DELETE_tag : DELETE_tag,
                        SAVE_tag   : SAVE_tag,
                        EXTRA_tags : EXTRA_tags + EXTRA_htmlTags,
                        elmContent : BLOCK.find('.ELMcontent').html()
                    };

                }
                function Get_htmlForm(elD){
                    var form = "" +
                        "<form action='' method='post' class='"+cls+"' id='EDITform_"+id+"' >" +
                             "<input type='hidden' name='BLOCK_id' value='"+id+"' />" +
                             "<div class='TOOLSem'>" +
                                  "<div class='TOOLSbtn'>" +
                                         //    EXTRAS_TAG +
                                         elD.EXTRA_tags +
                                         elD.SAVE_tag +
                                         elD.DELETE_tag+
                                        "<span>" +
                                        "    <input type='button'  class='editModeBTT editM-exit' " +
                                                    "name='EXIT' value='x'" +
                                                     " onclick=\"iEdit.evCallback.exitEditContent_byName('"+Name+"','"+id+"')\">" +
                                        "    <i>Exit</i>" +
                                        "</span>" +
                                  "</div>" +
                             "</div>" +
                             "<div class='ELMcontent'>" +
                                 elD.elmContent+
                             "</div>"+
                        "</form>";
                    return form;
                }
                //==============================================================

                 var elD  = Get_elmDet();
                 var form = Get_htmlForm(elD);
                 //console.log(form);

                 $.when(
                     elD.BLOCK.after(form)
                 ).then(
                     function(){
                          elD.BLOCK.next().show();
                          actionBtns_binds(elD.BLOCK,Name,id);
                     }
                 );
                //==============================================================

                 transform(elD.BLOCK,'form[class$='+Name+'][id=EDITform_'+id+']', Name);
                 // disable all links 'a' in this form
                 $('form[class$='+Name+'][id=EDITform_'+id+']')
                     .find('a')
                     .on('click', function(){return false;});

                  elD.BLOCK.hide();

            },

            // set by actionBtns_binds
            async_delete : function(Name ,id){

                    var BTTdelete_async = bttConf[Name].deleteBt.async;

                    // var postData = collect_postData(Name, id, "input[type=hidden]");
                    var postData = $('form[class$='+Name+'][id=EDITform_'+id+'] ').collectData("input[type=hidden]");

                    BTTdelete_async.fnpost(postData ,[Name, id] );

                    //====================================================================
                    this.exitEditContent_byName(Name,id);

                    $("*[id^="+Name+"_"+id+"]").remove();

            },

            async_save   : function(Name ,id){

                  //var postData = collect_postData(Name, id, "input[type=text] ,input[type=hidden] , textarea, select");
                  var postData = $('form[class$='+Name+'][id=EDITform_'+id+'] ').collectData();

                  var BTTsave_async = bttConf[Name].saveBt.async;

                  if( BTTsave_async.constructor == Object)
                  {
                      BTTsave_async.fnpost(postData, [Name, id] );

                    //bttConf[Name].saveBt.async.neApelata(" APEL");
                      //====================================================================
                      this.exitEditContent_byName(Name,id);
                      async_save_reconstruct(Name, id, postData);

                  } else {
                      console.log("Butonul de async nu a fost configurat corect ");
                  }
             //   return false;

            }
        },
        specif :{
            inEdit : function(elmType,elmName, elmId){

                var elmSel = $(" *[class^="+elmType+"][id^="+elmName+"_"+elmId+"_]");
                   elmSel.wrapInner("<div class='ELMcontent' />");


                this.tools(elmSel,elmType,elmName, elmId);

            },
            tools : function(elmSel,elmType,elmName, elmId){

                   console.log("Inside specif.tools scope");

                   // desc    = elmSel.attr('id').split('_');
                   // classes = elmSel.attr('class');

                    var cls  = elmSel.attr('class').replace(elmType,'');

                    var TYPE = elmType;
                    var id   = elmId;
                    var Name = elmName;        //ENTname || SINGname

                  //================================================================================================================


                    var BTT =  {name : 'e', style : ''};

                    if(typeof bttConf[Name].edit !='undefined')
                       $.extend(BTT,  bttConf[Name].edit);

                    //DEP
                   /* var BTTvalue = typeof BTTedit.name[Name]!='undefined' ? BTTedit.name[Name] : 'e';
                      var BTTstyle = typeof BTTedit.style[Name]!='undefined' ? BTTedit.style[Name] : "";
                   */


                  //================================================================================================================
                    elmSel.prepend
                    (
                        "<div class='TOOLSem' style='display: none;'>" +
                            "<div class='TOOLSbtn'>" +
                            "   <span>" +
                            "       <input type='button' class='editModeBTT' "+BTT.style+" name='EDIT' value='"+BTT.name+"'" +
                            "                            onclick=\"iEdit.evCallback.editContent('"+id+"','"+Name+"','"+TYPE+"','"+cls+"'); return false;\">" +
                            "       <i>Edit Content</i>" +
                            "   </span>" +
                            "</div>" +
                        "</div>"
                    );
            }
        }

    }
}();

/*=======================================[ call specific EDITMODE]====================================================*/

function spec_EditMode(elmType,elmName, elmId){

    var elmSel = $(" *[class^="+elmType+"][id^="+elmName+"_"+elmId+"_]");
    elmSel.wrapInner("<div class='ELMcontent' />");


    spec_setTOOLS(elmSel,elmType,elmName, elmId);                                                                          // BTT de edit - EditContent(id,Name,TYPE)

}
function spec_setTOOLS(elmSel,elmType,elmName, elmId){

   // desc    = elmSel.attr('id').split('_');
   // classes = elmSel.attr('class');

    var cls  = elmSel.attr('class').replace(elmType,'');

    var TYPE = elmType;
    var id   = elmId;
    var Name = elmName;        //ENTname || SINGname

  //================================================================================================================


    var BTT =  {name : 'e', style : ''};

    if(typeof bttConf[Name].edit !='undefined')
       $.extend(BTT,  bttConf[Name].edit);

    //DEP
   /* var BTTvalue = typeof BTTedit.name[Name]!='undefined' ? BTTedit.name[Name] : 'e';
      var BTTstyle = typeof BTTedit.style[Name]!='undefined' ? BTTedit.style[Name] : "";
   */


  //================================================================================================================
    elmSel.prepend
    (
        "<div class='TOOLSem' style='display: none;'>" +
            "<div class='TOOLSbtn'>" +
            "   <span>" +
            "       <input type='button' class='editModeBTT' "+BTT.style+" name='EDIT' value='"+BTT.name+"'" +
            "                            onclick=\"iEdit.evCallback.editContent('"+id+"','"+Name+"','"+TYPE+"','"+cls+"'); return false;\">" +
            "       <i>Edit Content</i>" +
            "   </span>" +
            "</div>" +
        "</div>"
    );

}

//======================================================================================================================

$(document).ready(function()      {

    /**
     * -- SETTINGS--
     *
     *  EDsel => EDname = new SELoptions(Array_ro,Array_en);
     *  extras => EDname_extra = functionName(id);
     */
   iEdit.init.set_iEdit();
   //iEdit.init.setLG('ru');
   //iEdit.init.testFunc();

});


