<?php
header('Content-Type: application/json; charset=utf-8');
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/classes/UserService.php';

$userService = new UserService();

if (!$userService->isAuthenticated()) {
    http_response_code(401);
    exit;
}

$action = $_GET['action'] ?? '';
$file = $_GET['file'] ?? '';

if ($action === 'thumbnail' && $file) {
    // Проверяем, имеет ли пользователь доступ к файлу
    $filePath = UPLOADS_DIR . str_replace(UPLOADS_URL, '', $file);
    
    if (!file_exists($filePath)) {
        http_response_code(404);
        exit;
    }
    
    // Создаем миниатюру
    $imageInfo = getimagesize($filePath);
    if (!$imageInfo) {
        http_response_code(400);
        exit;
    }
    
    $maxWidth = 300;
    $maxHeight = 300;
    
    list($width, $height) = $imageInfo;
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    
    $newWidth = $width * $ratio;
    $newHeight = $height * $ratio;
    
    // Создаем миниатюру в зависимости от типа
    switch ($imageInfo['mime']) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg($filePath);
            break;
        case 'image/png':
            $source = imagecreatefrompng($filePath);
            break;
        case 'image/gif':
            $source = imagecreatefromgif($filePath);
            break;
        case 'image/webp':
            $source = imagecreatefromwebp($filePath);
            break;
        default:
            http_response_code(400);
            exit;
    }
    
    $thumb = imagecreatetruecolor($newWidth, $newHeight);
    
    // Сохраняем прозрачность для PNG и GIF
    if ($imageInfo['mime'] === 'image/png' || $imageInfo['mime'] === 'image/gif') {
        imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
    }
    
    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Выводим изображение
    header('Content-Type: ' . $imageInfo['mime']);
    header('Cache-Control: public, max-age=86400');
    
    switch ($imageInfo['mime']) {
        case 'image/jpeg':
            imagejpeg($thumb, null, 85);
            break;
        case 'image/png':
            imagepng($thumb, null, 9);
            break;
        case 'image/gif':
            imagegif($thumb);
            break;
        case 'image/webp':
            imagewebp($thumb, null, 85);
            break;
    }
    
    imagedestroy($source);
    imagedestroy($thumb);
}
?>