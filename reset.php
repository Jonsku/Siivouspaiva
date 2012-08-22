<?php
ini_set("session.save_path",$_SERVER['DOCUMENT_ROOT']."/session/");
session_start();
//parse config
$config = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/siivouspaiva.ini", true);

//redirect
if(preg_match("/([^.].\.)?".$config['server']['server_name']."/i", $_SERVER["SERVER_NAME"]) == 0){
   header( 'Location: '.$config['server']['server_name'].$_SERVER["REQUEST_URI"] ) ;
}

$WRONG = -1;
$OUTDATED = 0;
$OK = 1;
include($_SERVER['DOCUMENT_ROOT']."/db.php");

$result = 0;
if(isset($_GET['id']) && strpos($_GET['id'],"_") > 0){
   //account validation
   list($seed,$secret) = split("_", $_GET['id'], 2);
   $now = strftime('%s');
   if( ($now - $seed) > ($config['security']['code_best_before_days'] * 24 * 60 * 60) ){
       $result = $OUTDATED;
   }else{
      try {
         $db = new PDO('sqlite:'.dirname(__FILE__).'/db/siivous.db');
         $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
         $id = intval($secret) ^ ( ( intval($seed) << 2 ) ^ ( intval($seed) >> 2 ) );
         if( db_userExists($db, $id) ){
            $db = NULL;
            $result = $OK;
            doLogin($id);
         }else{
            $db = NULL;
            header("HTTP/1.0 404 Not Found");
            exit();
         }
      }catch (PDOException $e) {
         echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
         $db = NULL;
         exit();
      } 
   }
}else{
   header("HTTP/1.0 404 Not Found");
   exit();
}
?>
<!DOCTYPE html>
<html lang="fi">
  <head>
    <meta charset="UTF-8">
    <title>Siivouspäivä - Email login</title>
  </head>
  <body>
<?php
   if($result == $OUTDATED){
?>
   <p>Unfortunately this link is outdated. You can either request a new one to be sent to the same email address, OR, loggin using the form on the map page.</p>
   <p><a href="<?php echo $config['paths']['base_url'] ?>/">Request a new email login link</a> <a href="<?php echo $config['paths']['base_url'] ?>/myyntipaikat.php">Login from the map page</a></p>
<?php
   }else if($result == $OK){
?>
         <p>You are now logged in.</p>
         <p>Please use the form below to change you password.</p>
         <form method="POST" action="<?php echo $config['paths']['base_url']; ?>/data.php?query=setpass">
            <fieldset>
               <label for="password">Password</label><input type="password" name="password" id="password" class="required" minlength="6"/>
               <label for="password_verify">Re-type password</label><input type="password" name="password_verify" id="password_verify" class="required"/>
               <input type="submit">Submit</input>
            </fieldset>
         </form>
         <p><a href="<?php echo $config['paths']['base_url'] ?>/myyntipaikat.php">Click to show the map</a></p>

<?php
   }
?>
  </body>
</html>
