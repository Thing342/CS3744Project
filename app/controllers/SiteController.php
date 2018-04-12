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