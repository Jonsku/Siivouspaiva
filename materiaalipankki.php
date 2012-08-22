<?php
ini_set("session.save_path",$_SERVER['DOCUMENT_ROOT']."/session/");
session_start();
$page = "Materiaalipankki";
include($_SERVER['DOCUMENT_ROOT']."/inc/header.php");
?>
  </head>

  <body>

    
    <?php include($_SERVER['DOCUMENT_ROOT']."/inc/navbar.php"); ?>
    
    <div id="content">
      <!-- content starts -->
      <div class="container">
        <div class="row">
          <div class="span12">
            <h2 style="margin-top: 50px;"><?php s(); ?>Voit käyttää vapaasti tämän sivun materiaaleja Siivouspäivästä tiedottamisessa ja myyntipaikkasi markkinoinnissa. Yksityishenkilöt voivat käyttää Siivouspäivän nimeä ja logoa vapaasti Siivouspäivän yhteydessä. Nimen ja logon käyttö muina päivinä tai yritysten markkinoinnissa on kielletty ilman lupaa. Klikkaamalla kuvia ne avautuvat suurempina<?php e(); ?></h2>
	  </div>
	</div>
	<div class="row center">
	  <h3 class="h3_ressource"><?php s('en_EN'); ?>Logos<?php e(); ?></h3>
	  <hr/>
	  <div class="span4">
	    <a href="<?php echo $config['paths']['base_url']; ?>/gfx/siivouslogo_pysty.jpg" target="_blank"><img class="thumbnails" src="<?php echo $config['paths']['base_url']; ?>/gfx/siivouslogo_pysty_small.png"/></a>
	  </div>
	  <div class="span8">
	    <a href="<?php echo $config['paths']['base_url']; ?>/gfx/siivouslogo_vaaka.jpg" target="_blank"><img class="thumbnails" src="<?php echo $config['paths']['base_url']; ?>/gfx/siivouslogo_vaaka_small.png"/></a>
	  </div>
	</div>
	  
	<div class="row center">
	  <h3 class="h3_ressource"><?php s('en_EN'); ?>Posters<?php e(); ?></h3>
	  <hr/>
	  <div class="span4">
	    <a href="<?php echo $config['paths']['base_url']; ?>/gfx/siivouspaivajuliste.pdf" target="_blank"><img class="thumbnails" src="<?php echo $config['paths']['base_url']; ?>/gfx/siivouspaivajuliste.png"/></a>
	  </div>
	  <div class="span4">
	    <a href="<?php echo $config['paths']['base_url']; ?>/gfx/siivouspaivaa4tyhja.pdf" target="_blank"><img class="thumbnails" src="<?php echo $config['paths']['base_url']; ?>/gfx/siivouspaivaa4tyhja.png"/></a>
	  </div>
	  <div class="span4">
	    <a href="<?php echo $config['paths']['base_url']; ?>/gfx/siivouspaivaempty.pdf" target="_blank"><img class="thumbnails" src="<?php echo $config['paths']['base_url']; ?>/gfx/siivouspaivaempty.png"/></a>
	  </div>
	</div>
			<!-- here thumbnails of the logos: http://siivouspaiva.com/gfx/siivouslogo_vaaka.jpg http://siivouspaiva.com/gfx/siivouslogo_pysty -->
			<!-- here thumbnails of the posters: http://siivouspaiva.com/gfx/siivouspaivajuliste.pdf http://siivouspaiva.com/gfx/siivouspaivaa4tyhja.pdf http://siivouspaiva.com/gfx/siivouspaivaempty.pdf -->
			<!-- here later some text and thumbnails of fotos -->	
		  
        
	<?php include($_SERVER['DOCUMENT_ROOT']."/inc/footer.php"); ?>
      </div>
      <!-- /container -->
    <!-- Plugin -->
    <script src="<?php echo $config['paths']['base_url']; ?>/bootstrap/js/bootstrap-collapse.js"></script>
  </body>
</html>