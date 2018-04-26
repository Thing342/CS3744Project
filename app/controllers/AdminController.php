<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 4/12/18
 * Time: 10:16 AM
 */

namespace app\controllers;

require_once "app/controllers/BaseController.php";
require_once "app/models/Unit.php";
require_once "app/models/Person.php";

use app\models\User;

use lib\Controller;


/**
 * Class AdminController
 * @package app\controllers
 *
 * Endpoint for admin control panel.
 * Prefix: '/admin'
 * Responsibilities:
 *  - Administrating user accounts
 *  - Editing, promoting/demoting, deleting other users accounts
 *  - Only accessible to users with administrator privileges
 */
class AdminController extends BaseController
{

    /**
     * Factory function, instantiates the controller (called by the server upon dispatch)
     */
    public static function init(): Controller {
        return new AdminController();
    }

    /**
     * @return array List of routes for this controller.
     */
    public function routes(): array
    {
        return [
            self::route("POST", "/delete/:userid", 'deleteUser'),
            self::route("GET", "/edit/:userid", 'editUserForm'),
            self::route("POST", "/edit/:userid", 'editUser'),
            self::route("GET", "/", 'index')
        ];
    }

    /**
     * Full path: GET '/admin'
     *
     * Show the main admin page.
     * Requires ADMIN permissions.
     */
    public function index($params) {
        $token = $this->require_authentication(User::TYPE_ADMIN);

        $users = User::fetchAll($this->getDBConn());

        require "app/views/admin.phtml";
    }

    /**
     * Full path: POST '/admin/edit/:userID'
     *
     * Updates the fields in a user account.
     * Requires ADMIN permissions.
     * Returns 200 and redirects to /admin if successful
     */
    public function editUser($params) {
      //validates form by preventing extra characters from being read
        $params = htmlspecialchars($params);

        $token = $this->require_authentication(User::TYPE_ADMIN);

        // Fetch account
        $user = User::fetch($this->getDBConn(), $params['userid']);
        if ($user == null) {
            $this->error404($params[0]);
        }

        // Change fields
        $user->setUsername(strtolower($_POST['username2']));
        $user->setEmail($_POST['email2']);
        $user->setType($_POST['type2']);
        $user->setFirstname($_POST['firstname2']);
        $user->setLastname($_POST['lastname2']);
        $user->setPrivacy($_POST['privacy2']);

        // Save back
        $res = $user->commit($this->getDBConn());
        error_log("Edited user ". $user->getUserId());

        error_log("Edited user ". $user->getUserId());
        if ($res) {
            // display success message and redirect to admin page
            $this->addFlashMessage('Edited user: ' . $user->getUsername(), self::FLASH_LEVEL_SUCCESS);
        } else {
            $this->addFlashMessage('Unknown error editing. Please try again: ', self::FLASH_LEVEL_SERVER_ERR);
        }

        $this->redirect('/admin');
    }

    /**
     * Full path: GET '/admin/edit/:userID'
     *
     * Shows the form for editing a user account.
     * Requires ADMIN permissions.
     */
    public function editUserForm($params) {
      //validates form by preventing extra characters from being read
        $params = htmlspecialchars($params);

        $token = $this->require_authentication(User::TYPE_ADMIN);

        // Fetch user object to fill in fields
        $user = User::fetch($this->getDBConn(), $params['userid']);
        if ($user == null) {
            $this->error404($params[0]);
        }

        require 'app/views/admin_edit.phtml';
    }

    /**
     * Full path: POST '/admin/delete/:userID'
     *
     * Deletes a user account.
     * Requires ADMIN permissions.
     * Returns 200 and redirects to /admin if successful
     */
    public function deleteUser($params) {

        
        $token = $this->require_authentication(User::TYPE_ADMIN);
        $userid = $params['userid'];

        // delete user object, redirect to homepage
        try {
            $res = User::delete($this->getDBConn(), $userid);
        } catch (\PDOException $dbErr) {
            $this->addFlashMessage('Database error on deleting user:<br>'.$dbErr->getMessage(), self::FLASH_LEVEL_SERVER_ERR);
            $this->redirect("/");
        }

        $this->addFlashMessage("Deleted user.", self::FLASH_LEVEL_INFO);

        $this->redirect('/admin');
    }
}
