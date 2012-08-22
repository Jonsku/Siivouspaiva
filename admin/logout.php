<?php
ini_set("session.save_path",$_SERVER['DOCUMENT_ROOT']."/session/");
session_start();
$_SESSION['admin'] = 0;
unset($_SESSION['admin']);
//$config = parse_ini_file("siivouspaiva.ini", true);
//redirect to homepage
header('Location: ../');
//echo $config['paths']['base_url'];
?>