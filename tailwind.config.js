/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#2563EB',
          dark: '#1D4ED8',
        },
        secondary: {
          DEFAULT: '#059669',
        },
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
} 