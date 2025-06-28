// Variables globales
let currentPage = 'main';
let currentGame = null;

// Variables del juego Ahorcado
let hangmanWords = ['JAVASCRIPT', 'REACT', 'HTML', 'CSS', 'PROGRAMACION', 'DESARROLLO', 'COMPUTADORA', 'INTERNET', 'TECNOLOGIA', 'SOFTWARE'];
let currentWord = '';
let guessedLetters = [];
let wrongGuesses = 0;
const maxWrongGuesses = 6;

// Variables del Quiz
let quizQuestions = [
    {
        question: "¿Cuál es la capital de Francia?",
        options: ["Londres", "Berlín", "París", "Madrid"],
        correct: 2
    },
    {
        question: "¿En qué año llegó el hombre a la Luna?",
        options: ["1967", "1969", "1971", "1973"],
        correct: 1
    },
    {
        question: "¿Cuál es el planeta más grande del sistema solar?",
        options: ["Saturno", "Júpiter", "Neptuno", "Tierra"],
        correct: 1
    },
    {
        question: "¿Quién escribió 'Don Quijote de la Mancha'?",
        options: ["Lope de Vega", "Miguel de Cervantes", "Garcilaso de la Vega", "Francisco de Quevedo"],
        correct: 1
    },
    {
        question: "¿Cuál es el océano más grande del mundo?",
        options: ["Atlántico", "Índico", "Ártico", "Pacífico"],
        correct: 3
    }
];
let currentQuestionIndex = 0;
let quizScore = 0;
let selectedAnswer = null;

// Variables del Memorama
let memoryCards = [];
let flippedCards = [];
let matchedPairs = 0;
let moves = 0;
const memoryEmojis = ['🎮', '🎯', '🎲', '🎪', '🎨', '🎭', '🎵', '🎸'];

// Variables del ChatBot
let chatMessages = [];

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    showPage('main');
    initializeChatbot();
});

// Navegación entre páginas
function showPage(pageId) {
    // Ocultar todas las páginas
    const pages = document.querySelectorAll('.page');
    pages.forEach(page => page.classList.remove('active'));
    
    // Mostrar la página seleccionada
    const targetPage = document.getElementById(pageId + '-page');
    if (targetPage) {
        targetPage.classList.add('active');
        currentPage = pageId;
    }
    
    // Inicializar contenido específico de la página
    if (pageId === 'games') {
        showGamesMenu();
    } else if (pageId === 'realmadrid') {
        showTab('historia');
    }
}

// SECCIÓN DE JUEGOS
function showGame(gameType) {
    // Ocultar menú de juegos
    document.getElementById('games-menu').style.display = 'none';
    
    // Ocultar todos los juegos
    const gameContents = document.querySelectorAll('.game-content');
    gameContents.forEach(game => game.style.display = 'none');
    
    // Mostrar el juego seleccionado
    const targetGame = document.getElementById(gameType + '-game');
    if (targetGame) {
        targetGame.style.display = 'block';
        currentGame = gameType;
        
        // Inicializar el juego
        if (gameType === 'hangman') {
            initializeHangman();
        } else if (gameType === 'quiz') {
            initializeQuiz();
        } else if (gameType === 'memory') {
            initializeMemoryGame();
        }
    }
}

function showGamesMenu() {
    document.getElementById('games-menu').style.display = 'block';
    const gameContents = document.querySelectorAll('.game-content');
    gameContents.forEach(game => game.style.display = 'none');
    currentGame = null;
}

// JUEGO AHORCADO
function initializeHangman() {
    currentWord = hangmanWords[Math.floor(Math.random() * hangmanWords.length)];
    guessedLetters = [];
    wrongGuesses = 0;
    
    updateHangmanDisplay();
    createAlphabetButtons();
    document.getElementById('new-word-btn').style.display = 'none';
}

function createAlphabetButtons() {
    const container = document.getElementById('alphabet-buttons');
    container.innerHTML = '';
    
    const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    for (let letter of alphabet) {
        const button = document.createElement('button');
        button.textContent = letter;
        button.onclick = () => guessLetter(letter);
        container.appendChild(button);
    }
}

function guessLetter(letter) {
    if (guessedLetters.includes(letter)) return;
    
    guessedLetters.push(letter);
    
    const button = Array.from(document.getElementById('alphabet-buttons').children)
        .find(btn => btn.textContent === letter);
    
    if (currentWord.includes(letter)) {
        button.classList.add('correct');
        button.disabled = true;
        
        // Verificar si ganó
        if (currentWord.split('').every(char => guessedLetters.includes(char))) {
            setTimeout(() => {
                alert('¡Felicidades! Ganaste 🎉');
                document.getElementById('new-word-btn').style.display = 'block';
            }, 500);
        }
    } else {
        button.classList.add('incorrect');
        button.disabled = true;
        wrongGuesses++;
        
        // Verificar si perdió
        if (wrongGuesses >= maxWrongGuesses) {
            setTimeout(() => {
                alert(`Perdiste 😢 La palabra era: ${currentWord}`);
                document.getElementById('new-word-btn').style.display = 'block';
            }, 500);
        }
    }
    
    updateHangmanDisplay();
}

function updateHangmanDisplay() {
    // Actualizar emoji
    const emojis = ['😊', '😐', '😕', '😟', '😨', '😰', '💀'];
    document.getElementById('hangman-emoji').textContent = emojis[wrongGuesses] || '💀';
    
    // Actualizar palabra
    const display = currentWord.split('').map(letter => 
        guessedLetters.includes(letter) ? letter : '_'
    ).join(' ');
    document.getElementById('word-display').textContent = display;
    
    // Actualizar intentos
    document.getElementById('attempts-left').textContent = 
        `Intentos restantes: ${maxWrongGuesses - wrongGuesses}`;
}

function newHangmanGame() {
    initializeHangman();
}

// JUEGO QUIZ
function initializeQuiz() {
    currentQuestionIndex = 0;
    quizScore = 0;
    selectedAnswer = null;
    
    document.getElementById('next-question-btn').style.display = 'none';
    document.getElementById('restart-quiz-btn').style.display = 'none';
    
    showQuestion();
}

function showQuestion() {
    const question = quizQuestions[currentQuestionIndex];
    
    document.getElementById('question-text').textContent = question.question;
    document.getElementById('question-counter').textContent = 
        `Pregunta ${currentQuestionIndex + 1} de ${quizQuestions.length}`;
    document.getElementById('score-display').textContent = `Puntuación: ${quizScore}`;
    
    // Actualizar barra de progreso
    const progress = ((currentQuestionIndex + 1) / quizQuestions.length) * 100;
    document.getElementById('progress-bar').style.setProperty('--progress', progress + '%');
    
    // Crear botones de opciones
    const container = document.getElementById('options-container');
    container.innerHTML = '';
    
    question.options.forEach((option, index) => {
        const button = document.createElement('button');
        button.className = 'option-btn';
        button.textContent = option;
        button.onclick = () => selectAnswer(index);
        container.appendChild(button);
    });
    
    selectedAnswer = null;
    document.getElementById('next-question-btn').style.display = 'none';
}

function selectAnswer(answerIndex) {
    if (selectedAnswer !== null) return;
    
    selectedAnswer = answerIndex;
    const question = quizQuestions[currentQuestionIndex];
    const buttons = document.querySelectorAll('.option-btn');
    
    buttons.forEach((button, index) => {
        button.disabled = true;
        if (index === question.correct) {
            button.classList.add('correct');
        } else if (index === selectedAnswer) {
            button.classList.add('incorrect');
        }
    });
    
    if (selectedAnswer === question.correct) {
        quizScore++;
    }
    
    document.getElementById('next-question-btn').style.display = 'block';
}

function nextQuestion() {
    if (currentQuestionIndex < quizQuestions.length - 1) {
        currentQuestionIndex++;
        showQuestion();
    } else {
        showQuizResults();
    }
}

function showQuizResults() {
    const container = document.getElementById('question-container');
    const emoji = quizScore >= 4 ? '🏆' : quizScore >= 3 ? '🥉' : quizScore >= 2 ? '📚' : '📖';
    const message = quizScore >= 4 ? '¡Excelente conocimiento!' : 
                   quizScore >= 3 ? '¡Buen trabajo!' : 
                   quizScore >= 2 ? 'No está mal, pero puedes mejorar' : 
                   '¡Sigue estudiando!';
    
    container.innerHTML = `
        <div style="text-align: center;">
            <div style="font-size: 4rem; margin-bottom: 20px;">${emoji}</div>
            <h3>Puntuación Final: ${quizScore}/${quizQuestions.length}</h3>
            <p style="margin: 20px 0;">${message}</p>
        </div>
    `;
    
    document.getElementById('next-question-btn').style.display = 'none';
    document.getElementById('restart-quiz-btn').style.display = 'block';
}

function restartQuiz() {
    initializeQuiz();
}

// JUEGO MEMORAMA
function initializeMemoryGame() {
    moves = 0;
    matchedPairs = 0;
    flippedCards = [];
    
    // Crear cartas
    memoryCards = [];
    const shuffledEmojis = [...memoryEmojis, ...memoryEmojis].sort(() => Math.random() - 0.5);
    
    shuffledEmojis.forEach((emoji, index) => {
        memoryCards.push({
            id: index,
            emoji: emoji,
            isFlipped: false,
            isMatched: false
        });
    });
    
    renderMemoryBoard();
    updateMovesCounter();
}

function renderMemoryBoard() {
    const board = document.getElementById('memory-board');
    board.innerHTML = '';
    
    memoryCards.forEach(card => {
        const cardElement = document.createElement('button');
        cardElement.className = 'memory-card';
        cardElement.textContent = card.isFlipped || card.isMatched ? card.emoji : '?';
        cardElement.onclick = () => flipCard(card.id);
        
        if (card.isFlipped) cardElement.classList.add('flipped');
        if (card.isMatched) cardElement.classList.add('matched');
        
        board.appendChild(cardElement);
    });
}

function flipCard(cardId) {
    if (flippedCards.length === 2) return;
    if (flippedCards.includes(cardId)) return;
    if (memoryCards[cardId].isMatched) return;
    
    memoryCards[cardId].isFlipped = true;
    flippedCards.push(cardId);
    renderMemoryBoard();
    
    if (flippedCards.length === 2) {
        setTimeout(checkMatch, 1000);
    }
}

function checkMatch() {
    const [first, second] = flippedCards;
    const firstCard = memoryCards[first];
    const secondCard = memoryCards[second];
    
    moves++;
    updateMovesCounter();
    
    if (firstCard.emoji === secondCard.emoji) {
        // Match encontrado
        firstCard.isMatched = true;
        secondCard.isMatched = true;
        matchedPairs++;
        
        if (matchedPairs === memoryEmojis.length) {
            setTimeout(() => {
                alert(`¡Felicidades! Completaste el juego en ${moves} movimientos 🎉`);
            }, 500);
        }
    } else {
        // No hay match
        firstCard.isFlipped = false;
        secondCard.isFlipped = false;
    }
    
    flippedCards = [];
    renderMemoryBoard();
}

function updateMovesCounter() {
    document.getElementById('moves-counter').textContent = `Movimientos: ${moves}`;
}

function resetMemoryGame() {
    initializeMemoryGame();
}

// CHATBOT
function initializeChatbot() {
    chatMessages = [
        {
            text: "¡Hola! Soy tu asistente virtual. ¿En qué puedo ayudarte hoy?",
            isBot: true,
            timestamp: new Date()
        }
    ];
    renderChatMessages();
}

function renderChatMessages() {
    const container = document.getElementById('chat-messages');
    container.innerHTML = '';
    
    chatMessages.forEach(message => {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${message.isBot ? 'bot' : 'user'}`;
        
        const time = message.timestamp.toLocaleTimeString([], { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        messageDiv.innerHTML = `
            <div>${message.text}</div>
            <div class="message-time">${time}</div>
        `;
        
        container.appendChild(messageDiv);
    });
    
    container.scrollTop = container.scrollHeight;
}

function sendMessage() {
    const input = document.getElementById('chat-input');
    const text = input.value.trim();
    
    if (!text) return;
    
    // Agregar mensaje del usuario
    chatMessages.push({
        text: text,
        isBot: false,
        timestamp: new Date()
    });
    
    input.value = '';
    renderChatMessages();
    
    // Mostrar indicador de escritura
    showTypingIndicator();
    
    // Generar respuesta del bot después de un delay
    setTimeout(() => {
        hideTypingIndicator();
        const botResponse = getBotResponse(text);
        chatMessages.push({
            text: botResponse,
            isBot: true,
            timestamp: new Date()
        });
        renderChatMessages();
    }, 1000 + Math.random() * 1000);
}

function showTypingIndicator() {
    const container = document.getElementById('chat-messages');
    const typingDiv = document.createElement('div');
    typingDiv.id = 'typing-indicator';
    typingDiv.className = 'typing-indicator';
    typingDiv.innerHTML = `
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
    `;
    container.appendChild(typingDiv);
    container.scrollTop = container.scrollHeight;
}

function hideTypingIndicator() {
    const indicator = document.getElementById('typing-indicator');
    if (indicator) {
        indicator.remove();
    }
}

function getBotResponse(userMessage) {
    const message = userMessage.toLowerCase();
    
    if (message.includes('hola') || message.includes('hi')) {
        return "¡Hola! ¿Cómo estás? ¿En qué puedo ayudarte?";
    }
    
    if (message.includes('nombre')) {
        return "Soy ChatBot, tu asistente virtual. Puedo ayudarte con información general y mantener una conversación contigo.";
    }
    
    if (message.includes('tiempo') || message.includes('clima')) {
        return "No tengo acceso a información meteorológica en tiempo real, pero te recomiendo consultar tu aplicación de clima favorita.";
    }
    
    if (message.includes('edad') || message.includes('años')) {
        return "Soy un programa de computadora, así que no tengo edad en el sentido tradicional. Fui creado para esta demostración.";
    }
    
    if (message.includes('ayuda') || message.includes('help')) {
        return "Puedo ayudarte con:\n• Responder preguntas generales\n• Mantener conversaciones\n• Proporcionar información básica\n• ¡Y mucho más! Solo pregúntame algo.";
    }
    
    if (message.includes('gracias') || message.includes('thanks')) {
        return "¡De nada! Es un placer poder ayudarte. ¿Hay algo más en lo que pueda asistirte?";
    }
    
    if (message.includes('adiós') || message.includes('bye')) {
        return "¡Hasta luego! Ha sido genial conversar contigo. ¡Que tengas un excelente día!";
    }
    
    if (message.includes('real madrid') || message.includes('fútbol')) {
        return "¡Ah, un fanático del fútbol! El Real Madrid es uno de los clubes más exitosos del mundo. ¿Te interesa saber más sobre algún aspecto específico del equipo?";
    }
    
    if (message.includes('programación') || message.includes('código')) {
        return "¡Me encanta hablar de programación! Es un campo fascinante que permite crear soluciones increíbles. ¿Hay algún lenguaje de programación que te interese especialmente?";
    }
    
    // Respuestas generales
    const generalResponses = [
        "Esa es una pregunta interesante. Cuéntame más al respecto.",
        "Entiendo tu punto. ¿Podrías darme más detalles?",
        "Es un tema fascinante. ¿Qué opinas tú sobre eso?",
        "Me parece muy interesante lo que dices. ¿Hay algo específico que te gustaría saber?",
        "Gracias por compartir eso conmigo. ¿En qué más puedo ayudarte?",
        "¡Qué perspectiva tan interesante! ¿Te gustaría explorar este tema más a fondo?"
    ];
    
    return generalResponses[Math.floor(Math.random() * generalResponses.length)];
}

function handleChatKeyPress(event) {
    if (event.key === 'Enter') {
        sendMessage();
    }
}

// REAL MADRID
function showTab(tabName) {
    // Ocultar todas las pestañas
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(tab => tab.classList.remove('active'));
    
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(btn => btn.classList.remove('active'));
    
    // Mostrar la pestaña seleccionada
    const targetTab = document.getElementById(tabName + '-tab');
    const targetButton = Array.from(tabButtons).find(btn => 
        btn.textContent.includes(getTabDisplayName(tabName))
    );
    
    if (targetTab) {
        targetTab.classList.add('active');
    }
    
    if (targetButton) {
        targetButton.classList.add('active');
    }
}

function getTabDisplayName(tabName) {
    const names = {
        'historia': 'Historia',
        'jugadores': 'Jugadores',
        'estadio': 'Estadio',
        'titulos': 'Títulos'
    };
    return names[tabName] || tabName;
}

// PROYECTO
function openExternalProject() {
    const url = document.getElementById('project-url').value;
    if (url) {
        window.open(url, '_blank', 'noopener,noreferrer');
    } else {
        alert('Por favor, ingresa una URL válida.');
    }
}