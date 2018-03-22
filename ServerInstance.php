<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 2/24/18
 * Time: 10:57 AM
 */

namespace lib;

use PDO;

define('PUBLIC_MEDIA_TYPES', 'png|jpg|jpeg|gif|css|js|html');

session_start();

/**
 * Class ServerInstance
 * @package lib
 *
 * Main class representing an instance of our server.
 * Handles database connections, URL routing, and controller dispatch.
 */
class ServerInstance
{
    private $config = []; // Configuration dict
    private $controllers = []; // Maps url prefixes to controller factories.

    /**
     * ServerInstance constructor.
     * @param array $config Configuration dict
     * @param array $controllers Maps url prefixes to controller factories.
     */
    function __construct(array $config, array $controllers)
    {
        $this->config = $config;
        $this->controllers = $controllers;

        $_ENV['SUBDIRECTORY'] = $config['Subdirectory'];
    }

    /**
     * Route a request.
     * @return bool (for the PHP dev server only) true if the request is for a dynamic resource, false if the resource is static.
     */
    function run(): bool {
        $req_uri = explode("?", $_SERVER["REQUEST_URI"])[0];
        $req_uri = str_replace($this->config['Subdirectory'], '', $req_uri);

        // Check if we're requesting a static resource (PHP Builtin Only)
        if ($this->config['Server'] == 'builtin' && preg_match("/public\/([\w-_+\/]+\.(".PUBLIC_MEDIA_TYPES."))$/", $_SERVER["REQUEST_URI"])) {
            return false;
        }

        // Try and find a matching controller prefix, then dispatch the request to it.
        foreach ($this->controllers as $prefix => $factory) {
            $internal_len = strlen($prefix);
            if(substr($req_uri, 0, $internal_len) != $prefix) continue; //

            // Match found; instantiate the matching controller
            $controller = call_user_func($factory);

            // Connect database
            if (array_key_exists('DB', $this->config)) {
                $pdo = $this->config_db();
                $controller->setDBConn($pdo); // Pass DB connection to our new controller
            }

            // Dispatch the request to our new controller.
            $res = $controller->handle_route(substr($req_uri, $internal_len)); // Will be falsy if no matching route is found

            // No matching route in controller found, throw 404
            if(!$res) {
                http_response_code(404);
                include_once "app/404.php";
            }

            // Log request URL and return code
            $this->log_request();

            return true;
        }

        // No matching controller found; throw 404
        http_response_code(404);
        $this->log_request();

        include_once "app/404.php";
        return true;
    }

    /**
     * Log the URL, method, and response code for a request
     */
    static function log_request() {
        error_log( $_SERVER['REQUEST_METHOD'] . ": " . $_SERVER["REQUEST_URI"] . " [" . http_response_code() . "]");
    }

    /**
     * Configure the database and return a connection
     * @return PDO our database connection, ready to go
     */
    function config_db() : \PDO {
        // config['DB'] exists
        $dbcfg = $this->config["DB"];
        $opt = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        if(array_key_exists('opt', $dbcfg)) {
            $opt = $dbcfg["opt"];
        }

        $dsn = "${dbcfg['dbms']}:host=${dbcfg['host']};dbname=${dbcfg['dbname']};charset=${dbcfg['charset']}";
        return new \PDO($dsn, $dbcfg["user"], $dbcfg["pass"], $opt);
    }
}