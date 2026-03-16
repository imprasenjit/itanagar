import { useEffect, useState, createContext, useContext, useCallback, ReactNode } from 'react';

type ToastType = 'success' | 'error' | 'info';
type AddToast = (message: string, type?: ToastType, duration?: number) => void;

interface Toast { id: number; message: string; type: ToastType; }

const ToastContext = createContext<AddToast | null>(null);

let _addToast: AddToast | null = null;

export function ToastProvider({ children }: { children: ReactNode }) {
  const [toasts, setToasts] = useState<Toast[]>([]);

  const addToast = useCallback((message: string, type: ToastType = 'info', duration = 4000) => {
    const id = Date.now();
    setToasts(t => [...t, { id, message, type }]);
    setTimeout(() => setToasts(t => t.filter(x => x.id !== id)), duration);
  }, []);

  useEffect(() => { _addToast = addToast; }, [addToast]);

  const icons: Record<ToastType, React.ReactElement> = {
    success: <svg className="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7"/></svg>,
    error:   <svg className="w-5 h-5 text-red-400 shrink-0"     fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12"/></svg>,
    info:    <svg className="w-5 h-5 text-blue-400 shrink-0"    fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>,
  };
  const borders: Record<ToastType, string> = { success: 'border-emerald-500/30', error: 'border-red-500/30', info: 'border-blue-500/30' };

  return (
    <ToastContext.Provider value={addToast}>
      {children}
      <div className="fixed bottom-4 right-4 z-[9999] flex flex-col gap-2 max-w-sm w-full pointer-events-none">
        {toasts.map(t => (
          <div key={t.id}
            className={`flex items-start gap-3 card p-3.5 border ${borders[t.type] || borders.info} shadow-xl pointer-events-auto animate-in slide-in-from-right-5`}>
            {icons[t.type] || icons.info}
            <p className="text-sm text-gray-700 flex-1">{t.message}</p>
            <button onClick={() => setToasts(ts => ts.filter(x => x.id !== t.id))}
              className="text-gray-400 hover:text-gray-700 transition-colors ml-1">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
        ))}
      </div>
    </ToastContext.Provider>
  );
}

export const useToast = () => useContext(ToastContext) as AddToast;

/** Call from anywhere (outside React tree) */
export const toast: AddToast = (message, type, duration) => _addToast?.(message, type, duration);
