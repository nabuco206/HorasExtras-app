import defaultTheme from 'tailwindcss/defaultTheme';
import flowbite from 'flowbite/plugin';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './node_modules/flowbite/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Instrument Sans', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [
        flowbite
    ],
};
