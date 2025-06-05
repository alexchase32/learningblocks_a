<?php
$data = json_decode(file_get_contents('php://input'), true);
if(!$data) exit;
$file = 'data/progress_'.$data['student'].'_'.$data['lesson'].'.json';
file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
?>
