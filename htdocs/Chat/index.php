<?php
require("../CoreLibrary/CoreFunctions.php");

$current = new Session("Chat", "Chat");
?>
<!DOCTYPE html>
<html>
<?= $current->getHtmlHead() ?>

<body class="container pt-4">
  <header><?= $current->getNavBar() ?></header>
  <?php
  if ($current->accessstatus)
  {
    echo '<h1>Chat</h1><hr>';
    echo '
      <ul class="nav nav-tabs">
      <li class="nav-item">
        <a class="nav-link' . (empty($_SERVER['QUERY_STRING']) ? ' active" href="#"' : '" href="?"') . '>General Chat</a>
      </li>
      </ul>
      ';

    echo '<div class="card" style="width:100%;"><div class="card-body" id="maintext" style="height:35em;overflow:auto;"><div style="margin: 0;position: absolute;top: 50%;left: 50%;transform: translate(-50%, -50%);"><div class="spinner-border" role="status" ></div></div></div><div class="card-header"><div class="input-group"><textarea class="form-control" style="overflow:auto;resize:none;height:1em;" id="chattext" autofocus></textarea><button class="btn btn-primary" style="display:inline;" onclick="submmsg()" id="submbtn"><i class="bi bi-send"></i></button></div></div></div></div>';
  }
  else
  {
    echo $current->accessstatusmsg;
  }
  ?>
  <?= $current->getFooter() ?>
  <script>
    $(document).ready(loadLog(1));

    function loadLog(needgodown = 0) {
      $.ajax({
        url: "message.php",
        cache: false,
        success: function(html) {
          if (html != "") {
            html = JSON.parse(html);
            var ttp = "";
            for (var i = 0; i < html.length; ++i) {
              if (lasttime != (html[i])[2].split(" ")[0]) {
                var date = new Date();

                var today = date;
                var dd = String(today.getDate()).padStart(2, '0');
                var mm = String(today.getMonth() + 1).padStart(2, '0');
                var yyyy = today.getFullYear();

                date.setDate(date.getDate() - 1);
                var yesterday = date;
                var dd2 = String(yesterday.getDate()).padStart(2, '0');
                var mm2 = String(yesterday.getMonth() + 1).padStart(2, '0');
                var yyyy2 = yesterday.getFullYear();

                ttp += (ttp != "" ? "<br>" : "") + "<div style=\"text-align:center;\"><span class=\"badge bg-body-tertiary text-body-tertiary\">" + ((html[i])[2].split(" ")[0] == yyyy + "-" + mm + "-" + dd ? "Today" : ((html[i])[2].split(" ")[0] == yyyy2 + "-" + mm2 + "-" + dd2 ? "Yesterday" : (html[i])[2].split(" ")[0])) + "</span></div>";
              }

              let temptext = document.createElement('span');
              temptext = $(temptext).html((html[i])[0]).text();

              if (4 in html[i]) {
                ttp += '<div class="row" style="width:fit-content;"><div class="col" style="flex-grow:unset;"><img style="border-radius: 50%;display:inline-block;width:2em;" src="' + (html[i])[4] + '" data-hasimg="' + (html[i])[3] + '"></div><div class="col"><h6>' + (html[i])[0] + '<small class="text-muted ms-2" style="font-size:0.8em;">' + (html[i])[2].split(" ")[1] + '</small></h6><p>' + (html[i])[1] + '</p></div></div>';
              } else {
                if (i - 1 >= 0 && html[i - 1][3] == html[i][3] && Math.abs(html[i][2].split(" ")[1].split(":")[1] - html[i - 1][2].split(" ")[1].split(":")[1]) <= 5) {
                  ttp += '<div class="row" style="width:fit-content;"><div class="col" style="margin-left:3.5em;"><p>' + (html[i])[1] + '</p></div></div>';
                } else {
                  ttp += '<div class="row" style="width:fit-content;"><div class="col" style="flex-grow:unset;"><img style="border-radius: 50%;display:inline-block;width:2em;" src="' + $($.parseHTML(ttp)).find('img[data-hasimg="' + (html[i])[3] + '"]').first().attr("src") + '"></div><div class="col"><h6>' + (html[i])[0] + '<small class="text-muted ms-2" style="font-size:0.8em;">' + (html[i])[2].split(" ")[1] + '</small></h6><p>' + (html[i])[1] + '</p></div></div>';
                }
              }

              var lasttime = (html[i])[2].split(" ")[0];
            }
            if (ttp != document.getElementById("maintext").innerHTML) {
              if (needgodown == 1) {
                document.getElementById("maintext").innerHTML = ttp;
                $('#maintext').scrollTop($('#maintext')[0].scrollHeight);
              } else {
                if (chk_scroll()) {
                  document.getElementById("maintext").innerHTML = ttp;
                  $('#maintext').scrollTop($('#maintext')[0].scrollHeight);
                } else {
                  document.getElementById("maintext").innerHTML = ttp;
                }
              }
            }
          } else {
            document.getElementById("maintext").innerHTML = "";
          }
        },
      });
    }

    function submmsg() {
      var msgtosend = document.getElementById("chattext").value;
      if (msgtosend != "") {
        $.ajax({
          url: "message.php?",
          cache: false,
          data: "msg=" + encodeURIComponent(msgtosend),
          type: "post",
          success: function(html) {
            loadLog(1);
          },
        });
      }
      document.getElementById("chattext").value = "";
    }

    function chk_scroll() {
      var elem = document.getElementById("maintext");
      if ((elem.scrollTop + elem.clientHeight) - elem.scrollHeight >= -5) {
        return true;
      } else {
        return false;
      }
    }

    $("#chattext").keydown(function(e) {
      if (e.keyCode == 13 && !e.shiftKey) {
        e.preventDefault();
        submmsg();

      }
    });

    setInterval(loadLog, 3000);
  </script>
</body>

</html>