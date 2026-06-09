import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50:  '#e6f4fb',
                    100: '#cce9f7',
                    200: '#99d3ef',
                    300: '#66bce7',
                    400: '#33a6df',
                    500: '#008ddc',
                    600: '#0077bf',
                    700: '#005f99',
                    800: '#004773',
                    900: '#002f4d',
                    DEFAULT: '#008ddc',
                },
                accent: {
                    50:  '#fff4e0',
                    100: '#ffe9c2',
                    200: '#ffd285',
                    300: '#ffbc47',
                    400: '#ffa61a',
                    500: '#fea000',
                    600: '#d68700',
                    700: '#a86a00',
                    800: '#7a4d00',
                    900: '#4d3000',
                    DEFAULT: '#fea000',
                },
            },
            boxShadow: {
                soft: '0 2px 12px rgba(0, 141, 220, 0.08)',
                card: '0 4px 24px rgba(15, 23, 42, 0.06)',
            },
        },
    },

    plugins: [forms],
};
