@extends('layouts.admin')

@section('title', __('Shop settings'))
@section('heading', __('Shop settings'))

@section('content')
@php
    $groups = [
        'brand' => [
            'title' => __('Brand & navigation'),
            'help' => __('These colors shape the top bar, buttons, and main brand look.'),
        ],
        'surfaces' => [
            'title' => __('Backgrounds & cards'),
            'help' => __('These colors shape page background, card edges, and soft panels.'),
        ],
        'text' => [
            'title' => __('Text'),
            'help' => __('These colors shape readable text on the storefront.'),
        ],
    ];
@endphp

<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="mx-auto max-w-3xl space-y-6" id="shop-settings-form">
    @csrf
    @method('PUT')

    <div class="admin-card space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('Storefront colors') }}</h2>
                <p class="mt-1 text-sm leading-6 text-[color:var(--muted)]">{{ __('Open the color studio to set exact hue, saturation, and lightness — not only the basic browser popup.') }}</p>
                <p class="mt-1 text-sm leading-6 text-[color:var(--muted)]">{{ __('Click Default to put that color back. Then save.') }}</p>
            </div>
            @if ($hasCustomColors)
                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-800 ring-1 ring-amber-200">{{ __('Custom colors active') }}</span>
            @else
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-800 ring-1 ring-emerald-200">{{ __('Using default colors') }}</span>
            @endif
        </div>

        @foreach ($groups as $groupKey => $group)
            <div class="space-y-3">
                <div>
                    <h3 class="font-display text-base font-extrabold text-[color:var(--plum)]">{{ $group['title'] }}</h3>
                    <p class="mt-0.5 text-sm text-[color:var(--muted)]">{{ $group['help'] }}</p>
                </div>

                <div class="space-y-3">
                    @foreach ($colorFields as $field)
                        @continue($field['group'] !== $groupKey)
                        @php
                            $key = $field['key'];
                            $value = old('colors.'.$key, $colors[$key] ?? '#000000');
                            $default = $defaultColors[$key] ?? '#000000';
                            $isChanged = strcasecmp((string) $value, (string) $default) !== 0;
                        @endphp
                        <div class="color-row rounded-2xl border p-4 {{ $isChanged ? 'border-[color:var(--violet)] bg-[color:var(--cream)]/60 is-changed' : 'border-stone-200 bg-white' }}"
                             data-default="{{ $default }}">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-sm font-extrabold text-[color:var(--plum)]">{{ __($field['label']) }}</p>
                                        <span class="changed-badge rounded-full bg-[color:var(--violet)] px-2 py-0.5 text-[10px] font-bold text-white {{ $isChanged ? '' : 'hidden' }}">{{ __('Changed') }}</span>
                                    </div>
                                    <p class="mt-1 text-sm leading-6 text-[color:var(--muted)]">
                                        <span class="font-bold text-[color:var(--plum)]">{{ __('Updates') }}:</span>
                                        {{ __($field['affects']) }}
                                    </p>
                                </div>

                                <div class="flex flex-wrap items-end gap-3">
                                    <button type="button"
                                            class="color-reset-default text-center"
                                            title="{{ __('Use default color') }}">
                                        <span class="mb-1 block text-[10px] font-bold text-[color:var(--muted)]">{{ __('Default') }}</span>
                                        <span class="mx-auto block h-9 w-9 rounded-full ring-2 ring-stone-300 ring-offset-2 transition hover:ring-[color:var(--violet)]"
                                              style="background: {{ $default }}"></span>
                                        <span class="mt-1 block text-[10px] font-bold text-[color:var(--violet)]">{{ __('Click to restore') }}</span>
                                    </button>

                                    <div class="color-studio">
                                        <button type="button" class="color-studio__trigger" aria-expanded="false">
                                            <span class="color-studio__swatch color-live" style="background: {{ $value }}"></span>
                                            <span class="text-start">
                                                <span class="block text-[10px] font-bold text-[color:var(--muted)]">{{ __('Selected') }}</span>
                                                <span class="color-hex-label block font-mono text-xs font-bold text-[color:var(--plum)]" dir="ltr">{{ $value }}</span>
                                            </span>
                                            <span class="text-xs font-bold text-[color:var(--violet)]">{{ __('Edit') }}</span>
                                        </button>

                                        <div class="color-studio__panel" role="dialog" aria-label="{{ __('Color studio') }}">
                                            <div class="color-studio__preview color-live" style="background: {{ $value }}"></div>

                                            <div class="color-studio__field">
                                                <label><span>{{ __('Hue') }}</span><span class="hue-val font-mono" dir="ltr">0°</span></label>
                                                <input type="range" class="color-hue color-studio__hue" min="0" max="360" step="1" value="0">
                                            </div>
                                            <div class="color-studio__field">
                                                <label><span>{{ __('Saturation') }}</span><span class="sat-val font-mono" dir="ltr">0%</span></label>
                                                <input type="range" class="color-sat color-studio__sat" min="0" max="100" step="1" value="0">
                                            </div>
                                            <div class="color-studio__field">
                                                <label><span>{{ __('Lightness') }}</span><span class="light-val font-mono" dir="ltr">0%</span></label>
                                                <input type="range" class="color-light color-studio__light" min="0" max="100" step="1" value="0">
                                            </div>

                                            <div class="color-studio__field">
                                                <label><span>{{ __('Exact values') }}</span></label>
                                                <div class="color-studio__grid" style="grid-template-columns: 1.2fr repeat(3, minmax(0, 1fr));">
                                                    <input type="text" class="color-hex" name="colors[{{ $key }}]" value="{{ $value }}" maxlength="7" placeholder="#000000" autocomplete="off" aria-label="Hex">
                                                    <input type="number" class="color-r2" min="0" max="255" step="1" placeholder="R" aria-label="{{ __('Red') }}">
                                                    <input type="number" class="color-g2" min="0" max="255" step="1" placeholder="G" aria-label="{{ __('Green') }}">
                                                    <input type="number" class="color-b2" min="0" max="255" step="1" placeholder="B" aria-label="{{ __('Blue') }}">
                                                </div>
                                            </div>

                                            <div>
                                                <p class="mb-1 text-[10px] font-bold text-[color:var(--muted)]">{{ __('Shade steps') }}</p>
                                                <div class="color-studio__shades"></div>
                                            </div>

                                            <div class="color-studio__actions">
                                                <button type="button" class="color-studio-close btn-ghost" style="padding:0.45rem 0.9rem;font-size:0.75rem;">{{ __('Done') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        @error('colors')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="admin-card space-y-4">
        <h2 class="font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('Shop details') }}</h2>
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-bold">{{ __('Shop name') }}</label>
                <input type="text" name="name" value="{{ old('name', $shop->name) }}" required class="admin-input">
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-bold">{{ __('Tagline') }} (AR)</label>
                <input type="text" name="tagline" value="{{ old('tagline', $shop->tagline) }}" class="admin-input">
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-bold">{{ __('Tagline') }} (EN)</label>
                <input type="text" name="tagline_en" value="{{ old('tagline_en', $shop->tagline_en) }}" class="admin-input" dir="ltr">
            </div>
            <div>
                <label class="mb-1 block text-sm font-bold">{{ __('Mobile') }}</label>
                <input type="text" name="phone" value="{{ old('phone', $shop->phone) }}" class="admin-input" dir="ltr">
            </div>
            <div>
                <label class="mb-1 block text-sm font-bold">{{ __('Email') }}</label>
                <input type="email" name="email" value="{{ old('email', $shop->email) }}" class="admin-input" dir="ltr">
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-bold">{{ __('Address') }} (AR)</label>
                <input type="text" name="address" value="{{ old('address', $shop->address) }}" class="admin-input">
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-bold">{{ __('Address') }} (EN)</label>
                <input type="text" name="address_en" value="{{ old('address_en', $shop->address_en) }}" class="admin-input" dir="ltr">
            </div>
            <div>
                <label class="mb-1 block text-sm font-bold">{{ __('Domain') }}</label>
                <input type="text" name="domain" value="{{ old('domain', $shop->domain) }}" required class="admin-input" dir="ltr">
            </div>
            <div>
                <label class="mb-1 block text-sm font-bold">{{ __('Currency symbol') }}</label>
                <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $shop->currency_symbol) }}" class="admin-input">
                <p class="mt-1 text-xs text-[color:var(--muted)]">{{ __('Example: ج.م — shown as EGP when the site language is English.') }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-bold">{{ __('Logo') }}</label>
                @if ($shop->logo_url)
                    <img src="{{ $shop->logo_url }}" alt="" class="mb-2 h-16 w-auto rounded-lg object-contain">
                @endif
                <input type="file" name="logo" accept="image/*" class="admin-input">
            </div>
            <div>
                <label class="mb-1 block text-sm font-bold">{{ __('Hero image') }}</label>
                @if ($shop->hero_url)
                    <img src="{{ $shop->hero_url }}" alt="" class="mb-2 h-16 w-28 rounded-lg object-cover">
                @endif
                <input type="file" name="hero_image" accept="image/*" class="admin-input">
            </div>
        </div>
    </div>

    <div class="flex flex-wrap gap-3">
        <button type="submit" class="btn-primary">{{ __('Save settings') }}</button>
    </div>
</form>

<div class="admin-card mx-auto mt-4 max-w-3xl space-y-3">
    <h2 class="font-display text-lg font-extrabold text-[color:var(--plum)]">{{ __('Restore default colors') }}</h2>
    <p class="text-sm leading-6 text-[color:var(--muted)]">{{ __('Go back to the original shop colors (the palette before any custom changes).') }}</p>
    <form action="{{ route('admin.settings.colors.restore') }}" method="POST"
          onsubmit="return confirm(@json(__('Restore all storefront colors to the original defaults?')))">
        @csrf
        <button type="submit" class="btn-ghost" @disabled(! $hasCustomColors)>
            {{ __('Restore default colors') }}
        </button>
    </form>
    @unless ($hasCustomColors)
        <p class="text-xs text-[color:var(--muted)]">{{ __('You are already using the default colors.') }}</p>
    @endunless
</div>

<script>
(() => {
    const clamp = (n, min, max) => Math.min(max, Math.max(min, n));

    const normalizeHex = (value) => {
        if (!value) return null;
        let v = String(value).trim().toUpperCase();
        if (!v.startsWith('#')) v = '#' + v;
        if (/^#[0-9A-F]{3}$/.test(v)) {
            v = '#' + v[1] + v[1] + v[2] + v[2] + v[3] + v[3];
        }
        return /^#[0-9A-F]{6}$/.test(v) ? v : null;
    };

    const hexToRgb = (hex) => {
        const clean = normalizeHex(hex);
        if (!clean) return null;
        return {
            r: parseInt(clean.slice(1, 3), 16),
            g: parseInt(clean.slice(3, 5), 16),
            b: parseInt(clean.slice(5, 7), 16),
        };
    };

    const rgbToHex = (r, g, b) => {
        const to = (n) => clamp(Math.round(n), 0, 255).toString(16).padStart(2, '0').toUpperCase();
        return `#${to(r)}${to(g)}${to(b)}`;
    };

    const rgbToHsl = ({ r, g, b }) => {
        r /= 255; g /= 255; b /= 255;
        const max = Math.max(r, g, b), min = Math.min(r, g, b);
        let h = 0, s = 0;
        const l = (max + min) / 2;
        if (max !== min) {
            const d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
            switch (max) {
                case r: h = (g - b) / d + (g < b ? 6 : 0); break;
                case g: h = (b - r) / d + 2; break;
                default: h = (r - g) / d + 4; break;
            }
            h /= 6;
        }
        return { h: Math.round(h * 360), s: Math.round(s * 100), l: Math.round(l * 100) };
    };

    const hslToRgb = (h, s, l) => {
        h = ((h % 360) + 360) % 360;
        s = clamp(s, 0, 100) / 100;
        l = clamp(l, 0, 100) / 100;
        if (s === 0) {
            const v = Math.round(l * 255);
            return { r: v, g: v, b: v };
        }
        const hue2rgb = (p, q, t) => {
            if (t < 0) t += 1;
            if (t > 1) t -= 1;
            if (t < 1/6) return p + (q - p) * 6 * t;
            if (t < 1/2) return q;
            if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
            return p;
        };
        const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
        const p = 2 * l - q;
        const hk = h / 360;
        return {
            r: Math.round(hue2rgb(p, q, hk + 1/3) * 255),
            g: Math.round(hue2rgb(p, q, hk) * 255),
            b: Math.round(hue2rgb(p, q, hk - 1/3) * 255),
        };
    };

    const closeAll = (except = null) => {
        document.querySelectorAll('.color-studio.is-open').forEach((studio) => {
            if (studio === except) return;
            studio.classList.remove('is-open');
            studio.querySelector('.color-studio__trigger')?.setAttribute('aria-expanded', 'false');
        });
    };

    const updateSliderTracks = (row, hsl) => {
        const sat = row.querySelector('.color-sat');
        const light = row.querySelector('.color-light');
        if (sat) {
            sat.style.background = `linear-gradient(90deg, hsl(${hsl.h},0%,${hsl.l}%), hsl(${hsl.h},100%,${hsl.l}%))`;
        }
        if (light) {
            light.style.background = `linear-gradient(90deg, #000, hsl(${hsl.h},${hsl.s}%,50%), #fff)`;
        }
    };

    const renderShades = (row, hex) => {
        const wrap = row.querySelector('.color-studio__shades');
        if (!wrap) return;
        const rgb = hexToRgb(hex);
        if (!rgb) return;
        const hsl = rgbToHsl(rgb);
        wrap.innerHTML = '';
        [-24, -12, -6, 0, 6, 12, 24].forEach((delta) => {
            const shade = rgbToHex(...Object.values(hslToRgb(hsl.h, hsl.s, clamp(hsl.l + delta, 4, 96))));
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'color-studio__shade';
            btn.style.background = shade;
            btn.title = shade;
            btn.addEventListener('click', () => applyHex(row, shade));
            wrap.appendChild(btn);
        });
    };

    const applyHex = (row, hex, { fromSliders = false } = {}) => {
        const clean = normalizeHex(hex);
        if (!clean) return;

        const rgb = hexToRgb(clean);
        const hsl = rgbToHsl(rgb);
        const def = normalizeHex(row.dataset.default);

        row.querySelectorAll('.color-live').forEach((el) => { el.style.background = clean; });
        const hexInput = row.querySelector('.color-hex');
        const label = row.querySelector('.color-hex-label');
        const badge = row.querySelector('.changed-badge');
        if (hexInput) hexInput.value = clean;
        if (label) label.textContent = clean;

        row.querySelector('.color-r2').value = rgb.r;
        row.querySelector('.color-g2').value = rgb.g;
        row.querySelector('.color-b2').value = rgb.b;

        if (!fromSliders) {
            row.querySelector('.color-hue').value = hsl.h;
            row.querySelector('.color-sat').value = hsl.s;
            row.querySelector('.color-light').value = hsl.l;
        }
        row.querySelector('.hue-val').textContent = `${hsl.h}°`;
        row.querySelector('.sat-val').textContent = `${hsl.s}%`;
        row.querySelector('.light-val').textContent = `${hsl.l}%`;
        updateSliderTracks(row, hsl);
        renderShades(row, clean);

        const changed = def && clean !== def;
        row.classList.toggle('is-changed', changed);
        row.classList.toggle('border-[color:var(--violet)]', changed);
        row.classList.toggle('bg-[color:var(--cream)]/60', changed);
        row.classList.toggle('border-stone-200', !changed);
        row.classList.toggle('bg-white', !changed);
        if (badge) badge.classList.toggle('hidden', !changed);
    };

    document.querySelectorAll('.color-row').forEach((row) => {
        const studio = row.querySelector('.color-studio');
        const trigger = row.querySelector('.color-studio__trigger');
        const closeBtn = row.querySelector('.color-studio-close');
        const hexInput = row.querySelector('.color-hex');
        const hue = row.querySelector('.color-hue');
        const sat = row.querySelector('.color-sat');
        const light = row.querySelector('.color-light');
        const r = row.querySelector('.color-r2');
        const g = row.querySelector('.color-g2');
        const b = row.querySelector('.color-b2');
        const reset = row.querySelector('.color-reset-default');

        applyHex(row, hexInput?.value || row.dataset.default);

        trigger?.addEventListener('click', (e) => {
            e.stopPropagation();
            const willOpen = !studio.classList.contains('is-open');
            closeAll(willOpen ? studio : null);
            studio.classList.toggle('is-open', willOpen);
            trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });

        closeBtn?.addEventListener('click', () => {
            studio.classList.remove('is-open');
            trigger?.setAttribute('aria-expanded', 'false');
        });

        const fromHsl = () => {
            const rgb = hslToRgb(+hue.value, +sat.value, +light.value);
            applyHex(row, rgbToHex(rgb.r, rgb.g, rgb.b), { fromSliders: true });
            row.querySelector('.hue-val').textContent = `${hue.value}°`;
            row.querySelector('.sat-val').textContent = `${sat.value}%`;
            row.querySelector('.light-val').textContent = `${light.value}%`;
            updateSliderTracks(row, { h: +hue.value, s: +sat.value, l: +light.value });
        };

        hue?.addEventListener('input', fromHsl);
        sat?.addEventListener('input', fromHsl);
        light?.addEventListener('input', fromHsl);

        hexInput?.addEventListener('input', () => {
            const clean = normalizeHex(hexInput.value);
            if (clean) applyHex(row, clean);
        });

        const fromRgb = () => {
            if ([r.value, g.value, b.value].some((v) => v === '' || Number.isNaN(+v))) return;
            applyHex(row, rgbToHex(+r.value, +g.value, +b.value));
        };
        r?.addEventListener('input', fromRgb);
        g?.addEventListener('input', fromRgb);
        b?.addEventListener('input', fromRgb);

        reset?.addEventListener('click', () => applyHex(row, row.dataset.default));
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.color-studio')) closeAll();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeAll();
    });
})();
</script>
@endsection
