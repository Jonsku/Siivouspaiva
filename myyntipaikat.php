<?php
ini_set("session.save_path",$_SERVER['DOCUMENT_ROOT']."/session/");
session_start();
if(isset($_POST['create'])){
  $_SESSION['create'] = 1;
  unset($_POST['create']);
}

if(isset($_POST["isLogged"])){
  header('Cache-Control: no-cache, must-revalidate');
  header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
  header('Content-type: application/json; charset=utf-8');
  echo json_encode( array( "success"=> (isset($_SESSION['uid']) && $_SESSION['uid']) > 0 ? "1" : "0") );
  exit(0);
}
if(isset($_POST["createOnLoad"])){
  header('Cache-Control: no-cache, must-revalidate');
  header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
  header('Content-type: application/json; charset=utf-8');
  echo json_encode( array( "success"=>isset($_SESSION['create']) ? $_SESSION['create'] : "0") );
  $_SESSION['create'] = 0;
  exit(0);
}

$page = "Myyntipaikat";
include($_SERVER['DOCUMENT_ROOT']."/inc/header.php");
?>
    <link type="text/css" href="<?php echo $config['paths']['base_url']; ?>/css/jquery.jscrollpane.css" rel="stylesheet" media="all" />
    <!-- Table sorter plugin -->
    <script type="text/javascript" src="<?php echo $config['paths']['base_url']; ?>/script/jquery.tablesorter.min.js"></script>
    <script type="text/javascript" src="<?php echo $config['paths']['base_url']; ?>/script/jquery.jscrollpane.min.js"></script>
    <!-- Google Map API init -->
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=<?php echo $config['googlemap']['api_key']; ?>&sensor=true&language=<?php echo getLangageCode(); ?>"></script>
  </head>

  <body>
    <!-- Init FB SDK -->
    <div id="fb-root"></div>
    
   <!-- header starts -->
    <?php include($_SERVER['DOCUMENT_ROOT']."/inc/navbar.php"); ?>
    <!-- navbar ends -->
    
    <!-- content starts -->
<?php if(!$config['stands']['open']){ ?>
    <div id="content">
      <div class="container">
        <div class="row">
            <div class="span12 inset">
              <br/>
              <br/>
              <h1 style="text-align:center"><?php s('fi_FI'); ?>Myyntipaikkoja voi lisätä <?php echo $config['stands']['opening_date'] ?> lähtien.<?php e(); ?></h1>
            </div>
        </div>
        <?php include($_SERVER['DOCUMENT_ROOT']."/inc/footer.php"); ?>
      </div>
    <!-- /container -->
    

  </body>
</html>
<?php return; }  ?>


    <div id="content">
      <div class="container">
        <div class="row" id="login_row">
          <div class="span12 inset">
            <!-- Login/out -->
            <p id="login-text"><?php s(); ?>Kirjaudu sisään Facebook-tunnuksillasi lisätäksesi karttaan myyntipaikkasi tai muokataksesi merkintöjäsi.<?php e(); ?></p>
            <br/><br/>
            <a name="login"><button id="logout-btn" class="btn-red"><?php s('en_EN'); ?>Log out<?php e(); ?></button><button id="email-login-select" class="btn-red login-btn"><?php s('en_EN'); ?>Login with an email address<?php e(); ?></button><button id="fb-login" class="btn-red login-btn"><?php s('en_EN'); ?>Login with a facebook account<?php e(); ?></button><button id="create-stand-btn" class="btn-red"><?php s(); ?>Lisää myyntipaikka<?php e(); ?></button><button id="create-recycle-btn" class="btn-red"><?php s(); ?>Lisää kierrätyspiste<?php e(); ?></button></a><span id="contact-fb-msg"><?php s('en_EN'); ?>Contacting facebook...<?php e(); ?></span><span id="check-fb-msg"><?php s('en_EN'); ?>Checking if you're logged in...<?php e(); ?></span><span id="no-fb-msg"><?php s('en_EN'); ?>Facebook is not responding...<?php e(); ?></span>
            <?php include($_SERVER['DOCUMENT_ROOT']."/inc/login.php"); ?>
          </div>
        </div>
        <?php include($_SERVER['DOCUMENT_ROOT']."/inc/map.php"); ?>
        
        <div class="row bookmark">
          <!-- Bookmark -->
          <div class="span12">
              <h3 name="user-bookmark" class="bookmark"><i class="minus-icon"></i><?php s('fi_FI'); ?>Suosikit <a target="_blank" href="<?php echo $config['paths']['base_url']; ?>/print_fav.php">Tulosta / Tallenna</a><?php e(); ?></h3>
          </div>
        </div>
        <div id="user-bookmark" class="collapsable bookmark">
        </div>
        
        <div class="row">
          <div class="span12">
            <div id="user-stands">
              <ul class="stands">
              </ul>
            </div>
          </div>
          
          <!-- Create/Edit stand -->
          <div id="stand-editing">
            <div id="stand-admin" class="inset collapsable">
              <div class="row">
                <div class="span12">
                  <div class="create">
                      <h2><?php s('fi_FI'); ?>Lisää myyntipaikka<?php e(); ?></h2>
                      <p><?php s('fi_FI'); ?>Täytä seuraavat kohdat ja paina <b>Tallenna</b> luodaksesi myyntipaikan. Tähdellä merkityt kohdat ovat pakollisia.<?php e(); ?></p>
                  </div>
                  <div class="modify">
                      <h2><?php s('en_EN'); ?>Modify your stand information<?php e(); ?></h2>
                      <p><?php s('fi_FI'); ?>Täytä seuraavat kohdat ja paina <b>Tallenna</b>. Tähdellä merkityt kohdat ovat pakollisia.<?php e(); ?></p>
                  </div>
                </div>
              </div>
              <form id="stand-form" class="form-vertical" method="post" action="#">
                <fieldset>
                  <div class="row">
                    <div class="span12">
                      <div class="row">
                        <div class="span4">
                          
                          <div class="control-group">
                              <label class="control-label" for="name"><?php s('fi_FI'); ?>Myyntipaikan nimi<?php e(); ?> *</label>
                              <div class="controls">
                                <input type="text" value="" name="name" id="name"/>
                              </div>
                          </div>
                          
                          <div class="control-group">
                              <label class="control-label" for="address"><?php s('fi_FI'); ?>Myyntipaikan osoite<?php e(); ?> *</label>
                              <div class="controls">
                                <input type="text" value="" name="address" id="address"/><button class="btn" id="pin-btn"><i class="icon-search" id="geocode-pin-btn"></i></button>
                                <input type="hidden" id="city" name="city"/>
                                 <p class="help-block"><?php s('fi_FI'); ?>Kirjoita osoite kenttään ja paina <i class="icon-search"></i> merkitäksesi paikan kartalla. Voit sen jälkeen liikuttaa merkkiä, jos haluat muuttaa myyntipaikan kohtaa.<?php e(); ?></p>
                              </div>
                          </div>
                          
                          <div class="control-group">
                              <label class="control-label"><?php s('fi_FI'); ?>Aukioloaika<?php e(); ?> *</label>
                              <div class="controls">
                                Klo. <select name="sh" id="sh" class="hour" data-default="08"></select>:<select name="sm" id="sm" class="minutes"></select> - <select name="eh" id="eh" class="hour" data-default="16"></select>:<select name="em" id="em" class="minutes"></select>
                              </div>
                          </div>
                          
                        </div>
                        
                        <div class="span4">
                          
                          <div class="control-group">
                              <label class="control-label" for="desc"><?php s('fi_FI'); ?>Lyhyt kuvaus siitä, mitä myyt (enintään 200 merkkiä)<?php e(); ?> *</label>
                              <div class="controls">
                                <textarea name="desc" id="desc"></textarea>
                                <span id="char-count">0 / 200 <?php s('fi_FI'); ?>merkkiä<?php e(); ?></span>
                              </div>
                          </div>
                          
                           <div class="control-group">
                              <label class="control-label" for="lnk"><?php s('fi_FI'); ?>Facebook-tapahtuma<?php e(); ?></label>
                              <div class="controls">
                                <input type="text" value="" name="lnk" id="lnk"/>
                                <p class="help-block"><?php s('fi_FI'); ?>Jos olet luonut myyntipaikkaasi varten Facebook-tapahtuman, voit lisätä sen linkin tähän.<?php e(); ?></p>
                              </div>
                          </div>

                        </div>
                        
                        <div class="span4">
                          <div class="control-group">
                              <p class="control-label"><?php s('fi_FI'); ?>Valitse seuraavista ne kohdat, jotka kuvaavat valikoimaasi parhaiten. Voit jättää kohdan myös tyhjäksi.<?php e(); ?></p>
                              <div class="controls">
                                    <ul class="tags-list">
                                      <li><label><input type="checkbox" class="tag" value="0"> <?php s('fi_FI'); ?>Vaatteita ja asusteita<?php e(); ?></label></li>
                                      <li><label><input type="checkbox" class="tag" value="1"> <?php s('fi_FI'); ?>Lastenvaatteita ja -tarvikkeita<?php e(); ?></label></li>
                                      <li><label><input type="checkbox" class="tag" value="2"> <?php s('fi_FI'); ?>Huonekaluja<?php e(); ?></label></li>
                                      <li><label><input type="checkbox" class="tag" value="3"> <?php s('fi_FI'); ?>Kodin pientavaroita<?php e(); ?></label></li>
                                      <li><label><input type="checkbox" class="tag" value="4"> <?php s('fi_FI'); ?>Leluja ja pelejä<?php e(); ?></label></li>
                                      <li><label><input type="checkbox" class="tag" value="5"> <?php s('fi_FI'); ?>Tekniikkaa<?php e(); ?></label></li>
                                      <li><label><input type="checkbox" class="tag" value="6"> <?php s('fi_FI'); ?>Levyjä<?php e(); ?></label></li>
                                      <li><label><input type="checkbox" class="tag" value="7"> <?php s('fi_FI'); ?>Leffoja<?php e(); ?></label></li>
                                      <li><label><input type="checkbox" class="tag" value="8"> <?php s('fi_FI'); ?>Kirjoja<?php e(); ?></label></li>
                                      <li><label><input type="checkbox" class="tag" value="9"> <?php s('fi_FI'); ?>Korjauspalveluita<?php e(); ?></label></li>
                                      <li style="display:none"><label><input type="checkbox" class="tag recycling_center" value="r"> <?php s('fi_FI'); ?>Recycling center<?php e(); ?></label></li>
                                    </ul>
                                  <input type="hidden" name="tags"/>
                              </div>
                          </div>
                        </div>
                      </div>
                      
                      <div class="row">
                        <div class="span12">
                          <div class="">
                            <button class="btn s-close"><?php s('fi_FI'); ?>Peruuta<?php e(); ?></button>
                            <button id="validate" type="submit" class="btn-red"></button>
                          </div>
                        </div>
                      </div>
                        
                    </div>
                  </div>
                  
                  
                </fieldset>
              </form>
            </div>
            <!-- stand edit ends -->
            <!-- static stand info -->
            <div id="stand-info" class="inset collapsable">
              <div class="row">
                <div class="span12">
                  <div class="row">
                    <div class="span4">
                      <p class="control-label"><?php s('fi_FI'); ?>Myyntipaikan nimi<?php e(); ?>:<br> <span id="stand-name"></span></p>
                      <p class="control-label"><?php s('fi_FI'); ?>Myyntipaikan osoite<?php e(); ?>:<br> <span id="stand-address"></span> <a class="btn btn-mini" href="#"><i class="icon-search"></i></a></p>
                    </div>
                    <div class="span4">
                      <p class="control-label"><?php s('fi_FI'); ?>Kuvaus<?php e(); ?>: <pre id="stand-description"></pre> </p>
                    </div>
                    <div class="span4">
                      <p class="control-label"><?php s('fi_FI'); ?>Aukioloaika<?php e(); ?>:<br> <span id="stand-opening-hours"></span></p>
                      <p class="control-label"><?php s('fi_FI'); ?>Kategoriat<?php e(); ?>:<br> <span id="stand-tags"></span></p>
                      <p class="control-label"><?php s('fi_FI'); ?>Facebook-tapahtuma<?php e(); ?>:<br> <a id="stand-link" target="_blank"></a></p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="span12">
                  <div id="info-btn"><button class="btn-red modify"><?php s('fi_FI'); ?>Muokkaa<?php e(); ?></button><button class="btn delete"><?php s('fi_FI'); ?>Poista myyntipaikka<?php e(); ?></button></div>
                  <div id="confirm-delete" style="display: none"><?php s('fi_FI'); ?>Oletko varma?<?php e(); ?> <button class="btn no"><?php s('fi_FI'); ?>Ei, peruuta<?php e(); ?></button><button class="btn yes"><?php s('fi_FI'); ?>Kyllä, poista<?php e(); ?></button></div>
                </div>
              </div>
            </div>
            <!-- End of stand admin -->
          </div>
        </div>
        <?php include($_SERVER['DOCUMENT_ROOT']."/inc/footer.php"); ?>
      </div>
    </div>
    <!-- /container -->
    
    
    <!-- Le javascript
    ================================================== -->
    <script type="text/javascript">
/* Localized Strings */
stringsL10N = new Array();
<?php include("l10n_map.php"); ?>
    </script>
    <!-- Placed at the end of the document so the pages load faster -->
    <script type="text/javascript" src="<?php echo $config['paths']['base_url']; ?>/script/jquery.form.js"></script>
    <script type="text/javascript" src="<?php echo $config['paths']['base_url']; ?>/script/form.util.js"></script>
    <script type="text/javascript" src="<?php echo $config['paths']['base_url']; ?>/script/login.js"></script>
    <script type="text/javascript" src="<?php echo $config['paths']['base_url']; ?>/script/standmap.js"></script>
    <script type="text/javascript" src="<?php echo $config['paths']['base_url']; ?>/script/fb.php"></script>    
    <script type="text/javascript" src="<?php echo $config['paths']['base_url']; ?>/script/myyntipaikat.js"></script>
    <script type="text/javascript">
      var createStandOnLoad = false;
      //$(document).ready(function(){
        $.ajax({
          type: 'POST',
          dataType: "json",
          url: '',
          data :{createOnLoad : 1},
          error: function(jqXHR, textStatus, errorThrown){
            $.log("There was an error.");
            $.log(jqXHR);
            $.log(textStatus);
            return;
          },
          success: function(data, textStatus, jqXHR){
            $.log(data);
            createStandOnLoad = data.success == "1";
            $.log("createStandOnLoad", createStandOnLoad);
            $.ajax({
              type: 'POST',
              dataType: "json",
              url: '',
              data :{isLogged : 1},
              error: function(jqXHR, textStatus, errorThrown){
                $.log("There was an error.");
                $.log(jqXHR);
                $.log(textStatus);
                return;
              },
              success: function(data, textStatus, jqXHR){
                if(data.success == "1"){
                  loggedIn();
                }
              }
            }); 
          }
        });
      //}
    </script>
    <!-- Plugin -->
    <script src="<?php echo $config['paths']['base_url']; ?>/bootstrap/js/bootstrap-collapse.js"></script>
    <script src="<?php echo $config['paths']['base_url']; ?>/bootstrap/js/bootstrap-tooltip.js"></script>
  </body>
</html>

