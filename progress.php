<?php
$student = preg_replace('/[^A-Za-z0-9_]/','', $_GET['student'] ?? '');
$files = glob('data/progress_'.$student.'_*.json');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Progress</title>
<style>body{font-family:Arial,sans-serif;}</style>
</head>
<body>
<h1>Progress for <?php echo htmlspecialchars($student); ?></h1>
<ul>
<?php foreach($files as $f): $d=json_decode(file_get_contents($f),true); ?>
<li>Lesson <?php echo $d['lesson']; ?>: <?php echo $d['score']; ?> / <?php echo $d['total']; ?></li>
<?php endforeach; ?>
</ul>
</body>
</html>
