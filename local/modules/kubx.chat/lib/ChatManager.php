<?php

namespace Kubx\Chat;

use Bitrix\Main\Loader;

/**
 * Менеджер чатов
 * Управляет диалогами между пользователями сайта и менеджерами Bitrix24
 */
class ChatManager
{
    private $bitrix24Client;
    
    public function __construct()
    {
        $this->bitrix24Client = new Bitrix24Client();
    }
    
    /**
     * Получить ID менеджера для пользователя
     * 
     * @param int $userId ID пользователя на сайте
     * @return int ID менеджера в Bitrix24
     */
    public function getUserManagerId(int $userId): int
    {
        Loader::includeModule('main');
        
        // Пытаемся получить из пользовательского поля
        $rsUser = \CUser::GetByID($userId);
        $user = $rsUser->Fetch();
        
        if (!empty($user['UF_MANAGER_ID'])) {
            return (int)$user['UF_MANAGER_ID'];
        }
        
        // Если не задан, возвращаем менеджера по умолчанию
        return Config::getDefaultManagerId();
    }
    
    /**
     * Инициализировать чат с менеджером
     * 
     * @param int $userId ID пользователя на сайте
     * @return array Данные для фронтенда
     */
    public function initChat(int $userId): array
    {
        $managerId = $this->getUserManagerId($userId);
        
        if (!$managerId) {
            return [
                'success' => false,
                'error' => 'Manager not assigned',
            ];
        }
        
        // Получаем информацию о менеджере из Bitrix24
        $managerInfo = $this->bitrix24Client->getUser($managerId);
        
        return [
            'success' => true,
            'managerId' => $managerId,
            'dialogId' => $managerId, // Для личных чатов dialogId = userId
            'managerName' => $managerInfo ? ($managerInfo['NAME'] . ' ' . $managerInfo['LAST_NAME']) : 'Менеджер',
            'managerPhoto' => $managerInfo['PERSONAL_PHOTO'] ?? '',
            'chatUrl' => $this->getChatUrl($managerId),
        ];
    }
    
    /**
     * Отправить сообщение менеджеру
     * 
     * @param int $userId ID пользователя на сайте
     * @param string $message Текст сообщения
     * @return array Результат
     */
    public function sendMessage(int $userId, string $message): array
    {
        $managerId = $this->getUserManagerId($userId);
        
        if (!$managerId) {
            return [
                'success' => false,
                'error' => 'Manager not assigned',
            ];
        }
        
        // Формируем сообщение с информацией о клиенте
        Loader::includeModule('main');
        $rsUser = \CUser::GetByID($userId);
        $user = $rsUser->Fetch();
        
        $userName = trim($user['NAME'] . ' ' . $user['LAST_NAME']) ?: 'Клиент #' . $userId;
        $fullMessage = "💬 *{$userName}*:\n{$message}";
        
        // Отправляем через API Bitrix24
        $messageId = $this->bitrix24Client->sendMessage($managerId, $fullMessage);
        
        if ($messageId) {
            return [
                'success' => true,
                'messageId' => $messageId,
            ];
        }
        
        return [
            'success' => false,
            'error' => 'Failed to send message',
        ];
    }
    
    /**
     * Получить URL для открытия чата в Bitrix24
     * 
     * @param int $managerId ID менеджера
     * @return string URL
     */
    public function getChatUrl(int $managerId): string
    {
        $webhookUrl = Config::getWebhookUrl();
        $domain = parse_url($webhookUrl, PHP_URL_HOST);
        $scheme = parse_url($webhookUrl, PHP_URL_SCHEME);
        
        return "{$scheme}://{$domain}/online/?IM_DIALOG=U{$managerId}";
    }
    
    /**
     * Получить историю сообщений
     * 
     * @param int $userId ID пользователя
     * @param int $lastId ID последнего сообщения
     * @param int $limit Лимит
     * @return array
     */
    public function getMessages(int $userId, int $lastId = 0, int $limit = 20): array
    {
        $managerId = $this->getUserManagerId($userId);
        
        if (!$managerId) {
            return [
                'success' => false,
                'error' => 'Manager not assigned',
            ];
        }
        
        $messages = $this->bitrix24Client->getMessages($managerId, $lastId, $limit);
        
        if ($messages === null) {
            return [
                'success' => false,
                'error' => 'Failed to load messages',
            ];
        }
        
        return [
            'success' => true,
            'messages' => $messages,
        ];
    }
    
    /**
     * Проверить подключение к Bitrix24
     * 
     * @return bool
     */
    public function testConnection(): bool
    {
        return $this->bitrix24Client->testConnection();
    }
}

