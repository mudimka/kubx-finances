<?php

namespace Kubx\Chat;

/**
 * Клиент для работы с REST API Bitrix24
 */
class Bitrix24Client
{
    private $webhookUrl;
    
    public function __construct(?string $webhookUrl = null)
    {
        $this->webhookUrl = $webhookUrl ?: Config::getWebhookUrl();
    }
    
    /**
     * Вызов метода REST API
     * 
     * @param string $method Метод API (например, 'im.message.add')
     * @param array $params Параметры метода
     * @return array|null Результат или null в случае ошибки
     */
    public function call(string $method, array $params = []): ?array
    {
        if (empty($this->webhookUrl)) {
            $this->log('ERROR: Webhook URL not configured');
            return null;
        }
        
        $url = rtrim($this->webhookUrl, '/') . '/' . $method . '.json';
        
        $this->log("Calling API: {$method}", $params);
        
        // Используем cURL вместо HttpClient для совместимости
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            $this->log("cURL Error: {$error}");
            return null;
        }
        
        if ($httpCode != 200) {
            $this->log("API Error: HTTP {$httpCode}");
            return null;
        }
        
        $result = json_decode($response, true);
        
        if (isset($result['error'])) {
            $this->log("API Error: " . ($result['error_description'] ?? $result['error']));
            return null;
        }
        
        $this->log("API Success", $result);
        
        return $result;
    }
    
    /**
     * Отправить сообщение в мессенджер
     * 
     * @param int $dialogId ID диалога (USER_ID для личного чата)
     * @param string $message Текст сообщения
     * @return int|null ID сообщения или null
     */
    public function sendMessage(int $dialogId, string $message): ?int
    {
        $result = $this->call('im.message.add', [
            'DIALOG_ID' => $dialogId,
            'MESSAGE' => $message,
        ]);
        
        return $result['result'] ?? null;
    }
    
    /**
     * Получить историю сообщений
     * 
     * @param int $dialogId ID диалога
     * @param int $lastId ID последнего сообщения (для пагинации)
     * @param int $limit Количество сообщений
     * @return array|null
     */
    public function getMessages(int $dialogId, int $lastId = 0, int $limit = 20): ?array
    {
        $result = $this->call('im.dialog.messages.get', [
            'DIALOG_ID' => $dialogId,
            'LAST_ID' => $lastId,
            'LIMIT' => $limit,
        ]);
        
        return $result['result'] ?? null;
    }
    
    /**
     * Получить информацию о пользователе
     * 
     * @param int $userId ID пользователя в Bitrix24
     * @return array|null
     */
    public function getUser(int $userId): ?array
    {
        $result = $this->call('user.get', [
            'ID' => $userId,
        ]);
        
        return $result['result'][0] ?? null;
    }
    
    /**
     * Проверить подключение к Bitrix24
     * 
     * @return bool
     */
    public function testConnection(): bool
    {
        $result = $this->call('app.info');
        return $result !== null;
    }
    
    /**
     * Логирование
     */
    private function log(string $message, array $data = []): void
    {
        if (!Config::isLoggingEnabled()) {
            return;
        }
        
        $logDir = $_SERVER['DOCUMENT_ROOT'] . '/local/logs/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . 'kubx_chat.log';
        $logMessage = date('Y-m-d H:i:s') . ' | ' . $message;
        
        if (!empty($data)) {
            $logMessage .= ' | ' . json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        
        file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND);
    }
}

