// CITIBRIDGE Main JavaScript
// Handles loading animation, form submissions, and interactions

// Loading Screen Animation
document.addEventListener('DOMContentLoaded', function() {
    const loadingScreen = document.getElementById('loadingScreen');
    
    if (loadingScreen) {
        // Type CITY
        setTimeout(() => {
            const cityText = document.querySelector('.city-text');
            if (cityText) {
                typeText(cityText, 'CITY', 100);
            }
        }, 300);
        
        // Type BRIDGEBANK
        setTimeout(() => {
            const bridgebankText = document.querySelector('.bridgebank-text');
            if (bridgebankText) {
                bridgebankText.style.opacity = '1';
                typeText(bridgebankText, 'BRIDGEBANK', 60);
            }
        }, 800);
        
        // Hide loading screen (reduced from 3.5s to 2s for better UX)
        setTimeout(() => {
            loadingScreen.classList.add('hidden');
            // Remove from DOM after transition
            setTimeout(() => {
                loadingScreen.style.display = 'none';
            }, 500);
        }, 2000);
    }
});

// Typing animation function
function typeText(element, text, speed = 100) {
    let i = 0;
    element.textContent = '';
    
    function type() {
        if (i < text.length) {
            element.textContent += text.charAt(i);
            i++;
            setTimeout(type, speed);
        }
    }
    
    type();
}

// Show loading screen programmatically
function showLoadingScreen(callback) {
    const loadingScreen = document.getElementById('loadingScreen');
    
    if (loadingScreen) {
        loadingScreen.style.display = 'flex';
        loadingScreen.classList.remove('hidden');
        
        // Reset typing animation
        setTimeout(() => {
            const cityText = document.querySelector('.city-text');
            const bridgebankText = document.querySelector('.bridgebank-text');
            
            if (cityText) {
                cityText.textContent = '';
                typeText(cityText, 'CITY', 100);
            }
            
            setTimeout(() => {
                if (bridgebankText) {
                    bridgebankText.style.opacity = '1';
                    bridgebankText.textContent = '';
                    typeText(bridgebankText, 'BRIDGEBANK', 80);
                }
            }, 1200);
            
            setTimeout(() => {
                loadingScreen.classList.add('hidden');
                if (callback) callback();
            }, 1800);
        }, 100);
    }
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Format currency input
function formatCurrencyInput(input) {
    let value = input.value.replace(/[^\d.]/g, '');
    const parts = value.split('.');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    
    if (parts.length > 2) {
        parts.pop();
    }
    
    if (parts[1] && parts[1].length > 2) {
        parts[1] = parts[1].substring(0, 2);
    }
    
    input.value = parts.join('.');
}

// Phone number formatting
function formatPhoneNumber(input) {
    let value = input.value.replace(/\D/g, '');
    if (value.length > 10) {
        value = value.substring(0, 10);
    }
    
    if (value.length >= 6) {
        input.value = `(${value.substring(0, 3)}) ${value.substring(3, 6)}-${value.substring(6)}`;
    } else if (value.length >= 3) {
        input.value = `(${value.substring(0, 3)}) ${value.substring(3)}`;
    } else {
        input.value = value;
    }
}

// PIN validation
function validatePIN(pin, minLength = 4, maxLength = 6) {
    const pinRegex = new RegExp(`^\\d{${minLength},${maxLength}}$`);
    return pinRegex.test(pin);
}

// Confirm action dialog
function confirmAction(message, callback) {
    if (confirm(message)) {
        if (callback) callback();
    }
}

// Show/hide password
function togglePassword(inputId, toggleId) {
    const input = document.getElementById(inputId);
    const toggle = document.getElementById(toggleId);
    
    if (input && toggle) {
        if (input.type === 'password') {
            input.type = 'text';
            toggle.textContent = 'Hide';
        } else {
            input.type = 'password';
            toggle.textContent = 'Show';
        }
    }
}

// AJAX form submission
function submitFormAJAX(formId, successCallback, errorCallback) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const formData = new FormData(form);
    const formDataObj = {};
    
    formData.forEach((value, key) => {
        formDataObj[key] = value;
    });
    
    // Add CSRF token
    const csrfToken = document.querySelector('input[name="csrf_token"]');
    if (csrfToken) {
        formDataObj.csrf_token = csrfToken.value;
    }
    
    fetch(form.action, {
        method: form.method || 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(formDataObj)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (successCallback) successCallback(data);
        } else {
            if (errorCallback) errorCallback(data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (errorCallback) errorCallback({ message: 'An error occurred. Please try again.' });
    });
}

// Real-time balance checking
function checkBalance(userId) {
    fetch('api/check_balance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ user_id: userId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateBalanceDisplay(data.balance);
        }
    })
    .catch(error => console.error('Error checking balance:', error));
}

// Update balance display
function updateBalanceDisplay(balance) {
    const balanceElements = document.querySelectorAll('.balance-display');
    balanceElements.forEach(element => {
        element.textContent = formatCurrency(balance);
    });
}

// Format currency for display
function formatCurrency(amount) {
    return '$' + parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Show notification
function showNotification(message, type = 'success') {
    // Remove existing notifications
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = `notification alert alert-${type}`;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '10000';
    notification.style.minWidth = '300px';
    notification.style.boxShadow = '0 4px 6px rgba(0,0,0,0.1)';
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Copied to clipboard!', 'success');
    }).catch(err => {
        console.error('Failed to copy:', err);
        showNotification('Failed to copy to clipboard', 'danger');
    });
}

// PIN entry step management
class PINEntryManager {
    constructor() {
        this.currentStep = 1;
        this.maxSteps = 3;
        this.pins = {
            1: null,
            2: null,
            3: null
        };
    }
    
    setStep(step) {
        if (step >= 1 && step <= this.maxSteps) {
            this.currentStep = step;
            this.updateStepDisplay();
        }
    }
    
    setPIN(step, pin) {
        if (step >= 1 && step <= this.maxSteps) {
            this.pins[step] = pin;
            this.updateStepDisplay();
        }
    }
    
    nextStep() {
        if (this.currentStep < this.maxSteps) {
            this.currentStep++;
            this.updateStepDisplay();
        }
    }
    
    previousStep() {
        if (this.currentStep > 1) {
            this.currentStep--;
            this.updateStepDisplay();
        }
    }
    
    updateStepDisplay() {
        // Update step indicators
        for (let i = 1; i <= this.maxSteps; i++) {
            const stepElement = document.querySelector(`.pin-step[data-step="${i}"]`);
            if (stepElement) {
                stepElement.classList.remove('active', 'completed');
                
                if (i < this.currentStep) {
                    stepElement.classList.add('completed');
                } else if (i === this.currentStep) {
                    stepElement.classList.add('active');
                }
            }
        }
    }
    
    getAllPINs() {
        return this.pins;
    }
    
    isComplete() {
        return Object.values(this.pins).every(pin => pin !== null);
    }
}

// Initialize global PIN manager
const pinManager = new PINEntryManager();

// Page-specific initialization
document.addEventListener('DOMContentLoaded', function() {
    // Initialize currency inputs
    const currencyInputs = document.querySelectorAll('input[data-type="currency"]');
    currencyInputs.forEach(input => {
        input.addEventListener('input', () => formatCurrencyInput(input));
    });
    
    // Initialize phone inputs
    const phoneInputs = document.querySelectorAll('input[data-type="phone"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', () => formatPhoneNumber(input));
    });
    
    // Initialize PIN entry steps
    const pinSteps = document.querySelectorAll('.pin-step');
    pinSteps.forEach(step => {
        step.addEventListener('click', () => {
            const stepNumber = parseInt(step.dataset.step);
            if (pinManager.pins[stepNumber]) {
                pinManager.setStep(stepNumber);
            }
        });
    });
});