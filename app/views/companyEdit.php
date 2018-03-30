<?php
/**
 * @var $company \app\models\Unit
 * @var $members \app\models\Person[]
 * @var $events  \app\models\UnitEvent[]
 *
 * @var $this \app\controllers\BaseController
 */
$title = $company->getName();

$js_init = 'init_ajax('.$company->getId().', true)';
$container_class = 'container';
$container_id = 'container';
include "app/views/_header.phtml"
?>
<div id = "whiteTextDiv">
    <a href="<?= $this->url('/companies') ?>">Back to List</a>
    <h2><?=$company->getName()?></h2>
    <form action="<?= $this->url('/companies/'. $company->getId()) ?>">
        <input type="submit" value="Done Editing">
    </form>
    <form method="post" action="<?= $this->url('/companies/'. $company->getId()) . "/delete" ?>" onsubmit="return confirm('Are you sure you want to delete this unit? (this cannot be undone)');">
        <input type="submit" value="Delete">
    </form>
    <p></p>
    <h3>Change Unit Name</h3>
    <form action="<?= $this->url('/companies/'. $company->getId()) . '/changeName' ?>" method="post">
        <input type="text" name="name" value="<?= $company->getName() ?>">
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
                <tr><form id="memberAdd">
                        <td><input type="text" name="rank" value="Rank"></td>
                        <td><input type="text" name="firstname" value="First Name"><input type="text" name="lastname" value="Last Name"></td>
                        <td><input type="submit" value="Add"></td>
                    </form></tr>
                <!--
                    AJAX
                -->
            </tbody>
        </table>
    </section>

    <h3>Events</h3>
    <section>
        <table>
            <thead><tr>
                <td>Image</td>
                <td>Name</td>
                <td>Date</td>
                <td>Description</td>
                <td>Location (Lat, Lon)</td>
                <td>Action</td>
            </tr></thead>
            <tbody id="events-tbody">
                <tr><form id="eventAdd">
                        <td><select name="type" id="" required>
                                <option selected disabled>Select an Event Type...</option>
                                <option value="battle">Battle</option>
                                <option value="diary">Diary</option>
                                <option value="event">Event</option>
                            </select></td>
                        <td><input type="text" name="eventName" value="Name"></td>
                        <td><input type="date" name="date" value="1944-01-01"></td>
                        <td><input type="text" name="description" value="Description"></td>
                        <td>
                            <input type="text" name="locationName" value="Location Name">
                            <input type="number" name="latitude" value="46.8207">
                            <input type="number" name="longitude" value="2.37545">
                        </td>
                        <td><input type="submit" value="Add"></td>
                    </form></tr>
                <!--
                    AJAX
                -->
            </tbody>
        </table>
    </section>

    <script src="/public/js/company_ajax.js" type="application/javascript"></script>
</div>
<?php
include "app/views/_footer.phtml"
?>