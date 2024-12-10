<!DOCTYPE html>
<html>
<?php
require("../CoreLibrary/CoreFunctions.php");

$current = new Session("Home", "EditQuote");

require_once("../CoreLibrary/IMP.php");
$IMP = new IMP();

if ($current->accessstatus && isset($_POST["quote"]) && isset($_POST["author"]) && isset($_POST["previousContentHash"]))
{
    $oldquote = fileread("../quote.txt");
    $newquote = json_encode(["quote" => $_POST["quote"], "author" => htmlspecialchars($_POST["author"])]);

    if ($newquote != $oldquote)
    {
        if ($_POST["previousContentHash"] === hash("sha256", $oldquote))
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
<style>
    .btn-group {
        width: 100%;
    }

    .tb {

        border-bottom-left-radius: 0px;
        border-bottom-right-radius: 0px;
        width: 6%;
        -webkit-appearance: none;
        -moz-appearance: none;
        padding-bottom: 0em;
    }

    .dropdown {
        position: relative;
        display: inline-block;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #f9f9f9;
        min-width: 160px;
        box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
        z-index: 1;
    }

    .dropdown-content a {
        color: black;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
    }

    .dropdown-content a:hover {
        background-color: #f1f1f1
    }

    .dropdown:hover .dropdown-content {
        display: block;
    }

    .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
    }

    @media (min-width: 768px) {
        .bd-placeholder-img-lg {
            font-size: 3.5rem;
        }
    }

    html,
    body {
        overflow-x: hidden;
        /* Prevent scroll on narrow devices */
    }

    body {
        padding-top: 3rem;
    }

    @media (max-width: 767.98px) {
        .offcanvas-collapse {
            position: fixed;
            top: 56px;
            /* Height of navbar */
            bottom: 0;
            width: 100%;
            padding-right: 1rem;
            padding-left: 1rem;
            overflow-y: auto;
            background-color: var(--gray-dark);
            transition: -webkit-transform .3s ease-in-out;
            transition: transform .3s ease-in-out;
            transition: transform .3s ease-in-out, -webkit-transform .3s ease-in-out;
            -webkit-transform: translateX(100%);
            transform: translateX(100%);
        }

        .offcanvas-collapse.open {
            -webkit-transform: translateX(-1rem);
            transform: translateX(-1rem);
            /* Account for horizontal padding on navbar */
        }
    }

    .nav-scroller {
        position: relative;
        z-index: 2;
        height: 2.75rem;
        overflow-y: hidden;
    }

    .nav-scroller .nav {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        -ms-flex-wrap: nowrap;
        flex-wrap: nowrap;
        padding-bottom: 1rem;
        margin-top: -1px;
        overflow-x: auto;
        color: rgba(255, 255, 255, .75);
        text-align: center;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
    }

    .nav-underline .nav-link {
        padding-top: .75rem;
        padding-bottom: .75rem;
        font-size: .875rem;
        color: var(--secondary);
    }

    .nav-underline .nav-link:hover {
        color: var(--blue);
    }

    .nav-underline .active {
        font-weight: 500;
        color: var(--gray-dark);
    }

    input[type=submit] {
        width: 100%;
        background-color: #4CAF50;
        color: white;
        padding: 14px 20px;
        margin: 8px 0;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    input[type=submit]:hover {
        background-color: #45a049;
    }

    .text-white-50 {
        color: rgba(255, 255, 255, .5);
    }

    .bg-purple {
        background-color: var(--purple);
    }

    .border-bottom {
        border-bottom: 1px solid #e5e5e5;
    }

    .box-shadow {
        box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .05);
    }

    .lh-100 {
        line-height: 1;
    }

    .lh-125 {
        line-height: 1.25;
    }

    .lh-150 {
        line-height: 1.5;
    }

    .red {
        color: #dc3545 !important;
    }

    .red:hover {
        background-color: #dc3545 !important;
        color: white !important;
    }

    .red:active {
        background-color: #dc3545 !important;
        color: white !important;
    }

    .taskbarbtn {
        font-size: 1.25em !important;
        display: inline-block !important;
        max-width: 5em;
        flex-grow: 1;
    }
</style>
<?= $current->getHtmlHead() ?>

<body class="container">
    <header><?= $current->getNavBar() ?></header>
    <br>
    <?php
    if ($current->accessstatus)
    {
        $oldquote = (array)json_decode(fileread("../quote.txt"), true);

        $historystored = (array)json_decode(fileread("history.txt"), true);
        if (isset($_GET["history"]) && array_key_exists(((int)$_GET["history"] - 1) * 20, $historystored))
        {
            echo '<a class="btn me-2" href="?" style="float:left;margin:auto;"><i class="bi bi-arrow-left" style="font-size:1.5em;"></i></a>';
            echo "<h1>Quote History</h1><hr>";

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
            echo '<a class="btn me-2" href="/" style="float:left;margin:auto;"><i class="bi bi-arrow-left" style="font-size:1.5em;"></i></a>';
            if (!empty($historystored))
            {
                echo '<a class="btn me-2" href="?history=1" style="float:right;margin:auto;"><i class="bi bi-clock-history" style="font-size:1.5em;"></i></a>';
            }
            echo "<h1>Edit Quote</h1><hr>";

            if (empty($fails))
            {
                $content = $oldquote;
                if (isset($_GET["revertedit"]) && count($historystored) - 1 > $_GET["revertedit"])
                {
                    $content = $historystored[$_GET["revertedit"]]["content"];
                    echo '<div class="alert alert-secondary" role="alert"><h3>Revertation</h3><p>You are reverting a previous edit. Edit it if needed.</p><a class="btn btn-success" href="?">View current version</a></div>';
                }
                echo '<div style="text-align:right;"><a href="/Support/?formatting" target="_blank">How to style text?</a></div><form method="post" id="editform"><input type="text" class="form-control form-control-lg" name="quote" placeholder="Quote" value="' . htmlspecialchars($content["quote"] ?? "") . '"><input type="text" class="form-control mt-2" name="author" placeholder="Source" value="' . ($content["author"] ?? "") . '"><input type="hidden" name="previousContentHash" value="' . hash("sha256", json_encode($oldquote)) . '"><button class="w-100 btn btn-lg btn-primary mt-2" style="z-index:2;position:relative;" onclick="preventMisclick($(this))">Submit</button></form>';
            }
            else
            {
                echo '<h3>Edit conflict</h3><p>Somebody else has edited the Quote while you were editing.</p><p>Please compare your edited version and the currently stored version and then select which one to keep.</p><div class="container"><h6 class="mt-2 mb-2">Your edited version:</h6><div class="card card-body"><h3>' . $IMP->line($fails["newcontent"]["quote"]) . '</h3><h5>By ' . $fails["newcontent"]["author"] . '</h5><form method="post" id="editver"><input type="hidden" name="quote" value="' . htmlspecialchars($fails["newcontent"]["quote"]) . '"><input type="hidden" name="author" value="' . $fails["newcontent"]["author"] . '"><input type="hidden" name="previousContentHash" value="' . hash("sha256", json_encode($oldquote)) . '"></form></div><h6 class="mt-2 mb-2">Currently stored version:</h6><div class="card card-body"><h3>' . $IMP->line($oldquote["quote"]) . '</h3><h5>By ' . $oldquote["author"] . '</h5></div></div><div class="row"><button class="btn btn-secondary col m-3 mb-0" style="min-width:20em;" onclick="$(\'#editver\').submit();preventMisclick($(this))"><i class="bi bi-pencil-square"></i> Submit my edited version</button><a class="btn btn-success col m-3 mb-0" style="min-width:20em;" href="../"><i class="bi bi-arrow-right"></i> Keep the currently stored version</a></div>';
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