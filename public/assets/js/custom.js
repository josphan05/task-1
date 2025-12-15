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
// Select2 Component (jQuery ready)
// ========================================
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($) {
        console.log('jQuery loaded, initializing Select2...');
        
        initAllSelect2();
        bindToggleAllButtons();
    });
}

function initAllSelect2() {
    if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 === 'undefined') {
        console.warn('Select2 not loaded');
        return;
    }
    
    var $ = jQuery;
    
    $('.select2-component').each(function() {
        var $el = $(this);
        
        // Skip if already initialized
        if ($el.hasClass('select2-hidden-accessible')) {
            return;
        }
        
        var useUserTemplate = $el.data('user-template') === 'true' || $el.data('user-template') === true;

        var config = {
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

        if (useUserTemplate) {
            config.templateResult = formatUserOption;
            config.templateSelection = formatUserSelection;
        }

        $el.select2(config);

        $el.on('change', function() {
            updateSelectCounter($(this));
            updateToggleButton($(this));
        });

        updateSelectCounter($el);
        console.log('Select2 initialized for:', $el.attr('id'));
    });
}

function bindToggleAllButtons() {
    if (typeof jQuery === 'undefined') return;
    var $ = jQuery;
    
    $(document).off('click', '.select2-toggle-all').on('click', '.select2-toggle-all', function() {
        var targetId = $(this).data('target');
        var $select = $('#' + targetId);
        
        var totalOptions = $select.find('option').length;
        var selectedCount = $select.select2('data').length;
        var allSelected = totalOptions === selectedCount;

        if (allSelected) {
            $select.val(null).trigger('change');
        } else {
            $select.find('option').prop('selected', true);
            $select.trigger('change');
        }
    });
}

function updateToggleButton($select) {
    var $ = jQuery;
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
}

function updateSelectCounter($select) {
    var wrapper = $select.closest('.select2-wrapper');
    var counter = wrapper.find('.select2-counter');
    
    if (counter.length > 0) {
        counter.find('.count').text($select.select2('data').length);
    }
}

function formatUserOption(user) {
    if (!user.id) return user.text;
    
    var $option = jQuery(user.element);
    var avatar = $option.data('avatar') || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.text) + '&background=6366f1&color=fff&size=36';
    var meta = $option.data('meta') || '';
    
    return jQuery(
        '<div class="select2-user-option">' +
            '<img src="' + avatar + '" alt="">' +
            '<div class="user-info">' +
                '<span class="user-name">' + user.text + '</span>' +
                (meta ? '<span class="user-meta">' + meta + '</span>' : '') +
            '</div>' +
        '</div>'
    );
}

function formatUserSelection(user) {
    return user.text;
}

// Global Select2 helpers
window.Select2 = {
    init: initAllSelect2,
    selectAll: function(selector) {
        var $el = jQuery(selector);
        $el.find('option').prop('selected', true);
        $el.trigger('change');
    },
    deselectAll: function(selector) {
        jQuery(selector).val(null).trigger('change');
    },
    toggleAll: function(selector) {
        var $el = jQuery(selector);
        var total = $el.find('option').length;
        var selected = $el.select2('data').length;
        if (total === selected) {
            this.deselectAll(selector);
        } else {
            this.selectAll(selector);
        }
    }
};
