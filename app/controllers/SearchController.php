<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 3/30/18
 * Time: 6:12 PM
 */

namespace app\controllers;

require_once 'app/models/UnitNote.php';

use app\models\Unit;
use app\models\Person;
use app\models\UnitNote;

/**
 * Class SearchyController
 * @package app\controllers
 *
 * Endpoint search functions.
 * Prefix: '/search'
 * Responsibilities:
 *  - Search
 */
class SearchController extends BaseController
{
    /**
     * Factory function, instantiates the controller (called by the server upon dispatch)
     */
    public static function init(): BaseController {
        return new SearchController();
    }

    /**
     * @return array List of routes for this controller.
     */
    public function routes(): array
    {
        return [
            self::route('GET', '/', 'search')
        ];
    }

    /**
     * Search endpoint
     */
    public function search($params) {
        if (array_key_exists('firstname', $_GET) && array_key_exists('lastname', $_GET)) {
            $this->showSearchResults($_GET['firstname'], $_GET['lastname']);
        } else {
            $this->showSearchPage();
        }
    }

    //--------------------

    private function showSearchPage() {
        $isResults = false;
        require "app/views/search.phtml";
    }

    private function showSearchResults(string $firstname, string $lastname) {
        $db = $this->getDBConn();
        $results = Person::search($db, $firstname, $lastname);

        if($results == null) {
            error_log(json_encode($db->errorInfo()));
            //$this->addFlashMessage("Unknown error during query!", self::FLASH_LEVEL_SERVER_ERR);
            $this->redirect('/search');
        }

        $isResults = true;
        require "app/views/search.phtml";
    }
}