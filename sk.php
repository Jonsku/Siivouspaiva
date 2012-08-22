<?php
ini_set("session.save_path",$_SERVER['DOCUMENT_ROOT']."/session/");
session_start();
$page = "Siisteys & kierrätys";
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
            <h2 style="margin-top:50px;"><?php s(); ?>Siivouspäivä on kierrätyksen juhlapäivä. Olemme saaneet Siivouspäivään mukaan miltei kaikki merkittävät pääkaupunkiseudulla vaikuttavat kierrätys- ja keräystoimijat. Rakennamme Siivouspäivän ajaksi Helsinkiin kierrätyspisteitä, joihin voi tuoda myymättä jääneet tavarat ja komeroista kaiken sen vanhan romun, joka ei ole enää aikoihin <?php e(); ?></h2>
            <p><?php s(); ?>Kierrätyspisteiden sijainnit löydät samasta kartasta kuin myyntipaikat. Kierrätyspisteet ovat avoinna klo 12-18. Jos et ehdi myymään Siivouspäivänä, niin siivoa muuten vaan ja pistä hyvä kiertämään!<?php e(); ?></p>
			<p><?php s(); ?>Siivouspäivä on siisti päivä. Alla kerrotaan, minkälaista tavaraa yhteistyökumppanimme ottavat Siivouspäivänä vastaan. Nimiä klikkaamalla pääset myös heidän sivuilleen ja voit lukea lisää lähelläsi olevista kierrätyspalveluista.<?php e(); ?></p>
			<p><?php s(); ?>PS. Myös sinä voit miettiä kierrätysmahdollisuuksien luomista omalle alueellesi ja olla yhteydessä johonkin pienempään paikallisjärjestöön, kuten Pelastusarmeijaan tai Pietarin Katulapset ry:hyn. Jos taloyhtiöllänne on tapana ottaa yhteinen jätelava pihojen tai ullakkojen siivousta varten, voit ehdottaa sen hankkimista juuri tuoksi viikonlopuksi.<?php e(); ?></p>
		  </div>
        </div>
      <?php include($_SERVER['DOCUMENT_ROOT']."/inc/footer.php"); ?>
    </div>
    <!-- Plugin -->
    <script src="<?php echo $config['paths']['base_url']; ?>/bootstrap/js/bootstrap-collapse.js"></script>
  </body>
</html>