<?php
require_once("../CoreLibrary/CoreFunctions.php");

$current = new Session("Conduct", "Conduct", USER_LIST, false);
?>
<!DOCTYPE html>
<html>
<?= $current->getHtmlHead() ?>

<body class="container pt-4">
  <header><?= $current->getNavBar() ?></header>
  <div class="text-center mx-5 mt-3 mb-4">
    <h1>Code of Conduct</h1>
    <h5>This Code of Conduct helps us build a community that is rooted in kindness, collaboration, and mutual respect. Everyone who visits Intranet should follow it.</h5>
  </div>
  <div class="d-flex flex-wrap align-items-stretch">
    <div style="padding:2em;border-radius:0.5em;flex:45%;box-sizing:border-box;margin:1em;">
      <h4>Intranet isn't built by us, it's you.</h4>
      We indeed developed Intranet. But your participation is the reason why Intranet is running today. You are the one who makes Intranet alive.
    </div>
    <div style="padding:2em;border-radius:0.5em;flex:45%;box-sizing:border-box;margin:1em;">
      <h4>We are only here to help.</h4>
      We built this Code of Conduct in order to conserve these attitudes and spirits, making Intranet even more beneficial to everyone.
    </div>
    <div class="bg-success-subtle" style="padding:2em;border-radius:0.5em;flex:100%;margin:1em;justify-content:stretch;">
      <h4>Our Expectations</h4>
      <div style="display:flex;flex-wrap:wrap;justify-content:stretch;">
        <div style="margin:1em;flex:45%;">
          <h6>All members contribute to the best of their abilities.</h6>
          Everyone is responsible for benefitting the whole class. A simple act like <a href="/EditAssignments" target="_blank">posting assignments</a> can help a lot. Helping us to improve Intranet is also a way. We also encourage you to <a href="/Support?feedback" target="_blank">report the problems</a> you encountered while using Intranet. We're always glad to listen to your opinion.
        </div>
        <div style="margin:1em;flex:45%;">
          <h6>Always be respectful and inclusive.</h6>
          When you notice someone making a mistake, it is better to offer friendly suggestions and reminders instead of engaging in a fight. We highly encourage open communication, but it is important to maintain politeness when discussing topics with those who hold opposing opinions.
        </div>
      </div>
    </div>
    <div class="bg-danger-subtle" style="padding:2em;border-radius:0.5em;flex:100%;margin:1em;justify-content:stretch;">
      <h4>Unacceptable Behaviour</h4>
      <div style="display:flex;flex-wrap:wrap;justify-content:stretch;">
        <div style="margin:1em;flex:45%;">
          <h6>Spamming</h6>
          Posting or sending content that is targeted to annoy or disturb others, including sending repetitive characters and posting meaningless paragraphs, is not allowed. Direct or indirect commercial advertising is also prohibited.
        </div>
        <div style="margin:1em;flex:45%;">
          <h6>Violence</h6>
          No name-calling, personal attacks, threats or harassment are allowed. This includes wishing or hoping that someone experiences physical harm. Glorification and promotion of violence are also prohibited.
        </div>
        <div style="margin:1em;flex:45%;">
          <h6>Hateful conduct</h6>
          You may not promote violence against, threaten, harass, or isolate other people on the basis of race, opinion, behaviour, interest, belief, gender, or whatever else.
        </div>
        <div style="margin:1em;flex:45%;">
          <h6>Posting sensitive/NSFW content</h6>
          Posting or sending content that is sexually explicit, excessively gory, violent or hateful is not allowed. Exceptions may be made for documentary, educational, artistic, medical, and health content (e.g. Documentary of WWII).
        </div>
        <div style="margin:1em;flex:45%;">
          <h6>Encroachment on privacy</h6>
          You may not publish or post other people's private content without their express authorization and permission. We also prohibit threatening to expose private content or incentivizing others to do so.
        </div>
        <div style="margin:1em;flex:45%;">
          <h6>Encroachment on cyber safety</h6>
          You may not post or send content that is intended to compromise the security of any computer, network, or account, including but not limited to malware, phishing, and pharming.
        </div>
      </div>
    </div>
    <div class="bg-info-subtle" style="padding:2em;border-radius:0.5em;width:100%;margin:1em;justify-content:stretch;">
      <h4>Moderation</h4>
      <p>We believe moderation starts with the community itself, and so, the moderators of Intranet will be elected from the users of Intranet through popular votes.
        We generally expect that moderators:</p>
      <ul>
        <li>are patient and fair</li>
        <li>lead by example</li>
        <li>show respect for their fellow community members in their actions and words</li>
        <li>try their best to help users solve the problems they've met</li>
        <li>are open to some light but firm moderation to keep the community on track and resolve (hopefully) uncommon disputes and exceptions</li>
      </ul>
      <p>Moderators are responsible for:</p>
      <ul>
        <li>handling exceptional conditions that may disrupt the normal operations of Intranet</li>
        <li>ensuring this Code of Conduct has been operated well</li>
      </ul>
      Moderators can be identified by the hexagonal icon <i class="bi bi-hexagon-fill"></i> near their usernames. More info can be found <a href="/Support/?boardofmods" target="_blank">here</a>.
    </div>
    <div style="padding:2em;border-radius:0.5em;flex:100%;box-sizing:border-box;margin:1em;">
      <h4>Thank you!</h4>
      Thank you for reading this Code of Conduct!
    </div>
  </div>
  <p>Last updated on 2023-10-09.</p>
  <?= $current->getFooter() ?>
</body>

</html>