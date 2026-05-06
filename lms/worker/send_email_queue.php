<?php
/**
 * Email Queue Worker
 * Sends pending emails from email_queue.
 * Run via CLI/cron in an environment that supports SMTP.
 */

require_once __DIR__ . '/../config.php';

// --- PHPMailer bootstrap (local dependency) ---
$vendorAutoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($vendorAutoload)) {
    die("PHPMailer not installed. Run: cd lms/worker && composer require phpmailer/phpmailer\n");
}
require_once $vendorAutoload;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

$db = getDB();

// Take a batch of pending emails
$queue = $db->fetchAll(
    "SELECT * FROM email_queue WHERE status = 'pending' ORDER BY id ASC LIMIT 10"
);

if (empty($queue)) {
    echo "No pending emails.\n";
    exit(0);
}

echo "Sending " . count($queue) . " pending email(s)...\n";

foreach ($queue as $item) {
    $id = (int)$item['id'];
    $to = $item['recipient_email'];
    $subject = $item['subject'];
    $message = $item['message'];
    $type = $item['type'];

    $is_html = true; // we store HTML in message for our verification emails

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = !empty(SMTP_USERNAME);
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->Port = (int)SMTP_PORT;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->CharSet = 'UTF-8';

        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($to);

        $mail->isHTML($is_html);
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = strip_tags($message);

        $mail->send();

        $db->execute(
            "UPDATE email_queue SET status = 'sent', sent_at = NOW(), error_message = NULL, attempts = attempts + 1, last_attempt = NOW() WHERE id = ?",
            [$id]
        );

        echo "[OK] Sent email_queue #{$id} to {$to}\n";
    } catch (PHPMailerException $e) {
        $err = $e->getMessage();
        $db->execute(
            "UPDATE email_queue SET status = 'failed', error_message = ?, attempts = attempts + 1, last_attempt = NOW() WHERE id = ?",
            [$err, $id]
        );
        echo "[FAIL] email_queue #{$id} to {$to}: {$err}\n";
    } catch (Exception $e) {
        $err = $e->getMessage();
        $db->execute(
            "UPDATE email_queue SET status = 'failed', error_message = ?, attempts = attempts + 1, last_attempt = NOW() WHERE id = ?",
            [$err, $id]
        );
        echo "[FAIL] email_queue #{$id} to {$to}: {$err}\n";
    }
}

echo "Done.\n";

