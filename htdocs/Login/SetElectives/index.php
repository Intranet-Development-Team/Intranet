<!DOCTYPE html>
<html>
<?php
$current = new Session("Set Electives");

$electives = SUBJECTS["electives"];

if ($current->loginstatus && !$current->accessstatus && !is_file($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $current->username . "/electives.txt"))
{
  if (isset($_POST["logout"]))
  {
    $current->logOut();
  }
  else if (!empty($_POST["x1"]) && !empty($_POST["x2"]) && !empty($_POST["x3"]))
  {
    if (in_array($_POST["x1"], $electives["x1"], true) && in_array($_POST["x2"], $electives["x2"], true) && in_array($_POST["x3"], $electives["x3"], true))
    {
      filewrite("../Accounts/" . $current->username . "/electives.txt", json_encode([$_POST["x1"], $_POST["x2"], $_POST["x3"]]));
      header("Location: https://" . SITE_DOMAIN . "/" . ($_GET["redirect"] ?? ""));
    }
    else
    {
      $error = "Please select valid electives.";
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
if ($current->loginstatus && !$current->accessstatus && !is_file($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $current->username . "/electives.txt"))
{
?>

  <body class="bg-body-tertiary">
    <form method="post" id="logoutcurrent">
      <button type="button" class="btn text-danger" style="position:fixed;top:1em;left:1em;" onclick="confirmModal('Log out?','Are you sure to log out from current device?','$(\'#logoutcurrent\').submit()','Cancel','Log out')"><i class="bi bi-box-arrow-right"></i> Log out</button>
      <input type="hidden" name="logout">
    </form>
    <main class="form-signin" id="pf">
      <h1>Hi, <?= $current->username ?>.</h1>
      <h6>Please select your electives:</h6><br>
      <form method="post" id="mainform">
        <select class="form-select form-select-lg mb-3" name="x1" id="x1" onchange="if($('#x1').val()&&$('#x2').val()&&$('#x3').val()){$('#submitbtn').prop('disabled', false);}else{$('#submitbtn').prop('disabled', true);}" required>
          <option disabled selected>X1</option>
          <?php
          foreach ($electives["x1"] as $elective)
          {
            echo '<option value="' . $elective . '">' . $elective . '</option>';
          }
          ?>
        </select>
        <select class="form-select form-select-lg mb-3" name="x2" id="x2" onchange="if($('#x1').val()&&$('#x2').val()&&$('#x3').val()){$('#submitbtn').prop('disabled', false);}else{$('#submitbtn').prop('disabled', true);}" required>
          <option disabled selected>X2</option>
          <?php
          foreach ($electives["x2"] as $elective)
          {
            echo '<option value="' . $elective . '">' . $elective . '</option>';
          }
          ?>
        </select>
        <select class="form-select form-select-lg mb-3" name="x3" id="x3" onchange="if($('#x1').val()&&$('#x2').val()&&$('#x3').val()){$('#submitbtn').prop('disabled', false);}else{$('#submitbtn').prop('disabled', true);}" required>
          <option disabled selected>X3</option>
          <?php
          foreach ($electives["x3"] as $elective)
          {
            echo '<option value="' . $elective . '">' . $elective . '</option>';
          }
          ?>
        </select>
      </form>
      <small style="color:red;"><?= $error ?? "" ?></small>
      <br>
      <button type="button" class="w-100 btn btn-lg btn-primary" name="submit" id="submitbtn" onclick="if($('#x1').val()&&$('#x2').val()&&$('#x3').val()){confirmModal('Confirm your electives', '<h6>Your electives will be:</h6><h5 class=\'mb-2\'>'+$('#x1').find(':selected').val()+'&nbsp;&nbsp;&nbsp;&nbsp;'+$('#x2').find(':selected').val()+'&nbsp;&nbsp;&nbsp;&nbsp;'+$('#x3').find(':selected').val()+'</h5><p>Your electives are used to provide accurate Assignments and Calendar Events feeds. Please note that you won\'t be able to change your electives after submitting.</p>','$(\'#mainform\').submit();','Cancel','Confirm Submission')}" style="margin-top:0.2em;" disabled>Submit</button>
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