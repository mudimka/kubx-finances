# 🔧 Исправление подключения к Bitrix24

## Проблема
API возвращает `"connection": false`

## Причина
Настройки из `.settings.php` не были сохранены в БД при установке модуля.

---

## ✅ Решение

### Шаг 1: Закоммитить изменения

```bash
cd /Users/ivangrigorev/Documents/repositories/kubx-back

git add local/modules/kubx.chat/
git commit -m "fix: add settings initialization in InstallDB + fix script"
git push
```

### Шаг 2: Деплой на сервер

```bash
# На сервере
cd /path/to/bitrix
git pull
```

### Шаг 3: Запустить скрипт исправления

```bash
# На сервере
php local/modules/kubx.chat/fix-settings.php
```

**Ожидаемый результат:**

```
╔══════════════════════════════════════════════════════════╗
║       🔧 ИСПРАВЛЕНИЕ НАСТРОЕК KUBX.CHAT                  ║
╚══════════════════════════════════════════════════════════╝

📝 Шаг 1: Сохранение настроек в БД...
✅ Настройки сохранены!

📋 Шаг 2: Проверка сохраненных настроек...
  Webhook URL: https://crm.kubx.tech/rest/1/278ta5gyhwri5tag/
  Manager ID: 1
  Logging: Enabled

🔗 Шаг 3: Проверка подключения к Bitrix24...
HTTP Code: 200
✅ Подключение к Bitrix24 успешно!

📤 Шаг 4: Тест отправки сообщения...
✅ Сообщение отправлено! ID: 12345

✅ ГОТОВО! Настройки исправлены.
```

### Шаг 4: Проверить API

```bash
curl "https://demo.kubx.tech/local/ajax/kubx.chat/chat.php?action=test"
```

**Должно вернуть:**
```json
{
  "success": true,
  "message": "Chat API is working",
  "userId": "87",
  "connection": true
}
```

✅ **Теперь `"connection": true`!**

---

## 🎉 Готово!

Теперь можно:

1. **Использовать API:**
   ```javascript
   fetch('/local/ajax/kubx.chat/chat.php?action=init')
   ```

2. **Проверить в Bitrix24:**
   https://crm.kubx.tech/online/
   → Должно прийти тестовое сообщение

3. **Настроить в админке:**
   /bitrix/admin/ → Настройки → Настройки модулей → KUBX Chat Manager

---

## 📝 Что было исправлено

1. ✅ `install/index.php` - добавлено сохранение настроек в `InstallDB()`
2. ✅ `fix-settings.php` - скрипт для быстрого исправления
3. ✅ `options.php` - страница настроек в админке

---

## 🔄 Альтернативный способ

Если не хотите запускать `fix-settings.php`, можно переустановить модуль:

```bash
php -r "
require 'bitrix/modules/main/include/prolog_before.php';
\$m = CModule::CreateModuleObject('kubx.chat');
\$m->DoUninstall();
\$m->DoInstall();
echo 'Модуль переустановлен\n';
"
```

При новой установке настройки автоматически сохранятся в БД.

