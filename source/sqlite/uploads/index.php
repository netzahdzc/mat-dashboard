<!DOCTYPE html>
<html>
<body>

<p>
	<a href="../../"> << Regresar </a>
</p>

</body>
</html>
<?php
$files = array();
$dir = opendir('.'); // open the cwd..also do an err check.
while(false != ($file = readdir($dir))) {
        if(($file != ".") and ($file != "..") and ($file != "index.php")) {
                $files[] = $file; // put in array.
        }   
}

natsort($files); // sort.

// print.
foreach($files as $file) {
        echo("<a href='$file'>$file</a> <br />\n");
}
?>