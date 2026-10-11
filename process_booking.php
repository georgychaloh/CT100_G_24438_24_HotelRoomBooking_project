
<?php
// Grand Haven Hotel - Booking Processing System

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: booking.html");
    exit;
}

// 1. Receive and clean form data
$full_name = trim($_POST["full_name"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$room_type = $_POST["room_type"] ?? "";
$nights = filter_var($_POST["nights"] ?? "", FILTER_VALIDATE_INT);
$guests = filter_var($_POST["guests"] ?? "", FILTER_VALIDATE_INT);

// 2. Define room prices and guest capacities
$room_prices = [
    "single" => 3000,
    "double" => 5000,
    "family" => 8000
];

$room_names = [
    "single" => "Single Room",
    "double" => "Double Room",
    "family" => "Family Room"
];

$room_capacities = [
    "single" => 1,
    "double" => 2,
    "family" => 4
];

// 3. Validate customer information
$errors = [];

if ($full_name === "") {
    $errors[] = "Full name is required.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address.";
}

if (!preg_match('/^[0-9]{10}$/', $phone)) {
    $errors[] = "Phone number must contain exactly 10 digits.";
}

if (!isset($room_prices[$room_type])) {
    $errors[] = "Please select a valid room type.";
}

if ($nights === false || $nights < 1 || $nights > 14) {
    $errors[] = "Number of nights must be between 1 and 14.";
}

if ($guests === false || $guests < 1) {
    $errors[] = "Number of guests must be at least 1.";
}

if (
    isset($room_capacities[$room_type]) &&
    $guests !== false &&
    $guests > $room_capacities[$room_type]
) {
    $errors[] = "The selected room accommodates a maximum of "
        . $room_capacities[$room_type] . " guest(s).";
}

// 4. If validation fails, display errors
function escape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

if (!empty($errors)) {
    http_response_code(400);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Booking Error - Grand Haven Hotel</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <main class="booking-container">
            <h1>Booking Could Not Be Processed</h1>
            <p>Please correct the following errors:</p>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo escape($error); ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="booking.html" class="btn">Return to Booking</a>
        </main>
    </body>
    </html>
    <?php
    exit;
}

// 5. Calculate the booking bill
$room_cost = $room_prices[$room_type] * $nights;

$breakfast_selected = isset($_POST["breakfast"]);
$transfer_selected = isset($_POST["airport_transfer"]);
$conference_selected = isset($_POST["conference_room"]);

$breakfast_cost = $breakfast_selected
    ? 700 * $guests * $nights
    : 0;

$transfer_cost = $transfer_selected ? 2000 : 0;

$conference_cost = $conference_selected ? 5000 : 0;

$total_cost = $room_cost
    + $breakfast_cost
    + $transfer_cost
    + $conference_cost;

// 6. Display the booking confirmation
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - Grand Haven Hotel</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<main class="booking-container">
    <h1>Grand Haven Hotel</h1>
    <h2>Booking Confirmation</h2>

    <p>Thank you, <strong><?php echo escape($full_name); ?></strong>!</p>
    <p>Your booking details have been processed successfully.</p>

    <h3>Guest Details</h3>
    <p><strong>Full Name:</strong> <?php echo escape($full_name); ?></p>
    <p><strong>Email:</strong> <?php echo escape($email); ?></p>
    <p><strong>Phone:</strong> <?php echo escape($phone); ?></p>

    <h3>Booking Details</h3>
    <p><strong>Room Type:</strong>
        <?php echo escape($room_names[$room_type]); ?>
    </p>
    <p><strong>Number of Nights:</strong> <?php echo $nights; ?></p>
    <p><strong>Number of Guests:</strong> <?php echo $guests; ?></p>

    <h3>Bill Summary</h3>
    <p>Room charges:
        KSh <?php echo number_format($room_cost, 2); ?>
    </p>

    <p>Breakfast:
        KSh <?php echo number_format($breakfast_cost, 2); ?>
        <?php echo $breakfast_selected ? "(Selected)" : "(Not selected)"; ?>
    </p>

    <p>Airport transfer:
        KSh <?php echo number_format($transfer_cost, 2); ?>
        <?php echo $transfer_selected ? "(Selected)" : "(Not selected)"; ?>
    </p>

    <p>Conference room:
        KSh <?php echo number_format($conference_cost, 2); ?>
        <?php echo $conference_selected ? "(Selected)" : "(Not selected)"; ?>
    </p>

    <hr>

    <h2>Total Bill: KSh <?php echo number_format($total_cost, 2); ?></h2>

    <p>Please contact the hotel to confirm availability and payment arrangements.</p>

    <a href="index.html" class="btn">Return to Home</a>
    <a href="booking.html" class="btn">Make Another Booking</a>
</main>

</body>
</html>
