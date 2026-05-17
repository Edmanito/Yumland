/* =========================================
   KAISEKI SHUNEI — MINIJEUX.JS
   Système de mini-jeux tout ou rien
   ========================================= */

const STAGES = [
    { id:'snake',  emoji:'🐍', title:'Snake express !',  desc:'Mange 6 pommes sans mourir. Sois précis et rapide !',                               time:24, goal:6 },
    { id:'click',  emoji:'🎯', title:'Clique rapide !',  desc:'Touche au moins 8 cibles en 12 secondes.',                                          time:12, goal:8 },
    { id:'memory', emoji:'🧠', title:'Mémoire !',        desc:'Mémorise et reproduis 4 séquences de couleurs.',                                    time:24, goal:4 },
    { id:'word',   emoji:'⌨️', title:'Frappe rapide !',  desc:'Tape 5 mots japonais avant la fin du temps.',                                       time:20, goal:5 },
    { id:'calc',   emoji:'🔢', title:'Calcul mental !',  desc:'Réponds à 5 calculs en 16 secondes. Dernière épreuve !',                            time:16, goal:5 },
];

let jStage = 0, jTotal = 0, jDone = [], jRunning = false;
let jInterval = null, jTimerInterval = null, jScore = 0, jGoal = 0;

/* ── NAVIGATION ÉCRANS ── */
function jShow(id) {
    document.querySelectorAll('.j-screen').forEach(s => s.classList.remove('active'));
    document.getElementById(id).classList.add('active');
}

/* ── OUVRIR / FERMER ── */
function ouvrirJeux() {
    document.getElementById('jeux-overlay').classList.add('active');
    document.body.style.overflow = 'hidden';
    jRefreshHome();
    jShow('j-home');
}

function fermerJeux() {
    document.getElementById('jeux-overlay').classList.remove('active');
    document.body.style.overflow = '';
    jRunning = false;
    clearInterval(jInterval);
    clearInterval(jTimerInterval);
}

function appliquerRemiseEtFermer() {
    document.getElementById('input-remise').value = jTotal;
    const badge = document.getElementById('badge-remise');
    if (badge) { badge.textContent = '-' + jTotal + '%'; badge.style.display = 'inline-block'; }
    fermerJeux();
}

/* ── ACCUEIL ── */
function jRefreshHome() {
    const c = document.getElementById('j-dots');
    c.innerHTML = '';
    STAGES.forEach((s, i) => {
        const d = document.createElement('div');
        d.className = 'j-dot ' + (jDone.includes(i) ? 'done' : i === jStage ? 'current' : '');
        d.textContent = jDone.includes(i) ? '✓' : (i + 1);
        c.appendChild(d);
    });
    document.getElementById('j-total-pct').textContent = jTotal + '%';
}

/* ── INTRO ── */
function jStartIntro() {
    const s = STAGES[jStage];
    document.getElementById('j-intro-emoji').textContent  = s.emoji;
    document.getElementById('j-intro-title').textContent  = 'Niveau ' + (jStage + 1) + ' — ' + s.title;
    document.getElementById('j-intro-desc').textContent   = s.desc;
    document.getElementById('j-intro-reward').textContent = '+4% → total ' + (jTotal + 4) + '%';
    jShow('j-intro');
}

/* ── LANCEMENT ── */
function jLaunch() {
    const s = STAGES[jStage];
    document.getElementById('j-game-label').textContent = 'Niveau ' + (jStage + 1) + ' — ' + s.title;
    document.getElementById('j-game-score').textContent = '';
    document.getElementById('j-game-msg').textContent   = '';
    jScore = 0; jGoal = s.goal; jRunning = true;
    jShow('j-game');
    jStartTimer(s.time);
    if (s.id === 'snake')  jSnake();
    if (s.id === 'click')  jClick();
    if (s.id === 'memory') jMemory();
    if (s.id === 'word')   jWord();
    if (s.id === 'calc')   jCalc();
}

/* ── TIMER ── */
function jStartTimer(secs) {
    const fill = document.getElementById('j-timer-fill');
    fill.style.width = '100%'; fill.style.background = '#22c55e';
    let t = secs;
    clearInterval(jTimerInterval);
    jTimerInterval = setInterval(() => {
        t -= 0.1;
        const pct = Math.max(0, t / secs * 100);
        fill.style.width = pct + '%';
        fill.style.background = pct > 50 ? '#22c55e' : pct > 25 ? '#f59e0b' : '#ef4444';
        if (t <= 0) { clearInterval(jTimerInterval); jEndGame(false); }
    }, 100);
}

function jStopTimer() { clearInterval(jTimerInterval); }

function jUpdateScore() {
    document.getElementById('j-game-score').textContent = jScore + ' / ' + jGoal;
}

/* ── FIN DE JEU ── */
function jEndGame(won) {
    jRunning = false; jStopTimer(); clearInterval(jInterval);
    document.getElementById('j-game-area').innerHTML = '';

    if (won) {
        jTotal += 4; jDone.push(jStage);
        const isLast = jStage >= STAGES.length - 1;
        document.getElementById('j-res-emoji').textContent = isLast ? '🏆' : '🎉';
        document.getElementById('j-res-title').textContent = 'Bravo ! +4% gagné !';
        document.getElementById('j-res-desc').textContent  = isLast
            ? 'Tu as tout réussi ! Applique ta réduction.'
            : 'Continue — attention, un raté et tout disparaît !';
        document.getElementById('j-res-pct').textContent   = jTotal + '%';
        document.getElementById('j-res-pct').style.color   = '#22c55e';
        document.getElementById('j-btn-retry').textContent = 'Abandonner';
        document.getElementById('j-btn-next').style.display = isLast ? 'none' : '';
        jShow('j-result');
        if (isLast) setTimeout(() => jShow('j-final'), 1200);
    } else {
        const perdu = jTotal;
        jTotal = 0; jDone = []; jStage = 0;
        document.getElementById('j-res-emoji').textContent = '💸';
        document.getElementById('j-res-title').textContent = 'Raté ! Tout est perdu !';
        document.getElementById('j-res-desc').textContent  = perdu > 0
            ? 'Tu avais ' + perdu + '% ... tout s\'envole. Recommence depuis le début !'
            : 'Dommage ! Recommence depuis le début.';
        document.getElementById('j-res-pct').textContent   = '0%';
        document.getElementById('j-res-pct').style.color   = '#ef4444';
        document.getElementById('j-btn-retry').textContent = 'Tout recommencer';
        document.getElementById('j-btn-next').style.display = 'none';
        jShow('j-result');
    }
    jRefreshHome();
}

/* ══════════════════════
   MINI-JEUX
══════════════════════ */

/* ── SNAKE ── */
function jSnake() {
    const area = document.getElementById('j-game-area');
    area.innerHTML = '<canvas id="jcanvas" width="280" height="280"></canvas>';
    const cv = document.getElementById('jcanvas');
    const ctx = cv.getContext('2d');
    const C = 14, SZ = 20;
    let snake = [{x:7,y:7},{x:6,y:7},{x:5,y:7}];
    let dir = {x:1,y:0}, nd = {x:1,y:0}, food = {x:10,y:5};

    function draw() {
        ctx.fillStyle = '#0a0a0a'; ctx.fillRect(0,0,280,280);
        ctx.strokeStyle = '#181818'; ctx.lineWidth = 0.5;
        for(let i=0;i<=C;i++){ctx.beginPath();ctx.moveTo(i*SZ,0);ctx.lineTo(i*SZ,280);ctx.stroke();}
        for(let j=0;j<=C;j++){ctx.beginPath();ctx.moveTo(0,j*SZ);ctx.lineTo(280,j*SZ);ctx.stroke();}
        snake.forEach((s,i) => {
            ctx.fillStyle = i===0?'#4ade80':i%2===0?'#16a34a':'#22c55e';
            ctx.beginPath(); ctx.roundRect(s.x*SZ+1,s.y*SZ+1,SZ-2,SZ-2,3); ctx.fill();
        });
        ctx.fillStyle = '#ef4444';
        ctx.beginPath(); ctx.roundRect(food.x*SZ+2,food.y*SZ+2,SZ-4,SZ-4,4); ctx.fill();
    }

    function spawnFood() {
        do { food = {x:Math.floor(Math.random()*C), y:Math.floor(Math.random()*C)}; }
        while(snake.some(s => s.x===food.x && s.y===food.y));
    }

    jInterval = setInterval(() => {
        if (!jRunning) { clearInterval(jInterval); return; }
        dir = nd;
        const h = {x:snake[0].x+dir.x, y:snake[0].y+dir.y};
        if (h.x<0||h.x>=C||h.y<0||h.y>=C||snake.some(s=>s.x===h.x&&s.y===h.y)) {
            jEndGame(false); clearInterval(jInterval); return;
        }
        snake.unshift(h);
        if (h.x===food.x && h.y===food.y) {
            jScore++; jUpdateScore();
            if (jScore>=jGoal) { jEndGame(true); clearInterval(jInterval); return; }
            spawnFood();
        } else snake.pop();
        draw();
    }, 155);

    draw(); jUpdateScore();

    document.addEventListener('keydown', function sk(e) {
        if (!jRunning) { document.removeEventListener('keydown', sk); return; }
        if (e.key==='ArrowUp'    && dir.y!==1)  nd = {x:0,  y:-1};
        if (e.key==='ArrowDown'  && dir.y!==-1) nd = {x:0,  y:1};
        if (e.key==='ArrowLeft'  && dir.x!==1)  nd = {x:-1, y:0};
        if (e.key==='ArrowRight' && dir.x!==-1) nd = {x:1,  y:0};
        e.preventDefault();
    });

    area.insertAdjacentHTML('beforeend', `
        <div class="j-snake-ctrl">
            <div></div><button id="jsu">↑</button><div></div>
            <button id="jsl">←</button><button id="jsd">↓</button><button id="jsr">→</button>
        </div>`);
    document.getElementById('jsu').onclick = () => { if(dir.y!==1)  nd={x:0,y:-1}; };
    document.getElementById('jsd').onclick = () => { if(dir.y!==-1) nd={x:0,y:1}; };
    document.getElementById('jsl').onclick = () => { if(dir.x!==1)  nd={x:-1,y:0}; };
    document.getElementById('jsr').onclick = () => { if(dir.x!==-1) nd={x:1,y:0}; };
}

/* ── CLIC ── */
function jClick() {
    const area = document.getElementById('j-game-area');
    area.innerHTML = '<div class="j-targets" id="jtarea"></div>';
    jUpdateScore();
    const tarea = document.getElementById('jtarea');
    const emojis = ['🍱','🍣','🍜','🥢','🌸','🎋'];

    function spawn() {
        if (!jRunning) return;
        const t = document.createElement('div');
        t.className = 'j-target';
        t.textContent = emojis[Math.floor(Math.random() * emojis.length)];
        t.style.left = Math.random() * 280 + 'px';
        t.style.top  = Math.random() * 200 + 'px';
        t.style.background = 'rgba(188,156,100,0.15)';
        tarea.appendChild(t);
        setTimeout(() => t.classList.add('show'), 10);
        t.addEventListener('click', () => {
            if (!jRunning) return;
            t.remove(); jScore++; jUpdateScore();
            if (jScore >= jGoal) jEndGame(true);
        });
        setTimeout(() => { if (t.parentNode) t.remove(); }, 1000);
    }

    jInterval = setInterval(spawn, 550);
    spawn();
}

/* ── MÉMOIRE ── */
function jMemory() {
    const area = document.getElementById('j-game-area');
    const COLS = ['#ef4444','#3b82f6','#22c55e','#f59e0b'];
    let seq = [], input = [], phase = 'show';
    jStopTimer();

    function buildGrid(clickable) {
        area.innerHTML = '';
        const msg = document.createElement('div');
        msg.style.cssText = 'text-align:center;font-size:13px;color:#666;margin-bottom:8px;';
        msg.textContent = clickable ? 'Reproduis la séquence !' : 'Mémorise...';
        area.appendChild(msg);
        const g = document.createElement('div');
        g.className = 'j-mem-grid';
        COLS.forEach((c, i) => {
            const b = document.createElement('button');
            b.className = 'j-mem-btn'; b.style.background = c; b.id = 'jmb' + i;
            if (clickable) {
                b.addEventListener('click', () => {
                    if (!jRunning || phase !== 'input') return;
                    flash(i); input.push(i);
                    if (input[input.length-1] !== seq[input.length-1]) { jEndGame(false); return; }
                    if (input.length === seq.length) {
                        jScore++; jUpdateScore();
                        if (jScore >= jGoal) { jEndGame(true); return; }
                        seq.push(Math.floor(Math.random()*4)); input = []; phase = 'show';
                        setTimeout(showSeq, 600);
                    }
                });
            }
            g.appendChild(b);
        });
        area.appendChild(g);
    }

    function flash(i) {
        const b = document.getElementById('jmb' + i);
        if (!b) return;
        b.style.opacity = '0.3';
        setTimeout(() => { if (b) b.style.opacity = '1'; }, 300);
    }

    function showSeq() {
        phase = 'show'; buildGrid(false); let idx = 0;
        const iv = setInterval(() => {
            if (idx >= seq.length) { clearInterval(iv); phase = 'input'; buildGrid(true); jStartTimer(24); return; }
            flash(seq[idx]); idx++;
        }, 700);
    }

    seq = [Math.floor(Math.random()*4), Math.floor(Math.random()*4)];
    buildGrid(false);
    setTimeout(showSeq, 500);
    jUpdateScore();
}

/* ── MOT ── */
function jWord() {
    const WORDS = ['sushi','ramen','wasabi','mochi','kaiseki','matcha','gyoza','tempura','sakura','dashi'];
    const area = document.getElementById('j-game-area');
    let words = [], idx = 0;
    for (let i = 0; i < 5; i++) words.push(WORDS[Math.floor(Math.random() * WORDS.length)]);

    function showWord() {
        if (idx >= words.length) { jEndGame(true); return; }
        area.innerHTML = `
            <p style="font-size:12px;color:#666;margin-bottom:8px;">Mot ${idx+1} / ${words.length}</p>
            <div class="j-word-display">${words[idx]}</div>
            <input id="jwinp" class="j-input" autocomplete="off" style="margin-top:12px;" placeholder="Tape le mot..."/>`;
        document.getElementById('jwinp').focus();
        document.getElementById('jwinp').addEventListener('input', e => {
            if (e.target.value.toLowerCase() === words[idx]) {
                idx++; jScore++; jUpdateScore();
                document.getElementById('j-game-msg').textContent = '✓';
                setTimeout(() => { document.getElementById('j-game-msg').textContent = ''; showWord(); }, 300);
            }
        });
    }

    jUpdateScore(); showWord();
}

/* ── CALCUL ── */
function jCalc() {
    const area = document.getElementById('j-game-area');
    let answer = 0;

    function newQ() {
        const ops = ['+', '-', '×'];
        const op = ops[Math.floor(Math.random() * 3)];
        let a = Math.floor(Math.random()*30) + 5;
        let b = Math.floor(Math.random()*12) + 2;
        if (op === '+') answer = a + b;
        if (op === '-') { if(a<b) [a,b]=[b,a]; answer = a - b; }
        if (op === '×') { a=Math.floor(Math.random()*9)+2; b=Math.floor(Math.random()*9)+2; answer=a*b; }
        const d = area.querySelector('.j-calc-display');
        if (d) d.textContent = a + ' ' + op + ' ' + b + ' = ?';
    }

    area.innerHTML = `
        <div class="j-calc-display">...</div>
        <div style="display:flex;gap:8px;margin:10px 0;width:100%;max-width:280px;">
            <input id="jcinp" type="number" class="j-input" placeholder="Réponse" style="flex:1;"/>
            <button class="j-btn green" id="jcok">OK</button>
        </div>`;

    newQ(); jUpdateScore();

    document.getElementById('jcok').addEventListener('click', () => {
        const v = parseInt(document.getElementById('jcinp').value);
        if (v === answer) {
            jScore++; jUpdateScore();
            document.getElementById('j-game-msg').textContent = '✓ Correct !';
            if (jScore >= jGoal) { jEndGame(true); return; }
            document.getElementById('jcinp').value = ''; newQ();
        } else {
            document.getElementById('j-game-msg').textContent = '✗ Raté ! Réponse : ' + answer;
            setTimeout(() => document.getElementById('j-game-msg').textContent = '', 1200);
            document.getElementById('jcinp').value = ''; newQ();
        }
    });

    document.getElementById('jcinp').addEventListener('keydown', e => {
        if (e.key === 'Enter') document.getElementById('jcok').click();
    });
}

/* ── ÉCOUTEURS ── */
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('j-btn-start')?.addEventListener('click', jStartIntro);
    document.getElementById('j-btn-launch')?.addEventListener('click', jLaunch);
    document.getElementById('j-btn-retry')?.addEventListener('click', () => {
        jTotal = 0; jDone = []; jStage = 0; jRefreshHome(); jShow('j-home');
    });
    document.getElementById('j-btn-next')?.addEventListener('click', () => {
        jStage++; jRefreshHome(); jStartIntro();
    });
});
