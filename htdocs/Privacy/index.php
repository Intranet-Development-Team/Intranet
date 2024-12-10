<?php
require("../CoreLibrary/CoreFunctions.php");

$current = new Session("Privacy", "Privacy", USER_LIST, false);
?>
<!DOCTYPE html>
<html>
<?= $current->getHtmlHead() ?>

<body class="container pt-4">
  <header><?= $current->getNavBar() ?></header>
  <div class="text-center mx-5 mt-3 mb-4">
    <h1>Privacy Protection Policy</h1>
    <h5>When you use Intranet, you're trusting us with your information. We understand this is a big responsibility and work hard to protect your information.</h5>
  </div>
  <p>The content of the privacy protection policy tells you how we handle the personal identification data collected when you use Intranet. The privacy protection policy does not apply to related linked websites other than this website, nor does it apply to personnel who are not entrusted or participated in the management of this website.</p>
  <br>
  <p><b>1. Methods of collecting, processing and using your personal data</b></p>
  <p>When you visit Intranet or use the functional services provided by us, we will consider the nature of the service function, ask you to provide the necessary personal data, and process and use your personal data within the scope of the specific purpose. Without your written consent, we will not use personal data for other purposes.</p>
  <p>We may retain your device and personal information such as your name, username, email address, contact information, IP address, and time of use when you use interactive functions.</p>
  <p>During normal browsing, the server may record relevant actions on its own, including but not limited to the IP address of the connected device, time of use, browser used, browsing and click data records, as a reference basis for us to improve our website services. These records are for internal use and will never be published.</p>
  <p>In order to provide accurate services, we may conduct statistics and analysis on the content of the collected questionnaires, and present the statistical data or explanatory text of the analysis results. In addition to internal research, we will publish statistical data and explanatory text as necessary, but does not involve specific personal information.</p>
  <br>
  <p><b>2. Data protection</b></p>
  <p>Our server is equipped with necessary security protection measures to protect the website and your personal data. We also take data security as the first consideration in our development. Strict protection measures are adopted, and only authorized personnel can access your data.</p>
  <p>If it is necessary to entrust other units to provide services, we will also strictly require them to comply with confidentiality obligations and take necessary inspection procedures to ensure that they will indeed comply.</p>
  <br>
  <p><b>3. Policy on sharing personal data with third parties</b></p>
  <p></p>Intranet will never provide, exchange, rent or sell any of your personal information to other individuals, groups, private companies or public agencies, but those with legal basis or contractual obligations are not limited to this.</p>
  <p>The proviso of the preceding paragraph includes but is not limited to:</p>
  <ul>
    <li>With your written consent.</li>
    <li>To avoid danger to your life, body, freedom or property.</li>
  </ul>
  <br>
  <p><b>4. Use of Cookies</b></p>
  <p>Intranet uses cookies to remember your login credential, and some of the functions may need cookies to work, such as the Login system. Without cookies, Intranet cannot work properly.</p>
  <br>
  <p><b>5. Amendment of privacy protection policy</b></p>
  <p>The privacy protection policy of this website will be revised at any time in response to needs, and the revised terms will be published on the website.</p>
  <br>
  <small>Last updated on 2023-09-17.</small>
  <?= $current->getFooter() ?>
</body>

</html>