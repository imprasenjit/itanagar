# Design System Guidelines

## Visual Hierarchy

Use size + weight + color contrast together — never just one:

| Level | Example | Classes |
|---|---|---|
| Page title | H1 | `text-3xl font-bold tracking-tight text-gray-900` |
| Section heading | H2 | `text-xl font-semibold text-gray-900` |
| Card title | H3 | `text-base font-semibold text-gray-900` |
| Label | Form label | `text-sm font-medium text-gray-700` |
| Body | Paragraph | `text-sm text-gray-600` |
| Caption | Helper text | `text-xs text-gray-400` |

---

## Color Palette (Tailwind defaults, extend in config)

### Brand — Blue
| Role | Class |
|---|---|
| Primary button | `bg-blue-600` / `hover:bg-blue-700` |
| Link | `text-blue-600` / `hover:text-blue-800` |
| Subtle bg | `bg-blue-50` |
| Icon fg | `text-blue-600` |
| Focus ring | `focus:ring-blue-500` |

### Semantic
| State | Background | Text |
|---|---|---|
| Success | `bg-green-50` | `text-green-800` |
| Warning | `bg-yellow-50` | `text-yellow-800` |
| Error | `bg-red-50` | `text-red-700` |
| Info | `bg-blue-50` | `text-blue-800` |
| Neutral | `bg-gray-100` | `text-gray-600` |

### Gray scale usage
| Level | Token |
|---|---|
| Page background | `gray-50` |
| Panel / sidebar bg | `white` |
| Subtle section bg | `gray-50` |
| Card | `white` |
| Card border | `gray-100` |
| Input border | `gray-300` |
| Divider | `gray-200` |
| Disabled text | `gray-400` |
| Placeholder | `gray-400` |
| Secondary text | `gray-500` |
| Body text | `gray-600` |
| Heading | `gray-900` |

---

## Spacing Rhythm

Maintain consistent vertical rhythm using multiples of 4:

```
Section padding:   py-16 (64px)
Card padding:      p-6   (24px)
Form gap:          space-y-4 (16px)
Inline gap:        gap-3  (12px)
Tight inline:      gap-2  (8px)
Icon margin:       mr-2   (8px)
```

---

## Border Radius Scale

| Element | Radius |
|---|---|
| Buttons | `rounded-lg` |
| Cards | `rounded-2xl` |
| Input fields | `rounded-lg` |
| Modals, sheets | `rounded-2xl` |
| Badges, pills | `rounded-full` |
| Avatars | `rounded-full` |
| Tooltips | `rounded-md` |
| Images | `rounded-xl` |

---

## Shadow Conventions

| Element | Shadow |
|---|---|
| Card (flat design) | `shadow-sm border border-gray-100` |
| Card (elevation) | `shadow-md` |
| Dropdown | `shadow-lg ring-1 ring-black/5` |
| Modal | `shadow-xl` |
| Tooltip | `shadow-md` |

---

## Component Size Variants

### Button sizes
```
xs:  px-2.5 py-1.5  text-xs
sm:  px-3   py-2    text-sm
md:  px-4   py-2    text-sm    ← default
lg:  px-5   py-2.5  text-base
xl:  px-6   py-3    text-base
```

### Input sizes
```
sm:  px-2.5 py-1.5 text-sm
md:  px-3   py-2   text-sm    ← default
lg:  px-4   py-3   text-base
```

---

## Accessibility Checklist

- [ ] All interactive elements reachable by keyboard (`Tab`)
- [ ] Visible focus ring on all focusable elements (`focus:ring-2`)
- [ ] `aria-label` on icon-only buttons (`<button aria-label="Close">`)
- [ ] `aria-labelledby` on modals and dialogs
- [ ] `role="dialog"` + `aria-modal="true"` on modals
- [ ] Form `<input>` linked to `<label>` via `id`/`htmlFor`
- [ ] Error messages have `role="alert"` or are linked via `aria-describedby`
- [ ] Color contrast ratio ≥ 4.5:1 for normal text, ≥ 3:1 for large text
- [ ] No information conveyed by color alone (add icon or text)
- [ ] `alt` text on all `<img>` elements

---

## Animation Principles

1. **Speed**: UI transitions 150–200ms; content transitions 250–350ms
2. **Easing**: Use `ease-in-out` for most; `ease-out` for entering; `ease-in` for leaving
3. **Purpose**: Animate only to communicate state change or guide attention
4. **Reduce motion**: Always respect `prefers-reduced-motion`

```jsx
// Respect prefers-reduced-motion
<div className="transition-all duration-200 motion-reduce:transition-none">
```

### Common Framer Motion patterns
```jsx
// Fade in / slide up (page content)
const fadeUp = {
  hidden: { opacity: 0, y: 20 },
  visible: { opacity: 1, y: 0, transition: { duration: 0.3, ease: 'easeOut' } },
}

// Scale in (modal)
const scaleIn = {
  hidden: { opacity: 0, scale: 0.95 },
  visible: { opacity: 1, scale: 1, transition: { duration: 0.2, ease: 'easeOut' } },
}
```

---

## Responsive Design Checklist

- [ ] Test at 375px (iPhone SE), 768px (iPad), 1280px (laptop)
- [ ] No horizontal scroll on mobile
- [ ] Touch targets ≥ 44×44px on mobile
- [ ] Sidebar collapses to drawer/bottom-nav on mobile
- [ ] Tables scroll horizontally (`overflow-x-auto`) on mobile
- [ ] Multi-column grids reduce to 1 column on mobile
- [ ] Font sizes readable without zooming on mobile

---

## Tailwind Config Extensions

```js
// tailwind.config.js
module.exports = {
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        brand: {
          50:  '#eff6ff',
          100: '#dbeafe',
          500: '#3b82f6',
          600: '#2563eb',  // primary
          700: '#1d4ed8',
        },
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
      borderRadius: {
        '4xl': '2rem',
      },
      boxShadow: {
        'card': '0 1px 3px 0 rgb(0 0 0 / 0.05), 0 1px 2px -1px rgb(0 0 0 / 0.05)',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
    require('@tailwindcss/aspect-ratio'),
  ],
}
```
