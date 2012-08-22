/* Form validation error handlers */
function hideError(form){
    //$('#stand-admin .add-on').hide();
    form.find('.tooltip-trigger').tooltip('hide');
    form.find('.tooltip-trigger').tooltip('disable');
    form.find('.tooltip-trigger').removeClass('tooltip-trigger');
    form.find('.error').removeClass("error");
}

function showErrors(errors){
    for(error in errors){
        showError(errors[error].field, errors[error].msg);
    }
}

function showError(field, error){
    $.log("showError", field, $('#'+field));
    $('#'+field).tooltip(/*{title: error}*/);
    $('#'+field).data('tooltip').options.title = error;
    $('#'+field).tooltip('show');
    $('#'+field).tooltip('enable');
    $('#'+field).addClass("tooltip-trigger");
    $('#'+field).parents(".control-group").addClass("error");
}