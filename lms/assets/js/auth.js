/**
 * LMS JavaScript Functions
 * Authentication, form validation, and general functionality
 */

// Global variables
let csrfToken = '';
let isLoading = false;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeComponents();
    setupEventListeners();
    loadCSRFToken();
});

/**
 * Initialize all components
 */
function initializeComponents() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Initialize file uploads
    initializeFileUploads();

    // Initialize form validations
    initializeFormValidation();

    // Initialize auto-hide alerts
    initializeAlerts();
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Form submissions
    document.addEventListener('submit', function(e) {
        if (e.target.matches('form[data-ajax]')) {
            e.preventDefault();
            handleAjaxForm(e.target);
        }
    });

    // Confirm actions
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-confirm]')) {
            if (!confirm(e.target.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        }
    });

    // Delete confirmations
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-delete]')) {
            const message = e.target.getAttribute('data-delete') || 'Are you sure you want to delete this item?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        }
    });

    // Auto-save forms
    document.addEventListener('input', function(e) {
        if (e.target.matches('[data-auto-save]')) {
            debounce(() => autoSaveForm(e.target), 1000)();
        }
    });

    // Search functionality
    document.addEventListener('input', function(e) {
        if (e.target.matches('[data-search]')) {
            debounce(() => performSearch(e.target), 300)();
        }
    });

    // Mobile menu toggle
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-toggle="sidebar"]')) {
            toggleSidebar();
        }
    });
}

/**
 * Load CSRF token
 */
function loadCSRFToken() {
    const tokenField = document.querySelector('input[name="csrf_token"]');
    if (tokenField) {
        csrfToken = tokenField.value;
    }
}

/**
 * Show loading state
 */
function showLoading(element) {
    if (element) {
        element.classList.add('loading');
        const spinner = document.createElement('span');
        spinner.className = 'spinner-border spinner-border-sm ms-2';
        spinner.setAttribute('role', 'status');
        spinner.setAttribute('aria-hidden', 'true');
        
        const originalText = element.innerHTML;
        element.setAttribute('data-original-text', originalText);
        element.innerHTML = originalText + ' ' + spinner.outerHTML;
        element.disabled = true;
    }
}

/**
 * Hide loading state
 */
function hideLoading(element) {
    if (element) {
        element.classList.remove('loading');
        const originalText = element.getAttribute('data-original-text');
        if (originalText) {
            element.innerHTML = originalText;
            element.removeAttribute('data-original-text');
        }
        element.disabled = false;
    }
}

/**
 * Handle AJAX form submission
 */
function handleAjaxForm(form) {
    if (isLoading) return;

    const formData = new FormData(form);
    const submitButton = form.querySelector('[type="submit"]');
    
    showLoading(submitButton);
    isLoading = true;

    fetch(form.action, {
        method: form.method || 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1500);
            }
        } else {
            showAlert(data.message || 'An error occurred', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An unexpected error occurred', 'danger');
    })
    .finally(() => {
        hideLoading(submitButton);
        isLoading = false;
    });
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info', duration = 5000) {
    const alertContainer = getOrCreateAlertContainer();
    
    const alertId = 'alert-' + Date.now();
    const alertHTML = `
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    alertContainer.insertAdjacentHTML('beforeend', alertHTML);
    
    // Auto-hide after duration
    setTimeout(() => {
        const alert = document.getElementById(alertId);
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, duration);
}

/**
 * Get or create alert container
 */
function getOrCreateAlertContainer() {
    let container = document.getElementById('alert-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'alert-container';
        container.className = 'position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
    return container;
}

/**
 * Initialize file uploads
 */
function initializeFileUploads() {
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            handleFilePreview(this);
        });
    });

    // Drag and drop functionality
    const dropZones = document.querySelectorAll('.file-upload-area');
    dropZones.forEach(zone => {
        zone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        
        zone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });
        
        zone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            const input = this.querySelector('input[type="file"]');
            if (input && files.length > 0) {
                input.files = files;
                handleFilePreview(input);
            }
        });
    });
}

/**
 * Handle file preview
 */
function handleFilePreview(input) {
    const preview = document.getElementById(input.getAttribute('data-preview'));
    if (!preview) return;

    const file = input.files[0];
    if (!file) {
        preview.innerHTML = '';
        return;
    }

    // File type validation
    const allowedTypes = input.getAttribute('accept')?.split(',') || [];
    if (allowedTypes.length > 0 && !allowedTypes.includes(file.type)) {
        showAlert('Invalid file type. Please select a valid file.', 'danger');
        input.value = '';
        return;
    }

    // File size validation
    const maxSize = parseInt(input.getAttribute('data-max-size')) || 10485760; // 10MB default
    if (file.size > maxSize) {
        showAlert('File size too large. Please select a smaller file.', 'danger');
        input.value = '';
        return;
    }

    // Generate preview
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                <p class="mt-2 small">${file.name} (${formatFileSize(file.size)})</p>
            `;
        };
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = `
            <i class="fas fa-file fa-3x text-muted mb-2"></i>
            <p class="small">${file.name} (${formatFileSize(file.size)})</p>
        `;
    }
}

/**
 * Format file size
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}

/**
 * Initialize auto-hide alerts
 */
function initializeAlerts() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
}

/**
 * Toggle sidebar (mobile)
 */
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('show');
    }
}

/**
 * Auto-save form data
 */
function autoSaveForm(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    
    // Store in localStorage
    localStorage.setItem('autosave_' + form.id, JSON.stringify(data));
    
    showAlert('Form auto-saved', 'info', 2000);
}

/**
 * Restore auto-saved form data
 */
function restoreAutoSavedForm(formId) {
    const savedData = localStorage.getItem('autosave_' + formId);
    if (!savedData) return;
    
    try {
        const data = JSON.parse(savedData);
        const form = document.getElementById(formId);
        if (!form) return;
        
        Object.keys(data).forEach(key => {
            const field = form.querySelector(`[name="${key}"]`);
            if (field) {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    field.checked = data[key] === 'on';
                } else {
                    field.value = data[key];
                }
            }
        });
        
        showAlert('Form data restored from auto-save', 'info', 3000);
    } catch (e) {
        console.error('Error restoring auto-saved data:', e);
    }
}

/**
 * Clear auto-saved form data
 */
function clearAutoSavedForm(formId) {
    localStorage.removeItem('autosave_' + formId);
}

/**
 * Perform search
 */
function performSearch(input) {
    const query = input.value.trim();
    const target = input.getAttribute('data-search');
    
    if (query.length < 2) {
        clearSearchResults(target);
        return;
    }
    
    fetch(`../api/search.php?q=${encodeURIComponent(query)}&target=${target}`)
        .then(response => response.json())
        .then(data => {
            displaySearchResults(target, data.results);
        })
        .catch(error => {
            console.error('Search error:', error);
        });
}

/**
 * Display search results
 */
function displaySearchResults(target, results) {
    const container = document.querySelector(`[data-search-results="${target}"]`);
    if (!container) return;
    
    if (results.length === 0) {
        container.innerHTML = '<p class="text-muted">No results found</p>';
        return;
    }
    
    const html = results.map(result => `
        <div class="search-result-item p-2 border-bottom">
            <a href="${result.url}" class="text-decoration-none">
                <h6 class="mb-1">${escapeHtml(result.title)}</h6>
                <p class="mb-0 small text-muted">${escapeHtml(result.description)}</p>
            </a>
        </div>
    `).join('');
    
    container.innerHTML = html;
}

/**
 * Clear search results
 */
function clearSearchResults(target) {
    const container = document.querySelector(`[data-search-results="${target}"]`);
    if (container) {
        container.innerHTML = '';
    }
}

/**
 * Quiz functionality
 */
function initializeQuiz() {
    const quizForm = document.getElementById('quiz-form');
    if (!quizForm) return;
    
    // Timer functionality
    const timerElement = document.getElementById('quiz-timer');
    if (timerElement) {
        let timeLeft = parseInt(timerElement.getAttribute('data-time-limit')) * 60; // Convert minutes to seconds
        
        const timer = setInterval(() => {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            
            timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                clearInterval(timer);
                showAlert('Time is up! Submitting your quiz...', 'warning');
                quizForm.submit();
            }
            
            timeLeft--;
        }, 1000);
    }
    
    // Save progress
    quizForm.addEventListener('change', function() {
        saveQuizProgress();
    });
}

/**
 * Save quiz progress
 */
function saveQuizProgress() {
    const quizForm = document.getElementById('quiz-form');
    if (!quizForm) return;
    
    const formData = new FormData(quizForm);
    const answers = Object.fromEntries(formData.entries());
    
    localStorage.setItem('quiz_progress_' + quizForm.getAttribute('data-quiz-id'), JSON.stringify(answers));
}

/**
 * Restore quiz progress
 */
function restoreQuizProgress() {
    const quizForm = document.getElementById('quiz-form');
    if (!quizForm) return;
    
    const savedProgress = localStorage.getItem('quiz_progress_' + quizForm.getAttribute('data-quiz-id'));
    if (!savedProgress) return;
    
    try {
        const answers = JSON.parse(savedProgress);
        Object.keys(answers).forEach(questionId => {
            const field = quizForm.querySelector(`[name="${questionId}"]`);
            if (field) {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    field.checked = answers[questionId] === field.value;
                } else {
                    field.value = answers[questionId];
                }
            }
        });
        
        showAlert('Quiz progress restored', 'info', 3000);
    } catch (e) {
        console.error('Error restoring quiz progress:', e);
    }
}

/**
 * Utility functions
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '<',
        '>': '>',
        '"': '"',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

/**
 * Progress tracking
 */
function updateProgress(element, percentage) {
    const progressBar = element.querySelector('.progress-bar');
    if (progressBar) {
        progressBar.style.width = percentage + '%';
        progressBar.setAttribute('aria-valuenow', percentage);
        progressBar.textContent = percentage + '%';
    }
}

/**
 * Initialize course progress
 */
function initializeCourseProgress() {
    const progressElements = document.querySelectorAll('[data-progress]');
    progressElements.forEach(element => {
        const percentage = parseInt(element.getAttribute('data-progress'));
        updateProgress(element, percentage);
    });
}

/**
 * Forum functionality
 */
function initializeForum() {
    // Reply form toggle
    const replyButtons = document.querySelectorAll('[data-toggle="reply"]');
    replyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const target = this.getAttribute('data-target');
            const replyForm = document.getElementById(target);
            if (replyForm) {
                replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';
            }
        });
    });
    
    // Character counter for posts
    const textareas = document.querySelectorAll('textarea[data-max-length]');
    textareas.forEach(textarea => {
        const maxLength = parseInt(textarea.getAttribute('data-max-length'));
        const counter = document.createElement('div');
        counter.className = 'text-muted small mt-1';
        counter.textContent = `0 / ${maxLength} characters`;
        
        textarea.addEventListener('input', function() {
            const currentLength = this.value.length;
            counter.textContent = `${currentLength} / ${maxLength} characters`;
            
            if (currentLength > maxLength) {
                counter.classList.add('text-danger');
            } else {
                counter.classList.remove('text-danger');
            }
        });
        
        textarea.parentNode.appendChild(counter);
    });
}

/**
 * Notification system
 */
function showNotification(message, type = 'info', duration = 5000) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <span>${message}</span>
            <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, duration);
}

/**
 * Initialize all page-specific functionality
 */
function initializePage() {
    const pageType = document.body.getAttribute('data-page');
    
    switch (pageType) {
        case 'quiz':
            initializeQuiz();
            restoreQuizProgress();
            break;
        case 'course':
            initializeCourseProgress();
            break;
        case 'forum':
            initializeForum();
            break;
    }
}

// Initialize page-specific functionality when DOM is ready
document.addEventListener('DOMContentLoaded', initializePage);

// Export functions for global use
window.LMS = {
    showAlert,
    showLoading,
    hideLoading,
    showNotification,
    updateProgress,
    toggleSidebar,
    debounce,
    escapeHtml
};
