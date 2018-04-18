<!DOCTYPE html>
<html>
<head>
  <meta charset='utf-8'>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="author" content="Håkan K Arnoldson">
  <title>Arnoldson family</title>
  <meta name="description" content="Domain for the Arnoldson family.">
  <meta name="robots" content="noindex, follow">

  <meta name="DC.date" content="2017-02-14" />

  <link rel="stylesheet" href="dependencies/normalize-5.0.0/normalize.min.css" />
  <link rel="stylesheet" href="dependencies/font-awesome-4.7.0/css/font-awesome.min.css" />
  <link rel="stylesheet" href="css/main.min.css" />

  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />

  <script src='https://www.google.com/recaptcha/api.js'></script>
</head>
<body>
  <header class="center">
    <h2>Welcome to</h2>
    <h1>The Arnoldson family</h1>
  </header>

  <figure class="center" id="main-photo">
    <img src="img/main.jpg" alt="Family photo" />
  </figure>

  <section class="center" id="family-members">
    <figure class="cols">
      <figcaption>
        <h3>Håkan</h3>
        <ul class="social-list">
          <li><a href="https://www.facebook.com/arnoldson" target="_blank" aria-label="To Håkan's Facebook"><i class="fa fa-facebook-square fa-lg" aria-hidden="true" title="Facebook"></i></a></li>
          <li><a href="https://twitter.com/hkarnoldson" target="_blank" aria-label="To Håkan's Twitter"><i class="fa fa-twitter-square fa-lg" aria-hidden="true" title="Twitter"></i></a></li>
        </ul>
        <ul class="social-list">
          <li><a href="https://arnoldson.online" target="_blank" aria-label="To Håkan's Portfolio"><i class="fa fa-folder-open fa-lg" aria-hidden="true" title="Portfolio"></i></a></li>
          <li><a href="https://github.com/hkarn" target="_blank" aria-label="To Håkan's GitHub"><i class="fa fa-github-square fa-lg" aria-hidden="true" title="GitHub"></i></a></li>
          <li><a href="https://www.linkedin.com/in/arnoldson/" target="_blank" aria-label="To Håkan's LinkedIn" id="linkedin-link"><i class="fa fa-linkedin-square fa-lg" aria-hidden="true" title="LinkedIn"></i></a></li>
        </ul>
      </figcaption>
    </figure>
    <figure class="cols">
      <figcaption>
        <h3>Alisa</h3>
        <ul class="social-list">
          <li><a href="https://www.facebook.com/alice.shlykova" target="_blank" aria-label="To Alisa's Facebook"><i class="fa fa-facebook-square fa-lg" aria-hidden="true" title="Facebook"></i></a></li>
        </ul>
      </figcaption>
    </figure>
  </section>

  <section class="contact">
    <h3 class="center">Contact us</h3>
    <?php
      date_default_timezone_set('Europe/Stockholm');
      include 'php/spamcheckDBvariable.php';
      include 'includes/contact-form.php';
    ?>
    <p class="center"><a href="https://arnoldson.net/files/keys/hka.asc" target="_blank">PGP/GPG key</a></p>
  </section>

  <footer class="center">
    <time itemprop="startDate" datetime="2017-02-14" pubdate="true"></time>
    Licensed under <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank"><i class="fa fa-creative-commons fa-1" aria-hidden="true"></i></a> &amp; <a href="https://github.com/hkarn/family-contact-site">MIT</a> by Håkan Arnoldson. Hosted on Iceland by <a href="https://1984hosting.com/" target="_blank">1984 ehf</a>.
  </footer>

  <script src="dependencies/jquery-3.1.1/js/jquery-3.1.1.js"></script>
  <script type="text/javascript">
  var lang = window.navigator.languages ? window.navigator.languages[0] : null;
    lang = lang || window.navigator.language || window.navigator.browserLanguage || window.navigator.userLanguage;
  if (lang.indexOf('-') !== -1)
    lang = lang.split('-')[0];
  if (lang.indexOf('_') !== -1)
    lang = lang.split('_')[0];

  var link = document.getElementById('linkedin-link');
  if (lang == "sv")
    link.href = "https://www.linkedin.com/in/arnoldson/sv";
  </script>
  <script src="js/contact.js"></script>
</body>
