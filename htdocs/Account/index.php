<?php
require_once("../CoreLibrary/CoreFunctions.php");

$current = new Session("Account Settings", "Account");
if ($current->accessstatus)
{
    if (isset($_POST["logout"]))
    {
        $current->logOut();
    }
    else if (isset($_POST["logoutdevice"]))
    {
        foreach (scandir("../Login/Accounts/" . $current->username  . "/Logins") as $filename)
        {
            if ($filename !== "." && $filename !== "..")
            {
                if (hash("sha3-512", $filename) === $_POST["logoutdevice"])
                {
                    $devicedetail = json_decode(fileread("../Login/Accounts/" . $current->username  . "/Logins/" . $filename), true);
                    unlink("../Login/Accounts/" . $current->username  . "/Logins/" . $filename);
                    $notification = new Notification("Device Logged out", "Your " . $devicedetail["device"] . " has been logged out.", "Account Settings", "", "", NotificationType::success);
                    $notificationOperator = new NotificationSystemOperator();
                    $notificationOperator->addInstantPushNotification($notification);
                }
            }
        }
    }
    else if (isset($_POST["uploadpicsubmit"]) && is_uploaded_file($_FILES['userprofilepicture']['tmp_name']))
    {
        if ($_FILES["userprofilepicture"]["size"] <= 3145728)
        {
            $image = @imagecreatefromstring(fileread($_FILES["userprofilepicture"]["tmp_name"]));
            if ($image !== false)
            {
                $size = min(imagesx($image), imagesy($image));
                $max = max(imagesx($image), imagesy($image));
                if (imagesx($image) === $max)
                {
                    $image = imagecrop($image, ['x' => round((imagesx($image) - $size) / 2), 'y' => 0, 'width' => $size, 'height' => $size]);
                }
                else
                {
                    $image = imagecrop($image, ['x' => 0, 'y' => round((imagesy($image) - $size) / 2), 'width' => $size, 'height' => $size]);
                }
                if ($image !== false)
                {
                    $image = imagescale($image, 200, 200);
                    ob_start();
                    imagepng($image);
                    $imgdata = ob_get_clean();
                    imagedestroy($image);
                    $current->setpfp($imgdata);
                    $notification = new Notification("Profile Picture Upload Succeeded", "Your profile picture has been updated.", "Account Settings", "", "", NotificationType::success);
                }
                else
                {
                    $notification = new Notification("Profile Picture Upload Failed", "An known error has occurred. Please try again.", "Account Settings", "", "", NotificationType::danger);
                }
            }
            else
            {
                $notification = new Notification("Profile Picture Upload Failed", "The image file you have uploaded is not in a supported format.", "Account Settings", "", "", NotificationType::danger);
            }
        }
        else
        {
            $notification = new Notification("Profile Picture Upload Failed", "The image file you have uploaded is too large (> 3 MB).", "Account Settings", "", "", NotificationType::danger);
        }
        $notificationOperator = new NotificationSystemOperator();
        $notificationOperator->addInstantPushNotification($notification);
    }
    else if (isset($_POST["changepwsubmit"]) && isset($_POST["oldpassword"]) && isset($_POST["newpassword"]) && isset($_POST["newpasswordconfirm"]))
    {
        if ($current->validatepw($_POST["oldpassword"]))
        {
            if (mb_strlen($_POST["newpassword"]) >= 8 && preg_match('/[A-Z]/', $_POST["newpassword"]) && preg_match('/[a-z]/', $_POST["newpassword"]) && preg_match('/[0-9]/', $_POST["newpassword"]))
            {
                if ($_POST["newpassword"] === $_POST["newpasswordconfirm"])
                {
                    if ($_POST["oldpassword"] !== $_POST["newpassword"])
                    {
                        $current->setpw($_POST["newpassword"]);
                        $current->logOut();
                        exit();
                    }
                    else
                    {
                        $notification = new Notification("Password Changing Failed", "The new password you entered is exactly the same as your current one.", "Account Settings", "", "", NotificationType::danger);
                    }
                }
                else
                {
                    $notification = new Notification("Password Changing Failed", "The confirmation password doesn't match.", "Account Settings", "", "", NotificationType::danger);
                }
            }
            else
            {
                $notification = new Notification("Password Changing Failed", "The new password doesn't meet the strength require_oncements.", "Account Settings", "", "", NotificationType::danger);
            }
        }
        else
        {
            $notification = new Notification("Password Changing Failed", "The old password doesn't match.", "Account Settings", "", "", NotificationType::danger);
        }
        $notificationOperator = new NotificationSystemOperator();
        $notificationOperator->addInstantPushNotification($notification);
    }
    else if (isset($_POST["changebdaysubmit"]) && isset($_POST["birthday"]))
    {
        require_once("../CoreLibrary/DatetimeHandlers.php");
        if (validateDatetime($_POST["birthday"], "Y-m-d"))
        {
            $current->setBirthday(strtotime($_POST["birthday"]));
            $notification = new Notification("Birthday Changing Succeeded", "Your birthday has been updated.", "Account Settings", "", "", NotificationType::success);
        }
        else
        {
            $notification = new Notification("Birthday Changing Failed", "Please enter a valid date.", "Account Settings", "", "", NotificationType::danger);
        }
        $notificationOperator = new NotificationSystemOperator();
        $notificationOperator->addInstantPushNotification($notification);
    }
}
?>
<!DOCTYPE html>
<html>
<?= $current->getHtmlHead() ?>

<body class="container pt-4">
    <header><?= $current->getNavBar() ?></header>
    <?php
    if ($current->accessstatus)
    {
        echo '<h1>Account Settings</h1><div style="display:flex;flex-wrap:wrap;align-items: center;justify-content: center;text-align:center;"><img src="' . $current->getpfp(200) . '" style="border-radius:50%;width:12em;margin-top:2em;margin-bottom:2em;margin-right:1.5em;margin-left:1.5em;" class="border border-3"><div style="margin-left:1.5em;margin-right:1.5em;"><h6>' . $current->rolefordisplay . '<br></h6><h1>' . $current->username . '<br></h1></div></div>
        <br>
        ';

        $notificationsystemoperator = new NotificationSystemOperator();
        $newloginscount = $notificationsystemoperator->getNotificationCountByIDs("new_login");

        if (isset($_GET["security"]))
        {
            require_once("../CoreLibrary/DatetimeHandlers.php");

            echo '<ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link" href="?">Info</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="#">Security</a>
            </li>
            </ul>
            <div class="mb-5">
            <h5 class="mt-3">Change password</h5><form method="post" enctype="multipart/form-data" action="#chpw" onsubmit="preventMisclick($(\'#changepwsubmit\'))"><div class="input-group mb-3"><input type="password" class="form-control" name="oldpassword" placeholder="Old Password" id="oldpassword" required><input type="password" class="form-control" name="newpassword" placeholder="New Password" id="newpassword" onkeyup="showHint(this.value)" required><input type="password" class="form-control" name="newpasswordconfirm" placeholder="Confirm New Password" id="newpasswordconfirm" required></div><div class="form-check form-switch"><input id="showpasswordscheckbox" class="form-check-input" type="checkbox" onclick="showPassword()"><label class="form-check-label" for="showpasswordscheckbox">Show passwords</label></div><small id="hint"><span class=\"text-muted\">You will need to re-login after changing the password successfully.</span></small><button class="w-100 btn btn-primary mt-1" name="changepwsubmit" id="changepwsubmit">Submit</button></form>
            </div>
            <div class="mb-5">';

            echo '<h5 class="mb-3 mt-3 d-flex" id="devices">Devices' . ($newloginscount ? '<span class="badge bg-danger rounded-pill ms-2 align-self-center" style="font-size:.6em;">' . $newloginscount . '</span>' : "") . '</h5>';
            echo isset($logoutdevicemsg) ? $logoutdevicemsg : "";

            if (is_dir("../Login/Accounts/" . $current->username  . "/Logins"))
            {
                foreach (scandir("../Login/Accounts/" . $current->username  . "/Logins") as $key => $filename)
                {
                    if ($filename !== "." && $filename !== "..")
                    {
                        $loginhashedid = basename($filename, ".txt");
                        $logindata = json_decode(fileread("../Login/Accounts/" . $current->username  . "/Logins/" . $filename), true);
                        $savedlogindata = json_decode(fileread("../Login/Accounts/" . $current->username  . "/Logins/" . $filename), true);
                        echo '<div class="card card-body mb-2"><h4 class="card-title mt-2 d-flex align-items-baseline"><div>' . $savedlogindata["device"] . '</div><div class="ms-3 fs-6 flex-fill">' . $savedlogindata["browser"] . '</div>' . ($notificationsystemoperator->getNotificationCountByIDnData("new_login", $loginhashedid) ? '<span class="badge rounded-pill bg-danger ms-3" style="font-size:.8rem;">New</span>' : "") . ($logindata["exptime"] <= time() || $logindata["lastactive"] + 60 * 60 * 24 * 14 <= time() ? '<span class="badge rounded-pill bg-secondary d-inline-block ms-3" style="font-size:.5em;vertical-align:.2em;">Session Expired</span>' : "") . '</h4><hr><h6>IP address(es)</h6>' . implode(", ", $savedlogindata["ipaddress"]) . '<hr><h6>Last active time</h6>' . ucfirst(displayDatetime($savedlogindata["lastactive"])) . '<div>' . ($current->hashedloginid === $loginhashedid ? '<h6 class="text-success float-end"><i class="bi bi-check-lg"></i> Your current session</h6>' : '<form id="logoutdevice' . $key . '" method="post" enctype="multipart/form-data" action="#devices" onsubmit="preventMisclick($(\'#logoutbtn' . $key . '\'))"><button type="button" class="btn btn-danger float-end" onclick="confirmModal(\'Log out?\',\'Are you sure to log out from this device?\',\'$(\\\'#logoutdevice' . $key . '\\\').submit()\',\'Cancel\',\'Log out\')" id="logoutbtn' . $key . '"><i class="bi bi-box-arrow-right"></i> Log out from this device</button><input type="hidden" name="logoutdevice" value="' . hash("sha3-512", $filename) . '"></form>') . '</div></div>';
                    }
                }

                $notificationsystemoperator->removeNotificationByIDs("new_login");
            }
            echo '</div>';
        }
        else
        {
            $numberofnewlogins = 0;
            if (is_file("../Login/Accounts/" . $current->username  . "/NewLogins.txt"))
            {
                $newlogins = (array)json_decode(fileread("../Login/Accounts/" . $current->username  . "/NewLogins.txt"), true);

                foreach ($newlogins as $login)
                {
                    if ($login !== $current->hashedloginid)
                    {
                        $numberofnewlogins++;
                    }
                }
            }
            echo '<ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link active" href="#">Info</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="?security">Security' . ($newloginscount ? '<span class="badge rounded-pill bg-danger ms-2">' . $newloginscount . '</span>' : "") . '</a>
        </li>
        </ul>
        <h5 class="mt-3">Your electives</h5>
        <div class="mb-5">
        <div class="row align-items-start text-center mt-5 mb-5"><div class="col"><h4>
        ' . implode('</h4></div><div class="col"><h4>', json_decode(fileread("../Login/Accounts/" . $current->username . "/electives.txt"), true)) . '</h4></div>
        </div>
        <small class="text-muted">Your electives cannot be changed after being set up.</small>
        </div>
        <div class="mb-5">
        <h5 class="mt-3">Change profile picture</h5><form method="post" enctype="multipart/form-data" onsubmit="preventMisclick($(\'#uploadpicsubmit\'))"><div class="input-group"><input type="file" class="form-control" name="userprofilepicture" accept="image/*" required><button class="btn btn-primary" type="submit" name="uploadpicsubmit" id="uploadpicsubmit"><i class="bi bi-upload"></i> Upload</button></div></form>
        </div><div class="mb-5">
        <h5 class="mt-3">Change birthday</h5><form method="post" enctype="multipart/form-data" onsubmit="preventMisclick($(\'#changebdaysubmit\'))"><div class="input-group"><input type="date" class="form-control" name="birthday" placeholder="Date" value="' . date("Y-m-d", $current->getBirthday()) . '"><button class="btn btn-primary" name="changebdaysubmit" id="changebdaysubmit">Submit</button></div></form>
        </div>';
        }
        echo '<hr><form method="post" enctype="multipart/form-data" id="logoutcurrent" onsubmit="preventMisclick($(\'#logoutbtn\'))"><button type="button" style="width:100%;" class="btn btn-danger btn-lg" onclick="confirmModal(\'Log out?\',\'Are you sure to log out from current device?\',\'$(\\\'#logoutcurrent\\\').submit()\',\'Cancel\',\'Log out\')" id="logoutbtn"><i class="bi bi-box-arrow-right"></i> Log out</button><input type="hidden" name="logout"></form>';
    }
    else
    {
        echo $current->accessstatusmsg;
    }
    ?>
    <script>
        function showPassword() {
            let x1 = document.getElementById("oldpassword");
            if (x1.type == "text") {
                x1.type = "password";
            } else if (x1.type == "password") {
                x1.type = "text";
            }
            let x2 = document.getElementById("newpassword");
            if (x2.type == "text") {
                x2.type = "password";
            } else if (x2.type == "password") {
                x2.type = "text";
            }
            let x3 = document.getElementById("newpasswordconfirm");
            if (x3.type == "text") {
                x3.type = "password";
            } else if (x3.type == "password") {
                x3.type = "text";
            }

        }

        function showHint(str) {
            if (str.length >= 8 && (/\d/.test(str)) == true && (/[A-Z]/.test(str)) == true && (/[a-z]/.test(str)) == true) {
                document.getElementById("hint").style.color = (str.length >= 16 ? "#177a2e" : "#1570d1");
                document.getElementById("hint").innerHTML = (str.length >= 16 ? "Strong password" : "Good password") + "<br>";
            } else if (str.length == 0) {
                document.getElementById("hint").style.color = "black";
                document.getElementById("hint").innerHTML = "You will need to re-login after changing the password successfully.<br>";
            } else {
                document.getElementById("hint").innerHTML = "";
                if (str.length < 8) {
                    document.getElementById("hint").style.color = "red";
                    document.getElementById("hint").innerHTML += "&#10060; The new password must be at least 8 characters long.<br>";
                }
                if (/\d/.test(str) == false) {
                    document.getElementById("hint").style.color = "red";
                    document.getElementById("hint").innerHTML += "&#10060; The new password must include a number.<br>";
                }
                if (/[A-Z]/.test(str) == false) {
                    document.getElementById("hint").style.color = "red";
                    document.getElementById("hint").innerHTML += "&#10060; The new password must include a capital letter.<br>";
                }
                if (/[a-z]/.test(str) == false) {
                    document.getElementById("hint").style.color = "red";
                    document.getElementById("hint").innerHTML += "&#10060; The new password must include a small letter.<br>";
                }
            }
        }
    </script>
    <?= $current->getFooter() ?>
</body>

</html>