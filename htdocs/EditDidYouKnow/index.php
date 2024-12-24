<?php
require_once("../CoreLibrary/CoreFunctions.php");

$current = new Session("Home", "EditDidYouKnow");

require_once("../CoreLibrary/IMP.php");
$IMP = new IMP();

if ($current->accessstatus && isset($_POST["text"]) && isset($_POST["source"]) && isset($_POST["sourceurl"]) && isset($_POST["previousContentHash"]))
{
  $olddykcontent = fileread("../dyk.txt");
  $olddyk = (array)json_decode($olddykcontent, true);
  $newdyk = json_encode(["text" => $_POST["text"], "source" => htmlspecialchars($_POST["source"]), "sourceurl" => (isURL($_POST["sourceurl"]) ? htmlspecialchars($_POST["sourceurl"]) : htmlspecialchars("https://" . $_POST["sourceurl"]))]);

  if ($newdyk != $olddyk)
  {
    if ($_POST["previousContentHash"] === hash("sha256", $olddykcontent))
    {
      $history = (array)json_decode(fileread("history.txt"), true);
      if (count($history) !== 0)
      {
        $history[count($history) - 1]["content"] = ["text" => $_POST["text"], "source" => htmlspecialchars($_POST["source"]), "sourceurl" => htmlspecialchars($_POST["sourceurl"])];
      }
      $history[] = ["time" => date("Y-m-d H:i:s"), "user" => $current->username, "content" => []];
      filewrite("history.txt", json_encode($history));
      filewrite("../dyk.txt", $newdyk);

      header('Location: http://' . SITE_DOMAIN . '/');
      exit();
    }
    else
    {
      $fails = ["newcontent" => ["text" => $_POST["text"], "source" => htmlspecialchars($_POST["source"]), "sourceurl" => htmlspecialchars($_POST["sourceurl"])]];
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

    $olddykcontent = fileread("../dyk.txt");
    $olddyk = (array)json_decode($olddykcontent, true);

    $historystored = (array)json_decode(fileread("history.txt"), true);
    if (isset($_GET["history"]) && array_key_exists(((int)$_GET["history"] - 1) * 20, $historystored))
    {
      echo '<h1 class="d-flex"><a class="btn align-self-center me-2" href="?"><i class="bi bi-arrow-left fs-5"></i></a>Did You Know History</h1><hr>';


      $itemsdisplay = '';
      $itemsdisplay = '<div class="accordion">';
      $page = (int)$_GET["history"];
      for ($index = (count($historystored) - 1 - ($page - 1) * 20); $index > (count($historystored) - 1 -  $page * 20) && $index >= 0; --$index)
      {
        $itemsdisplay .= '<div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#hist' . $index . '">
          <span class="text-muted me-4" style="display:inline-block;">#' . $index . '</span><span class="me-4" style="display:inline-block;">' . $historystored[$index]["time"] . '</span><span class="me-4" style="display:inline-block;">' . (new User($historystored[$index]["user"]))->usernamefordisplay . '</span>
          </button>
        </h2>
        <div id="hist' . $index . '" class="accordion-collapse collapse">
          <div class="accordion-body"><h3>' . ($index === (count($historystored) - 1) ? $IMP->line($olddyk["text"]) : $IMP->line($historystored[$index]["content"]["text"])) . '</h3><h5 style="float:right;">From <a href="' . ($index === (count($historystored) - 1) ? $olddyk["sourceurl"] : $historystored[$index]["content"]["sourceurl"]) . '" target="_blank">' . ($index === (count($historystored) - 1) ? $olddyk["source"] : $historystored[$index]["content"]["source"]) . '</a></h5>' . ($index === (count($historystored) - 1) ? '<a style="color:var(--bs-green);display:block;font-weight:700;" href="?" class="mt-2">Current version</a>' : '<a class="btn btn-secondary mt-2" href="?revertedit=' . $index . '"><i class="bi bi-arrow-counterclockwise"></i> Revert this version</a>') . '</div>
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
      echo '<h1 class="d-flex"><a class="btn align-self-center me-2" href="/"><i class="bi bi-arrow-left fs-5"></i></a><span class="flex-fill">Edit Did You Know</span>';
      if (!empty($historystored))
      {
        echo '<a class="btn align-self-center me-2" href="?history=1"><i class="bi bi-clock-history fs-5"></i></a>';
      }
      echo '</h1><hr>';

      if (empty($fails))
      {
        $content = $olddyk;
        if (isset($_GET["revertedit"]) && count($historystored) - 1 > $_GET["revertedit"])
        {
          $content = $historystored[$_GET["revertedit"]]["content"];
          echo '<div class="alert alert-secondary" role="alert"><h3>Revertation</h3><p>You are reverting a previous edit. Edit it if needed.</p><a class="btn btn-success" href="?">View current version</a></div>';
        }
        echo '<div style="text-align:right;"><a href="/Support/?formatting" target="_blank">How to style text?</a></div><form method="post" id="editform" onsubmit="preventMisclick($(\'#submitbtn\'))"><input type="text" class="form-control form-control-lg" name="text" placeholder="Knowledge text" value="' . htmlspecialchars($content["text"] ?? "") . '"><input type="text" class="form-control mt-2" name="source" placeholder="Source" value="' . ($content["source"] ?? "") . '"><input type="text" class="form-control mt-2" name="sourceurl" placeholder="Source URL" value="' . ($content["sourceurl"] ?? "") . '"><input type="hidden" name="previousContentHash" value="' . hash("sha256", $olddykcontent) . '"><button class="w-100 btn btn-lg btn-primary mt-2" style="z-index:2;position:relative;" id="submitbtn">Submit</button></form>';
      }
      else
      {
        echo '<h3>Edit conflict</h3><p>Somebody else has edited the Did You Know field while you were editing.</p><p>Please compare your edited version and the currently stored version and then select which one to keep.</p><div class="container"><h6 class="mt-2 mb-2">Your edited version:</h6><div class="card card-body"><h3>' . $IMP->line($fails["newcontent"]["text"]) . '</h3><h5>From <a href="' . $fails["newcontent"]["sourceurl"] . '" target="_blank">' . $fails["newcontent"]["source"] . '</a></h5><form method="post" id="editver"><input type="hidden" name="text" value="' . htmlspecialchars($fails["newcontent"]["text"]) . '"><input type="hidden" name="source" value="' . $fails["newcontent"]["source"] . '"><input type="hidden" name="sourceurl" value="' . $fails["newcontent"]["sourceurl"] . '"><input type="hidden" name="previousContentHash" value="' . hash("sha256", $olddykcontent) . '"></form></div><h6 class="mt-2 mb-2">Currently stored version:</h6><div class="card card-body"><h3>' . $IMP->line($olddyk["text"]) . '</h3><h5>From <a href="' . $olddyk["sourceurl"] . '" target="_blank">' . $olddyk["source"] . '</a></h5></div></div><div class="row"><button class="btn btn-secondary col m-3 mb-0" style="min-width:20em;" onclick="preventMisclick($(this));$(\'#editver\').submit();"><i class="bi bi-pencil-square"></i> Submit my edited version</button><a class="btn btn-success col m-3 mb-0" style="min-width:20em;" href="../"><i class="bi bi-arrow-right"></i> Keep the currently stored version</a></div>';
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