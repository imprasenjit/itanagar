---
name: frontend-design
description: 'Expert React.js + Tailwind CSS frontend design skill. Use when building React components, designing UIs, applying Tailwind utility classes, creating responsive layouts, designing dashboards, landing pages, forms, modals, cards, navbars, tables, or any web UI task. Triggers: React component, Tailwind CSS, UI design, responsive layout, component library, webapp design, frontend, design system, dark mode, animation, accessibility.'
argument-hint: 'Describe the UI to build, e.g. "pricing card", "admin dashboard layout", "responsive navbar with mobile menu"'
---

# Frontend Design — React + Tailwind CSS

Expert skill for designing and building polished, accessible, responsive web UIs using React.js and Tailwind CSS.

## When to Use

- Building new React components from scratch
- Styling existing components with Tailwind utilities
- Creating page layouts (dashboards, landing pages, auth pages)
- Designing reusable UI primitives (buttons, modals, cards, tables, forms)
- Implementing responsive designs (mobile-first)
- Adding dark mode, animations, or micro-interactions
- Reviewing or improving existing frontend code quality

## Core Principles

1. **Mobile-first** — start with `sm:` breakpoint, expand upward
2. **Composability** — small, single-responsibility components
3. **Accessibility** — semantic HTML, ARIA labels, keyboard navigation
4. **No inline styles** — Tailwind utilities only; avoid `style={{}}` unless dynamic values require it
5. **Consistent spacing** — use the Tailwind spacing scale (4, 8, 12, 16, 24, 32, 48, 64)
6. **Design tokens via config** — custom colors, fonts, and shadows go in `tailwind.config.js`

## Procedure

1. **Clarify the component's purpose** — what data does it receive? what interactions does it have?
2. **Choose the right pattern** — see [Component Patterns](./references/patterns.md)
3. **Pick a layout strategy** — Flexbox (`flex`) for 1D, Grid (`grid`) for 2D
4. **Apply spacing and typography** — see [Tailwind Cheatsheet](./references/tailwind-cheatsheet.md)
5. **Make it responsive** — verify `sm:`, `md:`, `lg:` breakpoints
6. **Add states** — `hover:`, `focus:`, `disabled:`, `active:` variants
7. **Check accessibility** — `aria-*`, `role`, focus ring (`focus:ring-2`)
8. **Extract reusable parts** — if a pattern repeats 3+ times, extract to a component

## Technology Stack Assumptions

| Layer | Choice |
|---|---|
| Framework | React 18+ (functional components + hooks) |
| Styling | Tailwind CSS v3+ |
| Icons | Heroicons (`@heroicons/react`) or Lucide React |
| State | `useState`, `useReducer`, Zustand (complex), React Query (server state) |
| Forms | React Hook Form + Zod validation |
| Animation | Tailwind `transition` / `animate-*`, or Framer Motion for complex |
| Routing | React Router v6 |

## Quick Patterns Reference

### Button variants
```jsx
// Primary
<button className="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
  Save
</button>

// Secondary / outline
<button className="px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
  Cancel
</button>

// Destructive
<button className="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors">
  Delete
</button>
```

### Card
```jsx
<div className="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
  {/* content */}
</div>
```

### Form field
```jsx
<div className="space-y-1">
  <label htmlFor="email" className="block text-sm font-medium text-gray-700">
    Email
  </label>
  <input
    id="email"
    type="email"
    className="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
    placeholder="you@example.com"
  />
</div>
```

### Responsive grid
```jsx
<div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
  {items.map(item => <Card key={item.id} {...item} />)}
</div>
```

### Badge / pill
```jsx
<span className="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
  Active
</span>
```

For comprehensive patterns, layouts, and design system guidance see:
- [Component Patterns](./references/patterns.md)
- [Tailwind Cheatsheet](./references/tailwind-cheatsheet.md)
- [Design System](./references/design-system.md)
