<?php

/** {{{ Documentation
 * CauthManager
 *
 * PHP Version 5.4
 *
 * @category  Accounts
 * @package   Auth
 * @author    Victor Nițu <victor@serenitymedia.ro>
 * @copyright 2010 Serenity Media
 * @license   http://www.gnu.org/licenses/agpl-3.0.txt AGPLv3
 * @version   0.1.2
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

    /* {{{ private sanitizeLogin() */
    protected function sanitize ($var) {
        return $this->rodb->real_escape_string($var);
    }
    /* }}} */

    protected function getAllLoginDetails($loginName='', $type='email') {
        //TODO: docblock
        $loginQ = ( $type == 'email'
            ? "auth_users.email = '$loginName'"
            :  "auth_users.name = '$loginName'");
        $query = "SELECT auth_users.uid, auth_users.name AS uname, auth_users.active,
                         auth_users.cid,
                         auth_users.password, auth_users.email,
                         auth_user_details.language,
                         auth_user_details.country, auth_user_details.city,
                         auth_user_details.last_ip,
                         FROM_UNIXTIME(auth_user_details.creation, '%Y %D %M') AS joindate,
                         auth_user_details.first_name, auth_user_details.last_name,
                         auth_user_details.last_ip, auth_user_stats.failed_logins,
                         LOWER(auth_classes.name) AS uclass
                    FROM auth_users
                    JOIN auth_user_details
                        ON (auth_users.uid = auth_user_details.uid)
                    LEFT JOIN auth_user_stats
                        ON (auth_users.uid = auth_user_stats.uid)
                    LEFT JOIN auth_classes
                        ON (auth_users.cid = auth_classes.cid)
                    WHERE $loginQ;";

        $result = $this->rodb->query($query)
                    or die('Query failed: ' . $this->rodb->error);
        return $result;


    }

    /**
     * Get basic data for user
     * @param $loginName
     *
     * @return mixed
     */
    //old: getLoginDetails
    protected function Get_loginDetails($loginName) {

        $loginQ = filter_var($loginName, FILTER_VALIDATE_EMAIL) != false
                ? "auth_users.email = '$loginName'"
                : "auth_users.name = '$loginName'";


        $query = "SELECT auth_users.uid,
                         auth_users.name AS uname,
                         auth_users.active,
                         auth_users.cid,
                         auth_users.password,
                         auth_users.email,

                         auth_user_stats.permissions,

                         auth_classes.name AS uclass
                    FROM auth_users
                    JOIN auth_classes
                         ON (auth_users.cid = auth_classes.cid)
                    LEFT OUTER JOIN auth_user_stats
                        ON (auth_users.uid = auth_user_stats.uid)

                    WHERE $loginQ ";

                 /*" JOIN auth_user_details
                        ON (auth_users.uid = auth_user_details.uid)
                    LEFT JOIN auth_classes
                        ON (auth_users.cid = auth_classes.cid)
                    ";*/

        $result = $this->rodb->query($query)
                    or die('Query failed: ' . $this->rodb->error);

        return $result;
    }

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
    public function authCheck($loginName='', $password='')
    {
        $this->rodb = new mysqli(DB_HOST,DB_RO_USER,DB_RO_PASS,DB_NAME);
        $this->rodb->set_charset("utf8");

        //echo "Setting login vars & Sanitizing login...";
        $this->loginName = $this->sanitize($loginName);
        $this->password  = $this->sanitize($password);


        if (strlen($loginName) < 1 && strlen($password) < 1) {
            return false;
        }

        $resUserData    = $this->Get_loginDetails($loginName);
        $this->userData = $resUserData->fetch_object();

        if ($password !== $this->userData->password) {
            return false;
        }
        return true;

    }
    /* }}} */

    /* {{{ protected init  */
    protected function init () {


        if (isset($_POST['login']) && $_POST['login'] == __CLASS__) {

            // true / false - autentificat sau nu
            $authStatus = $this->authCheck( $_POST['loginName'], $_POST['password']);

            if ($authStatus) {

                $_SESSION['userData'] = $this->userData;
                // daca userul este autentificat
                $_SESSION['auth']     = true;

                // --------[ set session cookie ]-------
                sessionManager::setSessionCookie($this->userData, 3600);
                sessionManager::sessionToSQL(3600);
            } else {
                // Return 0, this means the check returned a Guest account
                unset($_SESSION['auth']);
                unset($_SESSION['userData']);
            }
            //Toolbox::clearSubmit();
        }

    }
    /* }}} */
}
