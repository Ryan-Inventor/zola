import type { Config } from 'tailwindcss'

export default {
  content: [
    './components/**/*.{js,vue,ts}',
    './layouts/**/*.vue',
    './pages/**/*.vue',
    './plugins/**/*.{js,ts}',
    './app.vue',
  ],
  theme: {
    extend: {
      colors: {
        orange: '#F56001',
        ink: '#0A0A0A',
        slate: '#5C5C5E',
        mist: '#F4F4F5',
        success: '#1E8E5A',
        alert: '#D14343',
        info: '#B8860B',
      },
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
      },
    },
  },
  plugins: [],
} satisfies Config
