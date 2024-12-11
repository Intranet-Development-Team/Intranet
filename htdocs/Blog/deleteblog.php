<?php
require_once("../CoreLibrary/CoreFunctions.php");

$current = new Session("Blog", "Blog");
require_once("../CoreLibrary/Blog.php");

if ($current->accessstatus && isset($_POST["id"]) && file_exists("../Blog/Blogs/" . $_POST["id"] . ".txt"))
{
    $blogobj = unserialize(fileread("../Blog/Blogs/" . $_POST["id"] . ".txt"));
    if ($blogobj->author->username === $current->username || $current->hasRole(["moderator"]))
    {
        filewrite("../Blog/Blogs/" . $_POST["id"] . ".txt", "DELETED");
        echo json_encode(["status" => "success", "id" => $_POST["id"]]);
    }
    else
    {
        echo json_encode(["status" => "failed", "id" => $_POST["id"]]);
    }
}
else
{
    echo json_encode(["status" => "failed"]);
}
