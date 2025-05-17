<?php
require_once("../../CoreLibrary/CoreFunctions.php");

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
if ($current->loginstatus && !$current->accessstatus && !is_file($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $current->username . "/electives.txt"))
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
        <h6 class="text-muted">Please select your electives:</h6>
      </div>
      <form method="post" id="mainform" onsubmit="preventMisclick($('#submitbtn'))">
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