import { celebrate, confetti } from './confetti';
import { bindSoundToggle, isSoundOn, playBirthday, playReveal } from './sound';

bindSoundToggle(document.querySelector('[data-sound-toggle]'));

// Opening celebration, then gentle bursts while she reads.
setTimeout(celebrate, 400);
const timer = setInterval(() => confetti({ count: 40, x: Math.random(), y: 0.2 + Math.random() * 0.3 }), 4500);
setTimeout(() => clearInterval(timer), 30000);

document.getElementById('celebrate-again')?.addEventListener('click', () => {
    celebrate();
    playReveal();
});

document.getElementById('play-music')?.addEventListener('click', () => {
    if (!isSoundOn()) document.querySelector('[data-sound-toggle]')?.click();
    playBirthday();
});
