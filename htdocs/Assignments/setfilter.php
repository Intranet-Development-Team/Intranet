<?php
require_once("../CoreLibrary/CoreFunctions.php");

$current = new Session("Assignments", "Assignments");

if ($current->accessstatus)
{
    if (isset($_POST["missing"]) && isset($_POST["assigned"]) && isset($_POST["done"]))
    {
        $content = [];
        if ($_POST["missing"] === "true")
        {
            $content[] = "missing";
        }
        if ($_POST["assigned"] === "true")
        {
            $content[] = "assigned";
        }
        if ($_POST["done"] === "true")
        {
            $content[] = "done";
        }
        if (!is_dir("../Login/Accounts/" . $current->username . "/Assignments"))
        {
            mkdir("../Login/Accounts/" . $current->username . "/Assignments");
        }
        filewrite("../Login/Accounts/" . $current->username . "/Assignments/FilterSettings.txt", json_encode($content));
    }
}
