<!DOCTYPE html>
<html>
<body>

<p>
	<a href="../"> << Regresar </a>
</p>

</body>
</html>
<?php 
	$command = escapeshellcmd('python /var/www/html/sqlite/reset.py');
	$output = shell_exec($command);
	echo $output;
?>