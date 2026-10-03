import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'jg-blue': {
                    DEFAULT: '#161b97',
                    50: '#f0f2ff',
                    100: '#e0e4ff',
                    600: '#12167d',
                    700: '#0e1163',
                },
                'jg-red': {
                    DEFAULT: '#f30b1f',
                }
            }
        },
    },

    plugins: [forms, typography],
};
