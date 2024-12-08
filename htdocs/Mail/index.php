<?php
$current = new Session("Mail", "Mail");
require("../CoreLibrary/Mail.php");

if ($current->accessstatus && !empty($_GET) && !empty($_POST))
{
    $folder = urlencode(urldecode($_GET["folder"]));
    $fileid = urlencode(urldecode($_GET["id"]));
    $target = "Mails/" . $current->username . "/" . $folder . "/" . $fileid . ".txt";
    if (is_file($target) && fileread($target) !== "DELETED")
    {
        if (isset($_POST["Delete"]))
        {
            filewrite($target, "DELETED");
            header("Location: https://" . SITE_DOMAIN . "/Mail/?" . $folder);
            exit();
        }
        else if (isset($_POST["Unread"]))
        {
            $mail = unserialize(fileread($target));
            $mail->read = false;
            filewrite($target, serialize($mail));
            header("Location: https://" . SITE_DOMAIN . "/Mail/?" . $folder);
            exit();
        }
        else if (isset($_POST["Star"]))
        {
            $mail = unserialize(fileread($target));
            $mail->star = true;
            filewrite($target, serialize($mail));
        }
        else if (isset($_POST["Unstar"]))
        {
            $mail = unserialize(fileread($target));
            $mail->star = false;
            filewrite($target, serialize($mail));
        }
    }
}

function getAllMails($folder = "Inbox"): string
{
    global $current;
    $display = '<ul class="list-group">';
    if (is_dir("Mails/" . $current->username . "/$folder") && pathInjectionSecure($folder))
    {
        $allMailNo = count(array_diff(scandir("Mails/" . $current->username . "/$folder"), array('..', '.')));
        for ($i = $allMailNo; $i >= 1; --$i)
        {
            $target = "Mails/" . $current->username . "/$folder/" . $i . ".txt";
            if (is_file($target) && fileread($target) !== "DELETED")
            {
                $display .= unserialize(fileread($target))->getSummaryMailListItem($folder, $i);
            }
        }
    }
    $display .= "</ul><h6 style=\"text-align:center;padding-top:1em;\">No more mails recently</h6>";
    return $display;
}

function getMail($folder, $id): string
{
    global $current;
    $display = '';
    $target = "Mails/" . $current->username . "/$folder/" . $_GET["id"] . ".txt";
    if (is_file($target) && fileread($target) !== "DELETED" && pathInjectionSecure($folder))
    {
        $mail = unserialize(fileread($target));
        $mail->read = true;
        filewrite($target, serialize($mail));
        $display = '<a class="btn" href="?' . $folder . '" style="float:left;margin-right:1em;"><i class="bi bi-arrow-left" style="font-size:1.5em;"></i></a>' . $mail->getDetailedMailPage($folder, $id);
    }
    else
    {
        echo '<div style="text-align:center;"><br><br><h1>Mail not found</h1><h2>The mail you\'re looking for cannot be found.</h2><br><br></div>';
    }
    return $display;
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
        if (isset($_GET["view"]))
        {
            echo getMail($_GET["folder"], $_GET["id"]);
        }
        else if (isset($_GET["Sent"]))
        {
            echo '<h1 class="d-flex"><span class="flex-fill">Mail</span><a type="button" class="btn btn-success align-self-center" href="/ComposeMail">+ Compose mail</a></h1><hr>';
            echo '<ul class="nav nav-tabs"><li class="nav-item"><a class="nav-link" href="?Inbox">Inbox</a></li><li class="nav-item"><a class="nav-link active" aria-current="page" href="#">Sent</a></li></ul>';
            echo getAllMails("Sent");
        }
        else
        {
            echo '<h1 class="d-flex"><span class="flex-fill">Mail</span><a type="button" class="btn btn-success align-self-center" href="/ComposeMail">+ Compose mail</a></h1><hr>';
            echo '<ul class="nav nav-tabs"><li class="nav-item"><a class="nav-link active" aria-current="page" href="#">Inbox</a></li><li class="nav-item"><a class="nav-link" href="?Sent">Sent</a></li></ul>';
            echo getAllMails();
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