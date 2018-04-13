<?php
/**
 * The company details page.
 *
 * @var $company \app\models\Unit
 * @var $comments \app\models\Comment[]
 * @var $this \app\controllers\CompanyController
 */
$title = $company->getName();

$js_init = 'init_ajax('.$company->getId().', false)';
$container_class = 'container';
$container_id = 'container';
include "app/views/_header.phtml"
?>
<script type="application/javascript">
    function deleteComment(commentID) {
        var action = '<?= $this->url('/companies/' . $company->getId() . '/deleteComment/') ?>' + commentID;
        if (confirm("Really delete this comment? This cannot be undone.")) {
            window.location.href = action;
        }
    }
</script>

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

    <div class="row">
        <section class="col col-12">
            <h4 class="my-2">Comments</h4>
            <ul class="list-unstyled">
                <?php foreach ($comments as $comment): ?>
                <li class="media p-2 my-2">

                    <?php if($this->getLoggedInUser()->getType() >= \app\models\User::TYPE_EDITOR): ?>
                        <button type="button" class="close mx-2" aria-label="Close" onclick="deleteComment(<?= $comment->getId() ?>)">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    <?php endif; ?>

                    <div class="media-body">
                        <?php
                            $user = $comment->getUser();
                            if($user->getType() == \app\models\User::TYPE_ADMIN) {
                                echo "<h5 class='mt-0 text-danger'> " . $comment->getUser()->getFullName() . " (" . $comment->getUser()->getUsername() . ")";
                                echo "<span class=\"ml-2 badge badge-secondary\">Administrator</span>";
                                echo "</h5>";
                            } elseif($user->getType() == \app\models\User::TYPE_EDITOR) {
                                echo "<h5 class='mt-0 text-primary'> " . $comment->getUser()->getFullName() . " (" . $comment->getUser()->getUsername() . ")";
                                echo "<span class=\"ml-2 badge badge-secondary\">Moderator</span>";
                                echo "</h5>";
                            } else {
                                echo "<h5 class='mt-0'> " . $comment->getUser()->getFullName() . " (" . $comment->getUser()->getUsername() . ")";
                                echo "</h5>";
                            }
                        ?>
                        <p><?= $comment->getTimestamp() ?></p>
                        <p><?= $comment->getText() ?></p>
                    </div>
                </li>
                <?php endforeach; ?>

                <?php if ($this->is_logged_in() && $this->getLoggedInUser()->getType() >= \app\models\User::TYPE_COMMENTER): ?>
                <li class="p-2 my-2">
                    <form action="<?= $this->url('/companies/' . $company->getId() . '/submitComment') ?>" method="post">
                        <div class="form-group">
                            <label for="commentText">Add New Comment</label>
                            <textarea class="form-control" id="commentText" name="commentText" rows="5" placeholder="Add a comment..."></textarea>
                            <small id="commentRulesText" class="form-text text-muted">
                                Please be sure that your comment adheres to our site rules before posting. Otherwise,
                                it will likely be flagged and deleted by a moderator or administrator.
                            </small>
                            <input type="submit" class="btn btn-primary">
                            <a href="#" class="btn btn-secondary">Back to top</a>
                        </div>
                    </form>
                </li>
                <?php else: ?>
                <i>You must be <a href="<?= $this->url('/users/login') ?>">logged in</a> to leave a comment.</i>
                <?php endif; ?>

            </ul>
        </section>
    </div>

</div>

<script src="<?= $this->url('/public/js/company_ajax.js" type="application/javascript') ?>"></script>
<?php
include "app/views/_footer.phtml"
?>