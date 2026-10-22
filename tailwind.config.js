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
                papel: '#F4F0DF',
                marfil: '#FDFAF1',
                verde: {
                    50: '#EAF3EC',
                    100: '#D5E7D9',
                    200: '#ABCFB3',
                    300: '#77AE89',
                    400: '#478A5F',
                    500: '#2A6E46',
                    600: '#135E34',
                    700: '#06582F',
                    800: '#00552F',
                    900: '#003C21',
                    950: '#002B18',
                },
                oro: {
                    50: '#FDF8E9',
                    100: '#FBF0D1',
                    200: '#F8E1A3',
                    300: '#F5D172',
                    400: '#F3C24F',
                    500: '#F2B544',
                    600: '#DFA22D',
                    700: '#B9831D',
                    800: '#976615',
                    900: '#795210',
                },
                neutral: {
                    50: '#F8F6EC',
                    100: '#F1EEE0',
                    200: '#E5E1CD',
                    300: '#CCC7AE',
                    400: '#A6A58F',
                    500: '#787D6E',
                    600: '#60665A',
                    700: '#4C534A',
                    800: '#3A423B',
                    900: '#2B352E',
                    950: '#24352D',
                },
                gray: {
                    50: '#F8F6EC',
                    100: '#F1EEE0',
                    200: '#E5E1CD',
                    300: '#CCC7AE',
                    400: '#A6A58F',
                    500: '#787D6E',
                    600: '#60665A',
                    700: '#4C534A',
                    800: '#3A423B',
                    900: '#2B352E',
                    950: '#24352D',
                },
            },
        },
    },

    plugins: [forms, typography],
};
