<?php
ini_set("session.save_path",$_SERVER['DOCUMENT_ROOT']."/session/");
session_start();
include($_SERVER['DOCUMENT_ROOT']."/db.php");

 


/* ############### */
//print_r($_GET);
if(!isset($_GET["query"]) || trim($_GET["query"]) == ""){
    exit();
}

include($_SERVER['DOCUMENT_ROOT']."/log.php");

$query = trim($_GET["query"]);
//handle query
foreach($_POST as $key => $b){
    $_POST[$key] = urldecode($_POST[$key]);
}
//Json headers
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json; charset=utf-8');

//all the queries that need the user to be an admin
switch($query){
    case "adminLoad":
    case "invite":
    case "specials":
    case "revoke":
    case "add_news":
    case "delete_news":
    if(!isAdmin()){
            echo json_encode(array("error"=>"no pasaran"));
            exit();
        }
        break;
    default:break;
}

//all the queries that need the user to be logged in
switch($query){
    case "get":
    case "add":
    case "add_recycle":
    case "delete":
    case "favorites_list":
    case "add_favorite":
    case "delete_favorite":
    case "setpass":
    case "confirm":
        if(!isLogged()){
            echo json_encode(array("error"=>"logged out"));
            exit();
        }
        break;
    default:break;
}

switch($query) {
    /* STANDS */
     case "getAll":
        getAllStands();
        break;
    case "get":
        get();
        break;
    case "load":
        load();
        break;
    case "adminLoad":
        adminLoad();
        break;
    case "add":
        add();
        break;
    case "add_recycle":
        addRecycle();
        break;
    case "delete":
        delete();
        break;
    /* SPECIALS */
    case "mail":
        message();
        break;
    case "invite":
        //$_POST["email"] = "jonathan.cremieux@gmail.com";
        invite();
        break;
    case "confirm":
        confirm();
        break;
    case "specials":
        specials();
        break;
    case "revoke":
        revoke();
        break;
    /* NEWS */
    case "news_list":
        newsList();
        break;
    case "news_items":
        getNewsItem();
        break;
    case "add_news":
        addNewsItem();
        break;
    case "delete_news":
        deleteNewsItem();
        break;
    /* FAVORITES */
    case "favorites_list":
        favoritesList();
        break;
    case "add_favorite":
        addFavorite();
        break;
    case "delete_favorite":
        deleteFavorite();
        break;
    /* LOGIN/REGISTER */
    case "login":
        login();
        break;
    case "logout":
        logout();
        break;
    case "setpass":
        setPass();
        break;
}

?>