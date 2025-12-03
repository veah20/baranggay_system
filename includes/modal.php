<?php
/**
 * Modal Component
 * Reusable modal templates for the application
 */
?>

<!-- Generic Modal -->
<div class="modal fade" id="genericModal" tabindex="-1" aria-labelledby="genericModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="genericModalLabel">Modal Title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="genericModalBody">
                Modal content goes here
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="genericModalBtn">Save changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="confirmModalLabel">
                    <i class="fas fa-exclamation-triangle me-2" aria-hidden="true"></i>Confirm Action
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="confirmModalBody">
                Are you sure you want to proceed?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmModalBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="successModalLabel">
                    <i class="fas fa-check-circle me-2" aria-hidden="true"></i>Success
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="successModalBody">
                Operation completed successfully!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="errorModalLabel">
                    <i class="fas fa-times-circle me-2" aria-hidden="true"></i>Error
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="errorModalBody">
                An error occurred. Please try again.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Info Modal -->
<div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="infoModalLabel">
                    <i class="fas fa-info-circle me-2" aria-hidden="true"></i>Information
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="infoModalBody">
                Information message goes here.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-info text-white" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Large Content Modal -->
<div class="modal fade" id="largeModal" tabindex="-1" aria-labelledby="largeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="largeModalLabel">Detailed View</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="largeModalBody">
                Large content goes here
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Modal custom styling */
    .modal-content {
        border-radius: 10px;
        border: none;
        box-shadow: 0 5px 25px rgba(0,0,0,0.15);
    }
    
    .modal-header {
        border-bottom: 1px solid #e9ecef;
        border-radius: 10px 10px 0 0;
    }
    
    .modal-header h5 {
        margin: 0;
        font-weight: 600;
    }
    
    .modal-body {
        padding: 20px;
        color: #333;
        line-height: 1.6;
    }
    
    .modal-footer {
        border-top: 1px solid #e9ecef;
        padding: 15px 20px;
    }
    
    .modal-footer .btn {
        padding: 8px 16px;
        font-weight: 500;
    }
    
    .modal-header.bg-success,
    .modal-header.bg-danger,
    .modal-header.bg-info,
    .modal-header.bg-warning {
        border-radius: 10px 10px 0 0;
    }
    
    /* Animation */
    .modal.fade .modal-dialog {
        transition: transform 0.3s ease-out, opacity 0.3s ease-out;
    }
    
    .modal.show .modal-dialog {
        transform: scale(1);
    }
    
    .modal.fade:not(.show) .modal-dialog {
        transform: scale(0.95);
    }
    
    /* Accessibility */
    .modal-dialog-centered {
        display: flex;
        align-items: center;
        min-height: calc(100% - 3.5rem);
    }
    
    .modal:focus {
        outline: none;
    }
</style>

<script>
/**
 * Modal Helper Functions
 * Easy-to-use functions for displaying modals
 */

// Show generic modal
function showModal(title, message, callback) {
    document.getElementById('genericModalLabel').textContent = title;
    document.getElementById('genericModalBody').textContent = message;
    
    if (callback) {
        document.getElementById('genericModalBtn').onclick = function() {
            callback();
            bootstrap.Modal.getInstance(document.getElementById('genericModal')).hide();
        };
    }
    
    new bootstrap.Modal(document.getElementById('genericModal')).show();
}

// Show confirmation modal
function showConfirmModal(message, callback) {
    document.getElementById('confirmModalBody').textContent = message;
    document.getElementById('confirmModalBtn').onclick = callback;
    new bootstrap.Modal(document.getElementById('confirmModal')).show();
}

// Show success modal
function showSuccessModal(message, callback) {
    document.getElementById('successModalBody').innerHTML = `
        <div class="text-center">
            <i class="fas fa-check-circle text-success" style="font-size: 48px; margin-bottom: 15px;"></i>
            <p>${message}</p>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('successModal'));
    modal.show();
    
    if (callback) {
        document.getElementById('successModal').addEventListener('hidden.bs.modal', callback, { once: true });
    }
}

// Show error modal
function showErrorModal(message, callback) {
    document.getElementById('errorModalBody').innerHTML = `
        <div class="text-center">
            <i class="fas fa-times-circle text-danger" style="font-size: 48px; margin-bottom: 15px;"></i>
            <p>${message}</p>
        </div>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('errorModal'));
    modal.show();
    
    if (callback) {
        document.getElementById('errorModal').addEventListener('hidden.bs.modal', callback, { once: true });
    }
}

// Show info modal
function showInfoModal(message, callback) {
    document.getElementById('infoModalBody').textContent = message;
    
    const modal = new bootstrap.Modal(document.getElementById('infoModal'));
    modal.show();
    
    if (callback) {
        document.getElementById('infoModal').addEventListener('hidden.bs.modal', callback, { once: true });
    }
}

// Show large modal
function showLargeModal(title, content) {
    document.getElementById('largeModalLabel').textContent = title;
    document.getElementById('largeModalBody').innerHTML = content;
    new bootstrap.Modal(document.getElementById('largeModal')).show();
}

// Close all modals
function closeAllModals() {
    const modals = document.querySelectorAll('.modal.show');
    modals.forEach(modal => {
        bootstrap.Modal.getInstance(modal).hide();
    });
}
</script>
