/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./**/*.php",
    "./admin/**/*.php",
    "./includes/**/*.php",
    "./js/**/*.js",
  ],
  theme: {
    extend: {},
  },
  plugins: [require("daisyui")],
};
