        <!-- footer starts -->
        <div class="container footer">
            <div class="row">
                <div class="span12">
                    <img class="line" src="<?php echo $config['paths']['base_url']; ?>/img/long_line.png" alt="footer starts">
                </div>
            </div>
            <div class="row">
                <div class="span12">
                    <div class="row">
                        <div class="span4">
                            <div class="row">
                                <ul class="span4 footer-nav">
                                <?php
                                foreach($navigation as $label=>$fileName){
                                ?>
                                  <li<?php if($page == $label) echo ' class="active"'; ?>><a href="<?php echo $config['paths']['base_url']."/".$_SESSION['locale']."/".$fileName; ?>"><?php s('fi_FI'); echo $label; e(); ?></a></li>
                                <?php
                                }
                                ?>
                                  <li><a href="http://www.facebook.com/siivouspaiva">Facebook</a></li>
                                </ul>
                            </div>
                        </div>
                            
                        <ul class="partner span4">
                            <li><a href="http://www.fida.info/" target="_blank"><img src="<?php echo $config['paths']['base_url']; ?>/partners/fida.png" alt="Fida International"></a></li>
                            <li><a href="http://helsinkilocomotive.org/" target="_blank"><img src="<?php echo $config['paths']['base_url']; ?>/partners/hnml.png" alt="Helsinki New Media Locomotive"></a></li>
                            <li><a href="http://www.kierratyskeskus.fi/" target="_blank"><img src="<?php echo $config['paths']['base_url']; ?>/partners/kk.png" alt="Kierrätyskeskus"></a></li>
                            <li><a href="http://www.lassila-tikanoja.fi" target="_blank"><img src="<?php echo $config['paths']['base_url']; ?>/partners/lt.jpg" alt="Lassila tikanoja"></a></li>
                        </ul>
                        <ul class="partner span4">
                            <li><a href="http://www.technoworld.fi/" target="_blank"><img src="<?php echo $config['paths']['base_url']; ?>/partners/stena.png" alt="STENA Innovative Recycling"></a></li>
                            <li><a href="http://www.uff.fi/" target="_blank"><img src="<?php echo $config['paths']['base_url']; ?>/partners/uff.png" alt="UFF Auttamisen iloa"></a></li>
                            <li><a href="http://www.kulttuurikameleontit.com/" target="_blank"><img src="<?php echo $config['paths']['base_url']; ?>/partners/kameleon.png" alt="Kulttuurikameleontit Ry"></a></li>
                            <li><a href="http://www.nebula.fi/" target="_blank"><img src="<?php echo $config['paths']['base_url']; ?>/partners/nebula.png" alt="Nebula.fi"></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- footer ends -->
        <script type="text/javascript">
         $('h3').click(function(){
            var showId = $(this).attr('name');
            if( $(this).find('i.minus-icon').length > 0 ){
                 $(this).find('i').removeClass("minus-icon").addClass("plus-icon");
            }else{
                 $(this).find('i').removeClass("plus-icon").addClass("minus-icon");
            }
            $('#'+showId).toggle();
        });
        </script>