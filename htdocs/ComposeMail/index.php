<?php
require_once("../CoreLibrary/CoreFunctions.php");

$current = new Session("Mail", "ComposeMail");
require_once("../CoreLibrary/Mail.php");

if ($current->accessstatus && isset($_POST["submit"]) && isset($_POST["to"]) && isset($_POST["subject"]) && isset($_POST["content"]))
{
    require_once("../CoreLibrary/IMP.php");
    $IMP = new IMP();

    $targets = explode(" ", $_POST["to"]);

    try
    {
        $mail = new Mail(htmlspecialchars($_POST["subject"]), $current->username, $targets, $IMP->text($_POST["content"]), date("Y-m-d H:i:s"));
        $mail->sendMail();
        if (is_file("../Login/Accounts/" . $current->username . "/Drafts/Mail_Compose.txt"))
        {
            unlink("../Login/Accounts/" . $current->username . "/Drafts/Mail_Compose.txt");
        }
        header("Location: https://" . SITE_DOMAIN . "/Mail?Sent");
        exit();
    }
    catch (UnknownUsernameException $e)
    {
        $notificationOperator = new NotificationSystemOperator();
        $notificationOperator->addInstantPushNotification(new Notification("Mail Sending Failed", "The user <i>" . $e->getMessage() . "</i> doesn't exist.", "Mail", "", "", NotificationType::danger));
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
        if (is_file("../Login/Accounts/" . $current->username . "/Drafts/Mail_Compose.txt"))
        {
            $saveddraft = json_decode(fileread("../Login/Accounts/" . $current->username . "/Drafts/Mail_Compose.txt"), true);
            $_POST["to"] = $saveddraft["to"];
            $_POST["subject"] = $saveddraft["subject"];
            $_POST["content"] = $saveddraft["content"];
            unlink("../Login/Accounts/" . $current->username . "/Drafts/Mail_Compose.txt");
        }
        echo '<h1 class="d-flex"><a class="btn align-self-center me-2" href="/Mail"><i class="bi bi-arrow-left fs-5"></i></a>Compose Mail</h1><hr>';
        echo '<form method="post" onsubmit="preventMisclick($(\'#submitbtn\'))"><small id="savealert" class="d-block text-center mb-2">Autosave enabled <i class="bi bi-check2"></i></small><div class="text-end"><a href="/Support/?formatting" target="_blank">How to style text?</a></div><input type="text" id="to" name="to" placeholder="To (Separate each username with a space if needed)" value="' . ($_POST["to"] ?? "") . '" oninput="savedraft()" class="form-control mb-2" required><input type="text" id="subject" name="subject" placeholder="Subject" value="' . ($_POST["subject"] ?? "") . '" oninput="savedraft()" class="form-control mb-2"><textarea name="content" style="resize:vertical;height:30em;" id="content" class="form-control w-100 mb-2" oninput="savedraft()" placeholder="Content">' . ($_POST["content"] ?? "") . '</textarea><div class="btn-group dropup w-100"><button class="w-100 btn btn-lg btn-primary" name="submit" id="submitbtn">Submit</button><button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" type="button"></button><ul class="dropdown-menu"><li><button type="button" class="dropdown-item" onclick="savedraft(\'saveimmediately\')">Save draft</button></li><li><button type="button" class="dropdown-item dropdown-item-danger" onclick="savedraft(\'discard\')">Discard draft</button></li></ul></div></form>';
    }
    else
    {
        echo $current->accessstatusmsg;
    }
    ?>
    <?= $current->getFooter() ?>
    <script>
        var timeout, delay = 4000;

        function discardDraft() {
            window.scrollTo(0, 0);
            document.getElementById("savealert").innerText = "Discarding...";
            $.ajax({
                url: "save.php",
                cache: false,
                data: {
                    type: "discard"
                },
                type: "post",
                success: function(html) {
                    document.getElementById("savealert").innerHTML = '<span class="text-danger">Draft discarded</span>';
                    $("#to").val("");
                    $("#subject").val("");
                    $("#content").val("");
                },
            });
        }

        function savedraft(type = "save") {
            clearTimeout(timeout);
            if (type == "discard") {
                confirmModal("Discard draft", "Are you sure to discard this draft? This action cannot be undone.", "discardDraft()");
            } else {
                if (type == "saveimmediately") {
                    let today = new Date();
                    let time = ("0" + today.getHours()).slice(-2) + ":" + ("0" + today.getMinutes()).slice(-2) + ":" + ("0" + today.getSeconds()).slice(-2);
                    document.getElementById("savealert").innerText = "Saving...";
                    $.ajax({
                        url: "save.php",
                        cache: false,
                        data: {
                            to: encodeURIComponent($("#to").val()),
                            subject: encodeURIComponent($("#subject").val()),
                            content: encodeURIComponent($("#content").val())
                        },
                        type: "post",
                        success: function(html) {
                            document.getElementById("savealert").innerHTML = "Last saved at " + time + ' <i class="bi bi-check2"></i>';
                        },
                    });
                    window.scrollTo(0, 0);
                    return;
                }
                timeout = setTimeout(function() {
                    let today = new Date();
                    let time = ("0" + today.getHours()).slice(-2) + ":" + ("0" + today.getMinutes()).slice(-2) + ":" + ("0" + today.getSeconds()).slice(-2);
                    document.getElementById("savealert").innerText = "Saving...";
                    $.ajax({
                        url: "save.php",
                        cache: false,
                        data: {
                            to: encodeURIComponent($("#to").val()),
                            subject: encodeURIComponent($("#subject").val()),
                            content: encodeURIComponent($("#content").val())
                        },
                        type: "post",
                        success: function(html) {
                            document.getElementById("savealert").innerHTML = "Last saved at " + time + ' <i class="bi bi-check2"></i>';
                        },
                    });
                }, delay);
            }

        }
    </script>
</body>

</html>