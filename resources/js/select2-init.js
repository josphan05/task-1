/**
 * Select2 Component Auto-Init
 * Sử dụng cấu hình chung từ jquery-config.js
 *
 * File này giữ lại để tương thích với các module ES6
 * Nếu sử dụng jQuery từ CDN, Select2 sẽ tự động khởi tạo qua jquery-config.js
 */

// Nếu jQuery đã được load và có Select2Config
if (typeof window.Select2Config !== 'undefined') {
    // Sử dụng cấu hình chung
    // Select2 đã được khởi tạo tự động bởi jquery-config.js
    console.log('Select2 sử dụng cấu hình chung từ jquery-config.js');
} else if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
    // Fallback: Nếu không có cấu hình chung, khởi tạo trực tiếp
    jQuery(document).ready(function($) {
        if (window.Select2Config) {
            window.Select2Config.initAll();
        }
    });
}

// Export functions để tương thích với code cũ
// Các hàm này sẽ sử dụng Select2Config nếu có
window.Select2 = window.Select2 || {
    init: function(selector) {
        if (window.Select2Config) {
            window.Select2Config.initAll(selector);
        }
    },
    selectAll: function(selector) {
        if (window.Select2Config) {
            window.Select2Config.selectAll(selector);
        }
    },
    deselectAll: function(selector) {
        if (window.Select2Config) {
            window.Select2Config.deselectAll(selector);
        }
    },
    toggleAll: function(selector) {
        if (window.Select2Config) {
            window.Select2Config.toggleAll(selector);
        }
    }
};
