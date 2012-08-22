<?php
function customLog($logFile, $stringData){
    $fh = fopen($logFile, 'a') or die("can't open file");
    $stringData .= "\n";
    fwrite($fh, $stringData);
    fclose($fh);
}


?>