<?php

namespace Kubx\Chat;

use Bitrix\Main\Config\Option;

class Config
{
    const MODULE_ID = 'kubx.chat';
    
    /**
     * Получить URL вебхука Bitrix24
     */
    public static function getWebhookUrl(): string
    {
        return Option::get(self::MODULE_ID, 'bitrix24_webhook_url', '');
    }
    
    /**
     * Получить ID менеджера по умолчанию
     */
    public static function getDefaultManagerId(): int
    {
        return (int)Option::get(self::MODULE_ID, 'default_manager_id', 1);
    }
    
    /**
     * Включено ли логирование
     */
    public static function isLoggingEnabled(): bool
    {
        return (bool)Option::get(self::MODULE_ID, 'enable_logging', true);
    }
    
    /**
     * Установить URL вебхука
     */
    public static function setWebhookUrl(string $url): void
    {
        Option::set(self::MODULE_ID, 'bitrix24_webhook_url', $url);
    }
    
    /**
     * Установить ID менеджера по умолчанию
     */
    public static function setDefaultManagerId(int $id): void
    {
        Option::set(self::MODULE_ID, 'default_manager_id', $id);
    }
}

