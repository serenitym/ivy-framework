<?php

class MDB2_Session
{

    private $flashdata;
    private $flashdata_varname;
    private $session_lifetime;
    private $link;
    private $lock_timeout;
    private $lock_to_ip;
    private $lock_to_user_agent;
    private $table_name;

    function __construct(&$link, $security_code, $session_lifetime = '', $lock_to_user_agent = true, $lock_to_ip = false, $gc_probability = '', $gc_divisor = '', $table_name = 'session_data', $lock_timeout = 60)
    {

        // store the connection link
        $this->link = $link;

        // continue if there is an active MySQL connection
        if ($this->_MDB2_ping()) {

            // make sure session cookies never expire so that session lifetime
            // will depend only on the value of $session_lifetime
            ini_set('session.cookie_lifetime', 0);

            // if $session_lifetime is specified and is an integer number
            if ($session_lifetime != '' && is_integer($session_lifetime))

                // set the new value
                ini_set('session.gc_maxlifetime', (int)$session_lifetime);

            // if $gc_probability is specified and is an integer number
            if ($gc_probability != '' && is_integer($gc_probability))

                // set the new value
                ini_set('session.gc_probability', $gc_probability);

            // if $gc_divisor is specified and is an integer number
            if ($gc_divisor != '' && is_integer($gc_divisor))

                // set the new value
                ini_set('session.gc_divisor', $gc_divisor);

            // get session lifetime
            $this->session_lifetime = ini_get('session.gc_maxlifetime');

            // we'll use this later on in order to try to prevent HTTP_USER_AGENT spoofing
            $this->security_code = $security_code;

            // some other defaults
            $this->lock_to_user_agent = $lock_to_user_agent;
            $this->lock_to_ip = $lock_to_ip;

            // the table to be used by the class
            $this->table_name = $table_name;

            // the maximum amount of time (in seconds) for which a process can lock the session
            $this->lock_timeout = $lock_timeout;

            // register the new handler
            session_set_save_handler(
                array(&$this, 'open'),
                array(&$this, 'close'),
                array(&$this, 'read'),
                array(&$this, 'write'),
                array(&$this, 'destroy'),
                array(&$this, 'gc')
            );

            // start the session
            session_start();

            // the name for the session variable that will be created upon script execution
            // and destroyed when instantiating this library, and which will hold information
            // about flashdata session variables
            $this->flashdata_varname = '_MDB2_session_flashdata_ec3asbuiad';

            // assume no flashdata
            $this->flashdata = array();

            // if there are any flashdata variables that need to be handled
            if (isset($_SESSION[$this->flashdata_varname])) {

                // store them
                $this->flashdata = unserialize($_SESSION[$this->flashdata_varname]);

                // and destroy the temporary session variable
                unset($_SESSION[$this->flashdata_varname]);

            }

            // handle flashdata after script execution
            register_shutdown_function(array($this, '_manage_flashdata'));

        // if no MySQL connections could be found
        // trigger a fatal error message and stop execution
        } else trigger_error('MDB2_Session: No MDB2 connection!', E_USER_ERROR);

    }

    public function get_active_sessions()
    {

        // call the garbage collector
        $this->gc();

        // counts the rows from the database
        $result = $this->link->queryRow('
            SELECT
                COUNT(session_id) as count
            FROM ' . $this->table_name . '
        ');

        if (PEAR::isError($result)) {
            die($result->getMessage());
        }

        // return the number of found rows
        return $result['count'];

    }

    public function get_settings()
    {

        // get the settings
        $gc_maxlifetime = ini_get('session.gc_maxlifetime');
        $gc_probability = ini_get('session.gc_probability');
        $gc_divisor     = ini_get('session.gc_divisor');

        // return them as an array
        return array(
            'session.gc_maxlifetime'    =>  $gc_maxlifetime . ' seconds (' . round($gc_maxlifetime / 60) . ' minutes)',
            'session.gc_probability'    =>  $gc_probability,
            'session.gc_divisor'        =>  $gc_divisor,
            'probability'               =>  $gc_probability / $gc_divisor * 100 . '%',
        );

    }

    public function regenerate_id()
    {

        // saves the old session's id
        $old_session_id = session_id();

        // regenerates the id
        // this function will create a new session, with a new id and containing the data from the old session
        // but will not delete the old session
        session_regenerate_id();

        // because the session_regenerate_id() function does not delete the old session,
        // we have to delete it manually
        $this->destroy($old_session_id);

    }

    public function set_flashdata($name, $value)
    {

        // set session variable
        $_SESSION[$name] = $value;

        // initialize the counter for this flashdata
        $this->flashdata[$name] = 0;

    }

    public function stop()
    {

        $this->regenerate_id();

        session_unset();

        session_destroy();

    }

    function close()
    {

        // release the lock associated with the current session
        $affected =& $this->exec('SELECT RELEASE_LOCK("' . $this->session_lock . '")');

        // stop execution and print message on error

        // Always check that result is not an error
        if (PEAR::isError($affected)) {
            die($affected->getMessage());
        }

        return true;

    }

    function destroy($session_id)
    {

        // deletes the current session id from the database
        $affected = $this->link->exec('

            DELETE FROM
                ' . $this->table_name . '
            WHERE
                session_id = "' . $this->link->escape($session_id) . '"

        ');

        // Always check that result is not an error
        if (PEAR::isError($affected)) {
            die($affected->getMessage());
        }

        // if anything happened
        // return true
        if ($affected !== -1) return true;

        // if something went wrong, return false
        return false;

    }

    function clean_session_dir()
    {
        $query =
            "SELECT GROUP_CONCAT(session_id, ' ')
                FROM " . $this->table_name . '
            WHERE
            session_expire < "' . $this->link->escape(time()) . '"';

        $dir_list = $this->link->fetchRow($query);

        if (strlen($dir_list[0]) > 0) {
            system('rm -rf ' . VAR_PATH . 'tmp/sessions/' . $dir_list[0]);
        }

    }

    function gc()
    {

        // deletes expired sessions directories from file system

        $this->clean_session_dir();

        // deletes expired sessions from database
        $affected = $this->link->exec('

            DELETE FROM
                ' . $this->table_name . '
            WHERE
                session_expire < "' . $this->link->escape(time()) . '"

        ');

        if (PEAR::isError($affected)) {
            die($affected->getMessage());
        }

    }

    function open($save_path, $session_name)
    {

        return true;

    }

    function read($session_id)
    {

        // get the lock name, associated with the current session
        $this->session_lock = $this->link->escape('session_' . $session_id);

        // try to obtain a lock with the given name and timeout
        $result = $this->link->query(
            'SELECT GET_LOCK("'
            . $this->session_lock . '", '
            . $this->link->escape($this->lock_timeout) . ')'
        );

        // if there was an error
        // stop execution
        if (!is_object($result) || strtolower(get_class($result)) != 'mysqli_result'
            || @mysqli_num_rows($result) != 1
            || !($row = mysqli_fetch_array($result))
            || $row[0] != 1
        ) {
            die('MDB2_Session: Could not obtain session lock!');
        }

        //  reads session data associated with a session id, but only if
        //  -   the session ID exists;
        //  -   the session has not expired;
        //  -   if lock_to_user_agent is TRUE and the HTTP_USER_AGENT is the same as the one who had previously been associated with this particular session;
        //  -   if lock_to_ip is TRUE and the host is the same as the one who had previously been associated with this particular session;
        $hash = '';

        // if we need to identify sessions by also checking the user agent
        if ($this->lock_to_user_agent && isset($_SERVER['HTTP_USER_AGENT']))

            $hash .= $_SERVER['HTTP_USER_AGENT'];

        // if we need to identify sessions by also checking the host
        if ($this->lock_to_ip && isset($_SERVER['REMOTE_ADDR']))

            $hash .= $_SERVER['REMOTE_ADDR'];

        // append this to the end
        $hash .= $this->security_code;

        $result = $this->_mysql_query('

            SELECT
                session_data
            FROM
                ' . $this->table_name . '
            WHERE
                session_id = "' . $this->_mysql_real_escape_string($session_id) . '" AND
                session_expire > "' . time() . '" AND
                hash = "' . $this->_mysql_real_escape_string(md5($hash)) . '"
            LIMIT 1

        ') or die($this->_mysql_error());

        // if anything was found
        if (is_object($result) && strtolower(get_class($result)) == 'mysqli_result' && @mysqli_num_rows($result) > 0) {

            // return found data
            $fields = @mysqli_fetch_assoc($result);

            // don't bother with the unserialization - PHP handles this automatically
            return $fields['session_data'];

        }

        $this->regenerate_id();

        // on error return an empty string - this HAS to be an empty string
        return '';

    }

    function write($session_id, $session_data)
    {

        // insert OR update session's data - this is how it works:
        // first it tries to insert a new row in the database BUT if session_id is already in the database then just
        // update session_data and session_expire for that specific session_id
        // read more here http://dev.mysql.com/doc/refman/4.1/en/insert-on-duplicate.html
        $result = $this->_mysql_query('

            INSERT INTO
                ' . $this->table_name . ' (
                    session_id,
                    hash,
                    session_data,
                    session_expire
                )
            VALUES (
                "' . $this->_mysql_real_escape_string($session_id) . '",
                "' . $this->_mysql_real_escape_string(md5(($this->lock_to_user_agent && isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '') . ($this->lock_to_ip && isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '') . $this->security_code)) . '",
                "' . $this->_mysql_real_escape_string($session_data) . '",
                "' . $this->_mysql_real_escape_string(time() + $this->session_lifetime) . '"
            )
            ON DUPLICATE KEY UPDATE
                session_data = "' . $this->_mysql_real_escape_string($session_data) . '",
                session_expire = "' . $this->_mysql_real_escape_string(time() + $this->session_lifetime) . '"

        ') or die($this->_mysql_error());

        // if anything happened
        if ($result) {

            // note that after this type of queries, mysqli_affected_rows() returns
            // - 1 if the row was inserted
            // - 2 if the row was updated

            // if the row was updated
            // return TRUE
            if (@$this->_MDB2_affected_rows() > 1) return true;

            // if the row was inserted
            // return an empty string
            else return '';

        }

        // if something went wrong, return false
        return false;

    }

    function _manage_flashdata()
    {

        // if there is flashdata to be handled
        if (!empty($this->flashdata)) {

            // iterate through all the entries
            foreach ($this->flashdata as $variable => $counter) {

                // increment counter representing server requests
                $this->flashdata[$variable]++;

                // if we're past the first server request
                if ($this->flashdata[$variable] > 1) {

                    // unset the session variable
                    unset($_SESSION[$variable]);

                    // stop tracking
                    unset($this->flashdata[$variable]);

                }

            }

            // if there is any flashdata left to be handled
            if (!empty($this->flashdata))

                // store data in a temporary session variable
                $_SESSION[$this->flashdata_varname] = serialize($this->flashdata);

        }

    }

    private function _MDB2_affected_rows()
    {

        // execute "mysqli_affected_rows" and returns the result
        return mysqli_affected_rows($this->link);

    }

    private function _mysql_error()
    {

        // execute "mysqli_error" and returns the result
        return 'MDB2_Session: ' . mysqli_error($this->link);

    }

    private function _MDB2_ping()
    {

        if ($this->link instanceof MDB2_Driver_mysqli) {
            // MDB2 doesn't have a ping method (yet)
            return true;
        } else {
            trigger_error("Unknown database type!", E_USER_ERROR);
            return false;
        }
    }

    private function _mysql_real_escape_string($string)
    {

        // execute "mysqli_real_escape_string" and returns the result
        return mysqli_real_escape_string($this->link, $string);

    }

}

?>
