<?php
require_once("../CoreLibrary/CoreFunctions.php");

$current = new Session("Dev Console", "Dev", ["developer"]);
if ($current->accessstatus)
{
    if (isset($_POST["loginlogdelete"]))
    {
        filewrite("loginlog.txt", "");
    }
    else if (isset($_POST["errorlogdelete"]))
    {
        filewrite("errorlog.txt", "");
    }
    else if (isset($_POST["feedbacksdelete"]))
    {
        filewrite("feedbacks.txt", "");
    }
    else if (isset($_POST["resumeservice"]))
    {
        if (is_file("maintaining.txt"))
        {
            unlink("maintaining.txt");
        }
    }
    else if (isset($_POST["suspendservice"]) && isset($_POST["suspendservice_msg"]))
    {
        if (!is_file("maintaining.txt"))
        {
            filewrite("maintaining.txt", htmlspecialchars($_POST["suspendservice_msg"]));
        }
    }
    else if (isset($_POST["updatemsg_msg"]) && isset($_POST["updatemsg_submit"]))
    {
        if (is_file("maintaining.txt"))
        {
            filewrite("maintaining.txt", htmlspecialchars($_POST["updatemsg_msg"]));
        }
    }
    else if (isset($_POST["changeuserrole_targetuser"]) && isset($_POST["changeuserrole_submit"]))
    {
        try
        {
            $testuser = new User($_POST["changeuserrole_targetuser"]);
            $role2write = array_values(array_diff((array)json_decode(fileread("../Login/Accounts/" . $item . "/role.txt"), true), ROLE_LIST));
            if (!empty($_POST["changeuserrole_roles"]))
            {
                foreach ($_POST["changeuserrole_roles"] as $role)
                {
                    if (in_array($role, ROLE_LIST, true))
                    {
                        $role2write[] = $role;
                    }
                }
            }
            filewrite("../Login/Accounts/" . $testuser->username . "/role.txt", json_encode($role2write));
            $notification = new Notification("Role Changing Succeeded", "The user <b>" . $testuser->username . "</b>'s roles has been reassigned successfully.", "Dev", "", "", NotificationType::success);
        }
        catch (UnknownUsernameException $e)
        {
            $notification = new Notification("Role Changing Failed", "The user <b>" . $testuser->username . "</b> does not exist.", "Dev", "", "", NotificationType::danger);
        }
        $instantPushNotifications[] = $notification;
    }
    else if (isset($_POST["adduser_username"]) && isset($_POST["adduser_password"]) && isset($_POST["adduser_submit"]))
    {
        $username = urlencode($_POST["adduser_username"]);
        if (!is_dir("../Login/Accounts/" . $username))
        {
            mkdir("../Login/Accounts/" . $username);
            filewrite("../Login/Accounts/" . $username . "/password.txt", hash("sha3-512", $_POST["adduser_password"]));
            filewrite("../Login/Accounts/" . $username . "/role.txt", "[]");
            copy("../Account/defaultpfp.txt", "../Login/Accounts/" . $username . "/pfp.txt");
            $notification = new Notification("Adding User Succeeded", "The user <b>" . $username . "</b> has been created.", "Dev", "", "", NotificationType::success);
        }
        else
        {
            $notification = new Notification("Adding User Failed", "The user <b>" . $username . "</b> already exists.", "Dev", "", "", NotificationType::danger);
        }
        $instantPushNotifications[] = $notification;
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
        echo '<h1>Dev Console</h1><hr>';
        echo '<ul class="nav nav-tabs"><li class="nav-item"><a class="nav-link' . (!(isset($_GET["loginlog"]) || isset($_GET["errorlog"]) || isset($_GET["servicestatus"]) || isset($_GET["changeuserrole"])) ? ' active" href="#"' : '" href="?"') . '>Feedbacks</a></li><li class="nav-item"><a class="nav-link' . (isset($_GET["loginlog"]) ? ' active" href="#"' : '" href="?loginlog"') . '>Login log</a></li><li class="nav-item"><a class="nav-link' . (isset($_GET["errorlog"]) ? ' active" href="#"' : '" href="?errorlog"') . '>Error log</a></li><li class="nav-item"><a class="nav-link' . (isset($_GET["changeuserrole"]) ? ' active" href="#"' : '" href="?changeuserrole"') . '>Change user\'s role</a></li><li class="nav-item"><a class="nav-link' . (isset($_GET["adduser"]) ? ' active" href="#"' : '" href="?adduser"') . '>Add user</a></li><li class="nav-item"><a class="nav-link' . (isset($_GET["servicestatus"]) ? ' active" href="#"' : '" href="?servicestatus"') . '>Service status</a></li></ul>';

        if (isset($_GET["loginlog"]))
        {
            $loginlogcontent = fileread("loginlog.txt");
            echo '<div class="card card-body" style="max-height:50em;overflow:auto;">' . (empty($loginlogcontent) ? "<h6 style=\"text-align:center;margin-bottom:0em;\">No logins logged</h6>" : $loginlogcontent) . '</div><form method="post"><button type="button" onclick="if(confirm(\'Are you sure to clear the login log? This action cannot be undone.\')){$(this).attr(\'type\',\'submit\');$(this).parent().submit();}" class="btn btn-lg w-100 mt-4 btn-danger" name="loginlogdelete"><i class="bi bi-trash3-fill"></i> Clear login log</button><form>';
        }
        else if (isset($_GET["errorlog"]))
        {
            $errorlog = fileread("errorlog.txt");
            echo '<div class="card card-body" style="max-height:50em;overflow:auto;">' . (empty($errorlog) ? "<h6 style=\"text-align:center;margin-bottom:0em;\">No errors logged</h6>" : $errorlog) . '</div><form method="post"><button type="button" onclick="if(confirm(\'Are you sure to clear the error log? This action cannot be undone.\')){$(this).attr(\'type\',\'submit\');$(this).parent().submit();}" class="btn btn-lg w-100 mt-4 btn-danger" name="errorlogdelete"><i class="bi bi-trash3-fill"></i> Clear error log</button><form>';
        }
        else if (isset($_GET["changeuserrole"]))
        {
            $userlistoptions = "";

            foreach (USER_LIST as $item)
            {
                $roleofuser = implode(", ", (array)json_decode(fileread("../Login/Accounts/" . $item . "/role.txt"), true));
                $testuser = new User($item);
                $userlistoptions .= '<option value="' . $testuser->username . '"' . (isset($_POST["changeuserrole_targetuser"]) && $_POST["changeuserrole_targetuser"] == $testuser->username ? " selected" : "") . '>' . $testuser->username . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . (!empty($roleofuser) ? "(" . $roleofuser . ")" : "") . '</option>';
            }

            $rolelistdisplay = "";

            foreach (ROLE_LIST as $role)
            {
                $rolelistdisplay .= '<div class="form-check">
                <input class="form-check-input" type="checkbox" name="changeuserrole_roles[]" value="' . $role . '" id="' . $role . '">
                <label class="form-check-label" for="' . $role . '">
                  ' . $role . '
                </label>
              </div>';
            }

            echo '<form method="post" style="margin-top:1em;"><select name="changeuserrole_targetuser" class="form-select">' . $userlistoptions . '</select><div class="card p-3 mt-2"><h6>Roles (leave all check boxes empty to clear all roles): </h6>' . $rolelistdisplay . '</div><small class="mt-2 text-muted" style="display:block;">Roles not defined in ROLE_LIST cannot be modified here.</small><button type="submit" class="btn btn-primary mt-2" name="changeuserrole_submit">Submit</button></form>';
        }
        else if (isset($_GET["adduser"]))
        {
            echo '<form method="post" style="margin-top:1em;"><input type="text" class="form-control" placeholder="Username" name="adduser_username" required><input type="password" class="form-control mt-2" placeholder="Password" name="adduser_password" required><button type="submit" class="btn btn-primary mt-2" name="adduser_submit">Submit</button></form>';
        }
        else if (isset($_GET["servicestatus"]))
        {
            if (is_file("maintaining.txt"))
            {
                echo '<h1 class="text-center mt-5 mb-5"><i class="bi bi-pause-fill"></i> Service has been suspended</h1><form method="post"><input type="text" name="updatemsg_msg" class="form-control form-control-lg mt-5 mb-5" placeholder="Message" value="' . htmlspecialchars(fileread("maintaining.txt")) . '"><button type="submit" name="updatemsg_submit" class="btn btn-primary btn-lg w-100 mb-3"><i class="bi bi-arrow-up-circle-fill"></i> Update message</button></form><form method="post"><button type="submit" name="resumeservice" class="btn btn-success btn-lg w-100"><i class="bi bi-play-fill"></i> Resume service</button></form>';
            }
            else
            {
                echo '<form method="post"><input type="text" name="suspendservice_msg" class="form-control form-control-lg mt-5 mb-5" placeholder="Message" value="We will resume our service shortly."><button type="submit" name="suspendservice" class="btn btn-danger btn-lg w-100"><i class="bi bi-pause-fill"></i> Suspend service</button></form>';
            }
        }
        else
        {
            $feedbackcontent = fileread("feedbacks.txt");
            echo '<div class="card card-body" style="max-height:50em;overflow:auto;">' . (empty($feedbackcontent) ? "<h6 style=\"text-align:center;margin-bottom:0em;\">No feedbacks</h6>" : $feedbackcontent) . '</div><form method="post"><button type="button" onclick="if(confirm(\'Are you sure to clear feedbacks? This action cannot be undone.\')){$(this).attr(\'type\',\'submit\');$(this).parent().submit();}" class="btn btn-lg w-100 mt-4 btn-danger" name="feedbacksdelete"><i class="bi bi-trash3-fill"></i> Clear Feedbacks</button><form>';
        }
    }
    else
    {
        echo $current->accessstatusmsg;
    }
    ?>

    <?= $current->getFooter() ?>
</body>

</html>