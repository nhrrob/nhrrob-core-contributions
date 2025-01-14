/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./includes/**/*.{php,html,js}"],
  darkMode: 'class',
  theme: {
    extend: {},
  },
  plugins: [],
  corePlugins: {
    preflight: false,
  },
}

// Build : npx tailwindcss -i ./assets/css/admin.css -o ./assets/css/admin.out.css --watch