<?php
require_once("../CoreLibrary/CoreFunctions.php");

$current = new Session("Blog", "EditBlog");
require_once("../CoreLibrary/Blog.php");

if ($current->accessstatus && isset($_POST["submit"]) && isset($_POST["title"]) && isset($_POST["content"]) && isset($_POST["visibility"]) && isset($_GET["id"]) && file_exists("../Blog/Blogs/" . $_GET["id"] . ".txt"))
{
  $blog = unserialize(fileread("../Blog/Blogs/" . $_GET["id"] . ".txt"));

  if ($blog !== false && $blog->author->username === $current->username)
  {
    if ($_POST["title"] != $blog->title || $blog->content != $_POST["content"] || $_POST["visibility"] != $blog->visibility || ($_POST["visibility"] === "selective" && ($_POST["visibilitytype"] != $blog->visibility_type || $_POST["visibilitytargets"] != $blog->visibility_targetusers)))
    {
      if ($_POST["visibility"] === "selective")
      {
        $blog = new Blog($current->username, htmlspecialchars($_POST["title"]), $_POST["content"], $blog->time, date("Y-m-d H:i:s"), $blog->likes, $blog->comments, $_POST["visibility"], $_POST["visibilitytype"], $_POST["visibilitytargets"] ?? []);
      }
      else
      {
        $blog = new Blog($current->username, htmlspecialchars($_POST["title"]), $_POST["content"], $blog->time, date("Y-m-d H:i:s"), $blog->likes, $blog->comments, in_array($_POST["visibility"], ["public", "private"]) ? $_POST["visibility"] : "public");
      }

      filewrite("../Blog/Blogs/" . $_GET["id"] . ".txt", serialize($blog));
    }

    header("Location: https://" . SITE_DOMAIN . "/Blog");
    exit();
  }
}
?>
<!DOCTYPE html>
<html>
<?= $current->getHtmlHead() ?>

<body class="container pt-4">
  <header><?= $current->getNavBar() ?></header>
  <?php
  if ($current->accessstatus)
  {
    if (isset($_GET["id"]) && file_exists("../Blog/Blogs/" . $_GET["id"] . ".txt"))
    {
      $blog = unserialize(fileread("../Blog/Blogs/" . $_GET["id"] . ".txt"));
      if ($blog !== false && $blog->author->username === $current->username)
      {
        $_POST["title"] = $blog->title;
        $_POST["content"] = htmlspecialchars($blog->content);
        $_POST["visibility"] = $blog->visibility;
        if ($_POST["visibility"] === "selective")
        {
          $_POST["visibilitytype"] = $blog->visibility_type;
          $_POST["visibilitytargets"] = $blog->visibility_targetusers;
        }

        $userlistoptions = "";

        $visibilitytargetsflipped = array_flip((array)($_POST["visibilitytargets"] ?? []));
        foreach (USER_LIST as $item)
        {
          $testuser = new User($item);
          if (isset($visibilitytargetsflipped[$testuser->username]))
          {
            $userlistoptions .= '<option selected>' . $testuser->username . '</option>';
          }
          else if ($testuser->username != $current->username)
          {
            $userlistoptions .= '<option value="' . $testuser->username . '">' . $testuser->username  . '</option>';
          }
        }

        echo '<h1 class="d-flex"><a class="btn align-self-center me-2" href="/Blog"><i class="bi bi-arrow-left fs-5"></i></a>Edit Blog</h1><hr>';
        echo '<div class="text-end"><a href="/Support/?formatting" target="_blank">How to style text?</a></div><form method="post"><input class="form-control mb-2" type="text" id="title" name="title" placeholder="Title" value="' . ($_POST["title"] ?? "") . '"><textarea placeholder="Content" name="content" style="resize:vertical;height:30em;" id="content" class="form-control w-100 mb-2">' . ($_POST["content"] ?? "") . '</textarea><div class="card mb-2" style="width: 100%;"><div class="card-body"><h5 class="card-title mb-3">Content visibility</h5><div class="form-check mb-2 form-check-inline"><input class="form-check-input" type="radio" name="visibility" value="public" id="public"' . (empty($_POST["visibility"]) || $_POST["visibility"] === "public" ? " checked" : "") . '><label class="form-check-label" for="public"><i class="bi bi-people-fill"></i> Public</label></div><div class="form-check mb-2 form-check-inline"><input class="form-check-input" type="radio" name="visibility" value="private" id="private"' . (!empty($_POST["visibility"]) && $_POST["visibility"] === "private" ? " checked" : "") . '><label class="form-check-label" for="private"><i class="bi bi-person-fill"></i> Private</label></div><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="visibility" value="selective" id="selective" ' . (!empty($_POST["visibility"]) && $_POST["visibility"] === "selective" ? " checked" : "") . '><label style="width:auto;" for="selective"><i class="bi bi-person-check-fill"></i> Selective</label><a class="ms-2" data-bs-toggle="modal" data-bs-target="#selectivesettings" type="button">Edit List</a>
        <div class="modal fade" id="selectivesettings" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5">Selective Settings</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <select class="form-select" id="visibilitytype" name="visibilitytype" style="width:100%;"><option value="include"' . (!empty($_POST["visibilitytype"]) && $_POST["visibilitytype"] === "include" ? " selected" : "") . '>Only include</option><option value="exclude"' . (!empty($_POST["visibilitytype"]) && $_POST["visibilitytype"] === "exclude" ? " selected" : "") . '>Only exclude</option></select><select class="form-select mt-2" style="width:100%;" name="visibilitytargets[]" id="visibilitytargets" multiple size="10">' . ($userlistoptions) . '</select>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Save changes</button>
            </div>
          </div>
        </div>
      </div>
      </div><small class="text-muted" style="font-weight:500;display:block;">Moderators are always able to see all the blogs for moderation purpose.</small></div></div><button class="w-100 btn btn-lg btn-primary" name="submit" id="submitbtn" onclick="preventMisclick($(this))">Submit</button></form>';
      }
      else
      {
        echo '<a class="btn me-2 d-inline-block" href="/Blog"><i class="bi bi-arrow-left" style="font-size:1.5em;"></i></a><div class="d-flex align-items-center text-center" style="height:30rem;"><div class="w-100"><h1>Blog not found</h1><h2>The blog you\'re looking for cannot be found.</h2></div></div>';
      }
    }
    else
    {
      echo '<a class="btn me-2 d-inline-block" href="/Blog"><i class="bi bi-arrow-left" style="font-size:1.5em;"></i></a><div class="d-flex align-items-center text-center" style="height:30rem;"><div class="w-100"><h1>Blog not found</h1><h2>The blog you\'re looking for cannot be found.</h2></div></div>';
    }
  }
  else
  {
    echo $current->accessstatusmsg;
  }
  ?>
  <?= $current->getFooter() ?>
</body>

</html>