<?php
/**
 * TripNexus - Email Service Class
 * Handles all email sending via Gmail SMTP using SwiftMailer-like approach
 */

require_once __DIR__ . '/../config/mail_config.php';
require_once __DIR__ . '/../db.php';

class EmailService {
    private $host;
    private $port;
    private $username;
    private $password;
    private $fromEmail;
    private $fromName;
    private $conn;

    public function __construct($connection) {
        $this->host = MAIL_HOST;
        $this->port = MAIL_PORT;
        $this->username = MAIL_USERNAME;
        // Remove spaces from password (Gmail adds spaces in app passwords for readability)
        $this->password = str_replace(' ', '', MAIL_PASSWORD);
        $this->fromEmail = MAIL_FROM_EMAIL;
        $this->fromName = MAIL_FROM_NAME;
        $this->conn = $connection;
    }

    /**
     * Send verification email with code
     */
    public function sendVerificationEmail($toEmail, $fullname, $verificationCode) {
        if (!ENABLE_EMAIL) return true;

        $subject = 'Verify Your Email - TripNexus';
        $templateFile = __DIR__ . '/email_templates/verification_email.html';
        
        if (!file_exists($templateFile)) {
            $this->logEmail($toEmail, 'error', 'Template file not found');
            return false;
        }

        $body = file_get_contents($templateFile);
        $body = str_replace(
            ['{{fullname}}', '{{verification_code}}', '{{expiry_minutes}}'],
            [$fullname, $verificationCode, VERIFICATION_CODE_EXPIRY],
            $body
        );

        return $this->send($toEmail, $subject, $body);
    }

    /**
     * Send password reset email with link
     */
    public function sendPasswordResetEmail($toEmail, $fullname, $resetToken) {
        if (!ENABLE_EMAIL) return true;

        $subject = 'Reset Your Password - TripNexus';
        $resetLink = APP_URL . '/new_password.html?token=' . $resetToken;
        $templateFile = __DIR__ . '/email_templates/password_reset_email.html';
        
        if (!file_exists($templateFile)) {
            $this->logEmail($toEmail, 'error', 'Template file not found');
            return false;
        }

        $body = file_get_contents($templateFile);
        $body = str_replace(
            ['{{fullname}}', '{{reset_link}}', '{{expiry_minutes}}'],
            [$fullname, $resetLink, RESET_TOKEN_EXPIRY],
            $body
        );

        return $this->send($toEmail, $subject, $body);
    }

    /**
     * Send booking confirmation email
     */
    public function sendBookingConfirmation($toEmail, $fullname, $bookingDetails) {
        if (!ENABLE_EMAIL) return true;

        $subject = 'Booking Confirmation - TripNexus';
        $templateFile = __DIR__ . '/email_templates/booking_confirmation_email.html';
        
        if (!file_exists($templateFile)) {
            $this->logEmail($toEmail, 'error', 'Template file not found');
            return false;
        }

        $body = file_get_contents($templateFile);
        $body = str_replace(
            ['{{fullname}}', '{{booking_details}}'],
            [$fullname, $this->formatBookingDetails($bookingDetails)],
            $body
        );

        return $this->send($toEmail, $subject, $body);
    }

    /**
     * Send order notification email
     */
    public function sendOrderNotification($toEmail, $fullname, $orderDetails) {
        if (!ENABLE_EMAIL) return true;

        $subject = 'Order Update - TripNexus';
        $templateFile = __DIR__ . '/email_templates/order_notification_email.html';
        
        if (!file_exists($templateFile)) {
            $this->logEmail($toEmail, 'error', 'Template file not found');
            return false;
        }

        $body = file_get_contents($templateFile);
        $body = str_replace(
            ['{{fullname}}', '{{order_details}}'],
            [$fullname, $this->formatOrderDetails($orderDetails)],
            $body
        );

        return $this->send($toEmail, $subject, $body);
    }

    /**
     * Save notification to database
     */
    public function saveNotification($userId, $type, $subject, $message) {
        $query = pg_query_params(
            $this->conn,
            'INSERT INTO notifications (user_id, type, subject, message) VALUES ($1, $2, $3, $4)',
            array($userId, $type, $subject, $message)
        );

        return $query !== false;
    }

    /**
     * Core email sending method using SMTP
     */
    private function send($toEmail, $subject, $htmlBody) {
        try {
            $socket = $this->createSMTPConnection();
            
            if (!$socket) {
                $this->logEmail($toEmail, 'error', 'Failed to connect to SMTP server');
                return false;
            }

            // Read server greeting
            $response = $this->readResponse($socket);
            if (strpos($response, '220') === false) {
                fclose($socket);
                $this->logEmail($toEmail, 'error', 'Invalid server response: ' . trim($response));
                return false;
            }

            // Send EHLO
            $this->writeCommand($socket, "EHLO localhost");
            $response = $this->readResponse($socket);

            // Start TLS
            $this->writeCommand($socket, "STARTTLS");
            $response = $this->readResponse($socket);
            
            if (strpos($response, '220') === false) {
                fclose($socket);
                $this->logEmail($toEmail, 'error', 'STARTTLS failed: ' . trim($response));
                return false;
            }

            // Enable encryption
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($socket);
                $this->logEmail($toEmail, 'error', 'Failed to enable TLS encryption');
                return false;
            }

            // Send EHLO again after TLS
            $this->writeCommand($socket, "EHLO localhost");
            $response = $this->readResponse($socket);

            // Authenticate
            $this->writeCommand($socket, "AUTH LOGIN");
            $response = $this->readResponse($socket);

            if (strpos($response, '334') === false) {
                fclose($socket);
                $this->logEmail($toEmail, 'error', 'AUTH LOGIN failed: ' . trim($response));
                return false;
            }

            // Send username
            $this->writeCommand($socket, base64_encode($this->username));
            $response = $this->readResponse($socket);

            if (strpos($response, '334') === false) {
                fclose($socket);
                $this->logEmail($toEmail, 'error', 'Username authentication failed');
                return false;
            }

            // Send password
            $this->writeCommand($socket, base64_encode($this->password));
            $response = $this->readResponse($socket);

            if (strpos($response, '235') === false) {
                fclose($socket);
                $this->logEmail($toEmail, 'error', 'Password authentication failed: ' . trim($response));
                return false;
            }

            // Mail from
            $this->writeCommand($socket, "MAIL FROM:<{$this->fromEmail}>");
            $response = $this->readResponse($socket);

            if (strpos($response, '250') === false) {
                fclose($socket);
                $this->logEmail($toEmail, 'error', 'MAIL FROM failed: ' . trim($response));
                return false;
            }

            // Recipient
            $this->writeCommand($socket, "RCPT TO:<{$toEmail}>");
            $response = $this->readResponse($socket);

            if (strpos($response, '250') === false) {
                fclose($socket);
                $this->logEmail($toEmail, 'error', 'RCPT TO failed: ' . trim($response));
                return false;
            }

            // Data
            $this->writeCommand($socket, "DATA");
            $response = $this->readResponse($socket);

            if (strpos($response, '354') === false) {
                fclose($socket);
                $this->logEmail($toEmail, 'error', 'DATA command failed: ' . trim($response));
                return false;
            }

            // Send headers and body
            $headers = $this->buildHeaders($toEmail, $subject);
            $fullMessage = $headers . "\r\n" . $htmlBody;
            
            fwrite($socket, $fullMessage . "\r\n.\r\n");
            $response = $this->readResponse($socket);

            if (strpos($response, '250') === false) {
                fclose($socket);
                $this->logEmail($toEmail, 'error', 'Failed to send message: ' . trim($response));
                return false;
            }

            // Quit
            $this->writeCommand($socket, "QUIT");
            fclose($socket);

            $this->logEmail($toEmail, 'success', 'Email sent successfully');
            return true;

        } catch (Exception $e) {
            $this->logEmail($toEmail, 'error', $e->getMessage());
            return false;
        }
    }

    /**
     * Create SMTP socket connection
     */
    private function createSMTPConnection() {
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $socket = @stream_socket_client(
            "tcp://{$this->host}:{$this->port}",
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $context
        );

        return $socket;
    }

    /**
     * Write command to socket
     */
    private function writeCommand(&$socket, $command) {
        fwrite($socket, $command . "\r\n");
    }

    /**
     * Read response from socket
     */
    private function readResponse(&$socket, $length = 1024) {
        $response = '';
        while ($line = fgets($socket, $length)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') {
                break;
            }
        }
        return $response;
    }

    /**
     * Build email headers
     */
    private function buildHeaders($toEmail, $subject) {
        $headers = "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "To: {$toEmail}\r\n";
        $headers .= "Subject: {$subject}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: 8bit\r\n";
        $headers .= "X-Mailer: TripNexus\r\n";
        return trim($headers);
    }

    /**
     * Format booking details for email
     */
    private function formatBookingDetails($details) {
        if (is_array($details)) {
            $formatted = "<ul>";
            foreach ($details as $key => $value) {
                $formatted .= "<li><strong>" . ucfirst(str_replace('_', ' ', $key)) . ":</strong> {$value}</li>";
            }
            $formatted .= "</ul>";
            return $formatted;
        }
        return $details;
    }

    /**
     * Format order details for email
     */
    private function formatOrderDetails($details) {
        return $this->formatBookingDetails($details);
    }

    /**
     * Log email activity
     */
    private function logEmail($toEmail, $status, $message) {
        $logDir = EMAIL_LOG_DIR;
        
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . date('Y-m-d') . '.log';
        $logMessage = date('H:i:s') . " | {$status} | {$toEmail} | {$message}\n";
        @file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

}
?>