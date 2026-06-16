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
            colors: {
                primary: {
                    50: '#f0f9f0',
                    100: '#dcf2dd',
                    200: '#bce5be',
                    300: '#93d396',
                    400: '#8bd08e',
                    500: '#72c076',
                    600: '#4fb455',
                    700: '#327736',
                    800: '#2d5f32',
                    900: '#274f2b',
                },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
