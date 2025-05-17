<?php
require_once("../CoreLibrary/CoreFunctions.php");

$current = new Session("Calendar", "EditCalendarEvents");

require_once("../CoreLibrary/CalendarEvents.php");

require_once("../CoreLibrary/IMP.php");
$IMP = new IMP();

if ($current->accessstatus)
{
  if ((isset($_POST["newEventsSubjects"]) && isset($_POST["newEventsNames"]) && isset($_POST["newEventsDates"]) && isset($_POST["newEventsStartTimes"]) && isset($_POST["newEventsEndTimes"])) || (isset($_POST["futureTodayEventsSubjects"]) && isset($_POST["futureTodayEventsNames"]) && isset($_POST["futureTodayEventsDates"]) && isset($_POST["futureTodayEventsStartTimes"]) && isset($_POST["futureTodayEventsEndTimes"]) && isset($_POST["originalFutureTodayEventsSubjects"]) && isset($_POST["originalFutureTodayEventsNames"]) && isset($_POST["originalFutureTodayEventsDates"]) && isset($_POST["originalFutureTodayEventsStartTimes"]) && isset($_POST["originalFutureTodayEventsEndTimes"])) || (isset($_POST["pastEventsSubjects"]) && isset($_POST["pastEventsNames"]) && isset($_POST["pastEventsDates"]) && isset($_POST["pastEventsStartTimes"]) && isset($_POST["pastEventsEndTimes"]) && isset($_POST["originalPastEventsSubjects"]) && isset($_POST["originalPastEventsNames"]) && isset($_POST["originalPastEventsDates"]) && isset($_POST["originalPastEventsStartTimes"]) && isset($_POST["originalPastEventsEndTimes"])))
  {
    $fails = [];
    $originalEvents = getAllEvents(false, true);
    $checkOriginalEvents = $originalEvents;

    if (isset($_POST["newEventsSubjects"]) && isset($_POST["newEventsNames"]) && isset($_POST["newEventsDates"]) && isset($_POST["newEventsStartTimes"]) && isset($_POST["newEventsEndTimes"]))
    {
      foreach ($_POST["newEventsNames"] as $key => $content)
      {
        if (!in_array($_POST["newEventsSubjects"][$key], SUBJECT_LIST, true))
        {
          $_POST["newEventsSubjects"][$key] = "Other";
        }
        if (!validateDatetime($_POST["newEventsStartTimes"][$key], 'H:i'))
        {
          $_POST["newEventsStartTimes"][$key] = "00:00";
        }
        if (!validateDatetime($_POST["newEventsEndTimes"][$key], 'H:i'))
        {
          $_POST["newEventsEndTimes"][$key] = "23:59";
        }
        if ($_POST["newEventsNames"] !== "" && validateDatetime($_POST["newEventsDates"][$key], 'Y-m-d'))
        {
          $originalEvents[] = ["subject" => $_POST["newEventsSubjects"][$key], "name" => $content, "date" => $_POST["newEventsDates"][$key], "start" => $_POST["newEventsStartTimes"][$key], "end" => $_POST["newEventsEndTimes"][$key]];
        }
      }
    }

    if (isset($_POST["futureTodayEventsSubjects"]) && isset($_POST["futureTodayEventsNames"]) && isset($_POST["futureTodayEventsDates"]) && isset($_POST["futureTodayEventsStartTimes"]) && isset($_POST["futureTodayEventsEndTimes"]) && isset($_POST["originalFutureTodayEventsSubjects"]) && isset($_POST["originalFutureTodayEventsNames"]) && isset($_POST["originalFutureTodayEventsDates"]) && isset($_POST["originalFutureTodayEventsStartTimes"]) && isset($_POST["originalFutureTodayEventsEndTimes"]))
    {
      foreach ($_POST["originalFutureTodayEventsNames"] as $key => $originalcontent)
      {
        $index = array_search(["subject" => $_POST["originalFutureTodayEventsSubjects"][$key], "name" => $originalcontent, "date" => $_POST["originalFutureTodayEventsDates"][$key], "start" => $_POST["originalFutureTodayEventsStartTimes"][$key], "end" => $_POST["originalFutureTodayEventsEndTimes"][$key]], $originalEvents);
        if ($index !== false)
        {
          if (!in_array($_POST["futureTodayEventsSubjects"][$key], SUBJECT_LIST, true))
          {
            $_POST["futureTodayEventsSubjects"][$key] = "Other";
          }
          if (!validateDatetime($_POST["futureTodayEventsStartTimes"][$key], 'H:i'))
          {
            $_POST["futureTodayEventsStartTimes"][$key] = "00:00";
          }
          if (!validateDatetime($_POST["futureTodayEventsEndTimes"][$key], 'H:i'))
          {
            $_POST["futureTodayEventsEndTimes"][$key] = "23:59";
          }
          if ($_POST["futureTodayEventsNames"][$key] === "" || !validateDatetime($_POST["futureTodayEventsDates"][$key], 'Y-m-d'))
          {
            unset($originalEvents[$index]);
          }
          else if ($_POST["futureTodayEventsNames"][$key] !== "" && validateDatetime($_POST["futureTodayEventsDates"][$key], 'Y-m-d'))
          {
            $originalEvents[$index] = ["subject" => $_POST["futureTodayEventsSubjects"][$key], "name" => $_POST["futureTodayEventsNames"][$key], "date" => $_POST["futureTodayEventsDates"][$key], "start" => $_POST["futureTodayEventsStartTimes"][$key], "end" => $_POST["futureTodayEventsEndTimes"][$key]];
          }
        }
        else
        {
          if (!($_POST["futureTodayEventsNames"][$key] === "" || !validateDatetime($_POST["futureTodayEventsDates"][$key], 'Y-m-d')))
          {
            $fails[] = ["original" => ["subject" => $_POST["originalFutureTodayEventsSubjects"][$key], "name" => htmlspecialchars($originalcontent), "date" => $_POST["originalFutureTodayEventsDates"][$key], "start" => $_POST["originalFutureTodayEventsStartTimes"][$key], "end" => $_POST["originalFutureTodayEventsEndTimes"][$key]], "new" => ["subject" => $_POST["futureTodayEventsSubjects"][$key], "name" => htmlspecialchars($_POST["futureTodayEventsNames"][$key]), "date" => $_POST["futureTodayEventsDates"][$key], "start" => $_POST["futureTodayEventsStartTimes"][$key], "end" => $_POST["futureTodayEventsEndTimes"][$key]]];
          }
        }
      }
    }

    if (isset($_POST["pastEventsSubjects"]) && isset($_POST["pastEventsNames"]) && isset($_POST["pastEventsDates"]) && isset($_POST["pastEventsStartTimes"]) && isset($_POST["pastEventsEndTimes"]) && isset($_POST["originalPastEventsSubjects"]) && isset($_POST["originalPastEventsNames"]) && isset($_POST["originalPastEventsDates"]) && isset($_POST["originalPastEventsStartTimes"]) && isset($_POST["originalPastEventsEndTimes"]))
    {
      foreach ($_POST["originalPastEventsNames"] as $key => $originalcontent)
      {
        $index = array_search(["subject" => $_POST["originalPastEventsSubjects"][$key], "name" => $originalcontent, "date" => $_POST["originalPastEventsDates"][$key], "start" => $_POST["originalPastEventsStartTimes"][$key], "end" => $_POST["originalPastEventsEndTimes"][$key]], $originalEvents);
        if ($index !== false)
        {
          if (!in_array($_POST["pastEventsSubjects"][$key], SUBJECT_LIST, true))
          {
            $_POST["pastEventsSubjects"][$key] = "Other";
          }
          if (!validateDatetime($_POST["pastEventsStartTimes"][$key], 'H:i'))
          {
            $_POST["pastEventsStartTimes"][$key] = "00:00";
          }
          if (!validateDatetime($_POST["pastEventsEndTimes"][$key], 'H:i'))
          {
            $_POST["pastEventsEndTimes"][$key] = "23:59";
          }
          if ($_POST["pastEventsNames"][$key] === "" || !validateDatetime($_POST["pastEventsDates"][$key], 'Y-m-d'))
          {
            unset($originalEvents[$index]);
          }
          else if ($_POST["pastEventsNames"][$key] !== "" && validateDatetime($_POST["pastEventsDates"][$key], 'Y-m-d'))
          {
            $originalEvents[$index] = ["subject" => $_POST["pastEventsSubjects"][$key], "name" => $_POST["pastEventsNames"][$key], "date" => $_POST["pastEventsDates"][$key], "start" => $_POST["pastEventsStartTimes"][$key], "end" => $_POST["pastEventsEndTimes"][$key]];
          }
        }
        else
        {
          if (!($_POST["pastEventsNames"][$key] === "" || !validateDatetime($_POST["pastEventsDates"][$key], 'Y-m-d')))
          {
            $fails[] = ["original" => ["subject" => $_POST["originalPastEventsSubjects"][$key], "name" => htmlspecialchars($originalcontent), "date" => $_POST["originalPastEventsDates"][$key], "start" => $_POST["originalPastEventsStartTimes"][$key], "end" => $_POST["originalPastEventsEndTimes"][$key]], "new" => ["subject" => $_POST["pastEventsSubjects"][$key], "name" => htmlspecialchars($_POST["pastEventsNames"][$key]), "date" => $_POST["pastEventsDates"][$key], "start" => $_POST["pastEventsStartTimes"][$key], "end" => $_POST["pastEventsEndTimes"][$key]]];
          }
        }
      }
    }

    if ($checkOriginalEvents != $originalEvents)
    {
      usort($originalEvents, function ($a, $b)
      {
        return strtotime($a["date"] . "T" . $a["start"]) <=> strtotime($b["date"] . "T" . $b["end"]);
      });
      $history = (array)json_decode(fileread("history.txt"), true);
      if (count($history) !== 0)
      {
        $history[count($history) - 1]["content"] = $checkOriginalEvents;
      }
      $history[] = ["time" => date("Y-m-d H:i:s"), "user" => $current->username, "content" => ""];
      filewrite("history.txt", json_encode($history));
      filewrite("../Calendar/events.txt", json_encode($originalEvents));
    }

    if (empty($fails))
    {
      header("Location: https://" . SITE_DOMAIN . "/Calendar");
      exit();
    }
  }
}

function getSubjectsSelectionInputChoices(string $choice = ""): string
{
  $return = '<option ' . ($choice === "" ? " selected" : "") . ' value="">Subject</option>';
  foreach (SUBJECT_LIST as $subject)
  {
    $return .= '<option value="' . $subject . '" ' . ($choice === $subject ? " selected" : "") . '>' . $subject . '</option>';
  }
  return $return;
}

function getFutureTodayEventsDisplay(int|string $version = "current"): string
{
  $display = '<div class="card"><div class="card-body">';
  $originalEvents = getEventsByRange(strtotime("today"), INF, false, true, $version);

  $counter = 0;
  foreach ($originalEvents as $item)
  {
    $counter++;
    $item["name"] = htmlspecialchars($item["name"]);
    $display .= '<div class="card shadow border-0 m-3"><div class="card-body" style="display:flex;"><select class="form-select me-2 futureTodayEventsSubjects" name="futureTodayEventsSubjects[]" style="max-width:10em;min-width:0em;width:fit-content;">' . getSubjectsSelectionInputChoices($item["subject"]) . '</select><input type="text" class="form-control me-2 futureTodayEventsNames" name="futureTodayEventsNames[]" style="flex-grow:1;" placeholder="Content"  value="' . $item["name"] . '"><input type="date" class="form-control me-2 futureTodayEventsDates" name="futureTodayEventsDates[]" style="width:15em;" value="' . $item["date"] . '"><input type="time" class="form-control me-2 futureTodayEventsStartTimes" name="futureTodayEventsStartTimes[]" style="width:10em;" value="' . $item["start"] . '"><input type="time" class="form-control futureTodayEventsEndTimes" name="futureTodayEventsEndTimes[]" style="width:10em;" value="' . $item["end"] . '"><input type="hidden" class="originalFutureTodayEventsSubjects" name="originalFutureTodayEventsSubjects[]" value="' . $item["subject"] . '"><input type="hidden" class="originalFutureTodayEventsNames" name="originalFutureTodayEventsNames[]" style="flex-grow:1;" placeholder="Content"  value="' . $item["name"] . '"><input type="hidden" class="originalFutureTodayEventsDates" name="originalFutureTodayEventsDates[]" value="' . $item["date"] . '"><input type="hidden" class="originalFutureTodayEventsStartTimes" name="originalFutureTodayEventsStartTimes[]" value="' . $item["start"] . '"><input type="hidden" class="originalFutureTodayEventsEndTimes" name="originalFutureTodayEventsEndTimes[]" value="' . $item["end"] . '"><button type="button" class="btn p-2" onclick="$(this).parent().parent().css(\'display\',\'none\');$(this).parent().children().eq(1).val(\'\');"><i class="bi bi-x-lg"></i></button></div></div>';
  }
  if ($counter == 0)
  {
    $display .= '<h6 style="text-align:center;" class="m-0">No today\'s or future events found</h6></div></div>';
  }
  else
  {
    $display .= '</div></div>';
  }
  return $display;
}

function getPastEventsDisplay(int|string $version = "current"): string
{
  $display = '<div class="card"><div class="card-body">';
  $originalEvents = getEventsByRange(-INF, strtotime("today"), false, true, $version);

  $counter = 0;
  foreach ($originalEvents as $item)
  {
    if (strtotime($item["date"] . "T" . $item["start"]) < strtotime("today"))
    {
      $counter++;
      $item["name"] = htmlspecialchars($item["name"]);
      $display .= '<div class="card shadow border-0 m-3"><div class="card-body" style="display:flex;"><select class="form-select me-2 pastEventsSubjects" name="pastEventsSubjects[]" style="max-width:10em;min-width:0em;width:fit-content;">' . getSubjectsSelectionInputChoices($item["subject"]) . '</select><input type="text" class="form-control me-2 pastEventsNames" name="pastEventsNames[]" style="flex-grow:1;" placeholder="Content"  value="' . $item["name"] . '"><input type="date" class="form-control me-2 pastEventsDates" name="pastEventsDates[]" style="width:15em;" value="' . $item["date"] . '"><input type="time" class="form-control me-2 pastEventsStartTimes" name="pastEventsStartTimes[]" style="width:10em;" value="' . $item["start"] . '"><input type="time" class="form-control pastEventsEndTimes" name="pastEventsEndTimes[]" style="width:10em;" value="' . $item["end"] . '"><input type="hidden" class="form-control me-2 originalPastEventsSubjects" name="originalPastEventsSubjects[]" style="flex-grow:1;" placeholder="Content"  value="' . $item["subject"] . '"><input type="hidden" class="form-control me-2 originalPastEventsNames" name="originalPastEventsNames[]" style="flex-grow:1;" placeholder="Content"  value="' . $item["name"] . '"><input type="hidden" class="originalPastEventsDates" name="originalPastEventsDates[]" value="' . $item["date"] . '"><input type="hidden" class="originalPastEventsStartTimes" name="originalPastEventsStartTimes[]" value="' . $item["start"] . '"><input type="hidden" class="originalPastEventsEndTimes" name="originalPastEventsEndTimes[]" value="' . $item["end"] . '"><button type="button" class="btn p-2" onclick="$(this).parent().parent().css(\'display\',\'none\');$(this).parent().children().eq(1).val(\'\');"><i class="bi bi-x-lg"></i></button></div></div>';
    }
  }
  if ($counter == 0)
  {
    $display .= '<h6 style="text-align:center;" class="m-0">No past events found</h6></div></div>';
  }
  else
  {
    $display .= '</div></div>';
  }
  return $display;
}

function array_diff_multidimensional($array1, $array2): array
{
  $difference = [];
  foreach ($array1 as $value)
  {
    if (!in_array($value, $array2))
    {
      $difference[] = $value;
    }
  }
  return $difference;
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
    $historystored = (array)json_decode(fileread("history.txt"), true);
    if (isset($_GET["history"]) && array_key_exists(((int)$_GET["history"] - 1) * 20, $historystored))
    {
      echo '<h1 class="d-flex"><a class="btn align-self-center me-2" href="?"><i class="bi bi-arrow-left fs-5"></i></a>Calendar Events History</h1><hr>';


      $itemsdisplay = '<div class="accordion">';
      $page = (int)$_GET["history"];
      for ($index = (count($historystored) - 1 - ($page - 1) * 20); $index > (count($historystored) - 1 -  $page * 20) && $index >= 0; --$index)
      {
        $display = '';
        if ($index === count($historystored) - 1)
        {
          $addeditems = array_diff_multidimensional(getAllEvents(false, true), ($index === 0 ? [] : $historystored[$index - 1]["content"]));
          foreach ($addeditems as $item)
          {
            $display .= '<div class="card shadow border border-1 border-success m-1"><div class="card-body" style="display:flex;"><div class="text-success m-auto me-2"><i class="bi bi-plus-lg"></i></div><div class="form-control me-2 text-success" style="width:10em;overflow:auto;">' . $item["subject"] . '</div><div class="form-control me-2 text-success" style="flex-grow:1;overflow:auto;">' . $IMP->line($item["name"]) . '</div><div class="form-control me-2 text-success" style="width:15em;overflow:auto;">' . date("d/m/Y", strtotime($item["date"])) . '</div><div class="form-control me-2 text-success" style="width:10em;overflow:auto;">' . $item["start"] . '</div><div class="form-control text-success" style="width:10em;overflow:auto;">' . $item["end"] . '</div></div></div>';
          }
          $deleteditems = array_diff_multidimensional(($index === 0 ? [] : $historystored[$index - 1]["content"]), getAllEvents(false, true));
          foreach ($deleteditems as $item)
          {
            $display .= '<div class="card shadow border border-1 border-danger m-1"><div class="card-body" style="display:flex;"><div class="text-danger m-auto me-2"><i class="bi bi-dash-lg"></i></div><div class="form-control me-2 text-danger" style="width:10em;overflow:auto;">' . $item["subject"] . '</div><div class="form-control me-2 text-danger" style="flex-grow:1;overflow:auto;">' . $IMP->line($item["name"]) . '</div><div class="form-control me-2 text-danger" style="width:15em;overflow:auto;">' . date("d/m/Y", strtotime($item["date"])) . '</div><div class="form-control me-2 text-danger" style="width:10em;overflow:auto;">' . $item["start"] . '</div><div class="form-control text-danger" style="width:10em;overflow:auto;">' . $item["end"] . '</div></div></div>';
          }
        }
        else
        {
          $addeditems = array_diff_multidimensional($historystored[$index]["content"], ($index === 0 ? [] : $historystored[$index - 1]["content"]));
          foreach ($addeditems as $item)
          {
            $display .= '<div class="card shadow border border-1 border-success m-1"><div class="card-body" style="display:flex;"><div class="text-success m-auto me-2"><i class="bi bi-plus-lg"></i></div><div class="form-control me-2 text-success" style="width:10em;overflow:auto;">' . $item["subject"] . '</div><div class="form-control me-2 text-success" style="flex-grow:1;overflow:auto;">' . $IMP->line($item["name"]) . '</div><div class="form-control me-2 text-success" style="width:15em;overflow:auto;">' . date("d/m/Y", strtotime($item["date"])) . '</div><div class="form-control me-2 text-success" style="width:10em;overflow:auto;">' . $item["start"] . '</div><div class="form-control text-success" style="width:10em;overflow:auto;">' . $item["end"] . '</div></div></div>';
          }
          $deleteditems = array_diff_multidimensional(($index === 0 ? [] : $historystored[$index - 1]["content"]), $historystored[$index]["content"]);
          foreach ($deleteditems as $item)
          {
            $display .= '<div class="card shadow border border-1 border-danger m-1"><div class="card-body" style="display:flex;"><div class="text-danger m-auto me-2"><i class="bi bi-dash-lg"></i></div><div class="form-control me-2 text-danger" style="width:10em;overflow:auto;">' . $item["subject"] . '</div><div class="form-control me-2 text-danger" style="flex-grow:1;overflow:auto;">' . $IMP->line($item["name"]) . '</div><div class="form-control me-2 text-danger" style="width:15em;overflow:auto;">' . date("d/m/Y", strtotime($item["date"])) . '</div><div class="form-control me-2 text-danger" style="width:10em;overflow:auto;">' . $item["start"] . '</div><div class="form-control text-danger" style="width:10em;overflow:auto;">' . $item["end"] . '</div></div></div>';
          }
        }
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
      echo '<h1 class="d-flex"><a class="btn align-self-center me-2" href="/Calendar"><i class="bi bi-arrow-left fs-5"></i></a><span class="flex-fill">Edit Calendar Events</span>';
        if (!empty($historystored))
        {
          echo '<a class="btn align-self-center me-2" href="?history=1"><i class="bi bi-clock-history fs-5"></i></a>';
        }
        echo '</h1><hr>';
      if (empty($fails))
      {
        if (isset($_GET["revertedit"]) && count($historystored) - 1 > $_GET["revertedit"])
        {
          $histid = (int)$_GET["revertedit"];
          echo '<div class="alert alert-secondary" role="alert"><h3>Revertation</h3><p>You are reverting a previous edit. Edit it if needed.</p><a class="btn btn-success" href="?">View current version</a></div>';
        }
        echo '<div style="text-align:right;"><a href="/Support/?formatting" target="_blank">How to style text?</a></div><ul class="nav nav-tabs">
    <li class="nav-item">
      <a class="nav-link" id="newlink" href="#"><i class="bi bi-plus-lg"></i> Add new</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" id="futuretodaylink" href="#futuretoday"><i class="bi bi-hourglass-split"></i> Edit future and today\'s events</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" id="pastlink" href="#past"><i class="bi bi-hourglass-bottom"></i> Edit past events</a>
    </li>
  </ul>';

        echo '<form method="post" id="forms" onsubmit="preventMisclick($(\'#submitbtn\'))"><div id="futuretodaypage">' . getFutureTodayEventsDisplay(isset($histid) ? $histid : "current") . '</div>';
        echo '<div id="pastpage">' . getPastEventsDisplay(isset($histid) ? $histid : "current") . '</div>';
        echo '<div id="newpage"><div class="card"><div class="card-body" id="addNewContainer"><div class="card shadow border-0 m-3"><div class="card-body" style="display:flex;"><select class="form-select me-2" name="newEventsSubjects[]" style="max-width:10em;min-width:0em;width:fit-content;">' . getSubjectsSelectionInputChoices() . '</select><input type="text" class="form-control me-2" name="newEventsNames[]" style="flex-grow:1;" placeholder="Content"><input type="date" class="form-control me-2" name="newEventsDates[]" style="width:15em;"><input type="time" class="form-control me-2" name="newEventsStartTimes[]" style="width:10em;"><input type="time" class="form-control" name="newEventsEndTimes[]" style="width:10em;"><button type="button" class="btn p-2" onclick="$(this).parent().parent().remove()"><i class="bi bi-x-lg"></i></button></div></div></div></form><div class="card-body"><button onclick="newEventItem()" class="btn btn-outline-secondary" style="width:100%;border:dashed;" type="button"><i class="bi bi-plus-lg"></i> New item</button></div></div></div><button type="button" class="btn btn-primary btn-lg w-100 mt-2" onclick="preSubmitOptimization();" id="submitbtn">Submit</button>';
      }
      else
      {
        $displayFailedEdits = "";
        $displayOriginals = "";
        foreach ($fails as $item)
        {
          $displayFailedEdits .= '<div class="card shadow border-0 m-3"><div class="card-body" style="display:flex;"><div class="form-control me-2" style="width:10em;overflow:auto;">' . $item["new"]["subject"] . '</div><div class="form-control me-2" style="flex-grow:1;overflow:auto;">' . $IMP->line($item["new"]["name"]) . '</div><div class="form-control me-2" style="width:15em;overflow:auto;">' . date("d/m/Y", strtotime($item["new"]["date"])) . '</div><div class="form-control me-2" style="width:10em;overflow:auto;">' . $item["new"]["start"] . '</div><div class="form-control" style="width:10em;overflow:auto;">' . $item["new"]["end"] . '</div><input type="hidden" name="newEventsSubjects[]" value="' . $item["new"]["subject"] . '"><input type="hidden" name="newEventsNames[]" value="' . $item["new"]["name"] . '"><input type="hidden" name="newEventsDates[]" value="' . $item["new"]["date"] . '"><input type="hidden" name="newEventsStartTimes[]" value="' . $item["new"]["start"] . '"><input type="hidden" name="newEventsEndTimes[]" value="' . $item["new"]["end"] . '"></div></div>';
          $displayOriginals .= '<div class="card shadow border-0 m-3"><div class="card-body" style="display:flex;"><div class="form-control me-2" style="width:10em;overflow:auto;">' . $item["original"]["subject"] . '</div><div class="form-control me-2" style="flex-grow:1;overflow:auto;">' . $IMP->line($item["original"]["name"]) . '</div><div class="form-control me-2" style="width:15em;overflow:auto;">' . date("d/m/Y", strtotime($item["original"]["date"])) . '</div><div class="form-control me-2" style="width:10em;overflow:auto;">' . $item["original"]["start"] . '</div><div class="form-control" style="width:10em;overflow:auto;">' . $item["original"]["end"] . '</div><input type="hidden" name="newEventsSubjects[]" value="' . $item["original"]["subject"] . '"><input type="hidden" name="newEventsNames[]" value="' . $item["original"]["name"] . '"><input type="hidden" name="newEventsDates[]" value="' . $item["original"]["date"] . '"><input type="hidden" name="newEventsStartTimes[]" value="' . $item["original"]["start"] . '"><input type="hidden" name="newEventsEndTimes[]" value="' . $item["original"]["end"] . '"></div></div>';
        }

        echo '<h3>Edit conflict</h3><p>Some of your edits cannot be made because their original content cannot be found in the current calendar events. This is usually because somebody else has edited the calendar events while you were editing.</p><p>Please choose which action to take.</p><p>Failed edits:</p><div class="card mb-3"><form method="post" id="addNewForm_new" onsubmit="preventMisclick($(\'#addNewForm_newsubmitbtn\'))"><div class="card-body">' . $displayFailedEdits . '</div></form></div><p>Their corresponding original content:</p><div class="card mb-3"><form method="post" id="addNewForm_original" onsubmit="preventMisclick($(\'#addNewForm_originalsubmitbtn\'))"><div class="card-body">' . $displayOriginals . '</div></form></div><div class="row"><button class="btn btn-primary col m-3 mb-0" style="min-width:20em;" onclick="$(\'#addNewForm_new\').submit();" id="addNewForm_newsubmitbtn"><i class="bi bi-plus-lg"></i> Add the failed edits as new events</button><button class="btn btn-secondary col m-3 mb-0" style="min-width:20em;" onclick="$(\'#addNewForm_original\').submit();" id="addNewForm_originalsubmitbtn"><i class="bi bi-clock-history"></i> Add back the original items</button><a class="btn btn-success col m-3 mb-0" style="min-width:20em;" href="../Calendar/"><i class="bi bi-arrow-right"></i> Continue without adding anything</a></div>';
      }
    }
  }
  else
  {
    echo $current->accessstatusmsg;
  }
  ?>
  <?= $current->getFooter() ?>
  <script>
    function newEventItem() {
      $("#addNewContainer").append('<div class="card shadow border-0 m-3"><div class="card-body" style="display:flex;"><select class="form-select me-2" name="newEventsSubjects[]" style="max-width:10em;min-width:0em;width:fit-content;"><?= getSubjectsSelectionInputChoices() ?></select><input type="text" class="form-control me-2" name="newEventsNames[]" style="flex-grow:1;" placeholder="Content"><input type="date" class="form-control me-2" name="newEventsDates[]" style="width:15em;"><input type="time" class="form-control me-2" name="newEventsStartTimes[]" style="width:10em;"><input type="time" class="form-control" name="newEventsEndTimes[]" style="width:10em;"><button type="button" class="btn p-2" onclick="$(this).parent().parent().remove()"><i class="bi bi-x-lg"></i></button></div></div>');
    }

    changePage();
    window.addEventListener("hashchange", changePage);

    function changePage() {
      switch (window.location.hash) {
        case '#futuretoday':
          $("#futuretodaypage").css('display', 'block');
          $("#pastpage").css('display', 'none');
          $("#newpage").css('display', 'none');

          $("#futuretodaylink").addClass("active");
          $("#pastlink").removeClass("active");
          $("#newlink").removeClass("active");
          break;

        case '#past':
          $("#futuretodaypage").css('display', 'none');
          $("#pastpage").css('display', 'block');
          $("#newpage").css('display', 'none');

          $("#futuretodaylink").removeClass("active");
          $("#pastlink").addClass("active");
          $("#newlink").removeClass("active");
          break;

        default:
          $("#futuretodaypage").css('display', 'none');
          $("#pastpage").css('display', 'none');
          $("#newpage").css('display', 'block');

          $("#futuretodaylink").removeClass("active");
          $("#pastlink").removeClass("active");
          $("#newlink").addClass("active");
          break;
      }
    }

    function preSubmitOptimization() {
      var removedFutureToday = 0;
      $('.futureTodayEventsNames').each(function(i, obj) {
        if ($('.futureTodayEventsSubjects').eq(i - removedFutureToday).val() == $('.originalFutureTodayEventsSubjects').eq(i - removedFutureToday).val() && $(obj).val() == $('.originalFutureTodayEventsNames').eq(i - removedFutureToday).val() && $('.futureTodayEventsDates').eq(i - removedFutureToday).val() == $('.originalFutureTodayEventsDates').eq(i - removedFutureToday).val() && $('.futureTodayEventsStartTimes').eq(i - removedFutureToday).val() == $('.originalFutureTodayEventsStartTimes').eq(i - removedFutureToday).val() && $('.futureTodayEventsEndTimes').eq(i - removedFutureToday).val() == $('.originalFutureTodayEventsEndTimes').eq(i - removedFutureToday).val()) {
          $(obj).parent().parent().remove();
          removedFutureToday++;
        }
      });
      var removedPast = 0;
      $('.pastEventsNames').each(function(i, obj) {
        if ($('.pastEventsSubjects').eq(i - removedPast).val() == $('.originalPastEventsSubjects').eq(i - removedPast).val() && $(obj).val() == $('.originalPastEventsNames').eq(i - removedPast).val() && $('.pastEventsDates').eq(i - removedPast).val() == $('.originalPastEventsDates').eq(i - removedPast).val() && $('.pastEventsStartTimes').eq(i - removedPast).val() == $('.originalPastEventsStartTimes').eq(i - removedPast).val() && $('.pastEventsEndTimes').eq(i - removedPast).val() == $('.originalPastEventsEndTimes').eq(i - removedPast).val()) {
          $(obj).parent().parent().remove();
          removedPast++;
        }
      });
      $("#forms").submit();
    }

    $("input").keyup(function(event) {
      if (event.keyCode === 13) {
        event.preventDefault();
        $("#submitbtn").click();
        return false;
      }
    });
  </script>

</body>

</html>