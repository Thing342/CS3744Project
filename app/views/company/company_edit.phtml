<?php
/**
 * The 'edit company' page
 * @var $company \app\models\Unit
 * @var $this \app\controllers\BaseController
 */
$title = $company->getName();

$js_init = 'init_ajax(\''. $_ENV['SUBDIRECTORY'] . '\',' . $company->getId() . ', true)';
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
            <h3>Upload Company Photo</h3>
            <img src="<?= $company->getPhotoFileURL() ?>" alt="Company Photo Thumbnail" class="img-thumbnail my-4 mw-75">
            <form class="form-inline" action="<?= $this->url('/companies/' . $company->getId()) . '/changePhoto' ?>"
                  method="post" enctype="multipart/form-data">
                <label for="companyPhotoUpload">New Company Photo:</label>
                <input type="file" class="ml-4" id="companyPhotoUpload" name="companyPhotoUpload" accept="image/jpeg">
                <input type="submit" class="btn btn-primary" value="Upload Photo">
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
            <h3>Notes</h3>
            <form id="noteAdd" class="p-4 my-4 border rounded container">
                <h6>Add New Note:</h6>
                <div class="form-group row">
                    <div class="form-group col col-12 col-lg-4">
                        <label for="eventName">Note Title</label>
                        <input type="text" class="form-control" name="eventName" placeholder="Name" required>

                        <hr class="my-4">

                        <label for="title">Image URL (optional)</label>
                        <input type="text" class="form-control" name="imageURL" placeholder="http://ec2-54-198-251-177.compute-1.amazonaws.com/CS3744Project/public/img/786th.jpg">

                        <input type="submit" class="btn btn-primary my-4 w-100" value="Add">
                    </div>
                    <div class="form-group col col-12 col-lg-8">
                        <label for="description">Text</label>
                        <textarea name="description" class="form-control w-100 h-100" placeholder="Note Text" required></textarea>
                    </div>
                </div>

            </form>
            <table class="table">
                <thead>
                <tr>
                    <th scope="col">Image</th>
                    <th scope="col">Title</th>
                    <th scope="col">Text</th>
                    <th scope="col">Action</th>
                </tr>
                </thead>
                <tbody id="notes-tbody">
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
