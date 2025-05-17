<?php
require_once("../../CoreLibrary/CoreFunctions.php");

$current = new Session("Change Password");
if ($current->loginstatus && !$current->accessstatus && !is_file($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $current->username . "/firstlogin.txt"))
{
  if (isset($_POST["logout"]))
  {
    $current->logOut();
  }
  else if (!empty($_POST["password"]) && !empty($_POST["confirmpassword"]))
  {
    if (mb_strlen($_POST["password"]) >= 8 && preg_match('/[A-Z]/', $_POST["password"]) && preg_match('/[a-z]/', $_POST["password"]) && preg_match('/[0-9]/', $_POST["password"]))
    {
      if ($_POST["password"] === $_POST["confirmpassword"])
      {
        if (hash("sha3-512", $_POST["password"]) !== fileread("../Accounts/" . $current->username . "/password.txt"))
        {
          filewrite("../Accounts/" . $current->username . "/password.txt", hash("sha3-512", $_POST["password"]));
          filewrite("../Accounts/" . $current->username . "/firstlogin.txt", "");
          header("Location: https://" . SITE_DOMAIN . "/" . ($_GET["redirect"] ?? ""));
        }
        else
        {
          $error = "The new and the old passwords are the same.";
        }
      }
      else
      {
        $error = "The confirmation password is different.";
      }
    }
    else
    {
      $error = "The new password doesn't meet the strength require_oncements.";
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
if ($current->loginstatus && !$current->accessstatus && !is_file($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $current->username . "/firstlogin.txt"))
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
        <h5>This is your first login.</h5>
        <h6 class="text-muted">To protect your account,<br>please change your password below:</h6>
      </div>
      <form method="post" enctype="multipart/form-data" onsubmit="preventMisclick($('#submitbtn'))">
        <div class="form-floating mb-1">
          <input type="password" class="form-control" id="password" name="password" placeholder="New password" onkeyup="showHint(this.value);if(showHint($('#password').val())&&ifmatches()){$('#submitbtn').prop('disabled', false);}else{$('#submitbtn').prop('disabled', true);}" autofocus required>
          <label for="password">New password</label>
        </div>
        <p id="hint" style="text-align:left;font-size:0.75em;margin-top:0.2em;"></p>
        <div class="form-floating mb-1">
          <input type="password" class="form-control" id="confirmpassword" name="confirmpassword" placeholder="Confirm new password" onkeyup="ifmatches();if(showHint($('#password').val())&&ifmatches()){$('#submitbtn').prop('disabled', false);}else{$('#submitbtn').prop('disabled', true);}" required>
          <label for="confirmpassword">Confirm new password</label>
        </div>
        <p id="hint2" style="text-align:left;font-size:0.75em;margin-top:0.2em;color:red;"><?= $error ?? "" ?></p>
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" onclick="spw()" id="spch">
          <label class="form-check-label" for="spch">Show passwords</label>
        </div>
        <button class="w-100 btn btn-lg btn-primary" name="submitbtn" id="submitbtn" style="margin-top:0.2em;" disabled>Change</button>
      </form>
    </main>
  <?php
}
else if ($current->accessstatus)
{
  header("Location: https://" . SITE_DOMAIN . "/" . ($_GET["redirect"] ?? ""));
  exit();
}
  ?>
  <script>
    function spw() {
      var x = document.getElementById("password");
      var x1 = document.getElementById("confirmpassword");
      if (x.type === "text") {
        x.type = "password";
        x1.type = "password";
      } else if (x.type === "password") {
        x.type = "text";
        x1.type = "text";
      }
    }

    function ifmatches() {
      if ($("#password").val() !== $("#confirmpassword").val() && $("#confirmpassword").val() !== "") {
        document.getElementById("hint2").style.color = "red";
        document.getElementById("hint2").innerHTML = "&#10060; Confirm password do not match.<br>";
      } else {
        document.getElementById("hint2").innerHTML = "";
        if ($("#password").val() === $("#confirmpassword").val()) {
          return true;
        }
      }
    }

    function showHint(str) {
      ifmatches();
      if (str.length >= 8 && (/\d/.test(str)) == true && (/[A-Z]/.test(str)) == true && (/[a-z]/.test(str)) == true) {
        document.getElementById("hint").style.color = (str.length >= 16 ? "#177a2e" : "#1570d1");
        document.getElementById("hint").innerHTML = (str.length >= 16 ? "Strong password" : "Good password") + "<br>";
        return true;
      } else if (str.length == 0) {
        document.getElementById("hint").innerHTML = "";
      } else {
        document.getElementById("hint").innerHTML = "";
        if (str.length < 8) {
          document.getElementById("hint").style.color = "red";
          document.getElementById("hint").innerHTML += "&#10060; At least 8 characters long.<br>";
        }
        if (/\d/.test(str) == false) {
          document.getElementById("hint").style.color = "red";
          document.getElementById("hint").innerHTML += "&#10060; Must include a number.<br>";
        }
        if (/[A-Z]/.test(str) == false) {
          document.getElementById("hint").style.color = "red";
          document.getElementById("hint").innerHTML += "&#10060; Must include a capital letter.<br>";
        }
        if (/[a-z]/.test(str) == false) {
          document.getElementById("hint").style.color = "red";
          document.getElementById("hint").innerHTML += "&#10060; Must include a small letter.<br>";
        }
      }
    }
  </script>
  </body>

</html>