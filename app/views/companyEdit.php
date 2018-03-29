<?php
/**
 * @var $company \app\models\Unit
 * @var $members \app\models\Person[]
 * @var $events  \app\models\UnitEvent[]
 *
 * @var $this \app\controllers\BaseController
 */
$title = $company->getName();

$js_init = '';
$container_class = 'container';
$container_id = 'container';
include "app/views/_header.phtml"
?>
<div id = "whiteTextDiv">
    <a href="<?= $this->url('/companies') ?>">Back to List</a>
    <h2><?=$company->getName()?></h2>
    <form action="<?= $this->url('/companies/'. $company->getId()) ?>">
        <input type="submit" value="Done">
    </form>
    <form action="<?= $this->url('/companies/'. $company->getId()) . "/delete" ?>">
        <input type="submit" value="Delete">
    </form>
    <p></p>
    <form action="<?= $this->url('/companies/'. $company->getId()) . '/changeName' ?>" method="post">
        <input type="text" name="name" value="New Unit Name">
        <input type="submit" value="Done">
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
            <tbody>
                <?php foreach ($members as $person): ?>
                    <tr>
                        <td><?= $person->getRank() ?></td>
                        <td><?= $person->getFullName() ?></td>
                        <td>
                            <form action="<?= $this->url('/companies/' . $company->getId() . "/personDelete/" . $person->getId())?>" method="post">
                                <input type="submit" value="X">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr><form action="<?= $this->url('/companies/' . $company->getId() . "/personAdd/") ?>" method="post">
                        <td><input type="text" name="rank" value="Rank"></td>
                        <td><input type="text" name="firstname" value="First Name"><input type="text" name="lastname" value="Last Name"></td>
                        <td><input type="submit"></td>
                    </form></tr>
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
            </tr></thead>
            <tbody>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><!-- TODO: Load Image From AJAX --></td>
                        <td><?= $event->getEventName() ?></td>
                        <td><?= $event->getDate() ?></td>
                        <td><?= $event->getDescription() ?></td>
                        <td><?= $event->getLocationString() ?></td>
                        <td>
                            <form action="<?= $this->url('/companies/' . $company->getId() . "/eventDelete/" . $event->getId())?>" method="post">
                                <input type="submit" value="X">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr><form action="<?= $this->url('/companies/' . $company->getId() . "/eventAdd/") ?>" method="post">
                        <td><!-- Placeholder --></td>
                        <td><input type="text" name="eventName" value="Name"></td>
                        <td><input type="date" name="date" value="1944-01-01"></td>
                        <td><input type="text" name="description" value="Description"></td>
                        <td>
                            <input type="text" name="locationName" value="Location Name">
                            <input type="number" name="latitude" value="46.8207">
                            <input type="number" name="longitude" value="2.37545">
                        </td>
                        <td><input type="submit"></td>
                    </form></tr>
            </tbody>
        </table>
    </section>

</div>