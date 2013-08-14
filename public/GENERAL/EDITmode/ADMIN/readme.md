#Template implementation
------------------------------

```html
< * class='allENTS [otherClasses] [entSName]' id = '[entSName]_[LG]' >

      <class='ENT [otherClasses] [entName]' id = '[entName]_[id]_[LG]' >
</*>

<div class='addTOOLSbtn'>
      <input type='button' class='ATmpl' value='more settings' onclick='fmw.toggle(\"form[id^=EDITform] .admin-extras\"); return false;' />
</div>

 < * class='SING [otherClasses] [singName]' id = '[singName]_[id]_[LG]' >
 </*>
```

addTOOLSbtn -  adaugarea de butoane noi

deci un element editabil are din templateul creat urmatoarele proprietati:

+ el.otherClasses
+ el.name
+ el.id
+ el.lang



#Config EDITmode
 -------------------------------

 Pe baza impementarii in template se pot declara cofiguri pentru EDITmode
 configurile ar trebui scrise in public / moduleName / js / *.js


```javaScript
 ivyMods.set_iEdit.sampleMod = function(){

     iEdit.add_bttsConf(
         {
             'ENTname':
             {
                 modName: 'sampleModule'
                 ,edit: {}
                 ,saveBt:  {methName: 'sampleModule->updateMethName'}
                 ,deleteBt: {status:false, methName: 'sampleModule->deleteMethName'}
                 ,addBt: {
                     atrValue: 'un nume',
                     style : 'oric',
                     class : '',
                     status: '',
                     methName: '',
                     async : new fmw.asyncConf({})
                     }

                   // butoane extra pentru toolbarul elementului editabil
                 ,extraButtons:
                 {
                       manageGroup: {
                           callBack: "ivyMods.team.showManageGroups();",
                           attrValue: 'manage Groups',
                           attrName: 'manage Groups',
                           attrType: 'submit/ button',
                           class: ''
                       },
                       buttonName:{}
                 }
                 , extraHtml : ['htmlConetnt ',
                               "<span>" +
                                   "<input type='hidden' name='action_modName' value='user' >" +
                                   "<input type='hidden' name='action_methName' value='deactivateProfile [, other methods]' >" +
                                   "<input type='submit' name='deactivate' value='deactivate' >" +
                               "</span>",
                               '']


             },
             allENTSName : {
                 extraButtons: {}

             },
             'SINGname':
             {
                 // pentru mai multe despre setarea butoanelor in EDITmode see EDITmode.js -> var bttConf
             }
         });
 };
```
**element editabil**

+ elementele editabile au o clasa care incepe cu  ENT sau SING
+ ENT-urile fac parte dintr-un grup de ENT-uri care are o clasa ce incepe cu allENTS
si encapsuleaza toate celelalte elemente ENT
+ SING este un element singular care poate fi editabil
+ pentru orice element editabil pot fi configurate urmatoarele :

    + un module (core->modName) care sa fie managerul pentru inputurile create
    + butoanele default
    + butoanele extra (extraButtons)
    + butoanele extra html (extraHtml)

------------------------

**butoanele default:**

+ addBt
+ saveBt
+ deleteBt
+ edit

**proprietati butoane default:**

+ status : true / false -

    *daca sa fie sau nu afisat in toolbar*
+ methName

    *numele metodei apelate la apasarea acetui buton*
+ atrValue

    *atributul value al butonul*
+ atrName

    *atributul name al butonul*
+ async

    *not yet documented*
+ class

    *not sure if it still works*


**butoane extra (extraButtons)**

+ numele butonului
+ configul acestuia

**butoane extra - config**

butoanele extra pot avea urmatoarele proprietati de config :

+ callBack

    *functie de callback dupa apasarea butonului*
+ attrValue

    *atributul value al butonul*
+ attrName

    *atributul name al butonul*
+ attrType

    *'submit/ button' - tipul de buton *
+ class

    *clasa butonului*



**butoane extra html**

butoane puse efectiv cum sunt scrise (ar trebui totusi encapsulate cu un <span>).
aceste butoane pot avea si alte hiddenturi etc, sunt defapt un block html

din exemplu:

```
"<span>" +
   "<input type='hidden' name='action_modName' value='user' >" +
   "<input type='hidden' name='action_methName' value='deactivateProfile [, other methods]' >" +
   "<input type='submit' name='deactivate' value='deactivate' >" +
"</span>"
```

dupa cum se observa pot exista *action_modname* & *action_methName* care specifica
ce modul va fi managerul si ce metoda a lui



#Convert
------------------------------------------------------


dupa citirea configurilor un element editabil se va transforma astfel

**detaliile - initiale elementului (elD)**

+ elD.otherClasses
+ elD.name
+ elD.id
+ elD.lang

**detaliile - + elementului (elD)**

+ elD.elmContent

    *continutul elementului*


**configurile elementului (BTT)**


```
   "<form action='' method='post' class='"+elD.otherClasses + elD.name +"' id='EDITform_"+elD.id+"' >" +
      "<input type='hidden' name='BLOCK_id' value='"+elD.id+"' />" +
      "<input type='hidden' name='modName' value='"+BTT.modName+"' />" +
      "<input type='hidden' name='methName' value='' />" +

      "<div class='TOOLSem'>" +
           "<div class='TOOLSbtn'>" +
                  //    EXTRAS_TAG +
                  elD.EXTRA_tags +
                  elD.SAVE_tag +
                  elD.DELETE_tag+
                 "<span>" +
                 "   <input type='button'  class='editModeBTT editM-exit' " +
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
```
