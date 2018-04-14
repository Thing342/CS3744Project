<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>404 | In Their Own Words</title>
    <!-- Bootstrap CSS -->
    <!--link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"-->
    <link rel="stylesheet" href="https://bootswatch.com/4/solar/bootstrap.min.css">

    <!-- Our Stylesheet -->
    <link rel="stylesheet" href="<?= $_ENV['SUBDIRECTORY'] ?>/public/css/style-bs.css"/>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <script src="<?= $_ENV['SUBDIRECTORY'] ?>/public/js/scripts-jquery.js"></script>
</head>
<body>
<h1>404: Not Found</h1>
<?php
/**
 * Created by PhpStorm.
 * User: wes
 * Date: 2/22/18
 * Time: 9:45 AM
 *
 * A 404 error.
 */
echo "<p>The server could not find the specified file: $req_uri</p>"
?>
</body>
</html>