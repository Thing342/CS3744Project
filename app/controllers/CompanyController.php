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