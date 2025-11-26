/**
 * Capivarinha_Curitibaninha - Lógica do Jogo
 * Autor: Maoly Lara Serrano
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // --- 1. Seleção de Elementos do DOM ---
    const wordDisplay = document.getElementById('current-word');
    const inputField = document.getElementById('typing-input');
    const scoreDisplay = document.getElementById('score');
    const livesDisplay = document.getElementById('lives');
    const mascot = document.getElementById('mascot');
    
    // Telas
    const startScreen = document.getElementById('start-screen');
    const gameScreen = document.getElementById('gameplay-screen');
    const appFrame = document.querySelector('.app-frame'); // Para tremer a tela

    // Botões e Feedback
    const btnStart = document.getElementById('btn-start');
    const timerFill = document.getElementById('timer-fill');
    const timerBar = document.getElementById('timer-bar');
    const feedbackMsg = document.getElementById('feedback-msg');

    // --- 2. Estado do Jogo ---
    let wordsQueue = [];
    let currentWordObj = "";
    let score = 0;
    let lives = 3;
    let acertos = 0;
    let timerInterval;
    let timeLeft = 100; // Porcentagem (100% a 0%)
    let gameActive = false;

    // --- 3. Configuração de Assets ---
    const IMG_HAPPY = 'assets/img/char-capivara-happy.png';
    const IMG_SAD = 'assets/img/char-capivara-sad.png';

    // Normalizador de Texto (Remove acentos e minúsculas)
    // Ex: "Araucária" -> "araucaria"
    function normalize(str) {
        return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().trim();
    }

    // --- 4. Funções de API (Back-end) ---

    async function fetchWords() {
        try {
            const response = await fetch('api/get_palavras.php');
            if (!response.ok) throw new Error('Erro na rede');
            const data = await response.json();
            wordsQueue = data; // Espera-se um array de strings
            console.log("Palavras carregadas:", wordsQueue);
        } catch (error) {
            console.error("Erro ao buscar gírias:", error);
            wordDisplay.innerText = "Erro de conexão :(";
            wordDisplay.style.color = "red";
        }
    }

    async function saveScore() {
        try {
            const resp = await fetch('api/save_score.php', {
                method: 'POST',
                credentials: 'same-origin', // garante envio de cookies/sessão
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ pontos: score, acertos: acertos })
            });

            // Log de erro caso a API responda com falha (útil para debug)
            if (!resp.ok) {
                const json = await resp.json().catch(() => null);
                console.error('Falha ao salvar pontuação:', resp.status, json);
                // Mostrar feedback ao usuário se a sessão expirou ou houve erro
                if (json && json.message) showFeedback(json.message, 'red');
                else showFeedback('Não foi possível salvar sua pontuação.', 'red');
            } else {
                const json = await resp.json().catch(() => null);
                console.log('saveScore response:', json);
                // Mostrar mensagem curta de sucesso (silencioso)
                if (json && json.status === 'success') {
                    showFeedback('Pontuação salva!', 'green');
                }
            }
        } catch (error) {
            console.error("Erro ao salvar pontuação:", error);
        }
    }

    // --- 5. Lógica do Jogo ---

    // Botão Iniciar
    btnStart.addEventListener('click', async () => {
        btnStart.disabled = true;
        btnStart.innerText = "Carregando...";
        
        await fetchWords();
        
        if (wordsQueue && wordsQueue.length > 0) {
            startGame();
        } else {
            btnStart.innerText = "Tentar Novamente";
            btnStart.disabled = false;
        }
    });

    function startGame() {
        // Resetar variáveis
        score = 0;
        lives = 3;
        acertos = 0;
        gameActive = true;
        
        // Atualizar UI inicial
        updateUI();
        startScreen.classList.add('hidden');
        gameScreen.style.display = 'block'; // Mostra a tela de jogo
        gameScreen.classList.remove('hidden');
        
        // Foco no input
        inputField.value = '';
        inputField.focus();

        nextWord();
    }

    function nextWord() {
        if (!gameActive) return;

        // Se acabaram as palavras, o jogador venceu o "deck" atual
        if (wordsQueue.length === 0) {
            endGame(true); 
            return;
        }
        
        // Pega a próxima palavra e atualiza UI
        currentWordObj = wordsQueue.pop();
        // Animação de "Pop" na palavra
        wordDisplay.classList.remove('word-pop');
        void wordDisplay.offsetWidth;
        wordDisplay.classList.add('word-pop');
        // Mostra a dica se existir, senão mostra a palavra
        if (typeof currentWordObj === 'object' && currentWordObj.dica) {
            wordDisplay.innerText = currentWordObj.dica;
        } else if (typeof currentWordObj === 'object' && currentWordObj.termo) {
            wordDisplay.innerText = currentWordObj.termo;
        } else {
            wordDisplay.innerText = currentWordObj;
        }
        // Debug opcional
        console.log("Dica:", currentWordObj.dica, "| Resposta:", currentWordObj.termo);
        inputField.value = '';
        mascot.src = IMG_HAPPY;
        mascot.classList.remove('mascot-jump');
        resetTimer();
    }

    function resetTimer() {
        clearInterval(timerInterval);
        timeLeft = 100;
        timerFill.style.width = '100%';
        timerFill.className = ''; // Remove classes de alerta
        
        // A velocidade aumenta conforme os acertos (Dificuldade Progressiva)
        // Base: 50ms. A cada 5 acertos, diminui 2ms (mais rápido)
        const speed = Math.max(20, 50 - (Math.floor(acertos / 5) * 5));
        
        timerInterval = setInterval(() => {
            if (!gameActive) {
                clearInterval(timerInterval);
                return;
            }

            timeLeft -= 0.5; // Decremento
            timerFill.style.width = timeLeft + '%';

            // Mudança de Cor da Barra
            if (timeLeft < 40 && timeLeft > 20) {
                timerFill.classList.add('timer-warning');
            } else if (timeLeft <= 20) {
                timerFill.classList.remove('timer-warning');
                timerFill.classList.add('timer-critical');
            }

            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                handleMistake("O tempo acabou, piá!");
            }
        }, speed);
    }

    function handleSuccess() {
        // Pontuação: Base (10) + Bônus de Tempo (0 a 10)
        const timeBonus = Math.floor(timeLeft / 10);
        const points = 10 + timeBonus;
        
        score += points;
        acertos++;
        updateUI();

        // Feedback Visual
        inputField.classList.add('correct');
        mascot.classList.add('mascot-jump'); // Pulo de alegria
        showFeedback(`+${points} pts!`, 'green');

        // Som (opcional, se quiser adicionar depois)
        // playSound('correct');

        setTimeout(() => {
            inputField.classList.remove('correct');
            nextWord();
        }, 300); // Pequeno delay para ver o acerto
    }

    function handleMistake(reason = "Errou!") {
        lives--;
        updateUI();
        
        // Feedback Visual de Erro
        mascot.src = IMG_SAD;
        inputField.classList.add('wrong');
        appFrame.classList.add('screen-shake'); // Treme a tela inteira
        showFeedback(reason, 'red');

        // Remove classes após animação
        setTimeout(() => {
            inputField.classList.remove('wrong');
            appFrame.classList.remove('screen-shake');
            
            if (lives <= 0) {
                endGame(false);
            } else {
                nextWord(); // Passa para a próxima mesmo se errou
            }
        }, 600);
    }

    // Validação em Tempo Real (Input) com Feedback Visual Melhorado
    inputField.addEventListener('input', () => {
        if (!gameActive) return;

        const typed = normalize(inputField.value);
        // Compatibilidade: busca 'termo' se existir, senão usa string direta
        const targetWord = (typeof currentWordObj === 'object' && currentWordObj.termo) ? currentWordObj.termo : currentWordObj;
        const target = normalize(targetWord);

        // Feedback visual instantâneo com cores dinâmicas
        if (typed.length === 0) {
            // Campo vazio - estado neutro
            inputField.style.borderColor = "#CCC";
            inputField.style.backgroundColor = "#FFF";
            mascot.src = IMG_HAPPY;
            inputField.classList.remove('wrong');
        } else if (target.startsWith(typed)) {
            // Início da palavra está correto - verde (UX positiva)
            inputField.style.borderColor = "#4CAF50";
            inputField.style.backgroundColor = "#F1F8F6";
            mascot.src = IMG_HAPPY;
            inputField.classList.remove('wrong');
        } else {
            // Letra errada - vermelho (UX negativa)
            inputField.style.borderColor = "#F44336";
            inputField.style.backgroundColor = "#FFF3F3";
            mascot.src = IMG_SAD;
            inputField.classList.add('wrong');
        }

        // Verifica se completou a palavra
        if (typed === target) {
            handleSuccess();
        }

    });

    // Função auxiliar de UI
    function updateUI() {
        scoreDisplay.innerText = score;
        livesDisplay.innerText = lives;
    }

    function showFeedback(text, color) {
        feedbackMsg.innerText = text;
        feedbackMsg.style.color = color;
        feedbackMsg.classList.add('show-feedback');
        setTimeout(() => {
            feedbackMsg.classList.remove('show-feedback');
        }, 1000);
    }

    // --- 6. Fim de Jogo ---

    async function endGame(victory) {
        gameActive = false;
        clearInterval(timerInterval);
        
        // Salva pontuação silenciosamente
        await saveScore();

        // Prepara HTML da tela final
        const title = victory ? "Trilegal! Zerou o jogo!" : "Bah, piá... não deu!";
        const message = victory ? "Você manja muito de Curitiba!" : "Tente novamente pra não passar vergonha no tubo.";
        const image = victory ? IMG_HAPPY : IMG_SAD;

        gameScreen.innerHTML = `
            <div id="end-screen" class="screen-section">
                <img src="${image}" style="width: 120px; margin-bottom: 15px;">
                <h2 style="color: ${victory ? '#2E7D32' : '#C62828'}">${title}</h2>
                <p id="end-message">${message}</p>
                
                <div>Sua Pontuação Final</div>
                <div id="final-score">${score}</div>
                
                <div style="display: flex; gap: 10px; justify-content: center;">
                    <button class="btn-game btn-restart" onclick="location.reload()">Jogar Novamente</button>
                    <button class="btn-game" onclick="window.location.href='index.php?page=ranking'">Ver Ranking</button>
                </div>
            </div>
        `;
        
        // Se venceu, joga confetes (CSS)
        if (victory) {
            for(let i=0; i<30; i++) {
                createConfetti();
            }
        }
    }

    function createConfetti() {
        const confetti = document.createElement('div');
        confetti.classList.add('confetti');
        confetti.style.left = Math.random() * 100 + 'vw';
        confetti.style.animationDuration = Math.random() * 3 + 2 + 's';
        confetti.style.backgroundColor = ['#FFC107', '#2E7D32', '#0288D1', '#FFF'][Math.floor(Math.random() * 4)];
        document.body.appendChild(confetti);
        
        setTimeout(() => confetti.remove(), 5000);
    }
});