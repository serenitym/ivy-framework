<?php
/**
 * Authentication base library, contains some very basic and common functionality
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
 * AuthCommon
 * Manager class for controlling the authentication mechanics
 *
 * @category  Accounts
 * @package   Auth
 * @author    Victor Nițu <victor@serenitymedia.ro>
 * @copyright 2010 Serenity Media
 * @license   http://www.gnu.org/licenses/agpl-3.0.txt AGPLv3
 * @link      http://docs.serenitymedia.ro
 */
class AuthCommon
{

    /**
     * Returns true if current session is an authenticated one.
     *
     * @static
     * @access public
     * @return void
     */
    static function isAuth()
    {
        if (isset($_SESSION['auth']) && is_object($_SESSION['auth'])) {
            return true;
        }
    }

    /**
     * Returns true if current user is an administrator.
     *
     * @static
     * @access public
     * @return void
     */
    static function isAdmin()
    {
        if ($_SESSION['auth']->cid == 1 || $_SESSION['auth'] == 7) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Increments user_stats.failed_logins on each failed login.
     *
     * @param mixed $uid Desired user id
     *
     * @deprecated
     * @access protected
     * @return void
     */
    protected function incrementFails($uid)
    {
        //TODO: docblock
        $query = "UPDATE auth_user_stats
                    SET failed_logins = failed_logins+1
                    WHERE uid='$uid'";
        if (!$this->rodb->query($query)) {
            throw new Exception('Query failed: ' . $this->DB->error);
        } else {
            return true;
        }
    }

}
