// Система подтверждения действий
(function() {
    // Создание модального окна подтверждения
    function createConfirmModal() {
        const modal = document.createElement('div');
        modal.className = 'confirm-modal';
        modal.id = 'confirmModal';
        modal.innerHTML = `
            <div class="confirm-modal-content">
                <div class="confirm-modal-header">
                    <h3 id="confirmTitle">Подтверждение действия</h3>
                    <button class="confirm-close" onclick="closeConfirmModal()">&times;</button>
                </div>
                <div class="confirm-modal-body">
                    <p id="confirmMessage">Вы уверены, что хотите выполнить это действие?</p>
                </div>
                <div class="confirm-modal-footer">
                    <button class="btn-cancel" onclick="closeConfirmModal()">Отмена</button>
                    <button class="btn-confirm" id="confirmButton">Подтвердить</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    // Показать модальное окно
    window.showConfirmModal = function(title, message, onConfirm) {
        let modal = document.getElementById('confirmModal');
        if (!modal) {
            createConfirmModal();
            modal = document.getElementById('confirmModal');
        }

        document.getElementById('confirmTitle').textContent = title;
        document.getElementById('confirmMessage').textContent = message;
        
        const confirmBtn = document.getElementById('confirmButton');
        confirmBtn.onclick = function() {
            closeConfirmModal();
            if (onConfirm) onConfirm();
        };

        modal.style.display = 'flex';
    };

    // Закрыть модальное окно
    window.closeConfirmModal = function() {
        const modal = document.getElementById('confirmModal');
        if (modal) {
            modal.style.display = 'none';
        }
    };

    // Toast уведомления
    window.showToast = function(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <span class="toast-icon">${type === 'success' ? '✓' : type === 'error' ? '✗' : 'ℹ'}</span>
            <span class="toast-message">${message}</span>
        `;
        document.body.appendChild(toast);

        setTimeout(() => toast.classList.add('show'), 10);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    };

    // Обработка форм удаления
    document.addEventListener('DOMContentLoaded', function() {
        // Перехват всех форм удаления
        document.querySelectorAll('form[action*="delete"]').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const itemName = this.dataset.itemName || 'этот элемент';
                const itemType = this.dataset.itemType || 'элемент';
                
                showConfirmModal(
                    `Удаление ${itemType}`,
                    `Вы действительно хотите удалить "${itemName}"? Это действие нельзя отменить.`,
                    () => {
                        this.submit();
                    }
                );
            });
        });

        // Закрытие модального окна по клику вне его
        window.addEventListener('click', function(e) {
            const modal = document.getElementById('confirmModal');
            if (e.target === modal) {
                closeConfirmModal();
            }
        });

        // Закрытие по Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeConfirmModal();
            }
        });
    });
})();
