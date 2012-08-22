<?php
ini_set("session.save_path",$_SERVER['DOCUMENT_ROOT']."/session/");
session_start();
$page = "Vapaaehtoiseksi";
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
            <h2 style="margin-top:50px;"><?php s(); ?>Siivouspäivä ei olisi syntynyt ilman lukuisten vapaaehtoisten työpanosta. Myös sinä voit auttaa päivän onnistumisessa vielä paremmin ja laajemmin. Jos Siivouspäivään ei osallistu vielä kukaan asuinalueellasi, ei se tarkoita sitä, ettei tilanne voisi muuttua. Voit tiedottaa Siivouspäivästä lähialueellasi sekä ideoida, kannustaa ja avustaa muita osallistujia kirppistensä kanssa. Levittämällä ilmoituksia ja julisteita voit jakaa tietoa Siivouspäivästä myös niille, jotka eivät ole Facebookissa.<?php e(); ?></h2>
			<p><?php s(); ?>Etsimme myös vapaaehtoisia Helsinkiin rakennettaviin kierrätyspisteisiin. Aiempaa kokemusta tai kierrätysalan tuntemusta ei tarvita. Opastamme kaikki vapaaehtoiset tehtäviin.<?php e(); ?></p>
			<p><?php s(); ?>Jo muutaman tunnin päivystyksestä on kanssakaupunkilaisille paljon iloa. Jos olet innokas dykkari tai keräilijä, on kierrätyspisteellä päivystyksestä varmasti myös sinulle hyötyä.<?php e(); ?></p>
			<p><?php s(); ?>Jos taas innostut aiheesta enemmän tai haluat juuri omalle asuinalueellesi kierrätyspisteen, voit myös ottaa kokonaisen kierrätyspisteen ja sen organisoinnin vastuullesi. Me kerromme miten.<?php e(); ?></p>
			<p><?php s(); ?>Mikäli haluat vapaaehtoiseksi, ota yhteyttä osoitteeseen <a href="mailto:siivouspaiva@siivouspaiva.com">siivouspaiva@siivouspaiva.com</a><?php e(); ?></p>
          </div>
        </div>
      <?php include($_SERVER['DOCUMENT_ROOT']."/inc/footer.php"); ?>
    </div>
    <!-- Plugin -->
    <script src="<?php echo $config['paths']['base_url']; ?>/bootstrap/js/bootstrap-collapse.js"></script>
  </body>
</html>