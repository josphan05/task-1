// CoreUI JavaScript
import * as coreui from '@coreui/coreui';

// Make CoreUI available globally
window.coreui = coreui;

// Initialize CoreUI components when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-coreui-toggle="tooltip"]');
    tooltipTriggerList.forEach(el => new coreui.Tooltip(el));

    // Initialize all popovers
    const popoverTriggerList = document.querySelectorAll('[data-coreui-toggle="popover"]');
    popoverTriggerList.forEach(el => new coreui.Popover(el));

    // Initialize all dropdowns (header dropdowns only)
    const dropdownTriggerList = document.querySelectorAll('.header [data-coreui-toggle="dropdown"]');
    dropdownTriggerList.forEach(el => new coreui.Dropdown(el));

    const sidebar = document.querySelector('#sidebar');
    const sidebarTogglerMobile = document.querySelector('.header-toggler');

    // Handle sidebar nav group toggle using event delegation
    if (sidebar) {
        sidebar.addEventListener('click', function(e) {
            const toggle = e.target.closest('.nav-group-toggle');
            if (toggle) {
                e.preventDefault();
                e.stopPropagation();
                
                const navGroup = toggle.closest('.nav-group');
                if (navGroup) {
                    // Check current state and toggle
                    const isOpen = navGroup.classList.contains('show');
                    if (isOpen) {
                        navGroup.classList.remove('show');
                    } else {
                        navGroup.classList.add('show');
                    }
                }
            }
        }, true); // Use capture phase
    }

    // Sidebar toggle functionality (unfoldable - thu nhỏ, hover mới mở)
    const sidebarToggler = document.querySelector('.sidebar-toggler');
    if (sidebarToggler) {
        sidebarToggler.addEventListener('click', function(e) {
            e.preventDefault();
            document.body.classList.toggle('sidebar-unfoldable');
            
            // Lưu trạng thái vào localStorage
            if (document.body.classList.contains('sidebar-unfoldable')) {
                localStorage.setItem('sidebar-unfoldable', 'true');
            } else {
                localStorage.removeItem('sidebar-unfoldable');
            }
        });
        
        // Khôi phục trạng thái từ localStorage
        if (localStorage.getItem('sidebar-unfoldable') === 'true') {
            document.body.classList.add('sidebar-unfoldable');
        }
    }

    // Mobile sidebar toggle
    if (sidebarTogglerMobile && sidebar) {
        sidebarTogglerMobile.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }

    // Close sidebar on mobile when clicking outside
    document.addEventListener('click', function(event) {
        const isClickInsideSidebar = sidebar?.contains(event.target);
        const isClickOnToggler = sidebarTogglerMobile?.contains(event.target);
        
        if (!isClickInsideSidebar && !isClickOnToggler && sidebar?.classList.contains('show') && window.innerWidth < 992) {
            sidebar.classList.remove('show');
        }
    });
});

// Export for use in other modules
export { coreui };

