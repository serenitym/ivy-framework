
/**
 *  UTILIZARE:
   $.post(procesSCRIPT_file,  { parsePOSTfile : parsePOSTfile ,$_POST_array  } )
 */
// DOCUMENTEAZA TOT editMODEUL URGENT

var LG = 'ro';
var procesSCRIPT_file     = 'procesSCRIPT.php';                   // intermediaza requesturile scripurilor .js
var parsePOSTfile_getTags = 'MODELS/blog/ADMIN/getTags.php';


var iEdit = function(){

    // deprecated it should be usede fmw.lang
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
     *                methName: 'methName [, otherMethod, ...]',
     *                async : new asyncConf({
     *                                modName: 'modName',
     *                                methName: 'methName [, otherMethod, ...]',
     *                                parsePOSTfile : 'filePath.php' ,
     *                                callBack_fn : (typeof fnName != 'undefined'  ? fnName : ''),
     *                                restoreCore : 0
     *                            })
     *                },
     *            edit: {},
     *            deleteBt : {methName:'', status : 1, atrName:"delete_"+Name, atrValue: 'd' },
                  saveBt : {methName:'',status : 1, atrName:"save_"+Name, atrValue: 's' },

                  // butoane extra pentru elements
                  // atentie allEnts trebuie sa isi declare singura extraButtons
                  extraButtons:
                  {
                      manageGroup: {
                          callBack: "ivyMods.team.showManageGroups();",
                          attrValue: 'manage Groups',
                          attrName: 'manage Groups',
                          attrType: 'submit/ button',
                          class: '',
                          methName: ''
                      },
                      showAllmembers:{
                          callBack: "ivyMods.team.showAllMembers();",
                          atrValue: 'show all Members',
                          class: ''
                      }
                  }

                  // adaugare de html direct in TOOLS
                  extraHtml: ['htmlConetnt ',
                             "<span>" +
                                 "<input type='hidden' name='action_modName' value='user' >" +
                                 "<input type='hidden' name='action_methName' value='deactivateProfile [, other methods]' >" +
                                 "<input type='submit' name='deactivate' value='deactivate' >" +
                             "</span>",
                             ''];
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


    // templates
    var templates = {
        get_editTools:    function(elD){
         return  "<div class='TOOLSem' style='display: none;'>" +
                   "<div class='TOOLSbtn'>" +
                   "   <span>" +
                   "       <input type='button' class='editModeBTT' "+elD.BTT.style+" name='EDIT' value='"+elD.BTT.atrValue+"'" +
                   "                            onclick=\"iEdit.evCallback.editContent('"+elD.id+"','"+elD.Name+"','"+elD.TYPE+"','"+elD.cls+"'); return false;\">" +
                   "       <i>Edit Content</i>" +
                   "   </span>" +
                   "</div>" +
                "</div>";
        },

        get_ivyMethRef :  function(btt){

            var methRef = ( typeof btt.methName == 'undefined' || !btt.methName) ? '' :
                         "<input type='hidden' name='action_methName' value='"+btt.methName+"'>";
            return methRef;

        },
        get_extraButtons: function(extraBtt){
           /**
            * extraBtt = {
            *  callBack : '',
            *  attrValue : key,
            *  attrName: key,
            *  attrType:  'button'
            *  methName: ''
            *  }
            */
           var methName = this.get_ivyMethRef(extraBtt);
           return "<span>" +
                    methName +
                    "<input type='"+extraBtt.attrType+"' " +
                         " class='editModeBTT' " +
                        " name='"+extraBtt.attrName+"' " +
                        " value='"+extraBtt.attrValue+"' " +
                        " onclick=\""+extraBtt.callBack+"\">" +
                "</span>" ;
        },
        get_saveButton  : function(BTT){
            var saveBt   = BTT.saveBt;
            var methName = this.get_ivyMethRef(saveBt);

            return "<span>" +
                        methName +
                    "   <input type='submit'  class='editModeBTT editM-save' " +
                              " name='"+saveBt.atrName+"' value='"+saveBt.atrValue+"' />" +
                    "   <i>Save</i>" +
                     "</span>";
        },
        get_deleteButton: function(BTT){
            var deleteBt = BTT.deleteBt;
            var methName = this.get_ivyMethRef(deleteBt);
            return  "<span>" +
                         methName +
                   "     <input type='submit'  class='editModeBTT' " +
                               " name='"+deleteBt.atrName+"' value='"+deleteBt.atrValue+"' />" +
                   "     <i>Delete</i>" +
                    "</span>";
        },
        get_editForm:     function(elD){

            return  "" +
                "<form action='' method='post' class='"+elD.cls+"' id='EDITform_"+elD.id+"' >" +
                     "<input type='hidden' name='BLOCK_id' value='"+elD.id+"' />" +
                     elD.ivyBind +
                     "<div class='TOOLSem'>" +
                          "<div class='TOOLSbtn'>" +
                                 //    EXTRAS_TAG +
                                 elD.EXTRA_tags +
                                 elD.SAVE_tag +
                                 elD.DELETE_tag+
                                "<span>" +
                                "    <input type='button'  class='editModeBTT editM-exit' " +
                                            "name='EXIT' value='x'" +
                                             " onclick=\"iEdit.evCallback.exitEditContent_byName('"+elD.Name+"','"+elD.id+"')\">" +
                                "    <i>Exit</i>" +
                                "</span>" +
                          "</div>" +
                     "</div>" +
                     "<div class='ELMcontent'>" +
                         elD.elmContent+
                     "</div>"+
                "</form>";
        },

        get_bindIvyModule: function(BTT){

            return "<input type='hidden' name='modName' value='"+BTT.modName+"' />" +
                   "<input type='hidden' name='methName' value='' />";
        },

        get_addTools: function(elD){

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
        },
        get_addForm:  function(elD){

            return "<form action='' method='post' class='"+elD.FORM_class+"'   id='"+elD.FORM_id+"' style='display: none;'>" +
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
        }
    };
    //========================================[ PROTECTED FUNCTIONS ]===========


    // helpers, management
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

    function async_save_reconstruct(Name ,id, postData){

        var test = '';
            $("*[id^="+Name+"_"+id+"_] *[class^=ED]").map(function(){

                var EDname =  $(this).attr('class').split(' ').pop();
                $(this).html(postData[EDname+'_'+LG]);

                test +=EDname + ' = '+ postData[EDname+'_'+LG]+' \n \n';
            });
            alert("iEdit - async_save_reconstruct "+test);


    }

    // ============================================[ elementary ]===============
    // elD's
    // for editContent
    function get_elementEdited(id,Name,TYPE,cls){
        var BLOCK   = $('*[id^='+Name+'_'+id+'_]');

       /* atentie atrName - depind lucruri de el deci nu ar trebui schimbat*/
        var BTT = getBtt(Name, {
            deleteBt : {status : 1, atrName:"delete_"+Name, atrValue: 'd'},
            saveBt   : {status : 1, atrName:"save_"+Name, atrValue: 's'},
            //extraButtons:{},
            //extraHtml:[],
            modName: ''
        });

        var EXTRA_ivyBinds = [];
        var DELETE_tag     = function(){
             if (TYPE != 'ENT' || !BTT.deleteBt.status) {
                 return '';
             }
            return templates.get_deleteButton(BTT);
        }();
        var SAVE_tag       = function(){

           if (!BTT.saveBt.status ) {
               return '';
           }
            return templates.get_saveButton(BTT);

        }();
        var EXTRA_tags     = function(){

           if(typeof BTT.extraButtons =='undefined') {
               console.log('editContent - EXTRA_tags(): NU Avem extra butoane pt '
                           + Name);
               return '';
           } else {

               console.log('editContent - EXTRA_tags(): Avem extra butoane pt '
                           + Name);

               var htmlButtons = '';
               for(var key in BTT.extraButtons){

                   var extraBtt = {callBack : '', attrValue : key,
                                   attrName: key, attrType:  'button', methName: ''};

                   $.extend(extraBtt, BTT.extraButtons[key]);
                   htmlButtons += templates.get_extraButtons(extraBtt);

               }
               return htmlButtons;
           }


        }();
         // from '.addTOOLSbtn'
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
        var EXTRA_html     = function(){

            if(typeof BTT.extraHtml =='undefined') {
                console.log('editContent - EXTRA_html(): ' +
                             'NU Avem extraHtml pt' + Name);
                return '';
            } else {

                console.log('editContent - EXTRA_Html():' +
                             ' Avem extraHtml pt '+ Name);

                var html = '';
                for(var key in BTT.extraHtml){
                   /* console.log('editContent - EXTRA_Html():' +
                             ' extraHtml = '+ BTT.extraHtml[key]);*/
                    html += BTT.extraHtml[key];
                }
                return html;
            }

        }();
        var ivyBind        = function(){

            if(typeof BTT.modName =='undefined'){
                return ''
            }
            return templates.get_bindIvyModule(BTT);
        }();

         return {
             id: id,
             Name : Name,
             TYPE: TYPE,
             cls: cls,

             BLOCK      : BLOCK,
             BTT        : BTT,
             DELETE_tag : DELETE_tag,
             SAVE_tag   : SAVE_tag,
             EXTRA_tags : EXTRA_tags + EXTRA_htmlTags + EXTRA_html,
             ivyBind    : ivyBind,
             elmContent : BLOCK.find('.ELMcontent').html()
         };

    }
    // for addElement
    function get_elementToAdd(firstENT, allEnts){
        var elD = {};

        if(firstENT.length != 0) {

            elD.classes      = firstENT.attr('class');
            elD.TYPEarr      =  elD.classes.split(' ');
            //ENT || SING - restul claselor fara denumirea de ENT sau SING
            elD.cls          =  elD.classes.replace('ENT','');
            //ENTname || SINGname - numele ENT-ului se afla la pus ca ultima clasa a Elementului
            elD.nameENT      =  elD.TYPEarr[ elD.TYPEarr.length - 1];
            elD.FORM_content = firstENT.find('.ELMcontent').html();
            elD.FORM_class   =  elD.cls+" addForm";
            elD.FORM_id      = "new_"+ elD.nameENT+'_'+LG;
            // buttons settings
            elD.BTT          = getBtt( elD.nameENT, {});
            elD.BTTadd       = {status : 1, style : '', atrValue: '+', methName: '' };

            if (typeof elD.BTT.addBt !='undefined') {
                $.extend(elD.BTTadd, elD.BTT.addBt);
            }

            elD.html_ctrlAction = function(){

                 if (elD.BTT.modName != 'undefined') {
                     return  "<input type='hidden' name='modName' value='"+elD.BTT.modName+"' />" +
                             "<input type='hidden' name='methName' value='"+elD.BTTadd.methName+"' />";

                 }
                 return '';
            }();

        } else {
            elD.BTT             = {status: false};
        }



        var allEntsClss = allEnts.attr('class').split(' ');
        var allEntsName = allEntsClss[allEntsClss.length - 1];
        //console.log('allEntsName = '+ allEntsName);

        elD.BTTall = getBtt(allEntsName, {});

        elD.html_extraBTTS = function(){

            if( elD.BTTall.extraButtons =='undefined') {
                console.log('get_elementToAdd - NU Avem extra butoane');
                return '';
            } else {

                console.log('get_elementToAdd - Avem extra butoane');

                var htmlButtons = '';
                for(var key in elD.BTTall.extraButtons){

                    var extraBtt = { callBack : '',atrValue : key,
                                     atrName: key,  atrType:  'button'};

                    $.extend(extraBtt, elD.BTTall.extraButtons[key]);

                    htmlButtons += templates.get_extraButtons(extraBtt);
                }
                //console.log('extra butoanele sunt = ' + htmlButtons);
                return htmlButtons;
            }
        }();

        return elD;

    }
    // for init:tools
    function get_elementToEdit(elm){

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

    // ===========================================[ binds to action ]===========
    // 2
    function async_actionBind(editForm, actionName, Name, id){
        editForm
            .find('.TOOLSbtn input[class^=editModeBTT][name^='+actionName+'_]')
            .attr('onclick',"iEdit.evCallback.async_"+actionName+"('"+Name+"','"+id+"'); return false;");
    }
    // 2
    function ivyMethod_actionBind(editForm,  bttName,  methName, modName){
        //console.log('EDITmode - actionBtns_binds: '+modName);

        editForm
            .find('.TOOLSbtn input[name='+bttName+']')
             .on('click', function(){
                //alert('bttName = '+bttName+' methName = '+methName);
                editForm
                    .find("input[name=methName]")
                    .first()
                    .attr('value',methName);

                if(typeof modName != 'undefined' && modName == '') {
                    editForm
                        .find("input[name=modName]")
                        .first()
                        .attr('value',modName);
                }

             });
    }
    // 1
    function actionBtns_binds(elD){
        //BLOCK,Name,id

         var editForm = elD.BLOCK.next('form');
         var TOOLSbtn = editForm.find('.TOOLSbtn');

         if(TOOLSbtn.length > 0  && typeof elD.BTT != 'undefined')
         {

             // atentie se poate ajunge la a avea 2 eventuri de onclick pe un
             // buton deci ATENTIE MARE!!!
             //=============================[ asincron Binds ]==================
              if(typeof elD.BTT.deleteBt !='undefined'
                 && typeof elD.BTT.deleteBt.async !='undefined'
              ){
                  async_actionBind(editForm, 'delete', elD.Name, elD.id);
              }
              if(typeof elD.BTT.saveBt !='undefined'
                  && typeof elD.BTT.saveBt.async !='undefined'
              ){
                 async_actionBind(editForm, 'save', elD.Name, elD.id);
              }

              //=============================[set moduleControler for action ]==

             if(typeof elD.BTT.modName !='undefined'){

                 /**
                  * WORK with
                  * "<span>" +
                         "<input type='hidden' name='action_methName' value='deleteProfile'>" +
                         "<input type='submit' name='deleteProfile' value='delete profile' />" +
                     "</span>"

                  * see: templates.get_extraButtons
                  */
                 editForm
                     .find('.TOOLSbtn input[name=action_methName]')
                     .map(function(){
                         var bttName = $(this).siblings('input[type=submit]').attr('name');
                         var methName = $(this).attr('value');
                         // optional change modName
                         var modName = $(this).siblings('input[name=action_modName]').attr('value');
                         //console.log('EDITmode - actionBtns_binds: '+modName);
                         ivyMethod_actionBind(editForm, bttName, methName, modName);
                     });
             }

         }


    }

    // ==========================================[ live edit ]==================
    function transform(BLOCK,formSelector, elmName){

        $('*[classs$=hoverZoomLink]').removeClass('hoverZoomLink');

         BLOCK.find('*[class^=ED]').map(function()
         {
              // selELEM =  $(this).attr('class')+' ';
              if($(this).parents('*[class^=allENTS]').length <= 1)
              {

                  var EDclass  = $(this).attr('class');
                  //EDclass  = $.trim(EDclass);

                   //console.log(EDclass);
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


                   //console.log('EDtype '+EDtype+'  EDname '+EDname+' value '+EDvalue);
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




    //========================================[ PUBLIC FUNCTIONS ]==============
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

                $("*[class^=SING], *[class^=ENT]").map(function()
                {
                    var elD   = get_elementToEdit($(this)); //from element Details
                    var tools = templates.get_editTools(elD);
                    // pentru a putea recupera continutul
                    $(this).wrapInner("<div class='ELMcontent' />");
                    $(this).prepend(tools);
                });

            },
            tools_addEnt : function(){

                function prepare_addForm(elD, addForm){
                      // *** ATENTIE trebuie golita si imaginea
                      /**
                       * Se golesc toate fieldurile editabile
                       * apoi se pune o alta clasa fieldurilor editabile - cu CKeditor pentru a
                       * nu mai trebui sa distrug instantele de CKeditor pentru add-uri
                       *
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
                    var elD       = get_elementToAdd(firstENT, allENTS); //from element Details
                    var tools     =  templates.get_addTools(elD);
                    var addForm   =  $("#"+elD.FORM_id);

                    //console.log('addEnt tools = '+tools);
                    if (tools) {
                        allENTS.prepend(tools);
                        /**
                        * Daca nu exista ENTS visible inseamna ca nu a fost
                        * adaugat nici un ENT => trebuie sa apara TOOLSem din
                        * start , fara mouse over
                        */
                        var countENTS = allENTS.find('*[class^=ENT]:visible').length;
                        if(countENTS == 0) {
                            addForm.prev('.TOOLSem')
                                   .css('visibility','visible');
                        }
                    }

                    // daca sunt elemente in cadrul allENTS
                    if (firstENT.length != 0 && elD.BTTadd.status) {
                        firstENT.before(templates.get_addForm(elD));
                         prepare_addForm(elD, addForm);
                         transform(addForm,'form[class$=addForm]', elD.nameENT);
                         //console.log('addForm id = '+ addForm.attr('id'));
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

            //================================[ essencials ]====================
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

                 var elD  = get_elementEdited(id,Name,TYPE,cls);
                 var form = templates.get_editForm(elD);
                 //console.log(form);

                 $.when(
                     elD.BLOCK.after(form)
                 ).then(
                     function(){
                          elD.BLOCK.next().show();
                          actionBtns_binds(elD);
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
            //===========================================[ ]====================

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



