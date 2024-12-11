<?php
require_once("../CoreLibrary/CoreFunctions.php");

$current = new Session("Mail", "ComposeMail");

if ($current->accessstatus)
{
    $draftpath = "../Login/Accounts/" . $current->username . "/Drafts/Mail_Compose.txt";
    if (!is_dir("../Login/Accounts/" . $current->username . "/Drafts"))
    {
        mkdir("../Login/Accounts/" . $current->username . "/Drafts");
    }
    if (isset($_POST["type"]) && $_POST["type"] == "discard")
    {
        if (is_file($draftpath))
        {
            unlink($draftpath);
        }
    }
    else
    {
        $cleanedcontent = htmlspecialchars(urldecode($_POST["content"]));
        $cleanedto = htmlspecialchars(urldecode($_POST["to"]));
        $cleanedsubj = htmlspecialchars(urldecode($_POST["subject"]));
        filewrite($draftpath, json_encode(array("to" => $cleanedto, "subject" => $cleanedsubj, "content" => $cleanedcontent)));
    }
}
