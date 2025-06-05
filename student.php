<?php
$lessons = glob('data/lesson_*.json');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Student Lessons</title>
<style>body{font-family:Arial,sans-serif;} a.button{display:inline-block;padding:5px 10px;background:#4CAF50;color:#fff;text-decoration:none;margin:5px;}</style>
</head>
<body>
<h1>Select Lesson</h1>
<form method="get" action="run_lesson.php">
<label>Your Name:<br><input type="text" name="student" required></label><br><br>
<select name="lesson">
<?php foreach($lessons as $f): $id=basename($f,'\.json'); $data=json_decode(file_get_contents($f),true); ?>
    <option value="<?php echo substr($id,7); ?>"><?php echo htmlspecialchars($data['name']); ?></option>
<?php endforeach; ?>
</select>
<input type="submit" value="Start" class="button">
</form>
</body>
</html>
