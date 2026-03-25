<?php
require("../CoreLibrary/CoreFunctions.php");

$current = new Session("Support", "Support");

if ($current->accessstatus)
{
   if (isset($_POST["feedback"]) && isset($_POST["feedbacktype"]) && isset($_POST["feedbacktitle"]) && isset($_POST["feedback"]))
   {
      $image = false;
      if (is_uploaded_file($_FILES['feedbackadditpic']['tmp_name']) && $_FILES["feedbackadditpic"]["size"] <= 3145728)
      {
         $image = @imagecreatefromstring(fileread($_FILES["feedbackadditpic"]["tmp_name"]));
         if ($image !== false)
         {
            if (imagesx($image) > 1000)
            {
               $image = imagescale($image, 1000);
            }
            ob_start();
            imagepng($image);
            $imgbase64 = base64_encode(ob_get_clean());
            imagedestroy($image);
         }
      }
      $feedbacktxt = '<div class="card mb-2" style="width:100%;"><div class="card-body"><h5 class="card-title">' . htmlspecialchars($_POST["feedbacktitle"]) . '</h5><h6 class="card-subtitle mb-2 text-muted"><b>' . htmlspecialchars($_POST["feedbacktype"]) . '</b> &middot; By ' . $current->username . ' &middot; ' . date("Y-m-d H:i:s") . '</h6><p class="card-text">' . htmlspecialchars($_POST["feedback"]) . '</p>' . ($image !== false ? '<img src="data:image/png;base64,' . $imgbase64 . '" style="width:100%;">' : null) . '</div></div>';
      filewrite("../Dev/feedbacks.txt", $feedbacktxt, "beginning");
      header("Location: https://" . SITE_DOMAIN . "/Support/");
   }
   else if (isset($_POST["abusetitle"]) && isset($_POST["when"]) && isset($_POST["who"]) && isset($_POST["where"]) && isset($_POST["abusedescription"]) && is_uploaded_file($_FILES['abuseevidence']['tmp_name']) && $_FILES["abuseevidence"]["size"] <= 3145728)
   {
      $image = @imagecreatefromstring(fileread($_FILES["abuseevidence"]["tmp_name"]));
      if ($image !== false)
      {
         if (imagesx($image) > 1000)
         {
            $image = imagescale($image, 1000);
         }
         ob_start();
         imagepng($image);
         $imgbase64 = base64_encode(ob_get_clean());
         imagedestroy($image);
      }
      $abusetxt = '<div class="card mb-2" style="width:100%;"><div class="card-body"><h5 class="card-title">' . htmlspecialchars($_POST["abusetitle"]) . '</h5><h6 class="card-subtitle mb-2 text-muted">Involved page(s): <b>' . htmlspecialchars($_POST["where"]) . '</b><br>Involver(s): <b>' . htmlspecialchars($_POST["who"]) . '</b><br>Time: <b>' . htmlspecialchars($_POST["when"]) . '</b></h6><h6 class="card-subtitle mb-2 text-muted">Reported by ' . $current->username . ' at ' . date("Y-m-d H:i:s") . '</h6><hr><p class="card-text">' . htmlspecialchars($_POST["abusedescription"]) . '</p><img src="data:image/png;base64,' . htmlspecialchars($imgbase64) . '" style="width:100%;"></div></div>';
      filewrite("../Mod/abuses.txt", $abusetxt, "beginning");
      header("Location: https://" . SITE_DOMAIN . "/Support/");
   }
   else if (isset($_POST["votecandidate"]))
   {
      if (strtotime(date("Y-m") . "-1") <= time() && time() < strtotime(date("Y-m") . "-6"))
      {
         if (is_file("nominatedmods_" . date("Y-m") . ".txt"))
         {
            $json = (array)json_decode(fileread("nominatedmods_" . date("Y-m") . ".txt"), true);
            if (array_key_exists($_POST["votecandidate"], $json) && !in_array($current->username, $json[$_POST["votecandidate"]]["votes"]))
            {
               $json[$_POST["votecandidate"]]["votes"][] = $current->username;
               filewrite("nominatedmods_" . date("Y-m") . ".txt", json_encode($json));
            }
         }
      }
   }
   else if (isset($_POST["nominationsubmit"]) && isset($_POST["description"]))
   {
      if (strtotime(date("Y-m") . "-1") <= time() && time() < strtotime(date("Y-m") . "-6"))
      {
         if (is_file("nominatedmods_" . date("Y-m") . ".txt"))
         {
            $json = (array)json_decode(fileread("nominatedmods_" . date("Y-m") . ".txt"), true);
         }
         else
         {
            $json = [];
         }
         if (!array_key_exists($current->username, $json) && count($json) <= 5)
         {
            require_once("../CoreLibrary/DatetimeHandlers.php");
            $json[$current->username] = ["description" => htmlspecialchars($_POST["description"]), "time" => date("Y-m-d H:i:s"), "votes" => []];
            filewrite("nominatedmods_" . date("Y-m") . ".txt", json_encode($json));
         }
      }
   }
   else if (isset($_POST["quitelection"]))
   {
      if (strtotime(date("Y-m") . "-1") <= time() && time() < strtotime(date("Y-m") . "-6") && is_file("nominatedmods_" . date("Y-m") . ".txt"))
      {
         $json = (array)json_decode(fileread("nominatedmods_" . date("Y-m") . ".txt"), true);

         if (array_key_exists($current->username, $json))
         {
            unset($json[$current->username]);
            filewrite("nominatedmods_" . date("Y-m") . ".txt", json_encode($json));
         }
      }
   }
   else if (isset($_POST["markelected"]) && isset($_POST["markelectedcandidate"]) && $current->hasRole(["developer"]))
   {
      if (!(strtotime(date("Y-m") . "-1") <= time() && time() < strtotime(date("Y-m") . "-6") && is_file("nominatedmods_" . date("Y-m") . ".txt")))
      {
         $json = (array)json_decode(fileread("nominatedmods_" . date("Y-m") . ".txt"), true);

         if (array_key_exists($_POST["markelectedcandidate"], $json) && !array_key_exists("elected", $json[$_POST["markelectedcandidate"]]))
         {
            $json[$_POST["markelectedcandidate"]]["elected"] = true;
            filewrite("nominatedmods_" . date("Y-m") . ".txt", json_encode($json));

            $markelectednotification = new Notification("Mod Election Result", "You have been elected as one of the moderators for this month.", "Support", "mod_election", "", NotificationType::success);
            $markelectednotification->pushOnceIfNeeded([$_POST["markelectedcandidate"]]);
         }
      }
   }
   else if (isset($_POST["unmarkelected"]) && isset($_POST["unmarkelectedcandidate"]) && $current->hasRole(["developer"]))
   {
      if (!(strtotime(date("Y-m") . "-1") <= time() && time() < strtotime(date("Y-m") . "-6") && is_file("nominatedmods_" . date("Y-m") . ".txt")))
      {
         $json = (array)json_decode(fileread("nominatedmods_" . date("Y-m") . ".txt"), true);

         if (array_key_exists($_POST["unmarkelectedcandidate"], $json) && array_key_exists("elected", $json[$_POST["unmarkelectedcandidate"]]))
         {
            unset($json[$_POST["unmarkelectedcandidate"]]["elected"]);
            filewrite("nominatedmods_" . date("Y-m") . ".txt", json_encode($json));
         }
      }
   }
}
?>
<!DOCTYPE html>
<html>
<style>
   #background {
      position: absolute;
      top: 0.25em;
      right: 0.4em;
      color: grey;
      float: right;
   }

   .accordion-body {
      font-weight: 550;
   }

   .option {
      color: black;
      text-decoration: none;
   }

   .option>div {
      transition-duration: .6s;
   }

   .option>div:hover {
      transition-duration: .6s;
      box-shadow: 0rem 1rem 1rem rgba(0, 0, 0, .3) !important;
   }
</style>
<?= $current->getHtmlHead() ?>

<body class="container pt-4">
   <header><?= $current->getNavBar() ?></header>
   <?php
   if ($current->accessstatus)
   {
      $notificationsystemoperator = new NotificationSystemOperator();
      $boardofmodsPageNotificationsCount = $notificationsystemoperator->getNotificationCountByIDs("mod_election");

      if (isset($_GET["faq"]))
      { ?>
         <a class="btn me-2" href="/Support" style="float:left;margin:auto;"><i class="bi bi-arrow-left" style="font-size:1.5em;"></i></a>
         <h1>Frequently Asked Questions</h1>
         <h6>Find the answers of the common questions here!</h6>
         <br>
         <div class="accordion" id="acronyms">
            <h5>Acronyms</h5>
            <div class="accordion-item">
               <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#im">
                     What is IM?
                  </button>
               </h2>
               <div id="im" class="accordion-collapse collapse" data-bs-parent="#acronyms">
                  <div class="accordion-body">
                     The <b>I</b>ntranet <b>M</b>arkup is a human-readable markup language developed by Intranet Development Team for users to use in Intranet. For more info, please refer to the <a target="_blank" href="https://<?= SITE_DOMAIN ?>/Support/?formatting">text formatting guidelines</a>.
                  </div>
               </div>
            </div>
            <div class="accordion-item">
               <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#idt">
                     What is IDT?
                  </button>
               </h2>
               <div id="idt" class="accordion-collapse collapse" data-bs-parent="#acronyms">
                  <div class="accordion-body">
                     The <b>I</b>ntranet <b>D</b>evelopment <b>T</b>eam is a team of developers that creates, updates, monitors, and maintains the open-source project Intranet initially. More and more non-team-members are contributing to the development of Intranet. The IDT is no longer the only contributor. For more info, please refer to the <a target="_blank" href="https://<?= SITE_DOMAIN ?>/Support/?about">About page</a>.
                  </div>
               </div>
            </div>
         </div>
         <br>
         <div class="accordion" id="uaas">
            <h5>User accounts & accounts settings</h5>
            <div class="accordion-item">
               <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#chaun">
                     How can I change my username?
                  </button>
               </h2>
               <div id="chaun" class="accordion-collapse collapse" data-bs-parent="#uaas">
                  <div class="accordion-body">
                     Due to security reasons, you cannot change your username directly inside Intranet. Username is the unique identifier of your account, so you cannot change it. However, if you found there are spelling mistakes and/or inappropriate word(s)/ character(s)
                     in your username, you can contact our developers for changing the username.
                  </div>
               </div>
            </div>
         </div>
         <br>
         <div class="accordion" id="intranetdev">
            <h5>Intranet Development</h5>
            <div class="accordion-item">
               <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#joinidt">
                     How can I contribute to Intranet development?
                  </button>
               </h2>
               <div id="joinidt" class="accordion-collapse collapse" data-bs-parent="#intranetdev">
                  <div class="accordion-body">
                     Our <a href="https://github.com/Intranet-Development-Team/Intranet" target="_blank">GitHub repository</a> is open to the public. You can fork the repository, make changes, and then create a pull request to contribute to Intranet development. We welcome all kinds of contributions, including but not limited to bug fixes, new features, optimizations, and documentation improvements.
                  </div>
               </div>
            </div>
            <div class="accordion-item">
               <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#improint">
                     How can I help improve Intranet?
                  </button>
               </h2>
               <div id="improint" class="accordion-collapse collapse" data-bs-parent="#intranetdev">
                  <div class="accordion-body">
                     You can <a href="https://<?= SITE_DOMAIN ?>/Support/?feedback">send feedback</a> to us to report bugs you found, tell us what we can do better, and what features do you want in the future. Your support is the best way to help us to improve Intranet and the user experience of others. Or, if you are familiar with GitHub, <a href="https://github.com/Intranet-Development-Team/Intranet/issues" target="_blank">create an issue</a> in our GitHub repository.
                  </div>
               </div>
            </div>
         </div>
      <?php
      }
      else if (isset($_GET["feedback"]))
      {
      ?>
         <a class="btn me-2" href="/Support" style="float:left;margin:auto;"><i class="bi bi-arrow-left" style="font-size:1.5em;"></i></a>
         <h1>Feedback</h1>
         <h6>We would love to hear your thoughts on how we can improve your experience.</h6>
         <br>
         <form method="post" enctype="multipart/form-data" onsubmit="preventMisclick($('#feedbacksubmitbtn'))">
            <input type="text" class="form-control form-control-lg" placeholder="Descriptive title" name="feedbacktitle" required> <br>
            <h5>Select a feedback type:</h5>
            <select class="form-select" name="feedbacktype" required>
               <option value="Comments">Comments</option>
               <option value="Questions">Questions</option>
               <option value="Bug reports">Bug reports</option>
               <option value="Feature requests">Feature requests</option>
               <option value="Ideas">Ideas</option>
               <option value="Other">Other</option>
            </select>
            <br>
            <h5>Feedback:</h5>
            <textarea class="form-control" style="min-height:15em;" name="feedback" required></textarea>
            <br>
            <h5>Additional picture description(if needed):</h5>
            <input type="file" name="feedbackadditpic" accept="image/*" class="form-control"> <br><br><button type="submit" class="btn btn-primary" id="feedbacksubmitbtn">Submit</button>
         </form>
      <?php
      }
      else if (isset($_GET["abuse"]))
      {
      ?>
         <a class="btn me-2" href="/Support" style="float:left;margin:auto;"><i class="bi bi-arrow-left" style="font-size:1.5em;"></i></a>
         <h1>Report abuse</h1>
         <h6>Help all the users use Intranet more freely, confidently, equally and productively.</h6>
         <br>
         <form method="post" enctype="multipart/form-data" onsubmit="preventMisclick($('#reportsubmitbtn'))">
            <input type="text" class="form-control form-control-lg" placeholder="Descriptive title" name="abusetitle" required> <br>
            <h5>Basic information of the incident:</h5>
            <input type="text" class="form-control" placeholder="When did the incident happen?" name="when" required> <br><input type="text" class="form-control" placeholder="Who is/are involved in the incident?" name="who" required> <br><input type="text" class="form-control" placeholder="Where (Which page(s) on Intranet) did the incident happen?" name="where" required> <br>
            <h5>Describe what happened in detail:</h5>
            <textarea class="form-control" name="abusedescription" style="resize:vertical;min-height:20em;" required></textarea>
            <br>
            <h5>Screen capture evidence:</h5>
            <input type="file" name="abuseevidence" accept="image/*" class="form-control" required> <br><br><button type="submit" class="btn btn-primary" id="reportsubmitbtn">Submit</button>
         </form>
      <?php
      }
      else if (isset($_GET["about"]))
      {
      ?>
         <a class="btn me-2" href="/Support" style="float:left;margin:auto;"><i class="bi bi-arrow-left" style="font-size:1.5em;"></i></a>
         <h1>About</h1>
         <br>
         <div style="text-align:center;">
            <img src="/Complements/img/icon.png" style="width:6em;margin:auto;display:inline;">
            <h1 style="margin-left:0.5em;display:inline;vertical-align:middle;font-size:3em;">Intranet</h1>
         </div>
         <br>
         <hr>
         <h5 style="text-align:center;line-height:1.5em;">
            Version 3.2<br>Released on 25/3/2026<br><br>Site design &copy; Intranet Development Team<br>
            <p>Licensed under the MIT License.</p><br>
            <h6 style="text-align:center;line-height:1.5em;">Members of the Intranet Development Team<br>Henry Chan<br></h6>
         </h5>
      <?php
      }
      else if (isset($_GET["boardofmods"]))
      {
         $notificationsystemoperator->removeNotificationByIDs("mod_election");

         $onelection = strtotime(date("Y-m") . "-1") <= time() && time() < strtotime(date("Y-m") . "-6");
      ?>
         <a class="btn me-2" href="/Support" style="float:left;margin:auto;"><i class="bi bi-arrow-left" style="font-size:1.5em;"></i></a>
         <h1>Board of Moderators</h1>
         <h6>Moderators moderate and maintain the good atmosphere of Intranet. They are elected through nominations and popular votes.</h6>
         <br>
         <h3>Incumbent moderators</h3>
         <?php
         $currentmodsdisplay = "";
         foreach (scandir("../Login/Accounts") as $item)
         {
            if ($item !== ".." && $item !== "." && is_dir("../Login/Accounts/" . $item))
            {
               try
               {
                  $testuser = new User($item);
                  if ($testuser->hasRole(["moderator"]))
                  {
                     $currentmodsdisplay .= '<div class="card m-2" style="white-space:nowrap;width:fit-content;">
                     <div class="card-body">
                       <h5 style="margin-bottom:0;"><img src="' . $testuser->getpfp() . '" style="border-radius: 50%;display:inline-block;width:2em;" class="border me-2">' . $testuser->usernamefordisplay . '</a></h5>
                     </div>
                   </div>';
                  }
               }
               catch (UnknownUsernameException $e)
               {
                  //do nothing
               }
            }
         }
         if ($currentmodsdisplay === "")
         {
            $currentmodsdisplay = '<h5>There\'s no incumbent moderators.</h5>';
         }
         echo '<div style="display:flex;justify-content:stretch;flex-wrap: wrap;">' . $currentmodsdisplay . '</div>';
         ?>
         <hr>
         <p id="modelection">The election of new moderators will be started at 00:00 on the <b>1<sup>st</sup></b> of each month. All users of Intranet can nominate themselves to be moderators. Each user can vote for <b>one</b> candidate. The <b>3</b> candidates who get the most votes (<b>>3</b>) at 23:59 on the <b>5<sup>th</sup></b> of the month will be appointed to be the new moderators of Intranet. There can be at most <b>5 candidates</b> per month.</p>
         <p>If two or more candidates have the same votes, the nomination time will be used to compete (the earlier the better).</p>
         <p>The appointed moderators will have the role until the <b>5<sup>th</sup></b> of the next month if they're not elected the next month. However, if moderators do not fulfill their obligations, they might be impeached early.</p>
         <p>Voters' information, including their usernames, will not be disclosed.</p>
         <hr>
         <?php
         if ($onelection)
         {
            echo '<h3 class="me-2">Candidates' . (is_file("nominatedmods_" . date("Y-m") . ".txt") && array_key_exists($current->username, (array)json_decode(fileread("nominatedmods_" . date("Y-m") . ".txt"), true)) ? '<button class="btn btn-primary m-auto ms-3" disabled><i class="bi bi-check2-all"></i> Nominated</button>' : (!is_file("nominatedmods_" . date("Y-m") . ".txt") || (is_file("nominatedmods_" . date("Y-m") . ".txt") && count((array)json_decode(fileread("nominatedmods_" . date("Y-m") . ".txt"), true)) <= 5) ? '<button class="btn btn-primary m-auto ms-3" data-bs-toggle="modal" data-bs-target="#nominationmodal"><i class="bi bi-person-plus-fill"></i> New nomination</button>' : '<button class="btn btn-primary m-auto ms-3" data-bs-toggle="modal" data-bs-target="#nominationmodal" disabled><i class="bi bi-pause-fill"></i> Maximum number of candidates reached</button>')) . '</h3>
            <div class="modal fade" tabindex="-1" id="nominationmodal">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
    <form method="post" action="#modelection">
      <div class="modal-header">
        <h5 class="modal-title">New nomination</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><b>Nominate yourself to be a moderator.</b></p>
        <label for="nominationdescription" class="form-label">Description of candidate:</label>
        <textarea class="form-control" name="description" placeholder="Why would you elect yourself to be a moderator?" id="nominationdescription" required></textarea>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" name="nominationsubmit" type="submit">Submit</button>
      </div>
      </form>
    </div>
  </div>
</div>';
         }
         else
         {
            echo '<h3 class="me-2">Election result</h3>';
         }
         $nomiantedmodsdisplay = "";
         if (is_file("nominatedmods_" . date("Y-m") . ".txt") && !empty(json_decode(fileread("nominatedmods_" . date("Y-m") . ".txt"))))
         {
            require_once("../CoreLibrary/DatetimeHandlers.php");
            $nominations = json_decode(fileread("nominatedmods_" . date("Y-m") . ".txt"), true);
            $voted = false;
            foreach ($nominations as $candidate => $nomination)
            {
               if (in_array($current->username, $nomination["votes"]))
               {
                  $voted = $candidate;
               }
            }
            foreach ($nominations as $candidate => $nomination)
            {
               try
               {
                  $testuser = new User($candidate);
                  if ($onelection)
                  {
                     $nomiantedmodsdisplay .= '<div class="card m-2 col" style="min-width:15em;">
                     <div class="card-body">
                       <h5><img src="' . $testuser->getpfp() . '" style="border-radius: 50%;display:inline-block;width:2em;" class="border me-2">' . $testuser->usernamefordisplay . '</a></h5><p>' . $nomination["description"] . '</p><small style="display:block;" class="text-muted">Nominated ' . displayDatetime($nomination["time"]) . '</small><br><br><br><br><div style="position:absolute;right:1em;bottom:1em;"><small style="display:block;" class="text-muted float-end">Voted by ' . count($nomination["votes"]) . ' user' . (count($nomination["votes"]) > 1 ? "s" : "") . '</small><br>' . ($voted !== false ? ($voted === $candidate ? '<button class="btn btn-primary float-end" disabled><i class="bi bi-check2-circle"></i> Voted</button>' : '<button class="btn btn-primary float-end" disabled><i class="bi bi-check2"></i> Vote</button>') : '
                       <form method="post" action="#modelection"><input type="hidden" name="votecandidate" value="' . $testuser->username . '"><button class="btn btn-primary float-end"><i class="bi bi-check2"></i> Vote</button></form>') . ($current->username === $candidate ? '<br><br><form method="post" action="#modelection"><input type="hidden"  name="quitelection"><a class="link-danger" href="javascript:void();" style="display:block;text-decoration:none;" onclick="if(confirm(\'Are you sure to quit the election?\')){$(this).parent().submit();}"><i class="bi bi-escape"></i> Quit election</a></form>' : "") . '</div>
                     </div>
                   </div>';
                  }
                  else
                  {
                     $nomiantedmodsdisplay .= '<div class="card m-2 col" style="min-width:15em;">
                     <div class="card-body">
                       <h5><img src="' . $testuser->getpfp() . '" style="border-radius: 50%;display:inline-block;width:2em;" class="border me-2">' . $testuser->usernamefordisplay . '</a></h5><p>' . $nomination["description"] . '</p><small style="display:block;" class="text-muted">Nominated ' . displayDatetime($nomination["time"]) . '</small><br><br><br><br><div style="position:absolute;right:1em;bottom:1em;"><small style="display:block;" class="text-muted float-end">Voted by ' . count($nomination["votes"]) . ' user' . (count($nomination["votes"]) > 1 ? "s" : "") . '</small>' . (array_key_exists("elected", $nomination) ? '<br><small style="display:block;" class="float-end text-success fw-bolder">Elected</small>' : "") . ($current->hasRole(["developer"]) ? (array_key_exists("elected", $nomination) ? '<br><form method="post" action="#modelection"><input type="hidden" name="unmarkelectedcandidate" value="' . $testuser->username . '"><button class="btn btn-success float-end"  name="unmarkelected">Unmark as elected</button></form>' : '<form method="post" action="#modelection"><input type="hidden" name="markelectedcandidate" value="' . $testuser->username . '"><button class="btn btn-success float-end"  name="markelected">Mark as elected</button></form>') : "") . '</div>
                     </div>
                   </div>';
                  }
               }
               catch (UnknownUsernameException $e)
               {
                  //do nothing
               }
            }
         }
         if ($nomiantedmodsdisplay === "")
         {
            if ($onelection)
            {
               $nomiantedmodsdisplay = '<h5>There\'re no candidates currently.</h5>';
            }
            else
            {
               $nomiantedmodsdisplay = '<h5>There\'re no candidates this month.</h5>';
            }
         }
         echo '<div class="row">' . $nomiantedmodsdisplay . '</div>';
         ?>
      <?php
      }
      else if (isset($_GET["formatting"]))
      {
         require_once("../CoreLibrary/IMP.php");
         $IMP = new IMP();
      ?>
         <style>
            pre {
               white-space: break-spaces;
            }
         </style>
         <a class="btn me-2" href="/Support" style="float:left;margin:auto;"><i class="bi bi-arrow-left" style="font-size:1.5em;"></i></a>
         <h1>Text formatting guidelines</h1>
         <h6>Guidelines for using Intranet Markup to style your text.</h6>
         <br>
         <p><b>I</b>ntranet <b>M</b>arkup (IM) is a human-readable markup language developed by the IDT for the users of Intranet to style their text contents.</p>
         <p>IM is very similar to <a href="https://en.wikipedia.org/wiki/Markdown" target="_blank">Markdown</a>, a lightweight markup language that is used in well-known websites like Reddit and GitHub, but with some tiny differences which make IM even more human-readable.</p>
         <p>You can easily use IM for most of your inputs in Intranet. The following are some basic syntaxes.</p>
         <h3>Inline elements</h3>
         <p>Inline elements refer to those formatting elements that can be used in all editors. They are basically just changing the font properties of the text.</p>
         <div style="overflow:auto;">
            <table class="table table-striped">
               <thead>
                  <th>Element</th>
                  <th>Syntax</th>
                  <th>Result</th>
               </thead>
               <tr>
                  <th>Bold</th>
                  <td>
                     <pre>To fight and be **bold**</pre>
                  </td>
                  <td><?= $IMP->line("To fight and be **bold**") ?></td>
               </tr>
               <tr>
                  <th>Italics</th>
                  <td>
                     <pre>*Hamlet* is a tragedy written by William Shakespeare.</pre>
                  </td>
                  <td><?= $IMP->line("*Hamlet* is a tragedy written by William Shakespeare.") ?></td>
               </tr>
               <tr>
                  <th>Underline</th>
                  <td>
                     <pre>1+1=__2__</pre>
                  </td>
                  <td><?= $IMP->line("1+1=__2__") ?></td>
               </tr>
               <tr>
                  <th>Strikethrough</th>
                  <td>
                     <pre>8*8=~~64~~</pre>
                  </td>
                  <td><?= $IMP->line("8*8=~~64~~") ?></td>
               </tr>
               <tr>
                  <th>Highlight</th>
                  <td>
                     <pre>I need to highlight these ==very important words==.</pre>
                  </td>
                  <td><?= $IMP->line("I need to highlight these ==very important words==.") ?></td>
               </tr>
               <tr>
                  <th>Superscript</th>
                  <td>
                     <pre>8^{2}=64</pre>
                  </td>
                  <td><?= $IMP->line("8^{2}=64") ?></td>
               </tr>
               <tr>
                  <th>Subscript</th>
                  <td>
                     <pre>10100101_{2} = 165_{10}</pre>
                  </td>
                  <td><?= $IMP->line("10100101_{2} = 165_{10}") ?></td>
               </tr>
               <tr>
                  <th>Code</th>
                  <td>
                     <pre>`#ffffff` represents the colour of white in HTML.</pre>
                  </td>
                  <td><?= $IMP->line("`#ffffff` represents the colour of white in HTML.") ?></td>
               </tr>
               <tr>
                  <th>Link</th>
                  <td>
                     <pre>
URL as link text (no markup needed):
https://example.com
This will have the same effect: &lt;https://example.com&gt;

Customise link text:
[example link](https://example.com)
               </pre>
                  </td>
                  <td><?= $IMP->text("URL as link text (no markup needed):\nhttps://example.com\nThis will have the same effect: <https://example.com>\n\nCustomise link text:\n[example link](https://example.com)") ?></td>
               </tr>
            </table>
         </div>

         <h3 class="mt-2">Multiline elements</h3>
         <p>Multiline elements refer to those formatting elements that can only be used in multiline editors.</p>
         <div style="overflow:auto;">
            <table class="table table-striped">
               <thead>
                  <th>Element</th>
                  <th>Syntax</th>
                  <th>Result</th>
               </thead>
               <tr>
                  <th>Parahraph</th>
                  <td>
                     <pre>
Simply type any text, multiline editors will turn it to a paragraph.

Add an empty line to start a new paragraph.
</pre>
                  </td>
                  <td>
                     <?= $IMP->text("Simply type any text, multiline editors will turn it to a paragraph.\n\nAdd an empty line to start a new paragraph.") ?>
                  </td>
               </tr>
               <tr>
                  <th>Headings</th>
                  <td>
                     <pre>
# Heading 1
## Heading 2
### Heading 3
#### Heading 4
##### Heading 5
###### Heading 6

and a paragraph
      </pre>
                  </td>
                  <td>
                     <?= $IMP->text("# Heading 1\n## Heading 2\n### Heading 3\n#### Heading 4\n##### Heading 5\n###### Heading 6\n\nand a paragraph") ?>
                  </td>
               </tr>
               <tr>
                  <th>Image</th>
                  <td>
                     <pre>
![description here](https://picsum.photos/500/300)

The default is set to fill the parent width. You can also define the size of the image by adding `&lt;n&gt;` where `n` is the width (in scale with height) of the image:
![description here](https://picsum.photos/500/300)&lt;10&gt;
               </pre>
                  </td>
                  <td>
                     <?= $IMP->text("![description here](https://picsum.photos/500/300)\n\nThe default is set to fill the parent width. You can also define the size of the image by adding `<n>` where `n` is the width (in scale with height) of the image:\n![description here](https://picsum.photos/500/300)<10>") ?>
                  </td>
               </tr>
               <tr>
                  <th>Blockquote</th>
                  <td>
                     <pre>
> As A has said:
>> As B has said:
>>> "Blockquote is nestable!"
>> And C has said:
>>> "Just add \> at the beginning of the line to make a blockquote."
> We can see how good IM is
>
> You can also start a paragraph by adding an empty line.
               </pre>
                  </td>
                  <td>
                     <?= $IMP->text("> As A has said:\n>> As B has said:\n>>> \"Blockquote is nestable!\"\n>> And C has said:\n>>> \"Just add \\> at the beginning of the line to make a blockquote.\"\n> We can see how good IM is\n>\n> You can also start a paragraph by adding an empty line.") ?>
                  </td>
               </tr>
               <tr>
                  <th>Horizontal rule</th>
                  <td>
                     <pre>
You may add horizontal rules with the following syntaxes:
---
***
___
They are the same when being displayed.
               </pre>
                  </td>
                  <td>
                     <?= $IMP->text("You may add horizontal rules with the following syntaxes:\n---\n***\n___\nThey are the same when being displayed.") ?>
                  </td>
               </tr>
               <tr>
                  <th>Line break</th>
                  <td>
                     <pre>
Simply
press
`enter`
in
multiline
editors
will
do
               </pre>
                  </td>
                  <td>
                     <?= $IMP->text("Simply\npress\n`enter`\nin\nmultiline\neditors\nwill\ndo") ?>
                  </td>
               </tr>
               <tr>
                  <th>Ordered List</th>
                  <td>
                     <pre>
1. Put a number, a dot and ==a space== at the beginning of the line.
2. You can add as many items as you want by extending the numbers.
4. **Even if you have bad math**
    1. Indent an item by putting spaces in the front.
        1. You can even nest more items
    2. And back to the previous layer
      You can also add paragraphs
      > and blockquote
          Putting 4 more indent spaces forms a preformatted block
4. etc.
               </pre>
                  </td>
                  <td>
                     <?= $IMP->text("1. Put a number, a dot and ==a space== at the beginning of the line.\n2. You can add as many items as you want by extending the numbers.\n4. **Even if you have bad math**\n    1. Indent an item by putting spaces in the front.\n        1. You can even nest more items\n    2. And back to the previous layer\n      You can also add paragraphs\n      > and blockquote\n          Putting 4 more indent spaces forms a preformatted block\n4. etc.\n") ?>
                  </td>
               </tr>
               <tr>
                  <th>Unordered List</th>
                  <td>
                     <pre>
- everything is same here but **unordered**
 - but rememeber space after the dash is still needed!
 * an asterisk also works!
 + as well as a plus sign
               </pre>
                  </td>
                  <td>
                     <?= $IMP->text("- everything is same here but **unordered**\n - but rememeber space after the dash is still needed!\n * an asterisk also works!\n + as well as a plus sign") ?>
                  </td>
               </tr>
               <tr>
                  <th>Reference-style Link and Image</th>
                  <td>
                     <pre>
Sometimes, for tidiness, you may not want to inset the URL directly inline.
You can do it by insert a reference like this:
[a link](examplesite)
[examplesite]: https://example.com

This also work with images:
![an image](exampleimg)
[exampleimg]: https://picsum.photos/500/300

You can give any name to the reference, but make sure there are no duplicates.
You can place the reference at any place.
               </pre>
                  </td>
                  <td>
                     <?= $IMP->text("Sometimes, for tidiness, you may not want to inset the URL directly inline.\nYou can do it by insert a reference like this:\n[a link](examplesite)\n[examplesite]: https://example.com\n\nThis also work with images:\n![an image](exampleimg)\n[exampleimg]: https://picsum.photos/500/300\n\nYou can give any name to the reference, but make sure there are no duplicates.\nYou can place the reference at any place.") ?>
                  </td>
               </tr>
               <tr>
                  <th>Prefomatted Code Block</th>
                  <td>
                     <pre>
When you type more than one space consecutively, 
they're usually still rendered as one space:
a          b          c

Same as line breaks:
a



b



c

But they'll be preserved in **Prefomatted Code Block**:
```
a          b          c



b



c
```
You can use this to create [ASCII art](link1) or insert code.
[link1]: https://en.wikipedia.org/wiki/ASCII_art
               </pre>
                  </td>
                  <td>
                     <?= $IMP->text("When you type more than one space consecutively, \n they're usually still rendered as one space: \na          b          c\n\nSame as line breaks:\na\n\n\nb\n\nc\n\nBut they'll be preserved in **Prefomatted Code Block**:\n```\na          b          c\n\n\nb\n\n\nc\n```\nYou can use this to create [ASCII art](link1) or insert code.\n[link1]: https://en.wikipedia.org/wiki/ASCII_art") ?>
                  </td>
               </tr>
            </table>
         </div>
         <h3 class="mt-2">Escape character</h3>
         <p>Sometimes, you may want to type out some symbols used as markup syntax literally. You can <a href="https://en.wikipedia.org/wiki/Escape_character#:~:text=In%20computing%20and%20telecommunication%2C%20an,a%20particular%20case%20of%20metacharacters." target="_blank">escape those characters</a> with a backslash '\'.</p>
         <div style="overflow:auto;">
            <table class="table table-striped">
               <thead>
                  <th>Syntax</th>
                  <th>Result</th>
               </thead>
               <tr>
                  <td>
                     <pre>
Without backslashes: These are some asterisks: **hey**
With backslashes: These are some asterisks: \*\*hey\*\*
</pre>
                  </td>
                  <td>
                     <?= $IMP->text("Without backslashes: These are some asterisks: **hey**\nWith backslashes: These are some asterisks: \\*\*hey\\*\*") ?>
                  </td>
               </tr>
               <tr>
                  <td>
                     <pre>
What if you want to display backslashes?
You can escape a backslash itself by doubling it: \\
</pre>
                  </td>
                  <td>
                     <?= $IMP->text("What if you want to display backslashes?\nYou can escape a backslash itself by doubling it: \\") ?>
                  </td>
               </tr>
               <tr>
                  <td>
                     <pre>
List of escapable characters:

- Backslash: \\
- Backtick: \`
- Asterisk: \*
- Underscore: \_
- Curly braces: \{ \}
- Square brackets: \[ \]
- Less-than sign: \<
- Greater-than sign: \>
- Parentheses: \( \)
- Hash symbol: \#
- Plus sign: \+
- Hyphen/minus sign: \-
- Period/full stop: \.
- Exclamation mark: \!
- Pipe symbol: \|
- Tilde: \~
- Circumflex accent: \^

These characters all have special meanings in IM, so if you want to use them as literal characters, you need to escape them with a backslash (\\) in front of them.
</pre>
                  </td>
                  <td>
                     <?= $IMP->text("List of escapable characters:\n\n- Backslash: \\\\\n- Backtick: \\`\n- Asterisk: \\*\n- Underscore: \\_\n- Curly braces: \\{ \\}\n- Square brackets: \\[ \\]\n- Less-than sign: \\<\n- Greater-than sign: \\>\n- Parentheses: \\( \\)\n- Hash symbol: \\#\n- Plus sign: \\+\n- Hyphen/minus sign: \\-\n- Period/full stop: \\.\n- Exclamation mark: \\!\n- Pipe symbol: \\|\n- Tilde: \\~\n- Circumflex accent: \\^\n\nThese characters all have special meanings in IM, so if you want to use them as literal characters, you need to escape them with a backslash (\\\\) in front of them.\n") ?>
                  </td>
               </tr>
            </table>
         </div>
         <hr>
         <h3 class="mt-2">About</h3>
         <p>IM parser is developed by IDT and its source code is available under the <a href="https://en.wikipedia.org/wiki/MIT_License" target="_blank">MIT license</a>. Visit our <a href="https://github.com/Intranet-Development-Team/intranet-markup-parser" target="_blank">GitHub repository</a> to view the source code, start an issue, or develop upon it.</p>
      <?php
      }
      else
      {
      ?>
         <h1>Support</h1>
         <hr>
         <div class="row">
            <a class="col-lg p-3 option" href="?faq">
               <div class="card shadow p-3 rounded-4">
                  <h1 id="background"><i class="bi bi-question-circle"></i></h1>
                  <div class="card-body">
                     <h5 class="card-title">FAQ</h5>
                     <p class="card-text">Find the official answers of the common questions about Intranet.</p>
                  </div>
               </div>
            </a>
            <a class="col-lg p-3 option" href="?feedback">
               <div class="card shadow p-3 rounded-4">
                  <h1 id="background"><i class="bi bi-chat-square-dots"></i></h1>
                  <div class="card-body">
                     <h5 class="card-title">Feedback</h5>
                     <p class="card-text">Report bugs, request features, ask questions, tell us your ideas.</p>
                  </div>
               </div>
            </a>
            <a class="col-lg p-3 option" href="?abuse">
               <div class="card shadow p-3 rounded-4">
                  <h1 id="background"><i class="bi bi-exclamation-octagon"></i></h1>
                  <div class="card-body">
                     <h5 class="card-title">Report abuse</h5>
                     <p class="card-text">Report bullying, spamming, swearing, and other bad behaviour.</p>
                  </div>
               </div>
            </a>
         </div>
         <div class="row">
            <a class="col-lg p-3 option" href="?formatting">
               <div class="card shadow p-3 rounded-4">
                  <h1 id="background"><i class="bi bi-fonts"></i></h1>
                  <div class="card-body">
                     <h5 class="card-title">Text formatting</h5>
                     <p class="card-text">Learn how can you use Intranet Markup to customize your content.</p>
                  </div>
               </div>
            </a>
            <a class="col-lg p-3 option" href="?boardofmods">
               <div class="card shadow p-3 rounded-4">
                  <h1 id="background"><i class="bi bi-hexagon-half"></i></h1>
                  <div class="card-body">
                     <h5 class="card-title">Board of Moderators <?= ($boardofmodsPageNotificationsCount ? '<span class="badge rounded-pill bg-danger d-inline-block ms-3" style="vertical-align:.1em;">' . $boardofmodsPageNotificationsCount . '</span>' : "") ?></h5>
                     <p class="card-text">View the list of moderators, nominate, and vote for candidates.</p>
                  </div>
               </div>
            </a>
            <a class="col-lg p-3 option" href="?about">
               <div class="card shadow p-3 rounded-4">
                  <h1 id="background"><i class="bi bi-info-circle"></i></h1>
                  <div class="card-body">
                     <h5 class="card-title">About</h5>
                     <p class="card-text">View copyright info, technical details, and version info of Intranet.</p>
                  </div>
               </div>
            </a>
         </div>
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
