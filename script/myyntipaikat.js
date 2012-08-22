var userStands = new Object;
var uStand = undefined;
var special = false;

$("#create-recycle-btn, #create-stand-btn").hide();

createHoursMinutesSelect();
initializeMap();

/* ------------------------- */
/* ------------------------- */
//handle fb events
function loggedIn(){
    sessionStatus = true;
    $(".login-btn").hide();
    resetLoginForm();
    $("#login-text").text(stringsL10N["Myyntipaikkasi tiedot ja suosikkipaikkasi"]);
    

  //get user's stands, if any
  $.ajax({
      type: 'POST',
      url: baseUrl+'/data.php?query=get',
      success: function(data, textStatus, jqXHR){
          $("#user-stands").show();
         
          $('#create-stand-btn').show();
          if(data.stands && data.stands.length > 0){
            $.log("My stands are:", data.stands);
            updateStands(data.stands); //update stands array
            hasStand(data.stands);
            if(createStandOnLoad){
                $('#user-stands')[0].scrollIntoView(true);
            }
          }else if(createStandOnLoad){
            $('#create-stand-btn').click();
          }
          special = data.special === "1";
          if(special){
            $('#create-recycle-btn').show();
          }else{
            $('#create-recycle-btn').hide();
          }
      },
      dataType: "json"
  });
  
  //get user's favorites
  $.ajax({
      type: 'POST',
      url: baseUrl+'/data.php?query=favorites_list',
      success: function(data, textStatus, jqXHR){
        //$.log(data);
        var newlyLoaded = updateStands(data);
        for(var i in newlyLoaded){
          //set the stands as bookmarked
          addBookmark(newlyLoaded[i]);
          newlyLoaded[i].bookmark(true);
        }
      },
      dataType: "json"
  });
  
  $('div.bookmark').show();
  //change the link of print favorites
  $('h3.bookmark a').attr('href','./print_fav.php');
  $('h3.bookmark a').click(function(e){
    e.stopPropagation();
  });
  $('#logout-btn').show();
  $('.login-btn').hide();
}

function loggedOut(){
   // return;
    //$.log("User logged out.");
    sessionStatus = false;
    showAdmin(false);
    $("#stand-info").hide();
    $("#user-stands").hide();
    $('#user-stands .stands li').remove();
    $('#create-stand-btn').hide();
    $('#create-recycle-btn').hide();
    clearBookmarks();
    
    if( $('#user-bookmark').is(':visible') ){
        $('h3.bookmark').click();
    }
    $('div.bookmark').hide();
    $('#logout-btn').hide();
    $('.login-btn').show();
    $("#login-text").text(stringsL10N["Kirjaudu sisään Facebook-tunnuksillasi"]);
    
    //remove edit markers and filter real markers
    destroyUStand();
    unmarkAllUserStands();
}

//
function showAdmin(show){
    if(uStand){
        $.log("Show admin: ", uStand);
        uStand.setDraggable(show);
        if(show){
            uStand.listenerMUP = google.maps.event.addListener(uStand, 'mouseup', function() {
                markerReleased();
            });
            /* Show data in fields */
            $('#stand-admin input[name="name"]').val(uStand.data.name);
            $('#stand-admin input[name="address"]').val(uStand.data.address);
            $('#stand-admin input[name="city"]').val(uStand.data.city);
            $('#stand-admin select[name="sh"]').val(uStand.data.start_hour);
            $('#stand-admin select[name="sm"]').val(uStand.data.start_minute);
            $('#stand-admin select[name="eh"]').val(uStand.data.end_hour);
            $('#stand-admin select[name="em"]').val(uStand.data.end_minute);
            $('#stand-admin textarea').val(uStand.data.description);
            $('#stand-admin input[name="lnk"]').val(uStand.data.link);
            
            //parse tags
            //$('#stand-admin input[name="tags"]').val("");
            var tags = uStand.data.tags.split(" ");
            $('#stand-admin .tag').removeAttr('checked');
            for(var t in tags){
                if($('#stand-admin .tag[value="'+tags[t]+'"]')){
                    $('#stand-admin .tag[value="'+tags[t]+'"]').attr('checked','true');
                }
            }
                
            $('#stand-admin .modify').show();
            $('#stand-admin .modify h2').text(loadedStands[uStand.data.id].isRecyclingCenter() ? stringsL10N["Muokkaa kierrätyspistettäsi"] : stringsL10N["Muokkaa myyntipistettäsi"]);
            $('#stand-admin .create').hide();
            $('#stand-admin #validate').text($('#stand-admin .modify b').text());
            
        }else if(uStand.listenerMUP){
            google.maps.event.removeListener(uStand.listenerMUP);
            uStand.listenerMUP = null;
        }
    }else if(show){
        clearAdmin();
        $("#stand-admin h2").text("Create a stand");
        $('#stand-admin .modify').hide();
        $('#stand-admin .create').show();
        $('#stand-admin #validate').text($('#stand-admin .create b').text());
    }
    $("#stand-admin").toggle(show);

    if(!show){
        var wasCreateForm = $('#stand-admin .create').is(':visible');
        $("#create-stand-btn").toggle( $("#user-stands .stands li[name]").length < 1 ); //hide or show create stand button
        
        if( uStand ){
            $("#stand-info").toggle(true);
            refreshStandInfo(uStand.data);
            uStand.setPosition( new google.maps.LatLng(uStand.data.u, uStand.data.v) );
        }else{
             $("#stand-info").hide();
        }
    }
}

function markerReleased(){
        reverseGeocode(uStand.getPosition().Ua, uStand.getPosition().Va, function(geolocations){
            var address = getStreetAddress(geolocations);
            var mapPos = uStand.getPosition();
            $('#stand-form input[name="u"]').val(mapPos.Ua);
            $('#stand-form input[name="v"]').val(mapPos.Va);
            $('#stand-form input[name="city"]').val(getCityFromAddress(address));
        });
}

/*
 Delete user stand markers.
 Simply init: true => only delete the reference and the drag'n'drop listener
              false => delete the reference, remove from the map, and switch to default marker
*/
function destroyUStand(simplyInit){
    if(uStand){
        if(uStand.listenerMUP){
            google.maps.event.removeListener(uStand.listenerMUP);
            uStand.listenerMUP = null;
        }
        if(uStand.data.id === 0 && !simplyInit)
            uStand.setMap(null);
    }else{
        return;
    }
    $.log("destroyUStand", uStand);
    if(uStand.data && uStand.data.id != 0){
        if(!simplyInit){
            //changeMarkerIcon(uStand, loadedStands[uStand.data.id].isRecyclingCenter() ? "recycle": "stand");
            loadedStands[uStand.data.id].unmarkAsUserStand();
            isFiltered(loadedStands[uStand.data.id]);
        }
    }
    uStand = null;
    uStand = undefined;
}

/*
 Make the given stands editable by the user.
 stands : an array of stand objects
*/
function hasStand(stands){
    for(var i in stands){
        var theStand = loadedStands[stands[i].id];
        if(theStand === undefined || theStand === null){
           continue;
        }
        //hide create button
        if( !theStand.isRecyclingCenter() ) //if not a recycling center, hide the create stand button
            $('#create-stand-btn').hide();
        
       
        //replace regular marker with edit marker and inhibit filtering
        theStand.markAsUserStand();
        theStand.marker.data = theStand.data;
        
        
        appendStandToList(theStand); //show in the user's stand list
    }
}

/*
 Add a stand to the user's stand list
*/
function appendStandToList(theStand){
    if($('#user-stands .stands li[name="'+theStand.data.id+'"]').length > 0 ){
        $('#user-stands .stands li[name="'+theStand.data.id+'"] h3').contents()[1].nodeValue= " "+theStand.data.name;
    }else{
        var li = $('<li name="'+theStand.data.id+'"><h3 class="'+(theStand.isRecyclingCenter() ? "recycle" : "stand")+'"><i class="plus-icon"></i> '+theStand.data.name+'</h3></li>');
        li.click(function(){ //hide/reveal stand details
            if($(this).hasClass("open")){
                //TODO: if admin is open, alert user that the data will be lost
                showAdmin(false);
                $('#stand-info').hide();
                $(this).find('i').removeClass('minus-icon').addClass('plus-icon');
            }else{
                $('#user-stands .stands li.open').click(); //close all the other ones
                destroyUStand(true); 
                uStand = loadedStands[$(this).attr('name')].marker;
                uStand.data = loadedStands[$(this).attr('name')].data;
                attachStandEdit($(this));
                showAdmin(false); //show stand info
                $(this).find('i').removeClass('plus-icon').addClass('minus-icon');
                this.scrollIntoView(true);
            }
            $(this).toggleClass("open");
        });
        $('#user-stands .stands').append(li);
    }
    refreshStandInfo(theStand.data);
}

/*
 Update the information displayed for a user stand
*/
function refreshStandInfo(standData){
    var tags = standData.tags.split(" ");
    var textTags = "";
    for(var t in tags){
            textTags += " "+tags[t];
    }
    textTags = tagsCodeToString(textTags);
    $('#stand-link').text(standData.link);
    $('#stand-link').attr('href',standData.link);
    $('#stand-description').text(standData.description);
    $('#stand-name').text(standData.name);
    $('#stand-address').text(standData.address);
    $('#stand-opening-hours').text(standData.start_hour+":"+standData.start_minute+" - "+standData.end_hour+":"+standData.end_minute);
    $('#stand-tags').text(textTags);
    $('#info-btn button.delete').text( loadedStands[standData.id].isRecyclingCenter() ? stringsL10N["Poista kierrätyspiste"] : stringsL10N["Poista myyntipaikka"]);
}

/* clear stand creation form */
function clearAdmin(){
    $('#stand-admin input[name="name"]').val("");
    $('#stand-admin input[name="address"]').val("");
    $('#stand-admin input[name="city"]').val("");
    $('#stand-admin input[name="u"]').val("");
    $('#stand-admin input[name="v"]').val("");
    $('#stand-admin select[name="sh"]').val(8);
    $('#stand-admin select[name="sm"]').val(0);
    $('#stand-admin select[name="eh"]').val(16);
    $('#stand-admin select[name="em"]').val(0);
    $('#stand-admin textarea').val("");
    $('#stand-admin input[name="tags"]').val("");
    //$('#stand-admin input.tag').removeAttr('checked');
    $("#stand-admin input.tag").prop("checked", false);
    $('#stand-admin input[name="lnk"]').val("");
}



function selectStandFromList(standId){
    $('#user-stands .stands li[name="'+standId+'"]').removeClass("open").click();
}

function attachStandEdit(elem){
    elem.after($('#stand-editing'));
}

function detachStandEdit(){
    $('#user-stands .stands').before($('#stand-editing'));
}

//geocode stand address
$("#pin-btn").click(function(){
    geocode($('#stand-form input[name="address"]').val(), function(geoLocations){
        $.log(geoLocations);
        var address = getStreetAddress(geoLocations);
        if(address == -1){
            alert(stringsL10N["This is not a street address."]);
            return;
        }
        if(!uStand){
            //Create a temporary marker
            uStand = setMarkers(map, address.geometry.location, $("input.tag.recycling_center").prop("checked") ? "recycle_edit" : "stand_edit");
            uStand.data = {id: 0};
            uStand.setDraggable(true);
            uStand.listenerMUP = google.maps.event.addListener(uStand, 'mouseup', function() {
                markerReleased();
            });
        }else{
            uStand.setPosition(address.geometry.location);
        }
        //$('#stand-form input[name="u"]').val(uStand.getPosition().Ua);
        //$('#stand-form input[name="v"]').val(uStand.getPosition().Va);
        $('#stand-form input[name="address"]').val(address.formatted_address);
        
        var city = $.trim( getCityFromAddress(address) );
        
        
        if(city.indexOf("-1") >= 0){ //not city found
            $.log("Not city found");
            //try to reverse geocode the city
            reverseGeocode(uStand.getPosition().lat(), uStand.getPosition().lng(), function(possibleCities){
                $.log("try to reverse geocode the city...", possibleCities);
                var address = getStreetAddress(possibleCities);
                var city = $.trim( getCityFromAddress(address) );
                if(city.indexOf("-1") >= 0){
                    alert("We could not find a city for this address, we will assume it is in Helsinki.");
                }else{
                    $('#stand-form input[name="address"]').val(address.formatted_address);
                    $('#stand-form input[name="city"]').val(city); 
                }
                return true;
            });
        }else{
            $('#stand-form input[name="city"]').val(city);    
        }
        
        
        panAndZoomTo(uStand.getPosition());
        uStand.setTitle(stringsL10N["Your stand is at: "]+address.formatted_address);
        $('#map_canvas')[0].scrollIntoView(true);
    });
    return false;
});


//center map on stand
$('#stand-info a.btn').click(function(){
    focusOnStand(uStand);
    $('#map_canvas')[0].scrollIntoView(true);
    return false;
});

/* Update description char count when typing */
$('#stand-admin textarea').keyup(function(){
    if($('#stand-admin textarea').val().length > 200){
        var txt = $('#stand-admin textarea').val();
        $('#stand-admin textarea').val(txt.substring(0,200));
        return false;
    }else{
        $('#char-count').text($('#stand-admin textarea').val().length + " / 200 "+stringsL10N["merkkiä"]);
        return true;
    }
});


// CREATE
$('#create-stand-btn').click(function(){
    $('#user-stands .stands li.open').click(); //close all the other ones
    detachStandEdit();
    showAdmin(true);
    destroyUStand(true);
    //no stand for this user
    $('#stand-admin .create').show();
    $('#stand-admin .modify').hide();
    $("#stand-admin h2").text(stringsL10N["Lisää myyntipaikka"]);
    $('#stand-admin #validate').text($('#stand-admin .create b').text());
    clearAdmin();
    $(this).hide();
    if( $('#user-bookmark').is(':visible') ){
        $('h3.bookmark').click();
    }
    $("#stand-editing")[0].scrollIntoView(true);
});

$('#create-recycle-btn').click(function(){
    $('#user-stands .stands li.open').click(); //close all the other ones
    detachStandEdit();
    
    showAdmin(true);
    destroyUStand(true);
    $('#stand-admin .create').show();
    $('#stand-admin .modify').hide();
    $("#stand-admin h2").text(stringsL10N["Lisää kierrätyspiste"]);
    $('#stand-admin #validate').text($('#stand-admin .create b').text());
    clearAdmin();
    
    //tag as recycling center
    $("input.tag.recycling_center").prop("checked", true);
    if( $('#user-bookmark').is(':visible') ){
        $('h3.bookmark').click();
    }
    $("#stand-editing form")[0].scrollIntoView(true);
});

//open form to  modify
$('#info-btn .modify').click(function(){
    showAdmin(true);
    $("#stand-info").hide();
});

//Cancel modification/creation
$('#stand-admin button.s-close').click(function(){
    //if cancel stand creation
    if($('#stand-admin .create').is(':visible')){
        destroyUStand();
    }
    showAdmin(false);
    $('#create-stand-btn').show();
    return false;
});


/* delete stand */
$('#info-btn .delete').click(function(){
    $('#info-btn').hide(300);
    $('#confirm-delete').show();
    return false;
});

$('#confirm-delete .yes').click(function(){
    var data= {
        i: uStand.data.id
     };
    $.log("Destroy stand: ",data.i);
    $.ajax({
        type: 'POST',
        url: baseUrl+'/data.php?query=delete',
        dataType: "json",
        
        data: data,
        success: function(data, textStatus, jqXHR){
            if(data.error){
                alert("Error:"+data.error);
            }else{
                //remove the stand from the list
                $('#user-stands .stands li[name="'+uStand.data.id+'"]').remove();
                //delete userStands[uStand.data.id];
                removeStand(uStand.data.id);
                uStand.setMap(null);
                uStand = null;
                uStand = undefined;
                $('#confirm-delete').hide();
                $('#info-btn').show();
                showAdmin(false);
                refreshAllCities();
            }
            return;
        }
    });
    return false;
});

$('#confirm-delete .no').click(function(){
    $('#confirm-delete').hide();
    $('#info-btn').show();
    return false;
});

//form validation with jQuery plugin
$("#stand-form").validate({
    onfocusout: false, 
    onkeyup: false, 
    onclick: false, 
    rules: {
        name: { required: true},
        address: { required: true },
        city: { required: true },
        desc: { required: true, maxlength: 200 },
        lnk: {url: true}
    },
    messages: {
        name: {
          required: stringsL10N["Tämä kenttä on pakollinen."]
        },
        address: {
          required: stringsL10N["Tämä kenttä on pakollinen."]
        },
        city: {
          required: stringsL10N["Paina nappia <i class=\"icon-search\"></i> vahvistaaksesi osoitteen."]
        },
        desc: {
            required: stringsL10N["Tämä kenttä on pakollinen."],
            maxlength: stringsL10N["Kuvaus voi olla korkeintaan 200 merkkiä pitkä."]
        },
        lnk: {
            url: stringsL10N["Osoite on virheellinen."]
        }
    },
    errorPlacement: function(error,element) {
                    //add a tooltip message over the invalid field
                    showError( element.attr('id') != 'city' ? element.attr('id') : 'address', element.attr('id') != 'city' ? error.text() :  stringsL10N["Paina nappia <i class=\"icon-search\"></i> vahvistaaksesi osoitteen."]);
                    return true;
                },
    submitHandler: function(form){
        /* Validate form */
        var errors = new Array();
        if( uStand === undefined || uStand === null){
            errors.push({field:"address", msg:stringsL10N["Paina nappia <i class=\"icon-search\"></i> vahvistaaksesi osoitteen."]});
        }
        
        var s = parseInt($('#stand-admin select[name="sh"]').val());
        var e = parseInt($('#stand-admin select[name="eh"]').val());
        if(s > e){
            errors.push({field:"sh", msg:stringsL10N["Sulkemisajan täytyy olla myöhemmin kuin avaamisajan."]});
        }else if(s == e){
            s = parseInt($('#stand-admin select[name="sm"]').val());
            e = parseInt($('#stand-admin select[name="em"]').val());
            if(s >= e){
                errors.push({field:"sh", msg:stringsL10N["Sulkemisajan täytyy olla myöhemmin kuin avaamisajan."]});    
            }
        }
       // $('#stand-admin input[name="tags"]').val("");
        
        if(errors.length > 0){
            ////$.log("Form invalid.");
            ////$.log(errors);
            showErrors(errors);
            return false;
        }
        
        /* Process tags */
        $('#stand-admin input[name="tags"]').val("");
        $("#stand-admin .tag:checked").each(function(){
            $('#stand-admin input[name="tags"]').val($('#stand-admin input[name="tags"]').val()+$(this).val()+" ");
        });
        
        //$.log("Form valid.");
         var data= {
            name: urlencode($('#stand-admin input[name="name"]').val()),
            address: urlencode($('#stand-admin input[name="address"]').val()),
            city: urlencode($('#stand-admin input[name="city"]').val()),
            u: urlencode(uStand.getPosition().lat()),
            v: urlencode(uStand.getPosition().lng()),
            st: urlencode($('#stand-admin select[name="sh"]').val()+":"+$('#stand-admin select[name="sm"]').val()),
            et: urlencode($('#stand-admin select[name="eh"]').val()+":"+$('#stand-admin select[name="em"]').val()),
            desc: urlencode($('#stand-admin textarea').val()),
            t: urlencode($('#stand-admin input[name="tags"]').val()),
            lnk: urlencode($('#stand-admin input[name="lnk"]').val())
         };
         $.log("Update/create", $("input.tag.recycling_center").prop("checked") ? "Recycling center" : "Stand");
         $.log("Data", data);
        
        $.ajax({
            type: 'POST',
            url: $("input.tag.recycling_center").prop("checked") ? baseUrl+'/data.php?query=add_recycle' : baseUrl+'/data.php?query=add',
            data: data,
            error: function(jqXHR, textStatus, errorThrown){
                $.log("There was an error.");
                $.log(jqXHR);
                $.log(textStatus);
                return;
            },
            success: function(data, textStatus, jqXHR){
                if(data.error !== undefined){
                    alert("Error:"+data.error);
                }else{
                    //WARNING: updateStands can return an empty array (try creating stand with address Brooklyn, NY for instance)
                    var res = updateStands(data);
                    $.log("Updated/created stand:", res);
                    refreshAllCities();
                    refreshStandInfo(res[0].data);
                    hasStand(data);
                    if($('#stand-admin .create').is(':visible')){ //remove the temporary marker
                       destroyUStand();
                    }
                    selectStandFromList(res[0].data.id);
                }
            },
            dataType: "json"
        });
        return false;
    }
});

$('#stand-admin #validate').click(function(){
    hideError($('#stand-admin'));
});

function clearBookmarks(){
    $('#user-bookmark div[data-stand]').each(function(){
       var stand = loadedStands[$(this).attr('data-stand')];
       stand.bookmark(false);
    });
    $('#user-bookmark div').remove();
}

//Save bookmark to db
function bookmark(stand){
    if(!sessionStatus){
        return false;
    }
    if( $('#user-bookmark div[data-stand='+stand.data.id+']').length > 0){ //already bookmarked
        return false;
    }
    
    var data= {
        stand_id: urlencode(stand.data.id)
    };
    
   //$.log(data);
   $.ajax({
       type: 'POST',
       url: baseUrl+'/data.php?query=add_favorite',
       data: data,
       error: function(jqXHR, textStatus, errorThrown){
            $.log("There was an error.");
            $.log(jqXHR);
            $.log(textStatus);
            return;
        },
       success: function(data, textStatus, jqXHR){
           if(data.error !== undefined){
               alert("Error:"+data.error);
           }else{
            //$.log(data);
               var theStand = updateStands([data]);
               refreshAllCities();
               hasStand(data);
               addBookmark(theStand[0]);
           }
       },
       dataType: "json"
   });
}

//add a bookmark to the list
function addBookmark(stand){
    var exist = $('#user-bookmark div[data-stand='+stand.data.id+']').length>0;
    if(exist){ //bookmark already exist
        var mark = $('#user-bookmark div[data-stand='+stand.data.id+']');
        mark.empty();
    }else{
        var mark = $('<div data-stand="'+stand.data.id+'" class="row"></div>');
    }
    mark.append('<div class="span12"><div class="row">'+
                '<div class="span4"><i></i>'+stand.data.name+"<br/>"+stand.data.start_hour+":"+stand.data.start_minute+" - "+stand.data.end_hour+":"+stand.data.end_minute+'</div>'+
                '<div class="span4">'+stand.data.address+'</div>'+
                '<div class="span4"><pre>'+stand.data.description+'</pre></div>'+
                '</div></div>');
    if(!exist){
        $('#user-bookmark').append(mark);
    }
    mark.click(function(){
        focusOnStand(stand.marker);
        $('#map_canvas')[0].scrollIntoView(true);
        return true;
    });
    mark.find('i').click(function(){
        stand.bookmark(false);
        return false;
    });
   
    return;
}

//Remove bookmark from db
function unmark(stand){ 
    if(!sessionStatus){
        return false;
    }
    $.log("deleteBookmark", stand.data.id);
    if($('#user-bookmark div[data-stand='+stand.data.id+']').length==0){
        return false;
    }
    
    var data= {
        stand_id: urlencode(stand.data.id)
    };
   //$.log(data);
   $.ajax({
       type: 'POST',
       url: baseUrl+'/data.php?query=delete_favorite',
       data: data,
       error: function(jqXHR, textStatus, errorThrown){
            $.log("There was an error.");
            $.log(jqXHR);
            $.log(textStatus);
            return;
        },
       success: function(data, textStatus, jqXHR){
           if(data.error !== undefined){
               alert("Error:"+data.error);
           }else{
               $('#user-bookmark div[data-stand='+data.success+']').remove();
           }
       },
       dataType: "json"
   });
   return true;
}