<?php
/* Validate */
if(!isset($_POST["subject"]) || !isset($_POST["message"]) || !isset($_POST["email"])){
 echo json_encode(array("error"=>"incomplete query."));
 exit();
}

foreach($_POST as $key => $b){
    $_POST[$key] = urldecode($_POST[$key]);
}

if(trim($_POST["email"]) == ""){
   echo json_encode(array("error"=>"No email address specified.")); 
}
$from = trim($_POST["email"]);

if(trim($_POST["message"]) == ""){
   echo json_encode(array("error"=>"Empty message.")); 
}
$message = trim($_POST["message"]);

$subject = trim($_POST["subject"]);

$to      = 'siivouspaiva@siivouspaiva.com';
$headers = 'From: siivouspaiva@siivouspaiva.com' . "\r\n" .
    'Reply-To: '.$from."\r\n" .
    'X-Mailer: PHP/' . phpversion();

mail($to, $subject, $message, $headers);
echo json_encode(array("success"=>"Message sent."));
?>