<?php
/**
 * User administrative methods
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
 * User administrative methods.
 *
 * Contains methods to change password, generate tokens, recover accounts etc.
 *
 * Note: user will **always** be created containing these methods.
 *
 * @category  Accounts
 * @package   User
 * @author    Victor Nițu <victor@serenitymedia.ro>
 * @copyright 2010 Serenity Media
 * @license   http://www.gnu.org/licenses/agpl-3.0.txt AGPLv3
 * @link      http://docs.serenitymedia.ro
 */
class Auser extends LibUser
{

    /**
     * Generate a security token.
     *
     * By default, it assumes some default uid/email combo. In order to
     * create a better entropy, one could provide such values, even if not
     * accurate at all :-)
     *
     * The point of the above explanation is that the process is neither
     * reversible or reproducible. If you generate a token, you can't get it
     * again via exactly the same procedure.
     *
     * @param int  $uid   User id (bogus accepted)
     * @param bool $email User email (bogus accepted)
     *
     * @static
     * @access public
     * @return void
     */
    static function generate_token($uid=0, $email='john.doe@example.org')
    {
        $token = ''
            . base64_encode($uid)
            . base64_encode(microtime())
            . base64_encode($email)
            . Toolbox::randomString(10);
        return $token = md5($token);
    }

    /**
     * Specialized method, stores a single token into a single user's details
     * via prepared statement (safer method).
     *
     * @param mixed $uid   The user's id
     * @param bool  $token The token to store
     *
     * @access protected
     * @return void
     */
    protected function Db_storeToken($uid, $token = null)
    {
        if ($token === null) {
            $token = $this->generate_token($uid);
        }

        $stmt = $this->DB->prepare(
            "UPDATE auth_users SET token = ? WHERE uid = '$uid'"
        ) or trigger_error(
            "[ivy] User token storage error: " . $stmt->error(),
            E_USER_WARNING
        );

        $stmt->bindParam("s", $token);
        $stmt->execute();

        if ($stmt->errno) {
            trigger_error(
                "[ivy] User token storage error: " . $stmt->error(),
                E_USER_WARNING
            );
        }


    }

    /**
     * Read a user's token from database or test a given string against the
     * token value in the database.
     *
     * If left blank, $token does nothing. However, if one provides a string
     * as $token, it will be compared to the database value.
     *
     * Returns the token, if any, or the comparison result.
     *
     * @param mixed  $id    User's id
     * @param string $token The token (optional)
     *
     * @access public
     * @return void
     */
    public function Db_getToken($id, $token = '')
    {
        // Prepare the query: if $id is integer, select from existing users.
        // Otherwise, $id refers to a pending invitation, identified by
        // email addresses.
        $id   = filter_var($id, FILTER_SANITIZE_STRING); // Clean it up a little

        if (intval($id) > 0) {
            $query = "SELECT token FROM auth_users WHERE uid = '$id'";
        } else {
            $query = "SELECT token FROM auth_invitations WHERE email = '$id'";
        }

        // If database connection is still with us, get the token
        if (isset($this->DB) && strlen($token) < 1) {

            return $this->DB->query($query)->fetch_object()->token;

        } elseif (isset($this->DB) && strlen($token) > 1) {

            // If $token is defined, return comparison result
            if ($this->DB->query($query)->fetch_object()->token == $token) {
                return true;
            }

        } elseif (!isset($this->DB)) {
            // If database is lost, panic!
            error_log("[ivy] User: No database connection!", E_USER_WARNING);
        }

        return false;
    }

    /**
     * Store a user's token into the database.
     *
     * @param mixed $uid   User id
     * @param mixed $token The token to store
     *
     * @todo Try to merge with Db_storeToken()
     *
     * @access public
     * @return void
     */
    public function Db_setToken($uid, $token)
    {
        $uid   = filter_var($uid, FILTER_SANITIZE_STRING); // Clean it up a little
        $token = substr(filter_var($token, FILTER_SANITIZE_STRING), 0, 32);

        // If database connection is still with us, set the token
        if (isset($this->DB)) {
            $this->DB->query(
                "UPDATE auth_users SET token = '$token' WHERE uid = '$uid'"
            );
            if (!$this->DB->errno) {
                return true;
            }
        } else {
            // If database is lost, panic!
            error_log("[ivy] User: No database connection!", E_USER_WARNING);
        }

        return false;
    }

    /**
     * Read a user's email address from database or compare it to a given string.
     *
     * If left blank, $email does nothing. However, if one provides a string
     * as $email, it will be compared to the database value.
     *
     * Returns the email address, if any, or the comparison result.
     *
     * @param mixed  $id    User id
     * @param string $email Email address
     *
     * @access public
     * @return void
     */
    public function Db_getEmail($id, $email = '')
    {
        // @FIXME This adds a lot of duplicate content, unify with above function

        // Prepare the query: if $id is integer, select from existing users.
        // Otherwise, $id refers to a pending invitation, identified by
        // email addresses.
        $id   = filter_var($id, FILTER_SANITIZE_STRING); // Clean it up a little

        if (intval($id) > 0) {
            $query = "SELECT email FROM auth_users WHERE uid = '$id'";
        } else {
            $query = "SELECT email FROM auth_users WHERE name = '$id'";
        }

        // If database connection is still with us, get the token
        if (isset($this->DB) && $email == '') {
            return $this->DB->query($query)->fetch_object()->email;
        } elseif (isset($this->DB) && $email != '') {
            // If $token is defined, return comparison result
            return $this->DB->query($query)->fetch_object()->email == $email;
        } else {
            trigger_error("[ivy] User: No database connection!", E_USER_WARNING);
        }

        return false;
    }

    /**
     * Creates a secure password from a given string.
     *
     * At the moment, it only returns a md5() version of the string, but it
     * succeeds at providing a centralized password generation mechanism.
     *
     * @param mixed $string The plain text (!) password
     *
     * @todo Enhance the security of the password.
     *
     * @access public
     * @return void
     */
    public function createPassword($string)
    {
        // For now, a simple md5() will be better than plain text, and easier
        // to debug. If things get nasty (or ideas strike), it'll revolve
        // around some generated salt added to $string before md5-ing.

        return md5($string);
    }

    /**
     * Store a given password to a given user from database user table.
     *
     * Be aware that the password is stored **as is**, and it needs to be
     * safely generated, sanitized or whatever **before** storing it in the
     * database!
     *
     * @param mixed $uid      User id
     * @param mixed $password Password
     *
     * @access public
     * @return void
     */
    public function Db_setPassword($uid, $password)
    {
        $password = $this->createPassword($password);
        $uid      = intval($uid);

        $stmt = $this->DB->prepare(
            "UPDATE auth_users SET password = ? WHERE uid = ?"
        );
        $stmt->bind_param("si", $password, $uid);
        $stmt->execute();

        if ($stmt->errno) {
            return false;
        } else {
            return false;
            return true;
        }
    }

    /**
     * Return a given user's password string, as it is stored in the database.
     *
     * @param mixed $uid User id
     *
     * @static
     * @access public
     * @return void
     */
    static function Db_getPassword($uid)
    {
        $row = $this->DB->query(
            "SELECT password FROM auth_users WHERE uid = '$uid'"
        )->fetch_object() or $return = false;

        isset($row->password) && $return = $row->password;
        return $return;
    }

    /**
     * Check if a specific username is valid.
     *
     * @param mixed $uname The username to check
     *
     * @access public
     * @return void
     */
    public function valid_uname($uname)
    {
        $query_testUname = "SELECT uid
                            from auth_users
                            WHERE name = '{$uname}'
                            LIMIT 0,1    ";
        $res = $this->DB->query($query_testUname);

        $unameStat = $res->num_rows == 0 ? true : false;

        return $unameStat;
    }

    /**
     * Change username for a specific user.
     *
     * @param mixed $uid   The user id
     * @param mixed $uname The new username
     *
     * @access public
     * @return void
     */
    function change_uname($uid, $uname)
    {
        $query_uname = "
            UPDATE auth_users  SET name = '{$uname}' WHERE uid = $uid
        ";
        $this->DB->query($query_uname);
    }
    /**
     * Change the user class for a given user id.
     *
     * @param mixed $uid The user id
     * @param mixed $cid The new class id
     *
     * @access public
     * @return void
     */
    function change_uclass($uid, $cid)
    {
        /**
         * changing a class requiers deleting permissions of thaht user
         * and updating the actual class
         */
        $queries = array();
        // delete old permissions
        array_push(
            $queries,
            " DELETE FROM auth_user_stats WHERE uid = $uid "
        );
        array_push(
            $queries,
            " UPDATE auth_users SET cid = $cid WHERE uid = $uid "
        );

        $this->C->DB_queryBulk($queries);
        var_dump($queries);
    }
    /**
     * Change the status of a given user to active / inactive.
     *
     * @param mixed $uid          User id
     * @param mixed $activeStatus Status to switch to
     *
     * @todo If $activeStatus is unspecified, toggles the existing one.
     *
     * @access public
     * @return void
     */
    function change_activeStatus($uid, $activeStatus)
    {
        $query = "UPDATE auth_users SET active = $activeStatus WHERE uid = $uid";
        $this->DB->query($query);

        //echo "<b>Auser - change_activeStatus </b>";
    }


    /**
     * Generate a simple and reproducible token based on the email address.
     *
     * This is used during the invitation process, as it is a less secure token
     * than the individual user token. It is stored in a separate location,
     * the same table used to store active invitation requests.
     *
     * @param mixed $email A valid email address
     *
     * @access public
     * @return void
     */
    function mktoken($email)
    {
        return substr(md5(base64_encode($email)), 0, 24);
    }

    /***********[ Toolbar methods below this line ]***********/

    /**
     * Hook to "deactivate a user" command
     *
     * @access protected
     * @return void
     */
    public function _hook_deactivateUser()
    {
        $_POST['uid'] = mysql_real_escape_string(intval($_POST['uid']));
        $this->post = handlePosts::Get_postsFlexy(array('uid'));


        return true;
    }

    /**
     * Method called on "deactivate me" toolbar button press.
     *
     * It attempts to deactivate the currently logged in user.
     *
     * @access public
     * @return void
     */
    function deactivateUser()
    {

        /**
         * Atentie la folosirea acestei functii
         * Nu se cunosc implicatiile asupra celorlalte tabele
         */
        $uid = $this->post->uid;

        $query = "UPDATE auth_users SET active = '0' WHERE uid = '$uid' ";
        $this->DB->query($query);

        return false;
    }

    /**
     * Hook to user invitation command.
     *
     * Cleans up and validates POST data before doing anything regrettable with
     * that.
     *
     * @access protected
     * @return void
     */
    function _hook_inviteUser()
    {

        $this->post = handlePosts::Get_postsFlexy(array('email', 'cid', 'ref'));
        $this->post->email = filter_var(
            filter_var(
                $this->post->email, FILTER_SANITIZE_EMAIL
            ), FILTER_VALIDATE_EMAIL
        ); // this should be false if it fails the sanitize / validate combo test
        $this->post->cid = filter_var(
            $this->post->cid, FILTER_SANITIZE_NUMBER_INT
        );

        $existingQ = "SELECT uid FROM auth_users
            WHERE email = '{$this->post->email}';";

        if ($this->post->email === false) {
            echo ' <script type="text/javascript">
                alert("Are you sure is a valid email address?"+
                "\nPlease try again and send a report if anything seems broken.")
                </script> ';
            return false;
        } elseif ($this->DB->query($existingQ)->fetch_row()) {
            echo ' <script type="text/javascript">
                alert("Email already exists in the users database.")
                </script> ';
            return false;
        } else {
            echo ' <script type="text/javascript">
                alert("Invitation sent at ' . $this->post->email . '")
                </script> ';
        }

        return true;

    }

    /**
     * Add a pending invitation to the database and send a notification email
     * to the invited user.
     *
     * It expects sanitized data from a hook and halts
     * execution if the insert statement fails.
     *
     * Returns true if succeeded, else return false and trigger a
     * E_USER_WARNING error.
     *
     * @access public
     * @return bool
     */
    function inviteUser()
    {
        // Create a unique token for the invitation

        $email =& $this->post->email;
        $ref   =& $this->post->ref;
        $cid   =& $this->post->cid;
        $token =  $this->generate_token(0, $this->post->email);

        $refData = $this->DB->query(
            "SELECT cid FROM auth_users WHERE token = '$ref'"
        )->fetch_object();
        $refCid  = $refData->cid;

        if ($cid < $refCid) {
            $cid = $refCid;
        }

        // Prepare query to store invitation
        $stmt = $this->DB->prepare(
            "REPLACE INTO auth_invitations (email, cid, token)
                VALUES (?, ?, ?)"
        );
        $stmt->bind_param('sis', $email, $cid, $token);

        // If query fails, get out of here and log a user warning

        $stmt->execute();
        if ($stmt->errno) {
            error_log(
                "[ivy] User: Invitation error: " . $stmt->error(),
                E_USER_WARNING
            );
            return false;
        }
        $stmt->close();

        // If we reached this point, we can safely send the invitation via email

        $mail = ivyMailer::build();

        $mail->setFrom(SMTP_USER, "The Black Sea mailer");
        $mail->AddTo($this->post->email);
        $mail->subject = 'Your invitation on ' . SITE_NAME;
        $mail->defineText(
            PUBLIC_URL
            //. '?token=' . Toolbox::randomString(24)
            //. '?token=' . $this->mktoken($this->post->email)
            . '?token=' . $token
            . '&ref=' . $this->uid
            . '&email=' . $this->post->email
            . '&route=invite'
        );

        $mail->send();

        return true;
    }

    /**
     * Hook to invite confirmation command.
     *
     * It sanitizes the POST data and validates against various filters.
     *
     * @access public
     * @return void
     */
    public function _hook_inviteConfirm()
    {
        $this->post = handlePosts::Get_postsFlexy(
            array(
                'loginName',  'password',  'confirm',
                'first_name', 'last_name', 'title'
            )
        );

        $uniqueUserQ = "SELECT uid from auth_users
            WHERE name = '".strval($this->post->loginName)."';";


        if (
            $this->Db_getToken(
                filter_var($_GET['email'], FILTER_SANITIZE_EMAIL)
            ) != strval($_GET['token'])
        ) {
            $this->C->jsTalk .= "alert('Wrong security token, good bye!');";
            $this->C->jsTalk .= 'window.location = "/";';
            return false;
        }

        if ($this->DB->query($uniqueUserQ)->num_rows > 0) {
            $this->C->jsTalk .= "alert('Username exists, please retry!');";
            return false;
        }

        if (!preg_match('/^[a-z]+[a-z_]+[a-z]+$/i', $this->post->loginName)) {
            $this->C->jsTalk .= "alert('Username must be at least 3 characters "
                . "long, and the only allowed characters are letters and "
                . "underscores. \\n Good: \'user_name\', \'username\'\\n "
                . "Bad: \'_user_name\', \'username_\' ');";
            return false;
        }

        if ($this->post->password !== $this->post->confirm) {
            $this->C->jsTalk .= "alert('Password confirmation failed, try again!');";
            return false;
        }

        if (strlen($this->post->password) < 6) {
            $this->C->jsTalk .= "alert('Password must have at least 6 characters');";
            return false;
        }

        $db &= $this->DB;

        $this->post->password   = $db->real_escape_string($this->post->password);
        $this->post->loginName  = $db->real_escape_string($this->post->loginName);
        $this->post->first_name = $db->real_escape_string($this->post->first_name);
        $this->post->last_name  = $db->real_escape_string($this->post->last_name);
        $this->post->title      = $db->real_escape_string($this->post->title);
        $this->post->email      = $db->real_escape_string($_GET['email']);

        return true;
    }

    /**
     * Confirm an active invite.
     *
     * @access public
     * @return void
     */
    public function inviteConfirm()
    {
        $inviteQ = "SELECT * FROM auth_invitations WHERE email = '"
            . $this->post->email . "'";
        $invite = $this->DB->query($inviteQ)->fetch_object();


        $newUserQ = "INSERT INTO auth_users (cid, name, active, email, password)
            VALUES ('{$invite->cid}', '{$this->post->loginName}', '1',
                    '{$invite->email}', '{$this->post->password}');";

        if (!$this->DB->query($newUserQ)) {
            $this->C->jsTalk .= "alert('Something went wrong, "
               . "please contact us ASAP');";
            return false;
        } else {
            $newid = $this->DB->insert_id;
            $this->DB->query(
                "INSERT INTO auth_user_details (uid, first_name, last_name,title)
                VALUES ('$newid', '{$this->post->first_name}',
                    '{$this->post->last_name}', '{$this->post->title}')"
            );
            $this->DB->query(
                "UPDATE auth_users
                SET token = '"
                . $this->generate_token($newid, $this->post->email) . "';"
            );
            $this->DB->query(
                "INSERT INTO auth_user_stats (uid)
                VALUES ('$newid')"
            );
            $this->C->jsTalk .= "alert('Congratulations, you can now log in!');";
            $this->C->jsTalk .= 'window.location = "/?login";';
        }

        return false;
    }

    /**
     * Hook to change password command.
     *
     * @access protected
     * @return void
     */
    function _hook_changePassword()
    {
        $this->post = handlePosts::Get_postsFlexy(
            array('oldpw','newpw','confirm', 'uid')
        );

        foreach (array('uid', 'oldpw', 'newpw', 'confirm') as $key) {
            $this->post->$key = mysql_real_escape_string(
                strval($_POST[$key])
            );
        }

        $uid =& $this->post->uid;

        $result = $this->DB->query(
            "SELECT password from auth_users WHERE uid = '$uid'"
        )->fetch_row();
        $oldpw = $result[0];

        if (md5($this->post->oldpw) != $oldpw) {
            echo ' <script type="text/javascript">
                alert("Incorrect old password, please retry!")
                </script> ';
            return false;
        }

        if ($this->post->newpw !== $this->post->confirm) {
            echo ' <script type="text/javascript">
                alert("Password confirmation failed, please retry!")
                </script> ';
            return false;
            exit();
        } else {
            $password = $this->post->newpw;
            if (strlen($password) < 6) {
                echo ' <script type="text/javascript">
                    alert("Password too short, try using at least 6 characters.")
                    </script> ';
                return false;
            }

        }
        return true;
    }

    /**
     * Change a user's password.
     *
     * Both the user and the desired password come through a HTTP POST request.
     * See the hook's code for more details about what come from where.
     *
     * @access public
     * @return void
     */
    function changePassword()
    {
        $uid =& $this->post->uid;

        $this->Db_setPassword($uid, $this->post->newpw);
        $this->Db_setToken($uid, $this->generate_token($uid));

        $this->C->jsTalk .= 'alert("Well done, you successfully changed '
            . 'your password!");';
        $this->C->jsTalk .= 'window.location = "' . Toolbox::curURL() . '";';

        return false;
    }

    /**
     * Hook to validate and clean up the recover password input data.
     *
     * @access public
     * @return void
     */
    public function _hook_recoverPassword()
    {
        /**
         *  Check where the request came from:
         *  * if user got here via email, uid and token should be supplied
         *  * in the case of recovery form, we'd have a loginName
         */

        if (isset($_POST['uid'], $_POST['token'])) {
            $this->post  = handlePosts::Get_postsFlexy(array('uid', 'token', 'newpw', 'confirm'));
            $this->route = 'resetPassword';
            if ($this->Db_getToken($this->post->uid, $this->post->token) == true) {
                return true;
            } else {
                return false;
            }
        } else {
            $this->post  = handlePosts::Get_postsFlexy(array('loginName'));
            $this->route = 'recoveryEmail';
            if ($this->loginExists($this->post->loginName) == true) {
                return true;
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * This method decides if it should send a password recovery email or
     * actually do the requested recovery.
     *
     * @access public
     * @return void
     */
    public function recoverPassword()
    {
        switch($this->route) {
        default:
        case 'recoveryEmail':
            $this->sendRecoveryEmail();
            break;

        case 'resetPassword':
            $this->resetPassword();
            break;
        }

        return false;
    }

    /**
     * Method to reset a user's password.
     *
     * @todo   More detailed documentation!
     * @access private
     * @return void
     */
    private function resetPassword()
    {
        $this->post->newpw   = $this->DB->real_escape_string($this->post->newpw);
        $this->post->confirm = $this->DB->real_escape_string($this->post->confirm);

        if ($this->post->newpw != $this->post->confirm) {
            echo ' <script type="text/javascript">
                alert("Password confirmation failed, please retry!")
                </script> ';
            return false;
            exit();
        } else {
            $password =& $this->post->newpw;
            if (strlen($password) < 6) {
                echo ' <script type="text/javascript">
                    alert("Password too short, try using at least 6 characters.")
                    </script> ';
                return false;
            }
        }

        $uid =& $this->post->uid;
        $this->Db_setPassword($uid, $this->post->newpw);

        $this->C->jsTalk .= 'alert("Well done, you successfully changed you password!");';
        $this->C->jsTalk .= 'window.location = "/?login";';

        return false;
    }

    /**
     * Send a password recovery email.
     *
     * @todo   Document more of its behaviour, it does a lot of stuff.
     * @access private
     * @return void
     */
    private function sendRecoveryEmail()
    {
        // Let the email fun begin! Build the email object first.
        $mail = ivyMailer::build();

        $mail->setFrom(SMTP_USER, "The Black Sea mailer");
        $mail->AddTo($this->post->email, $this->post->name);
        $mail->subject = 'Password recovery on ' . SITE_NAME;
        $mail->defineText(
            PUBLIC_URL
            . '?user='  . base64_encode($this->post->email)
            . '&id=' . $this->post->uid
            . '&token=' . $this->post->token
            . '&route=recoverPassword'
        );

        $mail->send();

        // Send a confirmation to the front end
        // @FIXME ugly alert, must refine this shit
        $this->C->jsTalk .= "alert('Done! Please check your inbox.');";
        $this->C->jsTalk .= "window.location = '".Toolbox::curURL()."'";
    }

    /**
     * Check if a given login name exists.
     *
     * As in the Auth package methods, the login name could be either
     * a valid email address, determined by filter_var() function, or a valid
     * username. The method itself determines what to check for in the database.
     *
     * @param mixed $login The login name
     *
     * @access private
     * @return void
     */
    private function loginExists($login)
    {

        // Check what we have received: email address or user name
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $loginType = 'email';
            $query = "SELECT uid, token, name FROM auth_users
                WHERE email = '$login';";
        } else {
            $loginType = 'username';
            $query = "SELECT uid, token, email FROM auth_users
                WHERE name = '$login';";
        }

        // Get the user ID and email for the supplied login name
        $row  = $this->DB->query($query)->fetch_object();
        $uid  = $this->post->uid
              = $row->uid;

        // Set the user name and email from wherever they came from
        $this->post->name  = $loginType == 'name' ?: $row->name;
        $this->post->email = $loginType == 'email' ?: $row->email;
        $this->post->token = $row->token;

        // Stop here if uid is not a valid result or it doesn't exist
        if (!intval($uid)) {
            $this->C->jsTalk .= "alert('No such user, go away!');";
            return false;
        }

        // We got this far, so it's ok to confirm user exists
        return true;
    }
}
