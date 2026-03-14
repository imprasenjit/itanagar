# Tailwind CSS Cheatsheet

## Breakpoints (mobile-first)

| Prefix | Min-width | Target |
|---|---|---|
| *(none)* | 0px | Mobile (default) |
| `sm:` | 640px | Large phones |
| `md:` | 768px | Tablets |
| `lg:` | 1024px | Laptops |
| `xl:` | 1280px | Desktops |
| `2xl:` | 1536px | Wide screens |

## Spacing Scale (most used)

| Class | Value |
|---|---|
| `p-1` / `m-1` | 4px |
| `p-2` / `m-2` | 8px |
| `p-3` / `m-3` | 12px |
| `p-4` / `m-4` | 16px |
| `p-5` / `m-5` | 20px |
| `p-6` / `m-6` | 24px |
| `p-8` / `m-8` | 32px |
| `p-10` / `m-10` | 40px |
| `p-12` / `m-12` | 48px |
| `p-16` / `m-16` | 64px |
| `gap-4` | 16px (flex/grid gap) |
| `space-y-4` | 16px vertical stack gap |

## Typography

| Class | Effect |
|---|---|
| `text-xs` | 12px |
| `text-sm` | 14px |
| `text-base` | 16px |
| `text-lg` | 18px |
| `text-xl` | 20px |
| `text-2xl` | 24px |
| `text-3xl` | 30px |
| `text-4xl` | 36px |
| `font-normal` | 400 |
| `font-medium` | 500 |
| `font-semibold` | 600 |
| `font-bold` | 700 |
| `tracking-tight` | -0.025em letter-spacing |
| `leading-tight` | 1.25 line-height |
| `leading-relaxed` | 1.625 line-height |
| `truncate` | overflow ellipsis on 1 line |
| `line-clamp-2` | clamp to 2 lines |

## Colors (semantic usage)

| Use | Classes |
|---|---|
| Page bg | `bg-gray-50` |
| Card / surface | `bg-white` |
| Primary text | `text-gray-900` |
| Secondary text | `text-gray-500` |
| Muted / placeholder | `text-gray-400` |
| Border | `border-gray-200` |
| Subtle border | `border-gray-100` |
| Primary action | `bg-blue-600` / `hover:bg-blue-700` |
| Destructive | `bg-red-600` / `hover:bg-red-700` |
| Success badge | `bg-green-100 text-green-800` |
| Warning badge | `bg-yellow-100 text-yellow-800` |
| Error badge | `bg-red-100 text-red-800` |
| Info badge | `bg-blue-100 text-blue-800` |
| Neutral badge | `bg-gray-100 text-gray-600` |

## Flexbox Quick Reference

```
flex              → display: flex
flex-col          → flex-direction: column
items-center      → align-items: center
items-start       → align-items: flex-start
justify-between   → justify-content: space-between
justify-center    → justify-content: center
justify-end       → justify-content: flex-end
flex-1            → flex: 1 1 0% (fill remaining space)
flex-wrap         → flex-wrap: wrap
flex-shrink-0     → flex-shrink: 0 (don't shrink sidebar/icon)
gap-4             → gap: 16px
```

## Grid Quick Reference

```
grid               → display: grid
grid-cols-1        → single column
grid-cols-2        → two equal columns
grid-cols-3        → three equal columns
grid-cols-12       → 12-column system
col-span-2         → spans 2 columns
gap-4              → 16px gap
auto-rows-fr       → equal row heights
```

## Sizing

```
w-full    → 100%
w-screen  → 100vw
h-full    → 100%
h-screen  → 100vh
min-h-screen   → min-height: 100vh
max-w-sm       → 384px
max-w-md       → 448px
max-w-lg       → 512px
max-w-xl       → 576px
max-w-2xl      → 672px
max-w-4xl      → 896px
max-w-7xl      → 1280px (standard page container)
```

## Borders & Radius

```
rounded-sm      → 2px
rounded         → 4px
rounded-md      → 6px
rounded-lg      → 8px
rounded-xl      → 12px
rounded-2xl     → 16px
rounded-full    → 9999px (circles, pills)

border          → 1px solid (use with border-{color})
border-2        → 2px
border-t        → top border only
border-b        → bottom border only
divide-y        → borders between children (vertical stack)
divide-gray-100 → color for divide borders
```

## Shadows

```
shadow-sm      → subtle (cards, inputs)
shadow         → default (dropdowns)
shadow-md      → medium (modals)
shadow-lg      → large (overlays)
shadow-xl      → extra (elevated panels)
shadow-none    → remove shadow
```

## Position & Z-index

```
relative       → position: relative
absolute       → position: absolute
fixed          → position: fixed
sticky         → position: sticky
inset-0        → top/right/bottom/left: 0 (fullscreen overlay)
top-0 z-40     → sticky navbar pattern
z-50           → modal overlay
```

## Transitions & Animation

```
transition-colors      → animate color/bg/border changes
transition-all         → animate all properties
duration-150           → 150ms
duration-200           → 200ms (default sweet spot)
ease-in-out            → smooth both ways

animate-pulse          → loading skeleton
animate-spin           → spinner
animate-bounce         → attention grab
```

## Focus & Interactive States

```
hover:bg-gray-100          → hover background
hover:text-gray-900        → hover text
focus:outline-none         → remove default outline
focus:ring-2               → 2px ring
focus:ring-blue-500        → ring color
focus:ring-offset-2        → ring offset (gap between element and ring)
active:scale-95            → press effect
disabled:opacity-50        → dim when disabled
disabled:cursor-not-allowed
cursor-pointer             → hand cursor
```

## Dark Mode

Enable in `tailwind.config.js`: `darkMode: 'class'`

```
dark:bg-gray-900       → dark bg
dark:text-white        → dark text
dark:border-gray-700   → dark border
dark:bg-gray-800       → dark card
```

Toggle with `document.documentElement.classList.toggle('dark')`.

## Common Class Combos

### Overlay / backdrop
```
fixed inset-0 z-50 bg-black/50 backdrop-blur-sm
```

### Center content absolutely
```
absolute inset-0 flex items-center justify-center
```

### Truncate long text 1 line
```
truncate
```

### Pill badge
```
inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
```

### Icon button
```
rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors
```

### Divider
```
<div className="border-t border-gray-100" />
```

### Container / page wrapper
```
mx-auto max-w-7xl px-4 sm:px-6 lg:px-8
```
