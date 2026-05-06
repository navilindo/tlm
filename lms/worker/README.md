# Email Queue Worker

This project stores outbound emails in `email_queue`.

## Send pending emails

Run:

```bash
cd lms/worker
composer require phpmailer/phpmailer
php send_email_queue.php
```

## SMTP configuration

Edit `lms/config.php`:
- `SMTP_HOST`, `SMTP_PORT`
- `SMTP_USERNAME`, `SMTP_PASSWORD`
- `FROM_EMAIL`, `FROM_NAME`

## Cron (example)

Run every minute:

```bash
* * * * * cd /path/to/lms/worker && php send_email_queue.php >/dev/null 2>&1
```

