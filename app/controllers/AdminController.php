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

    public function index($params) {
        $token = $this->require_authentication(User::TYPE_ADMIN);

        $users = User::fetchAll($this->getDBConn());

        require "app/views/admin.phtml";
    }

    public function editUser($params) {
        $token = $this->require_authentication(User::TYPE_ADMIN);

        $user = User::fetch($this->getDBConn(), $params['userid']);
        if ($user == null) {
            $this->error404($params[0]);
        }

        $user->setUsername(strtolower($_POST['username2']));
        $user->setEmail($_POST['email2']);
        $user->setType($_POST['type2']);
        $user->setFirstname($_POST['firstname2']);
        $user->setLastname($_POST['lastname2']);
        $user->setPrivacy($_POST['privacy2']);

        $res = $user->commit($this->getDBConn());
        error_log("Edited user ". $user->getUserId());

        error_log("Edited user ". $user->getUserId());
        if ($res) {
            // display success message and redirect to new user's page
            $this->addFlashMessage('Edited user: ' . $user->getUsername(), self::FLASH_LEVEL_SUCCESS);
        } else {
            $this->addFlashMessage('Unknown error adding user. Please try again: ', self::FLASH_LEVEL_SERVER_ERR);
        }

        $this->redirect('/admin');
    }

    public function editUserForm($params) {
        $token = $this->require_authentication(User::TYPE_ADMIN);

        $user = User::fetch($this->getDBConn(), $params['userid']);
        if ($user == null) {
            $this->error404($params[0]);
        }

        require 'app/views/admin_edit.phtml';
    }

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