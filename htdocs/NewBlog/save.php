<?php
$current = new Session("Mail", "ComposeMail");

if ($current->accessstatus)
{
    $draftpath = "../Login/Accounts/" . $current->username . "/Drafts/Blog.txt";
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
        $cleanedttitle = htmlspecialchars(urldecode($_POST["title"]));
        $cleanedvisibility = in_array(urldecode($_POST["visibility"]), ["public", "selective", "private"]) ? urldecode($_POST["visibility"]) : "public";
        $cleanedvisibilitytype = urldecode($_POST["visibilitytype"]);
        $cleanedvisibilitytargets = (array) json_decode(urldecode($_POST["visibilitytargets"]));

        filewrite($draftpath, json_encode(array("title" => $cleanedttitle, "content" => $cleanedcontent, "visibility" => $cleanedvisibility, "visibilitytype" => $cleanedvisibilitytype, "visibilitytargets" => $cleanedvisibilitytargets)));
    }
}
