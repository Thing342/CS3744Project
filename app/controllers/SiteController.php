<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 2/22/18
 * Time: 2:33 PM
 */

namespace app\controllers;

require_once "app/controllers/BaseController.php";
require_once "app/models/Unit.php";
require_once "app/models/Person.php";

use lib\Controller;

/**
 * Class SampleController
 * @package app\controllers
 *
 * Sample controller. Used for hosting effectively-static pages.
 * Prefix: '/'
 * Responsibilities:
 *  - Serving site-level pages or pages with no other sensible controller
 */
class SiteController extends BaseController
{
    /**
     * Factory function, instantiates the controller (called by the server upon dispatch)
     */
    public static function init(): Controller {
        return new SiteController();
    }

    /**
     * @return array List of routes for this controller.
     */
    public function routes(): array
    {
        return [
            self::route("GET", "/about", 'about'),
            self::route("GET", "/credits", 'credits'),
            self::route("GET", "/units", 'units'),
            self::route("POST", "/deleteUser", 'deleteUser'),
            self::route("POST", "/oneToTwo", 'oneToTwo'),
            self::route("POST", "/TwoToOne", 'twoToOne'),
            self::route("POST", "/TwoToThree", 'twoToThree'),
            self::route("POST", "/ThreeToTwo", 'threeToTwo'),
            self::route("GET", "/admin", 'admin'),
            self::route("GET", "/units/add", 'addUnits'),
            self::route("GET", "/people/:unit/add", 'addPeople'),
            self::route("GET", "/people/:unit", 'people'),
            self::route("GET", "/", 'index')
        ];
    }

    /**
     * Full path: '/credits'
     *
     * Displays the credits page
     */
    public function credits($params) {
        require "app/views/credits.phtml";
    }

    /**
     * FIXME
     */
    public function deleteUser($params) {
        $conn = $this->getDBConn();
        //Gets ID of user to delete
        $idToDelete = $_POST['idToDelete'];
        $idToDeleteInt = (int)$idToDelete;

        // //Deletes entry from database
        //$q = "DELETE FROM `user` WHERE `user`.`userID`=".$idToDeleteInt."";
        $q = "DELETE FROM `user` WHERE `user`.`userID`=?";

        $stmt = $conn->prepare($q);

        $res = $stmt->execute([$idToDeleteInt]);

        //Redirects upon successful addition to database
        if($res===TRUE) {
            echo "User ID ";
            echo $_POST['idToDelete'];
            echo " deleted.";
        }
        else {
            echo $q;
        }
    }

    /**
     * FIXME
     */
    public function oneToTwo($params) {
        $conn = $this->getDBConn();
        //Gets ID of user to delete
        $idToPromote = $_POST['idToPromote'];
        $idToPromoteInt = (int)$idToPromote;

        $q = "UPDATE `user` SET `user`.`type`=2 WHERE `user`.`userID`=?";
        //$q = "DELETE FROM `user` WHERE `user`.`userID`=?";

        $stmt = $conn->prepare($q);

        $res = $stmt->execute([$idToPromoteInt]);

        //Redirects upon successful addition to database
        if($res===TRUE) {
            echo "User ID ";
            echo $_POST['idToPromote'];
            echo " given editing access.";
        }
        else {
            echo $q;
        }
    }

    /**
     * FIXME
     */
    public function twoToOne($params) {
        $conn = $this->getDBConn();
        //Gets ID of user to delete
        $idToDemote = $_POST['idToDemote'];
        $idToDemoteInt = (int)$idToDemote;

        $q = "UPDATE `user` SET `user`.`type`=1 WHERE `user`.`userID`=?";
        //$q = "DELETE FROM `user` WHERE `user`.`userID`=?";

        $stmt = $conn->prepare($q);

        $res = $stmt->execute([$idToDemoteInt]);

        //Redirects upon successful addition to database
        if($res===TRUE) {
            echo "User ID ";
            echo $_POST['idToDemote'];
            echo " had editing access removed.";
        }
        else {
            echo $q;
        }
    }

    /**
     * FIXME
     */
    public function twoToThree($params) {
        $conn = $this->getDBConn();
        //Gets ID of user to delete
        $idToPromote = $_POST['idToPromote'];
        $idToPromoteInt = (int)$idToPromote;

        $q = "UPDATE `user` SET `user`.`type`=3 WHERE `user`.`userID`=?";
        //$q = "DELETE FROM `user` WHERE `user`.`userID`=?";

        $stmt = $conn->prepare($q);

        $res = $stmt->execute([$idToPromoteInt]);

        //Redirects upon successful addition to database
        if($res===TRUE) {
            echo "User ID ";
            echo $_POST['idToPromote'];
            echo " is now an admin.";
        }
        else {
            echo $q;
        }
    }

    /**
     * FIXME
     */
    public function threeToTwo($params) {
        $conn = $this->getDBConn();
        //Gets ID of user to delete
        $idToDemote = $_POST['idToDemote'];
        $idToDemoteInt = (int)$idToDemote;

        $q = "UPDATE `user` SET `user`.`type`=2 WHERE `user`.`userID`=?";
        //$q = "DELETE FROM `user` WHERE `user`.`userID`=?";

        $stmt = $conn->prepare($q);

        $res = $stmt->execute([$idToDemoteInt]);

        //Redirects upon successful addition to database
        if($res===TRUE) {
            echo "User ID ";
            echo $_POST['idToDemote'];
            echo " is no longer an admin.";
        }
        else {
            echo $q;
        }
    }


    /**
     * Full path: '/about'
     *
     * Displays the about page
     */
    public function about($params) {
        require "app/views/about.phtml";
    }



    /**
     * Full path: '/admin'
     *
     * Displays the admin page
     */
    public function admin($params) {
        require "app/views/adminControls.php";
    }

    /**
     * Full path: '/' (site root)
     *
     * Displays the site homepage.
     */
    public function index($params) {
        require "app/views/index.phtml";
    }

}