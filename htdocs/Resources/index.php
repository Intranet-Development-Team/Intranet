<?php
require_once("../CoreLibrary/CoreFunctions.php");

$current = new Session("Resources", "Resources");

function getResourcesLinks()
{
  $displaytext = '';
  $links = json_decode(fileread("links.txt"), true);
  foreach ($links as $category)
  {
    $displaytext .= '<div class="card m-3 card-body"><h4>' . $category["category"] . '</h4><ul class="list-unstyled">';
    foreach ($category["contents"] as $item)
    {
      $displaytext .= '<li style="font-size:1.1rem;line-height:1.75rem;"><a target="_blank" href="' . $item["url"] . '">' . $item["name"] . '</a></li>';
    }
    $displaytext .= '</ul></div>';
  }
  return '<div class="d-flex align-content-stretch flex-wrap">' . $displaytext . '</div>';
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
  ?>
    <h1 class="d-flex"><span class="flex-fill">Resources</span><a class="btn align-self-center" href="/EditResources"><i class="bi bi-pencil"></i></a></h1>
    <hr>
    <?= getResourcesLinks(); ?>
    <div class="card m-3">
      <div class="card-body d-flex flex-column" id="innerx" style="height:25em;overflow:auto;">
        <h4>Files</h4>
        <div class="d-flex align-items-center text-center mb-2"><button id="backBtn" onclick="goOut()" class="btn me-1 border-white" disabled><i class="bi bi-arrow-left align-text-top"></i></button>
          <nav>
            <ol class="breadcrumb" id="bc1">
              <li class="breadcrumb-item active">Home</li>
            </ol>
          </nav>
        </div>
        <ul class="list-group" id="resourcesFiles"></ul>
        <div class="d-flex align-items-center text-center flex-fill">
          <h6 class="w-100" id="noitemtip"></h6>
        </div>
      </div>
    </div><iframe id="filedownloader" style="display:none;"></iframe></div>
  <?php
  }
  else
  {
    echo $current->accessstatusmsg;
  }
  ?>
  <?= $current->getFooter() ?>
  <script>
    var liele = document.getElementById("resourcesFiles");
    var currentPath = JSON.stringify(new Array());

    document.getElementById("noitemtip").innerHTML = '<div class="spinner-border" role="status"></div>';

    $.ajax({
      url: "scandir.php",
      cache: false,
      success: function(html) {
        document.getElementById("noitemtip").innerHTML = "";
        const objs = Object.values(JSON.parse(html));
        for (var i = 0; i < objs.length; ++i) {
          var ele = document.createElement('li');
          ele.className = "list-group-item list-group-item-action";
          ele.setAttribute('onclick', 'listitemclicked(this)');
          ele.id = i;
          ele.innerHTML = "<span>" + objs[i][0] + "</span><span style=\"color:grey;float:right;margin-top:0.4em;\">" + objs[i][1] + "</span>";
          liele.appendChild(ele);
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
        url: "scandir.php?filepath=" + encodeURIComponent(pathnow.join("/") + (pathnow.length !== 0 ? "/" : "")),
        cache: false,
        success: function(html) {
          document.getElementById("noitemtip").innerHTML = "";
          const objs = Object.values(JSON.parse(html));
          for (var i = 0; i < objs.length; ++i) {
            var ele = document.createElement('li');
            ele.className = "list-group-item list-group-item-action";
            ele.setAttribute('onclick', 'listitemclicked(this)');
            ele.id = i;
            ele.innerHTML = "<span>" + objs[i][0] + "</span><span style=\"color:grey;float:right;margin-top:0.4em;\">" + objs[i][1] + "</span>";
            liele.appendChild(ele);
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
        url: "scandir.php?",
        cache: false,
        data: "filepath=" + encodeURIComponent(pathnow.join("/") + "/"),
        success: function(html) {
          if (html != "Failed") {
            document.getElementById("noitemtip").innerHTML = "";
            const objs = Object.values(JSON.parse(html));
            for (var i = 0; i < objs.length; ++i) {
              var ele = document.createElement('li');
              ele.className = "list-group-item list-group-item-action";
              ele.setAttribute('onclick', 'listitemclicked(this)');
              ele.id = i;
              ele.innerHTML = "<span>" + objs[i][0] + "</span><span style=\"color:grey;float:right;margin-top:0.4em;\">" + objs[i][1] + "</span>";
              liele.appendChild(ele);
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

    function listitemclicked(objectclicked) {
      if (objectclicked.getElementsByTagName('i')[0].classList.contains("fileitem")) {
        redirectToDownload(objectclicked.firstChild.innerText);
      } else {
        document.getElementById("backBtn").disabled = true;
        goIn(objectclicked.firstChild.innerText);
      }
    }

    function redirectToDownload(fn) {
      document.getElementById("filedownloader").src = "https://<?= SITE_DOMAIN ?>/Resources/downloadresource.php?filepath=" + encodeURIComponent(JSON.parse(currentPath).join("/") + (JSON.parse(currentPath).length !== 0 ? "/" : "")) + "&filename=" + encodeURIComponent(fn);
    }
  </script>
</body>

</html>