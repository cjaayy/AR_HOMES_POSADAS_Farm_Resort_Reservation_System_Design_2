# Configuration Setup Guide

## Required Configuration Files

This project requires several configuration files that contain sensitive credentials. These files are **NOT** included in the repository for security reasons.

### Setup Instructions

1. **Database Configuration**

   ```bash
   cp config/database.example.php config/database.php
   ```

   Edit `config/database.php` and set your database credentials:

   - `DB_HOST` - Database host (usually `localhost`)
   - `DB_USER` - Database username
   - `DB_PASS` - Database password
   - `DB_NAME` - Database name (`ar_homes_resort_db`)

2. **Email Configuration**

   ```bash
   cp config/mail.example.php config/mail.php
   ```

   Edit `config/mail.php` and set your SMTP credentials:

   - For Gmail, create an App Password at: https://myaccount.google.com/apppasswords
   - Set `MAIL_USERNAME` to your Gmail address
   - Set `MAIL_PASSWORD` to your App Password

3. **Cloudflare Tunnel (Optional)**
   ```bash
   cp config/cloudflare.example.php config/cloudflare.php
   ```
   Only needed if you want external access via Cloudflare Tunnel.

### Important Security Notes

⚠️ **NEVER** commit these files to git:

- `config/database.php`
- `config/mail.php`
- `config/cloudflare.php`
- `config/connection.php`

These files are protected by `.gitignore` and should remain local to your development environment.

### Files Structure

```
config/
├── database.example.php    ← Template (committed to git)
├── database.php            ← Your actual config (NOT in git)
├── mail.example.php        ← Template (committed to git)
├── mail.php                ← Your actual config (NOT in git)
├── cloudflare.example.php  ← Template (committed to git)
├── cloudflare.php          ← Your actual config (NOT in git)
├── connection.php          ← Uses database.php (NOT in git)
└── Mailer.php              ← Email service (safe to commit)
```

## Quick Start

After cloning the repository:

```bash
# 1. Copy example configs
cp config/database.example.php config/database.php
cp config/mail.example.php config/mail.php

# 2. Edit the configs with your credentials
# Use your favorite text editor to update database.php and mail.php

# 3. Import the database
# (Import your SQL file using phpMyAdmin or command line)

# 4. Start your development server
# Open in XAMPP or your preferred local server
```

## Need Help?

- Database setup issues? Check XAMPP/MySQL is running
- Email not sending? Verify Gmail App Password is correct
- Access denied? Ensure file permissions are correct
