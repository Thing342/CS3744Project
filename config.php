<?php
/**
 * Configuration file for the server.
 *
 * This is device-specific.
 */

$config = [
    "DB" => [ // Access information for MySQL
        'dbms' => 'mysql',
        'host' => 'localhost',
        'dbname' => 'fantasticfour_p4',
        'charset' => 'utf8mb4',
        'user' => 'fantasticfour',
        'pass' => 'cs3744'
    ],
    "Location" => "http://localhost:9999", // The absolute URL where this site is hosted. (TODO: change for submission)
    "Subdirectory" => "", // The subdirectory within /htdocs where the site is stored.
    "Server" => "builtin" // deployment method. using "builtin" turns on settings specific to the PHP built-in server
];
