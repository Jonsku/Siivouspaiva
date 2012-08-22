<?php
ini_set("session.save_path","../session/");
session_start();
$_SESSION['admin'] = 1;
$page = "Staff Only!";
include("../inc/header.php");
?>
    <link type="text/css" href="<?php echo $config['paths']['base_url']; ?>/css/jquery.jscrollpane.css" rel="stylesheet" media="all" />
    <!-- Table sorter plugin -->
    <script type="text/javascript" src="<?php echo $config['paths']['base_url']; ?>/script/jquery.tablesorter.min.js"></script>
    <script type="text/javascript" src="<?php echo $config['paths']['base_url']; ?>/script/jquery.jscrollpane.min.js"></script>
    <!-- Google Map API init -->
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=<?php echo $config['googlemap']['api_key']; ?>&sensor=true"></script>
    <!-- TinyMCE -->
    <script type="text/javascript" src="<?php echo $config['paths']['base_url']; ?>/script/tiny_mce/jquery.tinymce.js"></script>    
  </head>

  <body>
    <!-- header starts -->
    <?php include("../inc/navbar.php"); ?>
    <!-- navbar ends -->
    
    <!-- content starts -->
    <div class="container">
        <?php include("../inc/map.php"); ?>
        <!-- Stands -->
        <div class="row">
            <h2>Manage stands</h2>
            <hr>
            <div class="span12 inset">
                <table id="stands-table" class="table-striped">
                    <thead>
                        <tr>
                          <th></th>
                          <th>Owner Info</th>
                          <th>Stand Name</th>
                          <th>Address</th>
                          <th>Opening hours</th>
                          <th>Description</th>
                          <th>Tags</th>
                          <th>Facebook event</th>
                          <th>Created/Modified</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
                <button class="btn" id="delete-stands">Delete selected</button>
            </div>
        </div>
    
        <!-- Specials -->
        <div class="row">
            <h2>Invite recycling centers, manage priviledges</h2>
            <hr>
            <div class="span12 inset">
                <form id="invite-form" method="post" action="<?php echo $config['paths']['base_url']; ?>/data.php?query=invite">
                    <h2>Invite a recycling center</h2>
                    <label for="email">Email address to send the invitation to:</label>
                    <input type="text" name="email" val=""/>
                    <button type="submit" class="btn btn-primary">Send invitation</button>
                </form>
    
                <table id="specials-table" class="table-striped">
                    <thead>
                        <tr>
                          <th></th>
                          <th>Status</th>
                          <th>Email</th>
                          <th>Facebook account</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
                <button class="btn" id="delete-specials">Delete selected</button>
            </div>
        </div>
    
        <!-- News -->
        <div class="row">
            <h2>Add, edit news</h2>
            <hr>
            <div class="span12 inset">
                <table id="news-table" class="table-striped">
                    <thead>
                        <tr>
                          <th></th>
                          <th></th>
                          <th>Date</th>
                          <th>Title</th>
                          <th>Authors</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
                <button class="btn" id="create-news">Create a new entry</button>
                
                <form id="news-form" method="post" action="#">
                    <input type="hidden" name="news-id" val="-1"/>
                    <label>Title <input type="text" name="news-title"/></label>
                    <label>Author(s) <input type="text" name="news-authors"/></label>
                    <textarea id="text-editor">
                    </textarea>
                    <button id="save-news" class="btn">Save</button><button id="cancel-news-edit" class="btn">Cancel</button>
                </form>
            </div>
        </div>
    </div>
    <!-- Script -->
    <script type="text/javascript">
      /* Localized Strings */
      stringsL10N = new Array();
      <?php include_once("../l10n_map.php"); ?>
    </script>
    <script type="text/javascript" src="<?php echo $config['paths']['base_url']; ?>/script/standmap.js"></script>
    <script type="text/javascript">
    
    $('#text-editor').tinymce({
        // Location of TinyMCE script
        script_url : '<?php echo $config['paths']['base_url']; ?>/script/tiny_mce/tiny_mce.js',
        // General options
        mode : "exact",
        //elements : "text-editor",
        theme : "advanced",
        plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,preview,media,searchreplace,print,contextmenu,paste,directionality,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave,ibrowser",

        // Theme options
        theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,preview,ibrowser",
        //theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
        //theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,
        height : "500",
        // Example content CSS (should be your site CSS)
        content_css : "<?php echo $config['paths']['base_url']; ?>/css/news.css",
        add_form_submit_trigger : false,
        //make path to image files absolute
        relative_urls : false, 
        remove_script_host : true,
        document_base_url : "<?php echo $config['paths']['base_url']; ?>",
        //save_callback : "myCustomSaveContent",
        
        // Drop lists for link/image/media/template dialogs
        /*
        template_external_list_url : "lists/template_list.js",
        external_link_list_url : "lists/link_list.js",
        external_image_list_url : "lists/image_list.js",
        media_external_list_url : "lists/media_list.js",
       */
        // Style formats
        /*
        style_formats : [
                {title : 'Bold text', inline : 'b'},
                {title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
                {title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
                {title : 'Example 1', inline : 'span', classes : 'example1'},
                {title : 'Example 2', inline : 'span', classes : 'example2'},
                {title : 'Table styles'},
                {title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
        ],
        */
        // Replace values for the template plugin
        /*
        template_replace_values : {
                username : "Some User",
                staffid : "991234"
        }
      */
    });
</script>
<!-- /TinyMCE -->
    
    
    
    <script type="text/javascript">
        
        function loadNews(){
            $.ajax({
                type: 'POST',
                url: '<?php echo $config['paths']['base_url']; ?>/data.php?query=news_list',
                data: data,
                success: function(data, textStatus, jqXHR){ //$.log("Ajax post result: "+textStatus); //$.log(data);
                    if(data.error){
                        alert("Error:"+data.error);
                    }else{
                        updateNewsList(data);
                    }
                },
                dataType: "json"
            });
            
        }
        function updateNewsList(data){
            $.log(data);
            for(var i in data){
                //format date
                var date = new Date(Number(data[i].timestamp)*1000);
                $('#news-table tbody').append('<tr id="'+data[i].id+'"><td><button class="btn edit">Edit</button></td><td><button class="btn delete">Delete</button></td><td>'+date+'</td><td>'+data[i].title+'</td><td>'+data[i].authors+'</td><tr>');
            }
            
            $('#news-table button').click(function(){
                if($(this).hasClass("edit")){
                    var toEdit = {id: $(this).parents('tr').attr('id') };
                    $.log(toEdit);
                    $.ajax({
                        type: 'POST',
                        url: '<?php echo $config['paths']['base_url']; ?>/data.php?query=news_items',
                        data: toEdit,
                        success: function(data, textStatus, jqXHR){ //$.log("Ajax post result: "+textStatus); //$.log(data);
                            if(data.error){
                                alert("Error:"+data.error);
                            }else{
                                editNews(data);
                                //$('#news-table tr#'+data.success).remove();
                            }
                        },
                        dataType: "json"
                    });
                }else if($(this).hasClass("delete")){
                    var toDelete = {id: $(this).parents('tr').attr('id') };
                    $.log(toDelete);
                    $.ajax({
                        type: 'POST',
                        url: '<?php echo $config['paths']['base_url']; ?>/data.php?query=delete_news',
                        data: toDelete,
                        success: function(data, textStatus, jqXHR){ //$.log("Ajax post result: "+textStatus); //$.log(data);
                            if(data.error){
                                alert("Error:"+data.error);
                            }else{
                                $('#news-table tr#'+data.success).remove();
                            }
                        },
                        dataType: "json"
                    });
                }
            });
        }
        
        function editNews(data){
            $('#news-form input[name="id"]').val(data.id);
            tinyMCE.getInstanceById("text-editor").setContent(data.content);
            $('#news-form input[name="news-title"]').val(data.title);
            $('#news-form input[name="news-authors"]').val(data.authors);
            $('#news-form').toggle(true);
        }
        
        $('#create-news').click(function(){
            $.log('create news.');
            $('#news-form input[name="id"]').val("-1");
            tinyMCE.getInstanceById("text-editor").setContent("");
            $('#news-form').toggle(true);
        });
        
        //$('#text-editor').setContent();
    //$('#text-editor').getContent();
    
        $('#save-news').click(function(){
                //$.log(urlencode(tinyMCE.getInstanceById("text-editor").getContent()));
                var data = {
                  id: $('#news-form input[name="news-id"]').val(),
                  content: urlencode( tinyMCE.getInstanceById("text-editor").getContent() ),
                  title: urlencode( $('#news-form input[name="news-title"]').val() ),
                  authors: urlencode( $('#news-form input[name="news-authors"]').val() )
                  };
                $.log(data);
                $.ajax({
                        type: 'POST',
                        url: '<?php echo $config['paths']['base_url']; ?>/data.php?query=add_news',
                        data: data,
                        success: function(data, textStatus, jqXHR){ //$.log("Ajax post result: "+textStatus); //$.log(data);
                            if(data.error){
                                alert("Error:"+data.error);
                            }else{
                                $.log("news id:"+data.success);
                            }
                        },
                        dataType: "json"
                 });
                return false;
        });
        
        $('#cancel-news-edit').click(function(){
            $('#news-form input[name="id"]').val("-1");
            tinyMCE.getInstanceById("text-editor").setContent("");
            $('#news-form').toggle(false);
            return false;
        });
        
        loadNews();
    
        function updateStandsList(data){
            $.log("updateStandsList", data);
            for(var i in data){
                //format date
                var date = new Date(Number(data[i].modified)*1000);
                
                //parse tags
                /*
                var textTags = "";
                var tags = data[i].tags.split(" ");
                */
                var textTags = tagsCodeToString(data[i].tags);
                /*
                for(var t in tags){
                    textTags += "&&"+$('#tags-labels .tag[name="'+tags[t]+'"]').text();
                }*/
                textTags = textTags.substring(2).replace(/&&/g,", ");
                var ownerInfo = data[i].owner_name;
                if(data[i].phone != "")
                  ownerInfo +=  "<br/>Phone: " + data[i].phone;
                if(data[i].email != "")
                  ownerInfo +=  "<br/>Email: " + data[i].email;
                if(data[i].fb_id != "")
                  ownerInfo +=  '<br/><a target="_blank" href="http://www.facebook.com/'+data[i].fb_id+'">Facebook page</a>';
                if(data[i].link != ""){
                  data[i].link = '<a target="_blank" href="'+data[i].link+'">Link</a>';
                }else{
                  data[i].link = "-";
                }
                
                //id, name, address, start_hour, start_minute, end_hour, end_minute, description, tags, link, u, v, modified
                $('#stands-table tbody').append('<tr id="'+data[i].id+'"><td><input type="checkbox"/></td><td>'+ownerInfo+'</td><td>'+data[i].name+'</td><td>'+data[i].address+'<input name="u" type="hidden" val="'+data[i].u+'"/><input name="v" type="hidden" val="'+data[i].v+'"/></td><td>'+pad(data[i].start_hour,2)+':'+pad(data[i].start_minute,2)+' - '+pad(data[i].end_hour,2)+':'+pad(data[i].end_minute,2)+'</td><td>'+data[i].description+'</td><td>'+textTags+'</td><td>'+data[i].link+'</td><td>'+date.toLocaleString()+'</td></tr>');
            }
        }
        
        function updateSpecialsList(data){
            $.log(data);
            for(var i in data){
                //format date
                var date = new Date(Number(data[i].timestamp)*1000);
                var status = ( data[i].status == "0" ? "Invitation mail sent to "+data[i].mail : "Special account activated for facebook account "+data[i].fb_name ) + " on "+date;
                //id, name, address, start_hour, start_minute, end_hour, end_minute, description, tags, link, u, v, modified
                if( $('#specials-table tbody tr#'+data[i].id).length > 0){
                    $('#specials-table tbody tr#'+data[i].id+' td:eq(1)').text(status);
                    $('#specials-table tbody tr#'+data[i].id+' td:eq(2)').text(data[i].mail);
                    $('#specials-table tbody tr#'+data[i].id+' td:eq(3)').text(data[i].fb_name);
                }else{
                    $('#specials-table tbody').append('<tr id="'+data[i].id+'"><td><input type="checkbox"/></td><td>'+status+'</td><td>'+data[i].mail+'</td><td>'+data[i].fb_name+'</td></tr>');
                }
            }
        }
        
        function loadSpecials(){
            $.ajax({
                type: 'POST',
                url: '<?php echo $config['paths']['base_url']; ?>/data.php?query=specials',
                success: function(data, textStatus, jqXHR){ //$.log("Ajax post result: "+textStatus); //$.log(data);
                    if(data.error){
                        alert("Error:"+data.error);
                    }else{
                        updateSpecialsList(data);
                    }
                },
                dataType: "json"
            });
        }
        
        //load Map API
        initializeMap();
        
        //load all stands
        var data= {um:"-90",uM:"90",vm:"-180",vM:"180"};
        $.log(data);
        $.ajax({
            type: 'POST',
            url: '<?php echo $config['paths']['base_url']; ?>/data.php?query=adminLoad',
            data: data,
             error: function(jqXHR, textStatus, errorThrown){
                $.log("There was an error.");
                $.log(jqXHR);
                $.log(textStatus);
                return;
            },
            success: function(data, textStatus, jqXHR){ //$.log("Ajax post result: "+textStatus); //$.log(data);
                if(data.error){
                    alert("Error:"+data.error);
                }else{
                    updateStandsList(data);
                }
            },
            dataType: "json"
        });
        
        //load specials
        loadSpecials();
        
        $('#invite-form .btn-primary').click(function(){
            if($('#invite-form input[name="email"]').val().replace(" ","").length == 0){
                alert("You must enter an email address to send an invitation!");
                return false;
            }
            $.ajax({
                type: 'POST',
                data: {email: urlencode($('#invite-form input[name="email"]').val().replace(" ",""))},
                url: '<?php echo $config['paths']['base_url']; ?>/data.php?query=invite',
                success: function(data, textStatus, jqXHR){ $.log("Ajax post result: "+textStatus); //$.log(data);
                    if(data.error){
                        alert("Error:"+data.error);
                    }else{
                        loadSpecials();
                    }
                },
                dataType: "json"
            });
            return false;
        });
        
        //delete stands button
        $('#delete-stands').click(function(){
            var selected = $('#stands-table input[type="checkbox"]:checked');
            if(selected.length<1){
                alert("No stand selected for deletion!");
                return false;
            }
            var del = confirm("Are you sure you want to delete "+selected.length+" stand"+(selected.length>1 ? "s?":"?"));
            if(del === true){
              selected.each(function(){
                $.ajax({
                    type: 'POST',
                    data: {i: urlencode($(this).parents('tr').attr('id'))},
                    url: '<?php echo $config['paths']['base_url']; ?>/data.php?query=delete',
                    success: function(data, textStatus, jqXHR){ $.log("Ajax post result: "+textStatus); //$.log(data);
                        if(data.error){
                            alert("Error:"+data.error);
                        }else{
                            $('#'+data.success).remove();
                        }
                    },
                    dataType: "json"
                });
              });
            }
        });
        
        //delete stands button
        $('#delete-specials').click(function(){
            var selected = $('#specials-table input[type="checkbox"]:checked');
            if(selected.length<1){
                alert("Nothing selected for deletion!");
                return false;
            }
            var del = confirm("Are you sure you want to revoke "+selected.length+" privilege"+(selected.length>1 ? "s?":"?"));
            if(del === true){
              selected.each(function(){
                $.ajax({
                    type: 'POST',
                    data: {id: urlencode($(this).parents('tr').attr('id'))},
                    url: '<?php echo $config['paths']['base_url']; ?>/data.php?query=revoke',
                    success: function(data, textStatus, jqXHR){ $.log("Ajax post result: "+textStatus); //$.log(data);
                        if(data.error){
                            alert("Error:"+data.error);
                        }else{
                            $('#'+data.success).remove();
                        }
                    },
                    dataType: "json"
                });
              });
            }
        });
        
        createHoursMinutesSelect();
    </script>
    <!-- Plugin -->
    <script src="<?php echo $config['paths']['base_url']; ?>/bootstrap/js/bootstrap-collapse.js"></script>
  </body>
</html>