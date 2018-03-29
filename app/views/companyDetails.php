<?php
/**
 * @var $company \app\models\Unit
 * @var $members \app\models\Person[]
 * @var $events  \app\models\UnitEvent[]
 */
$title = $company->getName();

$js_init = '';
$container_class = 'container';
$container_id = 'container';
include "app/views/_header.phtml"
?>
<div id = "whiteTextDiv">
    <a href="<?= $_ENV['SUBDIRECTORY'] ?>/companies">Back to List</a>
    <h2><?=$company->getName()?></h2>

    <h3>Members</h3>
    <section>
        <table>
            <thead>
            <tr>
                <td>Rank</td>
                <td>Name</td>
            </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $person): ?>
                    <tr>
                        <td><?= $person->getRank() ?></td>
                        <td><?= $person->getFullName() ?></td>
                    </tr>
                <?php endforeach; ?>
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
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>

</div>