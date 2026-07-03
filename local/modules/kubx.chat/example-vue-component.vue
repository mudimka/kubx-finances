<template>
  <div class="manager-chat">
    <!-- Кнопка открытия чата -->
    <button 
      @click="openChat" 
      class="chat-button"
      :class="{ loading: isLoading }"
      :disabled="isLoading"
    >
      <svg class="icon" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
      </svg>
      <span>{{ buttonText }}</span>
    </button>

    <!-- Модальное окно (опционально) -->
    <Transition name="fade">
      <div v-if="showModal" class="chat-modal-overlay" @click="closeModal">
        <div class="chat-modal" @click.stop>
          <div class="chat-header">
            <div class="manager-info">
              <div class="manager-name">{{ managerName }}</div>
              <div class="manager-status">Онлайн</div>
            </div>
            <button @click="closeModal" class="close-btn">×</button>
          </div>

          <!-- iframe с чатом Bitrix24 -->
          <iframe 
            v-if="chatUrl"
            :src="chatUrl" 
            frameborder="0"
            class="chat-iframe"
          />
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const isLoading = ref(false)
const showModal = ref(false)
const chatUrl = ref('')
const managerName = ref('Менеджер')
const managerId = ref(null)

const buttonText = computed(() => {
  if (isLoading.value) return 'Загрузка...'
  return 'Чат с менеджером'
})

/**
 * Открыть чат
 * По умолчанию открывает в новой вкладке
 * Раскомментируйте showModal для открытия в модальном окне
 */
const openChat = async () => {
  isLoading.value = true

  try {
    const response = await fetch('/local/ajax/kubx.chat/chat.php?action=init')
    const data = await response.json()

    if (data.success) {
      chatUrl.value = data.chatUrl
      managerName.value = data.managerName || 'Менеджер'
      managerId.value = data.managerId

      // Вариант 1: Открыть в новой вкладке (проще)
      window.open(chatUrl.value, '_blank')

      // Вариант 2: Показать модальное окно с iframe
      // showModal.value = true
    } else {
      console.error('Chat init error:', data.error)
      alert('Не удалось открыть чат. Попробуйте позже.')
    }
  } catch (error) {
    console.error('Failed to open chat:', error)
    alert('Ошибка подключения к чату')
  } finally {
    isLoading.value = false
  }
}

const closeModal = () => {
  showModal.value = false
}
</script>

<style scoped>
.chat-button {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.chat-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
}

.chat-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.chat-button.loading {
  opacity: 0.8;
}

.chat-button .icon {
  width: 20px;
  height: 20px;
}

/* Модальное окно (если используете) */
.chat-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.chat-modal {
  width: 90%;
  max-width: 800px;
  height: 80vh;
  background: white;
  border-radius: 12px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.chat-header {
  padding: 16px 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.manager-name {
  font-weight: 600;
  font-size: 16px;
}

.manager-status {
  font-size: 12px;
  opacity: 0.9;
}

.close-btn {
  background: none;
  border: none;
  color: white;
  font-size: 32px;
  line-height: 1;
  cursor: pointer;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  transition: background 0.2s;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.1);
}

.chat-iframe {
  flex: 1;
  width: 100%;
  border: none;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Mobile */
@media (max-width: 768px) {
  .chat-modal {
    width: 100%;
    height: 100vh;
    max-width: none;
    border-radius: 0;
  }

  .chat-button {
    font-size: 14px;
    padding: 10px 20px;
  }
}
</style>

