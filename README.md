# 🌐 Personal Profile Website

A modern, responsive personal portfolio & profile website built with **Laravel** and vanilla CSS. Features a dynamic admin dashboard for managing all content without touching code.

## ✨ Features

### 🖥️ Public Website
- **Hero Section** — Animated typing effect, gradient avatar frame, and CTA buttons
- **About Me** — Quote-style description card with contact information
- **Education Timeline** — Interactive alternating timeline with color-coded degree badges
- **Hobbies & Interests** — Dynamic hobby cards with icons
- **Photo Gallery** — Filterable image gallery with category tabs
- **Contact Form** — Working contact form with email notifications
- **Dark/Light Theme** — Toggle between themes with smooth transitions
- **Fully Responsive** — Optimized for all screen sizes

### 🔒 Admin Dashboard
- **Secure Login** — Authentication with 2FA (OTP via email)
- **Profile Management** — Edit name, bio, tagline, avatar, and contact info
- **Education CRUD** — Add, edit, delete, and restore education entries
- **Hobbies CRUD** — Manage hobbies with icon picker
- **Gallery CRUD** — Upload and categorize photos
- **Social Media CRUD** — Manage social media links
- **Contact Messages** — Inbox with read/unread, reply, and delete
- **Analytics** — Visitor tracking with charts and statistics
- **Session Security** — Fingerprint validation, idle timeout, rate limiting

## 🛠️ Tech Stack

| Layer       | Technology             |
|-------------|------------------------|
| Backend     | Laravel 13 (PHP 8.2+)  |
| Frontend    | Vanilla CSS, JavaScript |
| Icons       | Boxicons               |
| Fonts       | Poppins (Google Fonts)  |
| Admin UI    | Sneat Bootstrap Template|
| Database    | MySQL / SQLite          |
| Mail        | SMTP (Gmail / Mailtrap) |

## 🚀 Getting Started

### Prerequisites
- PHP 8.2 or higher
- Composer
- MySQL or SQLite
- Node.js & npm (for asset compilation, optional)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/YOUR_USERNAME/profile.git
   cd profile
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Setup environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**
   
   Edit `.env` and set your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_DATABASE=profile
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Run migrations & seeders**
   ```bash
   php artisan migrate --seed
   ```

6. **Create storage symlink**
   ```bash
   php artisan storage:link
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

8. **Visit the website**
   - Public site: `http://localhost:8000`
   - Admin login: `http://localhost:8000/222/auth/login`

## 📁 Project Structure

```
├── app/
│   ├── Http/Controllers/     # All controllers (Home, Auth, Admin CRUD)
│   ├── Models/               # Eloquent models
│   └── Mail/                 # Mailable classes
├── database/
│   ├── migrations/           # Database schema
│   └── seeders/              # Default data seeders
├── public/
│   ├── css/style.css         # Main stylesheet
│   ├── js/main.js            # Frontend JavaScript
│   └── images/               # Static assets
├── resources/views/
│   ├── components/           # Blade components (hero, about, gallery, etc.)
│   ├── admin/                # Admin dashboard views
│   └── welcome.blade.php     # Main public page
└── routes/web.php            # All route definitions
```

## 📄 License

This project is open-sourced for personal use.
