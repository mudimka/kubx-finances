# KUBX Chat Manager

Модуль для интеграции чата с менеджерами через Bitrix24.

## 🎯 Возможности

- ✅ Чат пользователя с личным менеджером через Bitrix24
- ✅ REST API интеграция с Bitrix24 (облако или коробка)
- ✅ Отправка сообщений в мессенджер Bitrix24
- ✅ Получение истории сообщений
- ✅ Автоматическое определение менеджера пользователя
- ✅ Логирование для отладки

## 📦 Установка

### 1. Настройка Bitrix24

Вебхук уже настроен: `https://crm.kubx.tech/rest/1/278ta5gyhwri5tag/`

Проверьте права вебхука:
- ✅ `im` - Мессенджер
- ✅ `user` - Пользователи

### 2. Установка модуля

```bash
# Через админку Bitrix
https://your-site.ru/bitrix/admin/
→ Marketplace → Установленные решения
→ KUBX Chat Manager → Установить
```

Или через консоль:

```bash
cd /path/to/bitrix
php -r "require 'bitrix/modules/main/include/prolog_before.php'; \$m = CModule::CreateModuleObject('kubx.chat'); \$m->DoInstall();"
```

### 3. Проверка работы

```bash
# Запустите тест
php local/modules/kubx.chat/test-chat.php
```

Ожидаемый результат:
```
✅ Модуль kubx.chat подключен
✅ Подключение успешно!
✅ Сообщение отправлено! ID: 12345
```

## 🚀 Использование

### AJAX API

#### Инициализация чата

```javascript
fetch('/local/ajax/kubx.chat/chat.php?action=init')
  .then(r => r.json())
  .then(data => {
    console.log(data);
    // {
    //   success: true,
    //   managerId: 1,
    //   managerName: "Иван Иванов",
    //   chatUrl: "https://crm.kubx.tech/online/?IM_DIALOG=U1"
    // }
  });
```

#### Отправка сообщения

```javascript
const formData = new FormData();
formData.append('action', 'send');
formData.append('message', 'Здравствуйте! У меня вопрос.');

fetch('/local/ajax/kubx.chat/chat.php', {
  method: 'POST',
  body: formData
})
  .then(r => r.json())
  .then(data => {
    console.log(data);
    // { success: true, messageId: 12345 }
  });
```

#### Тест подключения

```javascript
fetch('/local/ajax/kubx.chat/chat.php?action=test')
  .then(r => r.json())
  .then(data => console.log(data));
```

### PHP API

```php
use Kubx\Chat\ChatManager;

$chatManager = new ChatManager();

// Инициализация чата
$result = $chatManager->initChat($USER->GetID());

// Отправка сообщения
$result = $chatManager->sendMessage($USER->GetID(), 'Привет!');

// Проверка подключения
$isConnected = $chatManager->testConnection();
```

## 📁 Структура

```
local/modules/kubx.chat/
├── lib/
│   ├── Config.php           # Конфигурация
│   ├── Bitrix24Client.php   # REST API клиент
│   └── ChatManager.php      # Менеджер чатов
├── ajax/
│   └── chat.php            # AJAX endpoint (копируется в /local/ajax/)
├── install/
│   ├── index.php           # Установщик
│   └── version.php         # Версия
├── test-chat.php           # Тестовый скрипт
├── include.php             # Автозагрузка
└── .settings.php           # Настройки
```

## 🔧 Настройка менеджеров

### Вариант 1: Один менеджер для всех

Отредактируйте `.settings.php`:

```php
'default_manager_id' => [
    'value' => 1, // ID менеджера в Bitrix24
],
```

### Вариант 2: Разные менеджеры для пользователей

Добавьте пользовательское поле:

```php
// В админке: Настройки → Пользователи → Пользовательские поля
// Код: UF_MANAGER_ID
// Тип: Число

// Назначьте менеджера пользователю
$user = new CUser;
$user->Update(123, ['UF_MANAGER_ID' => 1]);
```

## 🔍 Логи

Логи сохраняются в `/local/logs/kubx_chat.log`

```bash
tail -f local/logs/kubx_chat.log
```

## 🐛 Отладка

```php
// Проверка конфигурации
use Kubx\Chat\Config;
echo Config::getWebhookUrl();

// Проверка подключения
use Kubx\Chat\Bitrix24Client;
$client = new Bitrix24Client();
var_dump($client->testConnection());

// Тест отправки сообщения
$messageId = $client->sendMessage(1, 'Тест');
var_dump($messageId);
```

## 🎨 Vue компонент (следующий шаг)

После установки модуля можно добавить Vue компонент в личный кабинет:

```vue
<template>
  <button @click="openChat">Чат с менеджером</button>
</template>

<script setup>
const openChat = async () => {
  const response = await fetch('/local/ajax/kubx.chat/chat.php?action=init')
  const data = await response.json()
  
  if (data.success) {
    // Открываем чат
    window.open(data.chatUrl, '_blank')
  }
}
</script>
```

## 📞 Поддержка

- Webhook: `https://crm.kubx.tech/rest/1/278ta5gyhwri5tag/`
- Документация Bitrix24 API: https://dev.1c-bitrix.ru/rest_help/
- Email: support@kubx.tech

