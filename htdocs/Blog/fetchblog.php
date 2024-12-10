<?php
require("../CoreLibrary/CoreFunctions.php");

$current = new Session("Blog","Blog");

require("../CoreLibrary/Blog.php");

if($current->accessstatus)
{
    $id = (int)$_GET["id"];
    if(is_file("Blogs/$id.txt"))
    {
        $value = unserialize(fileread("Blogs/$id.txt"));
        if($value !== false)
        {
            echo $value->getDisplayBlog($id);
        }
    }
}
?>