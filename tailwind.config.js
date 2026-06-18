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
                surface: {
                    DEFAULT: '#f8f9ff',
                    dim: '#cbdbf6',
                    bright: '#f8f9ff',
                    variant: '#d3e3ff',
                },
                'surface-container': {
                    lowest: '#ffffff',
                    low: '#eff4ff',
                    DEFAULT: '#e6eeff',
                    high: '#dde9ff',
                    highest: '#d3e3ff',
                },
                'on-surface': {
                    DEFAULT: '#0b1c30',
                    variant: '#59413a',
                },
                'inverse-surface': '#213146',
                'inverse-on-surface': '#ebf1ff',
                outline: {
                    DEFAULT: '#8d7168',
                    variant: '#e0bfb5',
                },
                'surface-tint': '#ab3502',
                primary: {
                    DEFAULT: '#802500',
                    container: '#a83300',
                    fixed: '#ffdbd0',
                    'fixed-dim': '#ffb59d',
                },
                'on-primary': '#ffffff',
                'on-primary-container': '#ffc9b8',
                'inverse-primary': '#ffb59d',
                'on-primary-fixed': '#390c00',
                'on-primary-fixed-variant': '#832600',
                secondary: {
                    DEFAULT: '#5d5e61',
                    container: '#e0dfe3',
                    fixed: '#e2e2e5',
                    'fixed-dim': '#c6c6c9',
                },
                'on-secondary': '#ffffff',
                'on-secondary-container': '#616265',
                'on-secondary-fixed': '#1a1c1e',
                'on-secondary-fixed-variant': '#45474a',
                tertiary: {
                    DEFAULT: '#673900',
                    container: '#894d00',
                    fixed: '#ffdcc0',
                    'fixed-dim': '#ffb875',
                },
                'on-tertiary': '#ffffff',
                'on-tertiary-container': '#ffca9c',
                'on-tertiary-fixed': '#2d1600',
                'on-tertiary-fixed-variant': '#6b3b00',
                error: {
                    DEFAULT: '#ba1a1a',
                    container: '#ffdad6',
                },
                'on-error': '#ffffff',
                'on-error-container': '#93000a',
                background: '#f8f9ff',
                'on-background': '#0b1c30',
                'zesty-orange': '#802500',
                'deep-charcoal': '#5d5e61',
                'amber-glow': '#673900',
                'input-fill': '#f8fafc',
                stripe: '#f1f5f9',
                'card-border': '#e2e8f0',
            },
            fontFamily: {
                sans: ['Work Sans', ...defaultTheme.fontFamily.sans],
                heading: ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
                body: ['Work Sans', ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                'headline-lg': ['48px', { lineHeight: '1.1', letterSpacing: '-0.02em', fontWeight: '800' }],
                'headline-lg-mobile': ['32px', { lineHeight: '1.2', letterSpacing: '-0.02em', fontWeight: '800' }],
                'headline-md': ['36px', { lineHeight: '1.2', letterSpacing: '-0.01em', fontWeight: '700' }],
                'headline-sm': ['24px', { lineHeight: '1.3', fontWeight: '700' }],
                'body-lg': ['18px', { lineHeight: '1.6', fontWeight: '400' }],
                'body-md': ['16px', { lineHeight: '1.5', fontWeight: '400' }],
                'body-sm': ['14px', { lineHeight: '1.4', fontWeight: '400' }],
                'label-caps': ['12px', { lineHeight: '1', letterSpacing: '0.05em', fontWeight: '600' }],
            },
            borderRadius: {
                sm: '0.25rem',
                DEFAULT: '0.5rem',
                md: '0.75rem',
                lg: '1rem',
                xl: '1.5rem',
            },
            spacing: {
                gutter: '24px',
                margin: '32px',
            },
            maxWidth: {
                content: '1200px',
            },
            boxShadow: {
                card: '0 0 0 1px #e2e8f0',
                'card-hover': '0px 4px 20px rgba(0, 0, 0, 0.05)',
                overlay: '0 10px 40px rgba(11, 28, 48, 0.12)',
                'button-hover': '0 2px 0 0 #5a1c00',
            },
        },
    },

    plugins: [forms],
};
