<?php
$dir = '.';
if (!isset($_POST['submit'])) {
    if ($dp = opendir($dir)) {
        $files = array();
        while (($file = readdir($dp)) !== false) {
            if (!is_dir($dir . $file)) {
                $files[] = $file;
            }
        }
        closedir($dp);
    } else {
        exit('Directory not opened.');
    }
    if ($files) {
        echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
        foreach ($files as $file) {
            echo '<input type="checkbox" name="files[]" value="' . $file . '" /> ' .
                 $file . '<br />';
        }
        echo '<input type="submit" name="submit" value="submit" />' .
             '</form>';
    } else {
        exit('No files found.');
    }
} else {
    if (isset($_POST['files'])) {
        foreach ($_POST['files'] as $value) {
            echo $dir . $value . '<br />';
        }
    } else {
        exit('No files selected');
    }
}
?>