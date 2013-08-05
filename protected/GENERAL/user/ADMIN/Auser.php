<?php
/**
 * Ce nu imi place:
 * Cuser va fi instantiat cu tot cu aceste metode
 */
class Auser
{
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
    function change_uname($uid, $uname)
    {
        $query_uname = "
            UPDATE auth_users  SET name = '{$uname}' WHERE uid = $uid
        ";
        $this->DB->query($query_uname);
    }
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
    function change_activeStatus($uid, $activeStatus)
    {
        $query = "UPDATE auth_users SET active = $activeStatus WHERE uid = $uid";
        $this->DB->query($query);

        //echo "<b>Auser - change_activeStatus </b>";
    }

    function delete_user($uid){

        /**
         * Atentie la folosirea acestei functii
         * Nu se cunosc implicatiile asupra celorlalte tabele
         */
        $query = "DELETE from auth_users WHERE uid = '$uid' ";
        $this->DB->query($query);
    }
}