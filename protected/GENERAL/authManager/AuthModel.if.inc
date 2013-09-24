<?php
/**
 * File containing the authentication main interface
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
 * AuthModel
 * Authentication plugin interface to standardize plugins behaviour and I/O
 * streams.
 *
 * @category  Accounts
 * @package   Auth
 * @author    Victor Nițu <victor@serenitymedia.ro>
 * @copyright 2010 Serenity Media
 * @license   http://www.gnu.org/licenses/agpl-3.0.txt AGPLv3
 * @link      http://docs.serenitymedia.ro
 */
interface AuthModel
{

    /**
     * __construct
     *
     * @param mixed $user  The user **object**
     * @param mixed $token User's security token for validation
     *
     * @access public
     * @return void
     */
    public function __construct($user, $token);

    /**
     * connectService
     *
     * @access public
     * @return void
     */
    public function connectService();

    /**
     * checkAuth
     *
     * @access public
     * @return void
     */
    public function checkAuth();

}
