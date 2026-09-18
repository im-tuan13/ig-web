import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                surface: '#faf9f9',
                'surface-container-low': '#f4f3f3',
                'surface-container-high': '#e8e8e8',
                'on-surface': '#1a1c1c',
                'on-surface-variant': '#444748',
                outline: '#747878',
                'outline-variant': '#c4c7c8',
                instagram: '#0095f6',
            },
            spacing: {
                'space-xxs': '4px',
                'space-xs': '8px',
                'space-sm': '12px',
                'space-md': '16px',
                'space-lg': '24px',
                'space-xl': '32px',
                'max-width-feed': '470px',
                'max-width-container': '935px',
            },
            fontSize: {
                'headline-lg': ['24px', { lineHeight: '32px', letterSpacing: '-0.02em', fontWeight: '600' }],
                'headline-md': ['20px', { lineHeight: '28px', letterSpacing: '-0.01em', fontWeight: '600' }],
                'body-md': ['14px', { lineHeight: '18px', fontWeight: '400' }],
                'body-sm': ['12px', { lineHeight: '16px', fontWeight: '400' }],
                'label-md': ['14px', { lineHeight: '18px', fontWeight: '600' }],
                'label-sm': ['12px', { lineHeight: '14px', letterSpacing: '0.02em', fontWeight: '600' }],
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
