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
        <a href="<?= $this->url('/companies') ?>">Back to List</a>
        <h2><?= $company->getName() ?></h2>
        <form action="<?= $this->url('/companies/' . $company->getId()) ?>">
            <input type="submit" value="Done Editing">
        </form>
        <br>
        <form method="post" action="<?= $this->url('/companies/' . $company->getId()) . "/delete" ?>"
              onsubmit="return confirm('Are you sure you want to delete this unit? (this cannot be undone)');">
            <input type="submit" value="Delete">
        </form>
        <p></p>
        <h3>Change Unit Name</h3>
        <form action="<?= $this->url('/companies/' . $company->getId()) . '/changeName' ?>" method="post">
            <input type="text" name="name" placeholder="<?= $company->getName() ?>" required>
            <input type="submit" value="Change Name">
        </form>
        <h3>Members</h3>
        <section>
            <table>
                <thead>
                <tr>
                    <td>Rank</td>
                    <td>Name</td>
                    <td></td>
                </tr>
                </thead>
                <tbody id="members-tbody">
                <tr>
                    <form id="memberAdd">
                        <td><input type="text" name="rank" placeholder="Rank" required></td>
                        <td><input type="text" name="firstname" placeholder="First Name" style="margin-right: 10px"
                                   required><input type="text" name="lastname" placeholder="Last Name" required></td>
                        <td><input type="submit" value="Add"></td>
                    </form>
                </tr>
                <!--
                    AJAX
                -->
                </tbody>
            </table>
        </section>

        <h3>Events</h3>
        <section>
            <table>
                <thead>
                <tr>
                    <td>Image</td>
                    <td>Name</td>
                    <td>Date</td>
                    <td>Description</td>
                    <td>Location (Lat, Lon)</td>
                    <td>Action</td>
                </tr>
                </thead>
                <tbody id="events-tbody">
                <tr>
                    <form id="eventAdd">
                        <td><select name="type" id="" required>
                                <option value="event">Event</option>
                                <option value="battle">Battle</option>
                                <option value="diary">Diary</option>
                            </select></td>
                        <td><input type="text" name="eventName" placeholder="Name" required></td>
                        <td><input type="date" name="date" value="1944-01-01" required></td>
                        <td><textarea name="description" placeholder="Description of Event" style="width: 100%"
                                      required></textarea></td>
                        <td>
                            <input type="text" name="locationName" placeholder="Location Name"
                                   style="margin-bottom: 10px" required>
                            <input type="number" name="latitude" placeholder="46.8207" step="any"
                                   style="margin-bottom: 10px" required>
                            <input type="number" name="longitude" placeholder="-2.37545" step="any" required>
                        </td>
                        <td><input type="submit" value="Add"></td>
                    </form>
                </tr>
                <!--
                    AJAX
                -->
                </tbody>
            </table>
        </section>
        <br>
        <form action="<?= $this->url('/companies/' . $company->getId()) ?>">
            <input type="submit" value="Done Editing">
        </form>

        <script src="<?= $this->url('/public/js/company_ajax.js" type="application/javascript') ?>"></script>
    </div>
<?php
include "app/views/_footer.phtml"
?>