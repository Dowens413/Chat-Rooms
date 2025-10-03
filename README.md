💬 Real-Time Multi-Room Chat Application
A fast and feature-rich web-based chat application designed for real-time communication between users, hosted on an AWS EC2 instance.

✨ Key Features
Real-Time Messaging: Instant message delivery powered by WebSockets.

Public Chat Rooms: Easily join and participate in open group conversations.

Private, Keyed Rooms: Create or join secure, private chat rooms protected by an access key/password.

Simple Interface: Intuitive user experience built with standard HTML, CSS, and JavaScript.

🛠️ Technology Stack
This application is built on a robust, lightweight stack ideal for real-time PHP environments:

Area

Technologies Used

Backend/Server

PHP (Core application and WebSocket server logic)

Real-Time Layer

WebSockets (for persistent, bi-directional communication)

Frontend

HTML, CSS, JavaScript (Vanilla for connectivity and DOM manipulation)

Database

MySQL (Used for storing messages, room details, and user data)

Deployment

AWS EC2

🚀 Installation and Setup
This application requires a PHP runtime environment and a MySQL database to function.

1. Database Configuration (MySQL)

You must recreate the necessary database tables before running the server.


2. Configure PHP and Dependencies

Ensure you have the required PHP extensions (like mysqli and those needed for WebSockets if using a specific library like Ratchet).

3. Start the WebSocket Server

The application is run by launching the dedicated PHP server file, which handles the WebSocket connections.

Execute the following command in your terminal:

php server.php

The server should output a message indicating it is running and listening on the configured host and port (e.g., ws://0.0.0.0:8080).

4. Access the Application

Once the server is running, navigate to the main HTML file in your web browser:

http://[YOUR_EC2_IP_ADDRESS]/index.html (or equivalent URL)

📝 Usage
Joining a Room: Enter a room name and click "Join."

Creating a Private Room: Check the "Private" option and enter a key/password before joining. This key will be required for all future users attempting to enter that room.

💡 Customization
All frontend styles are managed in the styles.css file and logic in the script.js file. The backend logic resides primarily in server.php and its related PHP classes.
