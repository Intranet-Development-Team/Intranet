<?php
require("../CoreLibrary/CoreFunctions.php");

$current = new Session("Blog", "Blog");
require("../CoreLibrary/Blog.php");

if ($current->accessstatus && isset($_POST["id"]) && file_exists("../Blog/Blogs/" . $_POST["id"] . ".txt"))
{
    $blogobj = unserialize(fileread("../Blog/Blogs/" . $_POST["id"] . ".txt"));

    if ($blogobj !== false)
    {
        if ($blogobj->isVisibleBy($current))
        {
            $flippedlikelist = array_flip($blogobj->likes);
            if (isset($flippedlikelist[$current->username]))
            {
                unset($flippedlikelist[$current->username]);
                $blogobj->likes = array_flip($flippedlikelist);
                filewrite("../Blog/Blogs/" . $_POST["id"] . ".txt", serialize($blogobj));
                $likesusers = [];
                foreach ($blogobj->likes as $likeruser)
                {
                    try
                    {
                        $likeruserobj = new User($likeruser);
                    }
                    catch (UnknownUsernameException $e)
                    {
                        //Do nothing
                    }
                    $likesusers[] = $likeruser . $likeruserobj->roleicon;
                }
                echo json_encode(["operation" => "unliked", "likesusers" => $likesusers]);
            }
            else
            {
                $blogobj->likes[] = $current->username;
                filewrite("../Blog/Blogs/" . $_POST["id"] . ".txt", serialize($blogobj));
                $likesusers = [];
                foreach ($blogobj->likes as $likeruser)
                {
                    try
                    {
                        $likeruserobj = new User($likeruser);
                    }
                    catch (UnknownUsernameException $e)
                    {
                        //Do nothing
                    }
                    $likesusers[] = $likeruser . $likeruserobj->roleicon;
                }
                echo json_encode(["operation" => "liked", "likesusers" => $likesusers]);

                $bloglikednotification = new Notification("Blog Liked", "Your blog " . (empty($blogobj->title) ? "with No title" : "<i>".$blogobj->title."</i>") . " has been liked by <b>" . $current->username . "</b>.", "Blog", "blog_liked");
                $bloglikednotification->push([$blogobj->author->username]);
            }
        }
    }
}
