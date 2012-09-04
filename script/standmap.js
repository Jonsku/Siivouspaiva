/* Add Custom Event Functionalities */
function EventTarget(){
    this._listeners = {};
}

EventTarget.prototype = {

    constructor: EventTarget,

    addListener: function(type, listener){
        if (typeof this._listeners[type] == "undefined"){
            this._listeners[type] = [];
        }

        this._listeners[type].push(listener);
    },

    fire: function(event){
        if (typeof event == "string"){
            event = { type: event };
        }
        if (!event.target){
            event.target = this;
        }

        if (!event.type){  //falsy
            throw new Error("Event object missing 'type' property.");
        }

        if (this._listeners[event.type] instanceof Array){
            var listeners = this._listeners[event.type];
            //$.log("event",event.type, this);
            for (var i=0, len=listeners.length; i < len; i++){
                listeners[i].call(this, this, event);
            }
        }
    },

    removeListener: function(type, listener){
        if (this._listeners[type] instanceof Array){
            var listeners = this._listeners[type];
            for (var i=0, len=listeners.length; i < len; i++){
                if (listeners[i] === listener){
                    listeners.splice(i, 1);
                    break;
                }
            }
        }
    }
};

/* Stand Class */
function Stand(){
    EventTarget.call(this);
}

Stand.prototype = new EventTarget();
Stand.prototype.constructor = Stand;
Stand.prototype.dontFilter = false;
Stand.prototype.isBookmarked = false;

Stand.prototype.isRecyclingCenter = function(){
    return /r/.test(this.data.tags); 
};

// Add/remove stand form bookmarks
Stand.prototype.bookmark = function(on){
    if(on && !sessionStatus){
        alert(stringsL10N["login to bookmark"]);
        return false;
    }
    
    //$.log("Bookmark: ", on, this.iWinListener);
    if(sessionStatus && !on && !confirm(stringsL10N["Haluatko poistaa tämän myyntipaikan suosikeistasi?"])){
        return false;
    }
    this.isBookmarked = on;
    var me = this;
    
    this.fire(on ? "bookmark" : "unmark");
    return true;
};

Stand.prototype.markAsUserStand = function(){
    if(this.dontFilter)
     return false;

    changeMarkerIcon(this.marker, this.isRecyclingCenter() ? "recycle_edit" : "stand_edit");
    this.dontFilter = true;
    return true;
};

Stand.prototype.unmarkAsUserStand = function(){
    if(!this.dontFilter)
        return false;
    changeMarkerIcon(this.marker, this.isRecyclingCenter() ? "recycle" : "stand");
    this.dontFilter = false;
    return true;
};

// Filter/unfilter stand based on criteria
Stand.prototype.filter = function(on){
    //if(this.dontFilter)
    //$.log("Filter: ",on && !this.dontFilter);
    this.marker.setVisible(!on || this.dontFilter);
    if(on && this.marker.iWin == openedWindow){
            openedWindow.close();
            openedWindow = undefined;
    }
    this.fire(on && !this.dontFilter ? "filter" : "unfilter");
};

function createStand(data, editMode){
        var p = new google.maps.LatLng(data.u, data.v);
        
        var newStand = new Stand();
        newStand.data = data;
        var markerName = newStand.isRecyclingCenter() ? "recycle": "stand";
        markerName += editMode ? "_edit" : "";
        //$.log("MarkerName", markerName);
        newStand.marker = setMarkers(map, p, markerName);
        google.maps.event.addListener(newStand.marker, 'dblclick', function(){
            focusOnStand(this);
        });
        
        newStand.addListener("bookmark",function(stand){
                if(typeof bookmark == 'function') { 
                        bookmark(stand); 
                }
        });
        newStand.addListener("unmark",function(stand){
                if(typeof unmark == 'function') { 
                        unmark(stand); 
                }
        });
        
        newStand.addListener("filter",function(stand){
            stand.marker.setVisible(false); 
        });
        newStand.addListener("unfilter",function(stand){
            stand.marker.setVisible(true);
        });
        
        var tagString = tagsCodeToString(data.tags);
        var timeString = "Avoinna "+data.start_hour+":"+data.start_minute+" - "+data.end_hour+":"+data.end_minute;
        var standInfo = '<div class="iWin" data-stand="'+data.id+'"><h4>'+data.name+'</h4><p>'+data.address+'</p><p>'+timeString+'</p><p>'+tagString+'</p>';
        //If the stand as a link to a facebook event
        if(data.link.match(/\S/)){
            standInfo += '<a href="'+data.link+'" target="_blank">'+stringsL10N["Myyntipaikan Facebook-sivu"]+'</a>';
        }
        standInfo += '<pre>'+data.description+'</pre>';
        standInfo += '<button class="btn-red bookmark">'+stringsL10N["Lisää suosikkeihin"]+'</button>';
        standInfo += '</div>';
        
        //create google map info window for the stand
        newStand.marker.iWin = new google.maps.InfoWindow({
            content: standInfo,
            maxWidth: 400
        });
        //Requirement:only one window opened at a time
        //content_changed
        newStand.iWinListener = google.maps.event.addListener(newStand.marker, 'click', function(){ //open info window on click on marker
                //$.log("Open win", this.iWin, on ? "This is a bookmarked stand" : "Not a bookmarked stand");
                if(openedWindow){
                    openedWindow.close();
                }
                openedWindow = this.iWin;
                openedWindow.open(map, this);
        });
        
        newStand.iWinListener = google.maps.event.addListener(newStand.marker.iWin, 'domready', function(){
            var myStand = loadedStands[$(this.getContent()).attr('data-stand')];
            //set bookmark button text and behaviour
            $('button.bookmark').text(myStand.isBookmarked ? stringsL10N["Poista suosikeistasi"] : stringsL10N["Lisää suosikkeihin"]).off('click').click(function(){
                    var myStand = loadedStands[$(this).parent().attr("data-stand")];
                    myStand.bookmark(!myStand.isBookmarked);
                    return false;
                });
        });
        
        google.maps.event.addListener(newStand.marker.iWin, 'closeclick', function(){ //unregister the window has being opened
            openedWindow = undefined;
        });
        
        newStand.addListener("bookmark",function(stand){
                if(stand.marker.iWin === openedWindow){
                     google.maps.event.trigger(stand.marker.iWin, 'domready');
                }
        });
        newStand.addListener("unmark",function(stand){
                if(stand.marker.iWin === openedWindow){
                     google.maps.event.trigger(stand.marker.iWin, 'domready');
                }
        });
        
        newStand.marker.setTitle(data.name+"\n"+timeString+"\n"+data.description);
        newStand.data.start_time = hourMinutesToMinutes(Number(data.start_hour),Number(data.start_minute));
        newStand.data.end_time = hourMinutesToMinutes(Number(data.end_hour),Number(data.end_minute));
        return newStand;
}



var cities = new Object; //list of cities
var loadedStands = new Object; //list of loaded stands
var openedWindow = undefined;
var tagsLabels = [ stringsL10N["Vaatteita ja asusteita"], stringsL10N["Lastenvaatteita ja -tarvikkeita"], stringsL10N["Huonekaluja"], stringsL10N["Kodin pientavaroita"], stringsL10N["Leluja ja pelejä"], stringsL10N["Tekniikkaa"], stringsL10N["Levyjä"], stringsL10N["Leffoja"], stringsL10N["Kirjoja"], stringsL10N["Korjauspalveluita"] ];

/* Google Map Integration */
var markerImgPath = baseUrl+"/img/";
var mapSprites = new Object;
var map = undefined;
var geocoder = undefined;
var marker = undefined;
var lastBounds = undefined;
var lastZoom = undefined;

//at what zoom level the stands are shown
var zoomBoundary = 9;

$("#stands_list").tablesorter({
        headers: {
                    // disable sort on tags
                    4: { sorter: false },
                    //the bookmark column
                    5: { sorter: false }
        }
}); 

function initializeMap() {
        var myOptions = {
          center: new google.maps.LatLng(60.169845, 24.93855080000003), //helsinki by default
          zoom: 12,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        };
        map = new google.maps.Map(document.getElementById("map_canvas"),
            myOptions);
        
        //GEOCODER
        geocoder = new google.maps.Geocoder();
        
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
      
        
        google.maps.event.addListener(map, 'bounds_changed', function() {
                ////$.log("Bounds have changed");
                var bounds = map.getBounds();
                if(cities.cities === undefined){ //init
                        refreshAllCities(function(){
                            var bounds = map.getBounds();
                            refreshStands(bounds.getSouthWest().lat(), bounds.getNorthEast().lat(), bounds.getSouthWest().lng(), bounds.getNorthEast().lng(), getCitiesLastUpdate());
                        });
                        lastBounds = bounds;
                        lastZoom = map.getZoom();
                        return;
                }else if(!isInCurrentCitiesBounds(bounds)){
                        getCitiesInBound(bounds.getSouthWest().lat(), bounds.getNorthEast().lat(), bounds.getSouthWest().lng(), bounds.getNorthEast().lng() );
                }else{
                        setCurrentCityToBounds(bounds);
                }
                
        });
        
         google.maps.event.addListener(map, 'dragend', function() {
            //$.log("drag ended");
            loadStandsFromDb();
         });
        
        google.maps.event.addListener(map, 'zoom_changed', function() {
                if(lastZoom < map.getZoom() && map.getZoom()>zoomBoundary){
                        $.log("zoom changed");
                        toggleStandsMarkers(true);
                        loadStandsFromDb();
                }else if(lastZoom > map.getZoom() && map.getZoom()<=zoomBoundary){
                        toggleStandsMarkers(false);
                }
                lastZoom = map.getZoom();
                
        });
        
        google.maps.event.addListener(map, 'center_changed', function() {
                
        });
}

//hide/show stands/cities depending on the zoom level
function toggleStandsMarkers(show){
    for(var i in loadedStands){
        loadedStands[i].marker.setVisible( show /*&& isFiltered(loadedStands[i]) */);
		if(show)
        	isFiltered(loadedStands[i]);
    }
    for(var i in cities.cities){
        cities.cities[i].marker.setVisible(!show);
    }
}
//zoom_changed

function loadStandsFromDb(){
     var bounds = map.getBounds();
    if(map.getZoom()>zoomBoundary){
                        //extract only the bounds that needs refreshing
                        //var unionBounds = lastBounds.union(bounds);
                        if(lastBounds.intersects(bounds) && !(lastBounds.contains(bounds.getNorthEast()) && lastBounds.contains(bounds.getSouthWest()))){ //chech if intersect and is not completely contained
                                //the intersecting rectangle
                                var north = Math.min(lastBounds.getNorthEast().lat(), bounds.getNorthEast().lat());
                                var south = Math.max(lastBounds.getSouthWest().lat(), bounds.getSouthWest().lat());
                                var east  = Math.min(lastBounds.getNorthEast().lng(), bounds.getNorthEast().lng());
                                var west  = Math.max(lastBounds.getSouthWest().lng(), bounds.getSouthWest().lng());
                                
                                if(north == lastBounds.getNorthEast().lat()){ //there is a rectangle North
                                        $.log("rect1");
                                        refreshStands(north,  bounds.getNorthEast().lat(), Math.min(bounds.getSouthWest().lng(), lastBounds.getSouthWest().lng()), Math.max(bounds.getNorthEast().lng(), lastBounds.getNorthEast().lng()));
                                }else{ //there is rectangle South
                                                                        $.log("rect1");
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
}

//get data about cities in bounds
function getCitiesInBound(um, uM, vm, vM, callback){
        var data = {
                um: urlencode(um),
                uM: urlencode(uM),
                vm: urlencode(vm),
                vM: urlencode(vM),
                c:  urlencode("true")
        };
        //$.log("refresh cities");
        //$.log(data);
        $.ajax({
            type: 'POST',
            url: baseUrl+'/data.php?query=load',
            data: data,
            success: function(data, textStatus, jqXHR){
                if(data.error){
                    alert("Error:"+data.error);
                }else{
                    updateCities(data);
                    if(callback){
                        callback();
                    }
                }
            },
            dataType: "json"
        });
}

function refreshAllCities(callback){
        getCitiesInBound(-90, 90, -180, 180, callback);
}

function refreshStands(um, uM, vm, vM, t){
    //var bounds = map.getBounds();
    var data= {
            um: urlencode(um),
            uM: urlencode(uM),
            vm: urlencode(vm),
            vM: urlencode(vM)
    };
    if(t != undefined){
            ////$.log("Refresh");
            data.t = urlencode(t);
    }
    $.ajax({
        type: 'POST',
        url: baseUrl+'/data.php?query=load',
        data: data,
        success: function(data, textStatus, jqXHR){
            if(data.error){
                alert("Error:"+data.error);
            }else{
                $.log("Fetched stands:", data.length);
                updateStands(data);
            }
        },
        dataType: "json"
    });
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

function changeMarkerIcon(marker, marker_name){
    var image = new google.maps.MarkerImage(markerImgPath+'map_sprites.png',
       mapSprites[marker_name].size,
       mapSprites[marker_name].origin,
       mapSprites[marker_name].anchor);
    
    var shadow = new google.maps.MarkerImage(markerImgPath+'map_sprites.png',
       mapSprites['shadow'].size,
       mapSprites['shadow'].origin,
       mapSprites['shadow'].anchor);
    
    marker.setIcon(image);
    marker.setShadow(shadow);
              
}

function unmarkAllUserStands(){
    for(var i in loadedStands){
        if(loadedStands[i].unmarkAsUserStand()){
            isFiltered(loadedStands[i]);
        }
    }
}

function geocode(address, callback){
    geocoder.geocode( {'address': address }, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            callback(results);
        }
    });
}

function reverseGeocode(lat, lng, callback){
        var latlng = new google.maps.LatLng(lat, lng);
        geocoder.geocode({'latLng': latlng}, function(results, status) {
            $.log("geocoder.geocode", results, status);
            if (status == google.maps.GeocoderStatus.OK && results[0]) {
                callback(results);
            }
        });
}

function panAndZoomTo(position){
    map.panTo(position);
    map.setZoom(16);
}

function goTo(bounds){
    map.fitBounds(bounds);
    var newBounds = map.getBounds();
    refreshStands(newBounds.getSouthWest().lat(), newBounds.getNorthEast().lat(), newBounds.getSouthWest().lng(), newBounds.getNorthEast().lng());
}

function getStreetAddress(geolocations){
        ////$.debug("Get street address:");
        for(var j in geolocations){
            
                var arr = jQuery.grep(geolocations[j].types, function(n, i){
                    return (n == "street_address" || n == "route" || n == "intersection");
                });
                
                if(arr.length > 0 && (geolocations[j].geometry.location_type === google.maps.GeocoderLocationType.ROOFTOP || geolocations[j].geometry.location_type === google.maps.GeocoderLocationType.RANGE_INTERPOLATED) ){
                    return geolocations[j];   
                }
        }
        return geolocations[0];
}

function getCityFromAddress(address){
    for(var j in address.address_components){
        var arr = jQuery.grep(address.address_components[j].types, function(n, i){
            return (n == "administrative_area_level_3");
        });
        if(arr.length > 0){
            return address.address_components[j].short_name;
        }
    }
    return -1;
}

/* CUSTOM FEATTURES */
function getPostCode(stand){
        var rxPattern = /[0-9]{5}/;
        var postCode = stand.address.match(rxPattern);
        return postCode != null ? postCode : "-";
}

function tagsCodeToString(tagsCode){
    //$.log("Coded:"+tagsCode);
    var tags = tagsCode.split(" ");
    var textTags = "";
    for(var t in tags){
        //$.log(tags[t]+"::" +Number(tags[t]) +" => " + tagsLabels[Number(tags[t])] );
        if(tags[t] != "" && Number(tags[t]) != NaN && tagsLabels[Number(tags[t])]){
            textTags += "&&"+tagsLabels[Number(tags[t])];
        }
    }
    textTags = textTags.substring(2).replace(/&&/g,", ");
    //$.log("Decoded:"+textTags);
    return textTags;
}


function updateCities(data){
    //$.log("updateCities");
    cities.current = [];
    if(cities.cities === undefined){
        cities.cities = [];
    }
    for(var i in data){
        var p = new google.maps.LatLng( (Number(data[i].u_min)+Number(data[i].u_max))/2.0, (Number(data[i].v_min)+Number(data[i].v_max))/2.0);
        if(cities.cities[data[i].city] === undefined){
            cities.cities[data[i].city] = new Object;
            cities.cities[data[i].city].name = data[i].city;
            $("#cities").append('<li class="'+data[i].city+'"></li>');
            cities.cities[data[i].city].loadedCount = 0;
            cities.cities[data[i].city].updated = 0;
            cities.cities[data[i].city].marker = setMarkers(map, p, "stand");
            cities.cities[data[i].city].marker.city = data[i].city;
            if(map.getZoom() > 10){
                cities.cities[data[i].city].marker.setVisible(false);
            }
            google.maps.event.addListener(cities.cities[data[i].city].marker, 'click', function(){
                focusOnCity(this.city);
            });
        }else{
            cities.cities[data[i].city].marker.setPosition(p);
        }
        cities.cities[data[i].city].marker.setTitle(data[i].city+": "+data[i].count+" "+stringsL10N["stand(s)"]);
        cities.cities[data[i].city].bounds = new google.maps.LatLngBounds(new google.maps.LatLng(data[i].u_min, data[i].v_min), new google.maps.LatLng(data[i].u_max, data[i].v_max) ) 
        cities.cities[data[i].city].count = data[i].count;
        $("#cities li."+data[i].city).html('<b>'+data[i].city+"</b> "+data[i].count);
         $("#cities li."+data[i].city).click(function(){
            focusOnCity($(this).attr('class'));
         });
        cities.current.push(data[i].city);
    }
}

//zoom and pan to show a specific city
function focusOnCity(city){
     goTo(cities.cities[city].bounds);
     $('#map_canvas')[0].scrollIntoView(true);
}

//Return the timestamp of the oldest update of cities data
function getCitiesLastUpdate(){
    var t = Number.MAX_VALUE;
    for(var i in cities.current){
        t = Math.min(t, cities.cities[cities.current[i]].updated);
    }
    return t;
}

// set the cities in bounds as current cities
function setCurrentCityToBounds(bounds){
    cities.current = [];
    for(var i in cities.cities){
        if(cities.cities[i].bounds.intersects(bounds)){
            cities.current.push(cities.cities[i].name);
        }
    }
    return;
}

/*
Get the city which contains the point.
Returns undefined is none is found.
*/
function getCityForPoint(p){
    if(cities.cities === undefined){
        return undefined;
    }
    for(var i in cities.cities){
         if(cities.cities[i].bounds.contains(p)){
            return cities.cities[i];
         }
    }
    return undefined;
}

function isInCurrentCitiesBounds(bounds){
    if(cities.current.length==0){
        return false;
    }
    for(var i in cities.current){
         if(cities.cities[cities.current[i]].bounds.intersects(bounds)){
            return true;
         }
    }
    return false;
}


function removeStand(id){
    delete loadedStands[id];
}



/*
Add loaded stands to the views or update existing stands data.
Returns an array containing all the stands added/updated.
*/
function updateStands(data){
    //$.log("updateStands");
    var city = undefined;
    var newlyLoaded = new Array();
    for(var i in data){
        //padding
        data[i].start_hour = pad(data[i].start_hour, 2);
        data[i].start_minute = pad(data[i].start_minute, 2);
        data[i].end_hour = pad(data[i].end_hour, 2);
        data[i].end_minute = pad(data[i].end_minute, 2);
        if(data[i].tags === ""){
            data[i].tags = "n"; //n = others
        }
       
        var p = new google.maps.LatLng(data[i].u, data[i].v);
        
        if(loadedStands[data[i].id] === undefined || loadedStands[data[i].id] === null){ //the stand is not in the list yet
            loadedStands[data[i].id] = createStand(data[i]);
        }else{
            loadedStands[data[i].id].marker.setPosition(p);
        }
        
         //recycling center tag filtering
        if(loadedStands[data[i].id].isRecyclingCenter() ){
            data[i].tags ="r "+data[i].tags;
        }
        
        var tagString = tagsCodeToString(data[i].tags);
        var timeString = "Avoinna "+data[i].start_hour+":"+data[i].start_minute+" - "+data[i].end_hour+":"+data[i].end_minute;
        var standInfo = '<div class="iWin" data-stand="'+data[i].id+'"><h4>'+data[i].name+'</h4><p>'+data[i].address+'</p><p>'+timeString+'</p><p>'+tagString+'</p>';
        //If the stand as a link to a facebook event
        if(data[i].link.match(/\S/)){
            standInfo += '<a href="'+data[i].link+'" target="_blank">'+stringsL10N["Myyntipaikan Facebook-sivu"]+'</a>';
        }
        standInfo += '<pre>'+data[i].description+'</pre>';
        standInfo += '<button class="btn-red bookmark">'+stringsL10N["Lisää suosikkeihin"]+'</button>';
        standInfo += '</div>';
        
        //update google map info window for the stand
        loadedStands[data[i].id].marker.iWin.setContent(standInfo);
        loadedStands[data[i].id].marker.setTitle(data[i].name+"\n"+timeString+"\n"+data[i].description);
        
        loadedStands[data[i].id].data = data[i];
        loadedStands[data[i].id].data.start_time = hourMinutesToMinutes(Number(data[i].start_hour),Number(data[i].start_minute));
        loadedStands[data[i].id].data.end_time = hourMinutesToMinutes(Number(data[i].end_hour),Number(data[i].end_minute));
        
        //update lastTimestamp if needed
        if(city === undefined){
            city = getCityForPoint(p);
        }else if( !city.bounds.contains(p) ){
            city = getCityForPoint(p);
        }
        
        //update city if needed
        if(city != undefined && city.updated < Number(data[i].modified)){
            city.updated = Number(data[i].modified);
        }
        
        if(city != undefined){
            loadedStands[data[i].id].data.city = city.name;
        }
        
        //Add to the list view
        addToListView(loadedStands[data[i].id]);
        //hide/show based on filters
        isFiltered(loadedStands[data[i].id]);
        
        
        newlyLoaded.push(loadedStands[data[i].id]);
    }
    return newlyLoaded;
}



/* PAN AND ZOOM */    
function focusOnStand(marker){
    ////$.log("focusOn");
    panAndZoomTo(marker.getPosition());
    google.maps.event.trigger(marker, "click");
}

function moveMapToAddress(geoLocation){
    if(!(geoLocation instanceof Array)){
        marker.setPosition(geoLocation.geometry.location);
        marker.setTitle(geoLocation.formatted_address);   
    }else{
        geoLocation = geoLocation[0];
    }
    $('#geocode-form input[name="address"]').val(geoLocation.formatted_address)
    goTo(geoLocation.geometry.viewport);
}


/* Geocoding search */
$('#geocode-form input[type="submit"]').click(function(){
    geocode($('#geocode-form input[name="geocode-address"]').val(), moveMapToAddress);
    return false;
});


/* FILTERING */


//filter by tags
function applyFilter(){
    var filter = $('body').data('filter.tags');
    var patt = new RegExp(filter);
    //$.log("Tag filter: "+filter);
    var start_time = Number($('body').data('filter.start_time'));
    var end_time = Number($('body').data('filter.end_time'));
    
    //hide/show the markers based on the tags
    for(var i in loadedStands){
        var stand = loadedStands[i];
        if(stand.dontShow){ //need to remain hidden, skip
            continue;
        }
        //test if range overlap
        var inTimeRange = ( stand.data.start_time < end_time && stand.data.end_time >= end_time ) || ( stand.data.end_time > start_time && stand.data.end_time <= end_time );
        stand.filter( !(inTimeRange && (filter != "" && patt.test(stand.data.tags))) );
    }
}

//return true if the marker should be filtered
function isFiltered(stand){
    var filter = $('body').data('filter.tags');
    var patt = new RegExp(filter);
    //$.log("Tag filter: "+filter);
    
    var start_time = Number($('body').data('filter.start_time'));
    var end_time = Number($('body').data('filter.end_time'));
    
    //$.log("( "+stand.data.start_time +" <= "+ end_time+" && "+stand.data.end_time+" >= "+end_time +") || ( "+stand.data.end_time+" >= "+start_time+" && "+stand.data.end_time+" <= "+end_time+" )");
    //test if range overlap
    var inTimeRange = ( stand.data.start_time <= end_time && stand.data.end_time >= end_time ) || ( stand.data.end_time >= start_time && stand.data.end_time <= end_time );
    stand.filter( !( !(stand.dontShow) && ( inTimeRange && ( filter != "" && patt.test(stand.data.tags) ) ) ) );
    //return !(stand.dontShow) && ( inTimeRange && ( filter != "" && patt.test(stand.data.tags) ) );
}

//To filter stands
$('#filter .tag-filter-toggle').click(function(){
    setTagsFilter();
    applyFilter();
});

//Reset filters
$('#filter .show-all').click(function(){
    $("#filter .tag-filter-toggle").attr("checked","true");
    setTagsFilter();
    applyFilter();
    return false;
});

$('#filter .deselect-all').click(function(){
    $("#filter .tag-filter-toggle").removeAttr("checked");
    setTagsFilter();
    applyFilter();
    return false;
});

function setTagsFilter(){
    var tags = ""
    $("#filter .tag-filter-toggle:checked").each(function(){
        tags += "|"+$(this).val();
    });
    tags = tags.substring(1);
    $('body').data('filter.tags', tags);
}

$('#times input[type="radio"]').click(function(){
   if( $(this).attr('value') === "all" ){
    $('body').data('filter.start_time', 0);
    $('body').data('filter.end_time',1440);
    //disable select
    $('#times select.time-filter').attr('disabled', 'true');
   }else if($(this).attr('value') == "now"){
        var dateNow = new Date();
        var h = dateNow.getHours();
        var m = dateNow.getMinutes();
        $('body').data('filter.start_time', hourMinutesToMinutes(h, m) );
        $('body').data('filter.end_time', hourMinutesToMinutes(h+1%23, m) );
   }else{
    $('#times select.time-filter').removeAttr('disabled');
    $('body').data('filter.start_time', hourMinutesToMinutes(Number($('#times select[name="sh"]').val()), Number($('#times select[name="sm"]').val())) );
    $('body').data('filter.end_time', hourMinutesToMinutes(Number($('#times select[name="eh"]').val()), Number($('#times select[name="em"]').val())) );
   }
   applyFilter();
});

$('#times select.time-filter').change(function(){
    var sh = Number( $('#times select[name="sh"]').val() );
    var sm = Number( $('#times select[name="sm"]').val() );
    var eh = Number( $('#times select[name="eh"]').val() );
    var em = Number( $('#times select[name="em"]').val() );
    if( !( eh > sh || (eh == sh && em >= sm) ) ){
        //revert to last value
        $(this).val($(this).data('current'));
    }else if($('#times input[type="radio"]:checked').val() == "other"){
        //do the change
        $(this).data('current', $(this).val());
        $('body').data('filter.start_time', hourMinutesToMinutes(sh, sm) );
        $('body').data('filter.end_time', hourMinutesToMinutes(eh, em) );
        applyFilter();
    }
   return false;
});

$('.scrollable').jScrollPane({autoReinitialise: true});

//register default values
$('#times select.time-filter').each(function(){
    $(this).data('current', $(this).val());
});

//disabled by default
$('#times select.time-filter').attr('disabled', '');

//disable now filter if not siivouspaiva
var dateNow = new Date();
var siivousDate = new Date(2012, 04, 12); //!January = 0

if(dateNow.getFullYear() === siivousDate.getFullYear() && dateNow.getMonth() === siivousDate.getMonth() && dateNow.getDate() === siivousDate.getDate()){
    $('#times label.now').show();
}



//manage the list view
function addToListView(stand){
    //$.log("addToListView", stand);
    var listTable = $("#stands_list");
    if( listTable.find("#listStand_"+stand.data.id).length > 0 ){
            //already in the list
            return;
    }
    $("#stands_list").find("tbody").append('<tr id="listStand_'+stand.data.id+'"><td><a href="#" class="showOnMap">'+stand.data.name+'</a><br/><span class="address">'+stand.data.address+'</span></td><td>'+stand.data.start_hour+":"+stand.data.start_minute+'</td><td>'+stand.data.end_hour+":"+stand.data.end_minute+'</td><td>'+getPostCode(stand.data)+'</td><td>'+stand.data.description+'</td><td><a href="#" class="bookmark">'+stringsL10N["Lisää suosikkeihin"]+'</a></td></tr>');
    //default behaviour for bookmark link
    $("#listStand_"+stand.data.id+" a.bookmark").click(function(){
        //$.log("list", stand);
        stand.bookmark(true);
        return false;
    });
    
    //Hide/show based on filters 
    stand.addListener("filter", function(stand){
            $("#listStand_"+stand.data.id).hide();
    });
    stand.addListener("unfilter", function(stand){
            $("#listStand_"+stand.data.id).show();
    });
    
    //Bookmark/unmark callbacks
    stand.addListener("bookmark", function(stand){
        $("#listStand_"+stand.data.id+" a.bookmark").off('click');
        $("#listStand_"+stand.data.id+" a.bookmark").text(stringsL10N["Poista suosikeistasi"]);
        $("#listStand_"+stand.data.id+" a.bookmark").click(function(){
                $.log("list", stand);
                stand.bookmark(false);
                return false;
        });
    });
    stand.addListener("unmark", function(stand){
        $("#listStand_"+stand.data.id+" a.bookmark").off('click');
        $("#listStand_"+stand.data.id+" a.bookmark").text(stringsL10N["Lisää suosikkeihin"]);
        $("#listStand_"+stand.data.id+" a.bookmark").click(function(){
                //$.log("list", stand);
                stand.bookmark(true);
                return false;
        });
    });
    
    //Show the stand on the map view
     $("#listStand_"+stand.data.id+" .showOnMap").click(function(){
        //1) Swicth to map
        $(".views.nav-tabs a:first").click();
        //2) Focus on the stand and open info window
        focusOnStand(stand.marker);
        return false;
     });
    
    $("#stands_list").trigger("update");
}

//views tabs
$(".views.nav-tabs a").click(function(){
    //$.log("Switch");
   if( !$(this).parent().hasClass('active') ){
        $("div.map").toggle();
        $("div.list").toggle();
        $(".views.nav-tabs li").removeClass('active');
        $(this).parent().addClass('active');
   }
   //close the info window if we switched to the list
   if($("div.map").is(':visible') && openedWindow){
        openedWindow.close();
   };
   return false;     
});

//initial state
$('body').data('filter.start_time', 0);
$('body').data('filter.end_time',1440);
setTagsFilter();
applyFilter();
