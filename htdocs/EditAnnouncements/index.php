<?php
require_once("../CoreLibrary/CoreFunctions.php");

$current = new Session("Home", "EditAnnouncements");

require_once("../CoreLibrary/IMP.php");
$IMP = new IMP();

if ($current->accessstatus && isset($_POST["content"]) && isset($_POST["previousContentHash"]))
{
  $oldannouncements = fileread("../announcements.txt");

  if ($_POST["content"] != $oldannouncements)
  {
    if ($_POST["previousContentHash"] === hash("sha256", $oldannouncements))
    {
      $history = (array)json_decode(fileread("history.txt"), true);
      if (count($history) - 1 >= 0)
      {
        $history[count($history) - 1]["content"] = $oldannouncements;
      }
      $history[] = ["id" => count($history), "time" => date("Y-m-d H:i:s"), "user" => $current->username, "content" => "", "minor" => isset($_POST["minoredit"])];
      filewrite("history.txt", json_encode($history));
      filewrite("../announcements.txt", $_POST["content"]);
      if (!isset($_POST["minoredit"]))
      {
        $announcementsUpdatedNotification = new Notification("Announcements Updated", "Announcements have been updated since your last view.", "Home", "announcements_updated");
        $announcementsUpdatedNotification->pushOnceIfNeeded(USER_LIST);
      }

      header('Location: http://' . SITE_DOMAIN . '/');
      exit();
    }
    else
    {
      $fails = ["newcontent" => $_POST["content"], "minor" => isset($_POST["minoredit"])];
    }
  }
  else
  {
    header('Location: http://' . SITE_DOMAIN . '/');
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
    $oldannouncements = fileread("../announcements.txt");

    $historystored = (array)json_decode(fileread("history.txt"), true);
    if (!empty($oldannouncements))
    {
      $historystored[count($historystored) - 1]["content"] = $oldannouncements;
    }

    if (isset($_GET["history"]) && !empty($historystored) && array_key_exists(((int)$_GET["history"] - 1) * 20, $historystored))
    {
      echo '<h1 class="d-flex"><a class="btn align-self-center me-2" href="?"><i class="bi bi-arrow-left fs-5"></i></a>Announcements History</h1><hr>';

      $itemsdisplay = '<div class="accordion">';
      $page = (int)$_GET["history"];
      $currentversion = count($historystored) - 1;

      for ($index = ($currentversion - ($page - 1) * 20); $index > ($currentversion -  $page * 20) && $index >= 0; --$index)
      {
        $itemsdisplay .= '<div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hist' . $index . '">
          <span class="text-muted me-4" style="display:inline-block;">#' . $index . '</span><span class="me-4" style="display:inline-block;">' . $historystored[$index]["time"] . '</span><span class="me-4" style="display:inline-block;">' . (new User($historystored[$index]["user"]))->usernamefordisplay . '</span>' . ($historystored[$index]["minor"] ? '<span class="me-4" style="display:inline-block;color:grey;">minor</span>' : '') . '
          </button>
        </h2>
        <div id="hist' . $index . '" class="accordion-collapse collapse">
          <div class="accordion-body"><div class="card card-body">' . ($index === $currentversion ? $IMP->text($oldannouncements) : $IMP->text($historystored[$index]["content"])) . '</div>' . ($index === $currentversion ? '<a style="color:var(--bs-green);display:block;font-weight:700;" href="?" class="mt-2">Current version</a>' : '<a class="btn btn-secondary mt-2" href="?revertedit=' . $index . '"><i class="bi bi-arrow-counterclockwise"></i> Revert this version</a>') . '</div>
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
      for ($i = 1; $i <= ceil(($currentversion + 1) / 20); $i++)
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
      if ($page < ceil(($currentversion + 1) / 20))
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
      echo '<h1 class="d-flex"><a class="btn align-self-center me-2" href="/"><i class="bi bi-arrow-left fs-5"></i></a><span class="flex-fill">Edit Announcements</span>';
      if (!empty($historystored))
      {
        echo '<a class="btn align-self-center me-2" href="?history=1"><i class="bi bi-clock-history fs-5"></i></a>';
      }
      echo '</h1><hr>';

      if (empty($fails))
      {
        $content = htmlspecialchars($oldannouncements);
        if (isset($_GET["revertedit"]) && count($historystored) - 1 > $_GET["revertedit"])
        {
          $content = htmlspecialchars($historystored[(int)$_GET["revertedit"]]["content"]);
          echo '<div class="alert alert-secondary" role="alert"><h3>Revertation</h3><p>You are reverting a previous edit. Edit it if needed.</p><a class="btn btn-success" href="?">View current version</a></div>';
        }
        echo '<div class="text-end"><a href="/Support/?formatting" target="_blank">How to style text?</a></div><form method="post" id="editform"><textarea name="content" style="resize:vertical;width:100%;min-height:30em;" id="content" class="form-control">' . $content . '</textarea><div class="form-check mt-2 mb-4"><input class="form-check-input" type="checkbox" name="minoredit" id="minoredit"><label class="form-check-label" for="minoredit">This is a <a href="javascript:alertModal(\'What is a minor edit?\',\'<p>A minor edit is an edit containing no modifications to the actual meaning of the announcement. This includes the correction of grammatical mistakes, spelling mistakes, text formatting etc.</p><p>Minor edits will not notify users, and will be marked minor on the history page.</p>\')">minor edit</a>.</label></div><input type="hidden" name="previousContentHash" value="' . hash("sha256", $oldannouncements) . '"><button class="w-100 btn btn-lg btn-primary mt-2" id="submitbtn" onclick="preventMisclick($(this))">Submit</button></form>';
      }
      else
      {
        echo '<h3>Edit conflict</h3><p>Somebody else has edited the announcements while you were editing.</p><p>Please compare your edited version, the currently stored version, and the merged version and then select which one to keep. You can edit the merged version manually and submit it.</p><div class="container"><div class="row"><div class="col" style="width:auto;min-width:20em;"><h6 class="card-subtitle">Your edited version:</h6><div class="form-control mt-2" style="width:100%;min-height:20em;">' . $IMP->text($fails["newcontent"]) . '</div><form method="post" id="editver"><input type="hidden" name="content" value="' . htmlspecialchars($fails["newcontent"]) . '"><input type="hidden" name="previousContentHash" value="' . hash("sha256", $oldannouncements) . '">' . ($fails["minor"] ? '<input type="hidden" name="minoredit">' : '') . '</form></div><div class="col" style="width:auto;min-width:20em;"><h6 class="card-subtitle">Currently stored version:</h6><div class="form-control mt-2" style="width:100%;min-height:20em;">' . $IMP->text($oldannouncements) . '</div></div></div><div class="row"><div class="col mt-3" style="width:auto;"><h6 class="card-subtitle">Merged version:</h6><div style="text-align:right;"><a href="/Support/?formatting" target="_blank">How to style text?</a></div><form method="post" id="mergedver"><textarea name="content" style="resize:vertical;width:100%;min-height:30em;" id="content" class="form-control mt-2">' . $oldannouncements . "\r\n" . htmlspecialchars($fails["newcontent"]) . '</textarea><div class="form-check mt-2 mb-4"><input class="form-check-input" type="checkbox" name="minoredit" id="minoredit"><label class="form-check-label" for="minoredit">This is a <a href="javascript:alertModal(\'What is a minor edit?\',\'<p>A minor edit is an edit containing no modifications to the actual meaning of the announcement. This includes the correction of grammatical mistakes, spelling mistakes, text formatting etc.</p><p>Minor edits will not notify users, and will be marked minor on the history page.</p>\')">minor edit</a>.</label></div><input type="hidden" name="previousContentHash" value="' . hash("sha256", $oldannouncements) . '"></form></div></div></div><div class="row"><button class="btn btn-primary col m-3 mb-0" style="min-width:20em;" onclick="$(\'#mergedver\').submit();preventMisclick($(this))"><i class="bi bi-union"></i> Submit the merged version</button><button class="btn btn-secondary col m-3 mb-0" style="min-width:20em;" onclick="$(\'#editver\').submit();preventMisclick($(this))"><i class="bi bi-pencil-square"></i> Submit my edited version</button><a class="btn btn-success col m-3 mb-0" style="min-width:20em;" href="../"><i class="bi bi-arrow-right"></i> Keep the currently stored version</a></div>';
      }
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