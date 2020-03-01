<?php
$fileAddress = $_GET['fileAddress'];
$trimStart = $_GET['trimStart'];
$trimEnd = $_GET['trimEnd'];
$copyrightChannel = $_GET['copyrightChannel'];

echo "python ../python/main.py $fileAddress $trimStart $trimEnd $copyrightChannel";
