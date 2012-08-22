<?php
ini_set("session.save_path",$_SERVER['DOCUMENT_ROOT']."/session/");
session_start();
$page = "Yhteystiedot";
include($_SERVER['DOCUMENT_ROOT']."/inc/header.php");
?>
  </head>

  <body>

    
    <?php include($_SERVER['DOCUMENT_ROOT']."/inc/navbar.php"); ?>
    
    <div id="content">
      <!-- content starts -->
      <div class="container">
        <div class="row">
          <div class="span12 contact">
            <div class="row">
              <div class="span4">
                <h2>Pauliina Seppälä</h2>               
                p. 050 572 7910<br>
                pauliina@siivouspaiva.com
              </div>             
              
              <div class="span4">
                <h2>Tanja Jänicke</h2>
                p. 040 140 5750<br>
                tanja@siivouspaiva.com
              </div>
            
              
              <div class="span4">
                <h2>Jaakko Blomberg</h2>
                p. 044 547 8354<br>
                jaakko@siivouspaiva.com
              </div>
			</div>
            <div class="row">
              <div class="span4">
                <h2>Mari Savio</h2>
                <b><?php s(); ?>Oheistapahtumat<?php e(); ?></b><br>
                p. 045 121 6172<br>
                mari@siivouspaiva.com
              </div>
          
            
              <div class="span4">
                <h2>Charlotte Remming</h2>
                <b><?php s(); ?>Oheistapahtumat<?php e(); ?></b><br>
                p. 050 5416654<br>
                charlotte@siivouspaiva.com
              </div>
            </div>
          </div>
        </div>
      <?php include($_SERVER['DOCUMENT_ROOT']."/inc/footer.php"); ?>
    </div>
    <!-- Plugin -->
    <script src="<?php echo $config['paths']['base_url']; ?>/bootstrap/js/bootstrap-collapse.js"></script>
  </body>
</html>