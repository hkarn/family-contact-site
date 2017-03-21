<?php

//Requires spamcheckDBvariable.php to be included before, or recaptha key to be intered below.
require __DIR__ . '/../php/spamcheckDBvariable.php';



$session_name = '_FORMSSID';   // Set a custom session name
session_name($session_name);
ini_set('session.use_only_cookies', 1);
$currentCookieParams = session_get_cookie_params();
if (!empty($_SERVER['HTTPS'])) $secure = TRUE; else $secure = FALSE;
session_set_cookie_params($currentCookieParams["lifetime"],
    $currentCookieParams["path"],
    $currentCookieParams["domain"],
    $secure,
    true);
session_start();
session_regenerate_id(true);

$_SESSION['lastrefresh'] = $_SERVER['REQUEST_TIME'];

  $token = bin2hex(openssl_random_pseudo_bytes(32));


  if (!isset($_SESSION['from']) OR ($_SESSION['lastrefresh'] <= ($_SERVER['REQUEST_TIME'] - 2400))) {
    //user either dont have a session or been inactive for more then 40 minutes
    $_SESSION['token'] = $token;
    $_SESSION['from'] = $domain;
    $_SESSION['lastcontent'] = "";
    $_SESSION['numberofsends'] = 0;
  } else {
    //token is valid for 3 attempts before a page reload has to be made. prevent button hammering
    if (($_SESSION['token'] != "destroyed") AND ($_SESSION['numberofsends'] <= 3)) {
      $token = $_SESSION['token'];
    }
  }
  $_SESSION['formsenttime'] = $_SERVER['REQUEST_TIME']-50;
?>

<div class="contact-form-container">
  <form id="contact-form" action="php/send-a-contact.php" method="post">
    <input id="form-hidden" name="token" type="hidden" value="<?php echo $token; ?>">
    <input id="form-url" name="url" type="url" value="">
    <fieldset>
      <input id="form-name" name="name" placeholder="Your name" type="text" tabindex="1" required>
    </fieldset>
    <fieldset>
      <input id="form-email" name="email" placeholder="Your e-mail" type="email" tabindex="2" required>
    </fieldset>
    <fieldset>
      <textarea id="form-message" name="message" placeholder="Your message...." tabindex="3" required></textarea>
    </fieldset>
    <fieldset>
      <div class="g-recaptcha" data-sitekey="<?php echo $reCAPTCHAkey_public; ?>"></div>
    </fieldset>
    <fieldset>
      <button name="submit" type="submit" id="contact-submit" data-submit="Sending..." tabindex="4">Send message</button>
      <div id="form-messages"></div>
    </fieldset>
  </form>
</div>
