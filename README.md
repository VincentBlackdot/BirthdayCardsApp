# Birthday Card Email System

A PHP-based system for sending birthday cards via email with features like template management, email tracking, and activity logging.

## Features

- Photo attachment in emails with inline display
- Email sending pre-loader animation
- Custom template uploading with validation
- Database integration for activity logging
- Template preview gallery
- Robust error handling
- Custom message support
- Email open tracking

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer for PHP dependencies
- SMTP server access for sending emails (Gmail recommended)

## Installation

1. Clone this repository to your web server directory
2. Create a MySQL database and import the schema:
   ```sql
   mysql -u root -p < database.sql
   ```

3. Install PHP dependencies:
   ```bash
   composer require phpmailer/phpmailer
   ```

4. Configure your environment:
   - Copy `.env.example` to `.env`
   - Update the `.env` file with your settings:
     ```ini
     EMAIL_USER=your_email@gmail.com
     EMAIL_PASSWORD=your_app_password
     SMTP_SERVER=smtp.gmail.com
     SMTP_PORT=587
     ```
   Note: For Gmail, you'll need to use an App Password. [Learn how to create one](https://support.google.com/accounts/answer/185833?hl=en)

5. Set up the database connection:
   - Open `config/database.php`
   - Update the database credentials if needed

6. Ensure required directories exist and are writable:
   ```bash
   mkdir -p uploads
   chmod 755 uploads
   ```

## Security Notes

The following files and directories are ignored by git for security:

- `.env` - Contains sensitive credentials
- `uploads/` - Contains user-uploaded files
- `*.log` - Contains debug and error logs
- `vendor/` - Contains dependencies
- `config/*.php` - Contains sensitive configurations (except database.php template)

Never commit the following sensitive information:
1. SMTP credentials
2. Database passwords
3. API keys
4. Debug logs
5. User uploaded content

## Development Setup

1. Copy `.env.example` to `.env`
2. Update `.env` with your local development settings
3. Make sure `uploads/` directory exists and is writable
4. Never commit the `.env` file or any sensitive credentials

## Troubleshooting

- If emails fail to send, check:
  1. SMTP credentials in `.env`
  2. Gmail App Password is correct
  3. SMTP ports are not blocked
  4. PHP error logs for detailed errors

## Contributing

1. Create a new branch for your feature
2. Ensure no sensitive data is committed
3. Test thoroughly before submitting PR
4. Update documentation as needed

## File Structure

```
├── api/
│   ├── get_templates.php
│   ├── send_email.php
│   ├── track_open.php
│   └── upload_template.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── config/
│   └── database.php
├── uploads/
│   └── templates/
├── vendor/
├── database.sql
├── index.php
└── README.md
```

## Usage

1. Access the application through your web browser
2. Upload birthday card templates through the upload form
3. Select a template from the gallery
4. Fill in recipient details and custom message
5. Send the birthday card
6. Track email opens in the database
