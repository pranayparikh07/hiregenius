# HireGenius - Smart Interview Platform

A modern web-based interview management system built with PHP and MySQL. Recruiters can create interviews with custom questions, share interview codes with candidates, and review their text-based responses.

## 📋 Features

### For Recruiters
- **Account Management**: Register and login with company details
- **Interview Creation**: Create interviews with custom or default questions
- **Interview Codes**: Share unique 6-digit codes with candidates
- **Response Review**: View all candidate answers with time tracking
- **Dashboard**: Overview of interviews and statistics

### For Candidates
- **Easy Access**: Join interviews using a simple 6-digit code
- **Timed Questions**: Answer questions within configurable time limits
- **Progress Tracking**: Visual progress bar and question counter
- **Auto-save**: Answers are automatically saved

### For Administrators
- **Recruiter Approval**: Approve or reject recruiter registrations
- **User Management**: Suspend or activate recruiter accounts
- **System Settings**: Configure default interview settings
- **Statistics Dashboard**: Overview of platform usage

## 🗂️ Project Structure

```
HireGenius/
├── admin/                  # Admin panel files
│   ├── dashboard.php       # Admin dashboard
│   ├── login.php          # Admin login
│   ├── logout.php         # Admin logout
│   ├── recruiters.php     # Manage recruiters
│   └── settings.php       # System settings
│
├── assets/
│   └── css/
│       └── style.css      # Main stylesheet
│
├── candidate/              # Candidate interview files
│   ├── complete-interview.php  # Mark interview complete
│   ├── interview.php      # Interview questions page
│   ├── join.php           # Join interview form
│   ├── save-answer.php    # Save answer API
│   └── thank-you.php      # Completion page
│
├── config/                 # Configuration files
│   ├── config.example.php # Example configuration
│   └── Database.php       # Database connection class
│
├── database/               # Database files
│   └── hiregenius.sql     # SQL schema and seed data
│
├── includes/               # Shared PHP files
│   ├── helpers.php        # Utility functions
│   └── init.php           # Application bootstrap
│
├── public/                 # Public files
│   └── index.php          # Landing page
│
├── recruiter/              # Recruiter panel files
│   ├── create-interview.php   # Create new interview
│   ├── dashboard.php      # Recruiter dashboard
│   ├── interviews.php     # List interviews
│   ├── login.php          # Recruiter login
│   ├── logout.php         # Recruiter logout
│   ├── register.php       # Recruiter registration
│   └── view-responses.php # View candidate responses
│
├── templates/              # Shared templates
│   ├── header.php         # Page header
│   └── footer.php         # Page footer
│
├── .gitignore             # Git ignore rules
└── README.md              # This file
```

## 🚀 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP/MAMP (for local development)

### Setup Steps

1. **Clone or Download**
   ```bash
   cd /path/to/htdocs
   git clone <repository-url> HireGenius
   ```

2. **Create Database**
   - Open phpMyAdmin or MySQL CLI
   - Import `database/hiregenius.sql`
   ```bash
   mysql -u root -p < database/hiregenius.sql
   ```

3. **Configure Application**
   ```bash
   cd HireGenius/config
   cp config.example.php config.php
   ```
   Edit `config.php` with your database credentials:
   ```php
   'database' => [
       'host'     => 'localhost',
       'username' => 'root',
       'password' => 'your_password',
       'database' => 'hiregenius',
   ],
   ```

4. **Set Permissions** (Linux/Mac)
   ```bash
   chmod 755 -R HireGenius
   ```

5. **Access Application**
   - Landing Page: `http://localhost/HireGenius/public/`
   - Admin Login: `http://localhost/HireGenius/admin/login.php`
   - Recruiter Login: `http://localhost/HireGenius/recruiter/login.php`

### Default Admin Credentials
- **Email**: admin@hiregenius.com
- **Password**: admin123

⚠️ **Important**: Change the default admin password after first login!

## 🔧 Configuration

### Application Settings (`config/config.php`)

| Setting | Description | Default |
|---------|-------------|---------|
| `app.name` | Application name | HireGenius |
| `app.debug` | Enable debug mode | false |
| `interview.default_time_per_question` | Seconds per question | 180 |
| `interview.max_questions` | Max questions per interview | 20 |

### Default Interview Questions

The system includes 9 default questions that can be customized in the config file. Recruiters can also create custom questions for each interview.

## 📱 Features Overview

### Interview Flow
1. Recruiter creates an interview with questions
2. System generates a unique 6-digit code
3. Candidate enters the code to join
4. Candidate answers questions with time limits
5. Answers are saved and marked complete
6. Recruiter reviews responses in dashboard

### Security Features
- Password hashing (bcrypt)
- CSRF protection
- Session management
- Input validation and sanitization
- Prepared statements (SQL injection prevention)

## 🎨 Customization

### Styling
All styles are in `assets/css/style.css` using CSS custom properties (variables) for easy theming:

```css
:root {
    --primary: #f39c12;
    --primary-dark: #e67e22;
    --success: #27ae60;
    --danger: #e74c3c;
    /* ... more variables */
}
```

### Adding New Features
1. Create PHP files in appropriate directories
2. Include `includes/init.php` for database and helpers
3. Use helper functions for common tasks
4. Follow existing code patterns

## 📄 API Endpoints

### Candidate APIs (JSON)
- `POST /candidate/save-answer.php` - Save interview answer
- `POST /candidate/complete-interview.php` - Mark interview complete

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error**
- Verify credentials in `config/config.php`
- Ensure MySQL service is running
- Check database exists

**Session Issues**
- Clear browser cookies
- Check PHP session settings
- Verify `session_start()` is called

**Styling Not Loading**
- Check file paths
- Clear browser cache
- Verify CSS file exists

## 📝 License

This project is for educational purposes. Feel free to use and modify.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## 📧 Support

For issues or questions, please open an issue on the repository.

---

**Built with ❤️ using PHP, MySQL, and modern CSS**
