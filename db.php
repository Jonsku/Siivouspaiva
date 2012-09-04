<?php

//parse config
$config = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/siivouspaiva.ini", true);
date_default_timezone_set ( $config['server']['timezone'] );

$floatfix = 0.0000000000001;

 

/* STANDS */
function db_getCityForStand($db, $id){
    $sql = 'SELECT (SELECT cities.name FROM cities WHERE stands.u BETWEEN cities.u_min AND cities.u_max AND stands.v BETWEEN cities.v_min AND cities.v_max) as city FROM stands WHERE stand.id = ?;';
    $stmt = $db->prepare($sql);
    $stmt->execute(array($id));
    $cityName = $standCount = $stmt->fetchColumn();
    return $cityName;
}

function db_getCitiesInBounds($db, $um, $uM, $vm, $vM){
    global $floatfix;
    $sql = 'SELECT cities.name as city, cities.u_min as u_min, cities.u_max as u_max, cities.v_min as v_min, cities.v_max as v_max, count(stands.id) as count FROM cities INNER JOIN stands ON (stands.u BETWEEN cities.u_min AND cities.u_max) AND (stands.v BETWEEN cities.v_min AND cities.v_max) WHERE NOT(cities.u_min > :uM + :fix OR cities.u_max < :um - :fix OR cities.v_min > :vM + :fix OR cities.v_max < :vm - :fix) GROUP BY cities.name';
    $stmt = $db->prepare($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->bindParam(':uM', $uM);
    $stmt->bindParam(':um', $um);
    $stmt->bindParam(':vM', $vM);
    $stmt->bindParam(':vm', $vm);
    $stmt->bindParam(':fix',$floatfix);
    $stmt->execute();
    $cities = array();
    while($row = $stmt->fetch()){ $cities[] = $row; }
    return $cities;
}

function db_getCitiesWithStandsCount($db){
    $sql = 'SELECT cities.name as city, cities.u_min as u_min, cities.u_max as u_max, cities.v_min as v_min, cities.v_max as v_max, count(stands.id) as count FROM cities INNER JOIN stands ON (stands.u BETWEEN cities.u_min AND cities.u_max) AND (stands.v BETWEEN cities.v_min AND cities.v_max) GROUP BY cities.name;';
    $stmt = $db->prepare($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute();
    $cities = array();
    while($row = $stmt->fetch()){ $cities[] = $row; }
    return $cities;
}

function db_updateCityBounds($db, $city, $u, $v){
    global $floatfix;
    $stmt = $db->prepare('SELECT * from cities WHERE name LIKE :city;');
    $stmt->bindParam(':city',$city);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute();
    if(!$row = $stmt->fetch()) { //create the city
        $stmt = $db->prepare("INSERT INTO cities (name, u_min, u_max, v_min, v_max) VALUES (:city, ROUND(:u,6) - :fix, ROUND(:u,6) + :fix, ROUND(:v,6) - :fix, ROUND(:v,6) + :fix);");
        $stmt->bindParam(':u',$u);
        $stmt->bindParam(':v',$v);
        $stmt->bindParam(':fix',$floatfix);
        $stmt->bindParam(':city',$city);
        $stmt->execute();
        return true;
    }
    if( !( ($row["u_min"] > $u || $row["u_max"] < $u) || ($row["v_min"] > $v ||  $row["v_max"] < $v) ) ){
        return false;
    }
    $toUpdate = array();
    $setU = false;
    $setV = false;
    if($row["u_min"] > $u){ $toUpdate[] = "u_min = ROUND(:u,6) - :fix"; $setU = true; }
    if($row["u_max"] < $u){ $toUpdate[] = "u_max = ROUND(:u,6) + :fix"; $setU = true; }
    if($row["v_min"] > $v){ $toUpdate[] = "v_min = ROUND(:v,6) - :fix"; $setV = true; }
    if($row["v_max"] < $v){ $toUpdate[] = "v_max = ROUND(:v,6) + :fix"; $setV = true; }

    $sql = "UPDATE cities SET ";
    $arSize = count($toUpdate);
    for($i = 0;$i<$arSize;$i++){
        $sql .= $toUpdate[$i];
        if($i < $arSize-1){ $sql .= ", "; }
    }
    $sql .= " WHERE name LIKE :city;";
    $stmt = $db->prepare($sql);
    if($setU){ $stmt->bindParam(':u',$u); }
    if($setV){ $stmt->bindParam(':v',$v); }
    $stmt->bindParam(':fix',$floatfix);
    $stmt->bindParam(':city',$city);
    $stmt->execute();
    return true;
}

function db_standExists($db, $id){
    $sql = "SELECT count(*) FROM stands WHERE stands.id = :id;";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}

function db_ownerHasStand($db, $user_id){
    $sql = "SELECT count(*) FROM stands WHERE stands.owner = :user_id;";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}

function db_getAllStands($db){
    $sql = "SELECT *, (SELECT cities.name FROM cities WHERE stands.u BETWEEN cities.u_min AND cities.u_max AND stands.v BETWEEN cities.v_min AND cities.v_max) as city FROM stands ORDER BY timestamp;";
    $stmt = $db->prepare($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute();
    $stands = array();
    while($row = $stmt->fetch()){ $stands[] = $row; }
    return $stands;
}

function db_getStand($db, $id){
    $sql = "SELECT *, (SELECT cities.name FROM cities WHERE stands.u BETWEEN cities.u_min AND cities.u_max AND stands.v BETWEEN cities.v_min AND cities.v_max) as city FROM stands WHERE stands.id = :id LIMIT 1;";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute();
    $stands = array();
    return $stmt->fetch();
}

function db_getStands($db, $user_id){
    $sql = "SELECT *, (SELECT cities.name FROM cities WHERE stands.u BETWEEN cities.u_min AND cities.u_max AND stands.v BETWEEN cities.v_min AND cities.v_max) as city FROM stands WHERE stands.owner = :owner";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':owner', $user_id);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute();
    $stands = array();
    while($row = $stmt->fetch()){
        $stands[] = $row;
    }
    return $stands;
}

function db_getAllStandsInfo($db){
    $sql = 'SELECT stands.id id, stands.name name, stands.address address, stands.start_hour start_hour, stands.start_minute start_minute, stands.end_hour end_hour, stands.end_minute end_minute, stands.description description, stands.tags tags, stands.link link, stands.u u, stands.v v, stands.modified modified, owners.fb_id fb_id, owners.email_id email, owners.phone phone, owners.name owner_name FROM stands, owners WHERE (u BETWEEN -90 AND 90) AND (v BETWEEN -180 AND 180) AND owners.id = stands.owner;';
    $stmt = $db->prepare($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute();
    $stands = array();
    while($row = $stmt->fetch()){
        $stands[] = $row;
    }
    return $stands;
}

function db_getStandsInBounds($db, $um, $uM, $vm, $vM, $timestamp = "0", $limit = 0, $offset = 0){
    $sql = 'SELECT id, name, address, start_hour, start_minute, end_hour, end_minute, description, tags, link, u, v, modified FROM stands WHERE (u BETWEEN :um AND :uM) AND (v BETWEEN :vm AND :vM)';
    if($timestamp != "0"){
        $sql .=" AND modified > :modified";  
    }
    if($limit > 0){
            $sql .= ' LIMIT :limit';
            $sql .= ' OFFSET :offset';
    }
    $sql .= ';';
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':uM', $uM);
    $stmt->bindParam(':um', $um);
    $stmt->bindParam(':vM', $vM);
    $stmt->bindParam(':vm', $vm);
    if($timestamp != "0"){
         $stmt->bindParam(':modified', $timestamp);
    }
    if($limit > 0){
        $stmt->bindParam(':limit', $limit);
        $stmt->bindParam(':offset', $offset);
    }
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute();
    $stands = array();
    while($row = $stmt->fetch()){
        $stands[] = $row;
    }
    return $stands;
}

function db_getNumberOfStandsInBounds($db, $um, $uM, $vm, $vM){
    $sql = 'SELECT count(*) FROM stands WHERE u <= :uM AND u >= :um AND v <= :vM AND v >= :vm;';
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':uM', $uM);
    $stmt->bindParam(':um', $um);
    $stmt->bindParam(':vM', $vM);
    $stmt->bindParam(':vm', $vm);
    $stmt->execute();
    return $stmt->fetchColumn();
}

function db_getStandIdOfOwner($db, $user_id){
    if(!db_ownerHasStand($db, $user_id)){
        return 0;
    }
    $sql = "SELECT id FROM stands WHERE owner = ?;";
    $stmt = $db->prepare($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute(array($user_id));
    $row = $stmt->fetch();
    //$lastId = $stmt->fetchColumn();
    return $row['id'];
}


function db_createUpdateStand($db, $user_id, $name, $address, $u, $v, $sT, $eT, $description, $tags, $link, $createRecycle = FALSE){
    $id = $createRecycle === FALSE ? db_getStandIdOfOwner($db, $user_id) : 0;
    if($id > 0){
        $sql = "UPDATE stands SET name = :name, address = :address, u =  ROUND(:u,6), v =  ROUND(:v,6), start_hour = :start_hour, start_minute = :start_minute, end_hour = :end_hour, end_minute = :end_minute, description = :description, tags = :tags, link = :link, modified = strftime('%s','now') WHERE id = :id AND owner = :owner;";
    }else{
        $sql = "INSERT INTO stands (id, owner, name, address, u, v, start_hour, start_minute, end_hour, end_minute, description, tags, link, timestamp, modified) VALUES (NULL, :owner, :name, :address, round(:u,6), round(:v,6), :start_hour, :start_minute, :end_hour, :end_minute, :description, :tags, :link, strftime('%s','now'), strftime('%s','now'));";
        $created = true;
    }
    $stmt = $db->prepare($sql);
    if($id > 0){
        $stmt->bindParam(':id', $id);
    }
    $stmt->bindParam(':owner', $user_id);
    $stmt->bindParam(':name',$name);
    $stmt->bindParam(':address',$address);
    $stmt->bindParam(':u',$u);
    $stmt->bindParam(':v',$v);
    $stmt->bindParam(':start_hour',$sT[0]);
    $stmt->bindParam(':start_minute',$sT[1]);
    $stmt->bindParam(':end_hour',$eT[0]);
    $stmt->bindParam(':end_minute',$eT[1]);
    $stmt->bindParam(':description',$description);
    $stmt->bindParam(':tags',$tags);
    $stmt->bindParam(':link',$link);
    $stmt->execute();
    if($id > 0){
        return $id;
    }else{
        return $db->lastInsertId();
    }
}

function db_deleteStand($db, $user_id, $id){
    /*
    if(!db_standExists($db, $id)){
        return false;
    }
    */
    $sql = "DELETE FROM stands WHERE id = ?";
    $whereParams = array($id);
    //only the stand owner and the admin are allowed to delete a stand
    if(!isset($_SESSION['admin']) || $_SESSION['admin'] != 1){
        $sql .= " AND owner = ?";
        $whereParams[] = $user_id;
    }
    $sql .= ";";
    $stmt = $db->prepare($sql);
    $stmt->execute($whereParams);
    return !db_standExists($db, $id);
}

/* SPECIALS */
function db_createNewSpecialId($db){
    $id = db_generateId(20);
    $sql = 'SELECT count(*) FROM specials WHERE id = :id;';
    do{
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }while($stmt->fetchColumn() > 0);
    return $id;
}

function db_generateId($size){
    $chars = "0123456789azertyuiopqsdfghjklmwxcvbnAZERTYUIOPQSDFGHJKLMWXCVBN";
    $charSize = strlen($chars);
    $id = "";
    for($i = 0; $i<$size; $i++){
        $id .= substr( $chars, rand(0,$charSize-1), 1 );
    }
    return $id;
}

function db_createInvitedSpecial($db, $email){
    $id = db_createNewSpecialId($db);
    $sql = "INSERT INTO specials (id, mail, owner_id, status, timestamp) VALUES (:id, :email, 0, 0, strftime('%s','now'));";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    return $id;
}

function db_isInvitationId($db, $id){
    $sql = 'SELECT count(*) FROM specials WHERE id = :id;';
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}

function db_getEmailFromInvitationId($db, $id){
    $sql = 'SELECT mail FROM specials WHERE id = :id;';
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetchColumn();
}

function db_isSpecialId($db, $id){
    $sql = 'SELECT count(*) FROM specials WHERE owner_id = :owner_id AND status = 1;';
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':owner_id', $id);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}

function db_getSpecials($db){
    $sql = "SELECT * FROM specials;";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $specials = array();
    while($row = $stmt->fetch()){
        $specials[] = $row;
    }
    return $specials;
}

function db_deleteSpecial($db, $id){
    $sql = "DELETE FROM specials WHERE id = :id;";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return true;
}

function db_acceptSpecial($db, $id){
    $sql = "UPDATE specials SET owner_id = :owner_id, status = 1, timestamp = strftime('%s','now') WHERE id = :id;";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':owner_id', $_SESSION['uid']);
    $stmt->execute();
    return true;
}

/* NEWS */
//CREATE TABLE news (id INTEGER PRIMARY_KEY, content BLOB, timestamp TEXT);
function db_newsExist($db,$id){
    $sql = 'SELECT COUNT(*) FROM news WHERE id = ?;';
    $stmt = $db->prepare($sql);
    $stmt->execute(array($id));
    return $stmt->fetchColumn() > 0;
}

function db_createUpdateNews($db,$content,$authors,$title,$id=-1){
    $sql = "INSERT INTO news (content, authors, title, timestamp) VALUES (?, ?, ?, strftime('%s','now'));";
    $params = array($content);
    $params[] = $authors;
    $params[] = $title;
    if($id >= 0 && db_newsExist($db,$id)){
        $sql = "UPDATE news SET content = ?, authors = ?, title = ?, timestamp = strftime('%s','now') WHERE id = ?;";
        $params[] = $id;
    }
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    if($id == -1 || $id == "-1" || $id == "" ){ //get the new id
        
        $stmt = $db->prepare('SELECT last_insert_rowid();');
        $stmt->execute();
        $id = $stmt->fetchColumn();
    }
    return $id;
}

function db_getNews($db,$id){
    $sql = 'SELECT * FROM news WHERE rowid = ?;';
    $stmt = $db->prepare($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute(array($id));
    return $stmt->fetch();
}

function db_getNewsList($db){
    $sql = 'SELECT rowid as id, timestamp, title ,authors FROM news ORDER BY timestamp DESC;';
    $stmt = $db->prepare($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute();
    $list = array();
    while($row = $stmt->fetch()){
        $list[] = $row;
    }
    return $list;
}

function db_deleteNews($db, $id){
    $sql = "DELETE FROM news WHERE rowid = ?;";
    $stmt = $db->prepare($sql);
    $stmt->execute(array($id));
    return true;
}

//FAVORITES
function db_createFavorite($db, $user_id, $stand_id){
    //first check that the favorite doesn't exist
    $sql = 'SELECT COUNT(*) FROM favorites WHERE user_id = ? AND stand_id = ?;';
    $stmt = $db->prepare($sql);
    $stmt->execute(array($user_id, $stand_id));
    if($stmt->fetchColumn() == 0){
        $sql = 'INSERT INTO favorites (user_id, stand_id) VALUES (?, ?);';
        $stmt = $db->prepare($sql);
        $stmt->execute(array($user_id, $stand_id));
    }
    return db_getStand($db, $stand_id);
}

function db_deleteFavorite($db, $user_id, $stand_id){
    $sql = 'DELETE FROM favorites WHERE user_id = ? AND stand_id = ?;';
    $stmt = $db->prepare($sql);
    $stmt->execute(array($user_id, $stand_id));
    return true;
}

function db_getFavorites($db, $user_id){
    $sql = 'SELECT *, (SELECT cities.name FROM cities WHERE stands.u BETWEEN cities.u_min AND cities.u_max AND stands.v BETWEEN cities.v_min AND cities.v_max) as city FROM stands WHERE id IN (SELECT stand_id FROM favorites WHERE user_id = ?) ORDER BY start_hour, start_minute;';
    $stmt = $db->prepare($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute(array($user_id));
    $stmt->execute();
    $stands = array();
    while($row = $stmt->fetch()){
        $stands[] = $row;
    }
    return $stands;
}

// LOGIN/REGISTER
/*
 Check if a user exists and then if the password match (if email is provided).
 Returns array: "result" => 1 if the user exists and password match
                            0 if the user exists but passwords do not match
                            -1 if the user is not in the database
                            -2 if the user is in the database but has not validated his/her email
                "user_id" => if "result" >= 0
*/
function db_checkUser($db, $email, $fb_id = "", $password = ""){
    if($password === "" && $fb_id === ""){
        $password = "0";
    }
    $sql = 'SELECT id, password, status FROM owners WHERE (fb_id = "" AND email_id = ?) OR (fb_id != "" AND fb_id = ?);';
    $stmt = $db->prepare($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute(array($email, $fb_id));
    
    $return = array();
    if( !($row = $stmt->fetch()) ){
        $return["result"] = -1;
    }else if($row['status'] != 1){
        $return["result"] = -2;
    }else if($password != ""){
       /* if(function_exists('customLog'))
            customLog("./logs/passTestLog.txt", "|".json_encode($_POST));*/
        if($password == $row['password']){
            $return["result"] = 1;
        }else{
            $return["result"] = 0;
        }
        $return["user_id"] = $row['id'];
    }else{
        $return["result"] = 1;
        $return["user_id"] = $row['id'];
    }
    return $return;
}


function db_createUser($db, $email, $fb_id, $password, $name, $phone){
    $timestamp = strftime('%s');
    $sql = "INSERT INTO owners (id, email_id, fb_id, password, name, phone, permission_asked, status, timestamp) VALUES (NULL, :email, :fb_id, :password, :name, :phone, 1, 0, :timestamp);";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':fb_id', $fb_id);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':timestamp', $timestamp);
    $stmt->execute();
    return array("id"=>$db->lastInsertId(), "t"=>$timestamp);
}

function db_validateAccount($db, $code){
    global $config;
    //extract id
    list($seed,$secret) = split("_", $code, 2);
    $id = intval($secret) ^ ( ( intval($seed) << 2 ) ^ ( intval($seed) >> 2 ) );
    $sql = "SELECT COUNT(*) as count FROM owners WHERE id = :id AND timestamp = :timestamp;";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':timestamp', $seed);
    $stmt->execute();
    if($stmt->fetchColumn() != 1){
        //invalid validation code
        return -1;
    }
    $now = strftime('%s');
    if( ($now - $seed) > ($config['security']['code_best_before_days'] * 24 * 60 * 60) ){
        //code outdated
        return 0;
    }
    //activate account
    $sql = "UPDATE owners SET status = 1, timestamp = :timestamp WHERE id = :id;";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':timestamp', $now);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    //login
    doLogin( $id );
    return 1;
}

function db_changePassword($db, $id, $password){
    $sql = "UPDATE owners SET password = ? WHERE id = ?;";
    $stmt = $db->prepare($sql);
    $stmt->execute(array($password, $id));
    return;
}

function db_userExists($db, $uid){
    $sql = "SELECT COUNT(*) as count FROM owners WHERE id = ?;";
    $stmt = $db->prepare($sql);
    $stmt->execute(array($uid));
    return ($stmt->fetchColumn() == 1);
}

/* #################### 
    HIGH LEVEL FUNCTIONS
   #################### */

function replacePost($jsonString){
    $tmp = json_decode($jsonString, true);
    foreach($tmp as $key => $b){
        $_POST[$key] = $b;
    }
}

function checkRequired($params){
    $missing = array();
    $empty = array();
    foreach($params as $param => $required){
        if(!isset($_POST[$param])){
            $missing[] = $param;
        }else if($required && trim($_POST[$param]) == ""){
            $empty[] = $param;
        }
    }
    if(sizeof($missing) + sizeof($empty) == 0){
        return 1;
    }else{
        $errMsg = "Incorrect query:\n";
        if( sizeof($missing) > 0 ){
            $errMsg .= "Missing: ".implode(", ",$missing)."\n";
        }
        if( sizeof($empty) > 0 ){
            $errMsg .= "Empty: ".implode(", ",$empty)."\n";
        }
        //$errMsg .= "POST: ".implode(", ",array_keys($_POST))."\n";
        return $errMsg;
    }
}


/* ############### */

/* STANDS */
//get all stands with cities
function getAllStands(){
     try {
        //connect to db
        $db = new PDO('sqlite:'.dirname(__FILE__).'/db/siivous.db');
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        echo json_encode(db_getAllStands($db));
        $db = NULL;
        return;
     }catch(PDOException $e) {
        echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
        $db = NULL;
        return;
     }
}

//get a stand by id (i:)
function get(){
    if(isset($_GET["debug"])){
        echo "debug\n";
        replacePost('{"i":"679632245"}');
    }
    
    /*
    if(function_exists('customLog'))
        customLog("./logs/getLog.txt", json_encode($_POST));
    */
    
    $user_id = trim($_SESSION['uid']);
    
    try {
        //connect to db
        $db = new PDO('sqlite:'.dirname(__FILE__).'/db/siivous.db');
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            $stands = db_getStands($db, $user_id);
            $isSpecial = db_isSpecialId($db, $user_id);
            echo json_encode(array("stands" => $stands, "special" => $isSpecial ? "1" : "0" ));
            $db = NULL;
            return;
    }catch (PDOException $e) {
        echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
        $db = NULL;
        return;
    }
}

function delete(){
    if(isset($_GET["debug"])){
        replacePost('{"i":"679632245"}');
    }
    
    if(function_exists('customLog'))
        customLog("./logs/standsLog.txt", "delete|".json_encode($_POST));

    /* Validate */
    $validate = checkRequired(array("i" => true));
    if($validate != 1){
        echo json_encode(array("error"=>$validate));
        return;
    }
    $user_id = trim($_SESSION['uid']);
    $id = trim($_POST["i"]);
    
    try {
        //connect to db
        $db = new PDO('sqlite:'.dirname(__FILE__).'/db/siivous.db');
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        if(db_deleteStand($db, $user_id, $id)){
            echo json_encode(array("success" => $id));
        }else{
            echo json_encode(array("error" => "Unable to delete stand."));
        }
        $db = NULL;
        return;
    }catch (PDOException $e) {
        echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
        $db = NULL;
        return;
    }
}

function add(){
    if(isset($_GET["debug"])){
        /*
        replacePost('{
            "address": "Tarkk\'ampujankatu+18%2C+00150+Helsinki%2C+Finland",
            "city": "Helsinki",
            "desc": "BlablablablbaBlablablablba%0ABlablablablba%0A%0ABlablablablba%0A%0ABlablablablba%0A%0ABlablablablbaBlablablablbaBlablablablbaBlablablablba%0ABlablablablbaBlablablablba%0A%0A%0A%0A%0ABlablablablbaBlablablablbaBlablablablba%0A%0A%0ABl",
            "et": "15%3A00",
            "i": "679632245",
            "lnk": "",
            "name": "My+Stand",
            "st": "12%3A00",
            "t": "0+2+3+6+",
            "u": "60.16078470000001",
            "v": "24.942949699999986"
        }');*/
        replacePost('{"i":"518892420","name":"Joensuu - Ilosaari","address":"Siltakatu 1, 80100 Joensuu, Finland","city":"","u":"62.5990455742272","v":"29.77065405078747","st":"10:00","et":"14:00","desc":"Kaikille avoin ja ilmainen kirpputori Joensuun Ilosaaressa. S\u00e4\u00e4varaus.","t":"0 1 3 4 6 7 8 ","lnk":"http:\/\/www.facebook.com\/JoensuunSeudunKevatsiivous"}');
        //replacePost('{"i":"679632245","name":"A Stand","address":"Tarkk\'ampujankatu 18, 00150 Helsinki, Finland","city":"Helsinki","u":"60.16195618672868","v":"24.945653289471466","st":"12:00","et":"17:00","desc":"sdsdsd","t":"0 2 4 6 8 ","lnk":""}');
    }    

    if(function_exists('customLog'))
        customLog("./logs/standsLog.txt", "add|".json_encode($_POST));
    /* Validate */
    $validate = checkRequired(array("address" => true, "city" => true, "u" => true, "v" => true, "desc" => true, "name" => true, "st" => true, "et" => true, "t" => false, "lnk" => false));
    if($validate != 1){
        echo json_encode(array("error"=>$validate));
        return;
    }
    
    $user_id = trim($_SESSION['uid']);
    $name = trim($_POST["name"]);
    $address = trim($_POST["address"]);
    $city = trim($_POST["city"]);
    $city = strcasecmp($city,"helsingfors") == 0 ? "Helsinki" : $city;
    $u = trim($_POST["u"]);
    $v = trim($_POST["v"]);
    $description = trim($_POST["desc"]);
    $sT = explode(":",$_POST["st"]);
    $eT = explode(":",$_POST["et"]);
    $tags = trim($_POST["t"]);
    $link = trim($_POST["lnk"]);
    $createRecyclingCenter = isset($_POST["i"]) ? TRUE : FALSE;
    if(function_exists('customLog'))
        customLog("./logs/debugLog.txt", $_POST["st"].", ".$_POST["et"]." => ".$sT[0].", ".$sT[1]." / ".$eT[0].", ".$eT[1]."\n");
    //connect to db
    try {
        $db = new PDO('sqlite:'.dirname(__FILE__).'/db/siivous.db');
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $id = db_createUpdateStand($db, $user_id, $name, $address, $u, $v, $sT, $eT, $description, $tags, $link, $createRecyclingCenter);
        db_updateCityBounds($db, $city, $u, $v);
        
        $return = array("id" => $id,
        "name" => $name,
        "address" => $address,
        "u" => $u,
        "v" => $v,
        "city" => $city,
        "start_hour" => $sT[0],
        "start_minute" => $sT[1],
        "end_hour" => $eT[0],
        "end_minute" => $eT[1],
        "description" => $description,
        "tags" => $tags,
        "link" => $link
        );
        if(function_exists('customLog'))         customLog("./logs/debugLog.txt", json_encode(array($return))."\n");
        echo json_encode(array($return));
        $db = NULL;
        return;
    } catch (PDOException $e) {
        echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
        $db = NULL;
        return;
    }
}

function addRecycle(){
    if(isset($_GET["debug"])){
        replacePost('{"i":"679632245","name":"My recycling center","address":"H\u00e4meentie 120, 00560 Helsinki, Finland","city":"Helsinki","u":"60.20412469999999","v":"24.968694899999946","st":"10:00","et":"16:00","desc":"My first recycling center! Exciting!!!!","t":"2 3 9 ","lnk":""}');
        unset($_GET["debug"]); //don't propagate
    }
    if(function_exists('customLog'))
        customLog("./logs/addRecycleLog.txt", json_encode($_POST));
    $user_id = trim($_SESSION['uid']);
    //connect to db
    try {
        $db = new PDO('sqlite:'.dirname(__FILE__).'/db/siivous.db');
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        if(!db_isSpecialId($db, $user_id)){ //check that the user has the permission
            echo json_encode(array("error"=>"Sinulla ei ole lupaa lisätä kierrätyspisteitä."));
            $db = NULL;
            return;
        }
        $db = NULL;
        $_POST["i"] = "OK";
        add();
        return;
    } catch (PDOException $e) {
        echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
        $db = NULL;
        return;
    }
}

function adminLoad(){
//connect to db
    try {
        $db = new PDO('sqlite:'.dirname(__FILE__).'/db/siivous.db');
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $stands = db_getAllStandsInfo($db);
        echo json_encode($stands);
        $db = NULL;   
        return;
    }catch (PDOException $e) {
        echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
        $db = NULL;
        return;
    }
}

function load(){
    if(isset($_GET["debug"])){
        //replacePost('{"um":"60.12712243909954","uM":"60.21251207707566","vm":"24.852720111523467","vM":"25.024381488476592"}');
        replacePost('{"um":"-90","uM":"90","vm":"-180","vM":"180","c":"true"}'); //{"um":"-90","uM":"90","vm":"-180","vM":"180","c":"true"}

        //replacePost('{"um":"60.12712243909954","uM":"60.21251207707566","vm":"24.737706988964874","vM":"25.139394611035186","t":"1.7976931348623157e+308"}');
        

    }
    /*
    if(function_exists('customLog'))
        customLog("./logs/loadLog.txt", json_encode($_POST));
*/
    /* Validate */
    $validate = checkRequired(array("um" => true, "uM" => true, "vm" => true, "vM" => true));
    if($validate != 1){
        echo json_encode(array("error"=>$validate));
        return;
    }
    
    $uM = trim($_POST["uM"]);
    $um = trim($_POST["um"]);
    $vM = trim($_POST["vM"]);
    $vm = trim($_POST["vm"]);
    $citiesOnly = isset($_POST["c"]) ? trim($_POST["c"]) : "0";
    $limit = isset($_POST["l"]) ? trim($_POST["l"]) : 0;
    $offset = isset($_POST["o"]) ? trim($_POST["o"]) : 0; //id negative, only return the number of stands in the boundaries
    $timestamp = isset($_POST["t"]) ? trim($_POST["t"]) : "0";

    //connect to db
    try {
        $db = new PDO('sqlite:'.dirname(__FILE__).'/db/siivous.db');//'.dirname(__FILE__).'

        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        
        if($offset < 0){
            //calculate how many stands are in the boundaries
            $standCount = db_getNumberOfStandsInBounds($ds, $um, $uM, $vm, $vM);
            echo json_encode(array("standCount" => $standCount));
        }else if($citiesOnly){
            $cities = db_getCitiesInBounds($db, $um, $uM, $vm, $vM);
            echo json_encode($cities);
        }else{
            $stands = db_getStandsInBounds($db, $um, $uM, $vm, $vM, $timestamp, $limit, $offset);
            echo json_encode($stands);
        }
        $db = NULL;   
        return;
    }catch (PDOException $e) {
        echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
        $db = NULL;
        return;
    }catch(Exception $ee){
        echo $ee->getMessage();
        return;
    }
}

/* SPECIALS */
/*
function mail_utf8($to, $subject = '(No subject)', $message = '', $header = '') {
  $header_ = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n";
  mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $header_ . $header);
}*/

function message(){ 
	/* Validate */
    $validate = checkRequired(array("subject" => false, "message" => true, "email" => true));
    if($validate != 1){
        echo json_encode(array("error"=>$validate));
        return;
    }

    $from = trim($_POST["email"]);
    $message = trim($_POST["message"]);
    $message = "Reply to ".$from."\r\n\r\n".$message;
    $subject = trim($_POST["subject"]);
    
    $to      = 'siivouspaiva@siivouspaiva.com'. "\r\n";
    $headers = 'From: siivouspaiva@siivouspaiva.com' . "\r\n" .
        'Reply-To: '.$from."\r\n" .
        'X-Mailer: PHP/' . phpversion()."\r\n";
    $headers .= 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/plain; charset=UTF-8'."\r\n";
    $headers .= 'Content-Transfer-Encoding: 8bit'."\r\n";
    $headers .= "\r\n";
/*
Mail causes a 500 error for an inexistent 'to' address. Don't know the cause of the problem but the solution is to put the following parameter in the mail() command:

"-fme@mydomain.com"
*/
    mail($to, "=?utf-8?b?".base64_encode($subject)."?=", $message, $headers, "-fme@siivouspaiva.com");
    echo json_encode(array("success"=>"Message sent."));
}

function invite(){
    $validate = checkRequired(array("email" => true));
    if($validate != 1){
        echo json_encode(array("error"=>$validate));
        return;
    }
    global $config;
    $email = trim($_POST["email"]);
     try {
        $db = new PDO('sqlite:'.dirname(__FILE__).'/db/siivous.db');
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $id = db_createInvitedSpecial($db, $email);
        $db = NULL;
        
        //send the mail
        $message = "Seuraa linkkiä vahvistaaksesi tämän kutsun.\r\n".
                    $config['paths']['base_url']."/register/".$id."\r\n".
                    "Ystävällisin terveisin,\r\n Siivouspäivä-työryhmä";
        $headers = 'From: siivouspaiva@siivouspaiva.com' . "\r\n" .
            'Reply-To: siivouspaiva@siivouspaiva.com'."\r\n" .
            'X-Mailer: PHP/' . phpversion()."\r\n";
        $headers .= 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/plain; charset=UTF-8'."\r\n";
        $headers .= 'Content-Transfer-Encoding: 8bit'."\r\n";
        $headers .= "\r\n";
        mail($email, "=?utf-8?b?".base64_encode("Kutsu kierrätyspisteiden lisääjäksi sivustolle siivouspaiva.com")."?=", $message, $headers);
        echo json_encode(array("success"=>"Invitation sent."));
        
        return;
      }catch (PDOException $e) {
        echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
        $db = NULL;
        return;
    }  
}

function confirm(){
    $validate = checkRequired(array("id" => true));
    if($validate != 1){
        echo json_encode(array("error"=>$validate));
        return;
    }
    $id = trim($_POST["id"]);
    
     try {
        $db = new PDO('sqlite:'.dirname(__FILE__).'/db/siivous.db');
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        if(!db_isInvitationId($db, $id)){
            echo json_encode(array("error"=>"Kutsu ei kelpaa."));
            $db = NULL;
            return;
        }
        db_acceptSpecial($db, $id);
        echo json_encode(array("success"=>"Privilege set."));
        $db = NULL;
        return;
      }catch (PDOException $e) {
        echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
        $db = NULL;
        return;
    }  
}

function specials(){
    try {
        $db = new PDO('sqlite:'.dirname(__FILE__).'/db/siivous.db');
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        echo json_encode(db_getSpecials($db));
        $db = NULL;
        return;
    }catch (PDOException $e) {
        echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
        $db = NULL;
        return;
    }  
}

function revoke(){
    $validate = checkRequired(array("id" => true));
    if($validate != 1){
        echo json_encode(array("error"=>$validate));
        return;
    }
    $id = trim($_POST["id"]);
    
     try {
        $db = new PDO('sqlite:'.dirname(__FILE__).'/db/siivous.db');
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        db_deleteSpecial($db, $id);
        echo json_encode(array("success"=>$id));
        $db = NULL;
        return;
      }catch (PDOException $e) {
        echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
        $db = NULL;
        return;
    }
}


/* NEWS */
function newsList(){
    try {
        $db = new PDO('sqlite:'.dirname(__FILE__).'/db/siivous.db');
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $newsList = db_getNewsList($db);
        echo json_encode($newsList);
        $db = NULL;
        return;
      }catch (PDOException $e) {
        echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
        $db = NULL;
        return;
    }
}

function getNewsItem(){
    if(isset($_GET["debug"])){
        replacePost('{"id":"1"}');
    }
    
    $validate = checkRequired(array("id" => true));
    if($validate != 1){
        echo json_encode(array("error"=>$validate));
        return;
    }
    $id = trim($_POST["id"]);
    
     try {
        $db = new PDO('sqlite:'.dirname(__FILE__).'/db/siivous.db');
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $newsItem = db_getNews($db,$id);
        echo json_encode($newsItem );
        $db = NULL;
        return;
      }catch (PDOException $e) {
        echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
        $db = NULL;
        return;
    }
}

function addNewsItem(){
    if(isset($_GET["debug"])){
        replacePost('{"id":"","content":"<h2>Test News<\/h2>\n<p>Some text<\/p>\n<p>An image<\/p>\n<p><img title=\"Damn thiefs!\" src=\"..\/newsImages\/lsbike_400.jpg\" alt=\"Lauri\'s stolen bike\" width=\"400\" height=\"267\" \/><\/p>\n<p><em>sdsdsds<\/em><\/p>"}');
    }
    if(function_exists('customLog'))
        customLog("./logs/addNewsLog.txt", json_encode($_POST));
  $validate = checkRequired(array("content" => true, "authors" => true, "title" => true));
    if($validate != 1){
        echo json_encode(array("error"=>$validate));
        return;
    }
    $content = trim($_POST["content"]);
    $authors = trim($_POST["authors"]);
    $title = trim($_POST["title"]);
    $id = isset($_POST["id"]) ? trim($_POST["id"]) : "-1";
    
     try {
        $db = new PDO('sqlite:'.dirname(__FILE__).'/db/siivous.db');
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $newsId = db_createUpdateNews($db,$content,$authors,$title,$id);
        echo json_encode(array("success" => $newsId));
        $db = NULL;
        return;
      }catch (PDOException $e) {
        echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
        $db = NULL;
        return;
    }  
}

function deleteNewsItem(){
    if(function_exists('customLog'))
        customLog("./logs/deleteNewsLog.txt", json_encode($_POST));
    $validate = checkRequired(array("id" => true));
    if($validate != 1){
        echo json_encode(array("error"=>$validate));
        return;
    }
    $id = trim($_POST["id"]);
    
     try {
        $db = new PDO('sqlite:'.dirname(__FILE__).'/db/siivous.db');
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        if(db_deleteNews($db, $id)){
            echo json_encode(array("success"=>$id));
        }else{
            echo json_encode(array("success"=>-1));
        }
        $db = NULL;
        return;
      }catch (PDOException $e) {
        echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
        $db = NULL;
        return;
    }   
}

//Favorite
function addFavorite(){
    if(function_exists('customLog'))
        customLog("./logs/addFavoriteLog.txt", json_encode($_POST));
    $validate = checkRequired(array("stand_id" => true));
    if($validate != 1){
        echo json_encode(array("error"=>$validate));
        return;
    }
    $user_id = trim($_SESSION['uid']);
    $stand_id = trim($_POST["stand_id"]);
    try{
        $db = new PDO('sqlite:'.dirname(__FILE__).'/db/siivous.db');
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $standAdded = db_createFavorite($db, $user_id, $stand_id);
        echo json_encode($standAdded);
        $db = NULL;
        return;
    }catch (PDOException $e) {
        echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
        $db = NULL;
        return;
    }   
}

function deleteFavorite(){
    if(function_exists('customLog'))
        customLog("./logs/deleteFavoriteLog.txt", json_encode($_POST));
    $validate = checkRequired(array("stand_id" => true));
    if($validate != 1){
        echo json_encode(array("error"=>$validate));
        return;
    }
    $user_id = trim($_SESSION['uid']);
    $stand_id = trim($_POST["stand_id"]);
    try{
        $db = new PDO('sqlite:'.dirname(__FILE__).'/db/siivous.db');
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        if( db_deleteFavorite($db, $user_id, $stand_id) ){
            echo json_encode(array("success"=>$stand_id));
        }else{
            echo json_encode(array("success"=>-1));
        }
        $db = NULL;
        return;
    }catch (PDOException $e) {
        echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
        $db = NULL;
        return;
    }   
}

function favoritesList(){
    if(function_exists('customLog'))
        customLog("./logs/getFavoritesLog.txt", json_encode($_POST));
    
    $user_id = trim($_SESSION['uid']);
    try{
        $db = new PDO('sqlite:'.dirname(__FILE__).'/db/siivous.db');
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $favorites = db_getFavorites($db, $user_id);
        echo json_encode($favorites);
        $db = NULL;
        return;
    }catch (PDOException $e) {
        echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
        $db = NULL;
        return;
    }   
}

/* LOGIN/REGISTER */
function login(){
    $validate = checkRequired(array("a" => true, "type" => true));
    if($validate != 1){
        echo json_encode(array("error"=>$validate));
        return;
    }
    
    $action = trim($_POST["a"]);
    
    if(function_exists('customLog'))
        customLog("./logs/loginLog.txt", $action."|".json_encode($_POST));
    switch($action){
        case 'login':
            validateLogin();
            break;
        case 'email_login':
            emailLogin();
            break;
        case 'register':
            registerUser();
            break;
        default:
            echo json_encode(array("error"=>"unknown query"));
            break;
    }
    return;
}

function validateLogin(){
    $requiredFields = trim($_POST["type"]) == "email" || (isset($_SESSION['invitation']) && $_SESSION['invitation'] === 1) ? array("email"=>true, "password" => false) : array("fb_id"=>true);
    $validate = checkRequired($requiredFields);
    if($validate != 1){
        echo json_encode(array("error"=>$validate));
        return;
    }
    $email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
    $password = isset($_POST["password"]) ? customEncrypt( trim($_POST["password"]) ) : "";
    $fb_id = isset($_POST["fb_id"]) ? trim($_POST["fb_id"]) : "";
    try{
        $db = new PDO('sqlite:'.dirname(__FILE__).'/db/siivous.db');
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $verification = db_checkUser($db, $email, $fb_id, $password);
        //1 if the user exists and password match
        //0 if the user exists but passwords do not match
        //-1 if the user is not in the database
        //-2 if the user is in the database but has not validated his/her email
        if($verification["result"] > 0){
            doLogin( $verification["user_id"] );
            echo json_encode( array("success"=>1) );
        }else if($verification["result"] == 0){
            echo json_encode(array("success"=>0,"message"=>"wrong password"));
        }else if($verification["result"] == -1){
            echo json_encode(array("success"=>0,"message"=>"no user"));
        }else if($verification["result"] == -2){
            echo json_encode(array("success"=>0,"message"=>"need validation"));
        }
        $db = NULL;
    }catch (PDOException $e) {
        echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
        $db = NULL;
    }
    return;
}

function emailLogin(){
    global $config;
    //$requiredFields = trim($_POST["type"]) == "email" ? array("email"=>true, "password" => false) : array("fb_id"=>true);
    $validate = checkRequired( array("email"=>true) );
    if($validate != 1){
        echo json_encode(array("error"=>$validate));
        return;
    }
    $email = trim($_POST["email"]);
    try{
        $db = new PDO('sqlite:'.dirname(__FILE__).'/db/siivous.db');
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $verification = db_checkUser($db, $email, "", "");
        //1 if the user exists and password match
        //0 if the user exists but passwords do not match
        //-1 if the user is not in the database
        //-2 if the user is in the database but has not validated his/her email
        if($verification["result"] >= 0){
            $t = strftime('%s');
            $id = $t."_".( intval($verification["user_id"]) ^ ( ( intval($t) << 2 ) ^ ( intval($t) >> 2 ) ) );
            $link = $config['paths']['base_url']."/reset/".$id;
            
            $message = "Asettaaksesi uuden salasanan klikkaa seuraavaa linkkiä\r\n". //To reset your password, please follow the link.
                        $link."\r\n".
                        "Ystävällisin terveisin,\r\n Siivouspäivä-työryhmä";
            $headers = 'From: siivouspaiva@siivouspaiva.com' . "\r\n" .
                'Reply-To: siivouspaiva@siivouspaiva.com'."\r\n" .
                'X-Mailer: PHP/' . phpversion()."\r\n";
            $headers .= 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/plain; charset=UTF-8'."\r\n";
            $headers .= 'Content-Transfer-Encoding: 8bit'."\r\n";
            $headers .= "\r\n";
            mail($email, "=?utf-8?b?".base64_encode("Aseta uusi salasana sivulle siivouspaiva.com")."?=", $message, $headers); //Reset your password on siivouspaiva.com
            
            echo json_encode(array("success"=>"1", "link"=>$link));
        }else if($verification["result"] == -1){
            echo json_encode(array("success"=>0,"message"=>"no user"));
        }else if($verification["result"] == -2){
            echo json_encode(array("success"=>0,"message"=>"need validation"));
        }
        $db = NULL;
    }catch (PDOException $e) {
        echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
        $db = NULL;
    }
    return;
}

function registerUser(){
    global $config;
    $requiredFields = trim($_POST["type"]) == "email" || (isset($_SESSION['invitation']) && $_SESSION['invitation'] === 1) ? array("email"=>true, "password" => true, "password_verify" => true) : array("fb_id"=>true);
    $requiredFields = array_merge($requiredFields, array("user_name"=>true, "phone" => true) );
    $validate = checkRequired( $requiredFields );
    if($validate != 1){
        echo json_encode(array("error"=>$validate));
        return;
    }

    $name = isset($_POST["user_name"]) ? trim($_POST["user_name"]) : "";
    $phone = isset($_POST["phone"]) ? trim($_POST["phone"]) : "";
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $password_verify = trim($_POST["password_verify"]);
    $fb_id = isset($_POST["fb_id"]) ? trim($_POST["fb_id"]) : "";
    try{
        $db = new PDO('sqlite:'.dirname(__FILE__).'/db/siivous.db');
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $verification = db_checkUser($db, $email, $fb_id, "");
        //1 if the user exists and password match
        //0 if the user exists but passwords do not match
        //-1 if the user is not in the database
        //-2 if the user is in the database but has not validated his/her email
        if($verification["result"] == -2){
            echo json_encode(array("success"=>"0", "message"=>"need validation"));
        }else if($verification["result"] != -1){
            echo json_encode(array("success"=>"0", "message"=>"user exists"));
        }else{
            if($password != $password_verify){
                echo json_encode(array("success"=>"0", "message"=>"passwords mismatch"));
            }else{
                //crypt pass
                if($password != ""){
                    $password = customEncrypt($password);
                }
                //create user
                $user_info = db_createUser($db, $email, $fb_id, $password, $name, $phone);

                $id = $user_info["t"]."_".( intval($user_info["id"]) ^ ( ( intval($user_info["t"]) << 2 ) ^ ( intval($user_info["t"]) >> 2 ) ) );
                if(trim($_POST["type"]) != "email" || (isset($_SESSION['invitation']) && $_SESSION['invitation'] === 1)){
                    //validate the account automatically
                    unset($_SESSION['invitation']);
                    echo json_encode( array("success"=>db_validateAccount($db, $id) ) );
                }else{
                    //send verif mail
                    $link = $config['paths']['base_url']."/register/".$id;
                    $message = "Vahvistaaksesi tilin, klikkaa seuraavaa linkkiä.On\r\n". //To validate your account, please follow the link.
                                $link."\r\n".
                                "Ystävällisin terveisin,\r\n Siivouspäivä-työryhmä";
                    $headers = 'From: siivouspaiva@siivouspaiva.com' . "\r\n" .
                        'Reply-To: siivouspaiva@siivouspaiva.com'."\r\n" .
                        'X-Mailer: PHP/' . phpversion()."\r\n";
                    $headers .= 'MIME-Version: 1.0' . "\r\n";
                    $headers .= 'Content-type: text/plain; charset=UTF-8'."\r\n";
                    $headers .= 'Content-Transfer-Encoding: 8bit'."\r\n";
                    $headers .= "\r\n";
                    mail($email, "=?utf-8?b?".base64_encode("Vahvista tili sivulle siivouspaiva.com")."?=", $message, $headers); //Validate your account on siivouspaiva.com
                    
                    echo json_encode(array("success"=>"1"/*, "link"=>$link*/));
                }
            }
        }
    }catch (PDOException $e) {
        echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
    }
    $db = NULL;
    return;
}

function setPass(){
    $validate = checkRequired( array("password"=>true, "password_verify"=>true) );
    if($validate != 1){
        echo json_encode(array("error"=>$validate));
        return;
    }
    $id = trim($_SESSION['uid']);
    $password = trim($_POST["password"]);
    $password_verify = trim($_POST["password_verify"]);
    if($password != $password_verify){
        echo json_encode(array("success"=>"0", "message"=>"passwords mismatch"));
        return;
    }
    try{
        $db = new PDO('sqlite:'.dirname(__FILE__).'/db/siivous.db');
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        //crypt pass
        $password = customEncrypt($password);
        db_changePassword($db, $id, $password);
        echo json_encode(array("success"=>"1"));
        $db = NULL;
    }catch (PDOException $e) {
        echo json_encode(array("error"=>"'Db error: ".$e->getMessage()."'"));
        $db = NULL;
    }
    return;
}

function doLogin( $userId ){
    if(isset($_SESSION['invitation'])){
        unset($_SESSION['invitation']);
    }
    $_SESSION['uid'] = $userId;
}

function logout(){
    $_SESSION['uid'] = -1;
    unset($_SESSION['uid']);
    echo json_encode(array("success"=>"1"));
    return;
}

function customEncrypt($mySecret){
    return sha1(md5($mySecret).$mySecret);
}

function isLogged(){
    if(!isset($_SESSION['uid']) || $_SESSION['uid']<0){
        return false;
    }
    return true;
}

function isAdmin(){
    if(!isset($_SESSION['admin']) || $_SESSION['admin'] != 1){
        return false;
    }
    return true;
}


?>
