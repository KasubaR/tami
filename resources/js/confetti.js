// Dependency-free canvas confetti. Does nothing if the visitor prefers reduced motion.
const COLORS = ['#e8c773', '#d9ae4a', '#f79ab1', '#ef6e8f', '#fbc2d0', '#ffffff', '#c4932b'];

export const prefersReducedMotion = () => window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;

export function confetti({ count = 140, x = 0.5, y = 0.5, spread = 1, duration = 3200 } = {}) {
    if (prefersReducedMotion()) return;

    const canvas = document.createElement('canvas');
    Object.assign(canvas.style, { position: 'fixed', inset: '0', width: '100%', height: '100%', pointerEvents: 'none', zIndex: 60 });
    const dpr = window.devicePixelRatio || 1;
    canvas.width = innerWidth * dpr;
    canvas.height = innerHeight * dpr;
    document.body.appendChild(canvas);
    const ctx = canvas.getContext('2d');
    ctx.scale(dpr, dpr);

    const parts = Array.from({ length: count }, () => {
        const angle = Math.random() * Math.PI * 2;
        const speed = (4 + Math.random() * 9) * spread;
        return {
            x: x * innerWidth,
            y: y * innerHeight,
            vx: Math.cos(angle) * speed,
            vy: Math.sin(angle) * speed - 5,
            size: 5 + Math.random() * 6,
            color: COLORS[(Math.random() * COLORS.length) | 0],
            rot: Math.random() * Math.PI,
            vr: (Math.random() - 0.5) * 0.4,
            round: Math.random() < 0.3,
        };
    });

    const start = performance.now();
    (function frame(now) {
        const t = now - start;
        ctx.clearRect(0, 0, innerWidth, innerHeight);
        const fade = t > duration - 800 ? Math.max(0, (duration - t) / 800) : 1;
        for (const p of parts) {
            p.vy += 0.22;
            p.vx *= 0.992;
            p.x += p.vx;
            p.y += p.vy;
            p.rot += p.vr;
            ctx.save();
            ctx.globalAlpha = fade;
            ctx.translate(p.x, p.y);
            ctx.rotate(p.rot);
            ctx.fillStyle = p.color;
            if (p.round) {
                ctx.beginPath();
                ctx.arc(0, 0, p.size / 2, 0, Math.PI * 2);
                ctx.fill();
            } else {
                ctx.fillRect(-p.size / 2, -p.size / 4, p.size, p.size / 2);
            }
            ctx.restore();
        }
        t < duration ? requestAnimationFrame(frame) : canvas.remove();
    })(start);
}

/** A few bursts from different spots, for the big moments. */
export function celebrate() {
    confetti({ count: 160, x: 0.5, y: 0.45 });
    setTimeout(() => confetti({ count: 100, x: 0.15, y: 0.6 }), 350);
    setTimeout(() => confetti({ count: 100, x: 0.85, y: 0.6 }), 700);
}
