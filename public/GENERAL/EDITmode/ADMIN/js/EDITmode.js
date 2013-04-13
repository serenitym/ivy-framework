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


var LG = 'ro';
var procesSCRIPT_file     = 'procesSCRIPT.php';                   // intermediaza requesturile scripurilor .js
var parsePOSTfile_getTags = 'MODELS/blog/ADMIN/getTags.php';

/**
 *  UTILIZARE:
   $.post(procesSCRIPT_file,  { parsePOSTfile : parsePOSTfile ,$_POST_array  } ) */



// DOCUMENTEAZA TOT editMODEUL URGENT

//trebuie cumva setat daca vreau sau nu indicatii pentru butoane


var iEdit = function(){

    var LG = 'ro';
    /**
     * Config ex:
     *
     *  elemName_ex: {
     *            addBt: {
     *                name: 'un nume',
     *                style : 'oric',
     *                class : '',
     *                status: '',
     *                async : new asyncConf({
     *                                moduleName: 'modulename',
     *                                methName: 'methName',
     *                                parsePOSTfile : 'filePath.php' ,
     *                                callBack_fn : (typeof fnName != 'undefined'  ? fnName : ''),
     *                                restoreCore : false
     *                            })
     *                },
     *            deleteBt:{},
     *            edit: {},
     *            saveBt:{}
     *       },
     *
     * Utilizarea configului
     *
     *  bttConf[elemName].add['name']
     *
     *  bttConf[elemName].add['async']
     *      .  fnload( jQobj, sendData, callBack_fn)
     *      .  fnpost( sendData, callBack_fn)
     *      .  allProperties
     */
    var bttConf =    {

            SGrecord: {
                edit: {  name : 'edit Record',   style: " style='width:60px;  margin-left: -40px;'  "  }
            },

            comment : {
                addBt : {status :false},
                saveBt: {status : false}
            },

            record : {
                addBt :{ name:'add Record', style :" style='width:80px;  margin-left: -60px; background-color: #D9E9F1;'  "},
                saveBt:{ satus : false}
            },

            recordHome :{
                addBt: {status: false},
                saveBt:{status:false}
            }

            /*
            // setate din picManager.js
            'pic-full' : {
                addBt  : { status : false },
                saveBt : { async : new asyncConf({ parsePOSTfile : 'PLUGINS/picManager/ADMIN/savePic.php' , restoreCore : false },
                                                 { callBack_fn : (typeof carousel_savePic == 'function'  ? carousel_savePic : 'altceva')}
                                                )
                        },
                deleteBt :{ async : new asyncConf({ parsePOSTfile : 'PLUGINS/picManager/ADMIN/deletePic.php', restoreCore : false},
                                                  { callBack_fn : (typeof carousel_deletePic == 'function' ? carousel_deletePic : 'altceva')}
                                                 )
                        }
            }*/


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

    function split( val )        {    return val.split( /,\s*/ ); }

    function extractLast( term ) {    return split( term ).pop(); }



    function async_Binds(BLOCK,Name,id) {

           var TOOLSbtn = BLOCK.next('form').find('.TOOLSbtn');


           if(TOOLSbtn.length > 0  && typeof bttConf[Name] != 'undefined')
           {

               var BTT = bttConf[Name];

               //if( typeof BTTdelete.asincr[Name]!='undefined')
               if(typeof BTT.deleteBt !='undefined')
                   if(typeof BTT.deleteBt.async !='undefined')
                   {

                       TOOLSbtn
                           .find('input[class=editModeBTT][name^=delete_]')
                                .attr('onclick',"iEdit.evCallback.async_delete('"+Name+"','"+id+"'); return false;");


                       //alert(deleteBtt.attr('name'));
                   }
               if( typeof BTT.saveBt != 'undefined')
                   if( typeof BTT.saveBt.async != 'undefined')
                   {
                       TOOLSbtn
                            .find('input[class=editModeBTT][name^=save_]')
                                .attr('onclick',"iEdit.evCallback.async_save('"+Name+"','"+id+"'); return false;");

                      /* console.log(' pentru '+Name
                                   +' avem callback-ul ' +  BTT.saveBt.async.callBack_fn
                                   + ' si file-ul de procesare ' + BTT.saveBt.async.parsePOSTfile);*/
                   }
           }
            //else{  alert('selectul toolsbtn nu functioneza');  }
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


    function transform(BLOCK,formSelector){

             BLOCK.find('*[class^=ED]').map(function(){
                 // selELEM =  $(this).attr('class')+' ';

                  var desc = ($(this).attr('class')+' ').split(' ');
                  var EDtype  = desc[0];
                  var EDname  = desc[desc.length-2]; //  (-2)  1- este ca sa imi ajunga la 0 si inca 1 pentru ca pune un elem in plus , nu stiu de ce

                  if(EDtype=='EDeditor' || EDtype=='EDpic')
                      var EDvalue = $.trim($(this).html());
                  else
                      var EDvalue = $.trim($(this).text());



                //  alert('EDname '+EDtype+'  EDname '+EDname+' value '+EDvalue);
                  replace(EDtype, EDname, EDvalue,formSelector);
          });
    }

    function replace(EDtype, EDname, EDvalue,formSelector){

          //alert(EDtype+' '+EDname+" "+EDvalue+" "+formSelector);

        var EDtag        = formSelector + ' *[class^='+EDtype+'][class$='+EDname+']';
        var INPUTname    = EDname+"_"+LG;
        var INPUTclass   = 'EDITOR '+EDname;
        var EDtag_height = $(EDtag).height()+'px';
        var EDtag_width  = $(EDtag).width();


        var EDreplace ={
            EDtxtp   : function (){
                        return "<input type='text' name='"+INPUTname+"'  class='"+INPUTclass+"' value='"+EDvalue+"' placeholder='"+EDname+"' />";
                      },

            EDtxt    : function (){
                       return "<input type='text' name='"+INPUTname+"'  class='"+INPUTclass+"' value='"+EDvalue+"' />";},
            EDdate   : function (){
                       return "<input type='text' name='"+INPUTname+"'  class='"+INPUTclass+"' value='"+EDvalue+"' />";},
            EDtags   : function (){
                       return "<input type='text' name='"+INPUTname+"'  class='"+INPUTclass+"' value='"+EDvalue+"' />";},

            EDtxa    : function (){
                     return "<textarea   name='"+INPUTname+"'  class='"+INPUTclass+"' >"+EDvalue+"</textarea>"; },
            EDeditor : function (){
                     return "<textarea   name='"+INPUTname+"'  class='"+INPUTclass+"'  id='editor_"+EDname+'_'+LG+"' >"+EDvalue+"</textarea>";},
            EDpic    : function (){
                           var  IDpr = $('input[name=IDpr]').val();
                             // if(typeof IDpr!='undefined') INPUTname='';
                             //(form,url_action,id_element,html_show_loading,html_error_http)
                             $('form[id^=EDITform]').attr('enctype','multipart/form-data');
                             $('form[id^=EDITform]').attr('encoding','multipart/form-data');
                            return "<div class='"+INPUTclass+"' id='frontpic'>" +
                                                     EDvalue+
                                                    "<div  id='formUPL' >"+
                                                         "<input type='file' id='fileUPL' name='filename_"+INPUTname+"' class='fileinput'  />" +
                                                         "<input type='hidden' name='id' value='"+IDpr+"'>" +
                                                        /* "<input type='submit' name='UPLDimg' value='UP'>"+*/
                                                     "</div>" +
                                                 "</div>"
                                                 ;

                 },
            EDsel    : function (){
                            if(eval('typeof '+EDname+'!= "undefined" ')) var options = eval(EDname+'.getHTMLoptions("'+EDvalue+'")');
                            else alert('nu l-a recunoscut ca obiect');
                            return "<select name='"+INPUTname+"' class='"+INPUTclass+"'>"+options+"</select>";
                            }
        };


        //alert(typeof EDtags[EDtype]);
        if( eval('typeof ' +EDreplace[EDtype]) == 'function' )
        {

            $(EDtag).replaceWith( EDreplace[EDtype]() );

             var EDcallback = {
                  EDeditor : function(){
                        var toolbar_conf = (EDtag_width < 500 ? 'EXTRAsmallTOOL' : 'smallTOOL' );
                        CKEDITOR.replace( 'editor_'+EDname+'_'+LG,
                                              {
                                                  toolbar : toolbar_conf,
                                                  height : EDtag_height
                                                ,width : EDtag_width
                                              });
                                      //$("textarea[id=editor_"+EDname+'_'+LG+"]").ckeditor();

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
                              source:
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

            if(eval('typeof ' +EDcallback[EDtype]) == 'function')
            EDcallback[EDtype]();

        }  else {
            alert('EDITmode nu s-a gasit functie pentru EDtype '+EDtype+'\n EDname = '+EDname);
        }


    }

    function remove_addNew(){
            var addFORM_id    = "new_"+nameENT+'_'+LG;

            $('.TOOLSem > input[name=addNewENT]').parent().show();
            $("#"+addFORM_id).hide();
    }


    function test(){
        console.log("Am apelat functia test si LG = "+LG);
    }

    //========================================[ PUBLIC FUNCTIONS ]====================================
    return {

        add_bttConf : function(bttName,bttName_conf ){

            bttConf[bttName] = bttName_conf;
        },

        init :{
            // we can refer to init as this. because is a named object
            set_iEdit :    function(){
                LG =  $("input[name=lang]").val();  //Need to get the current LG;

                this.tools();
                this.tools_addEnt();

                $("div[class^=ENT] , div[class^=SING]").live({
                    mouseover   : function() { $(this).find('.TOOLSem').show();},
                    mouseout    : function() { $(this).find('.TOOLSem').hide(); }
                });
            },
            tools :        function(){

                 // vizibilitate pentru variabilele locale (ex: LG ) si functiile locale (ex: replace)

                var get_elmDet = function(elm)    {

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
                      var classes = elm.attr('class');
                      var TYPEarr = classes.split(' ');
                      var BTT     =  {name : 'e', style : ''};
                      var Name    = desc[0];

                       if(typeof bttConf[Name] !='undefined' && typeof bttConf[Name].edit !='undefined')
                       $.extend(BTT,  bttConf[Name].edit);

                      return {
                          BTT     : BTT,
                          TYPE    : TYPEarr[0],     //ENT || SING
                          cls     : classes.replace(this.TYPE,''),
                          id      : desc[1],
                          Name    : Name      //ENTname || SINGname
                       }

                }
                $("div[class^=SING], div[class^=ENT]").map(function()
                   {

                       var elD = get_elmDet($(this)); //from element Details
                       /**
                        * uses
                        *
                        * BTT{style, name}
                        * id
                        * Name
                        * TYPE
                        * cls
                        */
                       $(this).wrapInner("<div class='ELMcontent' />");          // pentru a putea recupera continutul
                       $(this).prepend
                       (
                           "<div class='TOOLSem' style='display: none;'>" +
                               "<div class='TOOLSbtn'>" +
                               "   <span>" +
                               "       <input type='button' class='editModeBTT' "+elD.BTT.style+" name='EDIT' value='"+elD.BTT.name+"'" +
                               "                            onclick=\"iEdit.evCallback.editContent('"+elD.id+"','"+elD.Name+"','"+elD.TYPE+"','"+elD.cls+"'); return false;\">" +
                               "       <i>Edit Content</i>" +
                               "   </span>" +
                               "</div>" +
                           "</div>"
                       );
                   });

            },
            tools_addEnt : function(){

                 var get_elmDet = function(firstENT)    {

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

                      var classes = firstENT.attr('class');
                      var TYPEarr = classes.split(' ');
                      var nameENT = TYPEarr[TYPEarr.length - 1];        //ENTname || SINGname - numele ENT-ului se afla la pus ca ultima clasa a Elementului
                      var cls     = classes.replace('ENT','');   //ENT || SING - restul claselor fara denumirea de ENT sau SING
                     //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
                      var BTT = {status : 1, style : '',  name: '+'};

                      if(typeof bttConf[nameENT].addBt !='undefined')
                          $.extend(BTT, bttConf[nameENT].addBt);

                     //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

                      return {
                          BTT            : BTT,
                          nameENT        : nameENT,
                          FORM_class     :  cls+" addForm",
                          FORM_id        : "new_"+nameENT+'_'+LG,
                          FORM_content   : firstENT.find('.ELMcontent').html()

                       }

               }

                $('div[class^=allENTS]').map(function()
                {
                      var allENTS   = $(this);
                      var firstENT  = $(this).find('div[class^=ENT]:first');


                      if(firstENT.length > 0) // daca sunt elemente in cadrul allENTS
                      {
                        var elD = get_elmDet(firstENT); //from element Details
                        /**
                         * uses
                         * BTT {style, name, status}
                         *
                         * nameENT
                         * FORM_class
                         * FORM_id
                         * content
                         * */
                          //daca nu este eliminat butonul
                          if(elD.BTT.status)
                          {
                                //alert(BTTstatus);
                                 allENTS.prepend
                                 (
                                   "<div class='TOOLSem'>" +
                                       "<div class='TOOLSbtn'>" +
                                            "<span>" +
                                                  "<input type='button'  class='editModeBTT' "+elD.BTT.style+"  name='addNewENT' value='"+elD.BTT.name+"' " +
                                                                        " onclick=\"iEdit.evCallback.addEnt('"+elD.nameENT+"')\">" +
                                                  "<i>Add new</i>" +
                                            "</span>" +
                                       "</div>" +
                                   "</div>"
                                 );

                                  //____________________________________________________________________________________________
                                firstENT.before
                                (
                                     "<form action='' method='post' class='"+elD.FORM_class+"'   id='"+elD.FORM_id+"' style='display: none;'>" +
                                        "<div class='TOOLSem'>" +
                                              "<div class='TOOLSbtn'>                                                                        " +
                                              "     <span>" +
                                                      "<input type='submit' class='editModeBTT'   name='save_add"+elD.nameENT+"' value='s' />" +
                                                      "<i>save</i>" +
                                                  "</span>                         " +
                                              "     <span>" +
                                                      "<input type='button' class='editModeBTT'   name='EXIT' value='x' onclick=\"removeAddNew()\">" +
                                                      "<i>Exit</i>" +
                                                  "</span>       " +
                                        "     </div>          " +
                                        "</div> " +
                                        "<div class='ELMcontent'>" +
                                              elD.FORM_content +
                                        "</div>"+
                                    "</form>"
                                 );
                                  // ___________________________________________________________________________________________
                                 $("#"+elD.FORM_id).find('div[class^=ED]').empty();
                                  transform($("#"+elD.FORM_id),'.addForm');


                          }//if(BTT.status)
                      } //if(firstENT)
                });

            }

            ,testFunc : function(){

                console.log(test());
            }
           /* ,setLG : function(newLG){
                LG = newLG;
            }*/
        },
        evCallback : {

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
            // set by init.tools
            editContent : function(id,Name,TYPE,cls){


                    var elD = function(){

                           var BLOCK   = $('*[id^='+Name+'_'+id+'_]');

                           var BTT = getBtt(Name, {   deleteBt : {status : 1},   saveBt : {status : 1}   });
                           //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
                           var DELETE_tag  = function(){

                                var tag = '';
                                if (TYPE == 'ENT' && BTT.deleteBt.status)
                                {
                                   tag =
                                   "<span>" +
                                   "     <input type='submit'  class='editModeBTT'  name='delete_"+Name+"' value='d' />" +
                                   "     <i>Delete</i>" +
                                    "</span>";
                                }
                                return tag;  }();

                           var SAVE_tag   = function(){

                              var tag = '';
                              if ( BTT.saveBt.status )
                                  tag =
                                    "<span>" +
                                    "   <input type='submit'  class='editModeBTT'  name='save_"+Name+"' value='s' />" +
                                    "   <i>Save</i>" +
                                    "</span>";

                               return tag;
                           }();

                           var EXTRA_tags = function(){

                                /**
                                 ATENTIE!!!

                               * Este cumva ok ca butoanele extra sa fie in cadrul templateului pentru model, deoarece este mult mai clar asa
                               * Dar trebuie eventual gasita o metodata mai putin costisitoare si eventual mai eleganta
                               *
                               * DESCRIERE -
                               * daca inaintea unui element avem definit un elemnt.addTOOLSbtn - acesta va contine butoanele EXTRA pentru TOOLSbtn
                               * se adauc butoanele la forma standard pentru TOOLSbtn
                               * */
                                // POATE AR TREBUI SA GASESC O METODA MAI PUTIN COSTISITOARE
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

                           //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

                           return {
                                BLOCK      : BLOCK,
                                BTT        : BTT,
                                DELETE_tag :DELETE_tag,
                                SAVE_tag   : SAVE_tag,
                                EXTRA_tags : EXTRA_tags,
                                elmContent : BLOCK.find('.ELMcontent').html()

                           };
                    }();

                 //====================================================================================================================
                 var EXTRAS_TAG = '';
                 // if(eval('typeof '+Name+'_extra != "undefined" '))
                 //    EXTRAS_TAG = eval(Name+'_extra.getHTMLtag("'+id+'")'); //  daca este definit un Obiect de extra


                 //====================================================================================================================
                /**
                 * use
                 *
                 * cls
                 * id
                 * EXTRAS_TAG
                 * EXTRA_tags
                 * SAVE_tag
                 * DELETE_tag
                 * Name
                 * content
                 * */

                    $.when(
                           elD.BLOCK.after
                           (
                               "<form action='' method='post' class='"+cls+"' id='EDITform_"+id+"' >" +
                                   "<input type='hidden' name='BLOCK_id' value='"+id+"' />" +
                                   "<div class='TOOLSem'>" +
                                        "<div class='TOOLSbtn'>" +
                                                   EXTRAS_TAG+
                                               elD.EXTRA_tags +
                                               elD.SAVE_tag +
                                               elD.DELETE_tag+
                                              "<span>" +
                                              "    <input type='button'  class='editModeBTT' name='EXIT' value='x' onclick=\"iEdit.evCallback.exitEditContent_byName('"+Name+"','"+id+"')\">" +
                                              "    <i>Exit</i>" +
                                              "</span>" +
                                        "</div>" +
                                   "</div>" +
                                   "<div class='ELMcontent'>" +
                                       elD.elmContent+
                                   "</div>"+

                               "</form>"
                           )
                         ).then( function(){
                              elD.BLOCK.next().show();
                              async_Binds(elD.BLOCK,Name,id);
                           });

                //==================================================================================================================
                 // daca este definit un Obiect de extra
                // if( eval('typeof '+Name+'_extra != "undefined" '))  eval(Name+'_extra.alterCSS_EDITform()');


                  transform(elD.BLOCK,'form[class$='+Name+'][id=EDITform_'+id+']');
                  elD.BLOCK.hide();


            },
            // set by init.tools_addEnt
            addEnt: function(nameENT){

                var addFORM_id    = "new_"+nameENT+'_'+LG;

                remove_addNew();
                $('.TOOLSem > input[name=addNewENT]').parent().hide();
                $("#"+addFORM_id).show();

                $("#"+addFORM_id).find('.PRDpic > img').replaceWith("<img src='./MODELS/products/RES/small_img/site_produs_slice_pisici.jpg' alt='placeholder_img'>");


            },

            // set by async_Binds
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

   // LG = $("input[name=lang]").val();  //Need to get the current LG;
   // setEditMODE();

   iEdit.init.set_iEdit();
   //iEdit.init.setLG('ru');
   //iEdit.init.testFunc();

});



