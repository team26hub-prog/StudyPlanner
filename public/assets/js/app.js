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

document.querySelectorAll('[data-password-confirmation]').forEach((form) => {
    const password = form.querySelector('[name="password"]');
    const confirmation = form.querySelector('[name="password_confirmation"]');

    if (!password || !confirmation) {
        return;
    }

    const validateConfirmation = () => {
        confirmation.setCustomValidity(
            confirmation.value !== '' && confirmation.value !== password.value
                ? 'Passwords do not match.'
                : ''
        );
    };

    password.addEventListener('input', validateConfirmation);
    confirmation.addEventListener('input', validateConfirmation);
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
