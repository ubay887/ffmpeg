<?php
$fileAddress = $_GET['fileAddress'];
$trimStart = $_GET['trimStart'];
$trimEnd = $_GET['trimEnd'];
$copyrightChannel = $_GET['copyrightChannel'];

echo "python ../python/main.py $fileAddress $trimStart $trimEnd $copyrightChannel";


//$cmd = "ping 127.0.0.1  > log.txt &"; //example command
$cmd = "python ../python/main.py $fileAddress $trimStart $trimEnd $copyrightChannel  2> log.txt &"; //example command

exec($cmd);