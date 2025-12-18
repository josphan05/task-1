/**
 * jQuery Common Configuration
 * File cấu hình chung cho jQuery Validation và Select2 để tránh lặp lại code
 *
 * CÁCH SỬ DỤNG JQUERY VALIDATION:
 *
 * 1. Sử dụng cấu hình mặc định:
 *    window.ValidationConfig.init($form, {
 *        rules: { name: 'required', email: 'required' },
 *        messages: { name: 'Vui lòng nhập tên' }
 *    });
 *
 * 2. Sử dụng với errorPlacement tùy chỉnh:
 *    window.ValidationConfig.init($form, {
 *        rules: {...},
 *        messages: {...},
 *        customErrorPlacement: function(error, element) {
 *            // Logic tùy chỉnh
 *        }
 *    });
 *
 * CÁCH SỬ DỤNG SELECT2:
 *
 * 1. Select2 tự động khởi tạo cho tất cả elements có class 'select2-component'
 *
 * 2. Sử dụng các hàm helper:
 *    - window.Select2.init(selector) - Khởi tạo lại Select2 cho selector
 *    - window.Select2.selectAll(selector) - Chọn tất cả options
 *    - window.Select2.deselectAll(selector) - Bỏ chọn tất cả
 *    - window.Select2.toggleAll(selector) - Toggle chọn/bỏ chọn tất cả
 */

// Đợi jQuery sẵn sàng
(function() {
    'use strict';

    // Kiểm tra jQuery đã load chưa
    if (typeof jQuery === 'undefined') {
        console.warn('jQuery chưa được load');
        return;
    }

    var $ = jQuery;

    // ========================================
    // CẤU HÌNH CHUNG CHO JQUERY VALIDATION
    // ========================================
    var ValidationConfig = {
        // Cấu hình mặc định cho Validation
        defaultConfig: {
            onfocusout: function(element) {
                this.element(element);
            },
            onkeyup: false,
            errorElement: 'div',
            errorClass: 'invalid-feedback',
            highlight: function(element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element) {
                $(element).removeClass('is-invalid');
            },
            errorPlacement: function(error, element) {
                // Xử lý radio/checkbox
                if (element.attr('type') === 'radio' || element.attr('type') === 'checkbox') {
                    error.appendTo(element.closest('.mb-3, .mb-4'));
                }
                // Xử lý Select2
                else if (element.attr('id') === 'user_ids' || element.hasClass('form-multi-select') || element.hasClass('select2-component')) {
                    var $wrapper = element.closest('.select2-wrapper');
                    if ($wrapper.length) {
                        error.insertAfter($wrapper);
                    } else {
                        error.insertAfter(element.closest('.select2-container').parent() || element);
                    }
                }
                // Xử lý password field với wrapper
                else if (element.attr('id') === 'password') {
                    var $passwordWrapper = element.closest('.form-password');
                    if ($passwordWrapper.length) {
                        error.insertAfter($passwordWrapper);
                    } else {
                        error.insertAfter(element);
                    }
                }
                // Mặc định
                else {
                    error.insertAfter(element);
                }
            }
        },

        // Khởi tạo validation cho form
        init: function($form, customConfig) {
            customConfig = customConfig || {};

            if (!$form || !$form.length) {
                console.warn('Form không tồn tại');
                return;
            }

            if (typeof $.fn.validate === 'undefined') {
                console.warn('jQuery Validation chưa được load');
                return;
            }

            // Merge cấu hình
            var config = $.extend(true, {}, this.defaultConfig, customConfig);

            // Nếu có customErrorPlacement, sử dụng nó thay vì default
            if (customConfig.customErrorPlacement) {
                config.errorPlacement = customConfig.customErrorPlacement;
                delete config.customErrorPlacement;
            }

            // Khởi tạo validation
            $form.validate(config);

            return $form;
        }
    };

    // ========================================
    // CẤU HÌNH CHUNG CHO SELECT2
    // ========================================
    var Select2Config = {
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
        init: function($element, customConfig) {
            customConfig = customConfig || {};

            if (!$element || !$element.length) {
                console.warn('Element không tồn tại');
                return;
            }

            // Skip nếu đã được khởi tạo
            if ($element.hasClass('select2-hidden-accessible')) {
                return;
            }

            var useUserTemplate = $element.data('user-template') === 'true' || $element.data('user-template') === true;

            var config = $.extend({}, this.defaultConfig, {
                allowClear: !$element.prop('required'),
                placeholder: $element.data('placeholder') || 'Chọn...',
                closeOnSelect: !$element.prop('multiple')
            }, customConfig);

            // Nếu dùng user template
            if (useUserTemplate) {
                config.templateResult = this.formatUserOption;
                config.templateSelection = this.formatUserSelection;
            }

            $element.select2(config);

            // Bind events
            var self = this;
            $element.on('change', function() {
                self.updateCounter($(this));
                self.updateToggleButton($(this));
            });

            // Initial update
            this.updateCounter($element);
            this.updateToggleButton($element);

            return $element;
        },

        // Khởi tạo tất cả Select2 components
        initAll: function(selector) {
            selector = selector || '.select2-component';
            var self = this;
            $(selector).each(function() {
                self.init($(this));
            });
        },

        // Cập nhật counter
        updateCounter: function($select) {
            var wrapper = $select.closest('.select2-wrapper');
            var counter = wrapper.find('.select2-counter');

            if (counter.length > 0) {
                var count = $select.select2('data').length;
                counter.find('.count').text(count);
            }
        },

        // Cập nhật nút Toggle All
        updateToggleButton: function($select) {
            var id = $select.attr('id');
            var $btn = $('.select2-toggle-all[data-target="' + id + '"]');

            if ($btn.length === 0) return;

            var totalOptions = $select.find('option').length;
            var selectedCount = $select.select2('data').length;
            var allSelected = totalOptions === selectedCount && totalOptions > 0;

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

            var $option = $(user.element);
            var avatar = $option.data('avatar') || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.text) + '&background=6366f1&color=fff&size=36';
            var meta = $option.data('meta') || '';

            return $(
                '<div class="select2-user-option">' +
                    '<img src="' + avatar + '" alt="">' +
                    '<div class="user-info">' +
                        '<span class="user-name">' + user.text + '</span>' +
                        (meta ? '<span class="user-meta">' + meta + '</span>' : '') +
                    '</div>' +
                '</div>'
            );
        },

        // Format user selection
        formatUserSelection: function(user) {
            return user.text;
        },

        // Chọn tất cả
        selectAll: function(selector) {
            var $el = $(selector);
            $el.find('option').prop('selected', true);
            $el.trigger('change');
        },

        // Bỏ chọn tất cả
        deselectAll: function(selector) {
            $(selector).val(null).trigger('change');
        },

        // Toggle chọn tất cả
        toggleAll: function(selector) {
            var $el = $(selector);
            var total = $el.find('option').length;
            var selected = $el.select2('data').length;
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
            var targetId = $(this).data('target');
            var $select = $('#' + targetId);

            if ($select.length === 0) return;

            var totalOptions = $select.find('option').length;
            var selectedCount = $select.select2('data').length;
            var allSelected = totalOptions === selectedCount;

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
    // jQuery Validation Config
    window.ValidationConfig = ValidationConfig;

    // Select2 Config
    window.Select2Config = Select2Config;
    window.Select2 = {
        init: function(selector) {
            Select2Config.initAll(selector);
        },
        selectAll: function(selector) {
            Select2Config.selectAll(selector);
        },
        deselectAll: function(selector) {
            Select2Config.deselectAll(selector);
        },
        toggleAll: function(selector) {
            Select2Config.toggleAll(selector);
        }
    };

})();

