# CS3744Project
Group Project for CS3744

To deploy on XAMPP:
* Clone / copy this project into a subdirectory of `/htdocs`, Make sure to note the subdirectory.
* Use PHPMyAdmin to import the `db_init.sql` script.
* Edit `config.php`:
    * Edit the `DB` field to set your DB credentials. The db script comes with a user included, so these should noramlly not
     need to be edited.
     * Edit the `Location` field to set the root URL of your location. this should be of the form
     `http://<apache url>:<apache port>/<path>/<to>/<application>`.
     * Edt the `Subdirectory` field to set the internal subdirectory of the application. This
     should match the subdirectory in the url above (stripping away the url/port stuff). 
     * Edit the `Server` field to say `apache`.

To deploy using PHP dev server:
* Clone / copy the project to any directory you want.
* Use `mysql -u root -p < db_init.sql` to import the database.
* Start the PHP dev server using `php -S localhost:9999 -t . ./server.php`