<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 3/29/18
 * Time: 11:02 AM
 */

namespace app\controllers;

require_once 'app/models/UnitEvent.php';
require_once 'app/models/Comment.php';

use app\models\Comment;
use app\models\Unit;
use app\models\Person;
use app\models\UnitEvent;

use app\models\User;
use app\models\UserEvent;
use lib\Controller;

/**
 * Class CompanyController
 * @package app\controllers
 *
 * Endpoint for company, event, and person operations.
 * Prefix: '/companies'
 * Responsibilities:
 *  - Managing companies (add, edit, delete)
 *  - Managing company personnel (add, delete)
 *  - Managing company events (add, delete)
 */
class CompanyController extends BaseController
{
    /**
     * Factory function, instantiates the controller (called by the server upon dispatch)
     */
    public static function init(): Controller
    {
        return new CompanyController();
    }

    /**
     * Routing table
     */
    public function routes(): array
    {
        return [
            self::route("POST", "/add", 'companyAdd'),

            self::route("POST", "/:companyID/eventAdd", 'companyAddEventJSON'),
            self::route("POST", "/:companyID/personAdd", 'companyAddPersonJSON'),
            self::route("POST", "/:companyID/eventDelete/:eventID", 'companyDeleteEventJSON'),
            self::route("POST", "/:companyID/personDelete/:personID", 'companyDeletePersonJSON'),
            self::route("GET",  "/:companyID/events", 'companyEventsJSON'),
            self::route("GET",  "/:companyID/people", 'companyPeopleJSON'),

            self::route("POST",  "/:companyID/submitComment", 'submitComment'),
            self::route("GET",  "/:companyID/deleteComment/:commentID", 'deleteComment'),

            self::route("POST", "/:companyID/changeName", 'companyChangeName'),
            self::route("POST", "/:companyID/delete", 'companyDelete'),

            self::route("GET", "/:companyID/edit", 'companyEditPage'),
            self::route("GET", "/:companyID", 'companyPage'),
            self::route("GET", "/", 'companies'),
        ];
    }

    /**
     * Full path: '/companies/:companyID/changeName'
     *
     * Changes the name of the company to the value stored in $_POST['name']
     * Returns 200 and redirects to edit page if successful.
     */
    public function companyChangeName($params)
    {
        // Throw 401 if not logged in
        $token = $this->require_authentication(User::TYPE_EDITOR);

        // Validate url params
        $id = $params['companyID'];
        if ($id == null) {
            $this->error404($params[0]);
        }

        $db = $this->getDBConn();

        // Fetch company model
        $company = Unit::fetch($db, $id);
        if ($company == null) {
            $this->error404($params[0]);
        }

        // Set new name and save
        $company->setName($_POST['name']);
        $company->commit($db);

        // Redirect
        $this->addFlashMessage("Changed company name to ${_POST['name']}!", self::FLASH_LEVEL_SUCCESS);
        $this->redirect("/companies/" . $company->getId() . "/edit");
    }

    /**
     * Full path: '/companies/:companyID/delete'
     *
     * Deletes company and all associated events and people.
     * Returns 200 and redirects to /companies if successful.
     */
    public function companyDelete($params)
    {
        // Throw 401 if not logged in
        $token = $this->require_authentication(User::TYPE_EDITOR);

        // Throw 401 if not logged in
        $id = $params['companyID'];
        if ($id == null) {
            $this->error404($params[0]);
        }

        // Delete company model from DB
        $db = $this->getDBConn();
        $res = Unit::delete($db, $id); // true if successful

        if ($res) {
            $this->addFlashMessage("Deleted company!", self::FLASH_LEVEL_SUCCESS);
            $this->redirect("/companies");
        } else {
            $this->addFlashMessage("Unknown error when deleting company.", self::FLASH_LEVEL_SERVER_ERR);
            $this->redirect("/companies");
        }
    }

    /**
     * Full path: '/companies/add'
     *
     * Creates a new, blank company object.
     * Returns 200 and redirects to the new company's edit page if successful.
     */
    public function companyAdd($params)
    {
        // Throw 401 if not logged in
        $token = $this->require_authentication(User::TYPE_EDITOR);

        $db = $this->getDBConn();

        // Create new company and save
        $company = new Unit();
        $company->setName("New Company");

        $res = $company->commit($db); // true if successful

        // Redirect to new page on success
        if ($res) {
            $this->addFlashMessage("Added new company!", self::FLASH_LEVEL_SUCCESS);
            $this->redirect("/companies/" . $company->getId() . "/edit");
        } else {
            $this->addFlashMessage("Unknown error when adding company.", self::FLASH_LEVEL_SERVER_ERR);
            $this->redirect("/companies");
        }
    }

    /**
     * Full path: '/companies/:companyID/eventAdd'
     *
     * Used for AJAX requests.
     * Adds a new event based on the JSON sent in the request.
     * Responds with object containing success value of operation, plus either the error or newly-created object.
     */
    public function companyAddEventJSON($params)
    {
        // Throw 401 if not logged in
        $token = $this->require_authentication(User::TYPE_EDITOR);

        header("Content-type:application/json");

        // Validate URL params
        $id = $params['companyID'];
        if ($id == null) {
            $this->error404($params[0]);
        }

        // Read parsed request data
        $data = $this->get_post_json();
        if ($data == null) {
            http_response_code(400);
            return;
        }

        $db = $this->getDBConn();

        // Fill event fields and save
        $event = new UnitEvent();
        $event->setUnitID($id)
            ->setEvent($data['eventName'])
            ->setType($data['type'])
            ->setDate($data['date'])
            ->setDescription($data['description'])
            ->setLocationName($data['locationName'])
            ->setLatitude($data['latitude'])
            ->setLongitude($data['longitude']);
        $res = $event->commit($db); // true if successful

        if ($res) { // encode results object and send
            $json = [
                "result" => "success",
                "value" => $event->serialize() // convert model into JSONizeable array
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

    /**
     * Full path: '/companies/:companyID/eventAdd'
     *
     * Used for AJAX requests.
     * Adds a new person based on the JSON sent in the request.
     * Responds with object containing success value of operation, plus either the error or newly-created object.
     */
    public function companyAddPersonJSON($params)
    {
        // Throw 401 if not logged in
        $token = $this->require_authentication(User::TYPE_EDITOR);

        header("Content-type:application/json");

        // Validate URL params
        $id = $params['companyID'];
        if ($id == null) {
            $this->error404($params[0]);
        }

        // Read parsed request data
        $data = $this->get_post_json();
        if ($data == null) {
            http_response_code(400);
            return;
        }

        $db = $this->getDBConn();

        // Create and fill fields of model
        $person = new Person();
        $person->setFirstname($data['firstname'])
            ->setLastname($data['lastname'])
            ->setRank($data['rank'])
            ->setUnitID($id);

        // Save model
        $res = $person->commit($db); // true if save is successful

        if ($res) { // encode results object and send
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

    /**
     * Full path: '/companies/:companyID/personDelete/:personID'
     *
     * Used for AJAX requests.
     * Deletes the the person with given company and person ID.
     * Responds with object containing success value of operation, plus either the error if unsuccessful.
     */
    public function companyDeletePersonJSON($params)
    {
        // Throw 401 if not authenticated
        $token = $this->require_authentication(User::TYPE_EDITOR);

        header("Content-type:application/json");

        // validate url params
        $id = $params['companyID'];
        $personid = $params['personID'];
        if ($id == null || $personid == null) {
            $this->error404($params[0]);
        }

        // delete person from DB
        $db = $this->getDBConn();
        $res = Person::delete($db, $personid);

        // encode response data
        if ($res) {
            $json = [
                "result" => "success",
                "value" => "" // empty field for data consistency
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

    /**
     * Full path: '/companies/:companyID/eventDelete/:eventID'
     *
     * Used for AJAX requests.
     * Deletes the the event with given company and event ID.
     * Responds with object containing success value of operation, plus either the error if unsuccessful.
     */
    public function companyDeleteEventJSON($params)
    {
        // Throw 401 if not authenticated
        $token = $this->require_authentication(User::TYPE_EDITOR);

        header("Content-type:application/json");

        // validate url params
        $id = $params['companyID'];
        $eventid = $params['eventID'];
        if ($id == null || $eventid == null) {
            $this->error404($params[0]);
        }

        // delete event from DB
        $db = $this->getDBConn();
        $res = UnitEvent::delete($db, $eventid);

        // encode response data
        if ($res) {
            $json = [
                "result" => "success",
                "value" => "" // empty field for data consistency
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

    /**
     * Full path: '/companies/:companyID/events'
     *
     * Used for AJAX requests.
     * Fetches the list of events for this company.
     * Responds with a JSON-encoded list of the events for this company.
     */
    public function companyEventsJSON($params)
    {
        header("Content-type:application/json");

        // Validate url params
        $id = $params['companyID'];
        if ($id == null) {
            $this->error404($params[0]);
        }

        $db = $this->getDBConn();

        // Fetch all models under this Company from the DB
        $events = UnitEvent::fetchAllInUnit($db, $id);
        if ($events == null) {
            $this->error404($params[0]);
        }

        // Convert the model objects into JSON-izable arrays and stuff them into a list
        $serialized = [];
        foreach ($events as $event) {
            array_push($serialized, $event->serialize());
        }

        // send out encoded JSON
        echo json_encode($serialized);
    }

    /**
     * Full path: '/companies/:companyID/people'
     *
     * Used for AJAX requests.
     * Fetches the list of people for this company.
     * Responds with a JSON-encoded list of the people in this company.
     */
    public function companyPeopleJSON($params)
    {
        header("Content-type:application/json");

        // Validate url params
        $id = $params['companyID'];
        if ($id == null) {
            $this->error404($params[0]);
        }

        $db = $this->getDBConn();

        // Fetch all models under this Company from the DB
        $people = Person::fetchAllInUnit($db, $id);
        if ($people == null) {
            $this->error404($params[0]);
        }

        // Convert the model objects into JSON-izable arrays and stuff them into a list
        $serialized = [];
        foreach ($people as $person) {
            array_push($serialized, $person->serialize());
        }

        // send out encoded JSON
        echo json_encode($serialized);
    }

    /**
     * Full path: '/companies/:companyID'
     *
     * Fetches the company page.
     */
    public function companyPage($params)
    {
        // Validate url params
        $id = $params['companyID'];
        if ($id == null) {
            $this->error404($params[0]);
        }

        $db = $this->getDBConn();

        // Fetch company
        $company = Unit::fetch($db, $id);
        if ($company == null) {
            $this->error404($params[0]);
        }

        // Fetch comments
        $comments = Comment::fetchByUnit($db, $id);

        // Display page, passing in company object.
        require "app/views/companyDetails.php";
    }

    /**
     * Full path: '/companies/:companyID/edit'
     *
     * Fetches the editable page for a company.
     */
    public function companyEditPage($params)
    {
        $token = $this->require_authentication(User::TYPE_EDITOR);

        // Validate url params
        $id = $params['companyID'];
        if ($id == null) {
            $this->error404($params[0]);
        }

        $db = $this->getDBConn();

        // Fetch company
        $company = Unit::fetch($db, $id);
        if ($company == null) {
            $this->error404($params[0]);
        }

        // Display page, passing in company object.
        require "app/views/companyEdit.php";
    }

    /**
     * Full path: '/companies'
     *
     * Displays the full list of companies on the site
     */
    public function companies($params)
    {
        $db = $this->getDBConn();

        // Fetch the company list
        $companies = Unit::fetchAll($db);

        if ($companies == null) {
            $this->addFlashMessage("Unable to fetch companies: Unknown Error", self::FLASH_LEVEL_SERVER_ERR);
            $this->redirect('/');
        }

        // Display the page, passing in $companies as a variable
        require "app/views/companies.phtml";
    }

    /**
     * Full path: POST '/companies/:companyID/submitComment'
     *
     * Submits a comment to the company page.
     */
    public function submitComment($params)
    {
        $unitid = $params['companyID'];
        $token = $this->require_authentication();

        $commentText = $_POST['commentText'];
        $comment = Comment::build(-1, $token->getUser(), $unitid, date('Y-m-d H:i:s'), $commentText);

        if (!$comment->commit($this->getDBConn())) {
            $this->addFlashMessage("Failed to send comment: " . $this->getDBConn()->errorCode(), self::FLASH_LEVEL_SERVER_ERR);
        } else {
            $this->addFlashMessage("Sent comment.", self::FLASH_LEVEL_INFO);
        }

        $this->redirect('/companies/' . $unitid);
    }

    /**
     * Full path: POST '/companies/:companyID/deleteComment/:commentId'
     *
     * Deletes a comment from a company page.
     * Requires EDITOR permissions.
     */
    public function deleteComment($params) {
        $unitid = $params['companyID'];
        $commentid = $params['commentID'];
        $token = $this->require_authentication(2);

        if(!Comment::delete($this->getDBConn(), $commentid)) {
            $this->addFlashMessage("Failed to delete comment: " . $this->getDBConn()->errorCode(), self::FLASH_LEVEL_SERVER_ERR);
        } else {
            $this->addFlashMessage("Deleted comment.", self::FLASH_LEVEL_INFO);
        }

        $this->redirect('/companies/' . $unitid);
    }

}