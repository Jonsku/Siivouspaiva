var sessionStatus = false;

$('#email-login-form').ajaxForm({
    dataType : 'json',
    beforeSubmit: function(arr, form, options) {
        hideError(form);
        var go = false;
        //check validation
        var action = form.find('input[name="a"]').val();
        var type = form.find('input[name="type"]').val();
        /*
        $.log("beforeSubmit");
        $.log("action: ",action);
        $.log("type: ",type);
        */
        if(action === "login"){   
            go = form.validate().element( form.find('input[name="email"]') ) && ( !form.find('input[name="password"]').is(':visible') || form.validate().element( form.find('input[name="password"]') ) );
        }else if(action === "email_login"){
            go = form.validate().element( form.find('input[name="email"]') );
        }else if(action === "register"){
            go = type === 'email' || type === 'invite' ? form.validate().element( form.find('input[name="email"]') ) && form.validate().element( form.find('input[name="password"]') ) && form.validate().element( form.find('input[name="password_verify"]') ) : true;
            go = go && form.validate().element( form.find('input[name="user_name"]') ) && form.validate().element( form.find('input[name="phone"]') );
        }
        return go;
    },
    error: function(jqXHR, textStatus, errorThrown){
            $.log("There was an error.");
            $.log(jqXHR);
            $.log(textStatus);
            return;
    },
    success : function(responseText,statusText,jqXHR){
            

            var action = $('#email-login-form input[name="a"]').val();
            var type = $('#email-login-form input[name="type"]').val();
            $.log("On success");
            $.log("action: ",action);
            $.log("type: ",type);
            
            if(responseText.hasOwnProperty('error')){
                alert(responseText.error);
                return;
            }
            if(action === "login" ){
                if(responseText.success == 0){
                    if(responseText.message === "no user"){
                        $('#email-login-submit').hide();
                        $('#email-login-nomail-submit').hide();
                        $('#login-email-dont-exist').show();
                    }else if(responseText.message === "wrong password"){
                        showError( "password", stringsL10N["Incorrect password"]);
                        $.log("password is incorrect");
                    }else if(responseText.message === "need validation"){
                        alert(stringsL10N["You have not validated your account yet."]);
                    }
                }else{
                    $('#email-cancel-account-btn').click();
                    loggedIn();
                }
            }else if( action === "email_login" ){
                if(responseText.success == 0){
                    if(responseText.message === "need validation"){
                        alert(stringsL10N["This account has not yet been validated."]);
                    }
                    if(responseText.message === "no user"){
                        $('#email-login-submit').hide();
                        $('#email-login-nomail-submit').hide();
                        $('#login-email-dont-exist').show();
                    }
                }else{
                    alert(stringsL10N["We just sent you a mail with a link to login"]);
                    //TODO: remove in prod
                    //$.log(responseText.link);
                }
            }else if( action === "register" ){
                if(responseText.success == 0){
                    if(responseText.message === "need validation"){
                        alert(stringsL10N["This account has not yet been validated."]);
                    }
                    if(responseText.message === "user exists"){
                        alert(stringsL10N["There is already an account for this email address."]);
                        showError( "email", stringsL10N["There is already an account for this email address."]);
                    }
                    if(responseText.message === "passwords mismatch"){
                        showError( "password-verify", stringsL10N["This password does not match the first one."]);
                    }
                }else if(type === "email"){
                    alert(stringsL10N["Thank you! We just sent you a mail"]);
		    $('#email-login-form').hide();
                    //TODO: remove in prod
                    //$.log(responseText.link);
                }else{
                    loggedIn();
                }
            }else{
                $.log(jqXHR);
                $.log(statusText);
                $.log(responseText);
            }
            return;
    }
});

$('#email-login-form').submit(function(e) {
    // prevent normal browser submit and page navigation
    $.log('submit');
   // $(this).ajaxSubmit();
    e.preventDefault();
});

$('#email-login-form').validate({
    onfocusout: false, 
    onkeyup: false, 
    onclick: false,
    onsubmit: false,
    rules: {
        password: {
          minlength: 6
        },
        password_verify: {
         equalTo: "#password"
        }
    },
    errorPlacement: function(error,element) {
        if(element.attr('data-noerror')){
            return false;
        }
        //add a tooltip message over the invalid field
        showError( element.attr('id'), error.text());
        return true;
    },
    submitHandler: function(form) {
        $.log('submitHandler');
        return false;
    }
});

lastEmailInputValue = $('#email-login-form #email').val();
/* Monitor email input box */
setInterval(function(){
    if( $('#email-login-form input[name="a"]').val() != "login" /*|| $('#login-email-exist').is(':visible')*/ ){
        return true;
    }
    var newValue = $('#email-login-form #email').val();
    if(newValue === lastEmailInputValue){
        return true;
    }
    
    lastEmailInputValue = $('#email-login-form #email').val();
    //disable validation error display momentarily
    $('#email-login-form #email').attr('data-noerror',1);
    if( $('#email-login-form').validate().element( $('#email-login-form #email') ) ){
        $.log("check pass");
        $.ajax({
            url: $('#email-login-form').attr("action"),
            type: "POST",
            data: {a: "login", type: "email", email: lastEmailInputValue, password: "0" },
            dataType : 'json',
            error: function(jqXHR, textStatus, errorThrown){
                    $.log("There was an error.");
                    $.log(jqXHR);
                    $.log(textStatus);
                    return;
            },
            success : function(responseText,statusText,jqXHR){
                    $.log(jqXHR);
                    $.log(statusText);
                    $.log(responseText);
                    $.log("monitor");
                    hideError( $('#email-login-form') );
                    if(responseText.success == 0 && responseText.message === "wrong password"){
                        $('#login-email-dont-exist').hide();
                        $('#login-email-exist').show();
                        $('#login-email-exist .forgot-pass-txt').show();
                        $('#email-login-submit').show();
                        $('#email-login-nomail-submit').show();
                    }else{
                        $('#login-email-dont-exist').hide();
                        $('#login-email-exist').hide();
                        $('#login-email-exist .forgot-pass-txt').hide();
                    }
                    return;
            }
        });
    }else{
        $('#login-email-dont-exist').hide();
        $('#login-email-exist').hide();
        $('#login-email-exist .forgot-pass-txt').hide();
    }
    $('#email-login-form #email').removeAttr('data-noerror');
    return true;
}, 375);


/* Buttons handler */
$('#email-login-select').click(function(){
    resetLoginForm();
    
    $('#email-login-form, #email-field, #email-login-submit, #email-login-nomail-submit').show();
});

$('#email-login-submit').click(function(){
    $('#email-login-form input[name="a"]').val("login");
    $('#email-login-form').submit();
    return false;
});

$('#email-login-nomail-submit').click(function(){
    $('#email-login-form input[name="a"]').val("email_login");
    $('#email-login-form').submit();
    return false;
});

$('#email-re-login-btn').click(function(){
    $('#email-login-submit, #email-login-nomail-submit').show();
    $('#login-email-dont-exist').hide();
    $('#email-login-submit').click();
    return false;
});

$('#email-new-account-btn').click(function(){
    $('#login-email-dont-exist').hide();
    $('#login-email-exist .forgot-pass-txt').hide();
    $('#login-email-exist, #register-email, #retype-password, #login-email-exist .register_info').show();
    return false;
});

$('#permission').change(function() {
    $('#register-email .permission_info').toggle($(this).is(':checked'));
});

$('#email-save-account-btn').click(function(){
    $('#email-login-form input[name="a"]').val("register");
    //$('#email-login-form').ajaxSubmit();
    //return false;    
});

$('#email-cancel-account-btn').click(function(){
   //hide and reset the form
   $('#login-email-dont-exist, #login-email-exist .register_info ,#register-email .permission_info, #register-email, #email-login-form').hide();
   $('#email-login-submit').show();
   $('#email-login-form')[0].reset();
   $('#email-login-form input[name="a"]').val("login");
   return false; 
});

$('#logout-btn').click(function(){
    $.ajax({
        url: baseUrl+'/data.php?query=logout',
        type: "POST",
        dataType : 'json',
        error: function(jqXHR, textStatus, errorThrown){
                $.log("There was an error.");
                $.log(jqXHR);
                $.log(textStatus);
                return;
        },
        success : function(responseText,statusText,jqXHR){
            $.log(responseText);
            if(responseText.success != 1){
                $.log(jqXHR);
                $.log(statusText);
            }else{
                //var button = document.getElementById('fb-login');
                fbLogOut();
                /*button.onclick = function() {
                    $.log("Log in to facebook");
                    FB.login();
                };*/
                loggedOut();
            }
            return;
        }
    });
});

function resetLoginForm(){
    //$('#email-login-form').hide();
    $('.fb-register-notice, #register-email .permission_info, #register-email, #login-email-dont-exist, #login-email-exist, #email-login-form,#fb-save-account-btn').hide();
}