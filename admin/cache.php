<?php
    function saveCache($string){
        global $noCache; //if true, just return the plain string
        if(isset($noCache) && $noCache){
            return $string; 
        }
        return json_encode( array("success" => FALSE === file_put_contents ( $_SERVER['DOCUMENT_ROOT']."/cache/".$_GET['cache_lang']."_".$_GET['page'], $string) ? "error" : "ok", "page"=>$_GET['page']) );
    }
    
    ini_set("session.save_path",$_SERVER['DOCUMENT_ROOT']."/session/");
    session_start();
    if( isset($_SESSION['admin']) && $_SESSION['admin'] === 1 && isset($_GET['cache_lang']) && isset($_GET['page'])){
        $_GET['locale'] = $_GET['cache_lang'];
        
        //delete last cache if exists
        if( file_exists( $_SERVER['DOCUMENT_ROOT']."/cache/".$_GET['cache_lang']."_".$_GET['page'] ) != FALSE){
            unlink($_SERVER['DOCUMENT_ROOT']."/cache/".$_GET['cache_lang']."_".$_GET['page']);
        }
        ob_start("saveCache");
        
        if($_GET['page'] == ""){
            include_once($_SERVER['DOCUMENT_ROOT']."/index.php");
        }else{
            include_once($_SERVER['DOCUMENT_ROOT']."/".$_GET['page']);
        }
        ob_end_flush();
        
        exit(0);
    }
    
    $_SESSION['admin'] = 1;
    $page = "Staff Only!";
    include($_SERVER['DOCUMENT_ROOT']."/inc/header.php");
?>
<style>
    li.ok{
        background:#6EC49C;
    }
    
    li.error{
        background:#B4595B;
    }
</style>
   </head>
  <body>
    <!-- header starts -->
    <?php include($_SERVER['DOCUMENT_ROOT']."/inc/navbar.php"); ?>
    <div id="content">
      <!-- content starts -->
      <div class="container">
        <div class="row">
          <div class="span12">
            <ul id="cache_list">
                <?php foreach($navigation as $label=>$page){ ?>
                <li id="<?php echo $page; ?>"><?php echo $label; ?>: <a href="#" data-locale="fi_FI">Update Finnish cache</a> | <a href="#" data-locale="en_EN">Update English cache</a></li>
                <?php } ?>
            </ul>
            <button class="cache_all" data-locale="fi_FI">Update every Finnish caches</button><button class="cache_all" data-locale="en_EN">Update every English caches</button>
          </div>
        </div>
      </div>
      <script type="text/javascript">
        $("#cache_list a").click(function(){
            var thePage = $(this).parent().attr('id');
            var theLocale = $(this).attr('data-locale');
            var theLink = $(this);
            $.ajax({
              type: 'GET',
              dataType: "json",
              url: '#',
              data :{cache_lang : theLocale, page : thePage},
              error: function(jqXHR, textStatus, errorThrown){
                $.log("There was an error.");
                $.log(jqXHR);
                $.log(textStatus);
                return;
              },
              success: function(data, textStatus, jqXHR){
                $.log(data);
                //$.log("classes",data.page);
                theLink.parent().removeClass("error ok").addClass(data.success);
                //theLink.text(theLink.text()+" ("+data.success+")");
                }
            });
            return false; 
        });
        
        $("button.cache_all").click(function(){
            var theLocale = $(this).attr('data-locale');
            $("#cache_list a[data-locale="+theLocale+"]").click();
            return false;
        });
        
      </script>
  </body>
</html>