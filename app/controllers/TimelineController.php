<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 4/22/18
 * Time: 1:37 PM
 */

namespace app\controllers;

require_once 'app/models/TimelineEntry.php';

use app\models\TimelineEntry;
use app\models\User;
use lib\Controller;

/**
 * Class TimelineController
 * @package app\controllers
 *
 * Endpoint for the "Timeline" functionality.
 * Prefix: '/timeline'
 * Responsibilities:
 *  - CRUD Timeline events via AJAX
 *  - ???
 */
class TimelineController extends BaseController
{
    /**
     * Factory function, instantiates the controller (called by the server upon dispatch)
     */
    public static function init(): Controller
    {
        return new TimelineController();
    }

    /**
     * Routing table
     */
    public function routes(): array
    {
        return [
            self::route("PATCH", "/:event", "updateJSON"),
            self::route("DELETE", "/:event", "deleteJSON"),
            self::route("POST", "/", "createJSON"),
            self::route("GET", "/", "readJSON")
        ];
    }

    /**
     * Full path: POST '/timeline'
     *
     * Creates a new TimelineEntry object, and returns its serialized form if successful.
     * Requires EDITOR permissions.
     */
    public function createJSON($params) {
        // Throw 401 if not logged in
        $token = $this->require_authentication(User::TYPE_EDITOR);

        header("Content-type:application/json");

        // Read parsed request data
        $data = $this->get_post_json();
        if ($data == null) {
            http_response_code(400);
            return;
        }

        $db = $this->getDBConn();
        // Fill event fields and save
        $event = new TimelineEntry();
        $event
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
     * Full path: GET '/timeline'
     *
     * Returns JSON representation of all of the events in the timeline.
     */
    public function readJSON($params) {
        header("Content-type:application/json");

        $db = $this->getDBConn();

        // Fetch all models under this Company from the DB
        $events = TimelineEntry::fetchAll($db);
        if ($events == null) {
            $events = [];
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
     * Full path: PATCH '/timeline/:event'
     *
     * Updates a single TimelineEntry object, and returns its update form..
     * Requires EDITOR permissions.
     */
    public function updateJSON($params) {
        // Throw 401 if not logged in
        $token = $this->require_authentication(User::TYPE_EDITOR);

        // Validate URL params
        $id = $params['event'];
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

        $event = TimelineEntry::fetch($db, $id);
        if ($event == null) {
            $this->error404($params[0]);
        }

        header("Content-type:application/json");

        // Fill event fields and save
        $event
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
     * Full path: DELETE '/timeline/:event'
     *
     * Creates a new TimelineEntry object, and returns its serialized form if successful.
     * Requires EDITOR permissions.
     */
    public function deleteJSON($params) {
        // Throw 401 if not authenticated
        $token = $this->require_authentication(User::TYPE_EDITOR);

        header("Content-type:application/json");
        // validate url params
        $eventid = $params['event'];
        if ($eventid == null) {
            $this->error404($params[0]);
        }

        // delete event from DB
        $db = $this->getDBConn();
        $res = TimelineEntry::delete($db, $eventid);
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
}