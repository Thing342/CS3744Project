<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 2/22/18
 * Time: 2:33 PM
 */

namespace app\controllers;

require_once "app/controllers/BaseController.php";

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
            self::route("GET", "/", 'index'),
        ];
    }

    /**
     * Full path: '/' (site root)
     */
    public function index($params) {
        require "app/views/index.phtml";
    }
}