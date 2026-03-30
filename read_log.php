<?php
$file = 'storage/logs/laravel.log';
$handle = fopen($file, 'r');
fseek($handle, -5000, SEEK_END);
echo fread($handle, 5000);
fclose($handle);
