<?php
require("../CoreLibrary/CoreFunctions.php");

$current = new Session("Assignments", "Assignments");
require("../CoreLibrary/Assignments.php");
require("../CoreLibrary/DatetimeHandlers.php");
?>
<!DOCTYPE html>
<html>
<?= $current->getHtmlHead() ?>

<body class="container pt-4">
  <header><?= $current->getNavBar() ?></header>
  <?php
  if ($current->accessstatus)
  {
    $assignmentDisplay_Missing = "";
    $assignmentDisplay_Assigned = "";
    $assignmentDisplay_Done = "";
    $doneAssignments = [];
    $filterSettings = ["missing", "assigned"];

    if (is_file("../Login/Accounts/" . $current->username . "/Assignments/DoneAssignments.txt"))
    {
      $doneAssignments = json_decode(fileread("../Login/Accounts/" . $current->username . "/Assignments/DoneAssignments.txt"), true);
    }
    if (is_file("../Login/Accounts/" . $current->username . "/Assignments/FilterSettings.txt"))
    {
      $filterSettings = json_decode(fileread("../Login/Accounts/" . $current->username . "/Assignments/FilterSettings.txt"), true);
    }

    $index = 0;
    foreach (getAllAssignments() as $assignment)
    {
      $index++;
      $done = false;
      foreach ((array)$doneAssignments as $doneAssignment)
      {
        if ($doneAssignment["subject"] === $assignment["subject"] && $doneAssignment["content"] === $assignment["content"] && $doneAssignment["due"] === $assignment["due"])
        {
          $done = true;
          break;
        }
      }
      if (($assignment["due"] !== "" && strtotime($assignment["due"]) < strtotime("today")) && !$done)
      {
        $assignmentDisplay_Missing .= '<div class="card shadow border-0 mb-3" id="' . $index . '"><div class="card-body d-flex"><div class="align-self-center" style="width:2em;"><button type="button" class="btn btn-danger p-0 d-flex justify-content-center" style="width:2rem;height:2rem;" onclick="queueAssignmentsOps(this, \'done\')"><i class="bi bi-check-lg align-self-center"></i></button></div><div class="vr ms-3 me-3"></div><div class="align-self-center fw-bold me-3">' . $assignment["subject"] . '</div><div class="flex-fill text-break align-self-center">' . $assignment["content"] . '</div><div class="ms-3 align-self-center text-end" style="color:var(--bs-danger);min-width:5rem;" data-originaldate="' . $assignment["due"] . '">' . ucfirst(displayDatetime($assignment["due"])) . '</div></div></div>';
      }
      else if (($assignment["due"] === "" || strtotime($assignment["due"]) >= strtotime("today")) && !$done)
      {
        $assignmentDisplay_Assigned .= '<div class="card shadow border-0 mb-3" id="' . $index . '"><div class="card-body d-flex"><div class="align-self-center" style="width:2em;"><button type="button" class="btn btn-primary p-0 d-flex justify-content-center" style="width:2rem;height:2rem;" onclick="queueAssignmentsOps(this, \'done\')"><i class="bi bi-check-lg align-self-center"></i></button></div><div class="vr ms-3 me-3"></div><div class="align-self-center fw-bold me-3">' . $assignment["subject"] . '</div><div class="flex-fill text-break align-self-center">' . $assignment["content"] . '</div><div class="ms-3 align-self-center text-end" style="color:var(--bs-danger);min-width:5rem;" data-originaldate="' . $assignment["due"] . '">' . ($assignment["due"] === "" ? "No due date" : ucfirst(displayDatetime($assignment["due"]))) . '</div></div></div>';
      }
      else
      {
        $assignmentDisplay_Done .= '<div class="card shadow border-0 mb-3" id="' . $index . '"><div class="card-body d-flex"><div class="align-self-center" style="width:2em;"><button type="button" class="btn btn-secondary p-0 d-flex justify-content-center" style="width:2rem;height:2rem;" onclick="queueAssignmentsOps(this, \'undone\')"><i class="bi bi-check-lg align-self-center"></i></button></div><div class="vr ms-3 me-3"></div><div class="align-self-center fw-bold text-muted me-3">' . $assignment["subject"] . '</div><div class="flex-fill text-break align-self-center text-muted">' . $assignment["content"] . '</div><div class="align-self-center text-end ms-3 text-muted" style="min-width:5em;" data-originaldate="' . $assignment["due"] . '">' . ($assignment["due"] === "" ? "No due date" : ucfirst(displayDatetime($assignment["due"]))) . '</div></div></div>';
      }
    }

    echo '<h1 class="d-flex"><span class="flex-fill">Assignments</span><div class="dropdown align-self-center d-flex"><button class="btn btn-primary dropdown-toggle align-self-center" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside"><i class="bi bi-funnel-fill"></i></button><div class="dropdown-menu dropdown-menu-end p-3 fw-normal lh-base"><div class="form-check"><input class="form-check-input" type="checkbox" id="assignmentfilter_missing" onchange="changeFilterSettings()"' . (in_array("missing", $filterSettings) ? " checked" : "") . '><label class="form-check-label" for="assignmentfilter_missing">Missing</label></div><div class="form-check"><input class="form-check-input" type="checkbox" id="assignmentfilter_assigned" onchange="changeFilterSettings()"' . (in_array("assigned", $filterSettings) ? " checked" : "") . '><label class="form-check-label" for="assignmentfilter_assigned">Assigned</label></div><div class="form-check"><input class="form-check-input" type="checkbox" id="assignmentfilter_done" onchange="changeFilterSettings()"' . (in_array("done", $filterSettings) ? " checked" : "") . '><label class="form-check-label" for="assignmentfilter_done">Done</label></div></div></div><a href="../EditAssignments" class="btn align-self-center ms-2"><i class="bi bi-pencil"></i></a></h1><hr>';
    echo '<div class="' . (($assignmentDisplay_Missing . $assignmentDisplay_Assigned) !== "" ? "d-none" : "d-flex justify-content-center") . '" style="height:10em;" id="clearAllNotice"><h3 class="align-self-center">You\'ve cleared all your assignments!</h3></div>';
    echo '<div id="MissingAssignmentsContainer" style="' . (!in_array("missing", $filterSettings) ? "display:none;" : "") . '">' . $assignmentDisplay_Missing . '</div><div id="AssignedAssignmentsContainer" style="' . (!in_array("assigned", $filterSettings) ? "display:none;" : "") . '">' . $assignmentDisplay_Assigned . '</div><div id="DoneAssignmentsContainer" style="' . (!in_array("done", $filterSettings) ? "display:none;" : "") . '">' . $assignmentDisplay_Done . '</div>';
  }
  else
  {
    echo $current->accessstatusmsg;
  }
  ?>
  <?= $current->getFooter() ?>
  <script>
    var assignmentsOpsRequestSendTimeout,
      requestList = [],
      clickedDoneBtns = [];

    function queueAssignmentsOps(doneBtn, action) {
      clearTimeout(assignmentsOpsRequestSendTimeout);
      requestList.push({
        action: action,
        assignmentSubject: $(doneBtn).parent().next().next().html(),
        assignmentName: $(doneBtn).parent().next().next().next().html(),
        assignmentDue: $(doneBtn).parent().next().next().next().next().data("originaldate")
      });
      clickedDoneBtns.push(doneBtn);
      $(doneBtn).html('<div class="spinner-border spinner-border-sm align-self-center" role="status"></div>');
      $(doneBtn).css("pointer-events", "none");
      assignmentsOpsRequestSendTimeout = setTimeout(function() {
        sendAssignmentsOpsRequest(requestList, clickedDoneBtns)
      }, 2000);
    }

    function sendAssignmentsOpsRequest(requestListpara, clickedDoneBtnspara) {
      $.ajax({
        url: "assignmentops.php",
        type: "post",
        data: {
          data: JSON.stringify(requestListpara)
        },
        cache: false,
        success: function(json) {
          json = $.parseJSON(json);
          for (i = 0; i < json.length; i++) {
            if (json[i].status == "done") {
              $(clickedDoneBtnspara[i]).attr("class", "btn btn-secondary p-0 d-flex justify-content-center");
              $(clickedDoneBtnspara[i]).attr("onclick", "queueAssignmentsOps(this,'undone')");
              $(clickedDoneBtnspara[i]).parent().next().next().addClass('text-muted');
              $(clickedDoneBtnspara[i]).parent().next().next().next().addClass('text-muted');
              $(clickedDoneBtnspara[i]).parent().next().next().next().next().addClass('text-muted');
              $(clickedDoneBtnspara[i]).html('<i class="bi bi-check-lg align-self-center"></i>');

              let assignmentItem = $(clickedDoneBtnspara[i]).parent().parent().parent().clone(true);
              assignmentItem.css("opacity", 0);

              $(clickedDoneBtnspara[i]).parent().parent().parent().delay(1500).animate({
                opacity: 0,
                height: 0
              }, "slow", "swing", function() {
                if ($("#DoneAssignmentsContainer").children().length > 0) {
                  $("#DoneAssignmentsContainer").children().each(function() {
                    if (Number($(this).attr('id')) > Number(assignmentItem.attr('id'))) {
                      assignmentItem.insertBefore($(this));
                      return false;
                    } else if ($(this).is($("#DoneAssignmentsContainer").children().last())) {
                      $("#DoneAssignmentsContainer").append(assignmentItem);
                    }
                  });
                } else {
                  $("#DoneAssignmentsContainer").append(assignmentItem);
                }
                $(this).remove();
                assignmentItem.animate({
                  opacity: 1,
                }, "slow", "swing");
                assignmentItem.children(":first").children(":first").children(":first").css("pointer-events", "auto");
                checkIfClear();
                requestList = [];
                clickedDoneBtns = [];
              });
            } else if (json[i].status == "undone") {
              let missing = json[i].missing;
              if (missing) {
                $(clickedDoneBtnspara[i]).attr("class", "btn btn-danger p-0 d-flex justify-content-center");
                $(clickedDoneBtnspara[i]).parent().next().next().next().next().css("color", "var(--bs-danger)");
              } else {
                $(clickedDoneBtnspara[i]).attr("class", "btn btn-primary p-0 d-flex justify-content-center");
              }
              $(clickedDoneBtnspara[i]).attr("onclick", "queueAssignmentsOps(this,'done')");
              $(clickedDoneBtnspara[i]).parent().next().next().removeClass('text-muted');
              $(clickedDoneBtnspara[i]).parent().next().next().next().removeClass('text-muted');
              $(clickedDoneBtnspara[i]).parent().next().next().next().next().removeClass('text-muted');
              $(clickedDoneBtnspara[i]).html('<i class="bi bi-check-lg align-self-center"></i>');

              let assignmentItem = $(clickedDoneBtnspara[i]).parent().parent().parent().clone(true);
              assignmentItem.css("opacity", 0);

              $(clickedDoneBtnspara[i]).parent().parent().parent().delay(1500).animate({
                opacity: 0,
                height: 0
              }, "slow", "swing", function() {
                let containerId = "#AssignedAssignmentsContainer";

                if (missing) {
                  containerId = "#MissingAssignmentsContainer";
                }

                if ($(containerId).children().length > 0) {
                  $(containerId).children().each(function() {
                    if (Number($(this).attr('id')) > Number(assignmentItem.attr('id'))) {
                      assignmentItem.insertBefore($(this));
                      return false;
                    } else if ($(this).is($(containerId).children().last())) {
                      $(containerId).append(assignmentItem);
                    }
                  });
                } else {
                  $(containerId).append(assignmentItem);
                }
                $(this).remove();
                assignmentItem.animate({
                  opacity: 1,
                }, "slow", "swing");
                assignmentItem.children(":first").children(":first").children(":first").css("pointer-events", "auto");
                checkIfClear();
                requestList = [];
                clickedDoneBtns = [];
              });
            }
          }
        }
      })
    }

    function changeFilterSettings() {
      if (!$("#assignmentfilter_missing").is(":checked")) {
        $("#MissingAssignmentsContainer").css("display", "none");
      } else {
        $("#MissingAssignmentsContainer").css("display", "block");
      }
      if (!$("#assignmentfilter_assigned").is(":checked")) {
        $("#AssignedAssignmentsContainer").css("display", "none");
      } else {
        $("#AssignedAssignmentsContainer").css("display", "block");
      }
      if (!$("#assignmentfilter_done").is(":checked")) {
        $("#DoneAssignmentsContainer").css("display", "none");
      } else {
        $("#DoneAssignmentsContainer").css("display", "block");
      }

      $.ajax({
        url: "setfilter.php",
        type: "post",
        data: {
          missing: $("#assignmentfilter_missing").is(":checked"),
          assigned: $("#assignmentfilter_assigned").is(":checked"),
          done: $("#assignmentfilter_done").is(":checked")
        }
      });
    }

    function checkIfClear() {
      if ($("#MissingAssignmentsContainer").children().length == 0 && $("#AssignedAssignmentsContainer").children().length == 0) {
        $("#clearAllNotice").attr("class", "d-flex justify-content-center");
      } else {
        $("#clearAllNotice").attr("class", "d-none");
      }
    }
  </script>
</body>

</html>