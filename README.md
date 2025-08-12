# CineVault

CineVault is a dynamic movie review website inspired by IMDb. It allows users to search for movies, view detailed information, rate films, and leave comments. The site pulls movie data in real-time from the TMDb (The Movie Database) API, providing an up-to-date and interactive movie browsing experience.

## Features

- Browse and search movies/TV shows using the TMDb API.
- User authentication system (register, login).
- Movie rating system with user-specific ratings and favorite system.
- Comment and review section for movies.
- Responsive and user-friendly interface built with Bootstrap.
- MySQL database integration for user data, ratings, and comments.
- Backend powered by PHP.

## Technologies Used

- **Frontend:** HTML, CSS, JavaScript, Bootstrap
- **Backend:** PHP
- **Database:** MySQL
- **API:** TMDb API for movie data

## Setup Instructions

1. **Clone the repository:**

   ```bash
   git clone https://github.com/AhmadHakim119/CineVault.git
   ```
   
2. Install XAMPP (or WAMP):
    - Download XAMPP from https://www.apachefriends.org/index.html.
    - Install and launch XAMPP.
    - Start the Apache and MySQL modules from the control panel.

3. Set up the database:
    - Open phpMyAdmin by visiting http://localhost/phpmyadmin/ in your browser.
    - Create a new database (e.g., cinevault).
    - Import the cinevault.sql file from the project folder to create the necessary tables.

4. Configure the project:
    - Open the config.php file in the project folder.
    - Update it with your MySQL database credentials and your TMDb API key.

5. Place the project in the web root:
    - Copy the entire CineVault folder to C:\xampp\htdocs\ (or the equivalent web root for your server).

6. Run the project:
    - Open your browser and go to http://localhost/CineVault to start using the website.
  
## License

This project is licensed under the MIT License.
