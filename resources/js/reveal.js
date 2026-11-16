import { celebrate, prefersReducedMotion } from './confetti';
import { bindSoundToggle, playReveal } from './sound';

const stage = document.getElementById('gift-stage');

if (stage) {
    const box = document.getElementById('gift-box');
    const closed = document.getElementById('gift-closed');
    const opened = document.getElementById('gift-opened');
    let done = false;

    bindSoundToggle(document.querySelector('[data-sound-toggle]'));

    function open() {
        if (done) return;
        done = true;
        box.classList.add('is-opening');
        box.disabled = true;

        setTimeout(() => {
            closed.hidden = true;
            opened.hidden = false;
            opened.classList.add('reveal-in');
            opened.querySelector('[data-focus]')?.focus({ preventScroll: true });
            celebrate();
            playReveal();
        }, prefersReducedMotion() ? 0 : 900);
    }

    box.addEventListener('click', open);

    // Replay: run the whole sequence again without reloading the page.
    document.getElementById('gift-replay')?.addEventListener('click', () => {
        done = false;
        opened.hidden = true;
        opened.classList.remove('reveal-in');
        closed.hidden = false;
        box.classList.remove('is-opening');
        box.disabled = false;
        box.focus({ preventScroll: true });
    });
}
