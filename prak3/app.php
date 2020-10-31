<?php
include("config.php");
header('Content-type: text/javascript');

$mydata = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

echo $mydata;
 
?>