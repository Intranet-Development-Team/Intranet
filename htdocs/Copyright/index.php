<?php
require_once("../CoreLibrary/CoreFunctions.php");

$current = new Session("Copyright", "Copyright", USER_LIST, false);
?>
<!DOCTYPE html>
<html>
<?= $current->getHtmlHead() ?>

<body class="container pt-4">
  <header><?= $current->getNavBar() ?></header>
  <div class="text-center mx-5 mt-3 mb-4">
    <h1>Copyright Policy</h1>
    <h5>Before contributing to Intranet, you need to understand how copyrights are owned to defend your rights.</h5>
  </div>
  <br>
  <p><b>1. Cooperated content</b></p>
  <p>Cooperated content refers to the content which is editable by not only a specific user or group of users, but everyone. For instance, Announcements, Assignments, Quote, Did you know, Calendar events, and Resources.</p>
  <p>Cooperated content has no copyright under <a href="https://creativecommons.org/publicdomain/zero/1.0/" target="_blank" rel="license noopener noreferrer" style="display:inline-block;">CC0 1.0</a> unless otherwise specified. No copyright should be owned unless the content is quoted from other sources.</p>
  <p>When you do not create the content by yourself, you should state the source's license and/or copyright status, otherwise it will be distributed under <a href="https://creativecommons.org/publicdomain/zero/1.0/" target="_blank" rel="license noopener noreferrer" style="display:inline-block;">CC0 1.0</a>. Intranet is not responsible for copyright infringement.</p>
  <br>
  <p><b>2. User-owned content</b></p>
  <p>User-owned content refers to the content which is only editable by a specific user or group of users. The name(s) of its creator(s) will be clearly shown near the content. For instance, Blog, Mail, Chat, and your user profile picture.</p>
  <p>User-owned content's copyrights are owned by the posting and editing user(s) and it's shareable under <a href="http://creativecommons.org/licenses/by-sa/4.0/?ref=chooser-v1" target="_blank" rel="license noopener noreferrer" style="display:inline-block;">CC BY-SA 4.0</a> unless otherwise specified. This means, if you don't state the license of your content, <a href="http://creativecommons.org/licenses/by-sa/4.0/?ref=chooser-v1" target="_blank" rel="license noopener noreferrer" style="display:inline-block;">CC BY-SA 4.0</a> will be automatically applied.</p>
  <p>You should make sure the edits you've made do not contain materials that cannot be shared under <a href="http://creativecommons.org/licenses/by-sa/4.0/?ref=chooser-v1" target="_blank" rel="license noopener noreferrer" style="display:inline-block;">CC BY-SA 4.0</a> or your own stated license. Otherwise you may be accused of offending others' copyrights.</p>
  <br>
  <p><b>5. Amendment of copyright policy</b></p>
  <p>The copyright policy of this website will be revised at any time in response to needs, and the revised terms will be published on the website.</p>
  <br>
  <p>Last updated on 2024-09-29.</p>
  <?= $current->getFooter() ?>
</body>

</html>