<?php
// save.php — обработка формы и сохранение в БД
$host = 'localhost';
$dbname = 'u82683';    
$user = 'u82683';      
$pass = '1511698';   

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к БД: " . $e->getMessage());
}

function redirectWithError($msg, $data = []) {
    $params = http_build_query(array_merge(['error' => $msg], $data));
    header("Location: index.html?$params");
    exit;
}

$fullname = trim($_POST['fullname'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$birthdate = $_POST['birthdate'] ?? '';
$gender = $_POST['gender'] ?? '';
$languages = $_POST['languages'] ?? [];
$biography = trim($_POST['biography'] ?? '');
$contract = isset($_POST['contract']) ? 1 : 0;

// Валидация ФИО
if (!preg_match('/^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u', $fullname) || mb_strlen($fullname) > 150) {
    redirectWithError('ФИО должно содержать только буквы, пробелы и дефис (макс. 150 символов)', $_POST);
}
// Телефон
if (!preg_match('/^[\+\d\s\-\(\)]{10,20}$/', $phone)) {
    redirectWithError('Некорректный формат телефона', $_POST);
}
// Email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectWithError('Некорректный email', $_POST);
}
// Дата рождения
if (!strtotime($birthdate) || strtotime($birthdate) > time()) {
    redirectWithError('Некорректная дата рождения', $_POST);
}
// Пол
$allowedGenders = ['male', 'female', 'other'];
if (!in_array($gender, $allowedGenders)) {
    redirectWithError('Выберите корректный пол', $_POST);
}
// Языки программирования
$validLangNames = $pdo->query("SELECT name FROM programming_languages")->fetchAll(PDO::FETCH_COLUMN);
$validLanguages = array_intersect($languages, $validLangNames);
if (empty($validLanguages)) {
    redirectWithError('Выберите хотя бы один допустимый язык программирования', $_POST);
}
// Биография
if (mb_strlen($biography) > 5000) {
    redirectWithError('Биография не должна превышать 5000 символов', $_POST);
}
// Контракт
if (!$contract) {
    redirectWithError('Необходимо подтвердить ознакомление с контрактом', $_POST);
}

// Сохранение в БД
try {
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("INSERT INTO applications (full_name, phone, email, birth_date, gender, biography, contract_accepted) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$fullname, $phone, $email, $birthdate, $gender, $biography, $contract]);
    $appId = $pdo->lastInsertId();
    
    $placeholders = implode(',', array_fill(0, count($validLanguages), '?'));
    $stmtLang = $pdo->prepare("SELECT id FROM programming_languages WHERE name IN ($placeholders)");
    $stmtLang->execute($validLanguages);
    $langIds = $stmtLang->fetchAll(PDO::FETCH_COLUMN);
    
    $stmtLink = $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
    foreach ($langIds as $lid) {
        $stmtLink->execute([$appId, $lid]);
    }
    
    $pdo->commit();
    header("Location: index.html?success=1");
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    redirectWithError('Ошибка сохранения: ' . $e->getMessage(), $_POST);
}
?>