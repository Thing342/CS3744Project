<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 2/22/18
 * Time: 2:30 PM
 */

namespace lib;

require_once "util.php";

/**
 * Class Controller
 * @package lib
 *
 * Abstract base class for a controller. Intended to be re-usable between projects.
 * Handles internal routing, redirecting, and database connection management.
 */
abstract class Controller {
    public abstract function routes(): array; // Child classes return a list of possible routes to match against

    private $pdo = null;
    private $post_json = null;

    /**
     * Helper function for generating a route object, to be called by children.
     * @param string $method - HTTP method to match against
     * @param string $pattern - URL pattern to check for
     * @param string $func - function in controller to call
     * @return array A formatted route object.
     */
    protected function route(string $method, string $pattern, string $func) {
        return [
            "method" => $method, "pattern" => $pattern, "func" => [$this, $func]
        ];
    }

    public function url(string $internal): string {
        return $_ENV['SUBDIRECTORY'] . $internal;
    }

    /**
     * Handles a routed request.
     * @param string $internalpath The 'internal' path (stripped of the controller prefix) to check against
     * @return bool Truthy if a match was found, falsy otherwise.
     */
    public function handle_route(string $internalpath): bool {
        // Add leading slash, if not present:
        if(substr($internalpath, 0, 1) != "/") {
            $internalpath = "/".$internalpath;
        }

        // Match against all paths provided by child
        foreach ($this->routes() as $route) {
            if ($route["method"] == $_SERVER["REQUEST_METHOD"] and
                preg_match(gen_regex($route["pattern"]), $internalpath, $matches) == 1) {

                call_user_func($route["func"], $matches);
                return true;
            }
        }

        // no match.
        return false;
    }

    /**
     * Set the DB object used by this controller
     * @param \PDO $pdo - PDO connection to the database
     */
    public function setDBConn(\PDO $pdo): void
    {
        if ($this->pdo != null) {
            error_log("Cannot re-set DB for controller: ".get_class($this));
        }

        $this->pdo = $pdo;
    }

    /**
     * @return \PDO this controller's database connection
     */
    public function getDBConn(): \PDO
    {
        return $this->pdo;
    }

    /**
     * Throw a 404 error.
     * @param string $req_uri - The intended URL
     */
    protected function error404(string $req_uri) {
        http_response_code(404);
        include_once "app/404.php";
    }

    /**
     * Redirect the client to a LOCAL url.
     * @param string $new_url - The local url to redirect to
     */
    protected function redirect(string $new_url) {
        require "config.php";
        header("location: ".$config['Location'].$new_url);
        die();
    }

    // Flash severity levels, equivalent to CSS classes
    public const FLASH_LEVEL_SERVER_ERR = 'alert-dark';
    public const FLASH_LEVEL_USER_ERR = 'alert-danger';
    public const FLASH_LEVEL_WARN = 'alert-warning';
    public const FLASH_LEVEL_INFO = 'alert-info';
    public const FLASH_LEVEL_SUCCESS = 'alert-success';

    /**
     * Post a flash message, a short notice that will disappear on the user's next page reload
     * @param string $msg - The message
     * @param string $flash_level - The severity level
     * @return bool - If the operation was successful.
     */
    protected function addFlashMessage(string $msg, string $flash_level): bool {
        if (session_id() == '') return false;
        if (!isset($_SESSION)) return false;

        $flash = ['level'=>$flash_level, 'msg'=>$msg];

        if (!array_key_exists('FLASH', $_SESSION)) {
            $_SESSION['FLASH'] = [$flash];
        } else {
            array_push($_SESSION['FLASH'], $flash);
        }

        return true;
    }

    /**
     * Retrieve the list of stored flash messages and drain the queue.
     * @return array - an array of flash dictionaries.
     */
    protected function showFlashMessages(): array {
        if (session_id() == '') return [];
        if (!isset($_SESSION)) return [];
        if (!array_key_exists('FLASH', $_SESSION)) return [];

        $flashes = $_SESSION['FLASH'];
        unset($_SESSION['FLASH']);
        return $flashes;
    }

    /**
     * Attempt to parse the JSON body of a POST request, if it exists.
     * @return array|null - The content of the body, pushed into a dictionary, if it exists; otherwise null.
     */
    protected function get_post_json(): ?array {
        if ($this->post_json != null) return $this->post_json; // Use cached result, if possible.
        else {
            # Get JSON as a string
            $json_str = file_get_contents('php://input');

            # Get as an object
            $this->post_json = json_decode($json_str, true);
            return $this->post_json;
        }
    }
}