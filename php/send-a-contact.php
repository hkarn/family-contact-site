<?php

  include 'spamcheckDBvariable.php';
  /*this file is not included and should contain the following
  //details for your database if you want to use IP based spam protection
  $servername = "localhost";
  $username = "username";
  $password = "password";
  $dbname = "myDB";
  */


  $userip = sha1($_SERVER['REMOTE_ADDR']);
  $timestamp = date('Y-m-d G:i:s');

  $connection = true; //flag to skip further checks if DB error
  $conn = new mysqli($servername, $username, $password, $dbname);
  if ($conn->connect_error) {
    $connection = false;
  };

  /* This code needs to be run once only to initalize your table
  $sql = "CREATE TABLE TheSenders_FAM (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(40) NOT NULL,
    reg_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  )";
  if ($conn->query($sql) === TRUE) {
      echo "Table TheSenders_FAM created successfully";
  } else {
      echo "Error creating table: " . $conn->error;
  }

  end runonce */


  if ($connection == true) {

    $sql = "INSERT INTO TheSenders_FAM (ip, reg_time)
    VALUES ('$userip', '$timestamp')";

    $conn->query($sql);
    $oneweekago = date('Y-m-d H:i:s', strtotime('-1 hours'));


    $sql = "DELETE FROM TheSenders_FAM WHERE reg_time < NOW() - INTERVAL 10 HOUR";
    //Warning UTC time. Make sure your server and the page have the same timezone or switch to UTC.

    $conn->query($sql);


    $sql = "SELECT id FROM TheSenders_FAM WHERE ip = '$userip';";
    $result = $conn->query($sql);
    if ($result->num_rows > 25) {
      http_response_code(403);
      echo "To many requests from this IP. Temporarily blocked!";
      $conn->close();
      exit;
    };
    $conn->close();
  };

  session_start();
  session_regenerate_id();

  /*
  After the IP spam check this form uses various checks on session variables to try and minimize abuse.
  */

  $referer = $_SERVER['HTTP_REFERER'];
  $origin = $_SERVER['HTTP_ORIGIN'];
  $mydomain = 'arnoldson.net';

  if (!strpos($referer, $mydomain) AND !strpos($origin, $mydomain)) {
    //If neither origin or referer. Very simple abuse check and dont want to rely on origin yet (not implimented).
    http_response_code(403);
    echo "403 - External request refused.";
    exit;
    }

  if ($_SESSION['formsenttime'] > (time() - 30)) {
    //no more then every 30 sec
    http_response_code(400);
    echo "Please wait a little bit longer before using the form again.";
    exit;
  }

  if ($_SESSION['from'] !== "arnoldson.net") {
    //check our open reference
    http_response_code(403);
    echo "403 - Unauthorized request.";
    exit;
    }

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form fields and remove whitespace.
    $name = trim($_POST['name']);
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $message = trim($_POST['message']);

    if ( empty($name) OR empty($message) OR !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      // Set a 400 (bad request) response code and exit.
      http_response_code(400);
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
        'secret' => '6LcBg9wSAAAAADpS6eyj9mZdZPhyGCO9FDQK4Vmc',
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
      exit;
    }

//RECAPTCA END

    $recipient = "form1@arnoldson.net";
    $subject = "Arnoldson.net from " . $name;
    $email_content = "Name: " . $name . "\r\n";
    $email_content .= "Email: " . $email . "\r\n\r\n";
    $email_content .= "Message:\r\n" . $message . "\r\n";

    if ($_SESSION['lastcontent'] == $email_content) {
      //sent same content twice
      http_response_code(400);
      echo "It appears you already sent this data. Try again.";
      exit;
    }


    if ($_SESSION['token'] != $_POST['token']) {
      //sent token dont match session token. probably session token destroyed because of too many attempts
      http_response_code(400);
      echo "Maximum submissions passed. Please try again in a few hours or use a different contact method.";
      exit;
    };

    $email_headers = "From: Arnoldson.net <formmailer@arnoldson.net>\r\n";
    $email_headers .= "Reply-To: " . $name . "<" . $email . ">\r\n";
    $email_headers .= "Content-Type: text; charset=utf-8\r\n";
    $email_headers .= "Return-Path: <form1@arnoldson.net>\r\n";
    $email_headers .= "Errors-To: <form1@arnoldson.net>\r\n";

    if (mail($recipient, $subject, $email_content, $email_headers)) {

      //on success
      $_SESSION['formsenttime'] = time();
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



?>
