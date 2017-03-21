<?php
header('Content-type: text/html; charset=utf-8');
require __DIR__ . '/spamcheckDBvariable.php';

date_default_timezone_set('Europe/Stockholm');


if (!hammerguard('mysql:host=' . $servername . ';dbname=' . $dbname . ';charset=utf8mb4', $username, $password)) {

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

  /*
  After the IP spam check this form uses various checks on session variables to try and minimize abuse.
  */

  if ($_SESSION['formsenttime'] > ($_SERVER['REQUEST_TIME'] - 30)) {
    //no more then every 30 sec
    http_response_code(403);
    echo "Please wait a little bit longer before using the form again.";
    exit;
  }

  if ($_SESSION['from'] !== $domain) {
    //check hidden fixed session reference
    //this will refuse requests from people that never visited our site
    //(there is a random token also checked after loading the form)
    http_response_code(403);
    echo "403 - Unauthorized request.";
    exit;
  }

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form fields and remove whitespace and strip tags. There is no reason there should be tags here.
    $name = html_entity_decode(filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $message = html_entity_decode(filter_var(trim($_POST['message']), FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES));
    //variables are recoded after tags have bin stripped. this is so we can do sanitation on output on expected string

    $email_content = sha1($name . $email . $message); //Used for duplicate send check


    if ( empty($name) OR empty($message) OR !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      // Set a 400 (bad request) response code and exit.
      http_response_code(403);
      echo "Make sure you filled all fields and used a valid e-mail. Try again.";
      exit;
    }

    if ($_POST['url'] != "") {
      //A bot has tried to enter something in the after-load hidden URL field
      http_response_code(403);
      echo "Refused by spamfilter. Try another method of contact.";
      exit;
    };

    //RECAPTA START Check I am not a robot recaptcha
    $post_data = http_build_query(
      array(
        'secret' => $reCAPTCHAkey_secret,
        'response' => $_POST['g-recaptcha-response'],
        'remoteip' => $_SERVER['REMOTE_ADDR']
      )
    );
    $opts = array('http' =>
      array(
        'method'  => 'POST',
        'header'  => 'Content-type: application/x-www-form-urlencoded',
        'content' => $post_data
      )
    );
    $context  = stream_context_create($opts);
    $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
    $result = json_decode($response);
    if (!$result->success) {
      echo "reCAPTCHA verification failed, please try contacting us thru social media instead.";
      http_response_code(403);
      exit;
    }
    //RECAPTCA END

    if ($_SESSION['lastcontent'] == $email_content) {
      //sent same content twice
      http_response_code(403);
      echo "It appears you already sent this data. Try again.";
      exit;
    }


    if ($_SESSION['token'] !== $_POST['token']) {
      //sent token dont match session token. probably session token destroyed because of too many attempts
      http_response_code(403);
      echo "Maximum submissions passed. Please try again in a few hours or use a different contact method.";
      exit;
    };


    $m = new PHPMailer;

    $m->isSMTP();
    $m->CharSet = 'UTF-8';
    $m->SMTPAuth = true;
    //$m->SMTPDebug = 2;
    //$mail->Debugoutput = 'html';
    $m->Host = 'mail.1984.is';
    $m->Username = $mailfrom;
    $m->Password = $smtpout;
    $m->SMTPSecure = 'tls';
    $m->Port = 587;
    $m->isHTML(true);
    $m->Subject = 'Contact form from' . htmlentities($name);
    $m->msgHTML(htmlspecialchars($message));
    $m->AltBody = htmlspecialchars($message);
    $m->setFrom($mailfrom, 'Form @ Arnoldson.net');
    $m->AddReplyTo(htmlentities($email), htmlentities($name));
    $m->AddAddress($mailto, 'Håkan Arnoldson');

    if ($m->send()) {
      //on success
      $_SESSION['formsenttime'] = $_SERVER['REQUEST_TIME'];
      $_SESSION['lastcontent'] = $email_content;
      //give the user two chances to resend on mistakes before locking the token
      $_SESSION['numberofsends']++;
      if ($_SESSION['numberofsends'] >= 3) {
        $_SESSION['token'] = "destroyed";
      };

      http_response_code(200);
      echo "Thank you! We will answer shortly.";
      exit;
    } else {
      http_response_code(500);
      echo "The server has died. Try contacting me some other way.";
    }

  } else {
    //not POST
    http_response_code(403);
    echo "403 - Forbidden request.";
    exit;
  }

} else {
  http_response_code(403);
  echo "Too many attempts. Try again in a few hours or contact us on social media";
  exit;
}

//FUNCTIONS
function hammerguard($mysqlstring, $username, $password) {


  /**
  * Set to 20 tries in last 5 hours
  */
  try {
    $pdo = new PDO($mysqlstring, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //Error throw exceptions, catch with code.
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); //Not compatible with all drives, defaults to false if not supported. Prepare each statement instead.
  } catch (PDOException $e) {
    print "Database Error!<br/>";
    return false;
    die();
  }

  /*RUNONCE START
  try {
  $table = 'hammerguard_fam';
  $sql = "CREATE TABLE " . $table . " (
    iphash CHAR(64),
    time BIGINT);";
  $sth = $pdo->prepare($sql);
  $sth->execute();
  echo "Table: " . $table . " created succesfully.<br>";
} catch(PDOException $e) {
  echo $sql . "<br>" . $e->getMessage() . "<br>";
}

  END RUNONCE*/

  $ip = hash('sha256',$_SERVER['REMOTE_ADDR']);
  $time = $_SERVER['REQUEST_TIME'];
  $timelimit = $time-18001;
  try {
    $sql = "DELETE FROM hammerguard_fam WHERE time < :timelimit;";
    $sth = $pdo->prepare($sql);
    $sth->bindParam(':timelimit', $timelimit);
    $sth->execute();
  } catch(PDOException $e) {
    echo "Databasfel från hammerguard():<br>";
    echo $sql . "<br>" . $e->getMessage();
    $pdo = NULL;
    return false;
    exit;
  }
  $thecount = 0;
  try {
    $sql = "SELECT count(*) FROM hammerguard_fam WHERE iphash = :ip;";
    $sth = $pdo->prepare($sql);
    $sth->bindParam(':ip', $ip);
    $sth->execute();
    $count = $sth->fetch(PDO::FETCH_NUM);
    $thecount = reset($count);
  } catch(PDOException $e) {
    echo "Databasfel från hammerguard():<br>";
    echo $sql . "<br>" . $e->getMessage();
    $pdo = NULL;
    return false;
    exit;
  }
  if ($thecount < 21) {
    try {
      $sql = "INSERT INTO hammerguard_fam (
        iphash,
        time)
        VALUES (
        :ip,
        :time);";
      $sth = $pdo->prepare($sql);
      $sth->bindParam(':ip', $ip);
      $sth->bindParam(':time', $time);
      $sth->execute();
    } catch(PDOException $e) {
      echo "Databasfel från hammerguard():<br>";
      echo $sql . "<br>" . $e->getMessage();
      $pdo = NULL;
      return false;
      exit;
    }
      return false;
  } else {
    return true;
  }
}
