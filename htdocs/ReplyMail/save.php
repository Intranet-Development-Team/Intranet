<?php
require_once("../CoreLibrary/CoreFunctions.php");

$current = new Session("Mail", "ReplyMail");

if ($current->accessstatus)
{
    $draftpath = "../Login/Accounts/" . $current->username . "/Drafts/Mail_Reply.txt";
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
        $cleanedsubj = htmlspecialchars(urldecode($_POST["subject"]));
        filewrite($draftpath, json_encode(array("subject" => $cleanedsubj, "content" => $cleanedcontent)));
    }
}
