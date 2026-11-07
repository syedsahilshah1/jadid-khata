<?php
echo '<pre>';
echo 'PHP Version: ' . phpversion() . "\n";
echo 'mysqli available: ' . (function_exists('mysqli_connect') ? 'YES' : 'NO') . "\n";
echo 'open_basedir: ' . (ini_get('open_basedir') ?: 'none') . "\n";
echo 'memory_limit: ' . ini_get('memory_limit') . "\n";
echo 'max_execution_time: ' . ini_get('max_execution_time') . "\n";
echo 'upload_max_filesize: ' . ini_get('upload_max_filesize') . "\n";
echo 'post_max_size: ' . ini_get('post_max_size') . "\n";
echo 'display_errors: ' . ini_get('display_errors') . "\n";
echo '__DIR__: ' . __DIR__ . "\n";

// Test DB connection
$conn = @mysqli_connect('localhost', 'epowereducation_jadidcurrent', 'Tech@7863.90', 'epowereducation_jadidcurrent');
echo 'DB connect: ' . ($conn ? 'OK' : 'FAILED — ' . mysqli_connect_error()) . "\n";
if ($conn) mysqli_close($conn);

echo '</pre>';
echo '<p style="color:green">If you see this, PHP is working fine.</p>';
