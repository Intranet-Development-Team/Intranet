<?php
require_once("../CoreLibrary/CoreFunctions.php");

$current = new Session();
require_once("../CoreLibrary/Blog.php");

if ($current->accessstatus && isset($_POST["id"]) && file_exists("../Blog/Blogs/" . $_POST["id"] . ".txt"))
{
    $blogobj = unserialize(fileread("../Blog/Blogs/" . $_POST["id"] . ".txt"));

    if ($blogobj !== false)
    {
        if (isset($_POST["delete"]))
        {
            if (isset($_POST["commentid"]) && isset($blogobj->comments[$_POST["commentid"]]))
            {
                if ($blogobj->isVisibleBy($current) && ($current->username === $blogobj->author->username || $blogobj->comments[$_POST["commentid"]]["user"] === $current->username || $current->hasRole(["moderator"])))
                {
                    $blogobj->comments[$_POST["commentid"]] = "DELETED";
                }
            }
        }
        else if (isset($_POST["comment"]))
        {
            if ($blogobj->isVisibleBy($current))
            {
                require_once("../CoreLibrary/IMP.php");
                $IMP = new IMP();

                $blogobj->comments[] = ["user" => $current->username, "content" => $IMP->text($_POST["comment"]), "time" => date("Y-m-d H:i:s")];
            }

            $blogcommentednotification = new Notification("New Comment", "<b>" . $current->username . "</b> has commented on your blog " . (empty($blogobj->title) ? "with No title" : "<i>" . $blogobj->title . "</i>") . ".", "Blog", "blog_commented");
            $blogcommentednotification->push([$blogobj->author->username]);
        }
        filewrite("../Blog/Blogs/" . $_POST["id"] . ".txt", serialize($blogobj));
        $ifdeletable = $current->username === $blogobj->author->username || $current->hasRole(["moderator"]);
        $commentboxdisplay = "";
        $countcomments = 0;
        foreach ($blogobj->comments as $commentid => $comment)
        {
            if ($comment !== "DELETED")
            {
                try
                {
                    $commentuserobj = new User($comment["user"]);
                }
                catch (UnknownUsernameException $e)
                {
                    //Do nothing
                }
                $commentboxdisplay .= '<div class="my-3 p-3 bg-body shadow rounded-3" data-commentid="' . $commentid . '"><h5>' . $comment["user"] . $commentuserobj->roleicon . '</h5><p>' . $comment["content"] . '</p>' . ($comment["user"] === $current->username || $ifdeletable ? '<a class="link-danger" href="javascript:confirmModal(\'Delete Comment\',\'Are you sure to delete this comment? This action cannot be undone.\',\'deleteComment($(\\\'#delcombtn-' . $_POST["id"] . '-' . $commentid . '\\\'))\',\'Cancel\',\'Delete\');" style="float:left;text-decoration:none;" id="delcombtn-' . $_POST["id"] . '-' . $commentid . '">Delete</a>' : '') . '<span class="text-muted" style="float:right;">' . displayDatetime($comment["time"]) . '</span><br></div>';
                ++$countcomments;
            }
        }
        if ($countcomments === 0)
        {
            $commentboxdisplay = '<h6 style="text-align:center;">No comments</h6>';
        }
        echo json_encode(["comments" => $commentboxdisplay, "count" => $countcomments]);
    }
}
