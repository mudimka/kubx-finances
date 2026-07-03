# Быстрая установка KUBX Chat Manager

## ⚡ 3 шага до запуска

### Шаг 1: Установить модуль

```bash
# Зайдите в админку
https://your-site.ru/bitrix/admin/

# Marketplace → Установленные решения
# Найдите "KUBX Chat Manager"
# Нажмите "Установить"
```

### Шаг 2: Проверить установку

```bash
# На сервере запустите тест
cd /path/to/bitrix
php local/modules/kubx.chat/test-chat.php
```

Должно быть:
```
✅ Модуль kubx.chat подключен
✅ Подключение успешно!
✅ Сообщение отправлено!
```

### Шаг 3: Проверить в Bitrix24

1. Откройте https://crm.kubx.tech
2. Перейдите в мессенджер
3. Должно прийти тестовое сообщение

## ✅ Готово!

Теперь можно:
- Использовать AJAX API: `/local/ajax/kubx.chat/chat.php`
- Добавлять Vue компонент в личный кабинет
- Назначать менеджеров пользователям

## 🔧 Дополнительно

### Назначить менеджера пользователю

```php
// В админке или через код
$user = new CUser;
$user->Update(USER_ID, ['UF_MANAGER_ID' => 1]);
```

### Изменить менеджера по умолчанию

Отредактируйте `local/modules/kubx.chat/.settings.php`:

```php
'default_manager_id' => [
    'value' => 2, // Новый ID
],
```

## 📚 Документация

- README.md - Полная документация
- test-chat.php - Тестовый скрипт
- /local/logs/kubx_chat.log - Логи

