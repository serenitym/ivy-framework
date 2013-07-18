<?php
class User{

    use Singleton;
    use tReadOnlyDB;

    var $uid = 0;
    var $uname = 'Guest';

    # sa zicem ca ar putea fi low, editor, masterEditor, etc
    var $uclass = 'webmaster';  # trebuie sa fi editorul articolului ca sa il poti edita
    #var $uclass = 'masterEditor'; # sa zicem ca ar putea fii low, editor, masterEditor, etc
    function getRecordPermss(&$mod, $uidRec){
        #echo "Incerc sa iau permisiunile pentru record";
        if($this->uclass=='webmaster'){
            $mod->editRecordPermss = true;
            $mod->pubPermss = true;
            $mod->webmPermss = true;
        }
        elseif($this->uid == $uidRec){


             $mod->editRecordPermss = true;
             if($this->uclass=='publisher')
                 $mod->pubPermss = true;
            # declarate deja ca default in $mod;
            # $mod->pubPermss = ($this->uclass=='publisher' ? true : false);
        }
        else{
            $mod->ED = 'not';
            # declarate deja ca default in $mod;
            #$mod->editRecordPermss = false;
            #$mod->pubPermss = false;
        }


        # adica daca userul este acelasi de userul care a edita articolul
        # sau daca are permisiuni de masterEditor
        # atunci poate edita articolul
    }

        /**
         * Daca este un user logat
         *      - daca are permisiuni de master poate edita
         *      - daca nu are permisiuni
         *              - si este autorul recordului  - poate edita
         *
         */
    function get_EDrecord(&$mod, $uidRec){

        if(isset($this->admin) && $this->admin != NULL){
            if($mod->editRecords_Permss)   return '';
            elseif($uidRec == $this->uid)  return '';
            else return 'not';

        }

        return 'not';

    }

    function getRecordsPermss(&$mod=''){

        # daca este pe profilul personal
        # daca este masterEditor, admin ceva...
        # deocamdata toate se pot edita si sterge
        # variabila EDrecord este cea de care trebuie avut grija
        #$editRecords

        #daca i se transmite un pointer la un obiect va seta si o variabila
        # daca nu va da return doar true sau false

        if($this->uclass=='webmaster'){
            if($mod!='' && is_object($mod))
                 $mod->editRecords_Permss = true;
            return true;
        }
        else
            return false;



    }
    function getCommentsPermss($uidRec){

        if($this->uid == $uidRec) return true;
        elseif($this->uclass=='webmaster') return true;
        else
            return false;

    }
    function checkOwn($uidRec){
         if($this->uid == $uidRec) return true;
    }


    public function get_avatar( $email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array() ) {
        if (!defined('AVATAR') || AVATAR == FALSE) return 0;
        $url = 'http://cdn.libravatar.org/avatar/40f8d096a3777232204cb3f796c577b7?s=80&amp;d=mm';
        $url2  = 'http://cdn.libravatar.org/avatar/';
        $url2 .= md5( strtolower( trim( $email ) ) );
        $url2 .= "?s=$s&amp;d=$d&amp;r=$r";

        if(Toolbox::http_response_code($url2) != '404') {
            $url = $url2;
            unset($url2);
        }

        if ( $img ) {
            $url = '<img src="' . $url . '"';
            foreach ( $atts as $key => $val )
            $url .= ' ' . $key . '="' . $val . '"';
            $url .= ' />';
        }
    return $url;
    }

    private function getUserData($uid) {
        $detailsTable   = 'auth_user_details';
        $statsTable     = 'auth_user_stats';
        $detailsColumns = array('first_name','last_name','language','country','city','last_ip','creation','last_login');
        $statsColumns   = array('age','failed_logins','comments_count','articles_count','warn_count');

        $detailsQuery = "SELECT ";
        $statsQuery   = "SELECT ";

        foreach ($detailsColumns as $column) { $detailsQuery .= "$column, "; }
        foreach ($statsColumns   as $column) { $statsQuery .= "$column, "; }

        $detailsColumns .= " FROM $detailsTable WHERE uid = '$uid'";
        $statsColumns   .= " FROM $statsTable WHERE uid = '$uid'";

        $this->details = $this->DB->query($detailsQuery)->fetch_object();
        $this->stats = $this->DB->query($statsQuery)->fetch_object();
    }

    private function getUserGroups ($uid, $cid = 0) {
        $query = "SELECT group_concat(g) AS groups FROM (
                   SELECT auth_map_users_groups.gid AS g
                    FROM auth_map_users_groups
                    JOIN auth_groups ON (auth_groups.gid = auth_map_users_groups.gid)
                    WHERE auth_map_users_groups.uid = '" . $this->uid . "'
                  UNION
                   sELECT auth_map_classes_groups.gid AS g
                    FROM auth_map_classes_groups
                    JOIN auth_groups ON (auth_map_classes_groups.gid = auth_groups.gid)
                    WHERE auth_map_classes_groups.cid = '" . $this->cid . "'
                  ) AS T;";
        $groups = $this->rodb->query($query);
        $this->groupsCSV = $groups->fetch_row()[0];
        $this->groups = explode(',', $this->groupsCSV);
    }

    private function getGroupsString () {
        //return substr(implode(', ', $this->groups),0,-2);
    }


    public function init($uid = null)
    {
        $this->readOnlyConnect();

        $params = print_r($uid, true);

        //trigger_error('Debug break!', E_USER_ERROR);
        if (isset($_SESSION['auth']) && is_object($_SESSION['auth'])) {
            $auth         = &$_SESSION['auth'];
            $this->uid    = $auth->uid;
            $this->cid    = $auth->cid ?: 0;
            $this->uname  = isset($uid) ?
                                $auth->first_name . ' ' . $auth->last_name
                                : 'Guest user';
            $this->email  = $auth->email;
            $this->getUserGroups($uid, $this->cid);
        } else {
            return 0;
        }
    }

}
