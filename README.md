# 🎬 Anime Cube

**Anime Cube** is a comprehensive anime browsing and management platform that allows users to explore anime, view detailed information, manage their watchlist, and enjoy daily anime quotes.

## ✨ Features

### 🔓 Public Features (Before Login)
- Browse anime cards with images, titles, descriptions, and ratings
- View anime quotes fetched from live API (auto-refreshes every 5 minutes)
- Beautiful, responsive card-based layout
- Search and filter anime collection

### 🔐 User Features (After Login)
- **Detailed Anime Information** - View comprehensive details similar to MyAnimeList:
  - Synopsis and full description
  - Episode count, status, aired dates
  - Genres, themes, studios, producers
  - User ratings and statistics
  - Embedded trailers
- **Favorites Management** - Add/remove anime to/from your favorites
- **Watchlist Management** - Track your anime watching progress:
  - Set status (Watching, Completed, On Hold, Dropped, Plan to Watch)
  - Track episodes watched
  - Rate anime (1-10 score)
- **Personalized Dashboard** - See your custom anime collection

### 🎨 Design Features
- Modern, clean UI inspired by MyAnimeList
- Responsive design (mobile, tablet, desktop)
- Left sidebar with animated quote display
- Right main content area with anime cards
- Gradient backgrounds and smooth transitions
- Card hover effects

## 🛠️ Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL / MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **CSS Framework**: Bootstrap 5
- **API**: AnimeChan API (for anime quotes)
- **Server**: Apache (XAMPP)

## 📋 Prerequisites

- XAMPP (or any Apache + MySQL + PHP environment)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser (Chrome, Firefox, Edge, Safari)

## 🚀 Installation & Setup

### 1. Clone or Download the Project

Place the `AnimeCube` folder in your XAMPP `htdocs` directory:
```
C:\xampp\htdocs\AnimeCube
```

### 2. Start XAMPP

- Start **Apache** and **MySQL** from XAMPP Control Panel

### 3. Create Database

**Option A: Using phpMyAdmin**

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click "Import" tab
3. Choose the file: `AnimeCube/Database/schema.sql`
4. Click "Go" to execute

**Option B: Using MySQL Command Line**

```bash
mysql -u root -p < C:\xampp\htdocs\AnimeCube\Database\schema.sql
```

**Option C: Manual Setup**

1. Create database manually:
```sql
CREATE DATABASE anime_cube;
```

2. Copy and paste the SQL from `Database/schema.sql` into phpMyAdmin SQL tab

### 4. Configure Database Connection

The default configuration should work with XAMPP. If needed, edit `Database/db.php`:

```php
$host = "localhost";
$username = "root";
$password = "";        // Your MySQL password (empty by default in XAMPP)
$dbname = "anime_cube";
```

### 5. Access the Application

Open your browser and navigate to:
```
http://localhost/AnimeCube/index.php
```

Or simply:
```
http://localhost/AnimeCube/
```

## 📁 Project Structure

```
AnimeCube/
├── client/
│   ├── apiCall.php        # Fetches anime quotes from API
│   ├── Card.php           # Anime card grid component
│   ├── commonFile.php     # Common CSS/JS includes
│   ├── Content.php        # Detailed anime information page
│   ├── header.php         # Navigation header
│   ├── login.php          # Login form
│   └── signup.php         # Signup form
├── Database/
│   ├── db.php             # Database connection
│   └── schema.sql         # Database schema and sample data
├── public/
│   ├── anime1.jpg         # Sample anime images
│   ├── logo.jpg           # Site logo
│   └── style.css          # Custom styles
├── server/
│   ├── requests.php       # Handles login/signup
│   └── userActions.php    # Handles favorites/watchlist
├── index.php              # Main entry point
└── README.md              # This file
```

## 👤 User Guide

### Creating an Account

1. Click **"SignUp"** in the navigation
2. Fill in the registration form:
   - Username
   - Email
   - Password
   - Confirm Password
   - Address
3. Click **"Sign Up"**
4. You'll be redirected to login

### Logging In

1. Click **"Login"** in the navigation
2. Enter your **username** and **password**
3. Click **"Login"**
4. You'll be redirected to the homepage

### Browsing Anime

- **Before Login**: View anime cards with basic information
- **After Login**: Click "View Details" on any card to see full information

### Managing Favorites

1. Open an anime's detail page
2. Click **"Add to Favorites"** (❤️ button)
3. Click again to remove from favorites

### Managing Watchlist

1. Open an anime's detail page
2. Click **"Add to Watchlist"** (📝 button)
3. Choose a status:
   - **Watching** - Currently watching
   - **Completed** - Finished watching
   - **On Hold** - Paused
   - **Dropped** - Stopped watching
   - **Plan to Watch** - Want to watch later
4. Enter episodes watched
5. Optionally rate the anime (1-10)

### Viewing Quotes

- The left sidebar displays random anime quotes
- Quotes auto-refresh every 5 minutes
- See countdown timer at the bottom

## 🔧 Customization

### Adding More Anime

Insert anime data into the `anime` table using phpMyAdmin or SQL:

```sql
INSERT INTO `anime` 
(`title`, `title_english`, `image`, `description`, `synopsis`, `type`, `episodes`, `status`, `genres`, `score`) 
VALUES 
('Your Anime', 'English Title', './public/image.jpg', 'Short description', 'Full synopsis', 'TV', 24, 'Finished Airing', 'Action, Adventure', 8.50);
```

### Changing Styles

Edit `public/style.css` to customize colors, fonts, and layouts.

### API Configuration

The anime quotes API endpoint is in `client/apiCall.php`:
```php
curl_setopt($curl, CURLOPT_URL, "https://api.animechan.io/v1/quotes/random");
```

## 🐛 Troubleshooting

### Database Connection Error
- Verify XAMPP MySQL is running
- Check database credentials in `Database/db.php`
- Ensure database `anime_cube` exists

### Session Errors
- Clear browser cache and cookies
- Check PHP session configuration
- Ensure `session_start()` is at the top of files

### Quote API Not Working
- Check internet connection
- API might be rate-limited (wait 15 minutes)
- Fallback to cached quotes in session

### Images Not Loading
- Verify images exist in `public/` folder
- Check file paths in database
- Ensure correct permissions on `public/` folder

## 📊 Database Tables

### `users`
Stores user account information

### `anime`
Contains anime details (title, description, rating, etc.)

### `user_favorites`
Tracks which anime users have favorited

### `user_watchlist`
Tracks user's anime watching progress and ratings

## 🔐 Security Features

- Password hashing with `bcrypt`
- Prepared statements to prevent SQL injection
- Session-based authentication
- XSS protection with `htmlspecialchars()`
- CSRF protection recommended for production

## 🚀 Future Enhancements

- [ ] Advanced search and filtering
- [ ] User profile pages
- [ ] Anime recommendations based on favorites
- [ ] Social features (follow users, comments)
- [ ] Email verification
- [ ] Password reset functionality
- [ ] Admin panel for managing anime
- [ ] API for mobile apps
- [ ] Integration with MAL/AniList APIs
- [ ] Reviews and ratings system

## 📝 License

This project is open source and available for educational purposes.

## 👨‍💻 Developer Notes

- Built with PHP and MySQL
- No framework dependencies (vanilla PHP)
- Bootstrap 5 for responsive UI
- RESTful API structure for AJAX calls
- MVC-inspired file organization

## 🤝 Contributing

Feel free to fork, improve, and submit pull requests!

## 📧 Support

For issues or questions, please check:
1. This README file
2. Database schema comments
3. Code comments in PHP files

---

**Enjoy exploring the anime world with Anime Cube! 🎌**