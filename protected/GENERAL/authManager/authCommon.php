<?php

/**
 * authCommon
 * Manager class for controlling the authentication mechanics
 *
 * @package Auth
 * @version 0.1.2
 * @copyright Copyright (c) 2010 Serenity Media
 * @author  Victor Nițu <victor@serenitymedia.ro>
 * @license http://www.gnu.org/licenses/agpl-3.0.txt AGPLv3
 */
class authCommon {

    protected $rodb;

    /* {{{ private sanitizeLogin() */
    /**
     * sanitizeLogin
     * Performs real_escape_string on private properties
     * $username and $password.
     *
     * @access private
     * @return void
     */
    protected function sanitize ($var) {
        $this->$var = $this->rodb->real_escape_string($this->$var);
    }
    /* }}} */

    private function dbConnect() {
        $this->rodb = new mysqli(dbHost,dbroUser,dbroPass,dbName);
        $this->rodb->set_charset("utf8");
    }

    static function isAuth() {
        if(isset($_SESSION['auth']) && is_object($_SESSION['auth']))
            return true;
    }

    static function isAdmin() {
        if($_SESSION['auth']->cid == 1 || $_SESSION['auth'] == 7)
            return TRUE;
        else
            return FALSE;
    }

    protected function incrementFails($uid) {
        //TODO: docblock
        $query = "UPDATE auth_user_stats
                    SET failed_logins = failed_logins+1
                    WHERE uid='$uid'";
        if(!$this->rodb->query($query))
            throw new Exception('Query failed: ' . $this->DB->error);
        else
            return TRUE;
    }

    protected function storeResult($result) {
        if(!is_object($result))
            return FALSE;
        else {
            $this->userData = $result->fetch_object();
            return TRUE;
        }
    }

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
        if(!$this->storeResult($this->rodb->query($query)))
            die('Query failed: ' . $this->rodb->error);
        else {
            return TRUE;
        }
    }

    protected function getLoginDetails($loginName='', $type='email') {
        $loginQ = ( $type == 'email'
            ? "auth_users.email = '$loginName'"
            :  "auth_users.name = '$loginName'");

        $query = "SELECT auth_users.uid, auth_users.name AS uname, auth_users.active,
                         auth_users.cid,
                         auth_users.password, auth_users.email
                    FROM auth_users
                    JOIN auth_user_details
                        ON (auth_users.uid = auth_user_details.uid)
                    LEFT JOIN auth_classes
                        ON (auth_users.cid = auth_classes.cid)
                    WHERE $loginQ;";
        if(!$this->storeResult($this->rodb->query($query)))
            die('Query failed: ' . $this->rodb->error);
        else {
            return TRUE;
    }

    }

}
