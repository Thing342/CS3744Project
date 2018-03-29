<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 3/29/18
 * Time: 11:02 AM
 */

namespace app\controllers;

require_once 'app/models/UnitEvent.php';

use app\models\Unit;
use app\models\Person;
use app\models\UnitEvent;

use lib\Controller;

class CompanyController extends BaseController
{
    /**
     * Factory function, instantiates the controller (called by the server upon dispatch)
     */
    public static function init(): Controller {
        return new CompanyController();
    }

    public function routes(): array
    {
        return [
            self::route("POST", "/add", 'companyAdd'),

            self::route("POST", "/:companyID/personAdd/", 'companyAddPerson'),
            self::route("POST", "/:companyID/personDelete/:personID", 'companyDeletePerson'),
            self::route("POST", "/:companyID/eventAdd/", 'companyAddEvent'),
            self::route("POST", "/:companyID/eventDelete/:eventID", 'companyDeleteEvent'),

            self::route("POST", "/:companyID/changeName", 'companyChangeName'),
            self::route("POST", "/:companyID/delete", 'companyDelete'),

            self::route("GET", "/:companyID/edit", 'companyEditPage'),
            self::route("GET", "/:companyID", 'companyPage'),
            self::route("GET", "/", 'companies'),
        ];
    }

    /**
     * Full path: '/companies/:companyID'
     */
    public function companyPage($params) {
        $id = $params['companyID'];
        if ($id == null) {
            $this->error404($params[0]);
        }

        $db = $this->getDBConn();

        $company = Unit::fetch($db, $id);
        if ($company == null) {
            $this->error404($params[0]);
        }

        $members = Person::fetchAllInUnit($db, $id);
        if($members == null) {
            $members = [];
        }

        $events = UnitEvent::fetchAllInUnit($db, $id);
        if ($events == null) {
            $events = [];
        }

        require "app/views/companyDetails.php";
    }

    public function companyChangeName($params) {
        $token = $this->require_authentication();
        $id = $params['companyID'];
        if ($id == null) {
            $this->error404($params[0]);
        }

        $db = $this->getDBConn();

        $company = Unit::fetch($db, $id);
        if ($company == null) {
            $this->error404($params[0]);
        }

        $company->setName($_POST['name']);
        $company->commit($db);

        $this->addFlashMessage("Changed company name to ${_POST['name']}!", self::FLASH_LEVEL_SUCCESS);
        $this->redirect("/companies/". $company->getId() . "/edit");
    }

    public function companyDelete($params) {
        $token = $this->require_authentication();
        $id = $params['companyID'];
        if ($id == null) {
            $this->error404($params[0]);
        }

        $db = $this->getDBConn();

        $res = Unit::delete($db, $id);

        if($res) {
            $this->addFlashMessage("Deleted company!", self::FLASH_LEVEL_SUCCESS);
            $this->redirect("/companies");
        } else {
            $this->addFlashMessage("Unknown error when deleting company.", self::FLASH_LEVEL_SERVER_ERR);
            $this->redirect("/companies");
        }
    }

    public function companyAdd($params) {
        $token = $this->require_authentication();

        $db = $this->getDBConn();

        $company = new Unit();
        $company->setName("New Company");

        $res = $company->commit($db);

        if($res) {
            $this->addFlashMessage("Added new company!", self::FLASH_LEVEL_SUCCESS);
            $this->redirect("/companies/" . $company->getId() . "/edit");
        } else {
            $this->addFlashMessage("Unknown error when adding company.", self::FLASH_LEVEL_SERVER_ERR);
            $this->redirect("/companies");
        }
    }

    public function companyAddPerson($params) {
        $token = $this->require_authentication();
        $id = $params['companyID'];
        if ($id == null) {
            $this->error404($params[0]);
        }

        $db = $this->getDBConn();

        $person = new Person();
        $person->setFirstname($_POST['firstname'])
            ->setLastname($_POST['lastname'])
            ->setRank($_POST['rank'])
            ->setUnitID($id);

        $res = $person->commit($db);

        if($res) {
            $this->addFlashMessage("Added " . $person->getFullName(), self::FLASH_LEVEL_SUCCESS);
        } else {
            $this->addFlashMessage("Unknown error when adding person.", self::FLASH_LEVEL_SERVER_ERR);
        }

        $this->redirect("/companies/". $id . "/edit");
    }

    public function companyDeletePerson($params) {
        $token = $this->require_authentication();
        $id = $params['companyID'];
        $personid = $params['personID'];

        if ($id == null || $personid == null) {
            $this->error404($params[0]);
        }

        $db = $this->getDBConn();
        $res = Person::delete($db, $personid);

        if($res) {
            $this->addFlashMessage("Deleted person.", self::FLASH_LEVEL_SUCCESS);
        } else {
            $this->addFlashMessage("Unknown error when deleting person.", self::FLASH_LEVEL_SERVER_ERR);
        }

        $this->redirect("/companies/". $id . "/edit");
    }

    public function companyAddEvent($params) {
        $token = $this->require_authentication();

        $id = $params['companyID'];
        if ($id == null) {
            $this->error404($params[0]);
        }

        $db = $this->getDBConn();

        $event = new UnitEvent();
        $event->setUnitID($id)
            ->setEvent($_POST['eventName'])
            ->setType($_POST['type'])
            ->setDate($_POST['date'])
            ->setDescription($_POST['description'])
            ->setLocationName($_POST['locationName'])
            ->setLatitude($_POST['latitude'])
            ->setLongitude($_POST['longitude']);

        $res = $event->commit($db);

        if($res) {
            $this->addFlashMessage("Added new event!", self::FLASH_LEVEL_SUCCESS);
        } else {
            $this->addFlashMessage("Unknown error when adding event.", self::FLASH_LEVEL_SERVER_ERR);
        }

        $this->redirect("/companies/". $id . "/edit");
    }

    public function companyDeleteEvent($params) {
        $token = $this->require_authentication();

        $id = $params['companyID'];
        $eventid = $params['eventID'];

        if ($id == null || $eventid == null) {
            $this->error404($params[0]);
        }

        $db = $this->getDBConn();
        $res = UnitEvent::delete($db, $eventid);

        if($res) {
            $this->addFlashMessage("Deleted event.", self::FLASH_LEVEL_SUCCESS);
        } else {
            $this->addFlashMessage("Unknown error when deleting event.", self::FLASH_LEVEL_SERVER_ERR);
        }

        $this->redirect("/companies/". $id . "/edit");
    }

    /**
     * Full path: '/companies/:companyID/edit'
     */
    public function companyEditPage($params) {
        $token = $this->require_authentication();

        $id = $params['companyID'];
        if ($id == null) {
            $this->error404($params[0]);
        }

        $db = $this->getDBConn();

        $company = Unit::fetch($db, $id);
        if ($company == null) {
            $this->error404($params[0]);
        }

        $members = Person::fetchAllInUnit($db, $id);
        if($members == null) {
            $members = [];
        }

        $events = UnitEvent::fetchAllInUnit($db, $id);
        if ($events == null) {
            $events = [];
        }

        require "app/views/companyEdit.php";
    }


    /**
     * Full path: '/companies'
     */
    public function companies($params) {
        $db = $this->getDBConn();
        $companies = Unit::fetchAll($db);
        if ($companies == null) {
            $this->addFlashMessage("Unable to fetch companies: Unknown Error", self::FLASH_LEVEL_SERVER_ERR);
            $this->redirect('/');
        }

        require "app/views/companies.phtml";
    }

}