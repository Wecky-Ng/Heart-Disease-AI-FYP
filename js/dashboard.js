/**
 * Dashboard functionality for Heart Disease Prediction
 */

// Initialize dashboard components
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sidebar toggle functionality
    initSidebarToggle();
    
    // Initialize dropdown menus
    initDropdowns();
    
    // Initialize any other dashboard components
    initDashboardComponents();
});

/**
 * Initialize sidebar toggle functionality
 */
function initSidebarToggle() {
    // Wait for sidebar elements to be loaded
    setTimeout(function() {
        // Get all sidebar toggle buttons
        const sidebarToggles = document.querySelectorAll('[data-target="sidebarMenu"]');
        
        // Add click event to each toggle button
        sidebarToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Toggle sidebar visibility
                const sidebar = document.querySelector('.nk-sidebar');
                if (sidebar) {
                    sidebar.classList.toggle('toggle-active');
                    document.body.classList.toggle('sidebar-active');
                }
            });
        });
        
        // Close sidebar when clicking outside
        document.addEventListener('click', function(e) {
            const sidebar = document.querySelector('.nk-sidebar');
            const toggles = document.querySelectorAll('[data-target="sidebarMenu"]');
            
            // Check if click is outside sidebar and toggle buttons
            let clickedOutside = true;
            if (sidebar && sidebar.contains(e.target)) {
                clickedOutside = false;
            }
            
            toggles.forEach(function(toggle) {
                if (toggle.contains(e.target)) {
                    clickedOutside = false;
                }
            });
            
            // Close sidebar if clicked outside
            if (clickedOutside && sidebar && sidebar.classList.contains('toggle-active')) {
                sidebar.classList.remove('toggle-active');
                document.body.classList.remove('sidebar-active');
            }
        });
    }, 500); // Wait for components to load
}

/**
 * Initialize dropdown menus
 */
function initDropdowns() {
    // Wait for dropdown elements to be loaded
    setTimeout(function() {
        // Get all dropdown toggles
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
        
        // Add click event to each dropdown toggle
        dropdownToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Toggle dropdown menu
                const parent = this.closest('.dropdown');
                if (parent) {
                    parent.classList.toggle('show');
                    const menu = parent.querySelector('.dropdown-menu');
                    if (menu) {
                        menu.classList.toggle('show');
                    }
                }
            });
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            const dropdowns = document.querySelectorAll('.dropdown.show');
            
            dropdowns.forEach(function(dropdown) {
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.remove('show');
                    const menu = dropdown.querySelector('.dropdown-menu');
                    if (menu) {
                        menu.classList.remove('show');
                    }
                }
            });
        });
    }, 500); // Wait for components to load
}

/**
 * Initialize other dashboard components
 */
function initDashboardComponents() {
    // Initialize submenu toggles
    setTimeout(function() {
        const submenuToggles = document.querySelectorAll('.nk-menu-toggle');
        
        submenuToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                
                const parent = this.closest('.has-sub');
                if (parent) {
                    parent.classList.toggle('active');
                    const submenu = parent.querySelector('.nk-menu-sub');
                    if (submenu) {
                        if (submenu.style.maxHeight) {
                            submenu.style.maxHeight = null;
                        } else {
                            submenu.style.maxHeight = submenu.scrollHeight + "px";
                        }
                    }
                }
            });
        });
    }, 500); // Wait for components to load
}