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
                bgcolor: {
                    light: '#ffffff', // white
                    dark: '#18181b', // Tailwind's gray-900
                },
                navcolor: {
                    light: '#f9fafb', // Tailwind's gray-50
                    dark: '#27272a',  // Tailwind's gray-800
                },
                bgcontentcolor: {
                    light: '#f9fafb', // Tailwind's gray-50
                    dark: '#27272a',  // Tailwind's gray-800
                }
            }
        },
    },

    plugins: [forms],
};
