/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{js,jsx,ts,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          50: '#fff1f2',
          100: '#ffd6d8',
          200: '#ffadb1',
          300: '#ff7b82',
          400: '#f94d57',
          500: '#e11d26',   // primary CTA
          600: '#ba141c',   // gradient end
          700: '#8b0f14',   // close to logo #870f0f
          800: '#6e0c10',
          900: '#520a0d',
        },
        dark: {
          900: '#0b0808',   // near-black, warm tint
          800: '#130d0d',
          700: '#1c1212',
          600: '#221515',
          500: '#2e1a1a',
        },
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
        display: ['"Exo 2"', 'Inter', 'sans-serif'],
      },
      backgroundImage: {
        'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
        'hero-pattern': 'linear-gradient(135deg, #0b0808 0%, #1c1212 50%, #221515 100%)',
      },
      animation: {
        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
        'float': 'float 6s ease-in-out infinite',
        'shimmer': 'shimmer 2s linear infinite',
      },
      keyframes: {
        float: {
          '0%, 100%': { transform: 'translateY(0px)' },
          '50%': { transform: 'translateY(-20px)' },
        },
        shimmer: {
          '0%': { backgroundPosition: '-200% 0' },
          '100%': { backgroundPosition: '200% 0' },
        },
      },
    },
  },
  plugins: [],
}

