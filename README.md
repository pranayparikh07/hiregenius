# HireGenius

**Smart Video Interview Platform**

A modern, feature-rich video interview platform built with PHP and MySQL. HireGenius enables recruiters to create and manage video interviews, while candidates can record their responses from anywhere.

![PHP](https://img.shields.io/badge/PHP-7.4+-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green.svg)

---

## ✨ Features

### For Recruiters
- 📝 Create custom video interviews with multiple questions
- 🎯 Set time limits per question
- 📅 Schedule interview availability windows
- 👥 Invite candidates via unique interview codes
- 🎬 Review video responses with playback controls
- 📊 Track candidate progress and completion status

### For Candidates
- 🎥 Record video responses directly in browser
- ⏱️ Timer display for each question
- 🔄 Camera and microphone controls
- 📱 Responsive design for mobile devices
- ✅ Progress tracking through interview

### For Administrators
- 👤 Manage recruiter accounts
- ✅ Approve/reject recruiter registrations
- ⚙️ Configure system settings
- 📈 View platform statistics

---

## 🛠️ Requirements

- **XAMPP** (or similar PHP/MySQL stack)
- **PHP** 7.4 or higher
- **MySQL** 5.7 or higher (MariaDB compatible)
- **Web Browser** with WebRTC support (Chrome, Firefox, Edge)

---

## 🚀 Installation

### 1. Clone or Download

```bash
cd C:\xampp\htdocs
git clone https://github.com/yourusername/HireGenius.git
```

Or download and extract to `C:\xampp\htdocs\HireGenius`

### 2. Create Configuration File

```bash
cd HireGenius
copy config\config.example.php config\config.php
```

Edit `config\config.php` if needed (default settings work with XAMPP):

```php
'database' => [
    'host'     => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'hiregenius',
],
```

### 3. Import Database

1. Start XAMPP (Apache & MySQL)
2. Open phpMyAdmin: http://localhost/phpmyadmin
3. Click **Import** tab
4. Select `database/hiregenius.sql`
5. Click **Go**

### 4. Set Admin Password

Run this SQL query in phpMyAdmin to set the admin password:

```sql
UPDATE hiregenius.admins 
SET password = '$2y$10$0KZgsgwuQdB5yiBjivsq4eVgjAV5Qqh9gOquyrjajx33EurI6ex7q' 
WHERE email = 'admin@hiregenius.com';
```

### 5. Access the Application

- **Homepage:** http://localhost/HireGenius
- **Admin Panel:** http://localhost/HireGenius/admin/login.php
- **Recruiter Portal:** http://localhost/HireGenius/recruiter/login.php

---

## 👤 Default Admin Credentials

| Field | Value |
|-------|-------|
| Email | `admin@hiregenius.com` |
| Password | `admin123` |

> ⚠️ **Important:** Change the default password after first login!

---

## 📁 Project Structure

```
HireGenius/
├── admin/                  # Admin panel pages
│   ├── dashboard.php
│   ├── login.php
│   ├── logout.php
│   ├── recruiters.php
│   └── settings.php
├── assets/
│   └── css/
│       └── style.css       # Main stylesheet
├── candidate/              # Candidate interview pages
│   ├── interview.php       # Video recording interface
│   ├── start-interview.php
│   ├── upload-video.php
│   └── thank-you.php
├── config/
│   ├── config.example.php  # Example configuration
│   ├── config.php          # Your configuration (create this)
│   └── Database.php        # Database connection class
├── database/
│   └── hiregenius.sql      # Database schema
├── includes/
│   ├── helpers.php         # Helper functions
│   └── init.php            # Initialization script
├── public/
│   └── index.php           # Landing page
├── recruiter/              # Recruiter portal pages
│   ├── create-interview.php
│   ├── dashboard.php
│   ├── login.php
│   ├── signup.php
│   └── view-responses.php
├── uploads/
│   └── videos/             # Recorded video storage
├── .gitignore
├── index.php               # Root redirect
└── README.md
```

---

## 🎬 How It Works

### 1. Recruiter Registration
- Recruiter signs up with company details
- Admin approves the account
- Recruiter can now create interviews

### 2. Create Interview
- Recruiter creates interview with title and description
- Adds custom questions (or uses defaults)
- Sets time limit per question
- Sets availability window (start/end dates)
- Gets unique interview code

### 3. Candidate Interview
- Candidate enters interview code
- Provides name and email
- Grants camera/microphone permission
- Records video answer for each question
- Submits when complete

### 4. Review Responses
- Recruiter views all candidate submissions
- Watches video responses
- Downloads videos if needed
- Tracks completion status

---

## 🔧 Configuration Options

Edit `config/config.php` to customize:

```php
return [
    'database' => [
        'host'     => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'hiregenius',
    ],
    'app' => [
        'name'     => 'HireGenius',
        'url'      => 'http://localhost/HireGenius',
        'timezone' => 'UTC',
        'debug'    => false,
    ],
    'interview' => [
        'default_time_per_question' => 180,  // 3 minutes
        'max_questions'             => 20,
    ],
];
```

---

## 🔒 Security Features

- CSRF protection on all forms
- Password hashing with bcrypt
- Prepared statements (SQL injection prevention)
- Session security settings
- Input validation and sanitization
- Secure file upload handling

---

## 📱 Browser Compatibility

| Browser | Video Recording | Playback |
|---------|-----------------|----------|
| Chrome 60+ | ✅ | ✅ |
| Firefox 55+ | ✅ | ✅ |
| Edge 79+ | ✅ | ✅ |
| Safari 14.1+ | ✅ | ✅ |

> Note: Video recording requires HTTPS in production (localhost is exempt)

---

## 🐛 Troubleshooting

### "Configuration file not found"
Copy the example config:
```bash
copy config\config.example.php config\config.php
```

### "Invalid email or password"
Re-run the password update SQL in phpMyAdmin (see Installation step 4)

### Camera not working
- Ensure browser has camera permission
- Use HTTPS in production
- Check if camera is used by another application

### Video upload fails
- Check `uploads/videos/` folder exists and is writable
- Increase PHP upload limits in `php.ini`:
  ```ini
  upload_max_filesize = 100M
  post_max_size = 100M
  max_execution_time = 300
  ```

---

## 📄 License

This project is licensed under the MIT License.

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📞 Support

For issues and questions, please open a GitHub issue.

---

Made with ❤️ for modern recruitment
