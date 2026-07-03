# 🚀 Быстрый старт KUBX Chat Manager

## ✅ Модуль создан!

Вебхук уже настроен: `https://crm.kubx.tech/rest/1/278ta5gyhwri5tag/`

## 📦 Что дальше?

### 1. Закоммитьте в Git

```bash
cd /Users/ivangrigorev/Documents/repositories/kubx-back

git add local/modules/kubx.chat/
git commit -m "feat: add kubx.chat module for Bitrix24 integration"
git push
```

### 2. Деплой на сервер

```bash
# На сервере
cd /path/to/bitrix
git pull

# Или через rsync
rsync -avz local/modules/kubx.chat/ user@server:/path/to/bitrix/local/modules/kubx.chat/
```

### 3. Установите модуль

**Вариант А: Через админку**
```
https://your-site.ru/bitrix/admin/
→ Marketplace → Установленные решения
→ KUBX Chat Manager → Установить
```

**Вариант Б: Через консоль**
```bash
cd /path/to/bitrix
php -r "require 'bitrix/modules/main/include/prolog_before.php'; \$m = CModule::CreateModuleObject('kubx.chat'); \$m->DoInstall();"
```

### 4. Проверьте работу

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

### 5. Проверьте в Bitrix24

1. Откройте https://crm.kubx.tech
2. Зайдите в мессенджер
3. Должно прийти тестовое сообщение от клиента

## 🧪 Тест через AJAX

```bash
# Тест подключения
curl "https://your-site.ru/local/ajax/kubx.chat/chat.php?action=test"

# Ожидаемый результат:
# {"success":true,"message":"Chat API is working","userId":1,"connection":true}
```

## 📋 Структура модуля

```
local/modules/kubx.chat/
├── lib/
│   ├── Config.php           ✅ Конфигурация
│   ├── Bitrix24Client.php   ✅ REST API клиент
│   └── ChatManager.php      ✅ Менеджер чатов
├── ajax/
│   └── chat.php            ✅ AJAX endpoint
├── install/
│   ├── index.php           ✅ Установщик
│   ├── step.php            ✅ Шаг установки
│   ├── unstep.php          ✅ Шаг удаления
│   └── version.php         ✅ Версия 1.0.0
├── test-chat.php           ✅ Тестовый скрипт
├── README.md               ✅ Документация
├── INSTALL.md              ✅ Инструкция установки
└── .settings.php           ✅ Настройки с вашим webhook
```

## 🎯 Следующие шаги

### 1. Назначить менеджера пользователю (опционально)

```php
// Создайте UF поле в админке:
// Настройки → Пользователи → Пользовательские поля
// Код: UF_MANAGER_ID
// Тип: Число

// Назначьте менеджера
$user = new CUser;
$user->Update(USER_ID, ['UF_MANAGER_ID' => 1]);
```

### 2. Создать Vue компонент для фронтенда

```vue
<template>
  <button @click="openChat" class="chat-button">
    💬 Чат с менеджером
  </button>
</template>

<script setup>
const openChat = async () => {
  const response = await fetch('/local/ajax/kubx.chat/chat.php?action=init')
  const data = await response.json()
  
  if (data.success) {
    // Вариант 1: Открыть в новой вкладке
    window.open(data.chatUrl, '_blank')
    
    // Вариант 2: Показать iframe
    // showChatModal(data.chatUrl)
  }
}
</script>
```

### 3. Добавить компонент в личный кабинет

```vue
// В вашем layouts/cabinet.vue или components/
<ManagerChat />
```

## 📚 API Reference

### AJAX Endpoints

#### `GET /local/ajax/kubx.chat/chat.php?action=init`
Инициализация чата с менеджером

**Response:**
```json
{
  "success": true,
  "managerId": 1,
  "dialogId": 1,
  "managerName": "Иван Иванов",
  "chatUrl": "https://crm.kubx.tech/online/?IM_DIALOG=U1"
}
```

#### `POST /local/ajax/kubx.chat/chat.php`
Отправить сообщение

**Parameters:**
- `action=send`
- `message` - текст сообщения

**Response:**
```json
{
  "success": true,
  "messageId": 12345
}
```

#### `GET /local/ajax/kubx.chat/chat.php?action=test`
Тест подключения

**Response:**
```json
{
  "success": true,
  "message": "Chat API is working",
  "userId": 1,
  "connection": true
}
```

## 🔍 Отладка

### Логи
```bash
tail -f /path/to/bitrix/local/logs/kubx_chat.log
```

### Тест подключения к Bitrix24
```php
use Kubx\Chat\Bitrix24Client;

$client = new Bitrix24Client();
var_dump($client->testConnection()); // должно быть true
```

### Тест отправки сообщения
```php
use Kubx\Chat\ChatManager;

$chatManager = new ChatManager();
$result = $chatManager->sendMessage(1, 'Тест');
print_r($result);
```

## ⚙️ Настройки

Файл: `local/modules/kubx.chat/.settings.php`

```php
return [
    'bitrix24_webhook_url' => [
        'value' => 'https://crm.kubx.tech/rest/1/278ta5gyhwri5tag/',
    ],
    'default_manager_id' => [
        'value' => 1, // ID менеджера по умолчанию
    ],
    'enable_logging' => [
        'value' => true, // Включить логирование
    ],
];
```

## 🎉 Готово!

Модуль полностью готов к работе. Осталось только:
1. Закоммитить
2. Задеплоить на сервер
3. Установить через админку
4. Протестировать
5. Добавить Vue компонент в личный кабинет

---

**Документация:**
- README.md - Полная документация
- INSTALL.md - Установка
- test-chat.php - Тестирование

**Поддержка:** support@kubx.tech

