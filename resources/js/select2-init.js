/**
 * Select2 Component Auto-Init
 * Tự động khởi tạo Select2 cho tất cả elements có class 'select2-component'
 */

$(document).ready(function() {
    // Auto init tất cả select2 components
    initAllSelect2();

    // Bind toggle all buttons
    bindToggleAllButtons();
});

/**
 * Init tất cả Select2 components
 */
function initAllSelect2() {
    $('.select2-component').each(function() {
        const $el = $(this);
        const useUserTemplate = $el.data('user-template') === 'true' || $el.data('user-template') === true;

        const config = {
            theme: 'bootstrap-5',
            width: '100%',
            allowClear: !$el.prop('required'),
            placeholder: $el.data('placeholder') || 'Chọn...',
            closeOnSelect: !$el.prop('multiple'),
            language: {
                noResults: function() { return "Không tìm thấy kết quả"; },
                searching: function() { return "Đang tìm..."; },
                removeAllItems: function() { return "Xóa tất cả"; },
                removeItem: function() { return "Xóa"; }
            }
        };

        // Nếu dùng user template
        if (useUserTemplate) {
            config.templateResult = formatUserOption;
            config.templateSelection = formatUserSelection;
        }

        $el.select2(config);

        // Update counter nếu có
        $el.on('change', function() {
            updateSelectCounter($(this));
            updateToggleButton($(this));
        });

        // Initial update
        updateSelectCounter($el);
    });
}

/**
 * Bind sự kiện cho nút Toggle All
 */
function bindToggleAllButtons() {
    $(document).on('click', '.select2-toggle-all', function() {
        const targetId = $(this).data('target');
        const $select = $('#' + targetId);
        const $btn = $(this);
        
        const totalOptions = $select.find('option').length;
        const selectedCount = $select.select2('data').length;
        const allSelected = totalOptions === selectedCount;

        if (allSelected) {
            // Bỏ chọn tất cả
            $select.val(null).trigger('change');
        } else {
            // Chọn tất cả
            $select.find('option').prop('selected', true);
            $select.trigger('change');
        }
    });
}

/**
 * Cập nhật trạng thái nút Toggle
 */
function updateToggleButton($select) {
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
}

/**
 * Cập nhật counter cho select
 */
function updateSelectCounter($select) {
    const wrapper = $select.closest('.select2-wrapper');
    const counter = wrapper.find('.select2-counter');
    
    if (counter.length > 0) {
        const count = $select.select2('data').length;
        counter.find('.count').text(count);
    }
}

/**
 * Format user option với avatar
 */
function formatUserOption(user) {
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
}

/**
 * Format user selection
 */
function formatUserSelection(user) {
    return user.text;
}

// Export functions để có thể gọi từ bên ngoài
window.Select2 = {
    init: initAllSelect2,
    selectAll: function(selector) {
        const $el = $(selector);
        $el.find('option').prop('selected', true);
        $el.trigger('change');
    },
    deselectAll: function(selector) {
        $(selector).val(null).trigger('change');
    },
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
