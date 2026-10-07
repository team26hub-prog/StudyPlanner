document.querySelectorAll('form[data-swal-confirm]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        let confirmed = false;

        if (window.Swal) {
            const result = await window.Swal.fire({
                icon: 'warning',
                title: 'Do you want to delete this user?',
                text: form.dataset.swalConfirm,
                showCancelButton: true,
                confirmButtonText: 'Confirm',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#c94f43',
                cancelButtonColor: '#73817c',
                reverseButtons: true
            });
            confirmed = result.isConfirmed;
        } else {
            confirmed = window.confirm(form.dataset.swalConfirm || 'Delete this user?');
        }

        if (confirmed) HTMLFormElement.prototype.submit.call(form);
    });
});

document.querySelectorAll('[data-confirm]').forEach((button) => {
    button.closest('form')?.addEventListener('submit', (event) => {
        if (!window.confirm(button.dataset.confirm)) {
            event.preventDefault();
        }
    });
});

document.querySelectorAll('[data-auto-dismiss]').forEach((notice) => {
    const duration = Number.parseInt(notice.dataset.autoDismiss, 10) || 3000;
    const fadeDuration = 250;

    window.setTimeout(() => {
        notice.classList.add('is-dismissing');
        window.setTimeout(() => notice.remove(), fadeDuration);
    }, Math.max(0, duration - fadeDuration));
});

document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
    const field = document.getElementById(toggle.dataset.passwordToggle);
    if (!field) return;

    toggle.addEventListener('click', () => {
        const showing = field.type === 'password';
        field.type = showing ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', String(showing));
        toggle.setAttribute('aria-label', showing ? 'Hide password' : 'Show password');
    });
});

const returnToPreviousPage = (fallbackHref) => {
    const destination = new URL(fallbackHref, window.location.href);
    if (document.referrer && window.history.length > 1) {
        try {
            const previousPage = new URL(document.referrer);
            if (previousPage.origin === window.location.origin && previousPage.pathname === destination.pathname) {
                window.history.back();
                return;
            }
        } catch {
            // Use the overview fallback when the referrer cannot be parsed.
        }
    }
    window.location.assign(destination.href);
};

document.querySelectorAll('[data-history-back]').forEach((link) => {
    link.addEventListener('click', (event) => {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        returnToPreviousPage(link.href);
    });
});

document.querySelectorAll('[data-close-create-user]').forEach((button) => {
    button.addEventListener('click', async () => {
        let confirmed = false;
        if (window.Swal) {
            const result = await window.Swal.fire({
                icon: 'question',
                title: 'Leave New Account form?',
                text: 'Your unsaved account information will be lost.',
                showCancelButton: true,
                confirmButtonText: 'Leave form',
                cancelButtonText: 'Stay here',
                confirmButtonColor: '#23775f',
                cancelButtonColor: '#73817c',
                reverseButtons: true
            });
            confirmed = result.isConfirmed;
        } else {
            confirmed = window.confirm('Leave the New Account form? Your unsaved information will be lost.');
        }

        if (confirmed) returnToPreviousPage(button.dataset.closeCreateUser);
    });
});

const validationMessageFor = (field, form) => {
    const value = field.type === 'password' ? field.value : field.value.trim();
    const label = field.labels?.[0]?.textContent.replace(/\s*Optional\s*/i, '').trim()
        || field.closest('fieldset')?.querySelector('legend')?.textContent.trim()
        || 'This field';
    const password = form.querySelector('[name="password"]');

    if (field.name === 'password_confirmation' && password && field.value !== password.value) {
        return 'Passwords do not match. Please enter the same password in both fields.';
    }
    if (field.required && !value && field.type !== 'radio') {
        if (field.type === 'email') return 'Enter your email address.';
        return `${label} is required.`;
    }
    if (field.type === 'radio' && field.required && !form.querySelector(`[name="${CSS.escape(field.name)}"]:checked`)) {
        return `Please choose ${label.toLowerCase()}.`;
    }
    if (field.type === 'email' && value && !field.validity.valid) {
        return 'Enter a valid email address.';
    }
    if (field.tagName === 'SELECT' && ![...field.options].some((option) => option.value === field.value)) {
        return `Choose a valid ${label.toLowerCase()}.`;
    }
    if ((field.type === 'date' || field.type === 'datetime-local') && value && Number(value.slice(0, 4)) < 1000) {
        return `${label} must use a year from 1000 onward.`;
    }
    if (field.validity.tooShort) {
        return `${label} must be at least ${field.minLength} characters.`;
    }
    if (field.validity.tooLong) {
        return `${label} must be no more than ${field.maxLength} characters.`;
    }
    if (field.validity.patternMismatch) {
        return field.title || `${label} does not meet the required format.`;
    }
    if (field.validity.badInput || !field.validity.valid) {
        return `${label} is not valid. Please check the value.`;
    }
    return '';
};

document.querySelectorAll('form').forEach((form) => {
    const fields = [...form.querySelectorAll('input:not([type="hidden"]):not([type="submit"]):not([type="button"]), textarea, select')];
    if (!fields.length || form.hasAttribute('data-confirm')) return;
    form.noValidate = true;
    const attempted = new WeakSet();

    const validateField = (field, showMessage = true) => {
        // A radio group has one shared error and only needs its first input checked.
        if (field.type === 'radio' && form.querySelectorAll(`[name="${CSS.escape(field.name)}"]`)[0] !== field) return '';
        const message = validationMessageFor(field, form);
        const errorTarget = field.type === 'radio' ? field.closest('fieldset') : field;
        let error = errorTarget.parentElement.querySelector(`.field-validation-message[data-for="${CSS.escape(field.name)}"]`);
        if (!error) {
            error = document.createElement('span');
            error.className = 'field-validation-message';
            error.dataset.for = field.name;
            error.id = `validation-${field.id || field.name.replace(/[^a-z0-9_-]/gi, '-')}`;
            errorTarget.insertAdjacentElement('afterend', error);
        }
        error.textContent = showMessage ? message : '';
        error.hidden = !showMessage || !message;
        if (field.type === 'radio') {
            errorTarget.setAttribute('aria-invalid', String(Boolean(showMessage && message)));
        } else {
            field.setAttribute('aria-invalid', String(Boolean(showMessage && message)));
            const describedBy = (field.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean).filter((id) => id !== error.id);
            if (showMessage && message) describedBy.push(error.id);
            if (describedBy.length) field.setAttribute('aria-describedby', describedBy.join(' '));
            else field.removeAttribute('aria-describedby');
        }
        return message;
    };

    fields.forEach((field) => {
        const currentField = field.type === 'radio'
            ? form.querySelector(`[name="${CSS.escape(field.name)}"]`)
            : field;
        const validateAsUserEdits = () => {
            attempted.add(currentField);
            validateField(currentField);
            if (field.name === 'password') {
                const confirmation = form.querySelector('[name="password_confirmation"]');
                if (confirmation && (confirmation.value !== '' || attempted.has(confirmation))) {
                    attempted.add(confirmation);
                    validateField(confirmation);
                }
            }
        };
        field.addEventListener('input', validateAsUserEdits);
        field.addEventListener('change', validateAsUserEdits);
        field.addEventListener('blur', () => {
            attempted.add(field);
            validateField(field);
        });
    });

    form.addEventListener('submit', async (event) => {
        const invalid = [];
        fields.forEach((field) => {
            attempted.add(field);
            const message = validateField(field);
            if (message && (field.type !== 'radio' || !invalid.some((item) => item.field.name === field.name))) {
                invalid.push({ field, message });
            }
        });
        if (!invalid.length) return;

        event.preventDefault();
        const firstInvalid = invalid[0];
        firstInvalid.field.focus();
        const detail = invalid.length === 1
            ? firstInvalid.message
            : `${firstInvalid.message} There are ${invalid.length - 1} more field${invalid.length === 2 ? '' : 's'} to review.`;
        if (window.Swal) {
            await window.Swal.fire({
                icon: 'warning',
                title: 'Please check your information',
                text: detail,
                confirmButtonText: 'Review form',
                confirmButtonColor: '#23775f'
            });
            firstInvalid.field.focus();
        } else {
            window.alert(detail);
        }
    });
});

const serverError = document.querySelector('.error-message[role="alert"], [data-swal-error]');
if (serverError?.textContent.trim()) {
    const message = serverError.textContent.trim();
    if (window.Swal) {
        serverError.hidden = true;
        window.Swal.fire({
            icon: 'error',
            title: 'Please check your information',
            text: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#23775f'
        });
    }
}

const successNotice = document.querySelector('[data-swal-success]');
if (successNotice?.textContent.trim() && window.Swal) {
    successNotice.hidden = true;
    window.Swal.fire({
        icon: 'success',
        title: successNotice.dataset.swalTitle || 'Success',
        text: successNotice.textContent.trim(),
        confirmButtonText: 'Continue',
        confirmButtonColor: '#23775f'
    });
}

document.querySelectorAll('form[action$="/logout"]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        let confirmed = false;
        if (window.Swal) {
            const result = await window.Swal.fire({
                icon: 'question',
                title: 'Sign out?',
                text: 'Are you sure you want to sign out?',
                showCancelButton: true,
                confirmButtonText: 'Sign out',
                cancelButtonText: 'Stay signed in',
                confirmButtonColor: '#23775f'
            });
            confirmed = result.isConfirmed;
        } else {
            confirmed = window.confirm('Are you sure you want to sign out?');
        }
        if (confirmed) HTMLFormElement.prototype.submit.call(form);
    });
});

const menuToggle = document.querySelector('.menu-toggle');
const mobileNav = document.querySelector('.mobile-nav');
const appNavBackdrop = document.querySelector('.app-nav-backdrop');
const appMobileClose = document.querySelector('.app-mobile-close');

if (menuToggle && mobileNav) {
    const hasAppDrawer = menuToggle.classList.contains('app-menu-toggle');
    const closeMenu = () => {
        mobileNav.classList.remove('is-open');
        menuToggle.setAttribute('aria-expanded', 'false');
        if (hasAppDrawer) {
            appNavBackdrop?.classList.remove('is-open');
            document.body.classList.remove('has-app-menu-open');
            menuToggle.setAttribute('aria-label', 'Open navigation');
        }
    };

    menuToggle.addEventListener('click', (event) => {
        event.stopPropagation();
        const isOpen = !mobileNav.classList.contains('is-open');
        mobileNav.classList.toggle('is-open', isOpen);
        menuToggle.setAttribute('aria-expanded', String(isOpen));
        if (hasAppDrawer) {
            appNavBackdrop?.classList.toggle('is-open', isOpen);
            document.body.classList.toggle('has-app-menu-open', isOpen);
            menuToggle.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Open navigation');
            if (isOpen) {
                appMobileClose?.focus({ preventScroll: true });
            }
        }
    });

    appNavBackdrop?.addEventListener('click', closeMenu);
    appMobileClose?.addEventListener('click', () => {
        closeMenu();
        menuToggle.focus({ preventScroll: true });
    });

    document.addEventListener('click', (event) => {
        if (!mobileNav.contains(event.target) && !menuToggle.contains(event.target) && !appNavBackdrop?.contains(event.target)) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && mobileNav.classList.contains('is-open')) {
            closeMenu();
            menuToggle.focus({ preventScroll: true });
        }
    });

    mobileNav.querySelectorAll('a, button').forEach((element) => {
        element.addEventListener('click', () => {
            if (window.innerWidth <= 760) {
                closeMenu();
            }
        });
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 760) {
            closeMenu();
        }
    });
}
