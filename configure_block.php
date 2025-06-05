<?php
$lessonId = $_GET['lesson'] ?? '';
$index = (int)($_GET['index'] ?? 0);
$path = 'data/lesson_'.$lessonId.'.json';
if(!file_exists($path)) die('Lesson not found');
$lesson = json_decode(file_get_contents($path), true);
$block = $lesson['blocks'][$index];
$type = $block['type'];
$configPath = 'data/lesson_'.$lessonId.'_block'.$index.'.json';
if($_SERVER['REQUEST_METHOD']==='POST'){
    file_put_contents($configPath, json_encode($_POST, JSON_PRETTY_PRINT));
    header('Location: teacher_config.php?id='.$lessonId);
    exit;
}
$existing = file_exists($configPath) ? json_decode(file_get_contents($configPath), true) : [];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Configure Block</title>
<style>
body{font-family:Arial,sans-serif;}
.button{padding:5px 10px;}
.letters span{display:inline-block;padding:4px;border:1px solid #ccc;margin:2px;cursor:pointer;}
.letters span.missing{background:#bef264;}
</style>
</head>
<body>
<h1>Configure <?php echo ucfirst($type); ?> Block</h1>
<form method="post">
<?php if($type==='flashcard'): ?>
    <div id="pairs">
    <?php if(!empty($existing['pairs'])): foreach($existing['pairs'] as $p): ?>
        <div>English: <input type="text" name="pairs[][en]" value="<?php echo htmlspecialchars($p['en']); ?>"> Spanish: <input type="text" name="pairs[][es]" value="<?php echo htmlspecialchars($p['es']); ?>"></div>
    <?php endforeach; endif; ?>
    </div>
    <button type="button" onclick="addPair()" class="button">Add Pair</button>
<?php elseif($type==='translate'): ?>
    <?php for($i=0;$i<4;$i++): $en=$existing['sentences'][$i]['en']??''; $es=$existing['sentences'][$i]['es']??''; ?>
        <div>English: <input type="text" name="sentences[<?php echo $i; ?>][en]" value="<?php echo htmlspecialchars($en); ?>"><br>Spanish: <input type="text" name="sentences[<?php echo $i; ?>][es]" value="<?php echo htmlspecialchars($es); ?>"></div><br>
    <?php endfor; ?>
<?php elseif($type==='speakflashcard'): ?>
    <div id="speakcards">
    <?php if(!empty($existing['cards'])): foreach($existing['cards'] as $c): ?>
        <div>
            Question: <input type="text" name="cards[][question]" value="<?php echo htmlspecialchars($c['question']); ?>"><br>
            Answer: <input type="text" name="cards[][answer]" value="<?php echo htmlspecialchars($c['answer']); ?>"><br>
            Hint: <input type="text" name="cards[][hint]" value="<?php echo htmlspecialchars($c['hint']); ?>"><br>
            Points: <input type="number" name="cards[][points]" value="<?php echo htmlspecialchars($c['points']); ?>">
        </div><br>
    <?php endforeach; endif; ?>
    </div>
    <button type="button" onclick="addSpeakCard()" class="button">Add Card</button>
<?php elseif($type==='spell'): ?>
    <div id="spellwords">
    <?php if(!empty($existing['exercises'])): foreach($existing['exercises'] as $e): ?>
        <div>Word: <input type="text" name="exercises[][word]" value="<?php echo htmlspecialchars($e['word']); ?>"> Points: <input type="number" name="exercises[][points]" value="<?php echo htmlspecialchars($e['points']); ?>"></div>
    <?php endforeach; endif; ?>
    </div>
    <button type="button" onclick="addSpellWord()" class="button">Add Word</button>
<?php elseif($type==='spellquiz'): ?>
    <div id="spellquizWords">
    <?php if(!empty($existing['exercises'])): foreach($existing['exercises'] as $i=>$e): ?>
        <div class="exercise" data-index="<?php echo $i; ?>">
            Word: <input type="text" name="exercises[<?php echo $i; ?>][word]" class="wordInput" value="<?php echo htmlspecialchars($e['word']); ?>">
            <div class="letters"></div>
            <div class="answerHidden"></div>
            <input type="hidden" name="exercises[<?php echo $i; ?>][hint]" class="hintInput" value="<?php echo htmlspecialchars($e['hint'] ?? ''); ?>">
            <div>Option1: <input type="text" name="exercises[<?php echo $i; ?>][options][]" value="<?php echo htmlspecialchars($e['options'][0] ?? ''); ?>">
            Option2: <input type="text" name="exercises[<?php echo $i; ?>][options][]" value="<?php echo htmlspecialchars($e['options'][1] ?? ''); ?>">
            Option3: <input type="text" name="exercises[<?php echo $i; ?>][options][]" value="<?php echo htmlspecialchars($e['options'][2] ?? ''); ?>">
            Option4: <input type="text" name="exercises[<?php echo $i; ?>][options][]" value="<?php echo htmlspecialchars($e['options'][3] ?? ''); ?>"></div>
        <?php if(!empty($e['answer'])): foreach($e['answer'] as $a): ?>
            <input type="hidden" name="exercises[<?php echo $i; ?>][answer][]" value="<?php echo htmlspecialchars($a); ?>">
        <?php endforeach; endif; ?>
        </div><br>
    <?php endforeach; endif; ?>
    </div>
    <button type="button" onclick="addSpellQuiz()" class="button">Add Word</button>
<?php else: ?>
    <label>Image URL:<br><input type="text" name="image" id="imageInput" value="<?php echo htmlspecialchars($existing['image'] ?? ''); ?>"></label><br><br>
    <div id="imageContainer" style="position:relative;display:inline-block;">
        <img id="bgImage" src="<?php echo htmlspecialchars($existing['image'] ?? ''); ?>" style="max-width:600px;<?php echo empty($existing['image'])?'display:none;':''; ?>">
        <div id="hotspotArea" style="position:absolute;top:0;left:0;right:0;bottom:0;"></div>
    </div>
    <div id="hotspots">
    <?php if(!empty($existing['hotspots'])): foreach($existing['hotspots'] as $i=>$hs): ?>
        <div class="hotspot-form">
            <input type="hidden" name="hotspots[<?php echo $i; ?>][x]" value="<?php echo $hs['x']; ?>">
            <input type="hidden" name="hotspots[<?php echo $i; ?>][y]" value="<?php echo $hs['y']; ?>">
            English: <input type="text" name="hotspots[<?php echo $i; ?>][en]" value="<?php echo htmlspecialchars($hs['en']); ?>">
            Correct: <input type="text" name="hotspots[<?php echo $i; ?>][correct]" value="<?php echo htmlspecialchars($hs['correct']); ?>"><br>
            Option1: <input type="text" name="hotspots[<?php echo $i; ?>][options][]" value="<?php echo htmlspecialchars($hs['options'][0] ?? ''); ?>">
            Option2: <input type="text" name="hotspots[<?php echo $i; ?>][options][]" value="<?php echo htmlspecialchars($hs['options'][1] ?? ''); ?>">
            Option3: <input type="text" name="hotspots[<?php echo $i; ?>][options][]" value="<?php echo htmlspecialchars($hs['options'][2] ?? ''); ?>">
            Option4: <input type="text" name="hotspots[<?php echo $i; ?>][options][]" value="<?php echo htmlspecialchars($hs['options'][3] ?? ''); ?>">
        </div><br>
    <?php endforeach; endif; ?>
    </div>
    <p>Click on the image to add hotspots.</p>
<?php endif; ?>
    <input type="submit" value="Save" class="button">
</form>
<script>
function addPair(){
    const div=document.createElement('div');
    div.innerHTML='English: <input type="text" name="pairs[][en]"> Spanish: <input type="text" name="pairs[][es]">';
    document.getElementById('pairs').appendChild(div);
}
function addSpeakCard(){
    const div=document.createElement('div');
    div.innerHTML='Question: <input type="text" name="cards[][question]"><br>Answer: <input type="text" name="cards[][answer]"><br>Hint: <input type="text" name="cards[][hint]"><br>Points: <input type="number" name="cards[][points]" value="4">';
    document.getElementById('speakcards').appendChild(div);
    document.getElementById('speakcards').appendChild(document.createElement('br'));
}
function addSpellWord(){
    const div=document.createElement('div');
    div.innerHTML='Word: <input type="text" name="exercises[][word]"> Points: <input type="number" name="exercises[][points]" value="5">';
    document.getElementById('spellwords').appendChild(div);
}
function addSpellQuiz(existing){
    const list=document.getElementById('spellquizWords');
    const idx=list.querySelectorAll('.exercise').length;
    const div=document.createElement('div');
    div.className='exercise';
    div.dataset.index=idx;
    div.innerHTML=`Word: <input type="text" class="wordInput" name="exercises[${idx}][word]">
    <div class="letters"></div>
    <div class="answerHidden"></div>
    <input type="hidden" class="hintInput" name="exercises[${idx}][hint]">
    <div>Option1: <input type="text" name="exercises[${idx}][options][]">
    Option2: <input type="text" name="exercises[${idx}][options][]">
    Option3: <input type="text" name="exercises[${idx}][options][]">
    Option4: <input type="text" name="exercises[${idx}][options][]"></div><br>`;
    list.appendChild(div);
    setupSpellQuiz(div,idx,existing);
}
function setupSpellQuiz(div,idx,data){
    const wordInput=div.querySelector('.wordInput');
    const lettersDiv=div.querySelector('.letters');
    const answerDiv=div.querySelector('.answerHidden');
    const hintInput=div.querySelector('.hintInput');
    function render(){
        lettersDiv.innerHTML='';
        answerDiv.innerHTML='';
        const word=wordInput.value;
        [...word].forEach((ch,i)=>{
            const span=document.createElement('span');
            span.textContent=ch;
            span.style.cursor='pointer';
            span.style.padding='2px';
            span.addEventListener('click',()=>{span.classList.toggle('missing');update();});
            lettersDiv.appendChild(span);
        });
        update();
    }
    function update(){
        const word=wordInput.value;
        const chars=word.split('');
        const answer=[];
        lettersDiv.querySelectorAll('span').forEach((sp,i)=>{
            if(sp.classList.contains('missing')){chars[i]='_';answer.push(sp.textContent);}
        });
        hintInput.value=chars.join('');
        answerDiv.innerHTML='';
        answer.forEach(l=>{
            const h=document.createElement('input');
            h.type='hidden';
            h.name=`exercises[${idx}][answer][]`;
            h.value=l;
            answerDiv.appendChild(h);
        });
    }
    wordInput.addEventListener('input',render);
    if(data){
        wordInput.value=data.word||'';
        render();
        if(data.answer){
            lettersDiv.querySelectorAll('span').forEach((sp)=>{
                const count=data.answer.filter(a=>a===sp.textContent).length;
                for(let i=0;i<count;i++){sp.classList.add('missing');}
            });
            update();
        }
        if(data.options){
            div.querySelectorAll('input[name$="[options][]"]').forEach((inp,j)=>{inp.value=data.options[j]||'';});
        }
    } else {
        render();
    }
}
document.querySelectorAll('#spellquizWords .exercise').forEach((ex,idx)=>{
    const data={
        word:ex.querySelector('.wordInput').value,
        answer:Array.from(ex.querySelectorAll('input[name$="[answer][]"]')).map(i=>i.value),
        options:Array.from(ex.querySelectorAll('input[name$="[options][]"]')).map(i=>i.value),
        hint:ex.querySelector('.hintInput').value
    };
    setupSpellQuiz(ex,idx,data);
});
// hotspot support
const imageInput=document.getElementById('imageInput');
if(imageInput){
    const bg=document.getElementById('bgImage');
    const area=document.getElementById('hotspotArea');
    const list=document.getElementById('hotspots');
    let hIndex=list.querySelectorAll('.hotspot-form').length;
    // place existing markers
    list.querySelectorAll('input[name$="[x]"]').forEach((inp,idx)=>{
        const x=inp.value;
        const y=list.querySelectorAll('input[name$="[y]"]')[idx].value;
        const m=document.createElement('div');
        m.style.position='absolute';
        m.style.width='20px';
        m.style.height='20px';
        m.style.borderRadius='50%';
        m.style.background='red';
        m.style.left=x+'%';
        m.style.top=y+'%';
        area.appendChild(m);
    });
    imageInput.addEventListener('change',()=>{bg.src=imageInput.value; bg.style.display='block';});
    area.addEventListener('click',e=>{
        const rect=area.getBoundingClientRect();
        const x=((e.clientX-rect.left)/rect.width*100).toFixed(2);
        const y=((e.clientY-rect.top)/rect.height*100).toFixed(2);
        const marker=document.createElement('div');
        marker.style.position='absolute';
        marker.style.width='20px';
        marker.style.height='20px';
        marker.style.borderRadius='50%';
        marker.style.background='red';
        marker.style.left=x+'%';
        marker.style.top=y+'%';
        area.appendChild(marker);
        const div=document.createElement('div');
        div.className='hotspot-form';
        div.innerHTML='\
            <input type="hidden" name="hotspots['+hIndex+'][x]" value="'+x+'">\
            <input type="hidden" name="hotspots['+hIndex+'][y]" value="'+y+'">\
            English: <input type="text" name="hotspots['+hIndex+'][en]">\
            Correct: <input type="text" name="hotspots['+hIndex+'][correct]"><br>\
            Option1: <input type="text" name="hotspots['+hIndex+'][options][]">\
            Option2: <input type="text" name="hotspots['+hIndex+'][options][]">\
            Option3: <input type="text" name="hotspots['+hIndex+'][options][]">\
            Option4: <input type="text" name="hotspots['+hIndex+'][options][]">';
        list.appendChild(div);
        hIndex++;
    });
}
</script>
</body>
</html>
