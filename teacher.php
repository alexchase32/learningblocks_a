<?php
// Teacher form to create a lesson with blocks
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Create Lesson</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .block { border:1px solid #ccc; padding:10px; margin:10px 0; }
        .button { padding:5px 10px; }
    </style>
</head>
<body>
<h1>Create Lesson</h1>
<form method="post" action="teacher_save.php" id="lessonForm">
    <label>Lesson Name:<br><input type="text" name="name" required></label><br><br>
    <label>Date:<br><input type="date" name="date" required></label><br><br>
    <div id="blocks"></div>
    <button type="button" onclick="addBlock('flashcard')" class="button">Add Flashcard Block</button>
    <button type="button" onclick="addBlock('speakflashcard')" class="button">Add Speak Flashcards Block</button>
    <button type="button" onclick="addBlock('translate')" class="button">Add Translate Block</button>
    <button type="button" onclick="addBlock('hotspot')" class="button">Add Hotspot Block</button>
    <button type="button" onclick="addBlock('spell')" class="button">Add Spelling Block</button><br><br>
    <button type="button" onclick="addBlock('spellquiz')" class="button">Add Spelling Quiz Block</button><br><br>
    <input type="submit" value="Save Lesson" class="button">
</form>
<script>
let blockCount = 0;
function addBlock(type){
    const div = document.createElement('div');
    div.className='block';
    div.textContent = type.charAt(0).toUpperCase()+type.slice(1)+" Block";
    div.innerHTML += '<input type="hidden" name="blocks['+blockCount+'][type]" value="'+type+'">';
    div.innerHTML += '<input type="hidden" name="blocks['+blockCount+'][id]" value="'+Date.now()+blockCount+'">';
    document.getElementById('blocks').appendChild(div);
    blockCount++;
}
</script>
</body>
</html>
