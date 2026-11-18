// Optional sound effects, synthesised with the Web Audio API (no audio files).
// Off by default; the visitor opts in with the sound toggle.
const KEY = 'birthday-mystery-sound';
let ctx = null;

const read = () => {
    try {
        return localStorage.getItem(KEY) === 'on';
    } catch {
        return false;
    }
};
let enabled = read();

function audio() {
    ctx ??= new (window.AudioContext || window.webkitAudioContext)();
    if (ctx.state === 'suspended') ctx.resume();
    return ctx;
}

function note(freq, start, length = 0.25, type = 'sine', volume = 0.15) {
    const a = audio();
    const osc = a.createOscillator();
    const gain = a.createGain();
    osc.type = type;
    osc.frequency.value = freq;
    gain.gain.setValueAtTime(0, a.currentTime + start);
    gain.gain.linearRampToValueAtTime(volume, a.currentTime + start + 0.02);
    gain.gain.exponentialRampToValueAtTime(0.0001, a.currentTime + start + length);
    osc.connect(gain).connect(a.destination);
    osc.start(a.currentTime + start);
    osc.stop(a.currentTime + start + length + 0.05);
}

const guard = (fn) => () => {
    if (!enabled) return;
    try {
        fn();
    } catch {}
};

/** Sparkly rising chime for opening a gift. */
export const playReveal = guard(() => [523, 659, 784, 1047, 1319].forEach((f, i) => note(f, i * 0.09, 0.5, 'triangle')));

/** Soft tick for typing a letter. */
export const playTick = guard(() => note(880, 0, 0.06, 'sine', 0.06));

/** Lower, softer tick for deleting. */
export const playBack = guard(() => note(440, 0, 0.06, 'sine', 0.05));

/** Bright two-note ding when a word is right. */
export const playCorrect = guard(() => [659, 988].forEach((f, i) => note(f, i * 0.1, 0.3, 'triangle', 0.14)));

/** Gentle low buzz when a word is wrong. */
export const playWrong = guard(() => [220, 185].forEach((f, i) => note(f, i * 0.12, 0.25, 'sawtooth', 0.06)));

/** "Happy Birthday" for the finale. Frequencies in Hz, beats in quarter notes. */
export const playBirthday = guard(() => {
    const [C, D, E, F, G, A, B, C2] = [262, 294, 330, 349, 392, 440, 494, 523];
    const tune = [
        [G, 0.75], [G, 0.25], [A, 1], [G, 1], [C2, 1], [B, 2],
        [G, 0.75], [G, 0.25], [A, 1], [G, 1], [D * 2, 1], [C2, 2],
        [G, 0.75], [G, 0.25], [G * 2, 1], [E * 2, 1], [C2, 1], [B, 1], [A, 1],
        [F * 2, 0.75], [F * 2, 0.25], [E * 2, 1], [C2, 1], [D * 2, 1], [C2, 2],
    ];
    let t = 0;
    for (const [f, beats] of tune) {
        note(f, t * 0.42, beats * 0.4, 'triangle', 0.14);
        t += beats;
    }
});

export const isSoundOn = () => enabled;

export function setSound(on) {
    enabled = on;
    try {
        localStorage.setItem(KEY, on ? 'on' : 'off');
    } catch {}
    if (on) audio(); // unlock audio inside the user's click
}

/** Wire up a toggle button: <button data-sound-toggle>. */
export function bindSoundToggle(button) {
    if (!button) return;
    const on = button.querySelector('[data-sound-on]');
    const off = button.querySelector('[data-sound-off]');
    const paint = () => {
        if (on) on.hidden = !enabled;
        if (off) off.hidden = enabled;
        button.setAttribute('aria-pressed', String(enabled));
    };
    button.addEventListener('click', () => {
        setSound(!enabled);
        paint();
        if (enabled) playReveal();
    });
    paint();
}
