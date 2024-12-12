<?php
require_once("../CoreLibrary/CoreFunctions.php");

function getDevice()
{
  $useragentinfo = $_SERVER['HTTP_USER_AGENT'];
  if (preg_match('/windows/i', $useragentinfo))
  {
    return '<i class="bi bi-microsoft"></i> Windows';
  }
  else if (preg_match('/iphone/i', $useragentinfo))
  {
    return '<i class="bi bi-apple"></i> iPhone';
  }
  else if (preg_match('/mac/i', $useragentinfo))
  {
    return '<i class="bi bi-apple"></i> Mac';
  }
  else if (preg_match('/android/i', $useragentinfo))
  {
    return '<i class="bi bi-android"></i> Android';
  }
  else if (preg_match('/linux/i', $useragentinfo))
  {
    return "Linux";
  }
  else
  {
    return "Unknown Device";
  }
}

function getBrowser()
{
  $useragentinfo = $_SERVER['HTTP_USER_AGENT'];
  if (preg_match('/edg/i', $useragentinfo))
  {
    return '<i class="bi bi-browser-edge"></i> Edge';
  }
  else if (preg_match('/chrome|CriOS/i', $useragentinfo))
  {
    return '<i class="bi bi-browser-chrome"></i> Chrome';
  }
  else if (preg_match('/firefox/i', $useragentinfo))
  {
    return '<i class="bi bi-browser-firefox"></i> Firefox';
  }
  else if (preg_match('/safari/i', $useragentinfo))
  {
    return '<i class="bi bi-browser-safari"></i> Safari';
  }
  else
  {
    return "Unknown Browser";
  }
}

$current = new Session("Login", "", USER_LIST, false);
if ($current->loginstatus)
{
  header("Location: https://" . SITE_DOMAIN . "/" . ($_GET["redirect"] ?? ""));
  $loggedin = true;
  exit;
}
else
{
  $loggedin = false;
  $error = $current->loginstatusmsg;
}


if (!empty($_POST["username"]) && !empty($_POST["password"]) && !$loggedin)
{
  try
  {
    $testloginuser = new User($_POST["username"]);
    if ($testloginuser->validatepw($_POST["password"]))
    {
      do
      {
        $randomID = random_str();
      } while (is_file("Accounts/" . $testloginuser->username . "/Logins/" . hash("sha3-512", $randomID) . ".txt"));
      $randomVerification = random_str();

      $_COOKIE["user"] = $testloginuser->username;
      $_COOKIE["id"] = $randomID;
      $_COOKIE["verification"] = $randomVerification;
      if (!is_dir("Accounts/" . $testloginuser->username . "/Logins"))
      {
        mkdir("Accounts/" . $testloginuser->username . "/Logins");
      }
      filewrite("Accounts/" . $testloginuser->username . "/Logins/" . hash("sha3-512", $randomID) . ".txt", json_encode(["verification" => hash("sha3-512", $randomVerification), "exptime" =>  time() + 60 * 60 * 24 * 90, "lastactive" => time(), "lastidupdate" => time(), "ipaddress" => [$_SERVER['REMOTE_ADDR']], "device" => getDevice(), "browser" => getBrowser()]));

      $current = new Session("Login", "", USER_LIST, false);

      filewrite("../Dev/loginlog.txt", "<div class=\"alert alert-success\"><h5 class=\"alert-heading\" style=\"display:inline-block;\">" . $_SERVER['REMOTE_ADDR'] . "</h5><h6 style=\"float:right;margin-top:0.25em;\">" . date("Y-m-d H:i:s") . "</h6><br>Username entered: <b>" . $current->username . "</b><br>User agent info: <b>" . $_SERVER["HTTP_USER_AGENT"] . "</b><br>Status: <b>succeeded</b></div>", "beginning");

      setcookie("user", $current->username, [
        'expires' => time() + 60 * 60 * 24 * 365,
        'path' => '/',
        'domain' => SITE_DOMAIN,
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax',
      ]);

      setcookie("id", $randomID, [
        'expires' => time() + 60 * 60 * 24 * 365,
        'path' => '/',
        'domain' => SITE_DOMAIN,
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax',
      ]);

      setcookie("verification", $randomVerification, [
        'expires' => time() + 60 * 60 * 24 * 365,
        'path' => '/',
        'domain' => SITE_DOMAIN,
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax',
      ]);

      $newloginnotification = new Notification("New Login Detected", "A new <b>" . getDevice() . "</b> device has logged in to your account.", "Account Settings", "new_login", hash("sha3-512", $randomID), NotificationType::warning, true);
      $newloginnotification->push([$current->username]);

      header("Location: https://" . SITE_DOMAIN . "/" . ($_GET["redirect"] ?? ""));
      exit();
    }
    else
    {
      filewrite("../Dev/loginlog.txt", "<div class=\"alert alert-danger\"><h5 class=\"alert-heading\" style=\"display:inline-block;\">" . $_SERVER['REMOTE_ADDR'] . "</h5><h6 style=\"float:right;margin-top:0.25em;\">" . date("Y-m-d H:i:s") . "</h6><br>Username entered: <b>" . $_POST["username"] . "</b><br>User agent info: <b>" . $_SERVER["HTTP_USER_AGENT"] . "</b><br>Status: <b>failed</b></div>", "beginning");
      $error = "Invalid username or password.";
    }
  }
  catch (UnknownUsernameException $exception)
  {
    filewrite("../Dev/loginlog.txt", "<div class=\"alert alert-danger\"><h5 class=\"alert-heading\" style=\"display:inline-block;\">" . $_SERVER['REMOTE_ADDR'] . "</h5><h6 style=\"float:right;margin-top:0.25em;\">" . date("Y-m-d H:i:s") . "</h6><br>Username entered: <b>" . $_POST["username"] . "</b><br>User agent info: <b>" . $_SERVER["HTTP_USER_AGENT"] . "</b><br>Status: <b>failed</b></div>", "beginning");
    $error = "Invalid username or password.";
  }
}

if (isset($error))
{
  if ($error === "Maintenance is in progress.")
  {
    $maintenancemsg = fileread($_SERVER["DOCUMENT_ROOT"] . "/Dev/maintaining.txt");
    echo '<head><meta charset="utf-8"> <noscript><meta http-equiv="refresh" content="0; url=https://' . SITE_DOMAIN . '/JSdisabled/"></noscript> <link rel="icon" href="/Complements/img/icon.png"> <title>Maintenance Notice | ' . SITE_NAME . ' </title><link rel="stylesheet" href="/Complements/Bootstrap/css/bootstrap.min.css"></head><body><main><div style="margin: 0;position: absolute;top: 40%;left: 50%;-ms-transform: translate(-50%, -50%);transform: translate(-50%, -50%);text-align:center;"><img style="width:10em;margin-bottom:1em;" src="/Complements/img/icon.png"><h2><b>Intranet is currently down for maintenance</b></h2><br>' . ($maintenancemsg !== "" ? '<h4>' . $maintenancemsg . '</h4>' : "") . '</div></body>';
    exit();
  }
  else if ($error === "Your account is suspended.")
  {
    $suspensionedntime = fileread($_SERVER["DOCUMENT_ROOT"] . "/Login/Accounts/" . $current->username . "/suspended.txt");
    echo '<head><meta charset="utf-8"> <noscript><meta http-equiv="refresh" content="0; url=https://' . SITE_DOMAIN . '/JSdisabled/"></noscript> <link rel="icon" href="/Complements/img/icon.png"> <title>Suspension Notice | ' . SITE_NAME . ' </title><link rel="stylesheet" href="/Complements/Bootstrap/css/bootstrap.min.css"></head><body><main><div style="margin: 0;position: absolute;top: 40%;left: 50%;-ms-transform: translate(-50%, -50%);transform: translate(-50%, -50%);text-align:center;"><img style="width:10em;margin-bottom:1em;" src="/Complements/img/icon.png"><h2><b>Your account is suspended</b></h2><br><h4>Please retry login after <i>' . $suspensionedntime . '</i>.</h4></div></body>';
    exit();
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

<body class="bg-body-tertiary m-0">
  <main class="form-signin">
    <form method="post" onsubmit="preventMisclick($('#log'))">
      <img class="mb-4" src="../Complements/img/icon.png" alt="intranet" width="100" style="display: block;margin-left: auto;margin-right: auto;">
      <h1 class="h3 mb-3 fw-normal">
        <p style="text-align:center;"><?= SITE_NAME ?></p>
      </h1>

      <div class="form-floating mb-1">
        <input type="username" id="inputUN" class="form-control" placeholder="Username" name="username" required autofocus>
        <label for="inputUN">Username</label>
      </div>
      <div class="form-floating mb-1">
        <input type="password" id="inputPassword" class="form-control" placeholder="Password" name="password" required>
        <label for="inputPassword">Password</label>
      </div>
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" onclick="spw()" id="showpw">
        <label class="form-check-label" for="showpw">Show password</label>
      </div>
      <h6 style="color:red;"><?= $error ?></h6>
      <button class="w-100 btn btn-lg btn-primary" name="login" id="log">Log in</button><br><br>
      <span class="mt-5 mb-3 text-muted"><small>Site design &copy; Intranet Development Team</small><br><small style="font-size:0.75em;">Logging in means you agree to our <a href="../Conduct">Code of Conduct</a>, <a href="../Copyright">Copyright Policy</a> and <a href="../Privacy">Privacy Policy</a></small></span>
    </form>
  </main>

  <script>
    function spw() {
      var x = document.getElementById("inputPassword");
      if (x.type === "text") {
        x.type = "password";
      } else if (x.type === "password") {
        x.type = "text";
      }
    }
  </script>
</body>

</html>