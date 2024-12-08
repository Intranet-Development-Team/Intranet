<?php
$current = new Session("Mod Console", "Mod", ["moderator"]);
if ($current->accessstatus)
{
    if (isset($_POST['suspendaccount_submit']) && isset($_POST['suspendaccount_targetuser']) && isset($_POST['suspendaccount_time']) || isset($_POST['suspendaccount_revoke']))
    {
        try
        {
            $taruser = new User($_POST['suspendaccount_targetuser']);
            $endtime = date("Y-m-d H:i:s", strtotime($_POST['suspendaccount_time']));

            if (time() < strtotime($_POST['suspendaccount_time']))
            {
                filewrite("../Login/Accounts/" . $taruser->username . "/suspended.txt", $endtime);
                $notification = new Notification("Account Suspension Succeeded", "The user <b>" . $taruser->username . "</b> will not be able to log in until " . $endtime . ".", "Mod", "", "", NotificationType::success);
            }
            else
            {
                if (is_file("../Login/Accounts/" . $taruser->username . "/suspended.txt"))
                {
                    unlink("../Login/Accounts/" . $taruser->username . "/suspended.txt");
                }
                $notification = new Notification("Account Suspension Revoked", "Account suspension on the user <b>" . $taruser->username . "</b> has been revoked.", "Mod", "", "", NotificationType::success);
            }
        }
        catch (UnknownUsernameException $ex)
        {
            $notification = new Notification("Account Suspension Failed", "The user <b>" . $taruser->username . "</b> does not exist.", "Mod", "", "", NotificationType::danger);
        }
        $instantPushNotifications[] = $notification;
    }
    else if (isset($_POST['changeuserpw_submit']) && isset($_POST['changeuserpw_targetuser']) && isset($_POST['changeuserpw_newpassword']) && isset($_POST['changeuserpw_ownpassword']))
    {
        try
        {
            $taruser = new User($_POST['changeuserpw_targetuser']);
            if ($current->validatepw($_POST['changeuserpw_ownpassword']))
            {
                $taruser->setpw($_POST['changeuserpw_newpassword']);
                if (is_file("../Login/Accounts/" . $taruser->username . "/firstlogin.txt"))
                {
                    unlink("../Login/Accounts/" . $taruser->username . "/firstlogin.txt");
                }
                $notification = new Notification("Password Changing Succeeded", "The user <b>" . $taruser->username . "</b>'s password has been changed.", "Mod", "", "", NotificationType::success);
            }
            else
            {
                $notification = new Notification("Password Changing Failed", "You own password for identity verification is not correct.", "Mod", "", "", NotificationType::danger);
            }
        }
        catch (UnknownUsernameException $ex)
        {
            $notification = new Notification("Password Changing Failed", "The user <b>" . $taruser->username . "</b> does not exist.", "Mod", "", "", NotificationType::danger);
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
        echo '<h1>Mod Console</h1><hr>';
        echo '<ul class="nav nav-tabs"><li class="nav-item"><a class="nav-link' . (!(isset($_GET["reportedabuse"]) || isset($_GET["changeuserpw"])) ? ' active" href="#"' : '" href="?"') . '>Suspend account</a></li><li class="nav-item"><a class="nav-link' . (isset($_GET["reportedabuse"]) ? ' active" href="#"' : '" href="?reportedabuse"') . '>Reported abuses</a></li><li class="nav-item"><a class="nav-link' . (isset($_GET["changeuserpw"]) ? ' active" href="#"' : '" href="?changeuserpw"') . '>Change user\'s password</a></li></ul>';
        if (isset($_GET["reportedabuse"]))
        {
            $abusecontent = fileread("abuses.txt");
            echo '<div class="card card-body" style="max-height:50em;overflow:auto;">' . (empty($abusecontent) ? "<h6 style=\"text-align:center;margin-bottom:0em;\">No abuses reported</h6>" : $abusecontent) . '</div>';
        }
        else if (isset($_GET["changeuserpw"]))
        {
            $userlistoptions = "";

            foreach (USER_LIST as $item)
            {
                $testuser = new User($item);
                $userlistoptions .= '<option value="' . $testuser->username . '"' . (isset($_POST["changeuserpw_targetuser"]) && $_POST["changeuserpw_targetuser"] == $testuser->username ? " selected" : "") . '>' . $testuser->username . '</option>';
            }
            echo '<form method="post" style="margin-top:1em;"><select name="changeuserpw_targetuser" class="form-select">' . $userlistoptions . '</select><input type="password" class="form-control mt-2" placeholder="New password" name="changeuserpw_newpassword" required><input type="password" class="form-control mt-2" placeholder="Your own account\'s password" name="changeuserpw_ownpassword" required><div class="mt-2">' . ($msg_cpw ?? "") . '</div><small class="mt-2 text-muted" style="display:block;">After you change the user\'s password, the user must change the password on the first login again to make sure nobody knows it.</small><button type="submit" class="btn btn-primary mt-2" name="changeuserpw_submit">Submit</button></form>';
        }
        else
        {
            $userlistoptions = "";

            foreach (USER_LIST as $item)
            {
                $testuser = new User($item);
                $userlistoptions .= '<option value="' . $testuser->username . '"' . (isset($_POST["suspendaccount_targetuser"]) && $_POST["suspendaccount_targetuser"] == $testuser->username ? " selected" : "") . '>' . $testuser->username . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . (is_file("../Login/Accounts/" . $testuser->username . "/suspended.txt") && !empty(fileread("../Login/Accounts/" . $testuser->username . "/suspended.txt") && strtotime(fileread("../Login/Accounts/" . $testuser->username . "/suspended.txt")) >= time()) ? "(current suspension until " . fileread("../Login/Accounts/" . $testuser->username . "/suspended.txt")  . ")" : "") . '</option>';
            }

            echo '<form method="post"><select name="suspendaccount_targetuser" class="form-select mt-2">' . $userlistoptions . '</select><h6 class="mt-2">Block the target user from logging into Intranet until:</h6><input type="datetime-local" class="form-control" style=" width:100%;" name="suspendaccount_time" required><div class="mt-2">' . ($msg_suspension ?? "") . '</div><button type="submit" class="btn btn-primary mt-3" name="suspendaccount_submit">Submit</button></form>';
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