<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include Composer's autoload file to load PHPMailer and dotenv
require '../vendor/autoload.php';

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
        // Send the email
        sendComplaintEmail($name, $email, $complaint);
    }
}

function sendComplaintEmail($name, $email, $complaint) {
    $mail = new PHPMailer(true);

    try {
        // Server settings from .env
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];  // Make sure this is 'smtp.gmail.com'
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USER']; // Your Gmail address
        $mail->Password = $_ENV['SMTP_PASS']; // Your Gmail app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use TLS
        $mail->Port = 587; // Use 587 for TLS

        // Recipients
        $mail->setFrom($_ENV['SMTP_USER'], 'Klachtverwerking'); // Your email as sender
        $mail->addAddress($email); // Send email to the user who submitted the form
        $mail->addCC($_ENV['SMTP_USER']); // Add yourself as a CC

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
    } catch (Exception $e) {
        echo "Er is een fout opgetreden bij het verzenden van de e-mail: {$mail->ErrorInfo}";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klachtenformulier</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        form {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <h1>Dien uw klacht in</h1>
    <form method="POST" action="">
        <label for="name">Naam:</label>
        <input type="text" id="name" name="name" required>

        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email" required>

        <label for="complaint">Omschrijving klacht:</label>
        <textarea id="complaint" name="complaint" rows="5" required></textarea>

        <button type="submit">Verstuur</button>
    </form>
</body>
</html>