<?php
require_once("../CoreLibrary/CoreFunctions.php");

$current = new Session("Home", "EditQuote");

require_once("../CoreLibrary/IMP.php");
$IMP = new IMP();

if ($current->accessstatus && isset($_POST["quote"]) && isset($_POST["author"]) && isset($_POST["previousContentHash"]))
{
    $oldquotecontent = fileread("../quote.txt");
    $oldquote = (array)json_decode($oldquotecontent, true);
    $newquote = json_encode(["quote" => $_POST["quote"], "author" => htmlspecialchars($_POST["author"])]);

    if ($newquote != $oldquote)
    {
        if ($_POST["previousContentHash"] === hash("sha256", $oldquotecontent))
        {
            $history = (array)json_decode(fileread("history.txt"), true);
            if (count($history) !== 0)
            {
                $history[count($history) - 1]["content"] = ["quote" => $_POST["quote"], "author" => htmlspecialchars($_POST["author"])];
            }
            $history[] = ["time" => date("Y-m-d H:i:s"), "user" => $current->username, "content" => []];
            filewrite("history.txt", json_encode($history));
            filewrite("../quote.txt", $newquote);
            header('Location: http://' . SITE_DOMAIN . '/');
            exit();
        }
        else
        {
            $fails = ["newcontent" => ["quote" => $_POST["quote"], "author" => htmlspecialchars($_POST["author"])]];
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
        $oldquotecontent = fileread("../quote.txt");
        $oldquote = (array)json_decode($oldquotecontent, true);

        $historystored = (array)json_decode(fileread("history.txt"), true);
        if (isset($_GET["history"]) && array_key_exists(((int)$_GET["history"] - 1) * 20, $historystored))
        {
            echo '<h1 class="d-flex"><a class="btn align-self-center me-2" href="?"><i class="bi bi-arrow-left fs-5"></i></a>Quote History</h1><hr>';


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
          <div class="accordion-body"><h3>' . ($index === (count($historystored) - 1) ? $IMP->line($oldquote["quote"]) : $IMP->line($historystored[$index]["content"]["quote"])) . '</h3><h5 style="float:right;">By ' . ($index === (count($historystored) - 1) ? $IMP->line($oldquote["author"]) : $IMP->line($historystored[$index]["content"]["author"])) . '</h5>' . ($index === (count($historystored) - 1) ? '<a style="color:var(--bs-green);display:block;font-weight:700;" href="?" class="mt-2">Current version</a>' : '<a class="btn btn-secondary mt-2" href="?revertedit=' . $index . '"><i class="bi bi-arrow-counterclockwise"></i> Revert this version</a>') . '</div>
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
            echo '<h1 class="d-flex"><a class="btn align-self-center me-2" href="/"><i class="bi bi-arrow-left fs-5"></i></a><span class="flex-fill">Edit Quote</span>';
            if (!empty($historystored))
            {
                echo '<a class="btn align-self-center me-2" href="?history=1"><i class="bi bi-clock-history fs-5"></i></a>';
            }
            echo '</h1><hr>';

            if (empty($fails))
            {
                $content = $oldquote;
                if (isset($_GET["revertedit"]) && count($historystored) - 1 > $_GET["revertedit"])
                {
                    $content = $historystored[$_GET["revertedit"]]["content"];
                    echo '<div class="alert alert-secondary" role="alert"><h3>Revertation</h3><p>You are reverting a previous edit. Edit it if needed.</p><a class="btn btn-success" href="?">View current version</a></div>';
                }
                echo '<div style="text-align:right;"><a href="/Support/?formatting" target="_blank">How to style text?</a></div><form method="post" id="editform" onsubmit="preventMisclick($(\'#submitbtn\'))"><input type="text" class="form-control form-control-lg" name="quote" placeholder="Quote" value="' . htmlspecialchars($content["quote"] ?? "") . '"><input type="text" class="form-control mt-2" name="author" placeholder="Source" value="' . ($content["author"] ?? "") . '"><input type="hidden" name="previousContentHash" value="' . hash("sha256", $oldquotecontent) . '"><button class="w-100 btn btn-lg btn-primary mt-2" style="z-index:2;position:relative;" id="submitbtn">Submit</button></form>';
            }
            else
            {
                echo '<h3>Edit conflict</h3><p>Somebody else has edited the Quote while you were editing.</p><p>Please compare your edited version and the currently stored version and then select which one to keep.</p><div class="container"><h6 class="mt-2 mb-2">Your edited version:</h6><div class="card card-body"><h3>' . $IMP->line($fails["newcontent"]["quote"]) . '</h3><h5>By ' . $fails["newcontent"]["author"] . '</h5><form method="post" id="editver"><input type="hidden" name="quote" value="' . htmlspecialchars($fails["newcontent"]["quote"]) . '"><input type="hidden" name="author" value="' . $fails["newcontent"]["author"] . '"><input type="hidden" name="previousContentHash" value="' . hash("sha256", $oldquotecontent) . '"></form></div><h6 class="mt-2 mb-2">Currently stored version:</h6><div class="card card-body"><h3>' . $IMP->line($oldquote["quote"]) . '</h3><h5>By ' . $oldquote["author"] . '</h5></div></div><div class="row"><button class="btn btn-secondary col m-3 mb-0" style="min-width:20em;" onclick="preventMisclick($(this));$(\'#editver\').submit();"><i class="bi bi-pencil-square"></i> Submit my edited version</button><a class="btn btn-success col m-3 mb-0" style="min-width:20em;" href="../"><i class="bi bi-arrow-right"></i> Keep the currently stored version</a></div>';
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