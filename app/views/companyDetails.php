<?php
/**
 * The company details page.
 *
 * @var $company \app\models\Unit
 * @var $this \app\controllers\CompanyController
 */
$title = $company->getName();

$js_init = 'init_ajax('.$company->getId().', false)';
$container_class = 'container';
$container_id = 'container';
include "app/views/_header.phtml"
?>
<div id = "whiteTextDiv">
    <section class="border rounded p-4 mt-4 mb-4">
        <!-- Display "Edit" and "Delete" buttons if logged in -->
        <h2><?=$company->getName()?></h2>
        <?php if ($this->is_logged_in()): ?>
            <a href="<?= $this->url('/companies/'. $company->getId() .'/edit') ?>" class="btn btn-primary">Edit</a>
        <?php else: ?>
            <p><i>Log in to edit this page.</i></p>
        <?php endif; ?>
        <a class="btn btn-secondary" href="<?= $_ENV['SUBDIRECTORY'] ?>/companies">Back to List</a>
    </section>

    <div class="row">
        <section class="col col-12 col-lg-4">
            <h3>Members</h3>
            <table class="table">
                <thead>
                <tr>
                    <th scope="col"><b>Rank</b></th>
                    <th scope="col"><b>Name</b></th>
                </tr>
                </thead>
                <tbody id="members-tbody">
                <!--
                    AJAX
                -->
                </tbody>
            </table>
        </section>

        <section class="col col-12 col-md-8">
            <h3>Events</h3>

            <ul class="list-unstyled list-group" id="events-tbody">
                <!--
                    AJAX
                -->
            </ul>
        </section>
    </div>


</div>

<script src="<?= $this->url('/public/js/company_ajax.js" type="application/javascript') ?>"></script>
<?php
include "app/views/_footer.phtml"
?>