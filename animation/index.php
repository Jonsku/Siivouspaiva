<?php
    $config = parse_ini_file("../siivouspaiva.ini", true);
?>
<!DOCTYPE html>
<html lang="fi">
    <head>
        <meta charset="UTF-8">
        <title>Siivouspaive May 2012 - Stands registration visualisation</title>
        <script type="text/javascript" src="../script/jquery-1.7.1.min.js"></script>
        <style>
        *{
            font-family: Georgia, 'New Century Schoolbook', 'Nimbus Roman No9 L', serif;
        }
        body{
background: #f2f5f6; /* Old browsers */
background: -moz-linear-gradient(top,  #c8d7dc 0%, #e3eaed 63%, #f2f5f6 100%); /* FF3.6+ */
background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#c8d7dc), color-stop(63%,#e3eaed), color-stop(100%,#f2f5f6)); /* Chrome,Safari4+ */
background: -webkit-linear-gradient(top,  #c8d7dc 0%,#e3eaed 63%,#f2f5f6 100%); /* Chrome10+,Safari5.1+ */
background: -o-linear-gradient(top,  #c8d7dc 0%,#e3eaed 63%,#f2f5f6 100%); /* Opera 11.10+ */
background: -ms-linear-gradient(top,  #c8d7dc 0%,#e3eaed 63%,#f2f5f6 100%); /* IE10+ */
background: linear-gradient(top,  #c8d7dc 0%,#e3eaed 63%,#f2f5f6 100%); /* W3C */
filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#c8d7dc', endColorstr='#f2f5f6',GradientType=0 ); /* IE6-9 */
background-repeat:repeat-x;

            border:none;
            margin:0;
            padding:20px;
        }
        
        #time_line{
            width:95%;
            margin-left:auto;
            margin-right:auto;
            height:5px;
            top:20px;
            background:#d8963d;
            border:3px solid #000000;
            position:relative;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            
            -webkit-box-shadow: 0px 9px 9px 0px rgba(0, 0, 0, 0.75);
            box-shadow: 0px 9px 9px 0px rgba(0, 0, 0, 0.75);
        }
        
        #scrubber{
            position:absolute;
            width:48px;
            height:74px;
            top:-35px;
            left:0;
            margin-left:-12px;
            background:transparent url(./scrubber.png) no-repeat;
            color:#000000;
            padding-top:13px;
            text-align:center;
            font-size:1.1em;
        }
        
        h1, button{
            margin-bottom:50px;
            text-align:center;
        }
        
        h1 {
            display:none;
        }
        
        button{
            display:block;
            font-size:2em;
            width:20%;
            margin-left:auto;
            margin-right:auto;
            text-align:center;
        }
        </style>
        
        <script type="text/javascript">
        var baseUrl = "<?php echo $config['paths']['base_url']; ?>";
        var markerImgPath = baseUrl+"/img/";
        var mapSprites = new Object;
        var mapHelsinki = undefined;
        var mapFinland = undefined;
        
        
        //at what zoom level the stands are shown
        var zoomBoundary = 9;
        
        function initializeMap() {
                var myOptions = {
                  center: new google.maps.LatLng(60.203151,24.930725), //helsinki by default
                  zoom: 12,
                  mapTypeId: google.maps.MapTypeId.ROADMAP,
                  disableDefaultUI: true
                };
                mapHelsinki = new google.maps.Map(document.getElementById("map_canvas_helsinki"), myOptions);
                
                myOptions = {
                  center: new google.maps.LatLng(66.213739,26.850586), //helsinki by default
                  zoom: 5,
                  mapTypeId: google.maps.MapTypeId.ROADMAP,
                  disableDefaultUI: true
                };
                mapFinland = new google.maps.Map(document.getElementById("map_canvas_finland"), myOptions);
                
                //MAP SPRITES MAPPING
                mapSprites["recycle"] = {
                    size: new google.maps.Size(20, 32),
                    origin: new google.maps.Point(0,32),
                    anchor: new google.maps.Point(10, 32)
                };
                
                mapSprites["recycle_edit"] = {
                    size: new google.maps.Size(20, 32),
                    origin: new google.maps.Point(20,32),
                    anchor: new google.maps.Point(10, 32)
                };
                
                mapSprites["stand"] = {
                    size: new google.maps.Size(20, 32),
                    origin: new google.maps.Point(0,0),
                    anchor: new google.maps.Point(10, 32)
                };
                
                mapSprites["stand_edit"] = {
                    size: new google.maps.Size(20, 32),
                    origin: new google.maps.Point(20,0),
                    anchor: new google.maps.Point(10, 32)
                };
                
                mapSprites["shadow"] = {
                    size: new google.maps.Size(34, 21),
                    origin: new google.maps.Point(40,0),
                    anchor: new google.maps.Point(4, 19)
                };
              
                /*
                google.maps.event.addListener(map, 'bounds_changed', function() {
                        ////console.log("Bounds have changed");
                        var bounds = map.getBounds();
                        if(cities.cities === undefined){ //init
                                refreshAllCities();
                                refreshStands(bounds.getSouthWest().lat(), bounds.getNorthEast().lat(), bounds.getSouthWest().lng(), bounds.getNorthEast().lng(), getCitiesLastUpdate());
                                lastBounds = bounds;
                                lastZoom = map.getZoom();
                                return;
                                //vBounds = bs;
                        }else if(!isInCurrentCitiesBounds(bounds)){
                                getCitiesInBound(bounds.getSouthWest().lat(), bounds.getNorthEast().lat(), bounds.getSouthWest().lng(), bounds.getNorthEast().lng() );
                        }else{
                                setCurrentCityToBounds(bounds);
                        }
                        
                        
                        if(map.getZoom()>zoomBoundary){
                                //extrac only the bounds that needs refreshing
                                //var unionBounds = lastBounds.union(bounds);
                                if(lastBounds.intersects(bounds) && !(lastBounds.contains(bounds.getNorthEast()) && lastBounds.contains(bounds.getSouthWest()))){ //chech if intersect and is not completely contained
                                        //the intersecting rectangle
                                        var north = Math.min(lastBounds.getNorthEast().lat(), bounds.getNorthEast().lat());
                                        var south = Math.max(lastBounds.getSouthWest().lat(), bounds.getSouthWest().lat());
                                        var east  = Math.min(lastBounds.getNorthEast().lng(), bounds.getNorthEast().lng());
                                        var west  = Math.max(lastBounds.getSouthWest().lng(), bounds.getSouthWest().lng());
                                        
                                        if(north == lastBounds.getNorthEast().lat()){ //there is a rectangle North
                                                refreshStands(north,  bounds.getNorthEast().lat(), Math.min(bounds.getSouthWest().lng(), lastBounds.getSouthWest().lng()), Math.max(bounds.getNorthEast().lng(), lastBounds.getNorthEast().lng()));
                                        }else{ //there is rectangle South
                                                refreshStands(bounds.getSouthWest().lat(), south, Math.min(bounds.getSouthWest().lng(), lastBounds.getSouthWest().lng()), Math.max(bounds.getNorthEast().lng(), lastBounds.getNorthEast().lng()));
                                        }
                                        
                                        if(east == lastBounds.getNorthEast().lng()){ //there is a rectangle east
                                                refreshStands(lastBounds.getSouthWest().lat(), lastBounds.getNorthEast().lat(), east, bounds.getNorthEast().lng());
                                        }else{ //there is rectangle West
                                                refreshStands(lastBounds.getSouthWest().lat(), lastBounds.getNorthEast().lat(), bounds.getSouthWest().lng(), west);
                                        }
                                }else{
                                        refreshStands(bounds.getSouthWest().lat(), bounds.getNorthEast().lat(), bounds.getSouthWest().lng(), bounds.getNorthEast().lng(), getCitiesLastUpdate());
                                }
                                lastBounds = bounds;
                        }
                });
                
                google.maps.event.addListener(map, 'zoom_changed', function() {
                        if(lastZoom < map.getZoom() && map.getZoom()>zoomBoundary){
                                toggleStandsMarkers(true);
                        }else if(lastZoom > map.getZoom() && map.getZoom()<=zoomBoundary){
                                toggleStandsMarkers(false);
                        }
                        lastZoom = map.getZoom();
                });
                
                google.maps.event.addListener(map, 'center_changed', function() {
                        
                });
                */
        }
        
        /* Custom markers */
        function setMarkers(map, location, marker_name) {
          
            var image = new google.maps.MarkerImage(markerImgPath+'map_sprites.png',
               mapSprites[marker_name].size,
               mapSprites[marker_name].origin,
               mapSprites[marker_name].anchor);
            
            var shadow = new google.maps.MarkerImage(markerImgPath+'map_sprites.png',
               mapSprites['shadow'].size,
               mapSprites['shadow'].origin,
               mapSprites['shadow'].anchor);
          
            var shape = {
                coord: [0, 0, 20, 0, 20, 24, 0 , 24],
                type: 'poly'
            };
            var m = new google.maps.Marker({
                position: location,
                map: map,
                shadow: shadow,
                icon: image,
                shape: shape,
                title: "the title",
                zIndex: 1
            });
          return m;
        }
        
        function addStand(standInfo){
            var p = new google.maps.LatLng(standInfo.u, standInfo.v);
            setMarkers(mapHelsinki, p, "stand");
        }
        
        function addCity(standInfo){
            var p = new google.maps.LatLng(standInfo.u, standInfo.v);
            setMarkers(mapFinland, p, "stand");
        }
        </script>
        
    </head>
    <body>
        <!-- Google Map API init key=<?php echo $config['googlemap']['api_key']; ?> -->
        <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?&sensor=true"></script>
        <div id="wrapper">
            <button>Click to Play</button>
            <h1 id="date"></h1>
            <div id="map_canvas_finland" style="width:24%; height:800px; margin-right:1%; float:left;"></div>
            <div id="map_canvas_helsinki" style="width:75%; height:800px; float:left;"></div>
            <div id="time_line">
                <div id="scrubber"></div>
            </div>
            <br style="clear:both;">
        </div>
        <script type="text/javascript">
            initializeMap();
            $('button').click(function(){
               start(); 
            });
            
            var timeline = [];
            var cities = [];
            var duration = 0;
            var percent = 1;
            var count = 0;
            
            function start(){
                $.ajax({
                    type: 'POST',
                    url: baseUrl+'/data.php?query=getAll',
                    success: function(data, textStatus, jqXHR){
                        var i = 0;
                        timeline[i] = new Object;
                        timeline[i].time = 0;
                        timeline[i].timestamp = data[i].timestamp;
                        timeline[i].date = new Date(data[i].timestamp*1000);
                        timeline[i].u = data[i].u;
                        timeline[i].v = data[i].v;
                        timeline[i].city = data[i].city;
                        i++;
                        for(; i<data.length;i++){
                            timeline[i] = new Object;
                            timeline[i].time = data[i].timestamp*1000 - data[i-1].timestamp*1000;
                            timeline[i].timestamp = data[i].timestamp;
                            timeline[i].date = new Date(data[i].timestamp*1000);
                            timeline[i].u = data[i].u;
                            timeline[i].v = data[i].v;
                            timeline[i].city = data[i].city;
                            //console.log(timeline[i]);
                            /*
                            // hours part from the timestamp
                            var hours = date.getHours();
                            // minutes part from the timestamp
                            var minutes = date.getMinutes();
                            // seconds part from the timestamp
                            var seconds = date.getSeconds();
                            
                            // will display time in 10:30:23 format
                            var formattedTime = hours + ':' + minutes + ':' + seconds;
                            */
                        }
                        duration = ( timeline[timeline.length - 1].timestamp - timeline[0].timestamp);
                        percent = 100/duration;
                        $("button").hide();
                        $("h1").show();
                       next();
                    },
                    dataType: "json"
                });
            }
            var timeDef = 1000/(12*3600000);
            var current = 0;
            
            
            function next(){
                count++;
                $("#scrubber").text(count);
                var daysLeft = timeline[timeline.length-1].timestamp - timeline[current+1].timestamp;
                dayLeft = Math.round(daysLeft/86400);
                if(dayLeft>0){
                    $("#date").text(dayLeft+ " day"+( (dayLeft>1) ? "s" : "" ) +" to Siivouspäivä");
                }else{
                    $("#date").text("Siivouspäivä !");
                }
                if(cities[timeline[current].city] === undefined){
                    cities[timeline[current].city] = true;
                    addCity(timeline[current]);
                }
                addStand(timeline[current]);
                
                //console.log(timeline[current]);
                var scrubPos = ( timeline[current+1].timestamp-timeline[0].timestamp )*percent;
                $("#scrubber").animate({
                    left: scrubPos+"%"
                  }, timeline[current+1].time*timeDef, function() {
                    current++;
                    if(current<timeline.length-1){
                        next();
                    }else{
                        console.log(timeline[current]);
                    }
                  });
                /*
                //scrubPos *= 100;
                
                $("#scrubber").css("left",scrubPos+"%");
                //console.log(timeline[current]);
                current++;
                if(current<timeline.length)
                    setTimeout(next,timeline[current].time*timeDef);
                */
                return;
            }
        </script>
    </body>
</html>