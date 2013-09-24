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
        $readQ = "SELECT name, uid, token FROM auth_users;";
    }

}
