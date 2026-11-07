// Alpine.js est fourni par Livewire (@livewireScripts) : pas d'import séparé.

document.documentElement.classList.add('js');

const reveal = () => {
    const items = document.querySelectorAll('.reveal:not(.is-visible)');

    if (!('IntersectionObserver' in window)) {
        items.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '0px 0px -40px 0px' });

    items.forEach((el) => observer.observe(el));
};

document.addEventListener('DOMContentLoaded', reveal);
document.addEventListener('livewire:navigated', reveal);
