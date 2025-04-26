/*
// Fix for sidebar overlay issues (Commented out as core scripts.js should handle this)

document.addEventListener('DOMContentLoaded', function() {
    // Remove any duplicate overlay elements
    // const overlays = document.querySelectorAll('.nk-sidebar-overlay');
    
    // If there are multiple overlays, remove all but the first one
    // if (overlays.length > 1) {
    //     for (let i = 1; i < overlays.length; i++) {
    //         overlays[i].parentNode.removeChild(overlays[i]);
    //     }
    // }
    
    // If no overlay exists, create one
    // if (overlays.length === 0) {
    //     const overlay = document.createElement('div');
    //     overlay.className = 'nk-sidebar-overlay';
    //     overlay.setAttribute('data-target', 'sidebarMenu');
    //     document.body.appendChild(overlay);
    // }
    
    // Add click event to the overlay to close sidebar
    // const overlay = document.querySelector('.nk-sidebar-overlay');
    // if (overlay) {
    //     overlay.addEventListener('click', function() {
    //         document.body.classList.remove('nk-sidebar-active'); // This class might be incorrect, core uses .nav-shown
    //         const toggles = document.querySelectorAll('.nk-nav-toggle');
    //         toggles.forEach(function(toggle) {
    //             toggle.classList.remove('toggle-active'); // Core script handles toggle state
    //         });
    //     });
    // }
});
*/