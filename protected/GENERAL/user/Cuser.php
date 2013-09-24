<?php
/**
 * User class file
 *
 * PHP Version 5.3
 *
 * @category  Accounts
 * @package   User
 * @author    Victor Nițu <victor@serenitymedia.ro>
 * @copyright 2010 Serenity Media
 * @license   http://www.gnu.org/licenses/agpl-3.0.txt AGPLv3
 * @link      http://docs.serenitymedia.ro
 */

/**
 * Class Cuser
 *
 * Note: Guest user is logged in by default, and it will always be, unless
 * someone else logs in.
 *
 * @category  Accounts
 * @package   User
 * @author    Victor Nițu <victor@serenitymedia.ro>
 * @copyright 2010 Serenity Media
 * @license   http://www.gnu.org/licenses/agpl-3.0.txt AGPLv3
 * @link      http://docs.serenitymedia.ro
 */
class Cuser extends Permissions
{

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
    public $rights;                  // pointer to permissions

    //public $classes = array();
    public $sets = array();


    /**
     * getAvatar
     *
     * @param mixed  $email Email to get the avatar for
     * @param int    $s     Image dimensions (square's width)
     * @param string $d     Unit, probably (?)
     * @param string $r     No idea, ask the Gravatar guys
     * @param bool   $img   Return an image? If false, get a link only
     * @param bool   $atts  Some kind of data array, I guess
     *
     * @access public
     * @return void
     */
    public function getAvatar( $email, $s = 80, $d = 'mm', $r = 'g',
        $img = false, $atts = array()
    ) {

        if (!defined('AVATAR') || AVATAR == false) {
            return 0;
        }

        $url = 'http://cdn.libravatar.org/avatar/'
              .'40f8d096a3777232204cb3f796c577b7?s=80&amp;d=mm';
        $url2  = 'http://cdn.libravatar.org/avatar/';
        $url2 .= md5(strtolower(trim($email)));
        $url2 .= "?s=$s&amp;d=$d&amp;r=$r";

        if (Toolbox::http_response_code($url2) != '404') {
            $url = $url2;
            unset($url2);
        }

        if ( $img ) {
            $url = '<img src="' . $url . '"';
            foreach ( $atts as $key => $val ) {
                $url .= ' ' . $key . '="' . $val . '"';
            }
            $url .= ' />';
        }
        return $url;
    }

    /**
     * Sets the user's class from database
     *
     * @access protected
     * @return void
     */
    protected function Set_class()
    {
        $query = "SELECT cid, name AS uclass FROM auth_classes";
        $this->classes = $this->C->Db_Get_rows($query);
    }

    /**
     * Reads all the needed user data from the database.
     *
     * @todo Outsource table names (module config or whatever)
     *
     * @param mixed $uid User id
     *
     * @access private
     * @return void
     */
    private function getUserData($uid)
    {
        $detailsTable   = 'auth_user_details';
        $statsTable     = 'auth_user_stats';
        $detailsColumns = array('first_name', 'last_name', 'language',
                                'country', 'city', 'last_ip', 'creation',
                                'last_login');
        $statsColumns   = array('age', 'failed_logins', 'comments_count',
                                'articles_count', 'warn_count');

        $detailsQuery = $statsQuery = "SELECT ";

        foreach ($detailsColumns as $column) {
            $detailsQuery .= "$column, ";
        }

        foreach ($statsColumns   as $column) {
            $statsQuery .= "$column, ";
        }

        $detailsColumns .= " FROM $detailsTable WHERE uid = '$uid'";
        $statsColumns   .= " FROM $statsTable WHERE uid = '$uid'";

        $this->details = $this->DB->query($detailsQuery)->fetch_object();
        $this->stats = $this->DB->query($statsQuery)->fetch_object();
    }


    /**
     * Creates an authenticated user
     *
     * @access public
     * @return void
     */
    public function _init_authUser()
    {
        // dont realy know why it doesn't work
        /* foreach ($_SESSION['auth'] AS $dbFields => $dbValue) {
            $this->$dbFields = $dbValue;
        }*/


        // echo "<b>Cuser _init_</b>";
        // var_dump($_SESSION['userData']);
        $userData     = &$_SESSION['userData'];
        $this->uid    = $userData->uid;
        $this->cid    = $userData->cid ? $userData->cid  : 0;
        $this->uname  = $userData->uname;
        $this->email  = $userData->email;
        $this->uclass = $userData->uclass;
        // $this->first_name  = $userData->first_name;
        // $this->last_name  = $userData->last_name;

        $_COOKIE['token'] = $this->Db_getToken($this->uid);

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

        //buttoane pe toolbar
        // echo "Cuser - _init_authUser : toolbar buttons";
        $this->toolbarBtts();
    }

    /**
     * Class magic init method
     *
     * @access public
     * @return void
     */
    public function _init_()
    {
        //$uid = $_SESSION['userData']->uid;

        //trigger_error('Debug break!', E_USER_ERROR);
        $this->_init_second();

        if (!isset($_SESSION['auth'])) {
            //echo "Cuser - _init_second: Delogat user <br>";
            return ;

        } else {
            //echo "Cuser - _init_second: <b>Prima initiere</b> <br>";
            $this->_init_authUser();
            unset($this->C);
            unset($this->tree);
            unset($this->DB);
            $_SESSION['user'] = serialize($this);
        }

        //@todo: $_SESSION['userData'] poate ar trebui facut unset si la el
        // unset l apermisions

        //echo "<br> Cuser - _init_ :";
        //var_dump($this);
    }


    //====================================================

    /**
     * addGEN_edit - Not yet documented
     *
     * @access public
     * @return void
     */
    public function addGEN_edit()
    {
        //TODO: Document this!

        if ($this->rights['perm_manage']) {
            array_push($this->C->defaultAdmin_GENERAL, 'GEN_edit');
            // echo "Cuser - addGEN_edit ";
            //var_dump($this->C->defaultAdmin_GENERAL);
        }
    }

    /**
     * Adds account buttons on the main user toolbar.
     *
     * @access public
     * @return void
     */
    public function addToolbarAccount()
    {
        $buttons = $this->C->Render_objectFromPath(
            $this, "GENERAL/user/tmpl/account.html"
        );

        array_push($this->toolbarBtts, $buttons);
    }

    /**
     * Add logout button on the main user toolbar.
     *
     * @access public
     * @return void
     */
    public function addToolbarLogout()
    {
        array_push(
            $this->toolbarBtts,
            "<a href='index.php?logOUT=1' id='logOUT'>
                Log out {$this->uname}
                [ id: {$this->uid} | class: {$this->uclass} ]
            </a>"
        );

    }

    /**
     * Adds all the needed toolbar buttons.
     *
     * @access public
     * @return void
     */
    public function toolbarBtts()
    {
        $this->toolbarBtts = array();
        if (isset($this->C->TOOLbar)) {
            $this->toolbarBtts = &$this->C->TOOLbar->buttons;
        } else {

            $this->toolbarBtts = &$this->C->toolbarBtts;
        }
        $this->addGEN_edit();
        $this->addToolbarLogout();
        $this->addToolbarAccount();

    }

    /**
     * Secondary init function, a bit of a hackish way to separate init logic.
     *
     * @access public
     * @return void
     */
    public function _init_second()
    {

        if (isset($_GET['login'])) {
            isset($_SESSION['auth']) && Toolbox::relocate('/');

            $_SESSION['postLoginURL'] = $_SESSION['lastURL'];
            $this->C->jsTalk .= "ivyMods.user.popup('"
                . FW_PUB_URL . "', 'loginForm' , 'Login'); ";
            return true;
        }

        if (isset($_GET['route']) && $_GET['route'] == 'invite') {
            $this->C->jsTalk .= "ivyMods.user.popup('"
                . FW_PUB_URL
                . "', 'inviteConfirm' , 'Register account (invitation)'); ";
            return true;
        }

        if (isset($_GET['route']) && $_GET['route'] == 'recoverPassword') {
            isset($_SESSION['auth']) && Toolbox::relocate('/');

            if ($this->Db_getToken(
                intval($_GET['id']), strval($_GET['token'])
            ) == true) {
                $this->C->jsTalk .= "ivyMods.user.popup('"
                    . FW_PUB_URL . "', 'recoverPassword' , 'Reset password'); ";
                return true;
            } else {
                $this->C->jsTalk .= "alert('Invalid request.');";
                return false;
            }
        }

    }

    /**
     * Called when session is being rebuilt, also needs to
     * rebuild some core pointers and properties
     *
     * @param mixed $core Pointer to core
     *
     * @access public
     * @return void
     */
    public function afterInit(&$core)
    {
        /**
         * ADD GEN_edit to c or not
         * add profile buttons
         *  - delete account
         *  - change password
         *  - ivite
         */
        $core->Module_configCorePointers($this);
        $core->Module_Set_incFilesJsCss($this);
        $this->_init_second();
        $this->toolbarBtts();


    }

    /**
     * __destruct clears the token cookie
     *
     * @access public
     * @return void
     */
    public function __destruct()
    {
        $_COOKIE['token'] = null;
    }
}
