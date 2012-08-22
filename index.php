<?php
ini_set("session.save_path",$_SERVER['DOCUMENT_ROOT']."/session/");
session_start();
$page = "Etusivu";
include($_SERVER['DOCUMENT_ROOT']."/inc/header.php");
?>
  </head>

  <body>
    <!-- facebook -->
    <div id="fb-root"></div>
    <script>(function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/fi_FI/all.js#xfbml=1&appId=<?php echo $config['facebook']['app_id']; ?>";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>

    
    <?php include($_SERVER['DOCUMENT_ROOT']."/inc/navbar.php"); ?>
    
    <div id="content">
      <!-- content starts -->
      <div class="container">
        <div class="row">
          <div class="span12">
            <div class="row">
              <div class="span8">
                <div id="intro">
                  <div class="row">
                      <div class="span6 intro-text">
                          <h2><?php s(); ?>Siivouspäivä on kierrätyksen, vanhojen tavaroiden ystävien ja kaupunkikulttuurin uusi juhlapäivä! Siivouspäivä muuttaa kaupungit isoiksi kirpputoreiksi ja markkinoiksi, joille kuka tahansa voi tuoda ylimääräiset tavaransa myytäväksi tai annettavaksi. Tule mukaan!<?php e(); ?></h2>
                      </div>
                      <div class="myyntipaikkassi span2">
                          <img src="<?php echo $config['paths']['base_url']; ?>/img/add_stand.png" alt="Lisää oma myyntipaikkassi"/>
                          <span><?php s(); ?>Lisää oma<br/>myyntipaikkasi<br/><?php e(); ?>> ></span>
                      </div>                        
                  </div>
                </div>
                <div class="row">
                  <div class="span8">
                    <p><?php s(); ?>Vihdoinkin voit päästä helpolla eroon kaikista nurkkiin kertyneistä tavaroista, antaa niille uuden elämän ja vielä ansaita siitä ilosta!</p>
                    <p>Voit selata myyntipaikkoja kartalta ja lisätä sinne omasi Facebook-tunnuksillasi. Tutustu myös kohtiin kysyttyä sekä siisteys ja kierrätys ennen puotisi avaamista.<?php e(); ?></p>
                  </div>
                </div>
				<div class="news row">
                  <h1 class="title"><?php s(); ?>Siivouspäivä tulossa uudestaan 8.9.!<?php e(); ?></h1><br/>
					<p><?php s(); ?>Siivouspäivää vietetään seuraavan kerran lauantaina 8.9.! Nettisivuja uudistetaan vielä kesän aikana ja uusia myyntipaikkoja voi lisätä 8.8. lähtien.<?php e(); ?></p>
				</div>
				<div class="news row">
                  <h1 class="title"><?php s(); ?>Siivouspäivä menestyksekkäästi takana!<?php e(); ?></h1><br/>
					<p><?php s(); ?>Siivouspäivää vietettiin ensimmäisen kerran lauantaina 12.5. Vaikka sää olikin hieman kolea, oli myyjiä ja ostajia liikkeellä sankoin joukoin. Nettisivujemme kartalle merkittiin yhteensä lähes 600 myyntipaikkaa, minkä lisäksi monet tulivat mukaan hetken mielijohteesta. Suurkiitos kaikille mukana olleille, jotka tekivät päivästä vuoden siisteimmän juhlan!<?php e(); ?></p>
				</div>
				<div class="news row">
                  <h1 class="title"><?php s(); ?>Levitä tietoa Siivouspäivästä!<?php e(); ?></h1><br/>
                  <p><?php s(); ?>Voit tiedottaa Siivouspäivästä naapuristossasi helposti käyttämällä <a href="<?php echo $config['paths']['base_url']; ?>/gfx/siivouspaivaa4tyhja.pdf">ilmoituspohjaamme</a> tai valmista <a href="<?php echo $config['paths']['base_url']; ?>/gfx/siivouspaivaa4.pdf">julistettamme.</a> Kaikki ihmiset eivät löydä tietään nettisivuillemme, joten lisää muutama juliste vaikkapa lähikaupan ilmoitustaululle tai rappukäytävääsi, jotta mahdollisimman moni pääsisi osallistumaan Siivouspäivään.<?php e(); ?></p>
                </div>
              </div>
              
              <div class="span4">
                  <div class="like-box my-well" style="text-align: center; padding:0px; margin-bottom: 5px;">
                      <div class="fb-like-box" data-href="http://www.facebook.com/siivouspaiva" data-width="280" data-show-faces="true" data-stream="true" data-header="false"></div><!-- 345 -->
                  </div>
                  <!-- Contact form -->
                  <form id="message-form" class="form-inline my-well" action="/mail/" method="post">
                      <h1>Ota yhteyttä</h1>
                      <fieldset>
                          <div class="control-group subject">
                              <label for="subject">aihe:</label>
                              <div class="controls">
                                  <input type="text" name="subject" id="subject"/>
                              </div>
                          </div>
                          <div class="control-group email">
                              <label for="email">sähköpostiosoitteesi:</label>
                              <div class="controls">
                                  <input type="text" name="email" id="email"/>
                              </div>
                          </div>
                          <div class="control-group message">
                              <label for="message">viesti:</label>
                              <div class="controls">
                                  <textarea name="message" id="message"></textarea>
                              </div>
                          </div>
                          <button type="submit" class="btn-red">Lähetä</button>
                      </fieldset>
                  </form>
                  
                  <!-- Side illustration -->
                  <div class="illustration center">
                      <img src="<?php echo $config['paths']['base_url']; ?>/img/side_illustration.png" alt="side illustration">
                  </div>
              </div>
            </div>
          </div>
        </div>
      </div>
            
      <?php include($_SERVER['DOCUMENT_ROOT']."/inc/footer.php"); ?>
        
    </div>
    <script type="text/javascript">
    
    $.ajax({
            type: 'POST',
            url: '<?php echo $config['paths']['base_url']; ?>/data.php?query=news_list',
            success: function(data, textStatus, jqXHR){ //$.log("Ajax post result: "+textStatus); //$.log(data);
                if(data.error){
                    alert("Error:"+data.error);
                }else{
                    $.log(data);
                    for(var i in data){
                        $.ajax({
                            type: 'POST',
                            url: '<?php echo $config['paths']['base_url']; ?>/data.php?query=news_items',
                            data: {id: data[i].id},
                            success: function(data, textStatus, jqXHR){ //$.log("Ajax post result: "+textStatus); //$.log(data);
                                if(data.error){
                                    alert("Error:"+data.error);
                                }else{
                                    $('#news').append(data.content);
                                }
                            }
                        });
                    }
                }
            },
            dataType: "json"
        });
    
    //encode to send
    /*
    function urlencode(s) {
        s = encodeURIComponent(s);
        return s.replace(/~/g,'%7E').replace(/%20/g,'+');//.replace(/'/g,"\\'");
    }*/

    function messageSent(){
        $('#message-form input[name="email"]').val("");
        $('#message-form input[name="subject"]').val("");
        $('#message-form textarea').val("");
        alert("Kiitos! Olemme sinuun yhteydessä pian.")
    }
    
    $('#message-form button').click(function(){
        $('#message-form .error').removeClass("error");
        /* Validate */
        if($('#message-form input[name="email"]').val().replace(" ","").length == 0){
            alert("You must enter an email address.");
            $('.control-group.email').toggleClass("error");
            return false;
        }
        if($('#message-form textarea').val().replace(" ","").length == 0){
            alert("You must enter a message.");
            $('.control-group.textarea').toggleClass("error");
            return false;
        }
        var data= {
            email: urlencode($('#message-form input[name="email"]').val()),
            subject: urlencode($('#message-form input[name="subject"]').val()),
            message: urlencode($('#message-form textarea').val())
        };
        $.log(data);
        $.ajax({
            type: 'POST',
            url: '<?php echo $config['paths']['base_url']; ?>/data.php?query=mail',
            data: data,
            success: function(data, textStatus, jqXHR){ //$.log("Ajax post result: "+textStatus); //$.log(data);
                if(data.error){
                    alert("Error:"+data.error);
                }else{
                    messageSent();
                }
            },
            dataType: "json"
        });
        return false;
    });
    
    $('.news').prepend('<img class="line" src="<?php echo $config['paths']['base_url']; ?>/img/short_line.png" alt="news divider">');
    
    $('.myyntipaikkassi').click(function(){
        var myForm = $('<form action="./myyntipaikat.php" method="POST"><input type="hidden" name="create" value="1"/></form>');
       $('body').append(myForm);
       myForm.submit();
    });
    </script>
    <!-- Plugin -->
    <script src="<?php echo $config['paths']['base_url']; ?>/bootstrap/js/bootstrap-collapse.js"></script>
  </body>
</html>