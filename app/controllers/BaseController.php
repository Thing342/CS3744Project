<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 2/27/18
 * Time: 11:19 AM
 */

namespace app\controllers;

require_once "Controller.php";

use app\models\Token;
use app\models\User;
use lib\Controller;

/**
 * Class BaseController
 * @package app\controllers
 *
 * An Abstract Base Class containing project-specific common functionality for controllers.
 */
abstract class BaseController extends Controller
{
    /**
     * @param bool $full_check - check that the token actually exists
     * @return bool true if the user is currently logged in.
     */
    public function is_logged_in(bool $full_check=false)
    {
        // Check to see if a token exists in the session variable.
        if (session_id() == '') return false;
        if (!isset($_SESSION)) return false;
        if (!array_key_exists('tokenid', $_SESSION)) return false;

        if(!$full_check) {
            return true;
        }

        // Fetch it from the database to validate its existence
        $token = Token::fetch($this->getDBConn(), $_SESSION['tokenid']);
        if ($token == null) return false;

        return true;
    }

    /**
     * Validate the user's authentication status, and redirects them to the login page if they aren't logged in.
     * @param int $user_level - the minimum required user level needed to access this page.
     *  By default, this is set to `User::TYPE_COMMENTER` (the lowest level)
     * @return Token|null - A Token identifying the user's session if they are logged in, null otherwise.
     */
    public function require_authentication(int $user_level = User::TYPE_COMMENTER) : ?Token {
        // failure state
        $fail = function(string $reason) {
            // User is not logged in / expired token
            $this->addFlashMessage("You are not logged on. Please log in to view this page.", self::FLASH_LEVEL_USER_ERR);
            error_log("Login failed: ".$reason);
            header( "HTTP/1.0 401 Unauthorized" );
            $this->redirect('/users/login');
            die();
        };

        // Check if there's a token ID in the session variable
        if(session_id() == '') $fail('No session id!');
        if (!isset($_SESSION)) $fail('Session var not set!');
        if(!array_key_exists('tokenid', $_SESSION)) $fail('tokenid key not in session');

        // Fetch it from the database to validate its existence
        $token = Token::fetch($this->getDBConn(), $_SESSION['tokenid']);
        if ($token == null) {
            // clear out session to prevent user from using expired token id again
            session_unset();     // unset $_SESSION variable for the run-time
            session_destroy();   // destroy session data in storage
            session_start();

            $fail('invalid / expired token');
        }

        // Check user permissions
        $usertype = $token->getUser()->getType();
        if ($usertype < $user_level) {
            // Not high enough user perms
            $this->addFlashMessage("You do not have the necessary permissions required to view this page.", self::FLASH_LEVEL_USER_ERR);
            error_log("Inadequate user permissions: (Required $user_level, recieved $usertype) ");
            header( "HTTP/1.0 401 Unauthorized" );
            $this->redirect('/');
            die();
        }

        // Return the token to the user
        return $token;
    }

    /**
     * Shortcut method for returning the object representing the currently logged-in user.
     * @return User|null
     */
    public function getLoggedInUser() : ?User {
        if (!$this->is_logged_in() || !array_key_exists('user', $_SESSION)) return null;
        else return $_SESSION['user'];
    }

    /**
     * Shortcut method for returning the object representing the currently logged-in user.
     * @return User|null
     */
    public function getLoggedInUserType() : int {
        if (!$this->is_logged_in() || !array_key_exists('user', $_SESSION)) return 0;
        else return $this->getLoggedInUser()->getType();
    }

    /**
     * Overrides Controller's on_request method.
     * @inheritdoc
     */
    function on_request(array $route, array $matches)
    {
        parent::on_request($route, $matches);

        // autoexpire sessions
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
            // last request was more than 30 minutes ago
            session_unset();     // unset $_SESSION variable for the run-time
            session_destroy();   // destroy session data in storage
            session_start();

            $this->addFlashMessage('Logged out current user due to inactivity', self::FLASH_LEVEL_INFO);
        }
        $_SESSION['LAST_ACTIVITY'] = time();
    }
}