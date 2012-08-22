<?php //don't forger to include the Google Map API and <script type="text/javascript" src="./script/standmap.js"></script> in the calling page! ?>
<!-- Map component starts -->

<div class="row">
        
        <div class="span12">
                <div class="row">
                        <div class="span4">
                            <h3 name="map-tools" class="geocode"><i class="minus-icon"></i> <?php s(); ?>Osoitehaku<?php e(); ?></h3>
                            <div id="map-tools" class="inset collapsable">
                                <form id="geocode-form" method="post" action="#">
                                    <p><?php s(); ?>Kirjoita osoite, kaupunginosa tai postinumero<?php e(); ?>:</p>
                                    <input type="text" value="Helsinki" style="box-sizing: border-box;height: 2.4em;" name="geocode-address" id="geocode-address"/>
                                    <input type="submit" class="btn-green" value="<?php s(); ?>Etsi<?php e(); ?>"/>
                            </form>
                            </div>
                        </div>
                        <div class="span4">
                            <h3 name="times" class="hours"><i class="minus-icon"></i> <?php s(); ?>Aukioloajat<?php e(); ?></h3>
                            <div id="times" class="inset collapsable">
                                <p><?php s(); ?>Milloin pääset ostoksille?<?php e(); ?></p>
                                <label><input type="radio" name="group1" value="all" class="inline" checked> <?php s(); ?>Ihan milloin vaan<?php e(); ?></label>
                                <label class="now"><input type="radio" name="group1" value="now" class="inline"> <?php s(); ?>Nyt<?php e(); ?></label>
                                <label class="inline"><input type="radio" name="group1" value="other" class="inline"> <?php s(); ?>Klo.<?php e(); ?>&nbsp;</label><select name="sh" class="hour time-filter" data-default="8"></select>:<select name="sm" class="minutes time-filter"></select> - <select name="eh" class="hour time-filter" data-default="16"></select>:<select name="em" class="minutes time-filter"></select>
                            </div>
                        </div>
                        <div class="span4">
                            <h3 name="cities-div" class="cities"><i class="minus-icon"></i> <?php s(); ?>Kaupungit<?php e(); ?></h3>
                            <div id="cities-div" class="inset collapsable scrollable">
                                <ul id="cities"></ul>
                            </div>
                        </div>
                </div>
        </div>
</div>

<div class="row">
        <!-- Tags -->
        <div class="span12">
            <h3 name="filter" class="tags"><i class="minus-icon"></i> <?php s(); ?>Mitä olet etsimässä?<?php e(); ?></h3>
        </div>
</div>
<div id="filter" class="collapsable inset">
    <div class="row">
        <div class="span12">
                <div class="row">
                        <ul class="span4">
                            <li><label><input type="checkbox" class="tag-filter-toggle inline" value="6" checked="yes"/> <?php s(); ?>Levyjä<?php e(); ?></label></li>
                            <li><label><input type="checkbox" class="tag-filter-toggle inline" value="0" checked="yes"/> <?php s(); ?>Vaatteita ja asusteita<?php e(); ?></label></li>
                            <li><label><input type="checkbox" class="tag-filter-toggle inline" value="1" checked="yes"/> <?php s(); ?>Lastenvaatteita ja -tarvikkeita<?php e(); ?></label></li>
                            <li><label><input type="checkbox" class="tag-filter-toggle inline" value="2" checked="yes"/> <?php s(); ?>Huonekaluja<?php e(); ?></label></li>
                        </ul>
                        <ul class="span4">
                            <li><label><input type="checkbox" class="tag-filter-toggle inline" value="3" checked="yes"/> <?php s(); ?>Kodin pientavaroita<?php e(); ?></label></li>
                            <li><label><input type="checkbox" class="tag-filter-toggle inline" value="4" checked="yes"/> <?php s(); ?>Leluja ja pelejä<?php e(); ?></label></li>
                            <li><label><input type="checkbox" class="tag-filter-toggle inline" value="5" checked="yes"/> <?php s(); ?>Tekniikkaa<?php e(); ?></label></li>
                            <li><label><input type="checkbox" class="tag-filter-toggle inline" value="7" checked="yes"/> <?php s(); ?>Leffoja<?php e(); ?></label></li>
                        </ul>
                        <ul class="span4">
                            <li><label><input type="checkbox" class="tag-filter-toggle inline" value="8" checked="yes"/> <?php s(); ?>Kirjoja<?php e(); ?></label></li>
                            <li><label><input type="checkbox" class="tag-filter-toggle inline" value="9" checked="yes"/> <?php s(); ?>Korjauspalveluita<?php e(); ?></label></li>
                            <li><label><input type="checkbox" class="tag-filter-toggle inline" value="n" checked="yes"/> <?php s(); ?>Muuta<?php e(); ?></label></li>
                            <li style="position: relative;"><label><input type="checkbox" class="tag-filter-toggle inline" value="r" checked="yes"/> <?php s(); ?>Kierrätyspisteitä<?php e(); ?> <i class="recycle-tag"></i></label></li>
                        </ul>
                </div>
                <button class="btn-green show-all"><?php s(); ?>Valitse kaikki<?php e(); ?></button><button class="btn-green deselect-all"><?php s(); ?>Poista valinnat<?php e(); ?></button>
        </div>       
     </div>
</div>


<ul class="nav nav-tabs views">
  <li class="active"><a href="#"><?php s('en_EN'); ?>Map<?php e(); ?></a></li>
  <li><a href="#"><?php s('en_EN'); ?>List<?php e(); ?></a></li>
</ul>
<div id="views">
        <div class="row map">
                <!-- Map widget -->
                <div class="span12" style="height: 500px;"><div id="map_canvas" style="width:98%; height:100%"></div></div>
        </div>
        <div class="row list">
                <!-- List widget -->
                <div class="span12">
                        <table id="stands_list" class="tablesorter table-striped "> 
                                <thead> 
                                <tr> 
                                    <th><?php s(); ?>Nimi ja osoite<?php e(); ?> <i class="icon-chevron-down icon-white down"></i><i class="icon-chevron-up icon-white up"></i>&nbsp;&nbsp;</th>
                                    <th><?php s('en_EN'); ?>Opens at<?php e(); ?> <i class="icon-chevron-down icon-white down"></i><i class="icon-chevron-up icon-white up"></i>&nbsp;&nbsp;</th>
                                    <th><?php s('en_EN'); ?>Closes at<?php e(); ?> <i class="icon-chevron-down icon-white down"></i><i class="icon-chevron-up icon-white up"></i>&nbsp;&nbsp;</th> 
                                    <th><?php s(); ?>Postinumero<?php e(); ?> <i class="icon-chevron-down icon-white down"></i><i class="icon-chevron-up icon-white up"></i>&nbsp;&nbsp;</th> 
                                    <th><?php s(); ?>Kuvaus<?php e(); ?></th>
                                    <th></th>
                                </tr> 
                                </thead> 
                                <tbody> 
                                </tbody> 
                        </table> 
                </div>
        </div>
</div>
<!-- Map component end -->