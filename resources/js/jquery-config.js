/**
 * jQuery Common Configuration
 * File cấu hình chung cho jQuery để tránh lặp lại code
 */

// Đợi jQuery và DOM sẵn sàng
(function($) {
    'use strict';

    // Kiểm tra jQuery đã load chưa
    if (typeof $ === 'undefined') {
        console.warn('jQuery chưa được load');
        return;
    }

    // ========================================
    // CẤU HÌNH CHUNG CHO SELECT2
    // ========================================
    const Select2Config = {
        // Cấu hình mặc định cho Select2
        defaultConfig: {
            theme: 'bootstrap-5',
            width: '100%',
            language: {
                noResults: function() { return "Không tìm thấy kết quả"; },
                searching: function() { return "Đang tìm..."; },
                removeAllItems: function() { return "Xóa tất cả"; },
                removeItem: function() { return "Xóa"; }
            }
        },

        // Khởi tạo Select2 cho một element
        init: function($element, customConfig = {}) {
            if (!$element || !$element.length) {
                console.warn('Element không tồn tại');
                return;
            }

            // Skip nếu đã được khởi tạo
            if ($element.hasClass('select2-hidden-accessible')) {
                return;
            }

            const useUserTemplate = $element.data('user-template') === 'true' || $element.data('user-template') === true;

            const config = {
                ...this.defaultConfig,
                allowClear: !$element.prop('required'),
                placeholder: $element.data('placeholder') || 'Chọn...',
                closeOnSelect: !$element.prop('multiple'),
                ...customConfig
            };

            // Nếu dùng user template
            if (useUserTemplate) {
                config.templateResult = this.formatUserOption;
                config.templateSelection = this.formatUserSelection;
            }

            $element.select2(config);

            // Bind events
            $element.on('change', function() {
                Select2Config.updateCounter($(this));
                Select2Config.updateToggleButton($(this));
            });

            // Initial update
            this.updateCounter($element);
            this.updateToggleButton($element);

            return $element;
        },

        // Khởi tạo tất cả Select2 components
        initAll: function(selector = '.select2-component') {
            const self = this;
            $(selector).each(function() {
                self.init($(this));
            });
        },

        // Cập nhật counter
        updateCounter: function($select) {
            const wrapper = $select.closest('.select2-wrapper');
            const counter = wrapper.find('.select2-counter');

            if (counter.length > 0) {
                const count = $select.select2('data').length;
                counter.find('.count').text(count);
            }
        },

        // Cập nhật nút Toggle All
        updateToggleButton: function($select) {
            const id = $select.attr('id');
            const $btn = $(`.select2-toggle-all[data-target="${id}"]`);

            if ($btn.length === 0) return;

            const totalOptions = $select.find('option').length;
            const selectedCount = $select.select2('data').length;
            const allSelected = totalOptions === selectedCount && totalOptions > 0;

            if (allSelected) {
                $btn.removeClass('btn-outline-secondary').addClass('btn-outline-danger');
                $btn.find('i').removeClass('bi-check-all').addClass('bi-x-lg');
                $btn.find('span').text('Bỏ chọn tất cả');
            } else {
                $btn.removeClass('btn-outline-danger').addClass('btn-outline-secondary');
                $btn.find('i').removeClass('bi-x-lg').addClass('bi-check-all');
                $btn.find('span').text('Chọn tất cả');
            }
        },

        // Format user option với avatar
        formatUserOption: function(user) {
            if (!user.id) return user.text;

            const $option = $(user.element);
            const avatar = $option.data('avatar') || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.text)}&background=6366f1&color=fff&size=36`;
            const meta = $option.data('meta') || '';

            return $(`
                <div class="select2-user-option">
                    <img src="${avatar}" alt="">
                    <div class="user-info">
                        <span class="user-name">${user.text}</span>
                        ${meta ? `<span class="user-meta">${meta}</span>` : ''}
                    </div>
                </div>
            `);
        },

        // Format user selection
        formatUserSelection: function(user) {
            return user.text;
        },

        // Chọn tất cả
        selectAll: function(selector) {
            const $el = $(selector);
            $el.find('option').prop('selected', true);
            $el.trigger('change');
        },

        // Bỏ chọn tất cả
        deselectAll: function(selector) {
            $(selector).val(null).trigger('change');
        },

        // Toggle chọn tất cả
        toggleAll: function(selector) {
            const $el = $(selector);
            const total = $el.find('option').length;
            const selected = $el.select2('data').length;
            if (total === selected) {
                this.deselectAll(selector);
            } else {
                this.selectAll(selector);
            }
        }
    };

    // ========================================
    // KHỞI TẠO KHI DOM SẴN SÀNG
    // ========================================
    $(document).ready(function() {
        // Khởi tạo Select2 cho tất cả elements có class 'select2-component'
        if (typeof $.fn.select2 !== 'undefined') {
            Select2Config.initAll();
        } else {
            console.warn('Select2 chưa được load');
        }

        // Bind sự kiện cho nút Toggle All
        $(document).on('click', '.select2-toggle-all', function() {
            const targetId = $(this).data('target');
            const $select = $('#' + targetId);

            if ($select.length === 0) return;

            const totalOptions = $select.find('option').length;
            const selectedCount = $select.select2('data').length;
            const allSelected = totalOptions === selectedCount;

            if (allSelected) {
                Select2Config.deselectAll('#' + targetId);
            } else {
                Select2Config.selectAll('#' + targetId);
            }
        });
    });

    // ========================================
    // EXPORT RA WINDOW ĐỂ SỬ DỤNG GLOBAL
    // ========================================
    window.Select2Config = Select2Config;
    window.Select2 = {
        init: Select2Config.initAll.bind(Select2Config),
        selectAll: Select2Config.selectAll.bind(Select2Config),
        deselectAll: Select2Config.deselectAll.bind(Select2Config),
        toggleAll: Select2Config.toggleAll.bind(Select2Config)
    };

})(jQuery);

