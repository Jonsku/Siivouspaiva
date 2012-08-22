<?php
    header("Content-type: application/x-javascript");
    $config = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/siivouspaiva.ini", true);
    if($config['stands']['archive']){
?>
    function getUserID(){
       return 0;
    }
    var loginRow = document.getElementById('login_row');
    loginRow.parentNode.removeChild(loginRow);
    alert("NOTE: You are looking at the archive.\n You can only browse the stands, you won't be able to add or edit stands.");
<?php
    return;}
?>

function getFBID(){
    return <?php echo $config['facebook']['fb_id_function']; ?>;
}

window.fbAsyncInit = function() {
  FB.init({ appId: '<?php echo $config['facebook']['app_id']; ?>', // App ID
            channelUrl: '<?php echo $config['paths']['base_url']; ?>/channel/channel.php', // Channel File
	    status: true, 
	    cookie: true,
	    xfbml: true,
	    oauth: true});
  

  // run once with current status and whenever the status changes
  //FB.getLoginStatus(fbStatusChanged);
  $("#contact-fb-msg").hide();
  if(!sessionStatus){
    $("#check-fb-msg").show();
    clearTimeout(fbOutOfService);
  }
  $("#no-fb-msg").hide();
  setTimeout(function(){ if($("#check-fb-msg").is(':visible')){ $("#no-fb-msg").show(); } }, 30000);
  FB.getLoginStatus(function(response){fbLoginStatus(response, true);});
};

function fbLoginStatus(response, firstCall){
    $.log("fbLoginStatus",response);
    //hide Facebook check status message
    $("#check-fb-msg").hide();
    $("#no-fb-msg").hide();
    if(response.status === "connected"){
	$.ajax({
	    url: $('#email-login-form').attr("action"),
	    type: "POST",
	    data:{a:"login", type:"fb", fb_id:response.authResponse.userID},
	    dataType : 'json',
	    error: function(jqXHR, textStatus, errorThrown){
		    $.log("There was an error.");
		    $.log(jqXHR);
		    $.log(textStatus);
		    return;
	    },
	    success : function(responseText,statusText,jqXHR){
		$.log("Check fb user", responseText);
		if(responseText.success == 0){
		    if(responseText.message === "no user"){
			$("#fb-login").off('click');
			$("#fb-login").show();
			$("#fb-login").click(function() {
			    //$('.fb-register-notice').show();
			    $('#email-login-form').show();
			    //$('#login-email-exist').show();
			    $('#register-email').show();
                            $("#email-field").hide();
                            $("#retype-password").hide();
			    $('#email-login-submit').hide();
			    $('#email-login-nomail-submit').hide();
			    $('#login-email-dont-exist').hide();
			    $('#login-email-exist .forgot-pass-txt').hide();
			    $('#email-login-form input[name="type"]').val("fb");
			    $('#email-login-form input[name="fb_id"]').val(getFBID());
			    //$.log("FB id is set:", getFBID());
			});
			if(!firstCall){
			    $("#fb-login").click();
			}
		    }else{
			$.log(responseText.message);
		    }
		}else if(responseText.success == 1){
		    $.log("logged in");
		    loggedIn();
		}
	    }
	});
    }else if(firstCall){
	$("#fb-login").off('click');
        if(!sessionStatus){ //only show if not already logged in
            $("#fb-login").show();
        }else{
            $("#fb-login").hide();
        }
	$("#fb-login").click(function() {
	    $.log("Log in to facebook");
	    resetLoginForm();
	    FB.login();
	});
    }
    
    if(firstCall){
	FB.Event.subscribe('auth.authResponseChange', function(response) {
	    //$.log('The status of the session changed to: '+response.status);
	    fbLoginStatus(response, false);
	});
    }
    return;
}


function fbLogOut(){
    FB.getLoginStatus(function(response) {
	if (response.status === 'connected') {
	  FB.logout();
	  var button = document.getElementById('fb-login');
	  button.onclick = function() {
		$.log("Log in to facebook");
		resetLoginForm();
		FB.login();
	    };
	}
    });
}


fbOutOfSrevice = undefined;
$("#check-fb-msg").hide();
$("#no-fb-msg").hide();
  
(function() {
  var e = document.createElement('script'); e.async = true;
  e.src = document.location.protocol 
    + '//connect.facebook.net/en_US/all.js';
  document.getElementById('fb-root').appendChild(e);
  if(typeof sessionStatus === 'boolean' && !sessionStatus){
    fbOutOfService = setTimeout(function(){ $("#contact-fb-msg").hide(); $("#no-fb-msg").show();  }, 5000);
  }else{
    $("#contact-fb-msg").hide();
  }
}());