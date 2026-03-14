# Component Patterns — React + Tailwind

## Component File Structure

```
src/
├── components/
│   ├── ui/              # Primitives: Button, Input, Modal, Badge, Avatar
│   ├── layout/          # Page shells: Sidebar, Navbar, PageHeader
│   ├── features/        # Domain-specific composite components
│   └── index.ts         # Re-exports
├── pages/               # Route-level page components
├── hooks/               # Custom hooks
└── lib/                 # Utilities, helpers, constants
```

## Functional Component Template

```jsx
import { useState } from 'react'
import PropTypes from 'prop-types'   // or use TypeScript

function ComponentName({ title, description, onAction, className = '' }) {
  const [isOpen, setIsOpen] = useState(false)

  return (
    <div className={`bg-white rounded-xl p-6 ${className}`}>
      <h2 className="text-lg font-semibold text-gray-900">{title}</h2>
      {description && (
        <p className="mt-1 text-sm text-gray-500">{description}</p>
      )}
    </div>
  )
}

export default ComponentName
```

---

## Layout Patterns

### App shell with sidebar
```jsx
<div className="flex h-screen bg-gray-50">
  {/* Sidebar */}
  <aside className="hidden w-64 flex-shrink-0 border-r border-gray-200 bg-white lg:flex lg:flex-col">
    <nav className="flex-1 space-y-1 px-3 py-4">
      {/* nav items */}
    </nav>
  </aside>

  {/* Main content */}
  <div className="flex flex-1 flex-col overflow-hidden">
    <header className="border-b border-gray-200 bg-white px-6 py-4">
      {/* top bar */}
    </header>
    <main className="flex-1 overflow-y-auto p-6">
      {/* page content */}
    </main>
  </div>
</div>
```

### Centered auth / marketing page
```jsx
<div className="flex min-h-screen items-center justify-center bg-gray-50 px-4 py-12">
  <div className="w-full max-w-md space-y-8">
    {/* content */}
  </div>
</div>
```

### Dashboard stats row
```jsx
<div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
  {stats.map(({ label, value, change, icon: Icon }) => (
    <div key={label} className="overflow-hidden rounded-2xl bg-white px-6 py-5 shadow-sm border border-gray-100">
      <div className="flex items-center gap-3">
        <div className="rounded-lg bg-blue-50 p-2">
          <Icon className="h-5 w-5 text-blue-600" />
        </div>
        <p className="text-sm font-medium text-gray-500">{label}</p>
      </div>
      <p className="mt-3 text-3xl font-bold tracking-tight text-gray-900">{value}</p>
      <p className="mt-1 text-xs text-gray-500">{change}</p>
    </div>
  ))}
</div>
```

---

## Modal Pattern

```jsx
import { useEffect } from 'react'

function Modal({ isOpen, onClose, title, children }) {
  // Close on Escape key
  useEffect(() => {
    function handleEsc(e) { if (e.key === 'Escape') onClose() }
    if (isOpen) document.addEventListener('keydown', handleEsc)
    return () => document.removeEventListener('keydown', handleEsc)
  }, [isOpen, onClose])

  if (!isOpen) return null

  return (
    // Backdrop
    <div
      className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
      onClick={onClose}
      aria-modal="true"
      role="dialog"
      aria-labelledby="modal-title"
    >
      {/* Panel — stop click propagating to backdrop */}
      <div
        className="w-full max-w-lg rounded-2xl bg-white shadow-xl"
        onClick={e => e.stopPropagation()}
      >
        <div className="flex items-center justify-between border-b border-gray-100 px-6 py-4">
          <h2 id="modal-title" className="text-lg font-semibold text-gray-900">{title}</h2>
          <button onClick={onClose} className="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
            <span className="sr-only">Close</span>
            ✕
          </button>
        </div>
        <div className="px-6 py-4">{children}</div>
      </div>
    </div>
  )
}
```

---

## Table Pattern

```jsx
<div className="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
  {/* Optional header */}
  <div className="flex items-center justify-between px-6 py-4 border-b border-gray-100">
    <h3 className="text-base font-semibold text-gray-900">Users</h3>
    <button className="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
      Add new
    </button>
  </div>

  <div className="overflow-x-auto">
    <table className="min-w-full divide-y divide-gray-100">
      <thead className="bg-gray-50">
        <tr>
          <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
          <th className="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
          <th className="relative px-6 py-3"><span className="sr-only">Actions</span></th>
        </tr>
      </thead>
      <tbody className="divide-y divide-gray-100 bg-white">
        {rows.map(row => (
          <tr key={row.id} className="hover:bg-gray-50 transition-colors">
            <td className="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{row.name}</td>
            <td className="whitespace-nowrap px-6 py-4">
              <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                ${row.active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500'}`}>
                {row.active ? 'Active' : 'Inactive'}
              </span>
            </td>
            <td className="whitespace-nowrap px-6 py-4 text-right text-sm">
              <button className="text-blue-600 hover:text-blue-800 font-medium">Edit</button>
            </td>
          </tr>
        ))}
      </tbody>
    </table>
  </div>
</div>
```

---

## Form Pattern (with React Hook Form)

```jsx
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'

const schema = z.object({
  name: z.string().min(2, 'Name must be at least 2 characters'),
  email: z.string().email('Invalid email address'),
})

function MyForm({ onSubmit }) {
  const { register, handleSubmit, formState: { errors, isSubmitting } } = useForm({
    resolver: zodResolver(schema),
  })

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
      <div>
        <label className="block text-sm font-medium text-gray-700 mb-1">Name</label>
        <input
          {...register('name')}
          className={`block w-full rounded-lg border px-3 py-2 text-sm
            focus:outline-none focus:ring-1
            ${errors.name
              ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
              : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500'
            }`}
        />
        {errors.name && <p className="mt-1 text-xs text-red-600">{errors.name.message}</p>}
      </div>
      <button
        type="submit"
        disabled={isSubmitting}
        className="w-full px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
      >
        {isSubmitting ? 'Saving…' : 'Save'}
      </button>
    </form>
  )
}
```

---

## Navigation Patterns

### Top navbar
```jsx
<nav className="sticky top-0 z-40 border-b border-gray-200 bg-white/80 backdrop-blur-md">
  <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
    <div className="flex items-center gap-8">
      <a href="/" className="text-xl font-bold text-gray-900">Logo</a>
      <div className="hidden items-center gap-1 md:flex">
        {navLinks.map(link => (
          <a key={link.href} href={link.href}
            className="rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors">
            {link.label}
          </a>
        ))}
      </div>
    </div>
    <div className="flex items-center gap-3">
      <button className="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">Log in</button>
      <button className="px-4 py-2 bg-blue-600 text-sm font-medium text-white rounded-lg hover:bg-blue-700">Sign up</button>
    </div>
  </div>
</nav>
```

---

## Skeleton / Loading Pattern
```jsx
function Skeleton({ className = '' }) {
  return <div className={`animate-pulse rounded bg-gray-200 ${className}`} />
}

// Usage
<div className="space-y-3">
  <Skeleton className="h-4 w-3/4" />
  <Skeleton className="h-4 w-1/2" />
  <Skeleton className="h-32 w-full" />
</div>
```

---

## Empty State Pattern
```jsx
<div className="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 bg-white px-6 py-16 text-center">
  <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
    <Icon className="h-6 w-6 text-gray-400" />
  </div>
  <h3 className="text-sm font-semibold text-gray-900">No items yet</h3>
  <p className="mt-1 text-sm text-gray-500">Get started by creating your first item.</p>
  <button className="mt-6 px-4 py-2 bg-blue-600 text-sm font-medium text-white rounded-lg hover:bg-blue-700">
    Create item
  </button>
</div>
```
