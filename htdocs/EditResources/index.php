<?php
require_once("../CoreLibrary/CoreFunctions.php");

$current = new Session("Resources", "EditResources");

if ($current->accessstatus)
{
  if (isset($_POST["cates"]) && isset($_POST["texts"]) && isset($_POST["urls"]) && isset($_POST["lastversionhash"]))
  {
    $cates = json_decode($_POST["cates"], true);
    $texts = json_decode($_POST["texts"], true);
    $urls = json_decode($_POST["urls"], true);
    $afteralljson = array();
    foreach ($cates as $cateindex => $cate)
    {
      $contents = [];
      foreach ($texts[$cateindex] as $textindex => $text)
      {
        $contents[] = ["name" => htmlspecialchars($text), "url" => (isURL($urls[$cateindex][$textindex]) ? htmlspecialchars($urls[$cateindex][$textindex]) :  htmlspecialchars("https://" . $urls[$cateindex][$textindex]))];
      }
      $afteralljson[] = ["category" => htmlspecialchars($cate), "contents" => $contents];
    }

    $oldLinksJSON = fileread("../Resources/links.txt");
    $oldLinks = (array)json_decode($oldLinksJSON, true);
    if ($afteralljson != $oldLinks)
    {
      if ($_POST["lastversionhash"] === hash("sha256", $oldLinksJSON))
      {
        $history = (array)json_decode(fileread("history.txt"), true);
        if (count($history) !== 0)
        {
          $history[count($history) - 1]["content"] = $oldLinks;
        }
        $history[] = ["time" => date("Y-m-d H:i:s"), "user" => $current->username, "content" => ""];
        filewrite("history.txt", json_encode($history));
        filewrite("../Resources/links.txt", json_encode($afteralljson));
        header("Location: https://" . SITE_DOMAIN . "/Resources");
        exit();
      }
      else
      {
        $mergedjson = [];
        $currentversion = $oldLinks;
        foreach ($afteralljson as $cate)
        {
          $innew = false;
          foreach ($currentversion as $indexcate => $currcate)
          {
            if ($cate["category"] === $currcate["category"])
            {
              $innew = $indexcate;
              $mergedcontent = [];
              foreach ($cate["contents"] as $content)
              {
                $innewcontent = false;
                foreach ($currcate["contents"] as $indexcon => $currcontent)
                {
                  if ($content["url"] === $currcontent["url"])
                  {
                    $innewcontent = $indexcon;
                    $mergedcontent[] = $currcontent;
                    break;
                  }
                }
                if ($innewcontent !== false)
                {
                  unset($currcate["contents"][$innewcontent]);
                }
                else
                {
                  $mergedcontent[] = $content;
                }
              }
              foreach ($currcate["contents"] as $residuecontent)
              {
                $mergedcontent[] = $residuecontent;
              }
              $mergedjson[] = ["category" => $currcate["category"], "contents" => $mergedcontent];
              break;
            }
          }
          if ($innew !== false)
          {
            unset($currentversion[$innew]);
          }
          else
          {
            $mergedjson[] = $cate;
          }
        }

        foreach ($currentversion as $residue)
        {
          $mergedjson[] = $residue;
        }

        $mergedcates = [];
        $mergedtexts = [];
        $mergedurls = [];

        foreach ($mergedjson as $category)
        {
          $mergedcates[] = [$category["category"]];
          $mergedtexts_tmp = [];
          $mergedurls_tmp = [];
          foreach ($category["contents"] as $content)
          {
            $mergedtexts_tmp[] = $content["name"];
            $mergedurls_tmp[] = $content["url"];
          }
          $mergedtexts[] = $mergedtexts_tmp;
          $mergedurls[] = $mergedurls_tmp;
        }

        $mergedcates = htmlspecialchars(json_encode($mergedcates));
        $mergedtexts = htmlspecialchars(json_encode($mergedtexts));
        $mergedurls = htmlspecialchars(json_encode($mergedurls));

        $displaytext_new = '';
        foreach ($afteralljson as $category)
        {
          $displaytext_new .= '<div class="card card-body" style="margin:1%;"><h4>' . htmlspecialchars($category["category"]) . '</h4><p style="font-size:1.1em;line-height: 1.7;">';
          foreach ($category["contents"] as $item)
          {
            $displaytext_new .= '<a target="_blank" href="' . htmlspecialchars($item["url"]) . '">' . htmlspecialchars($item["name"]) . '</a><br>';
          }
          $displaytext_new .= '</p></div>';
        }
        $displaytext_new =  '<div style="display:flex;flex-wrap:wrap;justify-content:stretch;margin:-1%;">' . $displaytext_new . '</div><br>';

        $displaytext_merged = '';
        foreach ($mergedjson as $category)
        {
          $displaytext_merged .= '<div class="card card-body" style="margin:1%;"><h4>' . htmlspecialchars($category["category"]) . '</h4><p style="font-size:1.1em;line-height: 1.7;">';
          foreach ($category["contents"] as $item)
          {
            $displaytext_merged .= '<a target="_blank" href="' . htmlspecialchars($item["url"]) . '">' . htmlspecialchars($item["name"]) . '</a><br>';
          }
          $displaytext_merged .= '</p></div>';
        }
        $displaytext_merged =  '<div style="display:flex;flex-wrap:wrap;justify-content:stretch;margin:-1%;">' . $displaytext_merged . '</div><br>';

        $fails = ["new" => ["cates" => $_POST["cates"], "texts" => $_POST["texts"], "urls" => $_POST["urls"], "display" => $displaytext_new], "merged" => ["cates" => $mergedcates, "texts" => $mergedtexts, "urls" => $mergedurls, "display" => $displaytext_merged]];
      }
    }
    else
    {
      header("Location: https://" . SITE_DOMAIN . "/Resources");
      exit();
    }
  }
}

$sortablelistindex = 0;

function getEditingLinks(int|string $version)
{
  global $sortablelistindex;
  $displayeditor = '';
  if ($version === "current")
  {
    $links = (array)json_decode(fileread("../Resources/links.txt"), true);
  }
  else
  {
    $links = (array)json_decode(fileread("../EditResources/history.txt"), true)[(int)$version]["content"];
  }
  foreach ($links as $category)
  {
    $sortablelistindex++;
    $displayeditor .= '<div class="card card-body mb-4 bg-body-tertiary"><div class="d-flex"><i class="bi bi-arrows-move ms-2 me-3 align-self-center"></i><input type="text" class="form-control form-control-lg me-2" placeholder="Category Name" value="' . htmlspecialchars($category["category"]) . '"><button onclick="dellistitem(this.parentElement.parentElement)" class="btn p-2"><i class="bi bi-x-lg"></i></button></div><ul class="sortable linkscontainer" id="sortable-' . $sortablelistindex . '">';
    foreach ($category["contents"] as $item)
    {
      $displayeditor .= '<li class="ui-state-default bg-secondary-subtle d-flex"><i class="bi bi-arrows-move ms-2 me-3 align-self-center"></i><input type="text" class="form-control linktextitem me-2" style="display:inline-block;flex:40%;" value="' . htmlspecialchars($item["name"]) . '" placeholder="Text on link"><input type="text" class="form-control linkurlitem me-2"" value="' . htmlspecialchars($item["url"]) . '" placeholder="URL"><button onclick="dellistitem(this.parentElement)" class="btn p-2"><i class="bi bi-x-lg"></i></button></li>';
    }
    $displayeditor .= '</ul><button onclick="newliitem(this.previousElementSibling.id)" class="btn bg-dark-subtle w-100" type="button">+ New item</button></div>';
  }
  echo $displayeditor;
}

function getDisplayLinks()
{
  $displaytext = '';
  $links = json_decode(fileread("../Resources/links.txt"), true);
  foreach ($links as $category)
  {
    $displaytext .= '<div class="card card-body" style="margin:1%;"><h4>' . htmlspecialchars($category["category"]) . '</h4><p style="font-size:1.1em;line-height: 1.7;">';
    foreach ($category["contents"] as $item)
    {
      $displaytext .= '<a target="_blank" href="' . htmlspecialchars($item["url"]) . '">' . htmlspecialchars($item["name"]) . '</a><br>';
    }
    $displaytext .= '</p></div>';
  }
  return '<div style="display:flex;flex-wrap:wrap;justify-content:stretch;margin:-1%;">' . $displaytext . '</div><br>';
}
?>
<!DOCTYPE html>
<html>
<style>
  .sortable {
    list-style-type: none;
    padding: 0px;
    margin: 0px;
  }

  .sortable li {
    padding: 0.5em;
    margin: 0.5em;
    border-radius: 0.3em;
  }

  .bi-arrows-move:hover {
    cursor: grab;
  }

  .list-group-item {
    padding-top: 0.35em;
    padding-bottom: 0.35em;
  }
</style>
<?= $current->getHtmlHead() ?>
<script type="text/javascript" src="/Complements/jQuery/jquery-ui.js"></script>

<body class="container pt-4">
  <header><?= $current->getNavBar() ?></header>
  <?php
  if ($current->accessstatus)
  {
    if (!isset($_GET["files"]))
    {
      $historystored = (array)json_decode(fileread("history.txt"), true);
      if (isset($_GET["history"]) && array_key_exists(((int)$_GET["history"] - 1) * 20, $historystored))
      {
        echo '<h1 class="d-flex"><a class="btn align-self-center me-2" href="?"><i class="bi bi-arrow-left fs-5"></i></a>Resources Links History</h1><hr>';

        $itemsdisplay = '<div class="accordion">';
        $page = (int)$_GET["history"];
        for ($index = (count($historystored) - 1 - ($page - 1) * 20); $index > (count($historystored) - 1 -  $page * 20) && $index >= 0; --$index)
        {
          $display = "";
          if ($index === count($historystored) - 1)
          {
            $links = json_decode(fileread("../Resources/links.txt"), true);
            foreach ($links as $category)
            {
              $display .= '<div class="card card-body" style="margin:1%;"><h4>' . htmlspecialchars($category["category"]) . '</h4><p style="font-size:1.1em;line-height: 1.7;">';
              foreach ($category["contents"] as $item)
              {
                $display .= '<a target="_blank" href="' . htmlspecialchars($item["url"]) . '">' . htmlspecialchars($item["name"]) . '</a><br>';
              }
              $display .= '</p></div>';
            }
          }
          else
          {
            foreach ($historystored[$index]["content"] as $category)
            {
              $display .= '<div class="card card-body" style="margin:1%;"><h4>' . htmlspecialchars($category["category"]) . '</h4><p style="font-size:1.1em;line-height: 1.7;">';
              foreach ($category["contents"] as $item)
              {
                $display .= '<a target="_blank" href="' . htmlspecialchars($item["url"]) . '">' . htmlspecialchars($item["name"]) . '</a><br>';
              }
              $display .= '</p></div>';
            }
          }
          $display = '<div style="display:flex;flex-wrap:wrap;justify-content:stretch;margin:-1%;">' . $display . '</div><br>';
          $itemsdisplay .= '<div class="accordion-item">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hist' . $index . '">
            <span class="text-muted me-4" style="display:inline-block;">#' . $index . '</span><span class="me-4" style="display:inline-block;">' . $historystored[$index]["time"] . '</span><span class="me-4" style="display:inline-block;">' . (new User($historystored[$index]["user"]))->usernamefordisplay . '</span>
            </button>
          </h2>
          <div id="hist' . $index . '" class="accordion-collapse collapse">
            <div class="accordion-body"><div class="card card-body">' . $display . '</div>' . ($index === (count($historystored) - 1) ? '<a style="color:var(--bs-green);display:block;font-weight:700;" href="?" class="mt-2">Current version</a>' : '<a class="btn btn-secondary mt-2" href="?revertedit=' . $index . '"><i class="bi bi-arrow-counterclockwise"></i> Revert this version</a>') . '</div>
          </div>
        </div>';
        }
        $itemsdisplay .= '</div><nav>
          <ul class="pagination mt-5">';
        if ($page > 1)
        {
          $itemsdisplay .= '<li class="page-item">
            <a class="page-link" href="?history=' . ($page - 1) . '"> 
            <i class="bi bi-arrow-left"></i>
            </a>
          </li>';
        }
        else
        {
          $itemsdisplay .= '<li class="page-item">
            <a class="page-link disabled" href="#"> 
            <i class="bi bi-arrow-left"></i>
            </a>
          </li>';
        }
        for ($i = 1; $i <= ceil(count($historystored) / 20); $i++)
        {
          if ($i !== $page)
          {
            $itemsdisplay .= '<li class="page-item"><a class="page-link" href="?history=' . $i . '">' . $i . '</a></li>';
          }
          else
          {
            $itemsdisplay .= '<li class="page-item active"><a class="page-link" href="#">' . $i . '</a></li>';
          }
        }
        if ($page < ceil(count($historystored) / 20))
        {
          $itemsdisplay .= '<li class="page-item">
            <a class="page-link" href="?history=' . ($page + 1) . '"> 
            <i class="bi bi-arrow-right"></i>
            </a>
          </li>';
        }
        else
        {
          $itemsdisplay .= '<li class="page-item">
            <a class="page-link disabled" href="#"> 
            <i class="bi bi-arrow-right"></i>
            </a>
          </li>';
        }
        $itemsdisplay .= '</ul>
        </nav>';
        echo $itemsdisplay;
      }
      else
      {
        echo '<h1 class="d-flex"><a class="btn align-self-center me-2" href="/Resources"><i class="bi bi-arrow-left fs-5"></i></a><span class="flex-fill">Edit Resources</span>';
        if (!empty($historystored))
        {
          echo '<a class="btn align-self-center me-2" href="?history=1"><i class="bi bi-clock-history fs-5"></i></a>';
        }
        echo '</h1><hr>';

        if (empty($fails))
        {
          echo '<ul class="nav nav-tabs">
  <li class="nav-item">
    <a class="nav-link active" href="#">Links</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="?files">Files</a>
  </li>
</ul>';
          if (isset($_GET["revertedit"]) && count($historystored) - 1 > $_GET["revertedit"])
          {
            $histid = (int)$_GET["revertedit"];
            echo '<div class="alert alert-secondary" role="alert"><h3>Revertation</h3><p>You are reverting a previous edit. Edit it if needed.</p><a class="btn btn-success" href="?">View current version</a></div>';
          }

          echo '<div class="card card-body mb-4">
    <ul class="sortable" id="sortablex">';
          getEditingLinks(isset($histid) ? $histid : "current");
          echo '</ul><button onclick="newcate()" class="btn bg-dark-subtle w-100" type="button">+ New category</button></div>';
          echo '<button type="button" class="btn btn-primary btn-lg" style="width:100%;" onclick="submlist(this);preventMisclick($(this))" id="submitbtn">Submit</button><form style="display:none;" id="trueform" method="post"><textarea id="cates" name="cates" style="display:none;"></textarea><textarea id="texts" name="texts" style="display:none;"></textarea><textarea id="urls" name="urls" style="display:none;"></textarea><input type="hidden" name="lastversionhash" value="' . hash("sha256", fileread("../Resources/links.txt")) . '"></form>';
        }
        else
        {
          $currhash = hash("sha256", fileread("../Resources/links.txt"));
          echo '<h3 class="mt-2">Edit conflict</h3><p>Somebody else has edited the links while you were editing.</p><p>Please compare your edited version, the currently stored version, and the merged version and then select which one to keep.</p><div class="container"><div class="row"><div class="col" style="width:auto;min-width:20em;"><h6 class="card-subtitle">Your edited version:</h6><form method="post" id="editedverform"><div class="card p-2 mt-2">' . $fails["new"]["display"] . '</div><textarea style="display:none;" name="cates">' . $fails["new"]["cates"] . '</textarea><textarea style="display:none;" name="texts">' . $fails["new"]["texts"] . '</textarea><textarea style="display:none;" name="urls">' . $fails["new"]["urls"] . '</textarea><input type="hidden" name="lastversionhash" value="' . $currhash . '"></form></div><div class="col" style="width:auto;min-width:20em;"><h6 class="card-subtitle">Currently stored version:</h6><div class="card p-2 mt-2">' . getDisplayLinks() . '</div></div></div><div class="row"><div class="col" style="width:auto"><h6 class="card-subtitle">Merged version:</h6><form method="post" id="mergedverform"><div class="card p-2 mt-2">' . $fails["merged"]["display"] . '</div><textarea style="display:none;" name="cates">' . $fails["merged"]["cates"] . '</textarea><textarea style="display:none;" name="texts">' . $fails["merged"]["texts"] . '</textarea><textarea style="display:none;" name="urls">' . $fails["merged"]["urls"] . '</textarea><input type="hidden" name="lastversionhash" value="' . $currhash . '"></form></div></div></div><div class="row"><button class="btn btn-primary col m-3 mb-0" style="min-width:20em;" onclick="$(\'#mergedverform\').submit();preventMisclick($(this))"><i class="bi bi-union"></i> Submit the merged version</button><button class="btn btn-secondary col m-3 mb-0" style="min-width:20em;" onclick="$(\'#editedverform\').submit();preventMisclick($(this))"><i class="bi bi-pencil-square"></i> Submit my edited version</button><a class="btn btn-success col m-3 mb-0" style="min-width:20em;" href="../Resources"><i class="bi bi-arrow-right"></i> Keep the currently stored version</a></div>';
        }
  ?>
        <script>
          function dellistitem(objparent) {
            objparent.remove();
          }

          function newliitem(sortablewithid) {
            var div = document.createElement('div');
            div.innerHTML = '<li class="ui-state-default bg-secondary-subtle d-flex"><i class="bi bi-arrows-move ms-2 me-3 align-self-center"></i><input type="text" class="form-control linktextitem me-2" style="display:inline-block;flex:40%;" placeholder="Text on link"><input type="text" class="form-control linkurlitem me-2"" placeholder="URL"><button onclick="dellistitem(this.parentElement)" class="btn p-2"><i class="bi bi-x-lg"></i></button></li>';
            document.getElementById(sortablewithid).append(div.firstChild);
          }

          function newcate() {
            var div = document.createElement('div');
            var taridid = (parseInt($(".sortable")[$(".sortable").length - 1].id.split("-")[1]) + 1);
            div.innerHTML = '<div class="card card-body mb-4 bg-body-tertiary"><div class="d-flex"><i class="bi bi-arrows-move ms-2 me-3 align-self-center"></i><input type="text" class="form-control form-control-lg me-2" placeholder="Category Name"><button onclick="dellistitem(this.parentElement.parentElement)" class="btn p-2"><i class="bi bi-x-lg"></i></button></div><ul class="sortable linkscontainer" id="sortable-' + taridid + '"><li class="ui-state-default bg-secondary-subtle d-flex"><i class="bi bi-arrows-move ms-2 me-3 align-self-center"></i><input type="text" class="form-control linktextitem me-2" style="display:inline-block;flex:40%;" placeholder="Text on link"><input type="text" class="form-control linkurlitem me-2"" placeholder="URL"><button onclick="dellistitem(this.parentElement)" class="btn p-2"><i class="bi bi-x-lg"></i></button></li></ul><button onclick="newliitem(this.previousElementSibling.id)" class="btn bg-dark-subtle w-100" type="button">+ New item</button></div>';
            document.getElementById('sortablex').append(div.firstChild);
            $('#sortable-' + taridid).sortable({
              handle: ".bi-arrows-move"
            });
            $('#sortable-' + taridid).disableSelection();
          }

          function submlist(thisele) {
            var categoriescards = new Array();
            var alllinktexts = new Array();
            var alllinkurls = new Array();
            $(".form-control-lg").each(function() {
              categoriescards.push(this.value);
            });
            $("ul.sortable").each(function() {
              $(this).each(function() {
                let alllinktextstemp = new Array();
                alllinktextstemp.push($(this).children("li.ui-state-default").each(function() {
                  alllinktextstemp.push($(this).children("input")[0].value);
                }));
                alllinktextstemp.pop();
                alllinktexts.push(alllinktextstemp);
              });
            });
            $("ul.sortable").each(function() {
              $(this).each(function() {
                let alllinkurlstemp = new Array();
                alllinkurlstemp.push($(this).children("li.ui-state-default").each(function() {
                  alllinkurlstemp.push($(this).children("input")[1].value);
                }));
                alllinkurlstemp.pop();
                alllinkurls.push(alllinkurlstemp);
              });

            });
            alllinktexts.shift();
            alllinkurls.shift();
            $("#cates").val(JSON.stringify(categoriescards));
            $("#texts").val(JSON.stringify(alllinktexts));
            $("#urls").val(JSON.stringify(alllinkurls));
            thisele.style.pointerEvents = "none";
            thisele.innerHTML = "Loading...";
            document.getElementById("trueform").submit();
          }

          $(function() {
            <?php
            for (; $sortablelistindex >= 1; $sortablelistindex--)
            {
              echo '$("#sortable-' . $sortablelistindex . '").sortable({ handle: ".bi-arrows-move", connectWith: ".linkscontainer"});
              $("#sortable-' . $sortablelistindex . '").disableSelection();';
            }
            ?>
            $("#sortablex").sortable({
              handle: ".bi-arrows-move"
            });
            $("#sortablex").disableSelection();
          });
        </script>
      <?php
      }
    }
    else
    {
      echo '<h1 class="d-flex"><a class="btn align-self-center me-2" href="/Resources"><i class="bi bi-arrow-left fs-5"></i></a>Edit Resources Files</h1><hr>';
      echo '<ul class="nav nav-tabs">
  <li class="nav-item">
    <a class="nav-link" href="?">Links</a>
  </li>
  <li class="nav-item">
    <a class="nav-link active" href="#">Files</a>
  </li>
</ul>';
      echo '<div class="card">
      <div class="card-body d-flex flex-column" id="innerx" style="height:45em;overflow:auto;">
      <div class="input-group"><button onclick="newfolder()" class="btn bg-dark-subtle flex-grow-1" type="button"><i class="bi bi-folder-plus"></i> New folder</button><button onclick="newfileupload()" class="btn bg-dark-subtle flex-grow-1" type="button"><i class="bi bi-file-earmark-plus"></i> Upload new file</button></div>
        <div class="d-flex align-items-center text-center mb-2"><button id="backBtn" onclick="goOut()" class="btn me-1 border-white" disabled><i class="bi bi-arrow-left align-text-top"></i></button>
          <nav>
            <ol class="breadcrumb" id="bc1">
              <li class="breadcrumb-item active">Home</li>
            </ol>
          </nav>
        </div>
        <ul class="list-group" id="resourcesFiles"></ul>
        <div class="d-flex align-items-center text-center flex-fill" style="display:none;">
          <h6 class="w-100" id="noitemtip"></h6>
        </div>
      </div>
    </div>';
      echo '<div class="modal fade" id="renamefileordiralert">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Rename item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Give a new name to <b id="renamefileordiralert-filefullname"></b>:</p>
  <div style="display:flex;"><input type="text" class="form-control" id="renamefileordiralert-filebasename" placeholder="Enter a name..." value="">
  <b id="renamefileordiralert-fileextension" style="margin-top:0.45em;padding-left:0.5em;"></b></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="renamefilesubmit()">Rename</button>
      </div>
    </div>
  </div>
</div>';
      echo '<div class="modal fade" id="deletefileordiralert">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Delete item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure to delete <b id="deletefileordiralert-filefullname"></b>? This action <b>cannot be undone</b>.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-danger" onclick="deletefilesubmit()">Delete</button>
      </div>
    </div>
  </div>
</div>';
      echo '<div class="modal fade" id="newfolderalert">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create new folder</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Give a new name to the folder:</p>
  <input type="text" class="form-control" id="newfolderalert-foldername" placeholder="Enter a name..." value="">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success" onclick="newfoldersubmit()">Create</button>
      </div>
    </div>
  </div>
</div>';
      echo '<div class="modal fade" id="newfilealert">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Upload new file</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Select a file to upload:</p>
  <input class="form-control" type="file" id="newfilealert-files" accept=".jpg,.jpeg,.jfif,.pjpeg,.pjp,.apng,.avif,.gif,.png,.svg,.webp,.bmp,.tif,.tiff,.eps,.raw,.heif,.heic,.html,.yaml,.md,.xml,.xhtml,.json,.mp3,.aac,.ogg,.wav,.flac,.ape,.alac,.pdf,.webm,.mkv,.flv,.vob,.ogv,.ogg,.avi,.mov,.qt,.wmv,.mpg,.amv,.mp4,.m4p,.m4v,.mpeg,.zip,.rar,.7z,.tar,.gz,.xz,.doc,.docx,.odt,.rtf,.txt,.xls,.xlsx,.ods,.xls,.xlsx,.ods,.ppt,.pptx,.odp">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success" onclick="newfileuploadsubmit()">Upload</button>
      </div>
    </div>
  </div>
</div>';
      ?>
      <script>
        var liele = document.getElementById("resourcesFiles");
        var currentPath = JSON.stringify(new Array());

        var renamefilemodal = new bootstrap.Modal($("#renamefileordiralert"));
        var deletefilemodal = new bootstrap.Modal($("#deletefileordiralert"));
        var newfoldermodal = new bootstrap.Modal($("#newfolderalert"));
        var newfileuploadmodal = new bootstrap.Modal($("#newfilealert"));

        document.getElementById("noitemtip").innerHTML = '<div class="spinner-border" role="status"></div>';

        $.ajax({
          url: "../Resources/scandir.php",
          cache: false,
          success: function(html) {
            document.getElementById("noitemtip").innerHTML = '';
            const objs = Object.values(JSON.parse(html));
            for (var i = 0; i < objs.length; ++i) {
              liele.innerHTML += '<ul class="list-group list-group-horizontal"><li class="list-group-item list-group-item-action" onclick="listitemclicked(this)" id="' + i + '">' + "<span>" + objs[i][0] + "</span><span style=\"color:grey;float:right;margin-top:0.4em;\">" + objs[i][1] + "</span>" + '</li><div class="btn-group" role="group"><button type="button" class="btn border-top border-bottom" style="border-radius:0em;" onclick="renamefile(this.parentElement.previousElementSibling.firstElementChild)"><i class="bi bi-input-cursor-text" style="padding:0em;vertical-align:-0.125em;font-size: 1.5em;"></i></button><button type="button" class="btn border-top border-bottom border-end" style="padding-right:0em;padding-right:0.5em;vertical-align:-0.125em;font-size: 1.5em;" onclick="deletefile(this.parentElement.previousElementSibling.firstElementChild)"><i class="bi bi-trash-fill text-danger"></i></button></div></ul>';
            }
            if (i == 0) {
              document.getElementById("noitemtip").innerHTML = "This folder is empty.";
            }
          },
        });

        function goOut() {
          document.getElementById("backBtn").disabled = true;
          var backenabled = false;
          var pathnow = JSON.parse(currentPath);
          liele.innerHTML = "";
          document.getElementById("noitemtip").innerHTML = '<div class="spinner-border" role="status"></div>';
          pathnow.splice(-1);
          currentPath = JSON.stringify(pathnow);
          document.getElementById("bc1").innerHTML = "<li class=\"breadcrumb-item\">Home</li>";
          for (var i = 0; i < pathnow.length; i++) {
            document.getElementById("bc1").innerHTML += "<li class=\"breadcrumb-item\">" + pathnow[i] + "</li>";
          }
          document.getElementById("bc1").lastElementChild.classList = "breadcrumb-item active";
          if (pathnow.length !== 0) {
            backenabled = true;
          }
          $.ajax({
            url: "../Resources/scandir.php?filepath=" + encodeURIComponent(pathnow.join("/") + (pathnow.length !== 0 ? "/" : "")),
            cache: false,
            success: function(html) {
              document.getElementById("noitemtip").innerHTML = '';
              const objs = Object.values(JSON.parse(html));
              for (var i = 0; i < objs.length; ++i) {
                liele.innerHTML += '<ul class="list-group list-group-horizontal"><li class="list-group-item list-group-item-action" onclick="listitemclicked(this)" id="' + i + '">' + "<span>" + objs[i][0] + "</span><span style=\"color:grey;float:right;margin-top:0.4em;\">" + objs[i][1] + "</span>" + '</li><div class="btn-group" role="group"><button type="button" class="btn border-top border-bottom" style="border-radius:0em;" onclick="renamefile(this.parentElement.previousElementSibling.firstElementChild)"><i class="bi bi-input-cursor-text" style="padding:0em;vertical-align:-0.125em;font-size: 1.5em;"></i></button><button type="button" class="btn border-top border-bottom border-end" style="padding-right:0em;padding-right:0.5em;vertical-align:-0.125em;font-size: 1.5em;" onclick="deletefile(this.parentElement.previousElementSibling.firstElementChild)"><i class="bi bi-trash-fill text-danger"></i></button></div></ul>';
              }
              if (i == 0) {
                document.getElementById("noitemtip").innerHTML = "This folder is empty.";
              }
            },
            complete: function() {
              if (backenabled) {
                document.getElementById("backBtn").disabled = false;
              }
            },
          });
        }

        function goIn(sw2) {
          liele.innerHTML = "";
          document.getElementById("noitemtip").innerHTML = '<div class="spinner-border" role="status"></div>';
          var pathnow = JSON.parse(currentPath);
          pathnow.push(sw2);
          currentPath = JSON.stringify(pathnow);
          document.getElementById("bc1").innerHTML = "<li class=\"breadcrumb-item\">Home</li>";
          for (var i = 0; i < pathnow.length; i++) {
            document.getElementById("bc1").innerHTML += "<li class=\"breadcrumb-item\">" + pathnow[i] + "</li>";
          }
          document.getElementById("bc1").lastElementChild.classList = "breadcrumb-item active";
          $.ajax({
            url: "../Resources/scandir.php?",
            cache: false,
            data: "filepath=" + encodeURIComponent(pathnow.join("/") + "/"),
            success: function(html) {
              if (html != "Failed") {
                document.getElementById("noitemtip").innerHTML = '';
                const objs = Object.values(JSON.parse(html));
                for (var i = 0; i < objs.length; ++i) {
                  liele.innerHTML += '<ul class="list-group list-group-horizontal"><li class="list-group-item list-group-item-action" onclick="listitemclicked(this)" id="' + i + '">' + "<span>" + objs[i][0] + "</span><span style=\"color:grey;float:right;margin-top:0.4em;\">" + objs[i][1] + "</span>" + '</li><div class="btn-group" role="group"><button type="button" class="btn border-top border-bottom" style="border-radius:0em;" onclick="renamefile(this.parentElement.previousElementSibling.firstElementChild)"><i class="bi bi-input-cursor-text" style="padding:0em;vertical-align:-0.125em;font-size: 1.5em;"></i></button><button type="button" class="btn border-top border-bottom border-end" style="padding-right:0em;padding-right:0.5em;vertical-align:-0.125em;font-size: 1.5em;" onclick="deletefile(this.parentElement.previousElementSibling.firstElementChild)"><i class="bi bi-trash-fill text-danger"></i></button></div></ul>';
                }
                if (i == 0) {
                  document.getElementById("noitemtip").innerHTML = "This folder is empty.";
                }
              } else {
                document.getElementById("noitemtip").innerHTML = 'The folder you are finding no longer exists.';
              }
              document.getElementById("backBtn").disabled = false;
            },
            complete: function() {
              document.getElementById("backBtn").disabled = false;
            },
          });
        }

        function renamefile(element) {
          $("#renamefileordiralert-filefullname").html(element.innerText);
          if (element.getElementsByTagName('i')[0].classList.contains("fileitem")) {
            if (element.innerText.split(".").length > 1) {
              $("#renamefileordiralert-filebasename").val(element.innerText.split(".").slice(0, -1).join("."));
              $("#renamefileordiralert-fileextension").html("." + element.innerText.split(".")[element.innerText.split(".").length - 1]);
            } else {
              $("#renamefileordiralert-filebasename").val(element.innerText);
              $("#renamefileordiralert-fileextension").html("");
            }
          } else {
            $("#renamefileordiralert-filebasename").val(element.innerText);
            $("#renamefileordiralert-fileextension").html("");
          }
          renamefilemodal.show();
        }

        function renamefilesubmit() {
          if ($("#renamefileordiralert-filebasename").val() === "") {
            alert("Give a name to the file.");
            return;
          } else if (/[^a-zA-Z0-9_ \(\)]/.test($("#renamefileordiralert-filebasename").val())) {
            alert("Only a-zA-Z0-9_ () are allowed.");
            return;
          } else if (($("#renamefileordiralert-filebasename").val() + $("#renamefileordiralert-fileextension").html()).length > 64) {
            alert("File name cannot exceed 64 characters.");
            return;
          } else if ($("#renamefileordiralert-filefullname").html() === $("#renamefileordiralert-filebasename").val() + $("#renamefileordiralert-fileextension").html()) {
            alert("Give a new name to the file.");
            return;
          }
          document.getElementById("backBtn").disabled = true;
          var backenabled = false;
          renamefilemodal.hide();
          liele.innerHTML = "";
          document.getElementById("noitemtip").innerHTML = 'Renaming...';
          var pathnow = JSON.parse(currentPath);
          if (pathnow.length !== 0) {
            backenabled = true;
          }
          $.ajax({
            url: "fileops.php?rename&filepath=" + encodeURIComponent(pathnow.join("/") + (pathnow.length !== 0 ? "/" : "")),
            type: "post",
            cache: false,
            data: "filename=" + encodeURIComponent($("#renamefileordiralert-filefullname").html()) + "&newfilename=" + encodeURIComponent($("#renamefileordiralert-filebasename").val()),
            success: function(html) {
              if (html != "") {
                document.getElementById("notificationcontainer").innerHTML = html;
              }
              $.ajax({
                url: "../Resources/scandir.php?",
                cache: false,
                data: "filepath=" + encodeURIComponent(pathnow.join("/") + (pathnow.length !== 0 ? "/" : "")),
                success: function(html) {
                  if (html != "Failed") {
                    document.getElementById("noitemtip").innerHTML = '';
                    const objs = Object.values(JSON.parse(html));
                    for (var i = 0; i < objs.length; ++i) {
                      liele.innerHTML += '<ul class="list-group list-group-horizontal"><li class="list-group-item list-group-item-action" onclick="listitemclicked(this)" id="' + i + '">' + "<span>" + objs[i][0] + "</span><span style=\"color:grey;float:right;margin-top:0.4em;\">" + objs[i][1] + "</span>" + '</li><div class="btn-group" role="group"><button type="button" class="btn border-top border-bottom" style="border-radius:0em;" onclick="renamefile(this.parentElement.previousElementSibling.firstElementChild)"><i class="bi bi-input-cursor-text" style="padding:0em;vertical-align:-0.125em;font-size: 1.5em;"></i></button><button type="button" class="btn border-top border-bottom border-end" style="padding-right:0em;padding-right:0.5em;vertical-align:-0.125em;font-size: 1.5em;" onclick="deletefile(this.parentElement.previousElementSibling.firstElementChild)"><i class="bi bi-trash-fill text-danger"></i></button></div></ul>';
                    }
                    if (i == 0) {
                      document.getElementById("noitemtip").innerHTML = "This folder is empty.";
                    }
                  } else {
                    document.getElementById("noitemtip").innerHTML = 'The folder you are finding no longer exists.';
                  }
                },
                complete: function() {
                  if (backenabled) {
                    document.getElementById("backBtn").disabled = false;
                  }
                },
              });
            },
          });
        }

        function deletefile(element) {
          $("#deletefileordiralert-filefullname").html(element.innerText);
          deletefilemodal.show();
        }

        function deletefilesubmit() {
          document.getElementById("backBtn").disabled = true;
          var backenabled = false;
          deletefilemodal.hide();
          liele.innerHTML = "";
          document.getElementById("noitemtip").innerHTML = 'Deleting...';
          var pathnow = JSON.parse(currentPath);
          if (pathnow.length !== 0) {
            backenabled = true;
          }
          $.ajax({
            url: "fileops.php?delete&filepath=" + encodeURIComponent(pathnow.join("/") + (pathnow.length !== 0 ? "/" : "")),
            type: "post",
            cache: false,
            data: "filename=" + encodeURIComponent($("#deletefileordiralert-filefullname").html()),
            success: function(html) {
              if (html != "") {
                document.getElementById("notificationcontainer").innerHTML = html;
              }
              $.ajax({
                url: "../Resources/scandir.php?",
                cache: false,
                data: "filepath=" + encodeURIComponent(pathnow.join("/") + (pathnow.length !== 0 ? "/" : "")),
                success: function(html) {
                  if (html != "Failed") {
                    document.getElementById("noitemtip").innerHTML = '';
                    const objs = Object.values(JSON.parse(html));
                    for (var i = 0; i < objs.length; ++i) {
                      liele.innerHTML += '<ul class="list-group list-group-horizontal"><li class="list-group-item list-group-item-action" onclick="listitemclicked(this)" id="' + i + '">' + "<span>" + objs[i][0] + "</span><span style=\"color:grey;float:right;margin-top:0.4em;\">" + objs[i][1] + "</span>" + '</li><div class="btn-group" role="group"><button type="button" class="btn border-top border-bottom" style="border-radius:0em;" onclick="renamefile(this.parentElement.previousElementSibling.firstElementChild)"><i class="bi bi-input-cursor-text" style="padding:0em;vertical-align:-0.125em;font-size: 1.5em;"></i></button><button type="button" class="btn border-top border-bottom border-end" style="padding-right:0em;padding-right:0.5em;vertical-align:-0.125em;font-size: 1.5em;" onclick="deletefile(this.parentElement.previousElementSibling.firstElementChild)"><i class="bi bi-trash-fill text-danger"></i></button></div></ul>';
                    }
                    if (i == 0) {
                      document.getElementById("noitemtip").innerHTML = "This folder is empty.";
                    }
                  } else {
                    document.getElementById("noitemtip").innerHTML = 'The folder you are finding no longer exists.';
                  }
                },
                complete: function() {
                  if (backenabled) {
                    document.getElementById("backBtn").disabled = false;
                  }
                },
              });
            },
          });
        }

        function newfolder() {
          $("#newfolderalert-foldername").val("");
          newfoldermodal.show();
        }

        function newfoldersubmit() {
          if ($("#newfolderalert-foldername").val() === "") {
            alert("Give a name to the folder.");
            return;
          } else if (/[^a-zA-Z0-9_ \(\)]/.test($("#newfolderalert-foldername").val())) {
            alert("Only a-zA-Z0-9_ () are allowed.");
            return;
          } else if ($("#newfolderalert-foldername").val().length > 64) {
            alert("Folder name cannot exceed 64 characters.");
            return;
          }

          document.getElementById("backBtn").disabled = true;
          var backenabled = false;
          newfoldermodal.hide();
          liele.innerHTML = "";
          document.getElementById("noitemtip").innerHTML = 'Creating...';
          var pathnow = JSON.parse(currentPath);
          if (pathnow.length !== 0) {
            backenabled = true;
          }
          $.ajax({
            url: "fileops.php?newfolder&filepath=" + encodeURIComponent(pathnow.join("/") + (pathnow.length !== 0 ? "/" : "")),
            type: "post",
            cache: false,
            data: "newfoldername=" + encodeURIComponent($("#newfolderalert-foldername").val()),
            success: function(html) {
              if (html != "") {
                document.getElementById("notificationcontainer").innerHTML = html;
              }
              $.ajax({
                url: "../Resources/scandir.php?",
                cache: false,
                data: "filepath=" + encodeURIComponent(pathnow.join("/") + (pathnow.length !== 0 ? "/" : "")),
                success: function(html) {
                  if (html != "Failed") {
                    document.getElementById("noitemtip").innerHTML = '';
                    const objs = Object.values(JSON.parse(html));
                    for (var i = 0; i < objs.length; ++i) {
                      liele.innerHTML += '<ul class="list-group list-group-horizontal"><li class="list-group-item list-group-item-action" onclick="listitemclicked(this)" id="' + i + '">' + "<span>" + objs[i][0] + "</span><span style=\"color:grey;float:right;margin-top:0.4em;\">" + objs[i][1] + "</span>" + '</li><div class="btn-group" role="group"><button type="button" class="btn border-top border-bottom" style="border-radius:0em;" onclick="renamefile(this.parentElement.previousElementSibling.firstElementChild)"><i class="bi bi-input-cursor-text" style="padding:0em;vertical-align:-0.125em;font-size: 1.5em;"></i></button><button type="button" class="btn border-top border-bottom border-end" style="padding-right:0em;padding-right:0.5em;vertical-align:-0.125em;font-size: 1.5em;" onclick="deletefile(this.parentElement.previousElementSibling.firstElementChild)"><i class="bi bi-trash-fill text-danger"></i></button></div></ul>';
                    }
                    if (i == 0) {
                      document.getElementById("noitemtip").innerHTML = "This folder is empty.";
                    }
                  } else {
                    document.getElementById("noitemtip").innerHTML = 'The folder you are finding no longer exists.';
                  }
                },
                complete: function() {
                  if (backenabled) {
                    document.getElementById("backBtn").disabled = false;
                  }
                },
              });
            },
          });
        }

        function newfileupload() {
          $("#newfilealert-files").val("");
          newfileuploadmodal.show();
        }

        function newfileuploadsubmit() {
          if ($("#newfilealert-files").val() === "") {
            alert("Select a file to upload.");
            return;
          } else if (/[^a-zA-Z0-9_ \(\)]/.test(/(?:\/|\\)([^\\\/]*)\..+$/.exec($("#newfilealert-files").val())[0])) {
            alert("Only a-zA-Z0-9_ () are allowed in file name.");
            return;
          } else if ($("#newfilealert-files").val().split(/(\\|\/)/g).pop().length > 64) {
            alert("File name cannot exceed 64 characters.");
            return;
          } else if ($('#newfilealert-files')[0].files[0].size > 10485760) {
            alert("Compress the file to make it smaller than 10 MB (10485760 bytes).");
            return;
          }

          document.getElementById("backBtn").disabled = true;
          var backenabled = false;
          newfileuploadmodal.hide();
          liele.innerHTML = "";
          document.getElementById("noitemtip").innerHTML = 'Uploading...';
          var pathnow = JSON.parse(currentPath);
          if (pathnow.length !== 0) {
            backenabled = true;
          }
          var filedata = new FormData();
          filedata.append('file', $('#newfilealert-files')[0].files[0]);
          $.ajax({
            url: "fileops.php?newfileupload&filepath=" + encodeURIComponent(pathnow.join("/") + (pathnow.length !== 0 ? "/" : "")),
            type: "post",
            cache: false,
            data: filedata,
            processData: false,
            contentType: false,
            success: function(html) {
              if (html != "") {
                document.getElementById("notificationcontainer").innerHTML = html;
              }
              $.ajax({
                url: "../Resources/scandir.php?",
                cache: false,
                data: "filepath=" + encodeURIComponent(pathnow.join("/") + (pathnow.length !== 0 ? "/" : "")),
                success: function(html) {
                  if (html != "Failed") {
                    document.getElementById("noitemtip").innerHTML = '';
                    const objs = Object.values(JSON.parse(html));
                    for (var i = 0; i < objs.length; ++i) {
                      liele.innerHTML += '<ul class="list-group list-group-horizontal"><li class="list-group-item list-group-item-action" onclick="listitemclicked(this)" id="' + i + '">' + "<span>" + objs[i][0] + "</span><span style=\"color:grey;float:right;margin-top:0.4em;\">" + objs[i][1] + "</span>" + '</li><div class="btn-group" role="group"><button type="button" class="btn border-top border-bottom" style="border-radius:0em;" onclick="renamefile(this.parentElement.previousElementSibling.firstElementChild)"><i class="bi bi-input-cursor-text" style="padding:0em;vertical-align:-0.125em;font-size: 1.5em;"></i></button><button type="button" class="btn border-top border-bottom border-end" style="padding-right:0em;padding-right:0.5em;vertical-align:-0.125em;font-size: 1.5em;" onclick="deletefile(this.parentElement.previousElementSibling.firstElementChild)"><i class="bi bi-trash-fill text-danger"></i></button></div></ul>';
                    }
                    if (i == 0) {
                      document.getElementById("noitemtip").innerHTML = "This folder is empty.";
                    }
                  } else {
                    document.getElementById("noitemtip").innerHTML = 'The folder you are finding no longer exists.';
                  }
                },
                complete: function() {
                  if (backenabled) {
                    document.getElementById("backBtn").disabled = false;
                  }
                },
              });
            },
          });
        }

        function listitemclicked(objectclicked) {
          if (!objectclicked.getElementsByTagName('span')[0].getElementsByTagName('i')[0].classList.contains("fileitem")) {
            document.getElementById("backBtn").disabled = true;
            goIn(objectclicked.firstChild.innerText);
          }
        }
      </script>
  <?php
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