<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 2/22/18
 * Time: 9:14 AM
 *
 * The main server configuration file, declares controllers used in the project.
 * Execution code begins here.
 */

set_include_path(".");

require "config.php";

require_once "app/controllers/SiteController.php";
require_once "app/controllers/UserController.php";

require_once "ServerInstance.php";

// Maps a URL subsection to a controller factory method handle.
$controllers = [
    "/users" => "\app\controllers\UserController::init",
    "" => '\app\controllers\SiteController::init',
];

// Runs the routing script.
$server = new \lib\ServerInstance($config, $controllers);
return $server->run();