<?php
/**
 * AJAX endpoint для работы с чатом
 * URL: /local/ajax/kubx.chat/chat.php
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Kubx\Chat\ChatManager;

header('Content-Type: application/json');

global $USER;

if (!$USER->IsAuthorized()) {
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit;
}

if (!Loader::includeModule('kubx.chat')) {
    echo json_encode(['success' => false, 'error' => 'Module not installed']);
    exit;
}

$request = Application::getInstance()->getContext()->getRequest();
$action = $request->get('action') ?: $request->getPost('action');

$chatManager = new ChatManager();
$userId = $USER->GetID();

$response = [];

try {
    switch ($action) {
        case 'init':
            // Инициализация чата
            $response = $chatManager->initChat($userId);
            break;
            
        case 'send':
            // Отправить сообщение
            $message = $request->get('message') ?: $request->getPost('message');
            
            if (empty($message)) {
                $response = ['success' => false, 'error' => 'Message is empty'];
                break;
            }
            
            $response = $chatManager->sendMessage($userId, $message);
            break;
            
        case 'history':
            // Получить историю
            $lastId = (int)($request->get('lastId') ?: $request->getPost('lastId'));
            $limit = (int)($request->get('limit') ?: $request->getPost('limit')) ?: 20;
            
            $response = $chatManager->getMessages($userId, $lastId, $limit);
            break;
            
        case 'test':
            // Тестовый endpoint
            $response = [
                'success' => true,
                'message' => 'Chat API is working',
                'userId' => $userId,
                'connection' => $chatManager->testConnection(),
            ];
            break;
            
        default:
            $response = ['success' => false, 'error' => 'Unknown action'];
    }
} catch (\Exception $e) {
    $response = [
        'success' => false,
        'error' => $e->getMessage(),
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);

