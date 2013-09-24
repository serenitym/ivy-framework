<?php
/**
 * Authentication Manager class file.
 *
 * PHP Version 5.3+
 *
 * @category  Accounts
 * @package   Auth
 * @author    Victor Nițu <victor@serenitymedia.ro>
 * @copyright 2010 Serenity Media
 * @license   http://www.gnu.org/licenses/agpl-3.0.txt AGPLv3
 * @link      http://docs.serenitymedia.ro
 */

/**
 * CauthManager
 *
 * Authentication Manager class, handles login requests
 *
 * This class can be used to process the login form(s).
 *
 * Note that any form designed to use this class for processing, **must**
 * send the class name in a hidden field, as follows:
 *
 *      <input type="hidden" name="login" value="CauthManager" />
 *
 * @category  Accounts
 * @package   Auth
 * @author    Victor Nițu <victor@serenitymedia.ro>
 * @copyright 2010 Serenity Media
 * @license   http://www.gnu.org/licenses/agpl-3.0.txt AGPLv3
 * @link      http://docs.serenitymedia.ro
 */

class CauthManager extends AuthCommon
{

    /**
     * String user for login: can be either a (valid) user name, or a
     * valid email address.
     *
     * The type (name || email) will be further determined by login() method.
     *
     * @var mixed
     * @access protected
     */
    protected $loginName;

    /**
     * The password, not sure why it is still here...
     *
     * @var mixed
     * @access protected
     */
    protected $password;

    /**
     *  Class instance, as indicated by Singleton design pattern.
     */
    protected static $instance;

    /**
     * Return class instance or creates a new one.
     *
     * Implements Singleton design pattern.
     * Initially, this was designed a a trait, which was removed to ensure
     * compatibility with version 5.3
     *
     * @static
     * @final
     * @access public
     * @return void
     */
    final public static function getInstance()
    {
        return isset(static::$instance)
            ? static::$instance
            : static::$instance = new static(func_get_args());
    }

    /**
     * Class constructor, restricted access to enforce Singleton pattern
     *
     * @final
     * @access private
     * @return void
     */
    final private function __construct()
    {
        // {{{ Dead code
        /*
         * This is a neat, not appropriate, object creation via ReflectionClass,
         * when the number of parameters is uncertain.
         */
        // $reflection = new ReflectionClass(__CLASS__);
        // return $reflection->newInstanceArgs(func_get_args());

        /*
         * This was the old post-construct callback
         */
        //$this->init(func_get_args()); # }}}

        //array_push(func_get_args(), $C);
        //print "construct: ";
        //var_dump(func_get_args());

        // Hack needed - double usage of func_get_args() increases array's depth by 1
        $args = func_get_args();
        call_user_func_array(array(__CLASS__, "init"), $args[0]);

    }

    /**
     * Private __wakeup to enforce Singleton pattern.
     *
     * @final
     * @access private
     * @return void
     */
    final private function __wakeup()
    {
    }

    /**
     * Private __clone to enforce Singleton pattern.
     *
     * @final
     * @access private
     * @return void
     */
    final private function __clone()
    {
    }

    /**
     * Enable a class instance to seamlessly return the loginName property
     * when echoed.
     *
     * @access public
     * @return void
     */
    public function __tostring()
    {
        return $this->loginName;
    }

    /**
     * Generalize the string cleanup process.
     *
     * @param mixed $var String to be sanitized
     *
     * @access protected
     * @return void
     */
    protected function sanitize ($var)
    {
        return $this->rodb->real_escape_string($var);
    }

    /**
     * Acquire a bunch of details about one user from the database.
     *
     * This method attempts to get some user properties from the database, as
     * follows:
     * * users: uid, name, active, cid, password, email
     * * user_details: language, country, city, last_ip
     * * user_stats: failed_logins
     * * classes: name
     *
     * @param string $loginName Login name can be either a name or email address
     * @param bool   $type      Clear specification for type (email or username)
     *
     * @access protected
     * @return void
     */
    protected function getAllLoginDetails($loginName='', $type='email')
    {
        $loginQ = ( $type == 'email'
            ? "auth_users.email = '$loginName'"
            :  "auth_users.name = '$loginName'");
        $query = "SELECT auth_users.uid, auth_users.name AS uname, auth_users.active,
                         auth_users.cid,
                         auth_users.password, auth_users.email,
                         auth_user_details.language,
                         auth_user_details.country, auth_user_details.city,
                         auth_user_details.last_ip,
                         FROM_UNIXTIME(auth_user_details.creation, '%Y %D %M')
                            AS joindate,
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
     *
     * Return the following set of data:
     * * users: uid, name, active, cid, password, token, email,
     * * user_stats: permissions
     * * classes: name (as uclass)
     *
     * @param string $loginName Either a valid email address or user name
     *
     * @return mixed
     */
    protected function Get_loginDetails($loginName)
    {

        $loginQ = filter_var($loginName, FILTER_VALIDATE_EMAIL) != false
                ? "auth_users.email = '$loginName'"
                : "auth_users.name = '$loginName'";


        $query = "SELECT auth_users.uid,
                         auth_users.name AS uname,
                         auth_users.active,
                         auth_users.cid,
                         auth_users.password,
                         auth_users.token,
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

    /**
     * Attempt to validate a user's login - DEPRECATED!
     *
     * Deprecated, uses clear text passwords and trigger_error()
     *
     * @param string $loginName The name used for login
     * @param string $password  Clear text (!) password from POST data
     *
     * @static
     * @access public
     * @return void
     */
    public function authCheck($loginName='', $password='')
    {
        $this->rodb = new mysqli(DB_HOST, DB_RO_USER, DB_RO_PASS, DB_NAME);
        $this->rodb->set_charset("utf8");

        //echo "Setting login vars & Sanitizing login...";
        $this->loginName = $this->sanitize($loginName);
        $this->password  = $this->sanitize($password);


        if (strlen($loginName) < 1 && strlen($password) < 1) {
            return false;
        }

        $resUserData    = $this->Get_loginDetails($loginName);
        $this->userData = $resUserData->fetch_object();

        if ($this->userData->active != 1) {
            trigger_error("Inactive $loginName tried to log in", E_USER_NOTICE);
            return false;
        } elseif (md5($password) !== $this->userData->password) {
            trigger_error("Wrong password for $loginName", E_USER_NOTICE);
            return false;
        }
        return true;

    }

    /**
     * login
     *
     * @access protected
     * @return void
     */
    protected function login()
    {
        // true / false - autentificat sau nu
        $authStatus = $this->authCheck($_POST['loginName'], $_POST['password']);

        if ($authStatus) {

            $_SESSION['userData'] = $this->userData;
            // daca userul este autentificat
            $_SESSION['auth']     = true;

            // --------[ set session cookie ]-------
            //sessionManager::setSessionCookie($this->userData, 3600);
            //sessionManager::sessionToSQL(3600);
        } else {
            // Return 0, this means the check returned a Guest account
            unset($_SESSION['auth']);
            unset($_SESSION['userData']);
        }
        //Toolbox::clearSubmit();

        isset($_SESSION['auth']) || Toolbox::relocate('/');

        if (isset($_SESSION['postLoginURL'])) {
            $url = $_SESSION['postLoginURL'];
            unset($_SESSION['postLoginURL']);
            Toolbox::relocate($url);
        }
    }

    /**
     * logout
     *
     * @access protected
     * @return void
     */
    protected function logout()
    {
        //sessionManager::destroySession();
        //sessionManager::unsetCookies();
        global $session;
        $session->stop();
        Toolbox::relocate('/');
    }

    /**
     * init
     *
     * @access protected
     * @return void
     */
    protected function init ()
    {
        if (isset($_POST['login']) && $_POST['login'] == __CLASS__) {
            $this->login();
        } elseif (isset($_GET['logOUT'])) {
            $this->logout();
        }

    }
}
