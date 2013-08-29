<?php
/**
 * Ce nu imi place:
 * Cuser va fi instantiat cu tot cu aceste metode
 */
class Auser
{
    static function generate_token($uid=0, $email='')
    {
        $token = ''
            . base64_encode($uid)
            . base64_encode(microtime())
            . base64_encode($email)
            . Toolbox::randomString(10);
        return $token = md5($token);
    }
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
                    "[ivy] User token storag error: " . $stmt->error(),
                    E_USER_WARNING
                );
            }


    }
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
        if (isset($this->DB)) {
            return $this->DB->query($query)->fetch_object()->token;
        } else {
            trigger_error("[ivy] User: No database connection!", E_USER_WARNING);
        }

    }

    public function createPassword($string)
    {
        // For now, a simple md5() will be better than plain text, and easier
        // to debug. If things get nasty (or ideas strike), it'll revolve
        // around some generated salt added to $string before md5-ing.

        return md5($string);
    }

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

    static function Db_getPassword($uid)
    {
        $row = $this->DB->query(
            "SELECT password FROM auth_users WHERE uid = '$uid'"
        )->fetch_object() or $return = false;

        isset($row->password) && $return = $row->password;
        return $return;
    }

    /**
     * valid_uname
     *
     * @param mixed $uname
     * @access public
     * @return void
     */
    function valid_uname($uname)
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
     * change_uname
     *
     * @param mixed $uid
     * @param mixed $uname
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
     * change_uclass
     *
     * @param mixed $uid
     * @param mixed $cid
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
        array_push($queries, "
            DELETE FROM auth_user_stats WHERE uid = $uid
        ");
        array_push($queries, "
            UPDATE auth_users SET cid = $cid WHERE uid = $uid
        ");

        $this->C->DB_queryBulk($queries);
        var_dump($queries);
    }
    /**
     * change_activeStatus
     *
     * @param mixed $uid
     * @param mixed $activeStatus
     * @access public
     * @return void
     */
    function change_activeStatus($uid, $activeStatus)
    {
        $query = "UPDATE auth_users SET active = $activeStatus WHERE uid = $uid";
        $this->DB->query($query);

        //echo "<b>Auser - change_activeStatus </b>";
    }


    function mktoken($email)
    {
        return $token = substr(md5(base64_encode($email)), 0, 24);
    }

    /***********[ Toolbar methods below this line ]***********/

    /**
     * _hook_deactivateUser
     *
     * @access protected
     * @return void
     */
    function _hook_deactivateUser()
    {
        $_POST['uid'] = mysql_real_escape_string(intval($_POST['uid']));
        $this->post = handlePosts::Get_postsFlexy(array('uid'));


        return true;
    }
    /**
     * deactivateUser
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

    function _hook_inviteUser()
    {

        $this->post = handlePosts::Get_postsFlexy(array('email', 'cid'));
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
                alert("I cannot understand that, are you sure is a valid email address?"+
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
     * to the invited user. It expects sanitized data from a hook and halts
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
        $cid   =& $this->post->cid;
        $token =  $this->generate_token(0, $this->post->email);

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


        if ($this->Db_getToken(filter_var($_GET['email'], FILTER_SANITIZE_EMAIL)) != strval($_GET['token'])
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
            $this->C->jsTalk .= "alert('Username must be at least 3 characters long, and the only allowed characters are letters and underscores. \\n Good: \'user_name\', \'username\'\\n Bad: \'_user_name\', \'username_\' ');";
            return false;
        }

        if ($this->post->password !== $this->post->confirm) {
            $this->C->jsTalk .= "alert('Password confirmation failed, please retry!');";
            return false;
        }

        if (strlen($this->post->password) < 6) {
            $this->C->jsTalk .= "alert('The password must have at least 6 characters!');";
            return false;
        }

        $this->post->password   = $this->DB->real_escape_string($this->post->password);
        $this->post->loginName  = $this->DB->real_escape_string($this->post->loginName);
        $this->post->first_name = $this->DB->real_escape_string($this->post->first_name);
        $this->post->last_name  = $this->DB->real_escape_string($this->post->last_name);
        $this->post->title      = $this->DB->real_escape_string($this->post->title);
        $this->post->email      = $this->DB->real_escape_string($_GET['email']);

        return true;
    }
    public function inviteConfirm()
    {
        $inviteQ = "SELECT * FROM auth_invitations WHERE email = '"
            . $this->post->email . "'";
        $invite = $this->DB->query($inviteQ)->fetch_object();


        $newUserQ = "INSERT INTO auth_users (cid, name, active, email, password)
            VALUES ('{$invite->cid}', '{$this->post->loginName}', '1',
                    '{$invite->email}', '{$this->post->password}');";

        if (!$this->DB->query($newUserQ)) {
            $this->C->jsTalk .= "alert('Something went wrong, please contact us ASAP');";
            return false;
        } else {
            $newid = $this->DB->insert_id;
            $this->DB->query(
                "INSERT INTO auth_user_details (uid, first_name, last_name,title)
                VALUES ('$newid', '{$this->post->first_name}',
                    '{$this->post->last_name}', '{$this->post->title}')"
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
    function changePassword()
    {
        $uid      = $this->post->uid;

        $this->Db_setPassword($uid, $this->post->newpw);

        $this->C->jsTalk .= 'alert("Well done, you successfully changed you password!");';
        $this->C->jsTalk .= 'window.location = "' . Toolbox::curURL() . '";';

        return false;
    }

    private function recoverPassword()
    {
        if ($this->recoverTestEmail = true) {
            $this->buildRecoveryMail();
            $this->recoveryMail->send();
        } else {
            trigger_error("Email not registered", E_USER_NOTICE);
            return false;
        }
    }

    private function recoveryTestEmail($email)
    {
        $query = "SELECT uid FROM auth_users
            WHERE email = '$email';";
        $row = $this->DB->query($query)->fetch_row();
        $uid = $row[0];
        if (intval($uid) > 0) {
            return true;
        } else {
            return false;
        }
    }

    private function buildRecoveryMail()
    {
        if (defined('SMTP_PORT')) {
            $this->recoveryMail = new Mail(SMTP_SERVER, SMTP_PORT);
        } else {
            $this->recoveryMail = new Mail(SMTP_SERVER);
        }

        $mail =& $this->recoveryMail;

        $mail->username = SMTP_USER;
        $mail->password = SMTP_PASS;

        $mail->SetFrom($_POST['email'], $_POST['name']); // Name is optional

        if (!is_array($this->destinationEmail)) {
            $mail->AddTo($this->destinationEmail);
        } else {
            foreach ($this->destinationEmail as $destination) {
                $mail->AddTo($destination);
            }
        }

        $mail->subject = 'Around the Black Sea: password recovery';
        //$mail->message = $this->_emailBody;

        $hash = md5(date('r', time()));

        //read the atachment file contents into a string,
        //encode it with MIME base64,
        //and split it into smaller chunks

        //define the body of the message.
        $mail->message = "Test";

        // Chestii optionale
        // Note: contentType defaults to "text/plain; charset=iso-8859-1"
        //$mail->contentType = "text/html";
        //$mail->contentType =
        //"multipart/mixed; boundary=\"PHP-mixed-".$hash."\"";
        $mail->headers['Reply-To']=$_POST['email'];
        $mail->headers['Content-Type']
            = "multipart/mixed; boundary=\"PHP-mixed-".$hash."\"";

        //if(isset($_FILES['upload']))
        //$mail->addAttachment($_FILES['upload']);

        // unset ($_POST);

        return true;
    }
}
