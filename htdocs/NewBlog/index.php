<?php
$current = new Session("Blog", "NewBlog");
require("../CoreLibrary/Blog.php");

if ($current->accessstatus && isset($_POST["submit"]) && isset($_POST["title"]) && isset($_POST["content"]) && isset($_POST["visibility"]))
{
    if ($_POST["visibility"] === "selective")
    {
        $blog = new Blog($current->username, htmlspecialchars($_POST["title"]), $_POST["content"], date("Y-m-d H:i:s"), null, [], [], $_POST["visibility"], $_POST["visibilitytype"], $_POST["visibilitytargets"] ?? []);
    }
    else
    {
        $blog = new Blog($current->username, htmlspecialchars($_POST["title"]), $_POST["content"], date("Y-m-d H:i:s"), null, [], [], in_array($_POST["visibility"], ["public", "private"]) ? $_POST["visibility"] : "public");
    }

    $blog->postBlog();

    if (is_file("../Login/Accounts/" . $current->username . "/Drafts/Blog.txt"))
    {
        unlink("../Login/Accounts/" . $current->username . "/Drafts/Blog.txt");
    }

    header("Location: https://" . SITE_DOMAIN . "/Blog");
    exit();
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
        if (is_file("../Login/Accounts/" . $current->username . "/Drafts/Blog.txt"))
        {
            $saveddraft = json_decode(fileread("../Login/Accounts/" . $current->username . "/Drafts/Blog.txt"), true);
            $_POST["title"] = $saveddraft["title"];
            $_POST["content"] = $saveddraft["content"];
            $_POST["visibility"] = $saveddraft["visibility"];
            $_POST["visibilitytype"] = $saveddraft["visibilitytype"];
            $_POST["visibilitytargets"] = $saveddraft["visibilitytargets"];
            unlink("../Login/Accounts/" . $current->username . "/Drafts/Blog.txt");
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
                $userlistoptions .= '<option value="' . $testuser->username . '">' . $testuser->username . '</option>';
            }
        }

        echo '<h1 class="d-flex"><a class="btn align-self-center me-2" href="/Blog"><i class="bi bi-arrow-left fs-5"></i></a>New Blog</h1><hr>';
        echo '<form method="post"><small id="savealert" class="d-block text-center mb-2">Autosave enabled <i class="bi bi-check2"></i></small><div class="text-end"><a href="/Support/?formatting" target="_blank">How to style text?</a></div><input class="form-control mb-2" type="text" id="title" name="title" placeholder="Title" value="' . ($_POST["title"] ?? "") . '" oninput="savedraft()"><textarea placeholder="Content" name="content" style="resize:vertical;height:30em;" id="content" class="form-control w-100 mb-2" oninput="savedraft()">' . ($_POST["content"] ?? "") . '</textarea><div class="card mb-2 w-100"><div class="card-body"><h5 class="card-title mb-3">Content visibility</h5><div class="form-check form-check-inline"><input onchange="savedraft()" class="form-check-input" type="radio" name="visibility" value="public" id="public"' . (empty($_POST["visibility"]) || $_POST["visibility"] === "public" ? " checked" : "") . '><label class="form-check-label" for="public"><i class="bi bi-people-fill"></i> Public</label></div><div class="form-check form-check-inline"><input onchange="savedraft()" class="form-check-input" type="radio" name="visibility" value="private" id="private"' . (!empty($_POST["visibility"]) && $_POST["visibility"] === "private" ? " checked" : "") . '><label class="form-check-label" for="private"><i class="bi bi-person-fill"></i> Private</label></div><div class="form-check form-check-inline"><input onchange="savedraft()" class="form-check-input" type="radio" name="visibility" value="selective" id="selective" ' . (!empty($_POST["visibility"]) && $_POST["visibility"] === "selective" ? " checked" : "") . '><label for="selective"><i class="bi bi-person-check-fill"></i> Selective</label><a class="ms-2" data-bs-toggle="modal" data-bs-target="#selectivesettings" type="button">Edit List</a>
        <div class="modal fade" id="selectivesettings" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5">Selective Settings</h1>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <select class="form-select" id="visibilitytype" name="visibilitytype" style="width:100%;" onchange="savedraft()"><option value="include"' . (!empty($_POST["visibilitytype"]) && $_POST["visibilitytype"] === "include" ? " selected" : "") . '>Only include</option><option value="exclude"' . (!empty($_POST["visibilitytype"]) && $_POST["visibilitytype"] === "exclude" ? " selected" : "") . '>Only exclude</option></select><select class="form-select mt-2" style="width:100%;" name="visibilitytargets[]" id="visibilitytargets" multiple size="10" onchange="savedraft()">' . ($userlistoptions) . '</select>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Save changes</button>
            </div>
          </div>
        </div>
      </div>
      </div><small class="text-muted" style="font-weight:500;display:block;">Moderators are always able to see all the blogs for moderation purpose.</small></div></div><div class="btn-group dropup w-100"><button class="w-100 btn btn-lg btn-primary" name="submit" id="submitbtn" onclick="preventMisclick($(this))">Submit</button><button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false" type="button"></button><ul class="dropdown-menu"><li><button type="button" class="dropdown-item" onclick="savedraft(\'saveimmediately\')">Save draft</button></li><li><button type="button" class="dropdown-item dropdown-item-danger" onclick="savedraft(\'discard\')">Discard draft</button></li></ul></div></form>';
    }
    else
    {
        echo $current->accessstatusmsg;
    }
    ?>
    <?= $current->getFooter() ?>
    <script>
        var timeout, delay = 4000;

        function discardDraft() {
            window.scrollTo(0, 0);
            document.getElementById("savealert").innerText = "Discarding...";
            $.ajax({
                url: "save.php",
                cache: false,
                data: {
                    type: "discard"
                },
                type: "post",
                success: function(html) {
                    document.getElementById("savealert").innerHTML = '<span style="color:red;">Draft discarded</span>';
                    $("#title").val("");
                    $("#content").val("");
                    $("#public").prop("checked", true);
                    $("#visibilitytype option").prop("selected", false);
                    $("#visibilitytargets option").prop("selected", false);
                },
            });
        }

        function savedraft(type = "save") {
            clearTimeout(timeout);
            if (type == "discard") {
                confirmModal("Discard draft", "Are you sure to discard this draft? This action cannot be undone.", "discardDraft()");
            } else {
                if (type == "saveimmediately") {
                    let today = new Date();
                    let time = ("0" + today.getHours()).slice(-2) + ":" + ("0" + today.getMinutes()).slice(-2) + ":" + ("0" + today.getSeconds()).slice(-2);
                    document.getElementById("savealert").innerText = "Saving...";

                    let visibility = $("[name='visibility']:checked").val();

                    let visibilitytargets = new Array;
                    $("#visibilitytargets option:selected").each(function() {
                        visibilitytargets.push($(this).val());
                    });
                    visibilitytargets = JSON.stringify(visibilitytargets);

                    $.ajax({
                        url: "save.php",
                        cache: false,
                        data: {
                            title: encodeURIComponent($("#title").val()),
                            content: encodeURIComponent($("#content").val()),
                            visibility: encodeURIComponent(visibility),
                            visibilitytype: encodeURIComponent($("#visibilitytype").find(":selected").val()),
                            visibilitytargets: encodeURIComponent(visibilitytargets)
                        },
                        type: "post",
                        success: function(html) {
                            document.getElementById("savealert").innerHTML = "Last saved at " + time + " &#10003;";
                        },
                    });
                    window.scrollTo(0, 0);
                    return;
                }
                timeout = setTimeout(function() {
                    let today = new Date();
                    let time = ("0" + today.getHours()).slice(-2) + ":" + ("0" + today.getMinutes()).slice(-2) + ":" + ("0" + today.getSeconds()).slice(-2);
                    document.getElementById("savealert").innerText = "Saving...";

                    let visibility = $("[name='visibility']:checked").val();

                    let visibilitytargets = new Array;
                    $("#visibilitytargets option:selected").each(function() {
                        visibilitytargets.push($(this).val());
                    });
                    visibilitytargets = JSON.stringify(visibilitytargets);

                    $.ajax({
                        url: "save.php",
                        cache: false,
                        data: {
                            title: encodeURIComponent($("#title").val()),
                            content: encodeURIComponent($("#content").val()),
                            visibility: encodeURIComponent(visibility),
                            visibilitytype: encodeURIComponent($("#visibilitytype").find(":selected").val()),
                            visibilitytargets: encodeURIComponent(visibilitytargets)
                        },
                        type: "post",
                        success: function(html) {
                            document.getElementById("savealert").innerHTML = "Last saved at " + time + " &#10003;";
                        },
                    });
                }, delay);
            }

        }
    </script>
</body>

</html>