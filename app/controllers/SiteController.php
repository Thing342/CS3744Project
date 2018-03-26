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

use app\models\Unit;
use app\models\Person;

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
            self::route("GET", "/units", 'units'),
            self::route("GET", "/units/add", 'addUnits'),
            self::route("GET", "/people/:unit/add", 'addPeople'),
            self::route("GET", "/people/:unit", 'people'),
            self::route("GET", "/", 'index')
        ];
    }


    /**
     * Full path: '/' (site root)
     */
    public function about($params) {
        require "app/views/_header.phtml";
        require "app/views/about.php";
    }


    /**
     * Full path: '/' (site root)
     */
    public function index($params) {
        require "app/views/index.phtml";
    }

    /***
     * TEMP TESTING CODE
     ***/

    /**
     * Test getting units
     */
    public function units($params) {
        $units = Unit::fetchAll($this->getDBConn());
        foreach ($units as $unit) {
            /* @var \app\models\Unit $unit */
            $name = $unit->getName();
            echo <<<HTML
                <p>$name</p>
HTML;
        }
    }

    /**
     * Test adding and updating units
     */
    public function addUnits($params) {
        $unit = new Unit();
        $db = $this->getDBConn();

        $unit->setName("My Cool Unit");
        $unit->commit($db);

        // $unit->id gets updated when commit() is called

        $unit->setName("My Cool Unit #" . $unit->getId());
        $unit->commit($db);

        echo "<h1>OK</h1>";
    }

    /**
     * Test displaying people
     */
    public function people($params) {
        $unitID = $params['unit'];
        $db = $this->getDBConn();

        $unit = Unit::fetch($db, $unitID);
        if ($unit == null) {
            $this->error404($params[0]);
        }

        echo "<h1>" . $unit->getName() . "</h1>";

        $people = Person::fetchAllInUnit($db, $unitID);
        foreach ($people as $person) {
            /* @var \app\models\Person $person */
            $name = $person->getName();
            echo <<<HTML
                <p>$name</p>
HTML;
        }
    }

    /**
     * Test adding people
     */
    public function addPeople($params) {
        $unitID = $params['unit'];
        $db = $this->getDBConn();

        $unit = Unit::fetch($db, $unitID);
        if ($unit == null) {
            $this->error404($params[0]);
        }

        $person = Person::build(-1, $unitID, "Lieutenant", "Test", "Person");
        $person->commit($db);

        // $person->id gets updated when commit() is called

        $person->setLastname("Person #" . $person->getId());
        $person->commit($db);

        echo "<h1>OK</h1>";
    }

}