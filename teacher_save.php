<?php
if($_SERVER['REQUEST_METHOD']==='POST'){
    $lesson = [
        'id' => time(),
        'name' => $_POST['name'] ?? '',
        'date' => $_POST['date'] ?? '',
        'blocks' => $_POST['blocks'] ?? []
    ];
    if(!is_dir('data')) mkdir('data');
    file_put_contents('data/lesson_'.$lesson['id'].'.json', json_encode($lesson, JSON_PRETTY_PRINT));
    header('Location: teacher_config.php?id='.$lesson['id']);
    exit;
}
?>
