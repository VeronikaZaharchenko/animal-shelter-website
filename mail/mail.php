<?php
date_default_timezone_set('Europe/Moscow');

$name = htmlspecialchars(trim($_POST['name']), ENT_QUOTES, 'UTF-8');
$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
$message = htmlspecialchars(strip_tags(trim($_POST['message'])), ENT_QUOTES, 'UTF-8');

$domen = htmlspecialchars($_SERVER['SERVER_NAME'], ENT_QUOTES, 'UTF-8');
$ip = htmlspecialchars($_SERVER['SERVER_ADDR'], ENT_QUOTES, 'UTF-8');
$user_ip = htmlspecialchars($_SERVER['REMOTE_ADDR'], ENT_QUOTES, 'UTF-8');
$user_agent = htmlspecialchars($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES, 'UTF-8');
$date = date('d.m.Y в H:i');

header('Access-Control-Allow-Origin: *');
http_response_code(200);

$error = '';
if (empty($name)) {
    $error = 'Необходимо обязательно ввести ваше имя.';
} elseif (strlen($name) > 50) {
    $error = 'Ваше имя должно содержать максимум 50 символов.';
} elseif (empty($email)) {
    $error = 'Необходимо обязательно ввести e-mail адрес.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Недопустимый формат e-mail адреса. Проверьте правильность написания адреса.';
} elseif (empty($message)) {
    $error = 'Необходимо обязательно ввести текст сообщения.';
} elseif (strlen($message) < 5 || strlen($message) > 5000) {
    $error = 'Текст сообщения должен содержать от 5 до 5000 символов.';
}

if ($error) {
    echo $error;
    exit;
}


$body = "
<h1 class='head'>Сообщение с формы обратной связи</h1>
<table>
<tr><th>Параметр</th><th>Значение</th></tr>
<tr><td>Имя домена</td><td>{$domen}</td></tr>
<tr><td>IP адрес сервера</td><td>{$ip}</td></tr>
<tr><td>Имя пользователя</td><td>{$name}</td></tr>
<tr><td>IP пользователя</td><td>{$user_ip}</td></tr>
<tr><td>Агент пользователя</td><td>{$user_agent}</td></tr>
<tr><td>Электронная почта</td><td>{$email}</td></tr>
<tr><td>Текст сообщения</td><td>{$message}</td></tr>
<tr><td>Время поступления</td><td>{$date}</td></tr>
</table>
<style>
table {
    border-collapse: collapse;
    min-width: 320px;
    width: 50%;
    background: LightCyan;
    font-size: medium;
}
td, th {
    padding: 1em;
    border: gray 2px solid;
}
</style>
";

$headers = "From: admin@dp-zaharchenko.xn--80ahdri7a.site\n";
$headers .= "Content-Type: text/html; charset=UTF-8";

$theme = "Сообщение с сайта {$domen}";

if (mail('veronika03063@yandex.ru', $theme, $body, $headers)) {
    $result = 'true';
} else {
    $result = 'false';
}

echo $result;
?>
