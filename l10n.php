<?php

$localeToLanguage = array("fi_FI"=>"Suomeksi", "en_EN"=>"In English");

if(!isset($_SESSION['locale'])){
    $_SESSION['locale'] = "fi_FI";
}

if( isset($_GET['locale']) ){
    $_SESSION['locale'] = trim($_GET['locale']);
}else if( isset($_POST['locale']) ){
    $_SESSION['locale'] = trim($_POST['locale']);
}else{
    $bLoc = getBrowserLocale();
    if( isset($localeToLanguage[$bLoc]) ){
        $_SESSION['locale'] = $bLoc;
    }
}

//force to Finnish
$_SESSION['locale'] = "fi_FI";
/*
echo "<!-- ".$_SESSION['locale']."-->";
echo "<!-- ".getBrowserLocale()."-->";
*/
$stringDefaultLocale = "fi_FI";

function outCallback($string){
    global $stringDefaultLocale;
    $bt = debug_backtrace();
    $callingFile = basename($bt[2]['file']);
    //$callingLine = $bt[2]['line'];
    try {
        $db = new PDO('sqlite:'.dirname(__FILE__).'/db/l10n.db');
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        if( !addIfNotExists($db, $string, $callingFile, $stringDefaultLocale) ){
            $string = getTranslation($db, $string, $_SESSION['locale']);
        }
        $db = NULL;
    }catch (PDOException $e) {
        echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
        $db = NULL;
    }
    return  $string;
}

/* Called to translate in files */
function s($defaultLocale = "fi_FI"){
    global $stringDefaultLocale;
    
    $oHandlers = ob_list_handlers();
    if(sizeof($oHandlers) > 1 && $oHandlers[1] != "saveCache"){
        //buffer but don't do anything
        ob_start();
        return;
    }
    $stringDefaultLocale = $defaultLocale;
    //start buffering
    ob_start("outCallback");
}

function e(){
    //stop buffering and flush
    ob_end_flush();
}

/* SET AND RETRIEVE TRANSLATION INFORMATION */
function getLangageCode(){
    list($lang, $country) = explode("_", $_SESSION['locale'], 2);
    return $lang;
}

function getCountryCode(){
    list($lang, $country) = explode("_", $_SESSION['locale'], 2);
    return $country;
}

function getBrowserLocale(){
    $bLocale = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
    $bLocale = $bLocale[0];
    //turn it to a locale if only langage is provided
    if( strpos($bLocale,"-") === FALSE ){
        $bLocale .= "_".strtoupper($bLocale);
    }else{
        $bLocale = str_replace("-","_",$bLocale);
    }
    return $bLocale;
}

function addIfNotExists($db, $string, $file, $locale = 'fi_FI'){
    $hash = md5($string);
    $sql = "SELECT COUNT(*) FROM strings WHERE hash = ?;";
    $stmt = $db->prepare($sql);
    $stmt->execute(array($hash));
    if($stmt->fetchColumn() == 0){
        //save the string
        $sql = "INSERT INTO strings (hash,value) VALUES (:hash, :value);";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':hash', $hash);
        $stmt->bindParam(':value', $string);
        $stmt->execute();
        //create translation for default locale
        $sql = "INSERT INTO translations (locale, string, translation) VALUES (:locale, :hash, :value);";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':locale', $locale);
        $stmt->bindParam(':hash', $hash);
        $stmt->bindParam(':value', $string);
        $stmt->execute();
        //save reference to calling file
        $sql = "INSERT INTO files (filePath, string) VALUES (:file, :hash);";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':file', $file);
        $stmt->bindParam(':hash', $hash);
        $stmt->execute();
        return true;
    }else{
        //check if the reference to the file exists
        $sql = "SELECT COUNT(*) FROM files WHERE filePath = ? AND string = ?;";
        $stmt = $db->prepare($sql);
        $stmt->execute(array($file, $hash));
        if($stmt->fetchColumn() == 0){
            //save reference to calling file
            $sql = "INSERT INTO files (filePath, string) VALUES (:file, :hash);";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':file', $file);
            $stmt->bindParam(':hash', $hash);
            $stmt->execute();
        }
    }
    return false;
}

function getTranslation($db, $string, $locale){
    $hash = md5($string);
    $sql = "SELECT translation FROM translations WHERE locale = ? AND string = ?;";
    $stmt = $db->prepare($sql);
    $stmt->execute(array($locale, $hash));
    $translated = $stmt->fetchColumn();
    return $translated != "" ? $translated : $string;
}

function getFiles($db){
    $sql = "SELECT DISTINCT(filePath) FROM files";
    $stmt = $db->prepare($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute();
    $files = array();
    while($row = $stmt->fetch()){ $files[] = $row['filePath']; }
    return $files;
}

function getMissingTranslationForLocale($db, $locale){
    $sql = "SELECT strings.hash AS hash, strings.value AS value FROM strings WHERE strings.hash NOT IN (SELECT translations.string FROM translations WHERE locale = ?);";
    $stmt = $db->prepare($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute(array($locale));
    $strings = array();
    while($row = $stmt->fetch()){ $strings[] = $row; }
    return $strings;
}

function getStringsForFile($db, $fileName, $locale){
    $sql = "SELECT strings.hash AS hash, strings.value AS value, translations.translation AS locale_translation FROM files, strings LEFT JOIN translations ON  translations.string = strings.hash AND translations.locale = :locale WHERE  strings.hash = files.string AND filePath = :fileName;";
    $stmt = $db->prepare($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->bindParam(':fileName', $fileName);
    $stmt->bindParam(':locale', $locale);
    $stmt->execute();
    $strings = array();
    while($row = $stmt->fetch()){ $strings[] = $row; }
    return $strings;
}

function saveTranslation($db, $string, $translation, $locale){
    $sql = "SELECT COUNT(*) FROM translations WHERE string = :hash AND locale = :locale;";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':hash', $string);
    $stmt->bindParam(':locale', $locale);
    $stmt->execute();
    
    if($stmt->fetchColumn() == 0){
        //create a new translation
        $sql = "INSERT INTO translations (locale, string, translation) VALUES (:locale, :hash, :value);";
    }else{
        //update translation
        $sql = "UPDATE translations SET translation = :value WHERE string = :hash AND locale = :locale;";
    }
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':locale', $locale);
    $stmt->bindParam(':hash', $string);
    $stmt->bindParam(':value', $translation);
    $stmt->execute();
}

/* Cache */
function loadCache(){
    global $navigation;
    global $page;
  
    if( file_exists( $_SERVER['DOCUMENT_ROOT']."/cache/".$_SESSION['locale']."_".$navigation[$page] ) === FALSE){
        //no cache
        return false;
    }
    //load cached version instead
    include_once($_SERVER['DOCUMENT_ROOT']."/cache/".$_SESSION['locale']."_".$navigation[$page]);
    return true;
}

/* Queries handlers */
if(sizeof($_POST) < 1){
    return;
}

ini_set("session.save_path",$_SERVER['DOCUMENT_ROOT']."/session/");
session_start();
if(!isset($_GET["l10n"]) || !isset($_SESSION['admin']) || $_SESSION['admin']<0){
    echo "no-admin";
    return;
}

$query = trim($_GET["l10n"]);

//Json headers
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json; charset=utf-8');

try {
        $db = new PDO('sqlite:'.dirname(__FILE__).'/db/l10n.db');
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        switch($query){
            case "all_files":
                echo json_encode( array( "files"=>getFiles($db) ) );
                break;
            case "fileStrings":
                echo json_encode( array( "strings"=>getStringsForFile($db, $_POST['fileName'], isset($_POST['locale']) ? $_POST['locale'] : "fi_FI" ) ) );
                break;
            case "missingTranslations":
                echo json_encode( array( "strings"=>getMissingTranslationForLocale($db, isset($_POST['locale']) ? $_POST['locale'] : "fi_FI" ) ) );
                break;
            case "saveTranslation":
                saveTranslation($db, $_POST['string'], $_POST['translation'], $_POST['locale']);
                break;
        }    
}catch (PDOException $e) {
    echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
}
$db = NULL;
?>