# BlogPost

A simple PHP & MySQL blog application with user authentication, categories, posts, comments, and image uploads.

## Features

- User registration and login
- Create, edit, and delete blog posts with images
- Categorize posts
- Add and delete categories
- Comment on posts
- Responsive design

## Setup

1. **Clone or copy the project files to your server directory.**

2. **Import the database:**
   - Use the provided `database.sql` file to create the necessary tables.
   - Example using phpMyAdmin or MySQL CLI:
     ```
     mysql -u root -p < database.sql
     ```

3. **Configure database connection:**
   - Edit `includes/config.php` if your MySQL username/password is different.

4. **Set permissions:**
   - Make sure the `uploads` directory is writable for image uploads.

5. **Run the project:**
   - Access `index.php` in your browser via your local server (e.g., XAMPP, WAMP, or LAMP).
   - **Typical local URL:**  
     ```
     http://localhost/blogpost/
     ```
   - For login/register:  
     ```
     http://localhost/blogpost/login.php
     http://localhost/blogpost/register.php
     ```

## Usage

- Register a new user or log in.
- Create posts, upload images, and assign categories.
- Add comments to posts.
- Manage categories and posts.

## Author

Developed by Maksudul Hasan Limon
