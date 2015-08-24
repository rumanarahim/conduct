<?php
//DB configuration
$dbhost 	= "localhost";
$dbname		= "conduct";
$dbuser		= "root";
$dbpass		= "";

// database connection
try 
{
    $conn = new PDO("mysql:host=$dbhost;dbname=$dbname",$dbuser,$dbpass);
}
catch (PDOException $e)
{
    echo "Connection failed: ".$e->getMessage();
}

//DATE TIME CONMF
date_default_timezone_set('Australia/Melbourne');

//EMAIL
require 'PHPMailer-master/PHPMailerAutoload.php';

$mail = new PHPMailer;

//$mail->SMTPDebug = 3;                               // Enable verbose debug output

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'username@gmail.com';                 // SMTP username
$mail->Password = 'xxxxxx';                           // SMTP password
$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 587;                                    // TCP port to connect to
$mail->From = 'system@contacthq.com';
$mail->FromName = 'Contact HQ Mailer';
$mail->isHTML(true);                                  // Set email format to HTML

?>