/**
 * Colis module JavaScript functionality for TAB Logistique
 * Handles colis-specific interactions and UI components
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize status update modal
    initializeStatusUpdateModal();
    
    // Initialize search and filters
    initializeColisSearch();
    
    // Initialize tracking timeline
    initializeTrackingTimeline();
    
    // Initialize photo gallery
    initializePhotoGallery();
    
    // Initialize document list
    initializeDocumentList();
    
    // Initialize filter toggle
    initializeFilterToggle();
    
    // Initialize bulk actions
    initializeBulkActions();
});

/**
 * Initialize status update modal
 */
function initializeStatusUpdateModal() {
    const statusModal = document.getElementById('statutModal');
    
    if (statusModal) {
        // Pre-populate date with current time when modal is opened
        statusModal.addEventListener('show.bs.modal', function() {
            const dateStatutInput = document.getElementById('date_statut');
            if (dateStatutInput && !dateStatutInput.value) {
                const now = new Date();
                // Format date as YYYY-MM-DDThh:mm
                dateStatutInput.value = now.toISOString().slice(0, 16);
            }
        });
        
        // Handle form submission
        const statusForm = statusModal.querySelector('form');
        if (statusForm) {
            statusForm.addEventListener('submit', function(event) {
                if (!statusForm.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                statusForm.classList.add('was-validated');
            });
        }
    }
    
    // Initialize bulk status update modal
    const bulkStatusModal = document.getElementById('bulkStatusModal');
    if (bulkStatusModal) {
        const bulkStatusForm = document.getElementById('bulk-status-form');
        
        if (bulkStatusForm) {
            bulkStatusForm.addEventListener('submit', function(event) {
                const selectedCheckboxes = document.querySelectorAll('tbody .form-check-input:checked');
                
                if (selectedCheckboxes.length === 0) {
                    event.preventDefault();
                    alert('Veuillez sélectionner au moins un colis.');
                    return;
                }
                
                // Add selected IDs to form
                selectedCheckboxes.forEach(checkbox => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'colis_ids[]';
                    input.value = checkbox.value;
                    bulkStatusForm.appendChild(input);
                });
            });
        }
    }
}

/**
 * Initialize search and filters for colis list
 */
function initializeColisSearch() {
    const searchForm = document.querySelector('form[action*="colis_index"]');
    
    if (searchForm) {
        // Handle form reset
        const resetButton = searchForm.querySelector('button[type="reset"]');
        if (resetButton) {
            resetButton.addEventListener('click', function() {
                // Clear all inputs
                searchForm.querySelectorAll('input, select').forEach(field => {
                    field.value = '';
                });
                
                // Submit the form with cleared values
                setTimeout(() => searchForm.submit(), 50);
            });
        }
        
        // Handle column visibility toggle
        const columnCheckboxes = document.querySelectorAll('.dropdown-item input[type="checkbox"]');
        columnCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const columnIndex = this.dataset.column;
                const isVisible = this.checked;
                
                // Show/hide the corresponding column in the table
                if (columnIndex) {
                    const cells = document.querySelectorAll(`td:nth-child(${columnIndex}), th:nth-child(${columnIndex})`);
                    cells.forEach(cell => {
                        cell.style.display = isVisible ? '' : 'none';
                    });
                }
            });
        });
    }
}

/**
 * Initialize tracking timeline functionality
 */
function initializeTrackingTimeline() {
    const timeline = document.querySelector('.tracking-timeline');
    
    if (timeline) {
        // Scroll to the most recent status
        const activeItem = timeline.querySelector('.tracking-item.active');
        if (activeItem) {
            setTimeout(() => {
                activeItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 500);
        }
    }
}

/**
 * Initialize photo gallery with lightbox functionality
 */
function initializePhotoGallery() {
    const photoLinks = document.querySelectorAll('a[href*="uploads/photos/"]');
    
    photoLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Only if not using a lightbox library
            if (!window.SimpleLightbox) {
                e.preventDefault();
                
                // Create a simple lightbox
                const lightbox = document.createElement('div');
                lightbox.className = 'modal fade';
                lightbox.id = 'photoLightbox';
                lightbox.setAttribute('tabindex', '-1');
                
                lightbox.innerHTML = `
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Photo</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img src="${this.href}" class="img-fluid" alt="Photo agrandie">
                            </div>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(lightbox);
                
                // Show the modal
                const modal = new bootstrap.Modal(lightbox);
                modal.show();
                
                // Remove the modal from DOM when hidden
                lightbox.addEventListener('hidden.bs.modal', function() {
                    lightbox.remove();
                });
            }
        });
    });
}

/**
 * Initialize document list functionality
 */
function initializeDocumentList() {
    const documentLinks = document.querySelectorAll('a[href*="uploads/documents/"]');
    
    documentLinks.forEach(link => {
        // Add tracking for document clicks
        link.addEventListener('click', function() {
            // Track document access (could be implemented with AJAX)
            console.log('Document accessed:', this.href);
        });
    });
    
    // Handle document delete buttons
    const deleteDocumentButtons = document.querySelectorAll('.document-delete-btn');
    deleteDocumentButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce document ?')) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Initialize filter toggle functionality
 */
function initializeFilterToggle() {
    const filterToggle = document.querySelector('[data-bs-target="#collapseFilters"]');
    
    if (filterToggle) {
        // Check URL params to see if filters are active
        const urlParams = new URLSearchParams(window.location.search);
        const hasFilters = urlParams.toString() !== '';
        
        // If filters are active, expand the filter section
        if (hasFilters) {
            const collapseFilter = document.getElementById('collapseFilters');
            if (collapseFilter) {
                const bsCollapse = new bootstrap.Collapse(collapseFilter, { toggle: true });
                
                // Update the toggle icon
                filterToggle.querySelector('i').classList.remove('fa-chevron-down');
                filterToggle.querySelector('i').classList.add('fa-chevron-up');
            }
            
            // Highlight the filter button
            filterToggle.classList.add('btn-primary');
            filterToggle.classList.remove('btn-link');
        }
        
        // Toggle icon when filter is expanded/collapsed
        filterToggle.addEventListener('click', function() {
            const icon = this.querySelector('i');
            if (icon.classList.contains('fa-chevron-down')) {
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        });
    }
}

/**
 * Initialize bulk actions functionality
 */
function initializeBulkActions() {
    // Export buttons
    const exportButtons = document.querySelectorAll('button[data-export-format]');
    exportButtons.forEach(button => {
        button.addEventListener('click', function() {
            const format = this.dataset.exportFormat;
            const selectedIds = getSelectedColisIds();
            
            if (selectedIds.length === 0) {
                alert('Veuillez sélectionner au moins un colis à exporter.');
                return;
            }
            
            // Create a form to submit the export request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/export/' + format;
            
            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                form.appendChild(csrfInput);
            }
            
            // Add selected IDs
            selectedIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'colis_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            // Submit the form
            document.body.appendChild(form);
            form.submit();
        });
    });
    
    // Print button
    const printButton = document.querySelector('button[data-action="print"]');
    if (printButton) {
        printButton.addEventListener('click', function() {
            const selectedIds = getSelectedColisIds();
            
            if (selectedIds.length === 0) {
                alert('Veuillez sélectionner au moins un colis à imprimer.');
                return;
            }
            
            // Store selected IDs in sessionStorage
            sessionStorage.setItem('print_colis_ids', JSON.stringify(selectedIds));
            
            // Open print page in new window
            window.open('/colis/print', '_blank');
        });
    }
}

/**
 * Get IDs of selected colis in the table
 * @returns {Array} Array of selected IDs
 */
function getSelectedColisIds() {
    const selectedCheckboxes = document.querySelectorAll('tbody .form-check-input:checked');
    return Array.from(selectedCheckboxes).map(checkbox => checkbox.value);
}