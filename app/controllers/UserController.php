<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 2/25/18
 * Time: 2:04 PM
 *
 * @var array $config
 */

namespace app\controllers;

require_once "app/models/User.php";
require_once "app/models/Token.php";
require_once "app/models/Following.php";
require_once "app/controllers/BaseController.php";

use app\models\Token;
use app\models\User;
use app\models\Following;

/**
 * Class UserController
 * @package app\controllers
 *
 * Endpoint for user-based operations.
 * Prefix: '/users'
 * Responsibilities:
 *  - Managing user accounts (creation, showing, updating, deletion)
 *  - Managing user sessions (login, logout, tokens)
 *  - Logging out inactive users
 */
class UserController extends BaseController
{
    /**
     * Factory function.
     */
    public static function init() : UserController {
        return new UserController();
    }

    /**
     * Routing table
     */
    public function routes(): array
    {
        return [
            $this->route('GET', '/login', 'loginForm'),
            $this->route('GET', '/edit', 'edit'),
            $this->route('POST', '/editUser', 'editUser'),
            $this->route('POST', '/login', 'login'),
            $this->route('GET', '/logout', 'logout'),
            $this->route('POST', '/logout', 'logout'),
            $this->route('POST', '/delete', 'delete'),
            $this->route('DELETE', '/delete', 'delete'),
            $this->route('GET', '/new', 'newForm'),
            $this->route('POST', '/new', 'add'),
            $this->route('GET', '/all', 'viewAll'),
            $this->route('POST', '/:userId/unfollow', 'unfollow'),
            $this->route('POST', '/:userId/follow', 'follow'),
            $this->route('GET', '/', 'show')


        ];
    }
    /**
     * Full path: '/users/:userID/unfollow'
     *
     * Deletes following association.
     * Returns 200 and redirects to /companies if successful.
     */
    public function unfollow($params)
    {
        // Throw 401 if not logged in
        $token = $this->require_authentication();
        $currUser = $token->getUser()->getUserId();
        // Throw 401 if not logged in
        $id = $params['userId'];
        if ($id == null) {
            $this->error404($params[0]);
        }

        // Delete company model from DB
        $db = $this->getDBConn();
        $res = Following::deleteFollow($db, $currUser ,$id); // true if successful

        if ($res) {
            $this->addFlashMessage("Deleted following relationship!", self::FLASH_LEVEL_SUCCESS);
            $this->redirect("/users/all");
        } else {
            $this->addFlashMessage("Unknown error when deleting following relationship.", self::FLASH_LEVEL_SERVER_ERR);
            $this->redirect("/users/all");
        }
    }

    public function follow($params)
    {

        // Throw 401 if not logged in
        $token = $this->require_authentication();
        $currUser = $token->getUser()->getUserId();
        // Throw 401 if not logged in
        $id = $params['userId'];
      /*  if ($id == null) {
            $this->error404($params[0]);
        }*/
        try {
        // Delete company model from DB
        $db = $this->getDBConn();
        $follow = new Following();
        $res = $follow->setUserFrom($currUser)
            ->setUserTo($id)
            ->commit($this->getDBConn());

            error_log("Added following relationship ". $follow->getFollowId());

            require "config.php";
          }
          catch (\PDOException $dbErr) {
              $this->addFlashMessage('Database error on adding following relationship:<br>'.$dbErr->getMessage(), self::FLASH_LEVEL_SERVER_ERR);
          }

          // Some sort of error on adding user; return to form
          $this->redirect("/users/all");


    }
    /**
     * ENDPOINT
     * Shows the userpage for the currently logged-in user.
     * Full path: 'GET /users'
     */
    public function show($params) {
        $token = $this->require_authentication();

        if ($token == null) {
            $this->error404($params[0]);
            return;
        }

        // Fetch user info from token
        $user = $token->getUser();
        include_once "app/views/user.phtml";
    }

// shows list of all users in order of username
    public function viewAll($params){
      $token = $this->require_authentication();

      if ($token == null) {
                  $this->error404($params[0]);
                  return;
      }
      $db = $this->getDBConn();

      // Fetch the user list
      $users = User::fetchAll($db);
      $currUser = $token->getUser();
      $follow = new Following();
      if ($users == null) {
          $this->addFlashMessage("Unable to fetch users: Unknown Error", self::FLASH_LEVEL_SERVER_ERR);
          $this->redirect('/');
      }


      include_once "app/views/users.phtml";
    }


    /**
     * ENDPOINT
     * Shows the account creation form.
     * Full path: 'GET /users/new'
     */
    public function newForm($params) {
        include_once "app/views/user_new.phtml";
    }

    /**
     * ENDPOINT
     * Reads form data and creates a new user.
     * Full path: 'POST /users/new'
     */
    public function add($params) {
        try {
        	$baseType = (int)1;
            // Create a new user and attempt to save it to the database:
            $user = new User();
            $res = $user->setUsername(strtolower($_POST['username']))
                ->setPassword($_POST['password'])
                ->setEmail($_POST['email'])
                ->setType($baseType)
                ->setFirstname($_POST['firstname'])
                ->setLastname($_POST['lastname'])
                ->setPrivacy($_POST['privacy2'])
                ->commit($this->getDBConn());

            error_log("Added user ". $user->getUserId());

            require "config.php";
            if ($res) {
                // display success message and redirect to new user's page
                $this->addFlashMessage('Created new user: ' . $user->getUsername(), self::FLASH_LEVEL_SUCCESS);
                $this->redirect("/users/login");
            } else {
                $this->addFlashMessage('Unknown error adding user. Please try again: ', self::FLASH_LEVEL_SERVER_ERR);
            }
        } catch (\PDOException $dbErr) {
            $this->addFlashMessage('Database error on adding user:<br>'.$dbErr->getMessage(), self::FLASH_LEVEL_SERVER_ERR);
        }

        // Some sort of error on adding user; return to form
        $this->redirect("/users/new");
    }

    public function edit() {
      require "app/views/userEdit.php";
    }

    public function editUser($params) {
      try {
        $baseType = (int)1;
          // Create a new user and attempt to save it to the database:
          $user = User::fetch($this->getDBConn(), $_POST['id']);

          $user->setUsername(strtolower($_POST['username2']));
          $user->setPassword($_POST['password2']);
          $user->setEmail($_POST['email2']);
          $user->setType($baseType);
          $user->setFirstname($_POST['firstname2']);
          $user->setLastname($_POST['lastname2']);
          $user->setPrivacy($_POST['privacy2']);
          $res = $user->commit($this->getDBConn());

          error_log("Edited user ". $user->getUserId());
          require "config.php";
          if ($res) {
              // display success message and redirect to new user's page
              $this->addFlashMessage('Edited user: ' . $user->getUsername(), self::FLASH_LEVEL_SUCCESS);
              $this->redirect("/users/");
          } else {
              $this->addFlashMessage('Unknown error adding user. Please try again: ', self::FLASH_LEVEL_SERVER_ERR);
          }
      } catch (\PDOException $dbErr) {
          $this->addFlashMessage('Database error on adding user:<br>'.$dbErr->getMessage(), self::FLASH_LEVEL_SERVER_ERR);
      }

    }

    /**
     * ENDPOINT
     * Deletes (and logs out) the currently signed-in user.
     * Full path: 'POST/DELETE /users/new'
     */
    public function delete($params) {
        $token = $this->require_authentication();
        $userid = $token->getUser()->getUserId();

        // delete user object, redirect to homepage
        try {
            $res = User::delete($this->getDBConn(), $userid);
        } catch (\PDOException $dbErr) {
            $this->addFlashMessage('Database error on deleting user:<br>'.$dbErr->getMessage(), self::FLASH_LEVEL_SERVER_ERR);
            $this->redirect("/");
        }

        // make sure user is logged out after deletion
        session_unset();
        session_destroy();
        session_start();

        $this->addFlashMessage("Deleted user.", self::FLASH_LEVEL_INFO);

        if ($res) {
            error_log("Deleted user ".$userid);
            $this->redirect("/users/new");
        } else {
            $this->error404('/users/delete/' . $userid);
        }
    }

    /**
     * ENDPOINT
     * Reads form data and attempts to log in a user.
     * Full path: 'POST /users/login'
     */
    public function login($params) {
        // failure state
        $fail = function() {
            $this->addFlashMessage("Unable to log in: Error in usename or password.", self::FLASH_LEVEL_USER_ERR);
            error_log('failed login attempt!');
            header( "HTTP/1.0 401 Unauthorized" );
            $this->redirect('/users/login');
        };

        // try to fetch user: if user DNE, then fail
        $username = strtolower($_POST['username']);
        $user = User::fetchByName($this->getDBConn(), $username); // Long term todo - separate user info from auth
        if ($user == null) {
            error_log("Bad Username!");
            $fail();
        }

        // check password: if hash doesn't match, then fail
        if (!password_verify($_POST['password'], $user->getPasswordHash())) {
            error_log("Bad Password!");
            $fail();
        }

        // Everything checks out, start user session and create token:
        $token = new Token();
        $token->setUser($user);
        $token->setCreated(date("Y-m-d H:i:s"));
        $token->setExpires(date("Y-m-d H:i:s", time() + 30 * 60));
        $token->commit($this->getDBConn());

        session_start();
        $_SESSION['tokenid'] = $token->getTokenId();
        $_SESSION['user'] = $token->getUser();

        // Notify user of success
        $this->addFlashMessage("Welcome, $username!", self::FLASH_LEVEL_SUCCESS);
        $this->redirect('/users/');
    }

    /**
     * ENDPOINT
     * Shows the login page. If the user is already logged in, then they are logged out.
     * Full path: 'GET /users/login'
     */
    public function loginForm($params) {
        if($this->is_logged_in(true)) {
            error_log("logging out already logged-in user.");
            $this->logout([]);
        }

        include_once "app/views/user_login.phtml";
    }

    /**
     * ENDPOINT
     * Logs the currently signed in user out and deletes their session.
     * Full path: 'POST/DELETE /users/logout'
     */
    public function logout($params) {
        $token = $this->require_authentication();

        // delete token and destroy session:
        Token::delete($this->getDBConn(), $token->getTokenId());

        session_unset();
        session_destroy();
        session_start();

        // notify user and return
        $this->addFlashMessage('Logged out current user.', self::FLASH_LEVEL_INFO);
        $this->redirect('/users/login');
    }
}
