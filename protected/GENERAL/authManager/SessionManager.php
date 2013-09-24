<?php
/**
 * Session Manager class container
 *
 * PHP Version 5.3
 *
 * @category  Accounts
 * @package   Auth
 * @author    Victor Nițu <victor@serenitymedia.ro>
 * @copyright 2010 Serenity Media
 * @license   http://www.gnu.org/licenses/agpl-3.0.txt AGPLv3
 * @link      http://docs.serenitymedia.ro
 */

/**
 * sessionManager
 * Universal session manager used by authentication controller.
 *
 * @category  Accounts
 * @package   Auth
 * @author    Victor Nițu <victor@serenitymedia.ro>
 * @copyright 2010 Serenity Media
 * @license   http://www.gnu.org/licenses/agpl-3.0.txt AGPLv3
 * @link      http://docs.serenitymedia.ro
 */
class SessionManager extends AuthCommon
{


    /**
     * startSession
     *
     * @param bool $sid Session ID
     *
     * @deprecated
     * @static
     * @access public
     * @return void
     */
    static function startSession($sid = null)
    {
        if ($sid == null) {
            session_start();
        } else {
            session_start($sid);
        }
    }

    /**
     * ajaxResumeSession
     *
     * @static
     * @access public
     * @return void
     */
    static function ajaxResumeSession()
    {
        assert(isset($_COOKIE['PHPSESSID']));
                // 'You must enable cookies for this to work properly!');
        session_start($_COOKIE['PHPSESSID']);
    }

    /**
     * unsetSession
     *
     * @static
     * @access public
     * @return void
     */
    static function unsetSession()
    {
        $_SESSION = array();
        unset($_SESSION);
    }

    /**
     * destroySession
     *
     * @static
     * @access public
     * @return void
     */
    static function destroySession()
    {
        //unset($core);
        unset($_SESSION['auth']);
        unset($_SESSION['user']);
        unset($_SESSION['NR_pages']);
        session_destroy();
    }

    /**
     * clearCache
     *
     * @static
     * @access public
     * @return void
     */
    static function clearCache()
    {
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Mon, 26 Jul 1997 05:00:00GMT");
        //header("Content-Type: application/xml; charset=utf-8");
    }

    /**
     * unsetCookies
     *
     * @param bool $cookies Cookies to unset
     *
     * @static
     * @access public
     * @return void
     */
    static function unsetCookies($cookies = 'all')
    {
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach ($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name = trim($parts[0]);
                setcookie($name, '', time()-1000);
                setcookie($name, '', time()-1000, '/');
            }
        }
    }

    /**
     * setSessionCookie
     *
     * @param mixed $userData The $userData object
     * @param int   $expires  Expiry period
     *
     * @static
     * @access public
     * @return void
     */
    static function setSessionCookie($userData,$expires=3600)
    {
        //TODO: set a cookie with session data
        //setcookie("user[uname]",$_SESSION['auth']->name.'');
        //setcookie("user[uid]",  $_SESSION['auth']->uid.'');
        foreach ($userData as $key => $value) {
            setcookie("auth[$key]", base64_encode($value), time()+$expires);
        }
        setcookie("auth['sid']", base64_encode(session_id()));
        //var_dump($_COOKIE);
    }

    /**
     * sessionToSQL
     *
     * @param int $expires Expiry time
     *
     * @static
     * @access public
     * @return void
     */
    static function sessionToSQL($expires=3600)
    {
        $dblink  = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $sid     = $_COOKIE['PHPSESSID'];
        $uid     = base64_decode($_COOKIE['auth']['uid']);
        $address = $_SERVER['REMOTE_ADDR'];
        $agent   = $_SERVER['HTTP_USER_AGENT'];
        $time    = time();

        $query = "REPLACE INTO auth_sessions
                        (sid, uid, address, agent, time, expires)
                    VALUES
                        ('$sid', '$uid', '$address', '$agent', '$time', '$expires');
                    ";
        $dblink->query($query);
        if ($dblink->query($query) == false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * attemptCookieLogin
     *
     * @static
     * @access public
     * @return void
     */
    static function attemptCookieLogin()
    {
        $sid = base64_decode($_COOKIE['auth']['sid']);
        $uid = base64_decode($_COOKIE['auth']['uid']);
        $address = $_SERVER['REMOTE_ADDR'];
        $agent = $_SERVER['HTTP_USER_AGENT'];

        $rodb = new mysqli(DB_HOST, RO_DB_USER, RO_DB_PASS, DB_NAME);
        $query = "SELECT uid,address,agent,time,expires
                    FROM auth_sessions
                    WHERE sid = '$sid';";
        $result = $rodb->query($query);
        $dbSession = $result->fetchObject();

        if ($dbSession->uid == $uid
            && $dbSession->address = $address
            && $dbSession->agent   = $agent
        ) {
            return true;
        }

        // TODO: verify session against database
    }
}
