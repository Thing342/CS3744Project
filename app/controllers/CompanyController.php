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
    public static function init(): Controller
    {
        return new CompanyController();
    }

    public function routes(): array
    {
        return [
            self::route("POST", "/add", 'companyAdd'),
/*
            self::route("POST", "/:companyID/personAdd/", 'companyAddPerson'),
            self::route("POST", "/:companyID/personDelete/:personID", 'companyDeletePerson'),
            self::route("POST", "/:companyID/eventAdd/", 'companyAddEvent'),
            self::route("POST", "/:companyID/eventDelete/:eventID", 'companyDeleteEvent'),
*/
            self::route("POST", "/:companyID/eventAdd", 'companyAddEventJSON'),
            self::route("POST", "/:companyID/personAdd", 'companyAddPersonJSON'),
            self::route("POST", "/:companyID/eventDelete/:eventID", 'companyDeleteEventJSON'),
            self::route("POST", "/:companyID/personDelete/:personID", 'companyDeletePersonJSON'),
            self::route("GET",  "/:companyID/events", 'companyEventsJSON'),
            self::route("GET",  "/:companyID/people", 'companyPeopleJSON'),

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
    public function companyPage($params)
    {
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
        if ($members == null) {
            $members = [];
        }

        $events = UnitEvent::fetchAllInUnit($db, $id);
        if ($events == null) {
            $events = [];
        }

        require "app/views/companyDetails.php";
    }

    public function companyChangeName($params)
    {
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
        $this->redirect("/companies/" . $company->getId() . "/edit");
    }

    public function companyDelete($params)
    {
        $token = $this->require_authentication();
        $id = $params['companyID'];
        if ($id == null) {
            $this->error404($params[0]);
        }

        $db = $this->getDBConn();

        $res = Unit::delete($db, $id);

        if ($res) {
            $this->addFlashMessage("Deleted company!", self::FLASH_LEVEL_SUCCESS);
            $this->redirect("/companies");
        } else {
            $this->addFlashMessage("Unknown error when deleting company.", self::FLASH_LEVEL_SERVER_ERR);
            $this->redirect("/companies");
        }
    }

    public function companyAdd($params)
    {
        $token = $this->require_authentication();

        $db = $this->getDBConn();

        $company = new Unit();
        $company->setName("New Company");

        $res = $company->commit($db);

        if ($res) {
            $this->addFlashMessage("Added new company!", self::FLASH_LEVEL_SUCCESS);
            $this->redirect("/companies/" . $company->getId() . "/edit");
        } else {
            $this->addFlashMessage("Unknown error when adding company.", self::FLASH_LEVEL_SERVER_ERR);
            $this->redirect("/companies");
        }
    }

    public function companyAddPerson($params)
    {
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

        if ($res) {
            $this->addFlashMessage("Added " . $person->getFullName(), self::FLASH_LEVEL_SUCCESS);
        } else {
            $this->addFlashMessage("Unknown error when adding person.", self::FLASH_LEVEL_SERVER_ERR);
        }

        $this->redirect("/companies/" . $id . "/edit");
    }

    public function companyDeletePerson($params)
    {
        $token = $this->require_authentication();
        $id = $params['companyID'];
        $personid = $params['personID'];

        if ($id == null || $personid == null) {
            $this->error404($params[0]);
        }

        $db = $this->getDBConn();
        $res = Person::delete($db, $personid);

        if ($res) {
            $this->addFlashMessage("Deleted person.", self::FLASH_LEVEL_SUCCESS);
        } else {
            $this->addFlashMessage("Unknown error when deleting person.", self::FLASH_LEVEL_SERVER_ERR);
        }

        $this->redirect("/companies/" . $id . "/edit");
    }

    public function companyAddEvent($params)
    {
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

        if ($res) {
            $this->addFlashMessage("Added new event!", self::FLASH_LEVEL_SUCCESS);
        } else {
            $this->addFlashMessage("Unknown error when adding event.", self::FLASH_LEVEL_SERVER_ERR);
        }

        $this->redirect("/companies/" . $id . "/edit");
    }

    public function companyDeleteEvent($params)
    {
        $token = $this->require_authentication();

        $id = $params['companyID'];
        $eventid = $params['eventID'];

        if ($id == null || $eventid == null) {
            $this->error404($params[0]);
        }

        $db = $this->getDBConn();
        $res = UnitEvent::delete($db, $eventid);

        if ($res) {
            $this->addFlashMessage("Deleted event.", self::FLASH_LEVEL_SUCCESS);
        } else {
            $this->addFlashMessage("Unknown error when deleting event.", self::FLASH_LEVEL_SERVER_ERR);
        }

        $this->redirect("/companies/" . $id . "/edit");
    }

    public function companyAddEventJSON($params)
    {
        $token = $this->require_authentication();

        header("Content-type:application/json");

        $id = $params['companyID'];
        if ($id == null) {
            $this->error404($params[0]);
        }

        $data = $this->get_post_json();
        if ($data == null) {
            http_response_code(400);
            return;
        }

        $db = $this->getDBConn();

        $event = new UnitEvent();
        $event->setUnitID($id)
            ->setEvent($data['eventName'])
            ->setType($data['type'])
            ->setDate($data['date'])
            ->setDescription($data['description'])
            ->setLocationName($data['locationName'])
            ->setLatitude($data['latitude'])
            ->setLongitude($data['longitude']);

        $res = $event->commit($db);

        if ($res) {
            $json = [
                "result" => "success",
                "value" => $event->serialize()
            ];
            echo json_encode($json);
        } else {
            $json = [
                "result" => "error",
                "error" => $db->errorInfo()
            ];
            echo json_encode($json);
        }
    }

    public function companyAddPersonJSON($params)
    {
        $token = $this->require_authentication();

        header("Content-type:application/json");

        $id = $params['companyID'];
        if ($id == null) {
            $this->error404($params[0]);
        }

        $data = $this->get_post_json();
        if ($data == null) {
            http_response_code(400);
            return;
        }

        $db = $this->getDBConn();

        $person = new Person();
        $person->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setRank($data['rank'])
            ->setUnitID($id);

        $res = $person->commit($db);

        if ($res) {
            $json = [
                "result" => "success",
                "value" => $person->serialize()
            ];
            echo json_encode($json);
        } else {
            $json = [
                "result" => "error",
                "error" => $db->errorInfo()
            ];
            echo json_encode($json);
        }
    }

    public function companyDeletePersonJSON($params)
    {
        $token = $this->require_authentication();

        header("Content-type:application/json");

        $id = $params['companyID'];
        $personid = $params['personID'];

        if ($id == null || $personid == null) {
            $this->error404($params[0]);
        }

        $db = $this->getDBConn();
        $res = Person::delete($db, $personid);

        if ($res) {
            $json = [
                "result" => "success",
                "value" => ""
            ];
            echo json_encode($json);
        } else {
            $json = [
                "result" => "error",
                "error" => $db->errorInfo()
            ];
            echo json_encode($json);
        }
    }

    public function companyDeleteEventJSON($params)
    {
        $token = $this->require_authentication();

        header("Content-type:application/json");

        $id = $params['companyID'];
        $eventid = $params['eventID'];

        if ($id == null || $eventid == null) {
            $this->error404($params[0]);
        }

        $db = $this->getDBConn();
        $res = UnitEvent::delete($db, $eventid);

        if ($res) {
            $json = [
                "result" => "success",
                "value" => ""
            ];
            echo json_encode($json);
        } else {
            $json = [
                "result" => "error",
                "error" => $db->errorInfo()
            ];
            echo json_encode($json);
        }
    }

    public function companyEventsJSON($params)
    {
        header("Content-type:application/json");

        $id = $params['companyID'];
        if ($id == null) {
            $this->error404($params[0]);
        }

        $db = $this->getDBConn();

        $events = UnitEvent::fetchAllInUnit($db, $id);
        if ($events == null) {
            $this->error404($params[0]);
        }

        $serialized = [];
        foreach ($events as $event) {
            array_push($serialized, $event->serialize());
        }

        echo json_encode($serialized);
    }

    public function companyPeopleJSON($params)
    {
        header("Content-type:application/json");

        $id = $params['companyID'];
        if ($id == null) {
            $this->error404($params[0]);
        }

        $db = $this->getDBConn();

        $people = Person::fetchAllInUnit($db, $id);
        if ($people == null) {
            $this->error404($params[0]);
        }

        $serialized = [];
        foreach ($people as $person) {
            array_push($serialized, $person->serialize());
        }

        echo json_encode($serialized);
    }

    /**
     * Full path: '/companies/:companyID/edit'
     */
    public function companyEditPage($params)
    {
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
        if ($members == null) {
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
    public function companies($params)
    {
        $db = $this->getDBConn();
        $companies = Unit::fetchAll($db);
        if ($companies == null) {
            $this->addFlashMessage("Unable to fetch companies: Unknown Error", self::FLASH_LEVEL_SERVER_ERR);
            $this->redirect('/');
        }

        require "app/views/companies.phtml";
    }

}