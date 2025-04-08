/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./public/**/*.{html,js,php}",
    "./**/*.{html,js,php}",
    "./includes/**/*.php",
    "./assets/js/**/*.js"
  ],
  theme: {
    extend: {
      colors: {
        'rs-red': '#EE0000',
        'rs-blue': '#007CC3',
        'rs-gray': '#58595B',
        'rs-light-gray': '#F2F2F2',
        'rs-black': '#000000',
        'rs-white': '#FFFFFF',
      },
      fontFamily: {
        'sans': ['Arial', 'Helvetica', 'sans-serif'],
      }
    },
  },
  plugins: [],
}
