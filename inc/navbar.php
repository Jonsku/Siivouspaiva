    <!-- header starts -->
    <div id="header">
      <div class="container logo">
          <div class="row">
              <div class="span12 center">
                  <img src="<?php echo $config['paths']['base_url']; ?>/img/header.png" alt="SiivousPäivä"/>
              </div>
          </div>
      </div>
    
    
      <!-- navbar starts -->
      <div class="container">
        <div class="navbar row">
          <div class="span12">
            <div class="custom-navbar-inner">
              <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </a>
              <div class="nav-collapse">
                <ul class="nav">
                <?php
                foreach($navigation as $label=>$fileName){
                ?>
                  <li<?php if($page == $label) echo ' class="active"'; ?>><a href="<?php echo $config['paths']['base_url']."/".$_SESSION['locale']."/".$fileName; ?>"><?php s('fi_FI'); echo $label; e(); ?></a></li>
                <?php
                }
                ?>
                </ul>
                <?php
                  if(FALSE && isset($_SESSION['admin']) && $_SESSION['admin'] === 1){
                 ?>
                 <ul class="nav pull-right" data-no-collapse="true">
                  <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">Admin</a>
                    <ul class="dropdown-menu">
                      <li><a href="<?php echo $config['paths']['base_url']."/admin/"; ?>">Manage stands</a></li>
                      <li><a href="<?php echo $config['paths']['base_url']."/admin/translations.php"; ?>">Translations</a></li>
                      <li><a href="<?php echo $config['paths']['base_url']."/admin/cache.php"; ?>">Manage cache</a></li>
                      <li><a href="<?php echo $config['paths']['base_url']."/admin/logout.php"; ?>">Log out from admin</a></li>
                    </ul>
                  </li>
                </ul>
                 <?php
                  }
                 ?>
                <!-- Language selector -->
                <!--
                <ul class="nav pull-right" data-no-collapse="true">
                  <li class="dropdown">
                    <a href="<?php echo $config['paths']['base_url']."/".($_SESSION['locale'] == "fi_FI" ? "en_EN" : "fi_FI")."/".$navigation[$page]; ?>"><?php echo $_SESSION['locale'] == "fi_FI" ? $localeToLanguage['en_EN'] : $localeToLanguage['fi_FI']; ?></a>
                  </li>
                </ul>
                -->
                <!--
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $localeToLanguage[$_SESSION['locale']]; ?><b class="caret"></b></a>
                   
                    <ul class="dropdown-menu">
                    <?php
                      foreach($localeToLanguage as $code=>$langName){
                    ?>
                      <li<?php if($_SESSION['locale'] == $code) echo ' class="active"'; ?>><a href="<?php echo $config['paths']['base_url']."/".$code."/".$navigation[$page]; ?>"><?php echo $langName; ?></a></li>
                    <?php
                      }
                    ?>
                    </ul>
                  -->
              </div><!--/.nav-collapse -->
            </div>
          </div>
        </div>
      </div>
      <!-- navbar ends -->
    </div>
    <!-- header ends -->