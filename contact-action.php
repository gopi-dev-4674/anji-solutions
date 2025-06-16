<?php
// error_reporting(E_ALL); ini_set('display_errors', 1); // Uncomment during development
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Sanitize and validate inputs
    $name = htmlspecialchars(strip_tags($_POST["name"] ?? ''));
    $email = filter_var($_POST["email"] ?? '', FILTER_SANITIZE_EMAIL);
    $phone = preg_replace("/[^0-9]/", "", $_POST["phone"] ?? '');
    $date = htmlspecialchars($_POST["date"] ?? '');
    $service = htmlspecialchars($_POST["service"] ?? '');
    $address = htmlspecialchars(strip_tags($_POST["address"] ?? ''));
    $message = htmlspecialchars(strip_tags($_POST["message"] ?? ''));

    // 2. Validation
    $errors = [];
    if (empty($name)) $errors[] = "Name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";
    if (strlen($phone) != 10) $errors[] = "Phone number must be 10 digits.";
    if (empty($date)) $errors[] = "Preferred date is required.";
    if (empty($service)) $errors[] = "Service type is required.";
    if (empty($address)) $errors[] = "Address is required.";
    if (empty($message)) $errors[] = "Message is required.";

    if (!empty($errors)) {
        // Redirect to form with error flag
        header("Location: contact.html?error=1");
        exit();
    }

    // 3. DB connection
    $servername = "localhost"; // Stays the same on Hostinger
    $username = "u877255360_anjisolutions"; // Example: u123456789_user
    $password = "Anji@414";           // The password you set
    $dbname   = "u877255360_anji_solutions";     // Example: u123456789_db

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        header("Location: contact.html?error=db");
        exit();
    }

    // 4. Store in DB
    $sql = "INSERT INTO contact_form (name, email, phone, date, service, address, message)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $name, $email, $phone, $date, $service, $address, $message);

    if ($stmt->execute()) {
        // 5. Send email to admin
        $to = "pgopi1486@gmail.com"; // Replace with actual admin email
        $to = "pgopi1486@gmail.com"; // Replace with actual admin email
$subject = "New Contact Request - Anji Solutions";

$email_message = '
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        margin: 0;
        padding: 20px;
    }
    .email-container {
        max-width: 600px;
        margin: auto;
        background: #ffffff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    .logo {
        text-align: center;
        margin-bottom: 20px;
    }
    .logo img {
        max-width: 180px;
    }
    .content {
        font-size: 15px;
        color: #333333;
    }
    .content strong {
        color: #000;
    }
    .footer {
        margin-top: 30px;
        font-size: 13px;
        text-align: left;
        color: #555;
    }
  </style>
</head>
<body>
  <div class="email-container">
    <div class="logo">
      <img src="https://anjisolutions.com/images/anji.png" alt="Anji Solutions Logo">
    </div>

    <div class="content">
      <p>Hi <strong>Admin</strong>,</p>
      <p>You have received a new contact request from the website.</p>

      <p><strong>Name:</strong> ' . $name . '</p>
      <p><strong>Email:</strong> ' . $email . '</p>
      <p><strong>Phone:</strong> ' . $phone . '</p>
      <p><strong>Preferred Date:</strong> ' . $date . '</p>
      <p><strong>Service:</strong> ' . $service . '</p>
      <p><strong>Address:</strong> ' . $address . '</p>
      <p><strong>Message:</strong><br>' . nl2br($message) . '</p>
    </div>

    <div class="footer">
      <p>Thanks & Regards,<br><strong>Team Anji Solutions</strong></p>
    </div>
  </div>
</body>
</html>
';

$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type:text/html;charset=UTF-8\r\n";
$headers .= "From: no-reply@anjisolutions.com\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

        if (mail($to, $subject, $email_message, $headers)) {
            header("Location: contact.html?success=1");
        } else {
            header("Location: contact.html?error=mail");
        }
    } else {
        header("Location: contact.html?error=db");
    }

    $stmt->close();
    $conn->close();
    exit();
} else {
    echo "Invalid request.";
}
