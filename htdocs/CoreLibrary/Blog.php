<?php
require_once("CoreFunctions.php");

require_once("DatetimeHandlers.php");

if (empty($current))
{
    throw new CurrentSessionInstanceMissingException();
}

/*
visibility: public, selective private
visibility_type: include, exclude
*/

class Blog
{
    public $author, $title, $content, $time, $edittime, $likes, $comments, $visibility;

    public function __construct(string $author, string $title, string $content, string $time, string|null $edittime, array $likes, array $comments, string $visibility, string|null $visibility_type = null, array|null $visibility_targetusers = null)
    {
        global $current;
        $this->author = new User($author);
        $this->title = $title;
        $this->content = $content;
        $this->time = $time;
        $this->edittime = $edittime;
        $this->likes = $likes;
        $this->comments = $comments;

        $this->visibility = $visibility;
        if ($visibility === "selective")
        {
            $this->visibility_type = $visibility_type;
            $this->visibility_targetusers = $visibility_targetusers;
        }
    }

    public function getDisplayBlog(int $id): string
    {
        global $current;

        $returnval = "1";

        if ($this->isVisibleBy($current))
        {
            $liked = false;
            foreach ($this->likes as $liker)
            {
                if ($liker === $current->username)
                {
                    $liked = true;
                }
            }

            $ifdeletable = $current->username === $this->author->username || $current->hasRole(["moderator"]);

            $commentboxdisplay = "";
            $countcomments = 0;
            foreach ($this->comments as $commentid => $comment)
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
                    $commentboxdisplay .= '<div class="my-3 p-3 bg-body shadow rounded-3" data-commentid="' . $commentid . '"><h5>' . $comment["user"] . $commentuserobj->roleicon . '</h5><p>' . $comment["content"] . '</p>' . ($comment["user"] === $current->username || $ifdeletable ? '<a class="link-danger" href="javascript:confirmModal(\'Delete Comment\',\'Are you sure to delete this comment? This action cannot be undone.\',\'deleteComment($(\\\'#delcombtn-' . $id . '-' . $commentid . '\\\'))\');" style="float:left;text-decoration:none;" id="delcombtn-' . $id . '-' . $commentid . '">Delete</a>' : '') . '<span class="text-muted" style="float:right;">' . displayDatetime($comment["time"]) . '</span><br></div>';
                    ++$countcomments;
                }
            }
            if ($countcomments === 0)
            {
                $commentboxdisplay = '<h6 style="text-align:center;">No comments</h6>';
            }

            $likesdisplay = '';
            foreach ($this->likes as $likeruser)
            {
                try
                {
                    $likeruserobj = new User($likeruser);
                }
                catch (UnknownUsernameException $e)
                {
                    //Do nothing
                }
                $likesdisplay .= '<b style="display:block;font-weight:500;">' . $likeruserobj->usernamefordisplay . '</b>';
            }
            if (empty($likesdisplay))
            {
                $likesdisplay = '<b style="display:block;font-weight:500;">No likes yet</b>';
            }

            require_once("../CoreLibrary/IMP.php");
            $IMP = new IMP();

            $returnval = '<div class="card mt-4" id="' . $id . '"><div class="card-header d-flex d-flex align-items-center"><img src="' . $this->author->getpfp() . '" class="rounded-circle me-2" style="width:2em;"><span class="flex-fill">' . $this->author->usernamefordisplay . '</span>' . ($current->username === $this->author->username ? '<a class="btn" href="/EditBlog?id=' . $id . '"><i class="bi bi-pencil"></i></a>' : '') . ($ifdeletable ? '<a class="btn delBtn" style="color:var(--bs-red);" onclick="confirmModal(\'Delete blog\',\'Are you sure to delete this blog? This action cannot be undone.\',\'deleteBlog(' . $id . ')\')"><i class="bi bi-trash3"></i></a>' : '') . '</div><div class="card-body"><h4 class="card-title">' . (empty($this->title) ? "No title" : $this->title) . '</h4><p class="card-text">' . $IMP->text($this->content) . '</p><p class="text-muted text-end">Posted ' . displayDatetime($this->time) . ($this->edittime !== null ? '<br>Last edited ' . displayDatetime($this->edittime) : "") . '</p></div><div class="card-footer d-flex align-items-center flex-wrap">' . ($liked ? '<button class="btn btn-primary m-2" onclick="toggleLikeBlog($(this))"><i class="bi bi-heart-fill"></i> Liked</button>' : '<button class="btn btn-outline-primary m-2" onclick="toggleLikeBlog($(this))"><i class="bi bi-heart"></i> Like this</button>') . '<div class="dropend flex-fill"><span class="text-muted dropdown-toggle" role="button" data-bs-toggle="dropdown">' . count($this->likes) . '</span><div class="dropdown-menu p-3">' . $likesdisplay . '</div></div>' . ($ifdeletable ? '<span class="text-muted m-3">' . ($this->visibility === "selective" ? '<i class="bi bi-person-check-fill"></i>' : ($this->visibility === "private" ? '<i class="bi bi-person-fill"></i>' : '<i class="bi bi-people-fill"></i>')) . '</span>' : "") . '<button class="btn btn-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#commentbox' . $id . '" style="float:right;"><i class="bi bi-chat-dots"></i> <span>' . $countcomments . '</span> Comments</button><div class="collapse w-100" id="commentbox' . $id . '"><div style="margin:0em .35em 0em .35em;padding:1em;"><div>' . $commentboxdisplay . '</div><hr style="margin-top:0.5em;"><h6>Your comments:</h6><textarea class="form-control" style="min-height:5em;resize:vertical;margin:0.5em 0em 1em 0em;" placeholder="You may use IML for this"></textarea><button class="btn btn-primary w-100" onclick="if($(this).prev().val()!==\'\'){postComment($(this));}">Submit</button></div></div></div></div>';
        }
        return $returnval;
    }

    public function postBlog(): void
    {
        global $current;
        $fileid = count(array_diff(scandir($_SERVER["DOCUMENT_ROOT"] . "/Blog/Blogs"), array('..', '.'))) + 1;
        filewrite($_SERVER["DOCUMENT_ROOT"] . "/Blog/Blogs/$fileid.txt", serialize($this));
    }

    public function isVisibleBy(string|User $user): bool
    {
        global $current;
        try
        {
            $user = ($user instanceof User ? $user : new User($user));
            if ($this->author->username === $current->username || $this->visibility === "public" || $this->visibility === "selective" && ($this->visibility_type === "include") === in_array($current->username, (array)$this->visibility_targetusers) || in_array($current->role, ["moderator", "administrator", "developer"]))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        catch (UnknownUsernameException $ex)
        {
            return false;
        }
    }
}
