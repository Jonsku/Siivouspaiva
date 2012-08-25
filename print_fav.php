<?php
ini_set("session.save_path",$_SERVER['DOCUMENT_ROOT']."/session/");
session_start();

//parse config
$config = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/siivouspaiva.ini", true);

if(!isset($_SESSION['uid']) || $_SESSION['uid']<0){
  header( 'Location: http://'.$config['server']['server_name'] ) ;
  exit();
}

include($_SERVER['DOCUMENT_ROOT']."/db.php");

try{
    $db = new PDO('sqlite:'.dirname(__FILE__).'/db/siivous.db');
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
    $favorites = db_getFavorites($db, trim($_SESSION['uid']));
    $db = NULL;
}catch (PDOException $e) {
    echo "'Db error: ".$e->getMessage()."'";
    $db = NULL;
    exit();
}
//string localization
require_once($_SERVER['DOCUMENT_ROOT']."/l10n.php");
?>
<!DOCTYPE html>
<html lang="fi">
  <head>
    <meta charset="UTF-8">
    <title>Siivouspäivä - <? echo $config['next_siivoupaiva']; ?></title>
    <style>
    body{
      margin:0;
      padding:0;
      font-size:14px;
    }
    
    td{
      border-bottom:1px solid #000000;
      margin-top:10px;
      padding:20px 5px 5px 5px;
    }
    
    
    h1 { text-align: center; }
    </style>
  </head>

  <body>
      <?php if(sizeof($favorites) > 0){ ?>
      <table>
        <tbody>
          <?php foreach($favorites as $key=>$fav){ ?>
            <tr>
            <td><?php echo $fav['name']; ?></td>
            <td><?php echo str_pad($fav['start_hour'],2,'0',STR_PAD_LEFT).':'.str_pad($fav['start_minute'],2,'0',STR_PAD_LEFT).' - '.str_pad($fav['end_hour'],2,'0',STR_PAD_LEFT).':'.str_pad($fav['end_minute'],2,'0',STR_PAD_LEFT); ?></td>
            <td><?php echo $fav['address']; ?></td>
            <td><pre><?php echo $fav['description']; ?></pre></td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
      <?php }else{ ?>
        <h1><?php s(); ?>Sinulla ei ole vielä myyntipaikkoja suosikeissasi.<?php e(); ?></h1>
      <?php } ?>
  </body>
</html>
