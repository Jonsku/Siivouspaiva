<?php
//parse config
$config = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/siivouspaiva.ini", true);

//redirect
if(preg_match("/^".$config['server']['server_name']."$/i", $_SERVER["SERVER_NAME"]) == 0){
   header( 'Location: http://'.$config['server']['server_name'].$_SERVER["REQUEST_URI"] ) ;
}

$navigation = array("Etusivu"=>"",
                        "Myyntipaikat"=>"myyntipaikat.php",
                        "Info"=>"info.php",
                        "Siisteys & kierrätys"=>"sk.php",
                        "Vapaaehtoiseksi"=>"vapaaehtoiseksi.php",
                        "Materiaalipankki"=>"materiaalipankki.php",
                        "Yhteystiedot"=>"yhteystiedot.php");

//string localization
require_once($_SERVER['DOCUMENT_ROOT']."/l10n.php");
if( !( isset($_SESSION['admin']) && $_SESSION['admin'] === 1 ) && loadCache()){
   echo "<!-- cached copy -->";
   exit(0);
}
?>
<!DOCTYPE html>
<html lang="<?php echo getLangageCode(); ?>">
  <head>
    <meta charset="UTF-8">
    <title>Siivouspäivä - <?php echo $page ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:image" content="http://siivouspaiva.com/img/side_illustration.png"/>
    <meta property="og:type" content="cause" />
    <meta property="og:site_name" content="Siivouspäivä" />
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="<?php echo $config['paths']['base_url']; ?>/bootstrap/css/bootstrap.css" rel="stylesheet">    
    <link href="<?php echo $config['paths']['base_url']; ?>/bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
    <link href="<?php echo $config['paths']['base_url']; ?>/css/siivouspaiva.css" rel="stylesheet">
        
    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="<?php echo $config['paths']['base_url']; ?>/img/text_logo_60_60.jpg">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo $config['paths']['base_url']; ?>/bootstrap/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo $config['paths']['base_url']; ?>/bootstrap/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="<?php echo $config['paths']['base_url']; ?>/bootstrap/ico/apple-touch-icon-57-precomposed.png">
    
    <script type="text/javascript" src="<?php echo $config['paths']['jquery']; ?>"></script>
    <script type="text/javascript" src="<?php echo $config['paths']['base_url']; ?>/script/jquery.validate.min.js"></script>
    <script type="text/javascript" src="<?php echo $config['paths']['base_url']; ?>/script/bootstrap-dropdown.js"></script>
    <script type="text/javascript">
         var baseUrl = "<?php echo $config['paths']['base_url']; ?>";
         //log/debug
         (function($) {
            $.each(['log','warn'], function(i,fn) {
                $[fn] = function() {
                    if (!window.console) return;
                    var p = [], a = arguments;
                    for (var i=0; i<a.length; i++)
                        p.push(a[i]) && (i+1<a.length) && p.push(' ');
                    Function.prototype.bind.call(console[fn], console)
                        .apply(this, p);
                };
                
                $.fn[fn] = function() {
                    var p = [this], a = arguments;
                    for (var i=0; i<a.length; i++) p.push(a[i]);
                    $[fn].apply(this, p);
                    return this;
                };
            });
            $.assert = function() {
                window.console
                    && Function.prototype.bind.call(console.assert, console)
                       .apply(console, arguments);
            };
        })(jQuery);
         
        //encode to send
        function urlencode(s) {
            s = encodeURIComponent(s);
            return s.replace(/~/g,'%7E').replace(/%20/g,'+');//.replace(/'/g,"\\'");
        }
        
        //pad leading zeros
        function pad(num, size) {
            var s = "000000000" + num;
            return s.substr(s.length-size);
        }
        
        function createHoursMinutesSelect(){
            //create hours and minutes selects
            
            $("select.hour").each(function(){
                //var d = (this.dataset.default) ? this.dataset.default : 12;
                var d = this.getAttribute('data-default') ? this.getAttribute('data-default') : 12;
                d = pad(d,2);
                for(var h=0;h<24; h++){
                    h = pad(h,2);
                    if(h==d){
                        $(this).append('<option value="'+h+'" selected>'+h+'</option>');
                    }else{
                        $(this).append('<option value="'+h+'">'+h+'</option>');
                    }
                }
            });
            $("select.minutes").each(function(){
                for(var m=0;m<6; m++){
                    //m = pad(m,2);
                    $(this).append('<option value="'+m+'0">'+m+'0</option>');
                }
            });
        }
        
        function hourMinutesToMinutes(h, m){
            return h * 60 + m;
        }
    </script>
    <!-- Google Analytics -->
    <script type="text/javascript">
      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', 'UA-32022583-1']);
      _gaq.push(['_trackPageview']);
    
      (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
      })();
    
    </script>