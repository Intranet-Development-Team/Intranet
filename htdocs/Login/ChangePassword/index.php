<!DOCTYPE html>
<html>
<?php
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
      $error = "The new password doesn't meet the strength requirements.";
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
if ($current->loginstatus && !$current->accessstatus && !is_file($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $current->username . "/firstlogin.txt"))
{
?>

  <body class="bg-body-tertiary">
    <form method="post" id="logoutcurrent">
      <button type="button" class="btn text-danger" style="position:fixed;top:1em;left:1em;" onclick="confirmModal('Log out?','Are you sure to log out from current device?','$(\'#logoutcurrent\').submit()','Cancel','Log out')"><i class="bi bi-box-arrow-right"></i> Log out</button>
      <input type="hidden" name="logout">
    </form>
    <main class="form-signin" id="pf">
      <h1>Hi, <?= $current->username ?>.</h1>
      <h5 style="padding-top:0.2em;padding-bottom:0.2em;">This is your first login.</h5>
      <h6>To protect your account,<br>please change your password below:</h6><br>
      <form method="post" enctype="multipart/form-data">
        <div class="form-floating mb-1">
          <input type="password" class="form-control" id="password" name="password" placeholder="New password" onkeyup="showHint(this.value)" autofocus required>
          <label for="password">New password</label>
        </div>
        <p id="hint" style="text-align:left;font-size:0.75em;margin-top:0.2em;"></p>
        <div class="form-floating mb-1">
          <input type="password" class="form-control" id="confirmpassword" name="confirmpassword" placeholder="Confirm new password" onkeyup="ifmatches()" required>
          <label for="confirmpassword">Confirm new password</label>
        </div>
        <p id="hint2" style="text-align:left;font-size:0.75em;margin-top:0.2em;color:red;"><?= $error ?? "" ?></p>
        <div class="form-check" style="text-align:left;">
          <input class="form-check-input" type="checkbox" onclick="spw()" id="spch">
          <label class="form-check-label" for="spch">Show passwords</label>
        </div>
        <button class="w-100 btn btn-lg btn-primary" name="submit" id="log" onclick="preventMisclick($(this))" style="margin-top:0.2em;">Change</button>
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
      if (document.getElementById("password").value !== document.getElementById("confirmpassword").value && document.getElementById("confirmpassword").value !== "") {
        document.getElementById("hint2").style.color = "red";
        document.getElementById("hint2").innerHTML = "&#10060; Confirm password do not match.<br>";
      } else {
        document.getElementById("hint2").innerHTML = "";
      }
    }

    function showHint(str) {
      if (str.length >= 8 && (/\d/.test(str)) == true && (/[A-Z]/.test(str)) == true && (/[a-z]/.test(str)) == true) {
        document.getElementById("hint").style.color = (str.length >= 16 ? "#177a2e" : "#1570d1");
        document.getElementById("hint").innerHTML = (str.length >= 16 ? "Strong password" : "Good password") + "<br>";
      } else if (str.length == 0) {
        document.getElementById("hint").style.color = "black";
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
      ifmatches()
    }
  </script>
  </body>

</html>