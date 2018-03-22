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
     * @return bool true if the user is currently logged in.
     */
    public function is_logged_in()
    {
        // Check to see if a token exists in the session variable.
        if (session_id() == '') return false;
        if (!isset($_SESSION)) return false;
        if (array_key_exists('tokenid', $_SESSION)) return true;

        error_log(json_encode($_SESSION));
        return false;
    }

    /**
     * Validate the user's authentication status, and redirects them to the login page if they aren't logged in.
     * @return Token|null - A Token identifying the user's session if they are logged in, null otherwise.
     */
    public function require_authentication() : ?Token {
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
        if ($token == null) $fail('invalid / expired token');

        //$new_time = date("Y-m-d H:i:s", time() + 30 * 60);
        //$token->setExpires($new_time);
        //$token->commit($this->getDBConn());

        // Return the token to the user
        return $token;
    }
}