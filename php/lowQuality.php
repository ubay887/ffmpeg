<?php
$LOW_QUALITY_POSTFIX = "_LowQuality";

$fileAddress = $_GET['fileAddress'];
if(isset($_GET['quality'])) {
    $quality = $_GET['quality'];
}else{
    $quality = "480";
}

$outputAddress = dirname($fileAddress) . "/" . pathinfo($fileAddress)["filename"] . $LOW_QUALITY_POSTFIX . "." . pathinfo($fileAddress)["extension"];
$cmd = "ffmpeg -i $fileAddress -filter:v scale=\"trunc(oh*a/2)*2:$quality\" -c:a copy $outputAddress 2>&1";
echo $cmd;
var_dump(shell_exec($cmd));
