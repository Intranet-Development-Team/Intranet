<!DOCTYPE html>
<html>
<?php
$current = new Session("Set Birthday");

require("../../CoreLibrary/DatetimeHandlers.php");

if ($current->loginstatus && !$current->accessstatus && !is_file($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $current->username . "/birthday.txt"))
{
  if (isset($_POST["logout"]))
  {
    $current->logOut();
  }
  else if (!empty($_POST["birthday"]))
  {
    if (validateDatetime($_POST["birthday"], "Y-m-d"))
    {
      $current->setBirthday(strtotime($_POST["birthday"]));
      header("Location: https://" . SITE_DOMAIN . "/" . ($_GET["redirect"] ?? ""));
    }
    else
    {
      $error = "Please enter a valid date.";
    }
  }
}
?>
<style>
  .form-signin {
    text-align: center;
  }

  .bd-placeholder-img {
    font-size: 1.125rem;
    text-anchor: middle;
    -webkit-user-select: none;
    -moz-user-select: none;
    user-select: none;
  }

  @media (min-width: 768px) {
    .bd-placeholder-img-lg {
      font-size: 3.5rem;
    }
  }

  html,
  body {
    overflow-x: hidden;
    /* Prevent scroll on narrow devices */
  }

  @media (max-width: 767.98px) {
    .offcanvas-collapse {
      position: fixed;
      top: 56px;
      /* Height of navbar */
      bottom: 0;
      width: 100%;
      padding-right: 1rem;
      padding-left: 1rem;
      overflow-y: auto;
      background-color: var(--gray-dark);
      transition: -webkit-transform .3s ease-in-out;
      transition: transform .3s ease-in-out;
      transition: transform .3s ease-in-out, -webkit-transform .3s ease-in-out;
      -webkit-transform: translateX(100%);
      transform: translateX(100%);
    }

    .offcanvas-collapse.open {
      -webkit-transform: translateX(-1rem);
      transform: translateX(-1rem);
      /* Account for horizontal padding on navbar */
    }
  }

  .nav-scroller {
    position: relative;
    z-index: 2;
    height: 2.75rem;
    overflow-y: hidden;
  }

  .nav-scroller .nav {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -ms-flex-wrap: nowrap;
    flex-wrap: nowrap;
    padding-bottom: 1rem;
    margin-top: -1px;
    overflow-x: auto;
    color: rgba(255, 255, 255, .75);
    text-align: center;
    white-space: nowrap;
    -webkit-overflow-scrolling: touch;
  }

  .nav-underline .nav-link {
    padding-top: .75rem;
    padding-bottom: .75rem;
    font-size: .875rem;
    color: var(--secondary);
  }

  .nav-underline .nav-link:hover {
    color: var(--blue);
  }

  .nav-underline .active {
    font-weight: 500;
    color: var(--gray-dark);
  }

  .text-white-50 {
    color: rgba(255, 255, 255, .5);
  }

  .bg-purple {
    background-color: var(--purple);
  }

  .border-bottom {
    border-bottom: 1px solid #e5e5e5;
  }

  .box-shadow {
    box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .05);
  }

  .lh-100 {
    line-height: 1;
  }

  .lh-125 {
    line-height: 1.25;
  }

  .lh-150 {
    line-height: 1.5;
  }

  html,
  body {
    height: 100%;
  }

  body {
    display: flex;
    align-items: center;
  }

  .form-signin {
    display: block;
    margin-left: auto;
    margin-right: auto;
    min-width: 20%;
  }
</style>
<?= $current->getHtmlHead() ?>
<?php
if ($current->loginstatus && !$current->accessstatus && !is_file($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $current->username . "/birthday.txt"))
{
?>

  <body class="bg-body-tertiary">
    <form method="post" id="logoutcurrent">
      <button type="button" class="btn text-danger" style="position:fixed;top:1em;left:1em;" onclick="confirmModal('Log out?','Are you sure to log out from current device?','$(\'#logoutcurrent\').submit()','Cancel','Log out')"><i class="bi bi-box-arrow-right"></i> Log out</button>
      <input type="hidden" name="logout">
    </form>
    <main class="form-signin" id="pf">
      <h1>Hi, <?= $current->username ?>.</h1>
      <h6>Please fill in your birthday <br>to let others know more about you:</h6><br>
      <form method="post" enctype="multipart/form-data">
        <div class="form-floating mb-1">
          <input type="date" class="form-control" id="bday" name="birthday" placeholder="Birthday" value="<?= $_POST["birthday"] ?? "" ?>" required>
          <label for="birthday">Birthday</label>
        </div>
        <small style="color:red;"><?= $error ?? "" ?></small>
        <br>
        <button class="w-100 btn btn-lg btn-primary" name="submit" id="log" onclick="preventMisclick($(this))" style="margin-top:0.2em;">Submit</button>
      </form>
    </main>
  <?php
}
else if ($current->accessstatus)
{
  header("Location: https://" . SITE_DOMAIN . "/" . ($_GET["redirect"] ?? ""));
  exit();
} ?>
  </body>

</html>