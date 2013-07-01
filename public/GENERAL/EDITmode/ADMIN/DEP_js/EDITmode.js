/**
 * UTILIZARE GENERALA EDITmode.js
 *
 * < * class='allENTS [entSName]' id = '[entSName]_[LG]' >
 *     <class='ENT [entName]' id = '[entName]_[id]_[LG]' >
 *
 *  < * class='SING [singName]' id = '[singName]_[id]_[LG]' >
 */


var LG = 'ro';
var procesSCRIPT_file     = 'procesSCRIPT.php';                   // intermediaza requesturile scripurilor .js
var parsePOSTfile_getTags = 'MODELS/blog/ADMIN/getTags.php';
/*var parsePOSTfile2     = 'MODELS/products/ADMIN/PROMO.php';    // scriptul dorit pt $.post()*/

/**
 *  UTILIZARE:
   $.post(procesSCRIPT_file,  { parsePOSTfile : parsePOSTfile ,$_POST_array  } ) */



//============================================== [ HEEELP ] ============================================================

//lucruri care nu ar trebui sa stea aici
//dar pentru ca se incarca asincron....kkkt

// in cazul in care se doreste schimbarea pt butoane de nume, style, exista sau nu
//trebuie cumva setat daca vreau sa nu indicatii pentru butoane
/*
* probabil mai intai ar trebui sa vad cum as vrea sa ma refer
*
* BTT.elemName.add.status hmm...
* BTT.add.elName.status
*
* elemName = new bttconf(
*      {
*        add :{status: ceva, style: 'altceva '}
*      });
*
*
* bttConf[elemName].add['name']

* */

function async(){

      var defaults = { procesSCRIPT : 'procesSCRIPT' };
      var options = arguments;

      var prop = $.extend(defaults, options);

      //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
      var fns = {

          load : function(){
              // $.load(defaults.procesSCRIPT, options );
          }
      };

      return $.extend(prop, fns);
      /*
      options:
       - parsePOSTfile
       - asincrSCRIPT
       - modName
       - methName

      * */
  }

var bttConf = function()
{


    return  {
        elemName: {
          add: {
              name: 'un nume',
              style : 'oric',
              class : '',
              status: '',
              asynco : new async({modName: 'modName', methName: 'methName'})
              },
          delete:{},
          edit: {},
          save:{}
        }
    }
}();

function BTT(){

    this.name               = [];
    this.style              = [];
    this.class              = [];
    this.status             = [];
    this.asincr             = [];
    this.asincrSCRIPT       = [];
    this.asyncReq_action    = [];  // in curs de implementare

}


var BTTadd      = new BTT();
var BTTdelete   = new BTT();
var BTTedit     = new BTT();
var BTTsave     = new BTT();

BTTedit.name['SGrecord'] = 'edit Record';
BTTedit.style['SGrecord'] = " style='width:60px;  margin-left: -40px;'  ";

BTTadd.status['comment'] = false;
BTTsave.status['comment'] = false;
//BTTadd.style['comment'] = " style='width:80px;  margin-left: -60px;'  ";

BTTadd.name['record'] = 'add Record';
BTTadd.style['record'] = " style='width:80px;  margin-left: -60px; background-color: #D9E9F1;'  ";

BTTadd.status['recordHome'] = false;
BTTsave.status['recordHome'] = false;

BTTsave.status['record'] = false;


/*==================================[picManager - pic-full ]=================================*/
BTTadd.status['pic-full'] = false;

BTTsave.asincrSCRIPT['pic-full'] = 'PLUGINS/picManager/ADMIN/savePic.php';
BTTsave.asincr['pic-full'] = 'carousel_savePic';                   // functie apelata la click pe save sau doar true

BTTdelete.asincrSCRIPT['pic-full'] = 'PLUGINS/picManager/ADMIN/deletePic.php';
BTTdelete.asincr['pic-full'] = 'carousel_deletePic'; //functie apelata la apasarea pe butonul delete

// numele entului sau singului determina si numele fisierul care va procesa datele trimise asincron

//ex de eliminare a butonului de add
//BTTadd.status['nameEnt'] = 0;


//asyncReq_action


//====================================== [EXTRA OPTIONS] ===============================================================

//trebuie gandit mai generalizat ...aceasta clasa este foarte limitata
// pentru blog am nevoie de butoane de SEO si publish....
function getHTMLtags(id){

}
function getHTMLtag(id)      {
    switch(this.INPTtype)
    {

        case 'multiExtra' : tag = "<span>" +
                                    "<input type='button'  class='editModeBTT' name='EXTRA' " +
                                            "value='"+this.value+"' " +
                                            "onclick=\"javascript:"+this.MODname+".EXTRAS_display('"+id+"');\"  " +
                                            "style='width:"+this.width+"px;' />" +
                                    "<i>extra</i>" +
                             "</span>";
                        break;
        case 'submit' : tag = "<span>" +
                                    "<input type='submit'  class='editModeBTT' " +
                                            "name='"+this.value+"_"+this.MODname+"' " +
                                            "value='"+this.value+"'  " +
                                            "style='width:"+this.width+"px;' />" +
                                    "<i>extra</i>" +
                             "</span>";
                        break;
    }

    return tag;
}

function alterCSS_EDITform() {
    width = this.width + 10;
    TOOLSem_width = width+parseInt($('form[id^=EDITform] > .TOOLSem').css('width'));
    TOOLSem_marginLeft = (-1)*TOOLSem_width;
    $('form[id^=EDITform] > .TOOLSem').css('width',TOOLSem_width)
                             .css('margin-left',TOOLSem_marginLeft);
}
function closeEXTRAS()       {

    $('#EXTRAS_display').remove();
}
function EXTRAS_display(id)  {
    $('body').append
    (
        "<div id='EXTRAS_display' >" +
            "<p><input type='button' name='closeEXTRAS' value='x'  onclick='javascript:"+this.MODname+".closeEXTRAS();'/></p>" +
        "</div>"
    );
    extraSTR='';
    for(var key in this.extras)
    {
        extraNAME =this.extras[key];

        $('#EXTRAS_display').append("<div class='"+extraNAME+"'></div>");
        eval(extraNAME+'('+id+')');                            // se apeleaza functiile de extras
    }
    //extraSTR +=this.extras[key];
   // alert(extraSTR);
}
/**
 * MODname = apelantul(SINGname-ul)
 * extras  = numele functiei de extra
 * width   = width - butonul de extra
 * value   = denumirea butonului de extra
 * INPTtype   = input type button / submit
 *
 */

function EXTRAS(MODname,extras,width,value,INPTtype){

    this.MODname = MODname;                 // numele tagului pe care se aplica acest extra
    this.extras = extras;                   // array cu numele extraurilor
    this.width = width;                     // dimensiunea butonului
    this.value = value;                     // atributul value pentru button
    this.INPTtype = INPTtype;               // button || submit

    this.getHTMLtag        = getHTMLtag;
    this.getHTMLtags        = getHTMLtags;
    this.alterCSS_EDITform = alterCSS_EDITform;
    this.EXTRAS_display    = EXTRAS_display;
    this.closeEXTRAS       = closeEXTRAS;

}




//======================================================================================================================

$(document).ready(function()      {


    /**
     * -- SETTINGS--
     *
     *  EDsel => EDname = new SELoptions(Array_ro,Array_en);
     *  extras => EDname_extra = functionName(id);
     */

    LG = $("input[name=lang]").val();  //Need to get the current LG;
   EditMODE();
});

//deprecated
function ExitEditMODE()           {

    $('input[name=editMODE]').show();
    $('input[name=exitEditMODE]').hide();
    $('#status_editMODE').val('false');

    $('form[id^=new_]').remove();
    $('.TOOLSem').remove();
    ExitEditContent('ENT');
    ExitEditContent('SING');

}
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

    var BTTvalue = typeof BTTedit.name[Name]!='undefined' ? BTTedit.name[Name] : 'e';
    var BTTstyle = typeof BTTedit.style[Name]!='undefined' ? BTTedit.style[Name] : "";



  //================================================================================================================
    elmSel.prepend
    (
        "<div class='TOOLSem' style='display: none;'>" +
            "<div class='TOOLSbtn'>" +
            "   <span>" +
            "       <input type='button' class='editModeBTT' "+BTTstyle+" name='EDIT' value='"+BTTvalue+"'" +
            "                            onclick=\"EditContent('"+id+"','"+Name+"','"+TYPE+"','"+cls+"'); return false;\">" +
            "       <i>Edit Content</i>" +
            "   </span>" +
            "</div>" +
        "</div>"
    );

}





/*===========================================================================================*/
function EditMODE()               {

    $("div[class^=ENT], div[class^=SING]").wrapInner("<div class='ELMcontent' />");          // pentru a putea recupera continutul

    setTOOLS();                                                                          // BTT de edit - EditContent(id,Name,TYPE)
    setENTadd();                                                                         // BTT de add - addENT(nameENT)
    $('input[name=editMODE]').hide();
    $('input[name=exitEditMODE]').show();


    $("div[class^=ENT] , div[class^=SING]").live({
        mouseover   : function() { $(this).find('.TOOLSem').show();},
        mouseout    : function() { $(this).find('.TOOLSem').hide(); }
    });

}


function setTOOLS()               {

    $("div[class^=SING], div[class^=ENT]").map(function()
    {

        desc    = $(this).attr('id').split('_');
        classes = $(this).attr('class');
        TYPEarr = classes.split(' ');

        TYPE = TYPEarr[0];     //ENT || SING
        cls  = classes.replace(TYPE,'');
        id   = desc[1];
        Name = desc[0];        //ENTname || SINGname

      //================================================================================================================

        BTTvalue = typeof BTTedit.name[Name]!='undefined' ? BTTedit.name[Name] : 'e';
        BTTstyle = typeof BTTedit.style[Name]!='undefined' ? BTTedit.style[Name] : "";



      //================================================================================================================
        $(this).prepend
        (
            "<div class='TOOLSem' style='display: none;'>" +
                "<div class='TOOLSbtn'>" +
                "   <span>" +
                "       <input type='button' class='editModeBTT' "+BTTstyle+" name='EDIT' value='"+BTTvalue+"'" +
                "                            onclick=\"EditContent('"+id+"','"+Name+"','"+TYPE+"','"+cls+"'); return false;\">" +
                "       <i>Edit Content</i>" +
                "   </span>" +
                "</div>" +
            "</div>"
        );
    });
}
function setENTadd()              {


    $('div[class^=allENTS]').map(function()
    {

          firstENT  = $(this).find('div[class^=ENT]:first');


          if(typeof firstENT.attr('id') !='undefined') // daca sunt elemente in cadrul allENTS
          {


              classes = firstENT.attr('class');
              TYPEarr = classes.split(' ');


              nameENT = TYPEarr[TYPEarr.length - 1];        //ENTname || SINGname - numele ENT-ului se afla la pus ca ultima clasa a Elementului
              cls     = classes.replace('ENT','');   //ENT || SING - restul claselor fara denumirea de ENT sau SING



            /*  desc    = firstENT.attr('id').split('_');
              nameENT = desc[0];*/
              BTTstatus = typeof BTTadd.status[nameENT]!='undefined' ? BTTadd.status[nameENT] : 1;    //citeste statusul butonului daca acesta exista


           //===========================================================================================================

              //daca nu este eliminat butonul
              if(BTTstatus)
              {
                        allENTS   = $(this);
                        //alert(BTTstatus);

                        BTTvalue = typeof BTTadd.name[nameENT]!='undefined' ? BTTadd.name[nameENT] : '+';
                        BTTstyle = typeof BTTadd.style[nameENT]!='undefined' ? BTTadd.style[nameENT] : "";



                       //===============================================================================================

                         allENTS.prepend
                         (
                                   "<div class='TOOLSem'>" +
                                       "<div class='TOOLSbtn'>" +
                                            "<span>" +
                                                  "<input type='button'  class='editModeBTT' "+BTTstyle+"  name='addNewENT' value='"+BTTvalue+"'  onclick=\"addENT('"+nameENT+"')\">" +
                                                  "<i>Add new</i>" +
                                            "</span>" +
                                       "</div>" +
                                   "</div>"
                         );
                        //______________________________________________________________________________________________




                              content = firstENT.find('.ELMcontent').html();
                              FORM_id    = "new_"+nameENT+'_'+LG;
                              FORM_class = cls+" addForm";



                          //____________________________________________________________________________________________
                          firstENT.before
                          (

                                  "<form action='' method='post' class='"+FORM_class+"'   id='"+FORM_id+"' style='display: none;'>" +
                                      "<div class='TOOLSem'>" +
                                            "<div class='TOOLSbtn'>                                                                        " +
                                            "     <span>" +
                                                    "<input type='submit' class='editModeBTT'   name='save_add"+nameENT+"' value='s' />" +
                                                    "<i>save</i>" +
                                                "</span>                         " +
                                            "     <span>" +
                                                    "<input type='button' class='editModeBTT'   name='EXIT' value='x' onclick=\"removeAddNew()\">" +
                                                    "<i>Exit</i>" +
                                                "</span>       " +
                                      "     </div>          " +
                                      "</div> " +
                                      "<div class='ELMcontent'>" +
                                             content +
                                      "</div>"+
                                  "</form>"

                          );

                          // ___________________________________________________________________________________________
                          $("#"+FORM_id).find('div[class^=ED]').empty();
                          TRANSFORM($("#"+FORM_id),'.addForm');


              }//if(BTTstatus)



          } //if(firstENT)




    });

}

function addENT(nameENT)          {
    addFORM_id    = "new_"+nameENT+'_'+LG;

    removeAddNew();
    $('.TOOLSem > input[name=addNewENT]').parent().hide();
    $("#"+addFORM_id).show();

    $("#"+addFORM_id).find('.PRDpic > img').replaceWith("<img src='./MODELS/products/RES/small_img/site_produs_slice_pisici.jpg' alt='placeholder_img'>");


}
function removeAddNew()           {
     addFORM_id    = "new_"+nameENT+'_'+LG;

    $('.TOOLSem > input[name=addNewENT]').parent().show();
    $("#"+addFORM_id).hide();

}

/**
 * exist a specific element
 *
 * WORKING MODEL
 *  REMOVE  - <form action='' method='post' class='"+cls+"' id='EDITform_[id]' >" +
 *  SHOW    - <* class='TYPE Name' id='Name_{~id}_LG'>
 *
 *  cls = 'otherClasses Name'
 *
 * @param TYPE  = ENT / SING
 * @param Name
 * @constructor
 */

function ExitEditContent_byName(Name,id){

    $('form[id=EDITform_'+id+'][class$='+Name+'] textarea[id^=editor_]').map(function(){

         idTxa = $(this).attr('id');
         if (CKEDITOR.instances[idTxa]) CKEDITOR.instances[idTxa].destroy(true);
    });

   // alert('ExitEditContent_byName inchide '+'form[id=EDITform_'+id+'][class$='+Name+']');
   $('form[id=EDITform_'+id+'][class$='+Name+']').remove();
   //$('*[class$='+Name+']').not('*[id*=_new_]').show();
   $('*[id^='+Name+'_'+id+'_]').not('*[id*=_new_]').show();

}
function ExitEditContent(TYPE)          {

  /* LG='ro'*/;
    //pt mai multe editoare
    $('textarea[id^=editor]').map(function(){

        id = $(this).attr('id');
         if (CKEDITOR.instances[id]) CKEDITOR.instances[id].destroy(true);
    });

   $('form[id^=EDITform]').remove();
   $('.'+TYPE).not('*[id*=_new_]').show();

}
function EditContent(id,Name,TYPE,cls)  {
    
    // cls = toate clasele elementului, minus tipul lui

    // ExitEditContent('ENT');
     //ExitEditContent('SING');
     var DELETE_tag='';
     var SAVE_tag='';
     var EXTRAS_TAG='';
     var EXTRA_tags='';
     var content;
   //___________________________________________________________________________________________________________________


    if(TYPE == 'ENT')
    {
         //citeste statusul butonului daca acesta exista daca statusul este 0 - butonul nu va mai fii afisat
        DELETE_tag  =
            ( typeof BTTdelete.status[Name]!='undefined' ? BTTdelete.status[Name] : 1 )

            ? "<span>" +
                   "     <input type='submit'  class='editModeBTT'  name='delete_"+Name+"' value='d' />" +
                   "     <i>Delete</i>" +
                   "</span>"
            : "";
    }

      /*BTTsave_status  =
         typeof BTTsave.status[Name]!='undefined' ? BTTsave.status[Name] : 1;*/

        SAVE_tag =
          (typeof BTTsave.status[Name]!='undefined'  ? BTTsave.status[Name]: 1 )

          ?  "<span>" +
                 "   <input type='submit'  class='editModeBTT'  name='save_"+Name+"' value='s' />" +
                 "   <i>Save</i>" +
                 "</span>"
          : "";



    //====================================================================================================================

   // if(eval('typeof '+Name+'_extra != "undefined" '))
    //    EXTRAS_TAG = eval(Name+'_extra.getHTMLtag("'+id+'")'); //  daca este definit un Obiect de extra


  //====================================================================================================================


    //BLOCK   = $('*[id='+Name+'_'+id+'_'+LG+']');
    BLOCK   = $('*[id^='+Name+'_'+id+'_]');
    content = BLOCK.find('.ELMcontent').html();
  //====================================================================================================================

    /*
    ATENTIE!!!

    * Este cumva ok ca butoanele extra sa fie in cadrul templateului pentru model, deoarece este mult mai clar asa
    * Dar trebuie eventual gasita o metodata mai putin costisitoare si eventual mai eleganta
    *
    * DESCRIERE -
    * daca inaintea unui element avem definit un elemnt.addTOOLSbtn - acesta va contine butoanele EXTRA pentru TOOLSbtn
    * se adauc butoanele la forma standard pentru TOOLSbtn
    * */
    // POATE AR TREBUI SA GASESC O METODA MAI PUTIN COSTISITOARE

    EXTRA_btns = BLOCK.prevAll('.addTOOLSbtn');
    if(!EXTRA_btns.length)                          //daca nu gaseste butoane extra sa zicem la inceputul lui allEnts
         EXTRA_btns = BLOCK.prev('.addTOOLSbtn');   // incearca sa caute butoane inaintea entului curent

    if(EXTRA_btns.length ){

        EXTRA_btns.find('input').addClass('editModeBTT').wrap("<span>");
        EXTRA_tags = EXTRA_btns.html();

    }


  //====================================================================================================================
    $.when(
            BLOCK.after
            (
                "<form action='' method='post' class='"+cls+"' id='EDITform_"+id+"' >" +
                    "<input type='hidden' name='BLOCK_id' value='"+id+"' />" +
                    "<div class='TOOLSem'>" +
                         "<div class='TOOLSbtn'>" +
                                EXTRAS_TAG+
                                EXTRA_tags +
                                SAVE_tag +

                                DELETE_tag+
                               "<span>" +
                               "    <input type='button'  class='editModeBTT' name='EXIT' value='x' onclick=\"ExitEditContent_byName('"+Name+"','"+id+"')\">" +
                               "    <i>Exit</i>" +
                               "</span>" +
                         "</div>" +
                    "</div>" +
                    "<div class='ELMcontent'>" +
                        content+
                    "</div>"+

                "</form>"
            )
          ).then( function(){
               BLOCK.next().show();
               asincr_Binds(BLOCK,Name,id);
            });

    //=====================================================================================================================
    // daca este definit un Obiect de extra
   // if( eval('typeof '+Name+'_extra != "undefined" '))  eval(Name+'_extra.alterCSS_EDITform()');





   TRANSFORM(BLOCK,'form[class$='+Name+'][id=EDITform_'+id+']');
   BLOCK.hide();
}


//====================================================[ ASICRON FUNCTIONALITY ]=========================================
/**
 * BASED on
 *
 * "   <input type='submit'  class='editModeBTT'  name='delete_"+Name+"' value='d' />"
 * "   <input type='submit'  class='editModeBTT'  name='save_"+Name+"' value='s' />"
 *
 * BTTsave.asincr['pic-full'] = true;                   // functie apelata la click pe save sau doar true
   BTTdelete.asincr['pic-full'] = 'delete_picManager'; // functie apelata la apasarea pe butonul delete

 * @param BLOCK
 * @param Name = numele elemntului editat
 * @param id   = id-ul elementului
 */

function asincr_Binds(BLOCK,Name,id){

    var TOOLSbtn = BLOCK.next('form').find('.TOOLSbtn');

   if(TOOLSbtn.length > 0)
   {
       if( typeof BTTdelete.asincr[Name]!='undefined')
       {

           var deleteBtt = TOOLSbtn.find('input[class=editModeBTT][name^=delete_]');
           deleteBtt.attr('onclick',"asincr_delete('"+Name+"','"+id+"'); return false;");

           //alert(deleteBtt.attr('name'));
       }
       if( typeof BTTsave.asincr[Name]!='undefined')
       {
           TOOLSbtn.find('input[class=editModeBTT][name^=save_]')
               .attr('onclick',"asincr_save('"+Name+"','"+id+"'); return false;");
       }
   }
    else
   alert('selectul toolsbtn nu functioneza');



}
/**
 * ====[ UTILIZARE GENERALA EDITmode.js ]===============================================================
 *
 * < * class='allENTS [entSName]' id = '[entSName]_[LG]' >
 *     <* class='ENT [entName]' id = '[entName]_[id]_[LG]' >
 *          <* class='ED[txt, txa, editor ] [elName] ' >[  Content ]</*>
 *     </*>
 *
 *  < * class='SING [singName]' id = '[singName]_[id]_[LG]' >
 *
 *
 *  ====[ RETURNING MODEL ]=============================================================================
 *
 *  <form action='' method='post' class='[otherClasses Name]' id='EDITform_[id]' >
        <input type='hidden' name='BLOCK_id' value='"+id+"' />
        <div class='TOOLSem'>
             <div class='TOOLSbtn'>
                    EXTRAS_TAG
                    EXTRA_tags
                    SAVE_tag

                    DELETE_tag+
                   <span>
                       <input type='button'  class='editModeBTT' name='EXIT' value='x' onclick=\"ExitEditContent_byName('"+TYPE+"','"+Name+"')\">
                       <i>Exit</i>
                   </span>
             </div>
        </div>
        <div class='ELMcontent'>
           [ continutul elementului editat ]
           <input name='[elName]_[LG]' value='[ Content ]' />
       </div>

    "</form>"
 *
 * ====[ WORKING with ]===========================================================================
 *   -  ExitEditContent_byName(TYPE,Name)
 *
 * ====[ LOGISTICS ]===================================================================================
 *
 *  - processFILE = Name          - numele functiei care va procesa datele
 *
 *  - postData
 *      - id-ul
 *      - Name celui ce cere
 *      - action [delete / save]
 *
 *  - PT save
 *    + colect data's => -  all input de la care extragem Name si value
 *                       - puse intr-un vector
 *
 *   SEND
 *  -  procesSCRIPT_file = 'procesSCRIPT.php';                 // intermediaza requesturile scripurilor .js
 *  -  parsePOSTfile4    = 'PLUGINS/SEO/ADMIN/SEO.php';

    UTILIZARE:
    $.post(procesSCRIPT_file,  { parsePOSTfile : parsePOSTfile ,$_POST_array  } )

    CALLBACK funcs:
    - any callback functions should have Name & id as parameters
 *
 */
/**
 *
 * @param Name
 * @param id
 * @param selector = poate fii input, textarea, input[type='']
 */
function collect_postData(Name ,id, selector){

    var postData = {};

    $('form[class$='+Name+'][id=EDITform_'+id+'] ').find(selector).map(function(){

      var  inputName  = $(this).attr('name');
      var  inputValue = $(this).val();
        postData[inputName] = inputValue;
    });

    return postData;

}

function asincr_delete(Name ,id) {

       //alert('Incerc sa delete '+Name+''+id);


      //============[ callScript ]============================================================
      if( typeof BTTdelete.asincrSCRIPT[Name]!='undefined')
      {
          var parsePOSTfile_deletePic = BTTdelete.asincrSCRIPT[Name];
          var postData = collect_postData(Name, id, "input[type=hidden]");

          postData.parsePOSTfile = parsePOSTfile_deletePic;

          // TEST
         /* var test="Delete from "+Name+' id ='+id+"\n";
          for(var key in postData){
              test += key+' = '+postData[key]+"\n";
          }
          alert(test);*/
          $.post(procesSCRIPT_file, postData ,function(data){
              if(typeof  data == 'undefined') alert('erro - your data has not been deleted!! ');
          });
      }

      //============[ callback Func]==========================================================
      var deleteFn =  BTTdelete.asincr[Name];

      if (typeof deleteFn == 'string' &&
          eval('typeof ' + deleteFn) == 'function')
      {
          //alert('Considera ca s-a gasit o functie');
          eval(deleteFn+'("'+Name+'","'+id+'")');

      }
    //====================================================================
    ExitEditContent_byName(Name,id);

     $("*[id^="+Name+"_"+id+"]").remove();


}
/**
 * aditional in aceasta functie datele noi modificate trebuie puse la locul lor
 *
 * @param Name
 * @param id
 */

function asincr_save(Name ,id) {



      //============[ callScript ]============================================================
      var postData = collect_postData(Name, id, "input[type=text] ,input[type=hidden] , textarea, select");

      if( typeof BTTsave.asincrSCRIPT[Name]!='undefined')
      {
          var parsePOSTfile_savePic = BTTsave.asincrSCRIPT[Name];

          postData.parsePOSTfile = parsePOSTfile_savePic;


          $.post(procesSCRIPT_file, postData, function(data){
              // data este affected_rows
              if(typeof data!='undefined')
                alert('salvarile au fost efectuate');
            } );
      }
     // TEST
     /* var test="Save from "+Name+' id ='+id+"\n";
      for(var key in postData){
          test += key+'  =  '+postData[key]+"\n\n";
      }
      alert(test);*/


     //====================================================================
      ExitEditContent_byName(Name,id);
      asincr_save_reconstruct(Name, id, postData);

}
/**
 * ====[ UTILIZARE GENERALA EDITmode.js ]===============================================================
 *
 * < * class='allENTS [entSName]' id = '[entSName]_[LG]' >
 *     <* class='ENT [entName]' id = '[entName]_[id]_[LG]' >
 *          <* class='ED[txt, txa, editor ] [elName] ' >[  Content ]</*>
 *     </*>
 *
 *  < * class='SING [singName]' id = '[singName]_[id]_[LG]' >
 *
 *
 */
function asincr_save_reconstruct(Name ,id, postData) {
    var test = '';
    $("*[id^="+Name+"_"+id+"_] *[class^=ED]").map(function(){

        var EDname =  $(this).attr('class').split(' ').pop();
        $(this).html(postData[EDname+'_'+LG]);

        test +=EDname + ' = '+ postData[EDname+'_'+LG]+' \n \n';
    });
    alert(test);

     //============[ callback Func]==========================================================
      var saveFn =  BTTsave.asincr[Name];

      if (typeof saveFn == 'string' &&
          eval('typeof ' + saveFn) == 'function')
      {
          //alert('Considera ca s-a gasit o functie');
          eval(saveFn+'("'+Name+'","'+id+'")');

      }

}


//======================================================================================================================


function TRANSFORM(BLOCK,formSelector){


    BLOCK.find('*[class^=ED]').map(function(){
       // selELEM =  $(this).attr('class')+' ';

        desc = ($(this).attr('class')+' ').split(' ');

        EDtype  = desc[0];   last= desc.length-2; //  (-2)  1- este ca sa imi ajunga la 0 si inca 1 pentru ca pune un elem in plus , nu stiu de ce
        EDname  = desc[last];


        if(EDtype=='EDeditor' || EDtype=='EDpic') EDvalue = $.trim($(this).html());
        else  EDvalue = $.trim($(this).text());



      //  alert('EDname '+EDtype+'  EDname '+EDname+' value '+EDvalue);
        REPLACE(EDtype, EDname, EDvalue,formSelector);
    });


}


//_______________________________________[ EDsel ] _____________________________________________________________________
function getHTMLoptions(selected)                  {
    i = 0; HTMLopt='';
    while(this.options[i])
    {
        if(selected == this.options[i])  HTMLopt +="<option selected>"+this.options[i]+"</option>\n";
        else HTMLopt +="<option>"+this.options[i]+"</option>\n";
        i++;
    }
    /*alert(HTMLopt);*/
    return HTMLopt;
}
function SELoptions(optionsRO,optionsEN)           {

    switch(LG)
    {
        case 'ro': this.options = optionsRO; break;
        case 'en': this.options = optionsEN; break;
    }
    this.getHTMLoptions = getHTMLoptions;


}  //USE: EDname = new SELoptions(Array_ro,Array_en);

function REPLACE(EDtype, EDname, EDvalue,formSelector) {
    //alert(EDtype+' '+EDname+" "+EDvalue+" "+formSelector);

    /*LG='ro';*/
    EDtag        = formSelector + ' *[class^='+EDtype+'][class$='+EDname+']';
    INPUTname    = EDname+"_"+LG;
    INPUTclass   = 'EDITOR '+EDname;
    EDtag_height = $(EDtag).height()+'px';
    EDtag_width  = $(EDtag).width();


    //____________________________________________________________________________________________________________________________________




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
                    IDpr = $('input[name=IDpr]').val();
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
                    if(eval('typeof '+EDname+'!= "undefined" ')) options = eval(EDname+'.getHTMLoptions("'+EDvalue+'")');
                    else alert('nu l-a recunoscut ca obiect');
                    return "<select name='"+INPUTname+"' class='"+INPUTclass+"'>"+options+"</select>";
                    }
};


    //alert(typeof EDtags[EDtype]);
    if( eval('typeof ' +EDreplace[EDtype]) == 'function' )
    {

        EDtag_replacement =EDreplace[EDtype]();

        $(EDtag).replaceWith(EDtag_replacement);

         /*=======================================[ callback functions ]==============================================*/
         var EDcallback = {
             EDeditor : function(){
                        toolbar_conf = 'smallTOOL';
                        toolbar_conf = (EDtag_width < 500 ? 'EXTRAsmallTOOL' : 'smallTOOL' );

                        CKEDITOR.replace( 'editor_'+EDname+'_'+LG,
                                              {
                                                 /* toolbar : toolbar_conf,*/
                                                  height : EDtag_height
                                                //,width : EDtag_width
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
        //==============================================================================================================

        if(eval('typeof ' +EDcallback[EDtype]) == 'function')
        EDcallback[EDtype]();

    }
    else
        alert('EDITmode nu s-a gasit functie pentru EDtype '+EDtype+'\n EDname = '+EDname);
}


function split( val )        {    return val.split( /,\s*/ ); }
function extractLast( term ) {    return split( term ).pop(); }



