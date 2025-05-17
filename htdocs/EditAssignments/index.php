<?php
require_once("../CoreLibrary/CoreFunctions.php");

$current = new Session("Assignments", "EditAssignments");
require_once("../CoreLibrary/Assignments.php");
require_once("../CoreLibrary/DatetimeHandlers.php");

require_once("../CoreLibrary/IMP.php");
$IMP = new IMP();

if ($current->accessstatus)
{
  if ((isset($_POST["newAssignmentSubjects"]) && isset($_POST["newAssignmentContents"]) && isset($_POST["newAssignmentDueDates"])) || (isset($_POST["assignedAssignmentSubjects"]) && isset($_POST["assignedAssignmentContents"]) && isset($_POST["assignedAssignmentDueDates"]) && isset($_POST["originalAssignedAssignmentSubjects"]) && isset($_POST["originalAssignedAssignmentContents"]) && isset($_POST["originalAssignedAssignmentDueDates"])) || (isset($_POST["expiredAssignmentSubjects"]) && isset($_POST["expiredAssignmentContents"]) && isset($_POST["expiredAssignmentDueDates"]) && isset($_POST["originalExpiredAssignmentSubjects"]) && isset($_POST["originalExpiredAssignmentContents"]) && isset($_POST["originalExpiredAssignmentDueDates"])))
  {
    $fails = [];
    $originalAssignments = getAllAssignments(true);
    $checkOriginalAssignments = $originalAssignments;

    if (isset($_POST["newAssignmentSubjects"]) && isset($_POST["newAssignmentContents"]) && isset($_POST["newAssignmentDueDates"]))
    {
      foreach ($_POST["newAssignmentContents"] as $key => $content)
      {
        if (!in_array($_POST["newAssignmentSubjects"][$key], SUBJECT_LIST, true))
        {
          $_POST["newAssignmentSubjects"][$key] = "Other";
        }
        if ($_POST["newAssignmentContents"][$key] !== "" && ($_POST["newAssignmentDueDates"][$key] === "" || validateDatetime($_POST["newAssignmentDueDates"][$key], "Y-m-d")))
        {
          $originalAssignments[] = ["subject" => $_POST["newAssignmentSubjects"][$key], "content" => $content, "due" => $_POST["newAssignmentDueDates"][$key]];
        }
      }
    }

    if (isset($_POST["assignedAssignmentSubjects"]) && isset($_POST["assignedAssignmentContents"]) && isset($_POST["assignedAssignmentDueDates"]) && isset($_POST["originalAssignedAssignmentSubjects"]) && isset($_POST["originalAssignedAssignmentContents"]) && isset($_POST["originalAssignedAssignmentDueDates"]))
    {
      foreach ($_POST["originalAssignedAssignmentContents"] as $key => $originalcontent)
      {
        if (!in_array($_POST["assignedAssignmentSubjects"][$key], SUBJECT_LIST, true))
        {
          $_POST["assignedAssignmentSubjects"][$key] = "Other";
        }
        if (!in_array($_POST["originalAssignedAssignmentSubjects"][$key], SUBJECT_LIST, true))
        {
          $_POST["originalAssignedAssignmentSubjects"][$key] = "Other";
        }
        $index = array_search(["subject" => $_POST["originalAssignedAssignmentSubjects"][$key], "content" => $originalcontent, "due" => $_POST["originalAssignedAssignmentDueDates"][$key]], $originalAssignments);
        if ($index !== false)
        {
          if ($_POST["assignedAssignmentContents"][$key] === "")
          {
            unset($originalAssignments[$index]);
          }
          else if ($_POST["assignedAssignmentDueDates"][$key] === "" || validateDatetime($_POST["assignedAssignmentDueDates"][$key], "Y-m-d"))
          {
            $originalAssignments[$index] = ["subject" => $_POST["assignedAssignmentSubjects"][$key], "content" => $_POST["assignedAssignmentContents"][$key], "due" => $_POST["assignedAssignmentDueDates"][$key]];
          }
        }
        else if ($_POST["assignedAssignmentContents"][$key] !== "")
        {
          $fails[] = ["original" => ["subject" => $_POST["originalAssignedAssignmentSubjects"][$key], "content" => htmlspecialchars($originalcontent), "due" => $_POST["originalAssignedAssignmentDueDates"][$key]], "new" => ["subject" => $_POST["assignedAssignmentSubjects"][$key], "content" => $_POST["assignedAssignmentContents"][$key], "due" => $_POST["assignedAssignmentDueDates"][$key]]];
        }
      }
    }

    if (isset($_POST["expiredAssignmentSubjects"]) && isset($_POST["expiredAssignmentContents"]) && isset($_POST["expiredAssignmentDueDates"]) && isset($_POST["originalExpiredAssignmentSubjects"]) && isset($_POST["originalExpiredAssignmentContents"]) && isset($_POST["originalExpiredAssignmentDueDates"]))
    {
      foreach ($_POST["originalExpiredAssignmentContents"] as $key => $originalcontent)
      {
        if (!in_array($_POST["expiredAssignmentSubjects"][$key], SUBJECT_LIST, true))
        {
          $_POST["expiredAssignmentSubjects"][$key] = "Other";
        }
        if (!in_array($_POST["originalExpiredAssignmentSubjects"][$key], SUBJECT_LIST, true))
        {
          $_POST["originalExpiredAssignmentSubjects"][$key] = "Other";
        }
        $index = array_search(["subject" => $_POST["originalExpiredAssignmentSubjects"][$key], "content" => $originalcontent, "due" => $_POST["originalExpiredAssignmentDueDates"][$key]], $originalAssignments);
        if ($index !== false)
        {
          if ($_POST["expiredAssignmentContents"][$key] === "")
          {
            unset($originalAssignments[$index]);
          }
          else if ($_POST["expiredAssignmentDueDates"][$key] === "" || validateDatetime($_POST["expiredAssignmentDueDates"][$key], "Y-m-d"))
          {
            $originalAssignments[$index] = ["subject" => $_POST["expiredAssignmentSubjects"][$key], "content" => $_POST["expiredAssignmentContents"][$key], "due" => $_POST["expiredAssignmentDueDates"][$key]];
          }
        }
        else
        {
          if ($_POST["expiredAssignmentContents"][$key] !== "")
          {
            $fails[] = ["original" => ["subject" => $_POST["originalExpiredAssignmentSubjects"][$key], "content" => htmlspecialchars($originalcontent), "due" => $_POST["originalExpiredAssignmentDueDates"][$key]], "new" => ["subject" => $_POST["expiredAssignmentSubjects"][$key], "content" => $_POST["expiredAssignmentContents"][$key], "due" => $_POST["expiredAssignmentDueDates"][$key]]];
          }
        }
      }
    }

    if ($checkOriginalAssignments != $originalAssignments)
    {
      usort($originalAssignments, function ($a, $b)
      {
        if ($a["due"] === "")
        {
          $duedateA = INF;
        }
        else
        {
          $duedateA = strtotime($a["due"]);
        }
        if ($b["due"] === "")
        {
          $duedateB = INF;
        }
        else
        {
          $duedateB = strtotime($b["due"]);
        }
        if ($duedateA > $duedateB)
        {
          return 1;
        }
        else if ($duedateA < $duedateB)
        {
          return -1;
        }
        else
        {
          return 0;
        }
      });
      $history = (array)json_decode(fileread("history.txt"), true);
      if (count($history) !== 0)
      {
        $history[count($history) - 1]["content"] = $checkOriginalAssignments;
      }
      $history[] = ["time" => date("Y-m-d H:i:s"), "user" => $current->username, "content" => ""];
      filewrite("history.txt", json_encode($history));
      filewrite("../Assignments/assignments.txt", json_encode($originalAssignments));
    }

    if (empty($fails))
    {
      header("Location: https://" . SITE_DOMAIN . "/Assignments");
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

function getAllAssignedDisplay(int|string $version = "current"): string
{
  $display = '<div class="card"><div class="card-body">';
  $originalAssignments = getAssignmentsByRange(strtotime("today"), INF, true, $version);

  $counter = 0;
  foreach ($originalAssignments as $item)
  {
    $counter++;
    $item["content"] = htmlspecialchars($item["content"]);
    $display .= '<div class="card shadow border-0 m-1"><div class="card-body" style="display:flex;"><select class="form-select me-2 assignedAssignmentSubjects" name="assignedAssignmentSubjects[]" style="max-width:10em;min-width:0em;width:fit-content;">' . getSubjectsSelectionInputChoices($item["subject"]) . '</select><input type="text" class="form-control me-2 assignedAssignmentContents" name="assignedAssignmentContents[]" style="flex-grow:1;" placeholder="Content"  value="' . $item["content"] . '"><input type="date" class="form-control assignedAssignmentDueDates" name="assignedAssignmentDueDates[]" style="width:15em;" value="' . $item["due"] . '"><input type="hidden" name="originalAssignedAssignmentSubjects[]" value="' . $item["subject"] . '" class="originalAssignedAssignmentSubjects"><input type="hidden" name="originalAssignedAssignmentContents[]" value="' . $item["content"] . '" class="originalAssignedAssignmentContents"><input type="hidden" name="originalAssignedAssignmentDueDates[]" value="' . $item["due"] . '" class="originalAssignedAssignmentDueDates"><button type="button" class="btn p-2" onclick="$(this).parent().parent().css(\'display\',\'none\');$(this).parent().children().eq(1).val(\'\');"><i class="bi bi-x-lg"></i></button></div></div>';
  }
  if ($counter == 0)
  {
    $display .= '<h6 style="text-align:center;" class="m-0">No assigned assignments found</h6></div></div>';
  }
  else
  {
    $display .= '</div></div>';
  }
  return $display;
}

function getAllExpiredDisplay(int|string $version = "current"): string
{
  $display = '<div class="card"><div class="card-body">';
  $originalAssignments = getAssignmentsByRange(-INF, strtotime("today"), true, $version);

  $counter = 0;
  foreach ($originalAssignments as $item)
  {
    $counter++;
    $item["content"] = htmlspecialchars($item["content"]);
    $display .= '<div class="card shadow border-0 m-1"><div class="card-body" style="display:flex;"><select class="form-select me-2 expiredAssignmentSubjects" name="expiredAssignmentSubjects[]" style="max-width:10em;min-width:0em;width:fit-content;">' . getSubjectsSelectionInputChoices($item["subject"]) . '</select><input type="text" class="form-control me-2 expiredAssignmentContents" name="expiredAssignmentContents[]" style="flex-grow:1;" placeholder="Content"  value="' . $item["content"] . '"><input type="date" class="form-control expiredAssignmentDueDates" name="expiredAssignmentDueDates[]" style="width:15em;" value="' . $item["due"] . '"><input type="hidden" name="originalExpiredAssignmentSubjects[]" value="' . $item["subject"] . '" class="originalExpiredAssignmentSubjects"><input type="hidden" name="originalExpiredAssignmentContents[]" value="' . $item["content"] . '" class="originalExpiredAssignmentContents"><input type="hidden" name="originalExpiredAssignmentDueDates[]" value="' . $item["due"] . '" class="originalExpiredAssignmentDueDates"><button type="button" class="btn p-2" onclick="$(this).parent().parent().css(\'display\',\'none\');$(this).parent().children().eq(1).val(\'\');"><i class="bi bi-x-lg"></i></button></div></div>';
  }
  if ($counter == 0)
  {
    $display .= '<h6 style="text-align:center;" class="m-0">No expired assignments found</h6></div></div>';
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
      echo '<h1 class="d-flex"><a class="btn align-self-center me-2" href="?"><i class="bi bi-arrow-left fs-5"></i></a>Assignments History</h1><hr>';

      $itemsdisplay = '<div class="accordion">';
      $page = (int)$_GET["history"];
      for ($index = (count($historystored) - 1 - ($page - 1) * 20); $index > (count($historystored) - 1 -  $page * 20) && $index >= 0; --$index)
      {
        $display = '';
        if ($index === count($historystored) - 1)
        {
          $addeditems = array_diff_multidimensional(getAllAssignments(true), ($index === 0 ? [] : $historystored[$index - 1]["content"]));
          foreach ($addeditems as $item)
          {
            $display .= '<div class="card shadow border border-1 border-success m-1"><div class="card-body" style="display:flex;"><div class="text-success m-auto me-2"><i class="bi bi-plus-lg"></i></div><div class="form-control me-2 text-success" style="width:10em;overflow:auto;">' . $item["subject"] . '</div><div class="form-control me-2 text-success" style="flex-grow:1;overflow:auto;">' . $IMP->line($item["content"]) . '</div><div class="form-control me-2 text-success" style="width:15em;overflow:auto;">' . (empty($item["due"]) ? "No due date" : date("d/m/Y", strtotime($item["due"]))) . '</div></div></div>';
          }
          $deleteditems = array_diff_multidimensional(($index === 0 ? [] : $historystored[$index - 1]["content"]), getAllAssignments(true));
          foreach ($deleteditems as $item)
          {
            $display .= '<div class="card shadow border border-1 border-danger m-1"><div class="card-body" style="display:flex;"><div class="text-danger m-auto me-2"><i class="bi bi-dash-lg"></i></div><div class="form-control me-2 text-danger" style="width:10em;overflow:auto;">' . $item["subject"] . '</div><div class="form-control me-2 text-danger" style="flex-grow:1;overflow:auto;">' . $IMP->line($item["content"]) . '</div><div class="form-control me-2 text-danger" style="width:15em;overflow:auto;">' . (empty($item["due"]) ? "No due date" : date("d/m/Y", strtotime($item["due"]))) . '</div></div></div>';
          }
        }
        else
        {
          $addeditems = array_diff_multidimensional($historystored[$index]["content"], ($index === 0 ? [] : $historystored[$index - 1]["content"]));
          foreach ($addeditems as $item)
          {
            $display .= '<div class="card shadow border border-1 border-success m-1"><div class="card-body" style="display:flex;"><div class="text-success m-auto me-2"><i class="bi bi-plus-lg"></i></div><div class="form-control me-2 text-success" style="width:10em;overflow:auto;">' . $item["subject"] . '</div><div class="form-control me-2 text-success" style="flex-grow:1;overflow:auto;">' . $IMP->line($item["content"]) . '</div><div class="form-control me-2 text-success" style="width:15em;overflow:auto;">' . (empty($item["due"]) ? "No due date" : date("d/m/Y", strtotime($item["due"]))) . '</div></div></div>';
          }
          $deleteditems = array_diff_multidimensional(($index === 0 ? [] : $historystored[$index - 1]["content"]), $historystored[$index]["content"]);
          foreach ($deleteditems as $item)
          {
            $display .= '<div class="card shadow border border-1 border-danger m-1"><div class="card-body" style="display:flex;"><div class="text-danger m-auto me-2"><i class="bi bi-dash-lg"></i></div><div class="form-control me-2 text-danger" style="width:10em;overflow:auto;">' . $item["subject"] . '</div><div class="form-control me-2 text-danger" style="flex-grow:1;overflow:auto;">' . $IMP->line($item["content"]) . '</div><div class="form-control me-2 text-danger" style="width:15em;overflow:auto;">' . (empty($item["due"]) ? "No due date" : date("d/m/Y", strtotime($item["due"]))) . '</div></div></div>';
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
      echo '<h1 class="d-flex"><a class="btn align-self-center me-2" href="/Assignments"><i class="bi bi-arrow-left fs-5"></i></a><span class="flex-fill">Edit Assignments</span>';
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
      <a class="nav-link" id="assignedlink" href="#assigned"><i class="bi bi-hourglass-split"></i> Edit assigned</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" id="expiredlink" href="#expired"><i class="bi bi-hourglass-bottom"></i> Edit expired</a>
    </li>
  </ul>';
        echo '<form method="post" id="forms" onsubmit="preventMisclick($(\'#submitbtn\'))"><div id="assignedpage">' . getAllAssignedDisplay(isset($histid) ? $histid : "current") . '</div>';
        echo '<div id="expiredpage">' . getAllExpiredDisplay(isset($histid) ? $histid : "current") . '</div>';
        echo '<div id="newpage"><div class="card"><div class="card-body" id="addNewContainer"><div class="card shadow border-0 m-1"><div class="card-body" style="display:flex;"><select class="form-select me-2" name="newAssignmentSubjects[]" style="max-width:10em;min-width:0em;width:fit-content;">' . getSubjectsSelectionInputChoices() . '</select><input type="text" class="form-control me-2" name="newAssignmentContents[]" style="flex-grow:1;" placeholder="Content"><input type="date" class="form-control" name="newAssignmentDueDates[]" style="width:15em;"><button type="button" class="btn p-2" onclick="$(this).parent().parent().remove()"><i class="bi bi-x-lg"></i></button></div></div></div><div class="card-body"><button onclick="newAssignmentItem()" class="btn btn-outline-secondary" style="width:100%;border:dashed;" type="button"><i class="bi bi-plus-lg"></i> New item</button></div></div></div></form><button type="button" class="btn btn-primary btn-lg w-100 mt-2" onclick="preSubmitOptimization();" id="submitbtn">Submit</button>';
      }
      else
      {
        $displayFailedEdits = "";
        $displayOriginals = "";
        foreach ($fails as $item)
        {
          $displayFailedEdits .= '<div class="card shadow border-0 m-1"><div class="card-body" style="display:flex;"><div class="form-control me-2" style="width:10em;overflow:auto;">' . $item["new"]["subject"] . '</div><div class="form-control me-2" style="flex-grow:1;overflow:auto;">' . $item["new"]["content"] . '</div><div class="form-control me-2" style="width:15em;overflow:auto;">' . (empty($item["new"]["due"]) ? "No due date" : date("d/m/Y", strtotime($item["new"]["due"]))) . '</div><input name="newAssignmentSubjects[]" type="hidden" value="' . $item["new"]["subject"] . '"><input type="hidden" name="newAssignmentContents[]" value="' . $item["new"]["content"] . '"><input type="hidden" name="newAssignmentDueDates[]" value="' . $item["new"]["due"] . '"></div></div>';
          $displayOriginals .= '<div class="card shadow border-0 m-1"><div class="card-body" style="display:flex;"><div class="form-control me-2" style="width:10em;overflow:auto;">' . $item["original"]["subject"] . '</div><div class="form-control me-2" style="flex-grow:1;overflow:auto;">' . $item["original"]["content"] . '</div><div class="form-control me-2" style="width:15em;overflow:auto;">' . (empty($item["original"]["due"]) ? "No due date" : date("d/m/Y", strtotime($item["original"]["due"]))) . '</div><input type="hidden" name="newAssignmentSubjects[]" value="' . $item["original"]["subject"] . '"><input type="hidden" name="newAssignmentContents[]" value="' . $item["original"]["content"] . '"><input type="hidden" name="newAssignmentDueDates[]" value="' . $item["original"]["due"] . '"></div></div>';
        }

        echo '<h3>Edit conflict</h3><p>Some of your edits cannot be made because their original content cannot be found in the current assignment list. This is usually because somebody else has edited the assignment list while you were editing.</p><p>Please choose which action to take.</p><p>Failed edits:</p><div class="card mb-3"><form method="post" id="addNewForm_new" onsubmit="preventMisclick($(\'#addNewForm_newsubmitbtn\'))"><div class="card-body">' . $displayFailedEdits . '</div></form></div><p>Their corresponding original content:</p><div class="card mb-3"><form method="post" id="addNewForm_original" onsubmit="preventMisclick($(\'#addNewForm_originalsubmitbtn\'))"><div class="card-body">' . $displayOriginals . '</div></form></div><div class="row"><button class="btn btn-primary col m-3 mb-0" style="min-width:20em;" onclick="$(\'#addNewForm_new\').submit();" id="addNewForm_newsubmitbtn"><i class="bi bi-plus-lg"></i> Add the failed edits as new items</button><button class="btn btn-secondary col m-3 mb-0" style="min-width:20em;" onclick="$(\'#addNewForm_original\').submit();" id="addNewForm_originalsubmitbtn"><i class="bi bi-clock-history"></i> Add back the original items</button><a class="btn btn-success col m-3 mb-0" style="min-width:20em;" href="../Assignments/"><i class="bi bi-arrow-right"></i> Continue without adding anything</a></div>';
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
    function newAssignmentItem() {
      $("#addNewContainer").append('<div class="card shadow border-0 m-1"><div class="card-body" style="display:flex;"><select class="form-select me-2" name="newAssignmentSubjects[]" style="max-width:10em;min-width:0em;width:fit-content;"><?= getSubjectsSelectionInputChoices() ?> </select><input type="text" class="form-control me-2" name="newAssignmentContents[]" style="flex-grow:1;" placeholder="Content"><input type="date" class="form-control" name="newAssignmentDueDates[]" style="width:15em;"><button type="button" class="btn p-2" onclick="$(this).parent().parent().remove()"><i class="bi bi-x-lg"></i></button></div></div>');
    }

    changePage();
    window.addEventListener("hashchange", changePage);


    function changePage() {
      switch (window.location.hash) {
        case '#assigned':
          $("#assignedpage").css('display', 'block');
          $("#expiredpage").css('display', 'none');
          $("#newpage").css('display', 'none');

          $("#assignedlink").addClass("active");
          $("#expiredlink").removeClass("active");
          $("#newlink").removeClass("active");
          break;

        case '#expired':
          $("#assignedpage").css('display', 'none');
          $("#expiredpage").css('display', 'block');
          $("#newpage").css('display', 'none');

          $("#assignedlink").removeClass("active");
          $("#expiredlink").addClass("active");
          $("#newlink").removeClass("active");
          break;

        default:
          $("#assignedpage").css('display', 'none');
          $("#expiredpage").css('display', 'none');
          $("#newpage").css('display', 'block');

          $("#assignedlink").removeClass("active");
          $("#expiredlink").removeClass("active");
          $("#newlink").addClass("active");
          break;
      }
    }

    function preSubmitOptimization() {
      var removedAssigned = 0;
      $('.assignedAssignmentContents').each(function(i, obj) {
        if ($('.assignedAssignmentSubjects').eq(i - removedAssigned).find(":selected").val() == $('.originalAssignedAssignmentSubjects').eq(i - removedAssigned).val() && $(obj).val() == $('.originalAssignedAssignmentContents').eq(i - removedAssigned).val() && $('.assignedAssignmentDueDates').eq(i - removedAssigned).val() == $('.originalAssignedAssignmentDueDates').eq(i - removedAssigned).val()) {
          $(obj).parent().parent().remove();
          removedAssigned++;
        }
      });
      var removedExpired = 0;
      $('.expiredAssignmentContents').each(function(i, obj) {
        if ($('.expiredAssignmentSubjects').eq(i - removedExpired).find(":selected").val() == $('.originalExpiredAssignmentSubjects').eq(i - removedExpired).val() && $(obj).val() == $('.originalExpiredAssignmentContents').eq(i - removedExpired).val() && $('.expiredAssignmentDueDates').eq(i - removedExpired).val() == $('.originalExpiredAssignmentDueDates').eq(i - removedExpired).val()) {
          $(obj).parent().parent().remove();
          removedExpired++;
        }
      });
      $("#forms").submit();
    }

    $("input, select").keyup(function(event) {
      if (event.keyCode === 13) {
        event.preventDefault();
        $("#submitbtn").click();
        return false;
      }
    });
  </script>
</body>

</html>