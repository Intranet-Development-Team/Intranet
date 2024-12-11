<?php
require_once("../CoreLibrary/CoreFunctions.php");

$current = new Session("Resources", "Resources");

if ($current->accessstatus && pathInjectionSecure($_GET["filepath"] . $_GET["filename"]))
{
    if (is_file("./Resources Files/FILES/" . $_GET["filepath"] . $_GET["filename"]))
    {
        $pathinfo = pathinfo($_GET["filename"]);
        $filesize = filesize("./Resources Files/FILES/" . $_GET["filepath"] . $_GET["filename"]);
        header('Content-Type: application/' . $pathinfo["extension"]);
        header('Content-Length: ' . $filesize);
        header("Content-Range: 0-" . ($filesize - 1) . "/" . $filesize);
        header('Content-Disposition: attachment; filename="' . $pathinfo["basename"] . '"');
        echo fileread("./Resources Files/FILES/" . $_GET["filepath"] . $_GET["filename"]);
    }
}
