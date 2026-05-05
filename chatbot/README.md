# TripNexus Chatbot

A beautiful, interactive chatbot for the TripNexus travel booking website.

## Features

- **Modern Visual Design**: Gradient colors, smooth animations, and clean UI
- **Interactive Conversations**: Responds to user queries about flights, hotels, tours, and more
- **Quick Replies**: Predefined response buttons for easy navigation
- **Typing Indicator**: Shows when the bot is "thinking"
- **Responsive Design**: Works on desktop and mobile devices
- **Smooth Animations**: Pulse effects, slide-in animations, and more

## Files Included

1. `chatbot.css` - Styling and animations for the chatbot
2. `chatbot.js` - Interactive functionality and conversation logic
3. `chatbot.php` - Easy include file for PHP pages

## Integration

### Method 1: Manual Integration (already done for index.php and hotels/search_hotel.php)

Add these lines to your HTML head:

```html
<link rel="stylesheet" href="chatbot/chatbot.css">
```

Add this line before the closing </body> tag:

```html
<script src="chatbot/chatbot.js"></script>
```

### Method 2: PHP Include

For PHP pages, simply include:

```php
<?php include 'chatbot/chatbot.php'; ?>
```

## Usage

The chatbot will automatically appear as a floating button in the bottom-right corner of your website. Click the button to open/close the chat window.

### Supported Queries

The chatbot can help with:
- ✈️ **Flights**: Booking and searching for flights
- 🏨 **Hotels**: Finding accommodations
- 🚌 **Buses**: Traveling by bus
- 🚂 **Trains**: Booking train tickets
- 🗺️ **Tours**: Exploring tour packages
- ❓ **Help**: General assistance

### Quick Replies

Users can click quick reply buttons for faster interactions:
- "✈️ Flights"
- "🏨 Hotels" 
- "🗺️ Tours"
- "❓ Help"
- "🏠 Home"

## Customization

### Styling

Edit `chatbot.css` to customize:
- Colors (gradient backgrounds)
- Sizes and spacing
- Animation timings
- Border radius and shadows

### Conversation Logic

Edit `chatbot.js` to customize:
- Responses in the `getBotResponse()` method
- Quick reply options
- Welcome message
- Bot name and avatar

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Technologies Used

- **HTML5** - Structure
- **CSS3** - Styling with animations and gradients
- **JavaScript (ES6+)** - Interactive functionality
- **Bootstrap Icons** - Beautiful icons
- **Bootstrap 5** - Base styling (already included in TripNexus)
