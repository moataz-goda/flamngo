import Alpine from 'alpinejs';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

window.Alpine = Alpine;
Alpine.start();

gsap.registerPlugin(ScrollTrigger);

const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const coarse = window.matchMedia('(pointer: coarse)').matches;

function initCursor() {
    if (reduced || coarse) return;

    const dot = document.createElement('div');
    const ring = document.createElement('div');
    dot.className = 'cursor-dot';
    ring.className = 'cursor-ring';
    ring.dataset.label = 'عرض';
    document.body.append(dot, ring);
    document.body.style.cursor = 'none';

    let x = window.innerWidth / 2;
    let y = window.innerHeight / 2;
    let rx = x;
    let ry = y;

    window.addEventListener('mousemove', (e) => {
        x = e.clientX;
        y = e.clientY;
        dot.style.transform = `translate(${x}px, ${y}px) translate(-50%, -50%)`;
    });

    const tick = () => {
        rx += (x - rx) * 0.18;
        ry += (y - ry) * 0.18;
        ring.style.transform = `translate(${rx}px, ${ry}px) translate(-50%, -50%)`;
        requestAnimationFrame(tick);
    };
    tick();

    document.querySelectorAll('[data-cursor="view"], .img-zoom, .gift-card a').forEach((el) => {
        el.addEventListener('mouseenter', () => ring.classList.add('is-hover'));
        el.addEventListener('mouseleave', () => ring.classList.remove('is-hover'));
    });
}

function initMagnetic() {
    if (reduced || coarse) return;

    document.querySelectorAll('[data-magnetic]').forEach((btn) => {
        btn.addEventListener('mousemove', (e) => {
            const rect = btn.getBoundingClientRect();
            const dx = e.clientX - (rect.left + rect.width / 2);
            const dy = e.clientY - (rect.top + rect.height / 2);
            btn.style.transform = `translate(${dx * 0.18}px, ${dy * 0.18}px)`;
        });
        btn.addEventListener('mouseleave', () => {
            btn.style.transform = '';
        });
    });
}

function initTilt() {
    if (reduced || coarse) return;

    document.querySelectorAll('[data-tilt]').forEach((card) => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width;
            const y = (e.clientY - rect.top) / rect.height;
            const rx = (0.5 - y) * 10;
            const ry = (x - 0.5) * 12;
            card.style.transform = `perspective(900px) rotateX(${rx}deg) rotateY(${ry}deg) translateY(-4px)`;
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
        });
    });
}

function initReveals() {
    if (reduced) {
        document.querySelectorAll('.reveal').forEach((el) => el.classList.add('is-visible'));
        return;
    }

    gsap.utils.toArray('.reveal').forEach((el) => {
        gsap.fromTo(el, { autoAlpha: 0, y: 28 }, {
            autoAlpha: 1,
            y: 0,
            duration: 0.9,
            ease: 'power3.out',
            scrollTrigger: {
                trigger: el,
                start: 'top 88%',
            },
        });
    });

    const hero = document.querySelector('[data-hero]');
    if (hero) {
        gsap.from('[data-hero-item]', {
            y: 40,
            autoAlpha: 0,
            duration: 1,
            stagger: 0.12,
            ease: 'power3.out',
            delay: 0.15,
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initCursor();
    initMagnetic();
    initTilt();
    initReveals();
});
