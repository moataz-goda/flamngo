<?php

namespace App\Support;

class ThemePalette
{
    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return [
            'plum',
            'deep_purple',
            'violet',
            'orchid',
            'blush',
            'kraft',
            'cream',
            'gold',
            'ink',
            'muted',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function defaults(?string $theme = null): array
    {
        $theme = $theme ?: 'flamingo';

        return match ($theme) {
            'bubbles' => [
                'plum' => '#8E1F4B',
                'deep_purple' => '#E8467C',
                'violet' => '#F06292',
                'orchid' => '#FF8FC7',
                'blush' => '#F7C6D9',
                'kraft' => '#E6C79C',
                'cream' => '#FFF7FA',
                'gold' => '#E6C79C',
                'ink' => '#3A1A28',
                'muted' => '#9A6B7D',
            ],
            default => [
                'plum' => '#3D0A4B',
                'deep_purple' => '#6D1580',
                'violet' => '#8B23A8',
                'orchid' => '#B44FC6',
                'blush' => '#E9C6CE',
                'kraft' => '#E4CDB4',
                'cream' => '#FBF7F4',
                'gold' => '#C9A227',
                'ink' => '#2A1A30',
                'muted' => '#7A6A80',
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, string>
     */
    public static function merge(?string $theme, array $overrides = []): array
    {
        $colors = self::defaults($theme);

        foreach (self::keys() as $key) {
            if (! array_key_exists($key, $overrides)) {
                continue;
            }

            $value = self::normalizeHex($overrides[$key] ?? null);
            if ($value !== null) {
                $colors[$key] = $value;
            }
        }

        return $colors;
    }

    /**
     * @param  array<string, string>  $colors
     * @return array<string, string> CSS var name => hex
     */
    public static function cssVariables(array $colors, ?string $theme = null): array
    {
        $vars = [];

        foreach ($colors as $key => $hex) {
            $vars['--'.str_replace('_', '-', $key)] = $hex;
        }

        // Bubbles CSS also references rose/berry/champagne aliases.
        if (($theme ?: 'flamingo') === 'bubbles') {
            $vars['--berry'] = $colors['plum'] ?? $vars['--plum'] ?? '#8E1F4B';
            $vars['--rose'] = $colors['deep_purple'] ?? $vars['--deep-purple'] ?? '#E8467C';
            $vars['--champagne'] = $colors['kraft'] ?? $vars['--kraft'] ?? '#E6C79C';
        }

        return $vars;
    }

    public static function normalizeHex(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (! preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $value)) {
            return null;
        }

        if (strlen($value) === 4) {
            $value = '#'.$value[1].$value[1].$value[2].$value[2].$value[3].$value[3];
        }

        return strtoupper($value);
    }

    public static function isValidHex(mixed $value): bool
    {
        return self::normalizeHex($value) !== null;
    }

    /**
     * @return list<array{key: string, group: string, label: string, affects: string}>
     */
    public static function fields(): array
    {
        return [
            [
                'key' => 'plum',
                'group' => 'brand',
                'label' => 'Navbar & main headings',
                'affects' => 'Top navigation bar, page titles, and strong headings across the store.',
            ],
            [
                'key' => 'deep_purple',
                'group' => 'brand',
                'label' => 'Buttons & prices',
                'affects' => 'Primary buttons, price text, and strong action highlights.',
            ],
            [
                'key' => 'violet',
                'group' => 'brand',
                'label' => 'Links & badges',
                'affects' => 'Hover links, small badges, and accent markers.',
            ],
            [
                'key' => 'orchid',
                'group' => 'brand',
                'label' => 'Gradients & soft glow',
                'affects' => 'Hero glow, category gradients, and soft brand lighting.',
            ],
            [
                'key' => 'blush',
                'group' => 'surfaces',
                'label' => 'Borders & cards outline',
                'affects' => 'Card borders, form outlines, and light divider accents.',
            ],
            [
                'key' => 'kraft',
                'group' => 'surfaces',
                'label' => 'Warm decorative accents',
                'affects' => 'Warm decorative backgrounds behind hero images and cards.',
            ],
            [
                'key' => 'cream',
                'group' => 'surfaces',
                'label' => 'Page background',
                'affects' => 'Main storefront page background and soft panel fills.',
            ],
            [
                'key' => 'gold',
                'group' => 'surfaces',
                'label' => 'Gold line & luxury accents',
                'affects' => 'Decorative gold/champagne lines and luxury accent details.',
            ],
            [
                'key' => 'ink',
                'group' => 'text',
                'label' => 'Main text',
                'affects' => 'Default body text color on the storefront.',
            ],
            [
                'key' => 'muted',
                'group' => 'text',
                'label' => 'Secondary text',
                'affects' => 'Helper text, descriptions, and less important labels.',
            ],
        ];
    }
}
