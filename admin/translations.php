<?php
ini_set("session.save_path","../session/");
session_start();
$_SESSION['admin'] = 1;
$page = "Staff Only!";

//contains an include of l10n.php
include("../inc/header.php");

$files = array();
$strings = array();
$currentFile = "";
$currentLocale = "fi_FI";
try {
        $db = new PDO('sqlite:'.dirname(__FILE__).'/../db/l10n.db');
        $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $files = getFiles($db);
        if(sizeof($files) > 0){
          $currentFile = $files[0];
          $strings = getStringsForFile($db, $currentFile, $currentLocale);
        }
}catch(PDOException $e){
  $db = NULL;
}
?>
  </head>

  <body>
    <!-- header starts -->
    <?php include("../inc/navbar.php"); ?>
    <!-- navbar ends -->
    <div id="content">
      <!-- content starts -->
      <div class="container">
        <div class="row">
          <div class="span12">
            <form id="translation-form" method="POST" class="form-horizontal" action="<?php echo $config['paths']['base_url']; ?>/l10n.php?l10n=fileStrings">
            <fieldset>
              <div class="control-group">
                <label for="fileName">Pages</label>
                <div class="controls">
                  <select name="fileName" id="fileName">
                  <?php foreach($files as $file){ ?>
                    <option value="<?php echo $file; ?>"><?php echo $file; ?></option>
                  <?php } ?>
                  </select>
                </div>
              </div>
              
              <div class="control-group">
                <label for="locale">Language</label>
                <div class="controls">
                  <select name="locale" id="locale">
                    <?php foreach($localeToLanguage as $locale => $language){ ?>
                      <option value="<?php echo $locale; ?>"><?php echo $language; ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>
              <button class="btn-red" id="show-translations-btn">Show</button>
            </fieldset>
          </form>
        </div>
      </div>
      <?php
        if($currentFile != ""){
      ?>
      <h3 style="color:#000000">Translations for file <b id="current_file"><?php echo $currentFile ?></b> (<b id="current_locale"><?php echo $localeToLanguage[$currentLocale] ?></b>).</h3>
      <hr/>
      <div class="row">
        <table class="span12" id="rosetta">
          <thead>
            <tr><th>Default</th><th>Translation</th><th>Actions</th></tr>  
          </thead>
          <tbody>
            <?php foreach($strings as $string){ ?>
              <tr id="<?php echo $string['hash']; ?>"><td><?php echo $string['value']; ?></td><td><textarea class="translation"><?php echo $string['locale_translation']; ?></textarea></td><td class="actions"><button class="save" disabled="disabled">Save</button></tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
      <?php
        }else{
      ?>
       <h3 style="color:#000000">Translations for file <b id="current_file"></b> (<b id="current_locale"></b>).</h3>
      <hr/>
      <div class="row">
        <table class="span12" id="rosetta">
          <thead>
            <tr><th>Default</th><th>Translation</th><th>Actions</th></tr>  
          </thead>
          <tbody>
          </tbody>
        </table>
      <?php } ?>
    </div>
    <script type="text/javascript" src="<?php echo $config['paths']['base_url']; ?>/script/jquery.form.js"></script>
    <script type="text/javascript">
    localeToLanguage = <?php echo json_encode($localeToLanguage); ?>;
    currentFile = "<?php echo $currentFile; ?>";
    currentLocale = "<?php echo $currentLocale; ?>";
    
    $("textarea.translation").keyup(function(){
        //$.log("Text changed");
        $(this).parents("tr").find("button.save").removeAttr("disabled");
        return true;
    });
    
    $('#translation-form').ajaxForm({
        dataType : 'json',
        beforeSubmit: function(arr, form, options) {
          if($('button.save:not(:disabled)').length > 0){
            return confirm("One or more translations have been modified but not saved.\nIf you continue you will loose all the unsaved changes.\nAre you sure you want to proceed?");
          }
          return true;
        },
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
                if( responseText.hasOwnProperty('strings') ){
                    clearTranslationTable(responseText.strings, currentFile != $('#fileName option:selected').val() );
                    for(var i in responseText.strings){
                      appendTranslation( responseText.strings[i] );
                    }
                    $.log(responseText.strings);
                    currentFile = $('#fileName option:selected').val();
                    currentLocale = $('#locale option:selected').val();
                    $('#current_file').text(currentFile);
                    $('#current_locale').text(localeToLanguage[currentLocale]);
                    return;
                }
                return;
        }
    });
    
    $('#translation-form').submit(function(e) {
      // prevent normal browser submit and page navigation
      $.log('submit');
      $(this).ajaxSubmit();
      e.preventDefault();
    });
    
    $("button.save").click(function(){
      saveTranslation($(this).parents("tr")[0].id);
      return false;
    })
    
    function saveTranslation(trId){
      $.ajax({
        dataType : 'json',
        url: "<?php echo $config['paths']['base_url']; ?>/l10n.php?l10n=saveTranslation",
        type: "POST",
        data: {string: trId, translation: $("#"+trId+" textarea.translation").val(), locale: currentLocale },
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
                $("#"+trId+" button.save").attr("disabled", "disabled");
                return;
        }
      });
    }
    
    function clearTranslationTable(leaveOnly, removeLine){
      $("#rosetta tbody tr").each(function(){
        var remove = true;
        var idToMatch = $(this)[0].id;
        for(var i in leaveOnly){
          if(idToMatch === leaveOnly[i].hash){
            remove = false;
            break;
          }
        }
        if(remove){
          if(removeLine){
            $(this).remove();
          }else{
            $(this).find('textarea.translation').val("");
            $(this).find('button.save').attr("disabled","disabled");
          }
        }
      });
    }
    
    function appendTranslation(translationObject){
      //no need to create a row if the string is already there, let's just update the editor
      if($('#'+translationObject.hash).length > 0){
        $('#'+translationObject.hash+" textarea.translation").val(translationObject.locale_translation);
        $('#'+translationObject.hash+" button.save").attr("disabled","disabled");
        //done
        return;
      }
      var translation = $('<tr id="'+translationObject.hash+'"><td>'+translationObject.value+'</td><td><textarea class="translation">'+translationObject.locale_translation+'</textarea></td><td class="actions"><button class="save" disabled="disabled">Save</button></tr>');
      $("#rosetta tbody").append(translation);
      translation.find("button.save").click(function(){
        saveTranslation($(this).parents("tr")[0].id);
        return false;
      });
      translation.find("textarea.translation").keyup(function(){
        $.log("Text changed");
        $(this).parents("tr").find("button.save").removeAttr("disabled");
        return true;
      });
    }
    
    </script>
  </body>
</html>