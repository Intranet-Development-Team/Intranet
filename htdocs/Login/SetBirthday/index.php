<?php
require_once("../../CoreLibrary/CoreFunctions.php");

$current = new Session("Set Birthday");

require_once("../../CoreLibrary/DatetimeHandlers.php");

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
<!DOCTYPE html>
<html>
<style>
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
    width: 20em;
    padding: 15px;
  }
</style>
<?= $current->getHtmlHead() ?>

<?php
if ($current->loginstatus && !$current->accessstatus && !is_file($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $current->username . "/birthday.txt"))
{
?>

  <body class="bg-body-tertiary m-0">
    <form method="post" id="logoutcurrent" onsubmit="preventMisclick($('#logoutbtn'))">
      <button type="button" class="btn text-danger" style="position:fixed;top:1em;left:1em;" onclick="confirmModal('Log out?','Are you sure to log out from current device?','$(\'#logoutcurrent\').submit()','Cancel','Log out')" id="logoutbtn"><i class="bi bi-box-arrow-right"></i> Log out</button>
      <input type="hidden" name="logout">
    </form>
    <main class="form-signin">
      <div class="text-center mb-3">
        <h1>Hi, <?= $current->username ?>.</h1>
        <h6 class="text-muted">Please fill in your birthday <br>to let others know more about you:</h6>
      </div>
      <form method="post" enctype="multipart/form-data" onsubmit="preventMisclick($('#submitbtn'))">
        <div class="form-floating mb-1">
          <input type="date" class="form-control" id="bday" name="birthday" placeholder="Birthday" value="<?= $_POST["birthday"] ?? "" ?>" onchange="if($('#bday').val()){$('#submitbtn').prop('disabled', false);}else{$('#submitbtn').prop('disabled', true);}" required>
          <label for="birthday">Birthday</label>
        </div>
        <small style="color:red;"><?= $error ?? "" ?></small>
        <br>
        <button class="w-100 btn btn-lg btn-primary" name="submitbtn" id="submitbtn" style="margin-top:0.2em;" disabled>Submit</button>
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