/**
 * Custom JavaScript
 */

// ========================================
// Wait for DOM ready
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing...');

    // ========================================
    // Sidebar Toggle - Let CoreUI handle it
    // ========================================
    // CoreUI will handle sidebar toggle via data-coreui-toggle="unfoldable"
    // We just need to persist the state
    const sidebar = document.querySelector('.sidebar');
    const SIDEBAR_KEY = 'sidebar-unfoldable';

    // Load saved state
    if (localStorage.getItem(SIDEBAR_KEY) === 'true' && sidebar) {
        sidebar.classList.add('sidebar-narrow-unfoldable');
    }

    // Save state when sidebar is toggled
    if (sidebar) {
        // Watch for class changes
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    const isUnfoldable = sidebar.classList.contains('sidebar-narrow-unfoldable');
                    localStorage.setItem(SIDEBAR_KEY, isUnfoldable);
                }
            });
        });
        observer.observe(sidebar, { attributes: true, attributeFilter: ['class'] });
    }

    // ========================================
    // Sidebar Dropdown Toggle
    // ========================================
    document.addEventListener('click', function(e) {
        var toggle = e.target.closest('.nav-group-toggle');
        if (toggle) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            var navGroup = toggle.closest('.nav-group');
            if (navGroup) {
                navGroup.classList.toggle('show');
                console.log('Toggled nav-group:', navGroup.classList.contains('show'));
            }
            return false;
        }
    }, true);

    // ========================================
    // Mobile Sidebar Toggle - Sử dụng CoreUI Pro API
    // ========================================
    // CoreUI Pro sẽ tự động xử lý backdrop và mobile behavior
    // Chỉ cần đảm bảo sidebar được khởi tạo đúng cách
    if (typeof coreui !== 'undefined' && coreui.Sidebar && sidebar) {
        // Khởi tạo Sidebar instance nếu chưa có
        const sidebarInstance = coreui.Sidebar.getOrCreateInstance(sidebar);
        
        // Header toggler sẽ sử dụng CoreUI API
        const sidebarTogglerMobile = document.querySelector('.header-toggler');
        if (sidebarTogglerMobile) {
            sidebarTogglerMobile.addEventListener('click', function(e) {
                e.preventDefault();
                sidebarInstance.toggle();
            });
        }
    }

    // ========================================
    // Initialize Toasts
    // ========================================
    if (typeof coreui !== 'undefined' && coreui.Toast) {
        document.querySelectorAll('.toast').forEach(function(toastEl) {
            var toast = new coreui.Toast(toastEl);
            toast.show();
        });
    }

    // ========================================
    // Delete Modal
    // ========================================
    var deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.coreui.modal', function(event) {
            var button = event.relatedTarget;
            if (button) {
                var form = deleteModal.querySelector('#deleteForm');
                var itemName = deleteModal.querySelector('#deleteItemName');

                if (form && button.dataset.url) {
                    form.action = button.dataset.url;
                }
                if (itemName && button.dataset.name) {
                    itemName.textContent = button.dataset.name;
                }
            }
        });
    }
});

// ========================================
// Select2 Component - Sử dụng cấu hình chung
// ========================================
// Select2 đã được khởi tạo tự động bởi jquery-config.js
// Các hàm helper đã có sẵn trong window.Select2 và window.Select2Config
//
// Sử dụng:
// - window.Select2.init() - Khởi tạo lại tất cả Select2
// - window.Select2.selectAll(selector) - Chọn tất cả
// - window.Select2.deselectAll(selector) - Bỏ chọn tất cả
// - window.Select2.toggleAll(selector) - Toggle chọn tất cả
// - window.Select2Config.init($element, customConfig) - Khởi tạo một element với config tùy chỉnh
