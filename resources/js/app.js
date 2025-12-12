import './bootstrap';
import Alpine from "alpinejs";

window.Alpine = Alpine;
Alpine.start();

// Keyboard focus outlines toggle
document.addEventListener('keydown', (e) => {
    if (e.key === 'Tab') {
        document.body.classList.add('user-is-tabbing');
    }
});