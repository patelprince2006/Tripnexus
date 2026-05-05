// TripNexus Chatbot - Enhanced Version
class TripNexusChatbot {
    constructor() {
        this.isOpen = false;
        this.conversationHistory = [];
        this.init();
    }

    init() {
        this.createChatbotUI();
        this.bindEvents();
        this.addWelcomeMessage();
    }

    createChatbotUI() {
        const chatbotHTML = `
            <div class="chatbot-container" id="tripnexus-chatbot">
                <button class="chatbot-toggle-btn" id="chatbot-toggle">
                    <i class="bi bi-chat-dots-fill"></i>
                </button>
                <div class="chatbot-window" id="chatbot-window">
                    <div class="chatbot-header">
                        <div class="chatbot-header-info">
                            <div class="chatbot-avatar">
                                <i class="bi bi-globe2"></i>
                            </div>
                            <div class="chatbot-header-text">
                                <h3>TripNexus Assistant</h3>
                                <div class="chatbot-status">
                                    <span class="status-dot"></span>
                                    <span>Online</span>
                                </div>
                            </div>
                        </div>
                        <button class="chatbot-close-btn" id="chatbot-close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="chatbot-messages" id="chatbot-messages">
                    </div>
                    <div class="chatbot-input-container">
                        <div class="chatbot-input-wrapper">
                            <input type="text" class="chatbot-input" id="chatbot-input" placeholder="Ask about flights, hotels, tours...">
                            <button class="chatbot-send-btn" id="chatbot-send">
                                <i class="bi bi-send"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', chatbotHTML);
    }

    bindEvents() {
        const toggleBtn = document.getElementById('chatbot-toggle');
        const closeBtn = document.getElementById('chatbot-close');
        const sendBtn = document.getElementById('chatbot-send');
        const input = document.getElementById('chatbot-input');

        toggleBtn.addEventListener('click', () => this.toggleChatbot());
        closeBtn.addEventListener('click', () => this.toggleChatbot());
        sendBtn.addEventListener('click', () => this.sendMessage());
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendMessage();
            }
        });
    }

    toggleChatbot() {
        this.isOpen = !this.isOpen;
        const window = document.getElementById('chatbot-window');
        const toggleBtn = document.getElementById('chatbot-toggle');
        
        if (this.isOpen) {
            window.classList.add('active');
            toggleBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
        } else {
            window.classList.remove('active');
            toggleBtn.innerHTML = '<i class="bi bi-chat-dots-fill"></i>';
        }
    }

    addWelcomeMessage() {
        const welcomeHTML = `
            <div class="message bot">
                <div class="message-content">
                    👋 Hello! Welcome to TripNexus! I'm your personal travel assistant. 

I can help you with:
• ✈️ Flight bookings
• 🏨 Hotel reservations  
• 🚌 Bus tickets
• 🚂 Train bookings
• 🗺️ Tour packages

How can I assist you today?
                </div>
                <div class="quick-replies">
                    <button class="quick-reply-btn" data-action="flights">✈️ Book Flight</button>
                    <button class="quick-reply-btn" data-action="hotels">🏨 Find Hotel</button>
                    <button class="quick-reply-btn" data-action="tours">🗺️ Explore Tours</button>
                    <button class="quick-reply-btn" data-action="help">❓ More Help</button>
                </div>
            </div>
        `;
        
        const messagesContainer = document.getElementById('chatbot-messages');
        messagesContainer.innerHTML = welcomeHTML;
        
        this.bindQuickReplies();
    }

    bindQuickReplies() {
        const quickReplies = document.querySelectorAll('.quick-reply-btn');
        quickReplies.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const action = e.target.dataset.action;
                const text = e.target.textContent;
                this.handleUserMessage(text, action);
            });
        });
    }

    sendMessage() {
        const input = document.getElementById('chatbot-input');
        const message = input.value.trim();
        
        if (message) {
            this.handleUserMessage(message);
            input.value = '';
        }
    }

    handleUserMessage(text, action = null) {
        this.addUserMessage(text);
        this.showTypingIndicator();
        
        setTimeout(() => {
            this.hideTypingIndicator();
            const response = this.getBotResponse(text, action);
            this.addBotMessage(response.message, response.quickReplies);
        }, 600 + Math.random() * 800);
    }

    addUserMessage(text) {
        const messageHTML = `
            <div class="message user">
                <div class="message-content">${this.escapeHtml(text)}</div>
            </div>
        `;
        
        const messagesContainer = document.getElementById('chatbot-messages');
        messagesContainer.insertAdjacentHTML('beforeend', messageHTML);
        this.scrollToBottom();
    }

    addBotMessage(text, quickReplies = null) {
        let quickRepliesHTML = '';
        if (quickReplies) {
            quickRepliesHTML = '<div class="quick-replies">';
            quickReplies.forEach(reply => {
                quickRepliesHTML += `<button class="quick-reply-btn" data-action="${reply.action}">${reply.text}</button>`;
            });
            quickRepliesHTML += '</div>';
        }

        const messageHTML = `
            <div class="message bot">
                <div class="message-content">${text}</div>
                ${quickRepliesHTML}
            </div>
        `;
        
        const messagesContainer = document.getElementById('chatbot-messages');
        messagesContainer.insertAdjacentHTML('beforeend', messageHTML);
        this.scrollToBottom();
        this.bindQuickReplies();
    }

    showTypingIndicator() {
        const typingHTML = `
            <div class="message bot" id="typing-indicator">
                <div class="typing-indicator">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>
        `;
        
        const messagesContainer = document.getElementById('chatbot-messages');
        messagesContainer.insertAdjacentHTML('beforeend', typingHTML);
        this.scrollToBottom();
    }

    hideTypingIndicator() {
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }

    getBotResponse(text, action) {
        const lowerText = text.toLowerCase().trim();
        
        const responses = {
            flights: {
                message: '✈️ **Flight Booking Help**\n\nGreat! To book a flight:\n\n1. Go to our homepage 🏠\n2. Click on the "Flight" tab\n3. Enter your departure and arrival cities\n4. Select your travel dates\n5. Choose your preferred flight\n6. Complete the booking!\n\nWe offer flights to domestic and international destinations. Would you like to search for flights now?',
                quickReplies: [
                    { text: '🏨 Hotels', action: 'hotels' },
                    { text: '🗺️ Tours', action: 'tours' },
                    { text: '🏠 Home', action: 'home' }
                ]
            },
            hotels: {
                message: '🏨 **Hotel Booking Help**\n\nPerfect! Find your ideal stay:\n\n1. Visit our homepage 🏠\n2. Select the "Hotel" tab\n3. Enter your destination city\n4. Choose check-in and check-out dates\n5. Specify number of guests\n6. Browse and book your hotel!\n\nWe have options from budget to luxury stays. Ready to find a hotel?',
                quickReplies: [
                    { text: '✈️ Flights', action: 'flights' },
                    { text: '🗺️ Tours', action: 'tours' },
                    { text: '🏠 Home', action: 'home' }
                ]
            },
            buses: {
                message: '🚌 **Bus Ticket Booking**\n\nTravel by bus easily:\n\n1. Go to homepage 🏠\n2. Click on "Bus" tab\n3. Enter source and destination\n4. Select travel date\n5. Choose your bus\n6. Book your seats!\n\nWe have bus services across major cities. Need help with anything else?',
                quickReplies: [
                    { text: '✈️ Flights', action: 'flights' },
                    { text: '🏨 Hotels', action: 'hotels' },
                    { text: '🏠 Home', action: 'home' }
                ]
            },
            trains: {
                message: '🚂 **Train Ticket Booking**\n\nBook train tickets conveniently:\n\n1. Visit homepage 🏠\n2. Select "Train" tab\n3. Enter train number or stations\n4. Choose journey date\n5. Check availability\n6. Confirm your booking!\n\nWe cover major train routes. What else would you like to know?',
                quickReplies: [
                    { text: '✈️ Flights', action: 'flights' },
                    { text: '🏨 Hotels', action: 'hotels' },
                    { text: '🏠 Home', action: 'home' }
                ]
            },
            tours: {
                message: '🗺️ **Tour Packages**\n\nExplore amazing destinations!\n\n1. Go to homepage 🏠\n2. Click on "Tour" tab\n3. Browse our exclusive tour packages\n4. Select your dream destination\n5. Check travel dates and inclusions\n6. Book your adventure!\n\nWe have group tours, private tours, and adventure packages. Ready to explore?',
                quickReplies: [
                    { text: '✈️ Flights', action: 'flights' },
                    { text: '🏨 Hotels', action: 'hotels' },
                    { text: '🏠 Home', action: 'home' }
                ]
            },
            help: {
                message: '❓ **How Can I Help?**\n\nI\'m here to assist you with all your travel needs!\n\n**Services Available:**\n✈️ **Flights** - Domestic & international\n🏨 **Hotels** - Budget to luxury\n🚌 **Buses** - Intercity travel\n🚂 **Trains** - Rail bookings\n🗺️ **Tours** - Holiday packages\n\n**Quick Actions:**\n• Search and book\n• View bookings\n• Manage wishlist\n• Contact support\n\nWhat would you like help with?',
                quickReplies: [
                    { text: '✈️ Flights', action: 'flights' },
                    { text: '🏨 Hotels', action: 'hotels' },
                    { text: '🗺️ Tours', action: 'tours' },
                    { text: '🏠 Home', action: 'home' }
                ]
            },
            home: {
                message: '🏠 **Main Menu**\n\nWelcome back! I\'m your TripNexus travel assistant. \n\nWhat would you like to do today?\n• Book a flight ✈️\n• Find a hotel 🏨\n• Explore tours 🗺️\n• Get help ❓\n\nJust click a button below or type your question!',
                quickReplies: [
                    { text: '✈️ Book Flight', action: 'flights' },
                    { text: '🏨 Find Hotel', action: 'hotels' },
                    { text: '🗺️ Tours', action: 'tours' },
                    { text: '❓ Help', action: 'help' }
                ]
            },
            greeting: {
                message: '👋 **Hello!**\n\nGreat to meet you! I\'m your TripNexus travel assistant, here to make your travel planning easy and fun!\n\nWhether you need flights, hotels, buses, trains, or tours - I\'ve got you covered. How can I help you today?',
                quickReplies: [
                    { text: '✈️ Flights', action: 'flights' },
                    { text: '🏨 Hotels', action: 'hotels' },
                    { text: '🗺️ Tours', action: 'tours' }
                ]
            },
            thanks: {
                message: '😊 **You\'re Welcome!**\n\nHappy to help! If you need anything else - flights, hotels, tours, or just have questions - I\'m always here!\n\nIs there anything else I can assist you with for your trip?',
                quickReplies: [
                    { text: '✈️ Flights', action: 'flights' },
                    { text: '🏨 Hotels', action: 'hotels' },
                    { text: '🏠 Home', action: 'home' }
                ]
            },
            price: {
                message: '💰 **Pricing Information**\n\nGreat question! We offer:\n\n• **Best Price Guarantee** - Competitive rates\n• **Special Deals** - Exclusive discounts\n• **Transparent Pricing** - No hidden fees\n• **Flexible Options** - Choose what fits your budget\n\nYou can compare prices on our search pages. What would you like to book?',
                quickReplies: [
                    { text: '✈️ Flights', action: 'flights' },
                    { text: '🏨 Hotels', action: 'hotels' },
                    { text: '🗺️ Tours', action: 'tours' }
                ]
            },
            booking: {
                message: '📝 **Booking Guide**\n\nReady to book? Here\'s how:\n\n1. **Choose Service** - Flight/Hotel/Bus/Train/Tour\n2. **Enter Details** - Dates, destinations, passengers\n3. **Browse Options** - Compare prices and reviews\n4. **Select & Book** - Choose your preferred option\n5. **Make Payment** - Secure payment process\n6. **Get Confirmation** - Instant booking confirmation\n\nWhat would you like to book first?',
                quickReplies: [
                    { text: '✈️ Flights', action: 'flights' },
                    { text: '🏨 Hotels', action: 'hotels' },
                    { text: '🗺️ Tours', action: 'tours' }
                ]
            },
            contact: {
                message: '📞 **Contact Information**\n\nNeed to reach us?\n\n**Hotline:** +91 98765 43210\n**Email:** tripnexus.buiseness@gmail.com\n\nOur team is available 24/7 to help you!\n\nIs there something specific I can help you with right now?',
                quickReplies: [
                    { text: '✈️ Flights', action: 'flights' },
                    { text: '🏨 Hotels', action: 'hotels' },
                    { text: '🏠 Home', action: 'home' }
                ]
            },
            destination: {
                message: '🌍 **Popular Destinations**\n\nWe cover amazing destinations!\n\n**India:** Delhi, Mumbai, Goa, Manali, Agra, Kerala, Rajasthan\n\n**International:** Dubai, Singapore, Bangkok, London, New York, Paris\n\nWhere would you like to travel? Tell me your dream destination!',
                quickReplies: [
                    { text: '✈️ Flights', action: 'flights' },
                    { text: '🏨 Hotels', action: 'hotels' },
                    { text: '🗺️ Tours', action: 'tours' }
                ]
            },
            refund: {
                message: '🔄 **Refund & Cancellation**\n\nNeed to cancel? Here\'s our policy:\n\n• **Flexible Options** - Many bookings can be cancelled\n• **Refund Timeline** - Processed within 5-7 working days\n• **Cancellation Fees** - Depends on booking terms\n• **Instant Support** - Contact us for help\n\nPlease check your booking confirmation for specific terms. Need help with anything else?',
                quickReplies: [
                    { text: '✈️ Flights', action: 'flights' },
                    { text: '🏨 Hotels', action: 'hotels' },
                    { text: '🏠 Home', action: 'home' }
                ]
            },
            payment: {
                message: '💳 **Payment Options**\n\nWe accept multiple payment methods:\n\n• **Credit/Debit Cards** - Visa, Mastercard, Amex\n• **UPI** - Google Pay, PhonePe, Paytm\n• **Net Banking** - All major banks\n• **Wallets** - Popular e-wallets\n\nAll transactions are 100% secure and encrypted! Ready to make a booking?',
                quickReplies: [
                    { text: '✈️ Flights', action: 'flights' },
                    { text: '🏨 Hotels', action: 'hotels' },
                    { text: '🗺️ Tours', action: 'tours' }
                ]
            },
            goodbye: {
                message: '👋 **Goodbye!**\n\nThank you for chatting with TripNexus! Have a wonderful journey and safe travels!\n\nWe hope to see you again soon for all your travel needs. ✈️🏨🗺️',
                quickReplies: [
                    { text: '🏠 Home', action: 'home' },
                    { text: '❓ Help', action: 'help' }
                ]
            },
            default: {
                message: '🤔 **Got It!**\n\nI understand you\'re looking for travel information! Let me help you explore our services.\n\nYou can ask me about:\n• ✈️ Flights - Book air tickets\n• 🏨 Hotels - Find accommodation\n• 🚌 Buses - Travel by road\n• 🚂 Trains - Rail bookings\n• 🗺️ Tours - Holiday packages\n\nWhat interests you most?',
                quickReplies: [
                    { text: '✈️ Flights', action: 'flights' },
                    { text: '🏨 Hotels', action: 'hotels' },
                    { text: '🗺️ Tours', action: 'tours' },
                    { text: '❓ Help', action: 'help' }
                ]
            }
        };

        if (action) {
            return responses[action] || responses.default;
        }

        if (this.containsAny(lowerText, ['flight', 'flights', 'airplane', 'air', 'plane', 'fly'])) {
            return responses.flights;
        }

        if (this.containsAny(lowerText, ['hotel', 'hotels', 'stay', 'accommodation', 'room', 'rooms', 'resort', 'resorts'])) {
            return responses.hotels;
        }

        if (this.containsAny(lowerText, ['bus', 'buses'])) {
            return responses.buses;
        }

        if (this.containsAny(lowerText, ['train', 'trains', 'railway', 'rail'])) {
            return responses.trains;
        }

        if (this.containsAny(lowerText, ['tour', 'tours', 'trip', 'trips', 'travel', 'package', 'packages', 'holiday', 'vacation'])) {
            return responses.tours;
        }

        if (this.containsAny(lowerText, ['help', 'support', 'assist', 'assistance', 'guide', 'how', 'what'])) {
            return responses.help;
        }

        if (this.containsAny(lowerText, ['home', 'main', 'start', 'menu', 'begin'])) {
            return responses.home;
        }

        if (this.containsAny(lowerText, ['hi', 'hello', 'hey', 'greetings', 'good morning', 'good afternoon', 'good evening'])) {
            return responses.greeting;
        }

        if (this.containsAny(lowerText, ['thank', 'thanks', 'thx', 'thank you', 'appreciate'])) {
            return responses.thanks;
        }

        if (this.containsAny(lowerText, ['price', 'prices', 'cost', 'costs', 'rate', 'rates', 'cheap', 'affordable', 'budget', 'discount', 'deal'])) {
            return responses.price;
        }

        if (this.containsAny(lowerText, ['book', 'booking', 'reserve', 'reservation', 'confirm'])) {
            return responses.booking;
        }

        if (this.containsAny(lowerText, ['contact', 'call', 'phone', 'email', 'reach', 'support'])) {
            return responses.contact;
        }

        if (this.containsAny(lowerText, ['destination', 'destinations', 'place', 'places', 'where', 'location', 'locations', 'city', 'cities', 'country', 'countries'])) {
            return responses.destination;
        }

        if (this.containsAny(lowerText, ['refund', 'refunds', 'cancel', 'cancellation', 'return', 'returns'])) {
            return responses.refund;
        }

        if (this.containsAny(lowerText, ['pay', 'payment', 'payments', 'card', 'cards', 'upi', 'netbanking', 'wallet'])) {
            return responses.payment;
        }

        if (this.containsAny(lowerText, ['bye', 'goodbye', 'see you', 'farewell', 'exit', 'quit'])) {
            return responses.goodbye;
        }

        return responses.default;
    }

    containsAny(text, keywords) {
        return keywords.some(keyword => text.includes(keyword));
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    scrollToBottom() {
        const messagesContainer = document.getElementById('chatbot-messages');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new TripNexusChatbot();
});
