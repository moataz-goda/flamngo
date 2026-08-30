import Alpine from 'alpinejs';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

window.Alpine = Alpine;
Alpine.start();
gsap.registerPlugin(ScrollTrigger);

const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const coarse = window.matchMedia('(pointer: coarse)').matches;

function initBubbleField() {
    const canvas = document.querySelector('[data-bubble-canvas]');
    if (!canvas || reduced) return;

    const ctx = canvas.getContext('2d');
    let bubbles = [];
    let raf;

    const resize = () => {
        const parent = canvas.parentElement;
        canvas.width = parent.clientWidth;
        canvas.height = parent.clientHeight;
        const count = Math.min(36, Math.floor(canvas.width / 40));
        bubbles = Array.from({ length: count }, () => ({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            r: 8 + Math.random() * 28,
            vx: -0.2 + Math.random() * 0.4,
            vy: -0.35 - Math.random() * 0.55,
            a: 0.18 + Math.random() * 0.28,
        }));
    };

    const draw = () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        bubbles.forEach((b) => {
            b.x += b.vx;
            b.y += b.vy;
            if (b.y + b.r < 0) {
                b.y = canvas.height + b.r;
                b.x = Math.random() * canvas.width;
            }
            if (b.x < -b.r) b.x = canvas.width + b.r;
            if (b.x > canvas.width + b.r) b.x = -b.r;

            const g = ctx.createRadialGradient(b.x - b.r * 0.3, b.y - b.r * 0.3, 1, b.x, b.y, b.r);
            g.addColorStop(0, `rgba(255,255,255,${b.a + 0.25})`);
            g.addColorStop(0.45, `rgba(255,143,199,${b.a * 0.55})`);
            g.addColorStop(1, `rgba(126,232,250,${b.a * 0.2})`);
            ctx.beginPath();
            ctx.fillStyle = g;
            ctx.arc(b.x, b.y, b.r, 0, Math.PI * 2);
            ctx.fill();
            ctx.strokeStyle = `rgba(255,255,255,${b.a})`;
            ctx.lineWidth = 1.2;
            ctx.stroke();
        });
        raf = requestAnimationFrame(draw);
    };

    resize();
    draw();
    window.addEventListener('resize', resize);
    window.addEventListener('beforeunload', () => cancelAnimationFrame(raf));
}

function initCursorTrail() {
    if (reduced || coarse) return;

    const dot = document.createElement('div');
    const ring = document.createElement('div');
    dot.className = 'cursor-dot';
    ring.className = 'cursor-ring';
    ring.dataset.label = 'عرض';
    document.body.append(dot, ring);
    document.body.style.cursor = 'none';

    let x = innerWidth / 2;
    let y = innerHeight / 2;
    let rx = x;
    let ry = y;

    window.addEventListener('mousemove', (e) => {
        x = e.clientX;
        y = e.clientY;
        dot.style.transform = `translate(${x}px, ${y}px) translate(-50%, -50%)`;
    });

    window.addEventListener('click', (e) => {
        const pop = document.createElement('div');
        pop.className = 'bubble-pop';
        pop.style.left = `${e.clientX}px`;
        pop.style.top = `${e.clientY}px`;
        document.body.append(pop);
        setTimeout(() => pop.remove(), 450);
    });

    const tick = () => {
        rx += (x - rx) * 0.16;
        ry += (y - ry) * 0.16;
        ring.style.transform = `translate(${rx}px, ${ry}px) translate(-50%, -50%)`;
        requestAnimationFrame(tick);
    };
    tick();

    document.querySelectorAll('[data-cursor="view"], .bubble-card a, .img-zoom').forEach((el) => {
        el.addEventListener('mouseenter', () => ring.classList.add('is-hover'));
        el.addEventListener('mouseleave', () => ring.classList.remove('is-hover'));
    });
}

function initTilt() {
    if (reduced || coarse) return;
    document.querySelectorAll('[data-tilt]').forEach((card) => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const px = (e.clientX - rect.left) / rect.width;
            const py = (e.clientY - rect.top) / rect.height;
            card.style.transform = `perspective(1000px) rotateX(${(0.5 - py) * 12}deg) rotateY(${(px - 0.5) * 14}deg) translateY(-6px)`;
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
        });
    });
}

function initMagnetic() {
    if (reduced || coarse) return;
    document.querySelectorAll('[data-magnetic]').forEach((btn) => {
        btn.addEventListener('mousemove', (e) => {
            const rect = btn.getBoundingClientRect();
            const dx = e.clientX - (rect.left + rect.width / 2);
            const dy = e.clientY - (rect.top + rect.height / 2);
            btn.style.transform = `translate(${dx * 0.2}px, ${dy * 0.2}px)`;
        });
        btn.addEventListener('mouseleave', () => {
            btn.style.transform = '';
        });
    });
}

function initReveals() {
    if (reduced) {
        document.querySelectorAll('.reveal').forEach((el) => el.classList.add('is-visible'));
        return;
    }

    gsap.utils.toArray('.reveal').forEach((el) => {
        gsap.fromTo(el, { autoAlpha: 0, y: 36 }, {
            autoAlpha: 1,
            y: 0,
            duration: 1,
            ease: 'power3.out',
            scrollTrigger: { trigger: el, start: 'top 88%' },
        });
    });

    if (document.querySelector('[data-hero]')) {
        gsap.from('[data-hero-item]', {
            y: 48,
            autoAlpha: 0,
            duration: 1.05,
            stagger: 0.12,
            ease: 'power3.out',
            delay: 0.1,
        });
    }

    document.querySelectorAll('[data-counter]').forEach((el) => {
        const target = Number(el.dataset.counter || 0);
        const obj = { val: 0 };
        gsap.to(obj, {
            val: target,
            duration: 1.6,
            ease: 'power2.out',
            scrollTrigger: { trigger: el, start: 'top 90%' },
            onUpdate: () => {
                el.textContent = Math.round(obj.val);
            },
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initBubbleField();
    initCursorTrail();
    initTilt();
    initMagnetic();
    initReveals();
});
