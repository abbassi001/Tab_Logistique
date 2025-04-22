/**
 * Main JavaScript file for TAB Logistique
 * Contains common functionality used across the application
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    initializeTooltips();
    
    // Initialize Bootstrap popovers
    initializePopovers();
    
    // Add active class to current nav item
    highlightActiveNavItem();
    
    // Initialize checkAll functionality for tables
    initializeCheckAllTables();
    
    // Initialize clipboard copy functionality
    initializeClipboardCopy();
    
    // Initialize hover effects
    initializeHoverEffects();
    
    // Initialize form validation
    initializeFormValidation();
});

/**
 * Initialize Bootstrap tooltips
 */
function initializeTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Initialize Bootstrap popovers
 */
function initializePopovers() {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

/**
 * Highlight the active navigation item based on current URL
 */
function highlightActiveNavItem() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link, .sidebar .nav-link');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
        }
    });
}

/**
 * Initialize "check all" functionality for tables with checkboxes
 */
function initializeCheckAllTables() {
    const checkAllBoxes = document.querySelectorAll('input[id="checkAll"]');
    checkAllBoxes.forEach(checkAllBox => {
        if (checkAllBox) {
            checkAllBox.addEventListener('change', function() {
                const tableBody = this.closest('table').querySelector('tbody');
                if (tableBody) {
                    const checkboxes = tableBody.querySelectorAll('.form-check-input');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = checkAllBox.checked;
                    });
                }
            });
        }
    });
}

/**
 * Copy text to clipboard
 * @param {string} text - The text to copy to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showToast('Texte copié dans le presse-papier', 'success');
    }, function(err) {
        console.error('Erreur lors de la copie :', err);
        showToast('Erreur lors de la copie', 'danger');
    });
}

/**
 * Initialize clipboard copy functionality
 */
function initializeClipboardCopy() {
    window.copyToClipboard = copyToClipboard;
}

/**
 * Initialize hover effects for elements with .hover-shadow class
 */
function initializeHoverEffects() {
    const hoverElements = document.querySelectorAll('.hover-shadow');
    
    hoverElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            this.classList.add('shadow');
            this.style.transform = 'translateY(-5px)';
            this.style.transition = 'transform 0.3s ease';
        });
        
        element.addEventListener('mouseleave', function() {
            this.classList.remove('shadow');
            this.style.transform = 'translateY(0)';
        });
    });
}

/**
 * Show a toast notification
 * @param {string} message - The message to display
 * @param {string} type - The type of toast (success, danger, warning, info)
 */
function showToast(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    // Create a unique ID for the toast
    const toastId = 'toast-' + Date.now();
    
    // Create toast element
    const toastHtml = `
        <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-${type} text-white">
                <strong class="me-auto">Notification</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    // Add toast to container
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    // Initialize and show the toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
    toast.show();
    
    // Remove toast from DOM after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

/**
 * Initialize Bootstrap form validation
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        });
    });
}