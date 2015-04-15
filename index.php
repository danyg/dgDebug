<?php
error_reporting(E_ALL);
ini_set('dispaly_errors',1);
require ('debug.php');
$varToDump = $_SERVER;
?>

<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title></title>
</head>
<body>

<h1>dgDebug Test</h1>

<?php
dbg('A block Title')
    ->d($varToDump, 'A title Here');
?>

</body>
</html>