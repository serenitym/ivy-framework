<?php

/* {{{ Documentation */
/**
 * CauthManager
 *
 * @uses authCommon
 * @package Auth
 * @version 0.1.2
 * @copyright Copyright (c) 2010 Serenity Media
 * @author  Victor Nițu <victor@serenitymedia.ro>
 * @license http://www.gnu.org/licenses/agpl-3.0.txt AGPLv3
 */
/* }}} */

class CauthManager extends authCommon implements Serializable {

    use Singleton;

    protected $loginName;
    protected $password;


    /* {{{ public _render_() */
    /**
     * _render_
     *
     * @access public
     * @return void
     */
    public function _render_() {
        $display = '
          <form class="form-horizontal pull-right" action="" method="post">
            <div class="control-group">
              <div class="controls">
                <input class="loginInput input-small" name="loginName" type="text" id="inputEmail" placeholder="Email" />
                <input class="loginInput input-small" name="password" type="password" id="inputPassword" placeholder="Password" />
                <input name="login" type="hidden" value="CauthManager" />
                <input type="submit" class="btn btn-mini topbarBtn" value="Sign in" />
              </div>
            </div>
          </form>
        ';
        return $display;
    }
    /* }}} */

    /* {{{ protected init  */
    /**
     * init
     *
     * @param mixed $C
     * @access public
     * @return void
     */
    protected function init () {
        $this->rodb = new mysqli(DB_HOST,DB_RO_USER,DB_RO_PASS,DB_NAME);
        $this->rodb->set_charset("utf8");

        if(isset($_POST['login']) && $_POST['login'] == __CLASS__) {
            //
            //echo "Setting login vars...";
            $this->loginName = $_POST['loginName'];
            $this->password  = $_POST['password'];
            //
            //echo "done!<br/> \n Sanitizing login... ";
            $this->sanitize('loginName');
            $this->sanitize('password');
            //
            //echo "done!<br/> \n Authenticating user... ";
            $this->authCheck($this->loginName,$this->password);
            //
            // --------[ set session cookie ]-------
            sessionManager::setSessionCookie($this->userData,3600);
            sessionManager::sessionToSQL(3600);

            //Toolbox::clearSubmit();
        }
        else {
            //die('AHA!!');
            $this->user = User::getInstance($this->userData->uid);
            //var_dump($this->userData);
            //var_dump($_SESSION);
            //echo 'nologin';
        }
    }
    /* }}} */

    /* {{{ public authCheck($loginName='', $password='')  */
    /**
     * authCheck
     *
     * @param string $loginName
     * @param string $password
     * @static
     * @access public
     * @return void
     */
    public function authCheck($loginName='', $password='') {
        if(strlen($loginName) > 0 && strlen($password) > 0) {
            //echo "<b>Login attempt: user <i>$loginName</i> (using password: <i>$password</i>)</b>";
            if(filter_var($loginName,FILTER_VALIDATE_EMAIL) != false)
                $this->getLoginDetails($loginName);
            else
                $this->getLoginDetails($loginName,'username');

            if($password === $this->userData->password) {
                // This is where Cuser should be instantiated WITH uid as param.
                //die('Password match!');
                //xdebug_start_trace(BASE_PATH.'trace.txt');
                $this->user = User::getInstance($this->userData->uid);
                $_SESSION['auth']=$this->userData;
                //xdebug_stop_trace();
            }

        }
        else {
            // Return 0, this means the check returned a Guest account
            unset($_SESSION['auth']);
            return 0;
        }
    }
    /* }}} */

    /* {{{ __tostring */
    public function __tostring() {
        return $this->loginName;
    }
    /* }}} */

    /* {{{ public function serialize() */
    public function serialize() {
        return serialize(get_object_vars($this));
    }
    /* }}} */

    /* {{{ public function unserialize($data) */
    public function unserialize($data) {
        self::getInstance();

        // Set the values
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $this->$k = $v;
            }
        }
    }
    /* }}} */

}
