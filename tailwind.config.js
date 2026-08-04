import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.ts',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                display: ['Fraunces', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                // Demera brand palette — shared tokens, used differently per business line:
                // Living leans on terracotta + beige (warm/homey), Fashion leans on charcoal + cream (editorial/minimal).
                cream: {
                    50: '#fefdfb',
                    100: '#fdfaf5',
                    200: '#faf3e7',
                    300: '#f5ead4',
                    400: '#efdfbd',
                    500: '#e8d2a3',
                },
                beige: {
                    50: '#f8f4ee',
                    100: '#f0e8db',
                    200: '#e3d3ba',
                    300: '#d3ba97',
                    400: '#c2a279',
                    500: '#ab8a61',
                },
                charcoal: {
                    50: '#f4f4f5',
                    100: '#e3e3e5',
                    200: '#c2c2c6',
                    300: '#94949b',
                    400: '#5f5f68',
                    500: '#3a3a41',
                    600: '#2a2a30',
                    700: '#1f1f24',
                    800: '#16161a',
                    900: '#0e0e11',
                },
                terracotta: {
                    50: '#fdf3ee',
                    100: '#fbe3d6',
                    200: '#f5c2a8',
                    300: '#ec9a72',
                    400: '#df7548',
                    500: '#c85a2e',
                    600: '#a84623',
                    700: '#83371c',
                },
            },
            boxShadow: {
                soft: '0 2px 20px -4px rgb(0 0 0 / 0.08)',
                card: '0 8px 30px -8px rgb(0 0 0 / 0.12)',
            },
            borderRadius: {
                xl2: '1.25rem',
            },
        },
    },

    plugins: [forms, typography],
};
