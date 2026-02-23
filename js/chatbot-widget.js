// Travel Tales Chatbot Widget
(function () {
    // Create and inject the chatbot styles
    const injectStyles = () => {
        const styles = `
            :root {
                --primary-color: #667eea;
                --secondary-color: #764ba2;
                --background-color: #f5f7fa;
                --text-color: #2d3748;
            }

            /* Floating Chat Button */
            .floating-chat-button {
                position: fixed;
                bottom: 30px;
                right: 30px;
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
                color: white;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
                transition: all 0.3s ease;
                z-index: 1000;
            }

            .floating-chat-button:hover {
                transform: scale(1.1);
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
            }

            .floating-chat-button i {
                font-size: 24px;
            }

            .floating-chat-button .notification-badge {
                position: absolute;
                top: -5px;
                right: -5px;
                background: #ff4757;
                color: white;
                width: 20px;
                height: 20px;
                border-radius: 50%;
                font-size: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.2); }
                100% { transform: scale(1); }
            }

            /* Chat Widget */
            .chat-widget {
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 1000;
                font-family: 'Poppins', sans-serif;
            }

            .chat-container {
                position: absolute;
                bottom: 80px;
                right: 0;
                width: 350px;
                height: 500px;
                background: white;
                border-radius: 20px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                display: none;
                overflow: hidden;
                transition: all 0.3s ease;
            }

            .chat-container.active {
                display: flex;
                flex-direction: column;
                animation: slideUp 0.3s ease;
            }

            .chat-header {
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
                color: white;
                padding: 20px;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .chat-header h3 {
                font-size: 1.1rem;
                font-weight: 500;
            }

            .chat-close {
                cursor: pointer;
                font-size: 1.2rem;
            }

            .chat-messages {
                flex: 1;
                padding: 20px;
                overflow-y: auto;
                display: flex;
                flex-direction: column;
                gap: 15px;
            }

            .message {
                max-width: 80%;
                padding: 12px 16px;
                border-radius: 15px;
                font-size: 0.9rem;
                line-height: 1.4;
                white-space: pre-line;
            }

            .bot-message {
                background: var(--background-color);
                color: var(--text-color);
                align-self: flex-start;
                border-bottom-left-radius: 5px;
            }

            .user-message {
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
                color: white;
                align-self: flex-end;
                border-bottom-right-radius: 5px;
            }

            .chat-input {
                padding: 20px;
                border-top: 1px solid #edf2f7;
                display: flex;
                gap: 10px;
            }

            .chat-input input {
                flex: 1;
                padding: 12px;
                border: 1px solid #e2e8f0;
                border-radius: 25px;
                outline: none;
                font-family: inherit;
                font-size: 0.9rem;
            }

            .chat-input input:focus {
                border-color: var(--primary-color);
            }

            .send-button {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
                color: white;
                border: none;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: transform 0.2s ease;
            }

            .send-button:hover {
                transform: scale(1.1);
            }

            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .typing-indicator {
                display: flex;
                gap: 5px;
                padding: 12px 16px;
                background: var(--background-color);
                border-radius: 15px;
                align-self: flex-start;
                border-bottom-left-radius: 5px;
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .typing-indicator.active {
                opacity: 1;
            }

            .typing-dot {
                width: 8px;
                height: 8px;
                background: var(--text-color);
                border-radius: 50%;
                animation: typing 1s infinite;
            }

            .typing-dot:nth-child(2) { animation-delay: 0.2s; }
            .typing-dot:nth-child(3) { animation-delay: 0.4s; }

            @keyframes typing {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-5px); }
            }

            .suggestions {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-top: 10px;
            }

            .suggestion-chip {
                background: rgba(102, 126, 234, 0.1);
                color: var(--primary-color);
                padding: 6px 12px;
                border-radius: 15px;
                font-size: 0.85rem;
                cursor: pointer;
                transition: all 0.2s ease;
                border: 1px solid rgba(102, 126, 234, 0.2);
            }

            .suggestion-chip:hover {
                background: rgba(102, 126, 234, 0.2);
                transform: translateY(-1px);
            }

            .emoji-picker {
                position: absolute;
                bottom: 100%;
                right: 0;
                background: white;
                border-radius: 10px;
                box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                padding: 10px;
                display: none;
                grid-template-columns: repeat(6, 1fr);
                gap: 5px;
                margin-bottom: 10px;
            }

            .emoji-picker.active {
                display: grid;
            }

            .emoji-btn {
                width: 30px;
                height: 30px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                border-radius: 5px;
                transition: background 0.2s ease;
            }

            .emoji-btn:hover {
                background: var(--background-color);
            }

            .chat-actions {
                display: flex;
                gap: 10px;
            }

            .action-button {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                background: var(--background-color);
                color: var(--text-color);
                border: none;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s ease;
            }

            .action-button:hover {
                background: rgba(102, 126, 234, 0.1);
                color: var(--primary-color);
            }

            .chat-widget.js-disabled {
                display: none;
            }

            html.js .chat-widget.js-disabled {
                display: none;
            }

            html.no-js .chat-widget:not(.js-disabled) {
                display: none;
            }

            .offline-message {
                background: #fff3cd;
                color: #856404;
                padding: 12px 16px;
                border-radius: 15px;
                font-size: 0.9rem;
                line-height: 1.4;
                margin-top: 10px;
                border: 1px solid #ffeeba;
            }
        `;

        const styleElement = document.createElement('style');
        styleElement.textContent = styles;
        document.head.appendChild(styleElement);
    };

    // Create and inject the chatbot HTML
    const injectChatbot = () => {
        // Create floating chat button
        const floatingButton = document.createElement('div');
        floatingButton.className = 'floating-chat-button';
        floatingButton.innerHTML = `
            <i class="fas fa-comments"></i>
            <div class="notification-badge">1</div>
        `;
        floatingButton.onclick = toggleChat;
        document.body.appendChild(floatingButton);

        // Create chat widget
        const chatWidget = document.createElement('div');
        chatWidget.className = 'chat-widget';
        chatWidget.innerHTML = `
            <div class="chat-container">
                <div class="chat-header">
                    <h3>Travel Tales Assistant</h3>
                    <div class="chat-close" onclick="window.toggleChat()">
                        <i class="fas fa-times"></i>
                    </div>
                </div>
                <div class="chat-messages" id="chatMessages">
                    <div class="message bot-message">
                        Hello! 👋 I'm your Travel Tales assistant. How can I help you plan your perfect trip today?
                    </div>
                    <div class="suggestions">
                        <div class="suggestion-chip" onclick="window.useQuickReply('Show me popular destinations')">Popular destinations</div>
                        <div class="suggestion-chip" onclick="window.useQuickReply('How to book a trip?')">Book a trip</div>
                        <div class="suggestion-chip" onclick="window.useQuickReply('Travel tips')">Travel tips</div>
                    </div>
                </div>
                <div class="chat-input">
                    <div class="emoji-picker" id="emojiPicker">
                        <div class="emoji-btn" onclick="window.addEmoji('👋')">👋</div>
                        <div class="emoji-btn" onclick="window.addEmoji('😊')">😊</div>
                        <div class="emoji-btn" onclick="window.addEmoji('🌟')">🌟</div>
                        <div class="emoji-btn" onclick="window.addEmoji('✈️')">✈️</div>
                        <div class="emoji-btn" onclick="window.addEmoji('🌴')">🌴</div>
                        <div class="emoji-btn" onclick="window.addEmoji('🏖️')">🏖️</div>
                        <div class="emoji-btn" onclick="window.addEmoji('🗺️')">🗺️</div>
                        <div class="emoji-btn" onclick="window.addEmoji('🎒')">🎒</div>
                        <div class="emoji-btn" onclick="window.addEmoji('🌅')">🌅</div>
                        <div class="emoji-btn" onclick="window.addEmoji('🍴')">🍴</div>
                        <div class="emoji-btn" onclick="window.addEmoji('📸')">📸</div>
                        <div class="emoji-btn" onclick="window.addEmoji('❤️')">❤️</div>
                    </div>
                    <div class="chat-actions">
                        <button class="action-button" onclick="window.toggleEmojiPicker()" aria-label="Open emoji picker">
                            <i class="fas fa-smile"></i>
                        </button>
                    </div>
                    <input type="text" id="userInput" placeholder="Type your message..." onkeypress="window.handleKeyPress(event)" aria-label="Chat message input">
                    <button class="send-button" onclick="window.sendMessage()" aria-label="Send message">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
            <div class="chat-widget js-disabled">
                <div class="offline-message">
                    Please enable JavaScript to use the chat feature.
                </div>
            </div>
        `;
        document.body.appendChild(chatWidget);
    };

    // Check if this is the first visit
    const isFirstVisit = () => {
        return !localStorage.getItem('chatVisited');
    };

    // Mark as visited
    const markAsVisited = () => {
        localStorage.setItem('chatVisited', 'true');
    };

    // Toggle chat visibility
    const toggleChat = () => {
        const container = document.querySelector('.chat-container');
        container.classList.toggle('active');
        if (container.classList.contains('active')) {
            document.querySelector('#userInput').focus();
            // Hide notification badge when chat is opened
            document.querySelector('.notification-badge').style.display = 'none';
            markAsVisited();
        }
    };

    // Add message to chat
    const addMessage = (message, isUser = false, suggestions = []) => {
        const messagesContainer = document.getElementById('chatMessages');
        const messageWrapper = document.createElement('div');

        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'}`;
        messageDiv.innerHTML = message;
        messageWrapper.appendChild(messageDiv);

        if (!isUser && suggestions && suggestions.length > 0) {
            const suggestionsDiv = document.createElement('div');
            suggestionsDiv.className = 'suggestions';
            suggestions.forEach(suggestion => {
                const chip = document.createElement('div');
                chip.className = 'suggestion-chip';
                chip.textContent = suggestion;
                chip.onclick = () => {
                    document.getElementById('userInput').value = suggestion;
                    sendMessage();
                };
                suggestionsDiv.appendChild(chip);
            });
            messageWrapper.appendChild(suggestionsDiv);
        }

        messagesContainer.appendChild(messageWrapper);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    };

    // Show typing indicator
    const showTypingIndicator = () => {
        const messagesContainer = document.getElementById('chatMessages');
        const indicator = document.createElement('div');
        indicator.className = 'typing-indicator';
        indicator.innerHTML = `
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
        `;
        messagesContainer.appendChild(indicator);
        setTimeout(() => indicator.classList.add('active'), 100);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        return indicator;
    };

    // Hide typing indicator
    const hideTypingIndicator = (indicator) => {
        indicator.classList.remove('active');
        setTimeout(() => indicator.remove(), 300);
    };

    // Toggle emoji picker
    const toggleEmojiPicker = () => {
        const picker = document.getElementById('emojiPicker');
        picker.classList.toggle('active');
    };

    // Add emoji to input
    const addEmoji = (emoji) => {
        const input = document.getElementById('userInput');
        input.value += emoji;
        input.focus();
        toggleEmojiPicker();
    };

    // Send message to chatbot
    const sendMessage = async () => {
        const input = document.getElementById('userInput');
        const message = input.value.trim();

        if (message) {
            addMessage(message, true);
            input.value = '';

            const indicator = showTypingIndicator();

            try {
                // First check if we're online
                if (!navigator.onLine) {
                    throw new Error('You appear to be offline. Please check your internet connection.');
                }

                const response = await fetch('api/chatbot.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        message,
                        timestamp: new Date().toISOString(),
                        sessionId: localStorage.getItem('chatSessionId') || window.createSessionId()
                    })
                });

                if (!response.ok) {
                    throw new Error(`Server error: ${response.status}`);
                }

                const data = await response.json();

                setTimeout(() => {
                    hideTypingIndicator(indicator);
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    addMessage(data.response, false, data.suggestions || []);
                }, 1000 + Math.random() * 1000);
            } catch (error) {
                hideTypingIndicator(indicator);
                const errorMessage = document.createElement('div');
                errorMessage.className = 'error-message';
                errorMessage.innerHTML = `
                    ${error.message || 'Sorry, I encountered an error. Please try again.'}
                    <button class="retry-button" onclick="window.retryLastMessage()">Retry</button>
                `;
                document.getElementById('chatMessages').appendChild(errorMessage);
                console.error('Chatbot error:', error);
            }
        }
    };

    // Retry last message
    const retryLastMessage = () => {
        const lastUserMessage = document.querySelector('.user-message:last-of-type');
        if (lastUserMessage) {
            document.getElementById('userInput').value = lastUserMessage.textContent;
            sendMessage();
        }
    };

    // Handle key press in input
    const handleKeyPress = (event) => {
        if (event.key === 'Enter') {
            sendMessage();
        }
    };

    // Initialize chatbot
    const initChatbot = () => {
        // Remove no-js class when JavaScript is enabled
        document.documentElement.classList.remove('no-js');

        // Check if Font Awesome is loaded, if not load it
        if (!document.querySelector('link[href*="font-awesome"]')) {
            const fontAwesome = document.createElement('link');
            fontAwesome.rel = 'stylesheet';
            fontAwesome.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
            document.head.appendChild(fontAwesome);
        }

        // Check if Poppins font is loaded, if not load it
        if (!document.querySelector('link[href*="Poppins"]')) {
            const poppins = document.createElement('link');
            poppins.rel = 'stylesheet';
            poppins.href = 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap';
            document.head.appendChild(poppins);
        }

        // Inject styles and chatbot
        injectStyles();
        injectChatbot();

        // Show notification badge only on first visit
        const badge = document.querySelector('.notification-badge');
        if (!isFirstVisit()) {
            badge.style.display = 'none';
        }

        // Make functions available globally
        window.toggleChat = toggleChat;
        window.addMessage = addMessage;
        window.showTypingIndicator = showTypingIndicator;
        window.hideTypingIndicator = hideTypingIndicator;
        window.toggleEmojiPicker = toggleEmojiPicker;
        window.addEmoji = addEmoji;
        window.sendMessage = sendMessage;
        window.handleKeyPress = handleKeyPress;
        window.retryLastMessage = retryLastMessage;
        window.createSessionId = () => {
            const id = 'session_' + Math.random().toString(36).substr(2, 9);
            localStorage.setItem('chatSessionId', id);
            return id;
        };
    };

    // Initialize when DOM is loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initChatbot);
    } else {
        initChatbot();
    }
})(); 