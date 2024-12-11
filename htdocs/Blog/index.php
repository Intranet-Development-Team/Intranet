<?php
require_once("../CoreLibrary/CoreFunctions.php");

$current = new Session("Blog", "Blog");
require_once("../CoreLibrary/Blog.php");
?>
<!DOCTYPE html>
<html>
<?= $current->getHtmlHead() ?>

<body class="container pt-4">
  <header><?= $current->getNavBar() ?></header>
  <?php
  if ($current->accessstatus)
  {
    $notificationsystemoperator = new NotificationSystemOperator();
    $notificationsystemoperator->removeNotificationByIDs("blog_liked", "blog_commented");

    echo '<h1 class="d-flex"><span class="flex-fill">Blog</span><a type="button" class="btn btn-success align-self-center" href="/NewBlog">+ Write new blog</a></h1><hr>';
    $avaliableblogsids = [];
    for ($i = count(array_diff(scandir("Blogs"), array('..', '.'))); $i >= 1; --$i)
    {
      $blogfilecontent = fileread($_SERVER["DOCUMENT_ROOT"] . "/Blog/Blogs/$i.txt");
      if ($blogfilecontent != "DELETED")
      {
        $blogobj = unserialize($blogfilecontent);
        if ($blogobj->isVisibleBy($current))
        {
          if ($blogobj !== false && $blogobj->isVisibleBy($current))
          {
            $avaliableblogsids[] = $i;
          }
        }
      }
    }
    echo '<div id="blogscontainer"></div><input id="blogsavailable" type="hidden" value="' . htmlspecialchars(json_encode($avaliableblogsids, ENT_QUOTES)) . '">';
    echo '<h6 style="text-align:center;display: block;margin-top:1em;margin-left: auto;margin-right: auto;" class="spinner-border" id="endtip"></h6>';
  }
  else
  {
    echo $current->accessstatusmsg;
  }
  ?>
  <?= $current->getFooter() ?>
  <script>
    const blogsavailable = JSON.parse($("#blogsavailable").val());

    $(function() {
      if (blogsavailable.length > 0) {
        fetchBlog();
      } else {
        $("#endtip").removeClass().html("No more blogs recently");
      }
    });

    function fetchBlog() {
      $.ajax({
        url: "fetchblog.php?id=" + blogsavailable[0],
        cache: false,
        success: function(html) {
          $("#blogscontainer").append(html);
          if (blogsavailable.length === 1) {
            $.ajax({
              url: "fetchblog.php?id=" + blogsavailable[0],
              cache: false,
            });
            blogsavailable.shift();
            $("#blogsavailable").remove();
            $("#endtip").removeClass().html("No more blogs recently");
          } else {
            blogsavailable.shift();
            fetchBlog();
          }
        }
      });
    }

    function toggleLikeBlog(likebtn) {
      likebtn.html('<i class="spinner-border spinner-border-sm"></i>');
      likebtn.attr("onclick", "");
      $.ajax({
        url: "likeblog.php",
        cache: false,
        type: "post",
        data: {
          id: likebtn.parent().parent().attr('id')
        },
        success: function(json) {
          likebtn.attr("onclick", "toggleLikeBlog($(this))");
          json = JSON.parse(json);
          if (json.operation === "liked") {
            likebtn.attr("class", "btn btn-primary m-2").html('<i class="bi bi-heart-fill"></i> Liked');
          } else {
            likebtn.attr("class", "btn btn-outline-primary m-2").html('<i class="bi bi-heart"></i> Like this');
          }
          likebtn.next().find(">:nth-child(1)").html(json.likesusers.length);
          if (json.likesusers.length === 0) {
            var displaylikes = '<b style="display:block;font-weight:500;">No likes yet</b>';
          } else {
            var displaylikes = '<b style="display:block;font-weight:500;">' + json.likesusers.join('</b><b style="display:block;font-weight:500;">') + '</b>'
          }
          likebtn.next().find(">:nth-child(2)").html(displaylikes);
        },
      });
    }

    function deleteBlog(id) {
      $("#" + id).find("button.delBtn").html('<i class="spinner-border spinner-border-sm"></i>');
      $("#" + id).find("button.delBtn").attr("onclick", "");
      $.ajax({
        url: "deleteblog.php",
        cache: false,
        type: "post",
        data: {
          id: id
        },
        success: function(json) {
          json = JSON.parse(json);
          if (json.status === "success") {
            $("#" + json.id).animate({
              opacity: 0,
              height: "0px"
            }, "slow", "swing", function() {
              $(this).remove();
            });
          } else {
            $("#" + id).find("button.delBtn").attr("onclick", "confirmModal(\'Delete blog\',\'Are you sure to delete this blog? This action cannot be undone.\',\'deleteBlog(" + id + ")\')");
            alertModal("Deletion failed", "Deletion failed, please try again.");
          }
        },
      });
    }

    function postComment(comsubbtn) {
      comsubbtn.html('<i class="spinner-border spinner-border-sm"></i>');
      comsubbtn.attr("onclick", "");
      $.ajax({
        url: "commentops.php",
        type: "post",
        data: {
          id: comsubbtn.parent().parent().parent().parent().attr('id'),
          comment: comsubbtn.prev().val()
        },
        cache: false,
        success: function(json) {
          comsubbtn.attr("onclick", "if($(this).prev().val()!==\'\'){postComment($(this));}");
          comsubbtn.text("Submit");
          json = JSON.parse(json);
          comsubbtn.prev().val("");
          comsubbtn.parent().find("div").html(json.comments);
          comsubbtn.parent().parent().parent().find("button.btn-secondary>span").text(json.count);
        },
      });
    }

    function deleteComment(delbtn) {
      delbtn.html('<i class="spinner-border spinner-border-sm"></i>');
      delbtn.attr("onclick", "");
      $.ajax({
        url: "commentops.php",
        type: "post",
        data: {
          delete: "",
          id: delbtn.parent().parent().parent().parent().parent().parent().attr('id'),
          commentid: delbtn.parent().attr("data-commentid")
        },
        cache: false,
        success: function(json) {
          json = JSON.parse(json);
          delbtn.parent().parent().parent().parent().parent().find("button.btn-secondary>span").text(json.count);
          delbtn.parent().parent().html(json.comments);
        },
      });
    }
  </script>
</body>

</html>