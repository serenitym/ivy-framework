<?php
/**
 * Permissions class container
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
 * Class that manages permissions.
 *
 * These methods should only be used only if the $permissions array hasn't
 * yet been set from the database (user_stats table).
 *
 * @category  Accounts
 * @package   User
 * @author    Victor Nițu <victor@serenitymedia.ro>
 * @copyright 2010 Serenity Media
 * @license   http://www.gnu.org/licenses/agpl-3.0.txt AGPLv3
 * @link      http://docs.serenitymedia.ro
 */
class Permissions extends Auser
{

    // The following bunch of variables are coming from $_SESSION['userData']
    // and are set by CauthManager

    /**
     * User id
     *
     * @var float
     * @access public
     */
    public $uid = 0;
    /**
     * Class ID
     *
     * @var float
     * @access public
     */
    public $cid = 0;
    /**
     * User name, "Guest" by default
     *
     * @var bool
     * @access public
     */
    public $uname  = 'Guest';
    /**
     * Email address, "Guest" by default (?)
     *
     * @var bool
     * @access public
     */
    public $email  = 'Guest';
    /**
     * User class (as a string), "guest" by default
     *
     * @var bool
     * @access public
     */
    public $uclass = 'guest';
    /**
     * Permissions for user, should be an array (?)
     *
     * @var mixed
     * @access public
     */
    public $permissions;

    //public $classes = array();
    /**
     * The permission sets.
     *
     * Those are basically all the permissions, grouped by category and
     * destination (i.e. blog, sys, system a.o.)
     *
     * Structure is something like:
     * <pre>
     *     sets
     *       - blog
     *         - (bool) blog permission 1
     *         - (bool) blog permission 2
     *         - (bool) blog permission 3
     *       - system
     *         - (bool) system permission 1
     *         - (bool) system permission 2
     *         - (bool) system permission 3
     * </pre>
     *
     * @var bool
     * @access public
     */
    public $sets = array();

    /**
     * The names of the tables containing permissions
     *
     * @var bool
     * @access private
     */
    private $_tableNames = array();
    /**
     * The database prefix to identify tables containing permissions
     *
     * @var bool
     * @access private
     */
    private $_prefix = 'auth_permissions';

    /**
     * Array containing the IDs of the user's groups.
     *
     * @var bool
     * @deprecated deprecated since we removed groups from authentication
     * @access private
     */
    private $_groups = array();

    /**
     * Comma separated value with groups names.
     *
     * @var mixed
     *
     * @deprecated deprecated since we removed groups from authentication
     * @access private
     */
    private $_groupsCSV;

    private $_jsonFile = 'permissionSets.json';
    private $_currentPermission = null;


    /**
     * Set a value for current permission.
     *
     * @param int $value The value to be set
     *
     * @deprecated
     * @access private
     * @return void
     */
    private function _permissionSet ($value = 1)
    {
        $this->_currentPermission = $value;
        return 0;
    }

    /**
     * Select a permission by name, store selection in $_currentPermission.
     *
     * @param mixed &$name Name of the desired permission to select
     *
     * @deprecated
     * @access private
     * @return void
     */
    private function _selectPermission (&$name)
    {
        $this->_currentPermission = &$name;
    }

    /**
     * Save all the permissions info into a JSON file.
     *
     * @deprecated deprecated since permissions are stored in the database only
     * @access private
     * @return void
     */
    private function _jsonSave ()
    {
        Toolbox::Fs_writeTo($this->_jsonFile, json_encode($this->sets));
        return 0;
    }

    //==========================================================================

    /**
     * Sets the permissions array.
     *
     * @access private
     * @return void
     */
    private function Set_Db_permissions()
    {

        $serPermissions = serialize($this->permissions);
        $query = "REPLACE INTO auth_user_stats
                  SET permissions = '{$serPermissions}' , uid = {$this->uid}";
        //echo "<br> permissions - Set_Db_permissions : $query <br>";
        $this->DB->query($query);
    }

    /**
     * Set the permissions using permissions sets.
     *
     *     sets[{$tableName}][permissionName] = permissionValue
     *
     * Note that the 'gid' column is thrown away from the sets, being unnecessary.
     *
     * @access private
     * @return void
     */
    private function Set_permissionSets()
    {

        foreach ($this->_tableNames as $tableName) {

            $this->sets[$tableName] = array();

            $query = "DESCRIBE $tableName";
            $result = $this->DB->query($query);
            // $row[] = array('Field', 'Type', 'Null', 'Key', 'Default', 'Extra');
            while ($row = $result->fetch_assoc()) {
                $this->sets[$tableName][$row['Field']] =&
                    $this->permissions[$row['Field']];
            }
            //1
            unset($this->sets[$tableName]['gid']);

            // elibereaza rezultatele
            $result->free();
        }
    }

    /**
     * Set permissions for the current user based in the user class.
     *
     * @param mixed $cid The user's class id
     *
     * @access private
     * @return void
     */
    private function Set_permissions($cid)
    {
        //1
        $query = "SELECT * FROM "
                     .implode(' NATURAL JOIN ', $this->_tableNames)
                        . " WHERE `{$this->_tableNames[0]}`.`cid` = {$cid} ";

        // error_log( "[ ivy ] permissions - Set_permissions : query =  $query");
        // echo  "[ ivy ] permissions - Set_permissions : query =  $query <br>";
        $res = $this->DB->query($query);
        $this->permissions = $res->fetch_assoc();
        //var_dump($this->permissions);

    }

    /**
     * Set the table names to extract permissions from.
     *
     *     $this->_tableNames = array ('auth_permissions _blog', '','');
     *
     *     oldName: readPermissionSets ()
     *
     * @access private
     * @return void
     */
    private function Set_tableNames ()
    {
        $dbName = DB_NAME;
        $prefix = &$this->_prefix;
        // ce tabele incep cu $prefix (auth_permissions)
        $query = "SHOW TABLES WHERE Tables_in_$dbName
                              LIKE '$prefix%';";

        $result = $this->DB->query($query)
                        or die($query);

        while ($row = $result->fetch_row()) {
            /**
             * $row[0] = $row['Tables_in_blacksea_dev']
             *         = numele tabelelor cu permisiuni ( seturi de permisiuni )
             */
            array_push($this->_tableNames, $row[0]);
        }
        // echo "permissions";
        // var_dump($this->_tableNames);
    }

    /**
     * Set the permissions data and store them in the database.
     *
     * This method attempts to set the following data:
     * 1. $this->_groups & $this->_groupsCSV
     * 2. $this->_tableNames
     * 3. $this->permission
     * 4. $this->sets
     *
     * @access public
     * @return void
     */
    public function _init_permissions()
    {
        $this->_jsonFile = VAR_PATH.'tmp/' . $this->_jsonFile;

        //1
        $this->Set_tableNames();
        //2
        $this->Set_permissions($this->cid);
        //3
        $this->Set_permissionSets();
        //4
        $this->Set_Db_permissions();


    }
}
