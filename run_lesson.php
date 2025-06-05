<?php
$lessonId = $_GET['lesson'] ?? '';
$student = preg_replace('/[^A-Za-z0-9_]/','', $_GET['student'] ?? '');
$lessonPath = 'data/lesson_'.$lessonId.'.json';
if(!file_exists($lessonPath)) die('Lesson not found');
$lesson = json_decode(file_get_contents($lessonPath), true);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Lesson <?php echo htmlspecialchars($lesson['name']); ?></title>
<style>
body{font-family:Arial,sans-serif;}
.hidden{display:none;}
.correct{color:green;}
.incorrect{color:red;}
.button{padding:5px 10px;}
.flashcard{border:1px solid #ccc;padding:20px;margin:20px;}
.hotspot{position:absolute;width:30px;height:30px;border-radius:50%;background:#bef264;color:#000;display:flex;justify-content:center;align-items:center;cursor:pointer;font-weight:bold;transition:all .3s ease;z-index:10;}
.hotspot:hover{transform:scale(1.2);background:#a8e04d;}
.hotspot.expanded{width:auto;height:auto;border-radius:0.375rem;padding:0.5rem 1rem;}
.hotspot.correct{background:#86efac;}
.hotspot.incorrect{background:#fca5a5;}
.quiz-container{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:white;border:2px solid #bef264;border-radius:0.375rem;padding:1rem;z-index:100;display:none;}
.quiz-container button{background:#bef264;color:#000;border:none;padding:0.5rem 1rem;font-size:1rem;font-weight:bold;cursor:pointer;border-radius:0.375rem;margin-top:1rem;text-align:left;margin-right:1rem;}
.quiz-container button:hover{background:#a8e04d;}
.title{background:#bef264;color:#000;font-size:1.25rem;font-weight:bold;padding:0.5rem 1rem;border-radius:0.375rem;margin-bottom:1rem;}
.card{width:300px;height:200px;position:relative;margin:20px;border-radius:10px;box-shadow:0 4px 8px rgba(0,0,0,0.1);}
.card-front,.card-back{position:absolute;width:100%;height:100%;text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:center;font-size:1.5rem;border-radius:10px;transition:opacity 0.8s;}
.card-front{background:#f8fafc;opacity:1;visibility:visible;}
.card-back{background:#bef264;opacity:0;visibility:hidden;}
.card.flipped .card-front{opacity:0;visibility:hidden;}
.card.flipped .card-back{opacity:1;visibility:visible;}
.controls{margin:20px;display:flex;gap:10px;}
#listening{color:#4ade80;font-weight:bold;margin-top:10px;display:none;}
.learning-block{width:80%;margin:20px;padding:20px;border:1px solid #6ea8ff;border-radius:10px;background:#fff;box-shadow:0 0 10px rgba(0,0,0,0.2);}
.learning-block .row-container{display:flex;flex-direction:row;}
.learning-block .box{width:40px;height:30px;margin:15px;padding:10px;border:1px solid #6ea8ff;border-radius:10px;background:#fff;box-shadow:0 0 10px rgba(0,0,0,0.2);font-size:19px;text-align:center;cursor:pointer;}
.learning-block .selected-wrong{background-color:lightcoral!important;}
.learning-block .selected-correct{background-color:lightgreen!important;}
.spell-container{display:flex;flex-direction:column;align-items:center;padding:2rem;background:#fff;border-radius:0.375rem;box-shadow:0 4px 6px rgba(0,0,0,0.1);}
.spell-container .title{background:#bef264;color:#000;font-size:1.25rem;font-weight:bold;padding:0.5rem 1rem;border-radius:0.375rem;margin-bottom:1rem;}
.spell-container .question{margin:20px;font-size:1.5rem;line-height:1.6;text-align:center;}
.spell-container .word{font-size:2.8rem;font-weight:bold;margin:1rem 0;}
.spell-container .options{display:flex;justify-content:center;gap:10px;margin-top:1rem;}
.spell-container .option{background:#bef264;color:#000;border:none;padding:0.5rem 1rem;font-size:1.5rem;font-weight:bold;cursor:pointer;border-radius:0.375rem;}
.spell-container .option:hover{background:#a8e04d;}
.spell-score{font-size:1.25rem;font-weight:bold;margin-top:1rem;color:#000;}
</style>
</head>
<body>
<h1><?php echo htmlspecialchars($lesson['name']); ?></h1>
<div id="blocks"></div>
<div id="result" class="hidden"></div>
<script>
const lesson = <?php echo json_encode($lesson); ?>;
let score=0,total=0;let current=0;let timer;
function showBlock(i){
    if(i>=lesson.blocks.length){
        document.getElementById('blocks').innerHTML='';
        const res=document.getElementById('result');
        res.textContent='Finished! Score: '+score+'/'+total;
        res.classList.remove('hidden');
        fetch('save_progress.php',{
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body:JSON.stringify({student:'<?php echo $student; ?>',lesson:lesson.id,score:score,total:total})
        });
        return;
    }
    const block=lesson.blocks[i];
    fetch('data/lesson_'+lesson.id+'_block'+i+'.json')
        .then(r=>r.json())
        .then(cfg=>{
            if(block.type==='flashcard') flashcard(cfg,i);
            else if(block.type==='speakflashcard') speakFlash(cfg,i);
            else if(block.type==='translate') translate(cfg,i);
            else if(block.type==='spell') spell(cfg,i);
            else if(block.type==='spellquiz') spellQuiz(cfg,i);
            else hotspot(cfg,i);
        });
}
function flashcard(cfg,i){
    let container=document.getElementById('blocks');
    container.innerHTML='';
    const div=document.createElement('div');
    div.className='flashcard';
    const startBtn=document.createElement('button');
    startBtn.textContent='Start';
    startBtn.className='button';
    div.appendChild(startBtn);
    const timerSpan=document.createElement('span');
    timerSpan.style.marginLeft='10px';
    div.appendChild(timerSpan);
    const cardDiv=document.createElement('div');
    div.appendChild(cardDiv);
    container.appendChild(div);
    let index=0;
    startBtn.onclick=()=>{
        total+=cfg.pairs.length;
        startBtn.disabled=true;
        let time=120;
        timerSpan.textContent=time+'s';
        timer=setInterval(()=>{time--;timerSpan.textContent=time+'s'; if(time<=0){clearInterval(timer); showBlock(i+1);}},1000);
        showCard();
    };
    function showCard(){
        if(index>=cfg.pairs.length){ clearInterval(timer); showBlock(i+1); return; }
        const pair=cfg.pairs[index];
        cardDiv.innerHTML='';
        const en=document.createElement('div'); en.textContent=pair.en; cardDiv.appendChild(en);
        const knowBtn=document.createElement('button'); knowBtn.textContent='I know it'; knowBtn.className='button';
        const dontBtn=document.createElement('button'); dontBtn.textContent="I don't know"; dontBtn.className='button';
        cardDiv.appendChild(document.createElement('br'));
        cardDiv.appendChild(knowBtn); cardDiv.appendChild(dontBtn);
        const answerDiv=document.createElement('div'); cardDiv.appendChild(answerDiv);
        knowBtn.onclick=()=>{
            knowBtn.disabled=true; dontBtn.disabled=true;
            const input=document.createElement('input');
            answerDiv.appendChild(input);
            const chars=['á','é','í','ó','ú','ñ','¿','¡'];
            const charDiv=document.createElement('div');
            chars.forEach(c=>{const b=document.createElement('button');b.type='button';b.textContent=c;b.onclick=()=>{input.value+=c;};charDiv.appendChild(b);});
            answerDiv.appendChild(charDiv);
            const check=document.createElement('button');check.textContent='Check';check.className='button';answerDiv.appendChild(check);
            check.onclick=()=>{
                if(input.value.trim().toLowerCase()===pair.es.trim().toLowerCase())score++; index++; showCard();
            };
        };
        dontBtn.onclick=()=>{ answerDiv.textContent=pair.es; index++; setTimeout(showCard,1000); };
    }
}
function speakFlash(cfg,i){
    const container=document.getElementById('blocks');
    container.innerHTML='';
    const title=document.createElement('div');
    title.className='title';
    title.textContent='Speak Flashcards';
    container.appendChild(title);
    const scoreDiv=document.createElement('div');
    scoreDiv.id='score';
    scoreDiv.textContent='Score: '+score;
    container.appendChild(scoreDiv);
    const message=document.createElement('div');
    message.id='message';
    container.appendChild(message);
    const listening=document.createElement('div');
    listening.id='listening';
    listening.textContent='Listening...';
    listening.style.display='none';
    container.appendChild(listening);
    const cardContainer=document.createElement('div');
    cardContainer.className='card';
    cardContainer.innerHTML='<div class="card-inner"><div class="card-front"><div class="question"></div></div><div class="card-back"></div></div>';
    container.appendChild(cardContainer);
    const controls=document.createElement('div');
    controls.className='controls';
    controls.innerHTML='<button type="button">I Know This!</button><button type="button">Show Hint</button><button type="button">Next Question</button>';
    container.appendChild(controls);
    const knowBtn=controls.children[0];
    const hintBtn=controls.children[1];
    const nextBtn=controls.children[2];

    let currentCardIndex=0;
    let hintUsed=false;
    const pointsTotal=cfg.cards.reduce((s,c)=>s+parseInt(c.points||0),0);
    total+=pointsTotal;

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const recognition = new SpeechRecognition();
    recognition.lang = 'es-ES';
    recognition.continuous = false;
    recognition.interimResults = false;

    recognition.onresult = (event) => {
        const spokenText = event.results[0][0].transcript.toLowerCase().trim();
        const currentCard = cfg.cards[currentCardIndex];
        listening.style.display='none';
        if (spokenText === currentCard.answer.toLowerCase() && !hintUsed) {
            score += parseInt(currentCard.points);
            scoreDiv.textContent='Score: '+score;
            showMessage('Correct! +' + currentCard.points + ' points', false);
            cardContainer.classList.add('flipped');
        } else if (spokenText === currentCard.answer.toLowerCase() && hintUsed) {
            const pts=Math.floor(parseInt(currentCard.points)/2);
            score += pts;
            scoreDiv.textContent='Score: '+score;
            showMessage('Correct with hint! +' + pts + ' points', false);
            cardContainer.classList.add('flipped');
        } else {
            showMessage('Try again. You said: ' + spokenText, true);
            return;
        }
        currentCardIndex++;
    };
    recognition.onend = () => {
        listening.style.display='none';
        if(currentCardIndex>=cfg.cards.length){
            setTimeout(()=>showBlock(i+1),500);
        }else{
            showCard();
        }
    };

    knowBtn.onclick=()=>{
        listening.style.display='block';
        recognition.start();
    };
    hintBtn.onclick=()=>{
        const current = cfg.cards[currentCardIndex];
        showMessage('Hint: '+current.hint,false);
        hintUsed=true;
        hintBtn.style.backgroundColor='#fde047';
    };
    nextBtn.onclick=()=>{
        cardContainer.classList.remove('flipped');
        currentCardIndex=(currentCardIndex+1)%cfg.cards.length;
        hintUsed=false;
        hintBtn.style.backgroundColor='#bef264';
        showCard();
    };

    function showCard(){
        const card = cfg.cards[currentCardIndex];
        cardContainer.querySelector('.question').textContent=card.question;
        cardContainer.querySelector('.card-back').textContent=card.answer;
    }
    function showMessage(text,isError){
        message.textContent=text;
        message.style.color=isError?'red':'green';
        setTimeout(()=>{message.textContent='';},3000);
    }

    showCard();
}
function translate(cfg,i){
    const container=document.getElementById('blocks');
    container.innerHTML='';
    const form=document.createElement('div');
    cfg.sentences.forEach((s,idx)=>{
        const wrap=document.createElement('div');
        wrap.innerHTML='<div>'+s.en+'</div>';
        const input=document.createElement('input');
        wrap.appendChild(input);
        const fb=document.createElement('div'); wrap.appendChild(fb);
        form.appendChild(wrap);
        total++;
        input.addEventListener('keyup',()=>{
            const words=input.value.trim().split(/\s+/);
            const target=s.es.trim().split(/\s+/);
            fb.innerHTML='';
            words.forEach((w,j)=>{
                const span=document.createElement('span');
                span.textContent=w+' ';
                if(w===target[j]) span.className='correct'; else span.className='incorrect';
                fb.appendChild(span);
            });
            if(input.value.trim().toLowerCase()===s.es.trim().toLowerCase()){
                input.dataset.correct='1';
            }else{
                input.dataset.correct='0';
            }
        });
    });
    const done=document.createElement('button');done.textContent='Next';done.className='button';form.appendChild(done);
    container.appendChild(form);
    done.onclick=()=>{
        const inputs=form.querySelectorAll('input');
        inputs.forEach(inp=>{ if(inp.dataset.correct==='1') score++; });
        showBlock(i+1);
    };
}
function spell(cfg,i){
    const container=document.getElementById('blocks');
    container.innerHTML='';
    let idx=0; let currentGuess=''; let selected='';
    cfg.exercises.forEach(ex=>{total+=parseInt(ex.points||5);});
    const block=document.createElement('div');
    block.className='learning-block';
    container.appendChild(block);
    const header=document.createElement('h2'); header.textContent='Vocabulary Exercise: Spelling'; block.appendChild(header);
    const info=document.createElement('p'); info.textContent='Listen and put the letters in order to spell the words.'; block.appendChild(info);
    const audioBtn=document.createElement('button'); audioBtn.textContent='🔊'; audioBtn.className='box'; block.appendChild(audioBtn);
    const rowLetters=document.createElement('div'); rowLetters.className='row-container'; block.appendChild(rowLetters);
    const rowGaps=document.createElement('div'); rowGaps.className='row-container'; block.appendChild(rowGaps);
    audioBtn.onclick=speak;
    load();
    function load(){
        if(idx>=cfg.exercises.length){ showBlock(i+1); return; }
        rowLetters.innerHTML=''; rowGaps.innerHTML=''; currentGuess=''; selected='';
        const word=cfg.exercises[idx].word;
        const letters=word.split('').sort(()=>0.5-Math.random());
        letters.forEach(l=>{const b=document.createElement('div');b.className='box';b.textContent=l;b.onclick=()=>{selected=l;};rowLetters.appendChild(b);});
        for(let k=0;k<word.length;k++){const gap=document.createElement('div');gap.className='box gap';gap.onclick=function(){if(selected&&this.textContent===''){this.textContent=selected;currentGuess+=selected;selected='';check();}};rowGaps.appendChild(gap);}    }
    function speak(){
        const msg=new SpeechSynthesisUtterance(cfg.exercises[idx].word); msg.lang='es-MX'; window.speechSynthesis.speak(msg);
    }
    function check(){
        const word=cfg.exercises[idx].word;
        if(currentGuess.length===word.length){
            if(currentGuess===word){
                score+=parseInt(cfg.exercises[idx].points||5);
                alert('Correct! Score: '+score);
                idx++; load();
            }else{
                alert('Try again!');
                rowGaps.querySelectorAll('.gap').forEach(g=>g.textContent='');
                currentGuess='';
            }
        }
    }
}
function spellQuiz(cfg,i){
    const container=document.getElementById('blocks');
    container.innerHTML='';
    const wrap=document.createElement('div');
    wrap.className='spell-container';
    wrap.innerHTML='<div class="title">Spelling Pop Quiz</div><div class="question">Look at the incomplete Spanish word. Click on the missing letter(s) in correct order to complete it.</div><div class="word" id="word"></div><div class="options" id="options"></div><div class="spell-score" id="spellScore">Score: '+score+'</div>';
    container.appendChild(wrap);
    const wordEl=wrap.querySelector('#word');
    const opts=wrap.querySelector('#options');
    const scoreDiv=wrap.querySelector('#spellScore');
    let idx=0;
    let user=[];
    total+=cfg.exercises.length;
    load();
    function load(){
        if(idx>=cfg.exercises.length){ showBlock(i+1); return; }
        const ex=cfg.exercises[idx];
        wordEl.textContent=ex.hint;
        opts.innerHTML='';
        user=[];
        ex.options.forEach(o=>{
            const b=document.createElement('button');
            b.className='option';
            b.textContent=o;
            b.addEventListener('click',()=>choose(o));
            opts.appendChild(b);
        });
    }
    function choose(letter){
        const ex=cfg.exercises[idx];
        const cur=wordEl.textContent;
        user.push(letter);
        wordEl.textContent=cur.replace(/_/,letter);
        if(user.length===ex.answer.length){
            const ok=user.every((l,n)=>l===ex.answer[n]);
            wordEl.classList.add(ok?'correct':'incorrect');
            if(ok) score++;
            scoreDiv.textContent='Score: '+score;
            setTimeout(()=>{wordEl.classList.remove('correct','incorrect');idx++;load();},1500);
        }
    }
}
function hotspot(cfg,i){
    const container=document.getElementById('blocks');
    container.innerHTML='';
    const bodyDiv=document.createElement('div');
    bodyDiv.style.position='relative';
    bodyDiv.style.width='600px';
    bodyDiv.style.margin='20px auto';
    bodyDiv.style.backgroundImage='url('+cfg.image+')';
    bodyDiv.style.backgroundSize='contain';
    bodyDiv.style.backgroundRepeat='no-repeat';
    bodyDiv.style.backgroundPosition='center';
    bodyDiv.style.height='780px';
    container.appendChild(bodyDiv);
    const scoreDiv=document.createElement('div'); scoreDiv.id='score'; scoreDiv.textContent='Score: '+score; container.appendChild(scoreDiv);
    const message=document.createElement('div'); message.id='message'; container.appendChild(message);
    const quiz=document.createElement('div'); quiz.className='quiz-container'; quiz.id='quizContainer'; quiz.innerHTML='<h2></h2><div id="quizOptions"></div>'; container.appendChild(quiz);
    total+=cfg.hotspots.length;
    let remaining=cfg.hotspots.length;
    cfg.hotspots.forEach(hs=>{
        const spot=document.createElement('div');
        spot.className='hotspot';
        spot.style.left=hs.x+'%';
        spot.style.top=hs.y+'%';
        spot.textContent='?';
        bodyDiv.appendChild(spot);
        let state='hidden';
        spot.addEventListener('click',()=>{
            if(state==='hidden'){
                spot.textContent=hs.correct;
                spot.classList.add('expanded');
                state='revealed';
            }else if(state==='revealed'){
                spot.classList.remove('expanded');
                spot.textContent='✓';
                state='attempted';
                spot.style.pointerEvents='none';
                showQuiz(hs,spot);
            }
        });
    });
    function showQuiz(hs,spot){
        const question=quiz.querySelector('h2');
        const optionsDiv=document.getElementById('quizOptions');
        optionsDiv.innerHTML='';
        question.textContent="What's the word for \""+hs.en+"\"?";
        hs.options.forEach(o=>{
            const b=document.createElement('button'); b.textContent=o; b.addEventListener('click',()=>checkAnswer(hs,spot,o)); optionsDiv.appendChild(b);
        });
        quiz.style.display='block';
    }
    function checkAnswer(hs,spot,ans){
        if(ans===hs.correct){
            score+=20;
            spot.classList.add('correct');
            message.textContent='Correct!'; message.style.color='green';
        }else{
            spot.classList.add('incorrect');
            message.textContent='Incorrect. Correct: '+hs.correct; message.style.color='red';
        }
        remaining--; scoreDiv.textContent='Score: '+score; quiz.style.display='none';
        if(remaining===0){ setTimeout(()=>showBlock(i+1),500); }
    }
}
showBlock(current);
</script>
</body>
</html>
