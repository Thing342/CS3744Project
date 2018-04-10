<?php
/**
 * The 'edit user' page
 */
 $title = 'Edit Profile';

 $js_init = '';
 $container_class = 'container';
 $container_id = 'container';
 include "app/views/_header.phtml"
?>

<form method="post" action="<?= $_ENV['SUBDIRECTORY'] ?>/users/editUser">
  <input type="hidden" name="id" value="<?=$this->getLoggedInUser()->getUserId()?>">
  <h2 class="mt-4">Username: </h2>
  <input type="text" class="form-control mx-3" name="username2" value="<?= $this->getLoggedInUser()->getUsername() ?>" required minlength="7">
  <h2 class="mt-4">First Name: </h2>
  <input type="text" class="form-control mx-3" name="firstname2" value="<?= $this->getLoggedInUser()->getFirstname() ?>" required>
  <h2 class="mt-4">Last Name: </h2>
  <input type="text" class="form-control mx-3" name="lastname2" value="<?= $this->getLoggedInUser()->getLastname() ?>" required>
  <h2 class="mt-4">Email: </h2>
  <input type="text" class="form-control mx-3" name="email2" value="<?= $this->getLoggedInUser()->getEmail() ?>" required>
  <h2 class="mt-4">Password: </h2>
  <input type="password" class="form-control mx-3" name="password2" required minlength="7">
  <input id="privacy3" type="hidden" name="privacy2" value="<?= $this->getLoggedInUser()->getPrivacy() ?>">
  <div style="margin-bottom: 6em;margin-top: 1em;" class="nav-item dropdown">
        <a id="privacy2" style="display: inline;" name="privacy2" class="nav-link dropdown-toggle" role="button" data-toggle="dropdown" href="#" aria-haspopup="true" aria-expanded="false">
            <?= $this->getLoggedInUser()->getPrivacy() ?>
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
            <a id="public" class="dropdown-item" href="#">PUBLIC</a>
            <a id="private" class="dropdown-item" href="#">PRIVATE</a>
        </div>
  </div>
  <input type="submit" class="btn btn-primary" value="Change Details">
</form>

<?php
include "app/views/_footer.phtml"
?>
