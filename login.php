<?php
session_start();
include "config.php";

function sendOtp($phone) {
    $data = [
        'phone' => $phone,
        'gateway_key' => GATEWAY_KEY,
    ];

    $ch = curl_init(API_URL_SEND);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . MERCHANT_KEY,
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Log the result for debugging
    file_put_contents('api_log.txt', "Send OTP Response: " . $result . "\n", FILE_APPEND);
    
    if ($result === false) {
        return ['status' => 'error', 'message' => 'Unable to contact the API. ' . $error];
    }

    return json_decode($result, true);
}

function verifyOtp($phone, $otp, $otp_id) {
    $data = [
        'phone' => $phone,
        'otp' => $otp,
        'otp_id' => $otp_id,
        'gateway_key' => GATEWAY_KEY,
    ];

    $ch = curl_init(API_URL_VERIFY);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . MERCHANT_KEY,
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    // Log the result for debugging
    file_put_contents('api_log.txt', "Verify OTP Response: " . $result . "\n", FILE_APPEND);

    if ($result === false) {
        return ['status' => 'error', 'message' => 'Unable to contact the API. ' . $error];
    }

    return json_decode($result, true);
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['send_otp'])) {
        $phone = $_POST['phone'];
        $response = sendOtp($phone);
        
        if (isset($response['status']) && $response['status'] === true) {
            $_SESSION['phone'] = $phone;
            $_SESSION['otp_id'] = $response['data']['id'];
            $message = "Kode OTP Terkirim ke No Tujuan $phone. Silahkan Cek Pesan Whatsapp Anda Untuk Melihat Kode OTP.";
        } else {
            $message = "Failed to send OTP: " . ($response['message'] ?? 'Unknown error');
        }
    } elseif (isset($_POST['verify_otp'])) {
        $otp = implode('', $_POST['otp']); // Concatenate the individual OTP digits
        $phone = $_SESSION['phone'] ?? '';
        $otp_id = $_SESSION['otp_id'] ?? '';

        if ($phone && $otp_id) {
            $response = verifyOtp($phone, $otp, $otp_id);
            if (isset($response['status']) && $response['status'] === true) {
                $_SESSION['welcome_message'] = "Welcome, $phone!";
                header('Location: welcome.php');
                exit;
            } else {
                $message = "Gagal memverifikasi Kode OTP: " . ($response['message'] ?? 'Unknown error');
            }
        } else {
            $message = "No Telepon / Kode OTP Tidak Terdaftar.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login with OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('background.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            max-width: 400px;
            margin: 0;
            padding: 20px;
            border-radius: 8px;
            background-color: rgba(255, 255, 255, 0.9);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .message {
            color: red;
        }
        .otp-input {
            width: 50px;
            text-align: center;
            font-size: 1.5rem;
            margin: 0 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <center><img src="icon_otp.png" width="150"></center>
        <br/>
        <h2 class="text-center">Login</h2>
        <form method="POST" class="mb-4">
            <div class="mb-3">
                <input type="text" name="phone" class="form-control" placeholder="Enter your phone number" required>
            </div>
            <button type="submit" name="send_otp" class="btn btn-primary w-100">Dapatkan OTP</button>
        </form>

        <h2 class="text-center">Input Kode OTP</h2>
        <form method="POST">
            <div class="d-flex justify-content-center mb-3">
                <?php for ($i = 0; $i < 6; $i++): ?>
                    <input type="text" name="otp[]" class="otp-input" maxlength="1" pattern="[0-9]" required oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                <?php endfor; ?>
            </div>
            <button type="submit" name="verify_otp" class="btn btn-success w-100">Verifikasi OTP</button>
        </form>

        <?php if (!empty($message)): ?>
            <div class="message text-center mt-3"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
