<?php
require("CoreLibrary/CoreFunctions.php");

$current = new Session("Home");

require("CoreLibrary/CalendarEvents.php");
require("CoreLibrary/Assignments.php");
require_once("CoreLibrary/IMP.php");
$IMP = new IMP();

if ($current->accessstatus)
{
  if (isset($_POST["quo"]) && isset($_POST["aut"]))
  {
    $quotetext = json_encode(["text" => htmlentities($_POST["quo"]), "author" => htmlentities($_POST["aut"])]);
    filewrite("quote.txt", $quotetext);

    $history = (array)json_decode(fileread("quotehistory.txt"), true);
    $history[] = ["time" => date("Y-m-d H:i:s"), "user" => $current->username, "text" => htmlentities($_POST["quo"]), "author" => htmlentities($_POST["aut"])];
    filewrite("quotehistory.txt", json_encode($history));
  }
  else if (isset($_POST["revertquote"]))
  {
    $revertindex = (int)$_POST["revertquote"];
    $revert = (array)json_decode(fileread("quotehistory.txt"), true);

    if (array_key_exists($revertindex, $revert))
    {
      $quotetext = json_encode(["text" => $revert[$revertindex]["text"], "author" => $revert[$revertindex]["author"]]);
      filewrite("quote.txt", $quotetext);

      $history = (array)json_decode(fileread("quotehistory.txt"), true);
      $history[] = ["time" => date("Y-m-d H:i:s"), "user" => $current->username, "text" => $revert[$revertindex]["text"], "author" => $revert[$revertindex]["author"]];
      filewrite("quotehistory.txt", json_encode($history));
    }
  }
  else if (isset($_POST["dyk"]) && isset($_POST["dyksrc"]) && isset($_POST["dyksrcurl"]))
  {
    if (!preg_match('/^https?:\/\/[^\s]+$/', $_POST["dyksrcurl"]))
    {
      $_POST["dyksrcurl"] = "";
    }
    $dyktext = json_encode(["text" => htmlentities($_POST["dyk"]), "source" => htmlentities($_POST["dyksrc"]), "sourceurl" => htmlentities($_POST["dyksrcurl"])]);
    filewrite("dyk.txt", $dyktext);

    $history = (array)json_decode(fileread("dykhistory.txt"), true);
    $history[] = ["time" => date("Y-m-d H:i:s"), "user" => $current->username, "text" => htmlentities($_POST["dyk"]), "source" => htmlentities($_POST["dyksrc"]), "sourceurl" => htmlentities($_POST["dyksrcurl"])];
    filewrite("dykhistory.txt", json_encode($history));
  }
  else if (isset($_POST["revertdyk"]))
  {
    $revertindex = (int)$_POST["revertdyk"];
    $revert = (array)json_decode(fileread("dykhistory.txt"), true);

    if (array_key_exists($revertindex, $revert))
    {
      $quotetext = json_encode(["text" => $revert[$revertindex]["text"], "source" => $revert[$revertindex]["source"], "sourceurl" => $revert[$revertindex]["sourceurl"]]);
      filewrite("dyk.txt", $quotetext);

      $history = (array)json_decode(fileread("dykhistory.txt"), true);
      $history[] = ["time" => date("Y-m-d H:i:s"), "user" => $current->username, "text" => $revert[$revertindex]["text"], "source" => $revert[$revertindex]["source"], "sourceurl" => $revert[$revertindex]["sourceurl"]];
      filewrite("dykhistory.txt", json_encode($history));
    }
  }
}
?>
<!DOCTYPE html>
<html>
<style>
  .carousel {
    margin-bottom: 4rem;
    z-index: 0;
  }

  .carousel-item {
    height: 32rem;
  }

  .carouselbg1 {
    background-color: #b1d3bb;
  }

  .carouselbg2 {
    background-color: #b1d2d3;
  }

  html[data-bs-theme="dark"] .carouselbg1 {
    background-color: #6b8071;
  }

  html[data-bs-theme="dark"] .carouselbg2 {
    background-color: #6b7f80;
  }
</style>
<?= $current->getHtmlHead() ?>

<body>
  <header><?= $current->getNavBar() ?></header>
  <?php
  if ($current->accessstatus)
  {
    $quoteinfo = (array)json_decode(fileread("quote.txt"), true);
    $dykinfo = (array)json_decode(fileread("dyk.txt"), true);
  ?>
    <div id="carousel" class="carousel slide" data-bs-ride="carousel">

      <div class="carousel-indicators">
        <button data-bs-target="#carousel" data-bs-slide-to="0" class="active"></button>
        <button data-bs-target="#carousel" data-bs-slide-to="1"></button>
      </div>

      <div class="carousel-inner">
        <div class="carousel-item active carouselbg1">
          <div class="container">
            <div class="carousel-caption" style="height:100%;">
              <div style="position: absolute;top: 50%;left: 50%;-ms-transform: translate(-50%, -50%);transform: translate(-50%, -50%);width:100%;">
                <h3 style="text-align:center;font-family: Garamond, serif;margin-bottom:1em;display:inline-block;" id="mobcap1" class="text-dark">Quote</h3>
                <a style="position:absolute;right:0;" class="btn text-dark" href="EditQuote"><i class="bi bi-pencil"></i></a>
                <h1 style="font-family: Trebuchet MS;">“<?= $IMP->line($quoteinfo["quote"] ?? "") ?>”
                </h1>
                <h2 style="font-family: Georgia, serif;text-align:right;">—<?= $quoteinfo["author"] ?? "" ?></h2>
              </div>
            </div>
          </div>
        </div>

        <div class="carousel-item carouselbg2">
          <div class="container">
            <div class="carousel-caption" style="height:100%;">
              <div style="position: absolute;top: 50%;left: 50%;-ms-transform: translate(-50%, -50%);transform: translate(-50%, -50%);width:100%;">
                <h3 style="text-align:center;font-family: Garamond, serif;margin-bottom:1em;display:inline-block" id="mobcap1" class="text-dark">Did you know</h3>
                <a style="position:absolute;right:0;" class="btn text-dark" href="EditDidYouKnow"><i class="bi bi-pencil"></i></a>
                <h1 style="font-family: Trebuchet MS;"><?= $IMP->line($dykinfo["text"] ?? "") ?></h1>
                <h5 style="font-family: Georgia, serif;text-align:right;">From <a href="<?= $dykinfo["sourceurl"] ?? "" ?>" target="_blank"><?= $dykinfo["source"] ?? "" ?></a></h5>
              </div>
            </div>
          </div>
        </div>

        <button class="carousel-control-prev" type="button" data-bs-target="#carousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carousel" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </button>

      </div>
    </div>

    <main class="container">
      <div class="mb-5">
        <h1 class="d-flex">
          <span>Announcements
            <?php
            $notificationsystemoperator = new NotificationSystemOperator();
            if ($notificationsystemoperator->getNotificationCountByIDs("announcements_updated") > 0)
            {
              echo '<span class="badge rounded-pill bg-danger ms-2 align-middle" style="font-size:.35em;">Updated</span>';
              $notificationsystemoperator->removeNotificationByIDs("announcements_updated");
            }

            $currentannouncements = fileread("announcements.txt");
            ?>
          </span>
          <div class="d-flex flex-fill justify-content-end align-self-center">
            <a type="button" class="btn align-self-center" href="EditAnnouncements"><i class="bi bi-pencil"></i></a>
          </div>
        </h1>
        <hr>
        <div class="my-3 p-3 bg-body shadow rounded text-break">
          <div>
            <?php
            echo $IMP->text($currentannouncements);
            ?>
          </div>
        </div>
      </div>

    <?php
    $display = '';
    $events = getEventsByRange(strtotime("today"), strtotime("tomorrow"));
    foreach ($events as $event)
    {
      $display .= '<li class="list-group-item">' . ($event["subject"] !== "Other" ? '<b class="me-3">' . $event["subject"] . '</b>' : "") .  $event["name"] . '</span><span class="fw-lighter" style="float:right;">' . $event["start"] . ' - ' . $event["end"] . '</li>';
    }
    $allAssignments = getAssignmentsByRange(strtotime("today"), strtotime("tomorrow"));
    foreach ($allAssignments as $item)
    {
      $display .= '<li class="list-group-item">' . ($item["subject"] !== "Other" ? '<b class="me-3">' . $item["subject"] . '</b>' : "") . 'Submission of <i>' . $item["content"] . '</i></li>';
    }
    if (empty($display))
    {
      $display = '<span class="text-success fw-bold">A free day!</span>';
    }
    echo '<div class="mb-4"><h5>Today we are having...</h5><hr><ul class="my-3 p-3 bg-body shadow list-group list-group-flush rounded text-break">' . $display . '</ul></div>';

    $display = "";
    $allAssignments = getAssignmentsByRange(strtotime("tomorrow"), strtotime("tomorrow +1 days"));
    foreach ($allAssignments as $item)
    {
      $display .= '<li class="list-group-item">' . ($item["subject"] !== "Other" ? '<b class="me-3">' . $item["subject"] . '</b>' : "") . $item["content"] . '</li>';
    }
    if (empty($display))
    {
      $display = '<span class="text-success fw-bold">No homework is due tomorrow!</span>';
    }
    echo '<div class="mb-4"><h5>Assignments due tomorrow</h5><hr><ul class="my-3 p-3 bg-body shadow list-group list-group-flush rounded text-break">' . $display . '</ul></div>';

    $display = "";
    $events = getEventsByRange(strtotime("tomorrow"), strtotime("tomorrow +14 days"));
    foreach ($events as $event)
    {
      $display .= '<li class="list-group-item">' . ($event["subject"] !== "Other" ? '<b class="me-3">' . $event["subject"] . '</b>' : "") .  $event["name"] . '<span style="float:right;">' . ucfirst(displayDatetime($event["date"])) . '<span class="fw-lighter"> | ' . $event["start"] . " - " . $event["end"] . "</span></span></li>";
    }
    if (empty($display))
    {
      $display = '<span class="text-success fw-bold">No events for the next two weeks!</span>';
    }
    echo '<div class="mb-4"><h5>Upcoming events</h5><hr><ul class="my-3 p-3 bg-body shadow list-group list-group-flush rounded text-break"">' . $display . '</ul></div>';
  }
  else
  {
    echo $current->accessstatusmsg;
  }
    ?>
    </main>
    <div class="container">
      <?= $current->getFooter() ?>
    </div>
</body>

</html>