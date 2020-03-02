<?php
$fileAddress = $_GET['fileAddress'];
$trimStart = $_GET['trimStart'];
$trimEnd = $_GET['trimEnd'];
$copyrightChannel = $_GET['copyrightChannel'];
$libx = $_GET['libx'];

echo "python3 main.py $fileAddress $trimStart $trimEnd $copyrightChannel $libx <br>";


//$cmd = "ping 127.0.0.1  > log.txt &"; //example command
//$cmd = "python ../python/main.py $fileAddress $trimStart $trimEnd $copyrightChannel -y  2>log.txt &";
$cmd = "python3 main.py $fileAddress $trimStart $trimEnd $copyrightChannel $libx 2>&1";

echo "<br>";
echo chdir("../python");
echo "<br>";
echo shell_exec("ls");
echo "<br>";
var_dump(shell_exec($cmd));

echo "<hr><a href='http://avir.sytes.net/ffmpeg/ffmpeg/output/'>Output Folder</a>";