// Функция для валидации email
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Функция для валидации пароля (минимум 6 символов)
function validatePassword(password) {
    return password.length >= 6;
}

// Функция для валидации имени (только буквы, минимум 2 символа)
function validateName(name) {
    const re = /^[а-яА-ЯёЁa-zA-Z\s]{2,}$/;
    return re.test(name);
}

// Функция для отображения ошибки
function showError(input, message) {
    const formGroup = input.closest('.mb-3, .mb-4');
    let errorElement = formGroup.querySelector('.error-message');
    
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.className = 'error-message text-danger small mt-1';
        formGroup.appendChild(errorElement);
    }
    
    errorElement.textContent = message;
    input.style.borderColor = '#dc3545';
    input.classList.add('is-invalid');
    input.classList.remove('is-valid');
}

// Функция для скрытия ошибки
function clearError(input) {
    const formGroup = input.closest('.mb-3, .mb-4');
    const errorElement = formGroup.querySelector('.error-message');
    
    if (errorElement) {
        errorElement.remove();
    }
    
    input.style.borderColor = '';
    input.classList.remove('is-invalid');
    input.classList.add('is-valid');
}

// Валидация формы регистрации
function validateRegisterForm(event) {
    event.preventDefault();
    const form = event.target;
    let isValid = true;
    
    const login = form.querySelector('input[name="Login"]');
    const email = form.querySelector('input[name="Email"]');
    const password = form.querySelector('input[name="password"]');
    const confirmPassword = form.querySelector('input[name="confirm_password"]');
    
    // Валидация логина
    if (!validateName(login.value)) {
        showError(login, 'Логин должен содержать минимум 2 буквы');
        isValid = false;
    } else {
        clearError(login);
    }
    
    // Валидация email
    if (!validateEmail(email.value)) {
        showError(email, 'Введите корректный email');
        isValid = false;
    } else {
        clearError(email);
    }
    
    // Валидация пароля
    if (!validatePassword(password.value)) {
        showError(password, 'Пароль должен содержать минимум 6 символов');
        isValid = false;
    } else {
        clearError(password);
    }
    
    // Подтверждение пароля
    if (password.value !== confirmPassword.value) {
        showError(confirmPassword, 'Пароли не совпадают');
        isValid = false;
    } else {
        clearError(confirmPassword);
    }
    
    if (isValid) {
        form.submit();
    }
}

// Валидация формы входа
function validateLoginForm(event) {
    event.preventDefault();
    const form = event.target;
    let isValid = true;
    
    const login = form.querySelector('input[name="Login"]');
    const password = form.querySelector('input[name="password"]');
    
    if (!login.value.trim()) {
        showError(login, 'Введите логин');
        isValid = false;
    } else {
        clearError(login);
    }
    
    if (!password.value) {
        showError(password, 'Введите пароль');
        isValid = false;
    } else {
        clearError(password);
    }
    
    if (isValid) {
        form.submit();
    }
}

// Валидация формы отзыва
function validateCommentForm(event) {
    event.preventDefault();
    const form = event.target;
    let isValid = true;
    
    const comment = form.querySelector('textarea[name="comment"]');
    
    if (!comment.value.trim()) {
        showError(comment, 'Напишите комментарий');
        isValid = false;
    } else if (comment.value.trim().length < 10) {
        showError(comment, 'Комментарий должен содержать минимум 10 символов');
        isValid = false;
    } else {
        clearError(comment);
    }
    
    if (isValid) {
        form.submit();
    }
}

// Валидация формы обновления профиля
function validateProfileForm(event) {
    event.preventDefault();
    const form = event.target;
    let isValid = true;
    
    const email = form.querySelector('input[name="Email"]');
    
    if (email.value && !validateEmail(email.value)) {
        showError(email, 'Введите корректный email');
        isValid = false;
    } else {
        clearError(email);
    }
    
    const password = form.querySelector('input[name="password"]');
    const confirmPassword = form.querySelector('input[name="confirm_password"]');
    
    if (password.value && !validatePassword(password.value)) {
        showError(password, 'Пароль должен содержать минимум 6 символов');
        isValid = false;
    } else if (password.value && password.value !== confirmPassword.value) {
        showError(confirmPassword, 'Пароли не совпадают');
        isValid = false;
    } else {
        clearError(password);
        clearError(confirmPassword);
    }
    
    if (isValid) {
        form.submit();
    }
}

// Автовалидация при вводе
function setupAutoValidation(formId, validatorFunc) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const inputs = form.querySelectorAll('input, textarea');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim()) {
                const tempEvent = { target: form, preventDefault: () => {} };
                validatorFunc(tempEvent);
            }
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                clearError(this);
            }
        });
    });
    
    form.addEventListener('submit', validatorFunc);
}

// Инициализация валидации при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Форма регистрации
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        setupAutoValidation('registerForm', validateRegisterForm);
    }
    
    // Форма входа
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        setupAutoValidation('loginForm', validateLoginForm);
    }
    
    // Форма отзыва
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        setupAutoValidation('commentForm', validateCommentForm);
    }
    
    // Форма профиля
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        setupAutoValidation('profileForm', validateProfileForm);
    }
});

// Функция для показа toast уведомления
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;';
        document.body.appendChild(container);
    }
    
    const toast = document.createElement('div');
    const bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-info';
    toast.className = `toast align-items-center ${bgClass} text-white border-0 mb-2`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    const container = document.getElementById('toastContainer');
    container.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}
