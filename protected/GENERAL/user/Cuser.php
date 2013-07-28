<?php
class Cuser extends permissions{

    // comming from $_SESSION['userData'] & seted by CauthManager
    public $uid = 0;                // user ID
    public $cid = 0;                // class ID | table - auth_classes
    public $uname  = 'Guest';       // user name | table - auth_users.name
    public $email  = 'Guest';
    /**
    * @var string uclass
    *
    * - guest
    * - webmaster - poate edita, publica,deleta...
    * - masterEditor -
    * - publisher - nu poate edita decat articolele la care este autor
    */
    public $uclass = 'guest';
    public $permissions;

    //public $classes = array();
    public $sets = array();


    public function get_avatar( $email, $s = 80, $d = 'mm', $r = 'g',
        $img = false, $atts = array()
    ) {
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

    public function _init_()
    {
        //$uid = $_SESSION['userData']->uid;

        //trigger_error('Debug break!', E_USER_ERROR);
        if (isset($_SESSION['auth'])/* && count($_SESSION['userData']) > 0*/) {

            // dont realy know why it doesn't work
           /* foreach ($_SESSION['auth'] AS $dbFields => $dbValue) {
                $this->$dbFields = $dbValue;
            }*/

            $userData     = &$_SESSION['userData'];
            $this->uid    = $userData->uid;
            $this->cid    = $userData->cid ?: 0;
            $this->uname  = $userData->uname;
            $this->email  = $userData->email;
            $this->uclass = $userData->uclass;
           // $this->first_name  = $userData->first_name;
           // $this->last_name  = $userData->last_name;
            $this->permissions  = $userData->permissions;
            $this->rights =& $this->permissions;

            if (!$this->permissions) {
                $this->_init_permissions();
                error_log("[ ivy ] Cuser - _init_ : Citim permisiunile din bd");
                //echo "<br> Cuser _init_ : permissions from db ";
                //var_dump($this->permissions);

            } else {
                $this->permissions =  unserialize($this->permissions);
                //echo "<br> Cuser _init_ : permissions from serialized ";
                //var_dump($this->permissions);
            }

        } else {
            return 0;
        }

        //@todo: $_SESSION['userData'] poate ar trebui facut unset si la el
        //din moment ce e folosit doar la autentificare

        // unset l apermisions
        unset($this->C);
        unset($this->tree);
        unset($this->DB);
        $_SESSION['user'] = $this;
        //echo "<br> Cuser - _init_ :";
        //var_dump($this);
    }

}
