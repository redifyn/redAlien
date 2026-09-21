# 👽 redAlien

**redAlien** is a real-time collaboration and communication platform built with PHP, MySQL, JavaScript, WebRTC, and Bootstrap 5.

The project demonstrates a custom PHP MVC application with team-based communication, real-time messaging features, browser-based video rooms, screen sharing, file attachments, voice notes, user invitations, and authentication.

## 🚀 Features

- User registration and authentication
- Password recovery and reset
- Team/workspace collaboration
- Team member invitations
- Channels and messaging
- Message reactions and actions
- Typing and presence indicators
- File attachments
- Voice notes
- Video meeting rooms
- Browser-based WebRTC communication
- Screen sharing
- Meeting participant management
- Responsive Bootstrap 5 interface
- Custom PHP MVC architecture
- PDO database access
- CSRF protection

## 🛠️ Technology Stack

### Backend
- PHP 8+
- Object-Oriented PHP
- PDO
- MySQL
- Custom MVC architecture
- Composer
- PHPMailer

### Frontend
- HTML5
- CSS3
- Bootstrap 5
- JavaScript
- WebRTC
- Browser Media APIs

### Development
- Apache
- XAMPP
- Git
- GitHub

## 📁 Project Structure

```text
redAlien/
├── app/
│   ├── controllers/
│   ├── core/
│   ├── helpers/
│   ├── models/
│   ├── services/
│   └── views/
│
├── config/
├── database/
│   └── scripts/
├── public/
│   ├── assets/
│   │   ├── css/
│   │   ├── images/
│   │   └── js/
│   └── index.php
│
├── composer.json
├── composer.lock
└── README.md
```

## 🎥 Real-Time Communication

redAlien includes browser-based communication features built around WebRTC and JavaScript.

The project contains dedicated modules for:

- Video rooms
- WebRTC connections
- Screen sharing
- Meeting management
- Video signaling
- Meeting participants

The browser Media APIs are used to work with available camera, microphone, and screen-sharing capabilities.

## 💬 Collaboration

The collaboration system includes:

- Teams
- Channels
- Messaging
- Presence indicators
- Typing indicators
- Message reactions
- File attachments
- Voice notes
- Clipboard utilities
- Team invitations

## 🔐 Security

The application includes several security-focused components such as:

- PDO database access
- Authentication helpers
- CSRF protection
- Password reset functionality
- Configuration files excluded from version control
- Sensitive credentials kept outside the public Git repository

## ⚙️ Local Installation

### 1. Clone the repository

```bash
git clone https://github.com/redifyn/redAlien.git
```

### 2. Enter the project directory

```bash
cd redAlien
```

### 3. Install PHP dependencies

```bash
composer install
```

### 4. Configure the application

Create your local application configuration inside:

```text
config/config.php
```

Add your own local database and application credentials.

> `config/config.php` is intentionally excluded from Git so that private credentials are not published.

### 5. Configure the database

Create a MySQL database for the project and configure the connection details in your local configuration.

Development and database utility scripts are available inside:

```text
database/scripts/
```

### 6. Run locally

When using XAMPP, place the project inside:

```text
C:\xampp\htdocs\redAlien
```

Then access the application through your configured local Apache URL.

For browser features such as camera, microphone, WebRTC, and screen sharing, use a secure HTTPS development environment where required by the browser.

## 📌 Project Status

redAlien is an actively developed portfolio project focused on demonstrating PHP backend development together with modern browser-based real-time communication and collaboration features.

Additional improvements and production deployment configuration are planned.

## 👨‍💻 Developer

**Anselm Ifeanyi Dike**

PHP / Web Developer based in Vilnius, Lithuania.

GitHub: https://github.com/redifyn  
LinkedIn: https://www.linkedin.com/in/anselm-ify-dike-185730114/

---

Built as a portfolio project to demonstrate custom PHP MVC development, MySQL/PDO integration, JavaScript, WebRTC, and real-time collaboration concepts.