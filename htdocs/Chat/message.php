<?php
require_once("../CoreLibrary/CoreFunctions.php");

$current = new Session("Chat", "Chat");
require_once("../CoreLibrary/IMP.php");
$IMP = new IMP();

if ($current->accessstatus)
{
    if (isset($_POST["msg"]))
    {
        $chattext = urldecode($_POST["msg"]);
        $cleaned = nl2br($IMP->line($chattext));
        $originalt = json_decode(fileread("chatfiles/generalchat.txt"), true) ?? [];
        $originalt[] = [$current->username, $cleaned, date("Y-m-d H:i:s")];
        filewrite("chatfiles/generalchat.txt", json_encode($originalt));
    }
    else
    {
        $chat = (array)json_decode(fileread("chatfiles/generalchat.txt"), true);
        $appearedusers = [];
        foreach ($chat as &$item)
        {
            $testuser = new User($item[0]);
            $item[0] = $testuser->usernamefordisplay;
            $item[] = $testuser->username;
            $item[2] = date("Y-m-d H:i", strtotime($item[2]));
            if (!in_array($testuser->username, $appearedusers))
            {
                $item[] = $testuser->getpfp();
                $appearedusers[] = $testuser->username;
            }
        }
        echo json_encode($chat);
    }
}
