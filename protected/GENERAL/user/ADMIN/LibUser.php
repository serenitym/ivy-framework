<?php
/**
 * User shared library
 *
 * PHP Version 5.3
 */


class LibUser
{


    /**
     * regenerateAuthJson attempts to regenerate and write to dosk a JSON file
     * used primarily by KCFinder to identify user permissions on uploads.
     *
     * This might become a useful API feature at some point.
     *
     * JSON structure:
     * {user1: {$uid: $token}.
     *  user2: {$uid: $token}}
     *
     * @static
     * @access public
     * @return void
     */
    static function regenerateAuthJson()
    {
        $mdb2 = MDB2::singleton(DSN);

        $readQ = "SELECT name, uid, token FROM auth_users;";

        // Proceed with the query...
        $res =& $mdb2->query($query);

        // Always check that result is not an error
        if (PEAR::isError($res)) {
            die($res->getMessage());
        }

        $json = '{';

        // Get each row of data on each iteration until
        while (($row = $res->fetchRow(MDB2_FETCHMODE_OBJECT))) {
            $chunk = '"%s": {"uid": "%d", "token": "%s"},';
            $json .= sprintf($chunk, $row->name, $row->uid, $row->token);
        }

        $json = substr($json, 0, -1);
        $json .= '}';

        return $json;
    }

}
