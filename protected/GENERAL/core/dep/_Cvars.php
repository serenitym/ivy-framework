<?php

/**
 * vars
 *
 * @uses item
 * @package Core
 * @version 1.0
 * @copyright Copyright (c) 2012 Serenity Media
 * @author  Ioana Cristea
 * @license AGPLv3 {@link http://www.gnu.org/licenses/agpl-3.0.txt}
 */
class Cvars extends item
{

    #_[DEFAULTS]______________________________________________________________________________________________

    /**
     * the types to work with can be MODELS OR PLUGINS.
     * - generaly the plugins are used by the models;
     * - every type must have a protected & public part to it
     *
     * MODELS & PLUGINS
     *
     * PROTECTED part - cls. REQ - C[$mod_name].php
     *                - cls. OPT - AC[$mod_name].php               --> used for CMS ADMIN
     *
     * PUBLIC part    - MODELS  - OPT  -  js/anyName.js
     *                                 -  css/anyName.css
     *
     *                - PLUGINS - required  -  js/anyName.js
     *                                      -  css/anyName.css
     *__________________________________________________________________________________________________________________
     *
     *  $models = mod. that will be used;
     *  $plugins
     *__________________________________________________________________________________________________________________
     *
     *   $default_PLUGINS    - the models & plugins that should be integrated at any refresh of the site;
     *   $default_MODELS
     *__________________________________________________________________________________________________________________
     *   $admin_MOD  - array('mod_name'=>true, ... );         -- if a module has a class for ADMIN should be declared here;
     *   $theme_MOD  - array('mod_name'=>'theme_[name]',...); -- if a module or model has other themes than the default ones;
     *
     */

  //  var  $mods            = array('PLUGINS','MODELS','GENERAL');
  //  var  $models          = array('profile','masterBlog','articles','blog','comments','siteMap','contact','products','single','basket','mainPOPup');

  //  var  $plugins         = array('toolbar','MENUhorizontal','MENU_h_multiLevel','rating');
 //   var  $general         = array('TOOLbar','GEN_edit','EDITmode','user','authManager','usrManager');

/*  var  $default_PLUGINS = array('LANG','MENUhorizontal','menuPROD','SEO');
    var  $default_MODELS  = array('basket','mainPOPup');

    var  $menus = array(1=>'MENUhorizontal',2=>'MENUhorizontal',3=>'menuPROD'); */

    var  $default_PLUGINS = array();
    var  $default_MODELS  = array();
    var  $default_GENERAL  = array();
    var  $default_TEMPLATES  = array();
    var  $menus ;#= array();


#=======================================================================================================================

    var  $id =1;                            # curent id   (item id)
    var  $idT=1;                            # id primar (primul nivel al tree-ului,  parent_id (p_id) = 0 ) ;
    var  $idC=1;                            # id curent

/*  var  $type='products';                  # type mod. aferent itemului cu idC;
    var  $type_MOD = 'MODELS';              # module || models*/

    var  $type;                             # type mod. aferent itemului cu idC;
    var  $type_MOD;                         # plugins || models || templates
    var  $template;                         # denumirea templateului

    var  $tree=array();                     # array( idC=>item OBJECT );

#========================================== [ HISTORY ] ================================================================
    var  $history = array();                # id-uri
    var  $history_HREF='';                  # lista de linkuri
    var  $history_TITLE='';                 # aferent tagului <title>
    var  $history_TITLE_keywords='';

#========================================== [ SEO ] ====================================================================




#=======================================================================================================================

    var  $INC_css;                          # string de taguri css / js    - automat create de FMW;
    var  $INC_js;
    var  $TMPL_css;                         #TMPL_css[$type_MOD.'_'.$mod_name]  ? = true  => setari personalizare pt modele sau pluginuri
                                            #
#_______________________________________________________________________________________________________________________
    var  $admin;                            #  true | false    - determinat in LOG.php
    var  $DB;                               #  mysqli object


    var  $lang='en';                        # curent language
    var  $lang2;                            # alternate language
    var  $langs = array('ro');             # NOT IMPLEMENTED
/*
    var  $lang='ro';                        # curent language
    var  $lang2 ='en';                      # alternate language
    var  $langs = array('ro','en');         # NOT IMPLEMENTED*/

    var  $display = '';                     # DEPRECATED '-edit', '_editADD'
    var  $HTML_headerIMG='';                # CONST content




}


