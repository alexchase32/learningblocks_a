<?php
$id = $_GET['id'] ?? '';
$path = 'data/lesson_'.$id.'.json';
if(!file_exists($path)) die('Lesson not found');
$lesson = json_decode(file_get_contents($path), true);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Configure Lesson</title>
<style>body{font-family:Arial,sans-serif;} a.button{display:inline-block;padding:5px 10px;background:#4CAF50;color:#fff;text-decoration:none;margin:5px;}</style>
</head>
<body>
<h1>Configure Lesson: <?php echo htmlspecialchars($lesson['name']); ?></h1>
<ul>
<?php foreach($lesson['blocks'] as $i=>$b): ?>
<li><?php echo ucfirst($b['type']); ?> Block - <a class="button" href="configure_block.php?lesson=<?php echo $id; ?>&index=<?php echo $i; ?>">Configure</a></li>
<?php endforeach; ?>
</ul>
<a href="index.php" class="button">Home</a>
</body>
</html>
