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
        'dbname' => 'fantasticfour_p6',
        'charset' => 'utf8mb4',
        'user' => 'fantasticfour',
        'pass' => 'cs3744'
    ],

    // Settings for submission
    "Location" => "http://localhost/cs3744/project6/fantasticfour", // The absolute URL where this site is hosted.
    "Subdirectory" => "/cs3744/project6/fantasticfour", // The subdirectory within /htdocs where the site is stored.
    "Server" => "apache", // deployment method. using "builtin" turns on settings specific to the PHP built-in server
];