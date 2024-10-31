<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Include Composer's autoload file to load PHPMailer, dotenv, and Monolog
require '../vendor/autoload.php';

// Initialize Monolog
$log = new Logger('complaint_logger');
$log->pushHandler(new StreamHandler(__DIR__ . '/../logs/complaints.log', Logger::INFO));

// Load environment variables from the .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $complaint = $_POST['complaint'];

    // Validate form data (optional but recommended)
    if (empty($name) || empty($email) || empty($complaint)) {
        echo "Alle velden zijn verplicht!";
    } else {
        // Log complaint details
        $log->info("New complaint received", [
            'name' => $name,
            'email' => $email,
            'complaint' => $complaint
        ]);

        // Send the email
        sendComplaintEmail($name, $email, $complaint, $log);
    }
}

function sendComplaintEmail($name, $email, $complaint, $log) {
    $mail = new PHPMailer(true);

    try {
        // Server settings from .env
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USER'];
        $mail->Password = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom($_ENV['SMTP_USER'], 'Klachtverwerking');
        $mail->addAddress($email);
        $mail->addCC($_ENV['SMTP_USER']);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Uw klacht is in behandeling';
        $mail->Body    = "<p>Beste $name,</p>
                          <p>Uw klacht is in behandeling. Hieronder vindt u de details:</p>
                          <p><strong>Klantnaam:</strong> $name</p>
                          <p><strong>E-mail:</strong> $email</p>
                          <p><strong>Omschrijving klacht:</strong> $complaint</p>";

        // Send the email
        $mail->send();
        echo 'Klacht succesvol verzonden!';
        $log->info("Complaint email successfully sent to $email");

    } catch (Exception $e) {
        echo "Er is een fout opgetreden bij het verzenden van de e-mail: {$mail->ErrorInfo}";
        $log->error("Failed to send complaint email", [
            'error' => $mail->ErrorInfo,
            'name' => $name,
            'email' => $email,
            'complaint' => $complaint
        ]);
    }
}
?>
