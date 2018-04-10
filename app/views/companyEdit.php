<?php
/**
 * The 'edit company' page
 * @var $company \app\models\Unit
 * @var $this \app\controllers\BaseController
 */
$title = $company->getName();

$js_init = 'init_ajax(' . $company->getId() . ', true)';
$container_class = 'container';
$container_id = 'container';
include "app/views/_header.phtml"
?>
    <div id="whiteTextDiv">
        <section class="my-4">
            <h2><?= $company->getName() ?></h2>
            <div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups">
                <div class="btn-group mr-2" role="group" aria-label="First group">
                    <a class="btn btn-primary" href="<?= $this->url('/companies/' . $company->getId()) ?>">Done Editing</a>
                    <a class="btn btn-secondary" href="<?= $_ENV['SUBDIRECTORY'] ?>/companies">Back to List</a>
                </div>
                <div class="btn-group mr-2" role="group" aria-label="second group">
                    <form style="display: inline" method="post" action="<?= $this->url('/companies/' . $company->getId()) . "/delete" ?>"
                          onsubmit="return confirm('Are you sure you want to delete this unit? (this cannot be undone)');">
                        <input class="btn btn-danger" type="submit" value="Delete">
                    </form>
                </div>
            </div>

        </section>

        <section class="my-4">
            <h3>Change Unit Name</h3>
            <form class="form-inline" action="<?= $this->url('/companies/' . $company->getId()) . '/changeName' ?>" method="post">
                <label for="name">New Name:</label>
                <input type="text" class="form-control mx-3" name="name" placeholder="<?= $company->getName() ?>" required>
                <input type="submit" class="btn btn-primary" value="Change Name">
            </form>
        </section>

        <section class="my-4">
            <h3>Members</h3>
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">Rank</th>
                    <th scope="col">Name</th>
                    <th scope="col"></th>
                </tr>
                </thead>
                <tbody id="members-tbody">
                <tr>
                    <td colspan="2" class="container-fluid">
                        <form class="form-inline row justify-content-between" id="memberAdd">
                            <div class="col-11">
                                <input type="text" class="form-control mx-2" name="rank" placeholder="Rank" required>
                                <input type="text" class="form-control mx-2" name="firstname" placeholder="First Name" required>
                                <input type="text" class="form-control mx-2" name="lastname" placeholder="Last Name" required>
                            </div>
                            <input type="submit" class="btn btn-primary col-1" value="Add">
                        </form>
                    </td>
                </tr>
                <!--
                    AJAX
                -->
                </tbody>
            </table>
        </section>

        <section>
            <h3>Events</h3>
            <form id="eventAdd" class="p-4 my-4 border rounded container">
                <h6>Add New Event:</h6>
                <div class="form-group row">
                    <div class="form-group col col-12 col-lg-4">
                        <label for="type">Event Type</label>
                        <select class="form-control" name="type" id="" required>
                            <option value="event">Event</option>
                            <option value="battle">Battle</option>
                            <option value="diary">Diary</option>
                        </select>
                        <label for="eventName">Name</label>
                        <input type="text" class="form-control" name="eventName" placeholder="Name" required>
                        <label for="date">Event Date</label>
                        <input type="date" class="form-control" name="date" value="1944-01-01" required>

                        <hr class="my-4">

                        <label for="locationName" >Location</label>
                        <input type="text" class="form-control" name="locationName" placeholder="Location Name" required>

                        <label for="latitude">Latitude</label>
                        <input type="number" class="form-control" name="latitude" placeholder="46.8207" step="any" required>
                        <label for="longitude">Longitude</label>
                        <input type="number" class="form-control" name="longitude" placeholder="-2.37545" step="any" required>

                        <input type="submit" class="btn btn-primary my-4 w-100" value="Add">
                    </div>
                    <div class="form-group col col-12 col-lg-8">
                        <label for="description">Event Notes</label>
                        <textarea name="description" class="form-control w-100 h-100" placeholder="Description of Event" required></textarea>
                    </div>
                </div>

            </form>
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">Image</th>
                    <th scope="col">Name</th>
                    <th scope="col">Date</th>
                    <th scope="col">Description</th>
                    <th scope="col">Location (Lat, Lon)</th>
                    <th scope="col">Action</th>
                </tr>
                </thead>
                <tbody id="events-tbody">
                <!--
                    AJAX
                -->
                </tbody>
            </table>
        </section>
        <section>
            <div class="btn-toolbar" role="toolbar" aria-label="Toolbar with button groups">
                <div class="btn-group mr-2" role="group" aria-label="First group">
                    <a class="btn btn-primary" href="<?= $this->url('/companies/' . $company->getId()) ?>">Done Editing</a>
                    <a class="btn btn-secondary" href="<?= $_ENV['SUBDIRECTORY'] ?>/companies">Back to List</a>
                    <a class="btn btn-secondary" href="#">Back to Top</a>
                </div>
            </div>
        </section>

        <script src="<?= $this->url('/public/js/company_ajax.js" type="application/javascript') ?>"></script>
    </div>
<?php
include "app/views/_footer.phtml"
?>
