import { useEffect, useState } from 'react';
import { getFaq } from '../api';
import LoadingSpinner from '../components/LoadingSpinner';

function FaqItem({ item }: { item: Record<string, string> }) {
  const [open, setOpen] = useState(false);
  return (
    <div className="card overflow-hidden">
      <button onClick={() => setOpen(o => !o)}
        className="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-white/3 transition-colors">
        <span className="text-sm font-semibold text-white pr-4">{item.question || item.title}</span>
        <svg className={`w-4 h-4 text-gray-400 shrink-0 transition-transform ${open ? 'rotate-180' : ''}`}
          fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7"/>
        </svg>
      </button>
      {open && (
        <div className="px-5 pb-4 text-sm text-gray-400 leading-relaxed border-t border-white/5">
          <p className="pt-3" dangerouslySetInnerHTML={{ __html: item.answer || item.description }}/>
        </div>
      )}
    </div>
  );
}

export default function FAQ() {
  const [faqs, setFaqs]     = useState<Record<string, string>[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getFaq().then(r => setFaqs(r.data.data || [])).catch(() => {}).finally(() => setLoading(false));
  }, []);

  return (
    <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-20">
      <div className="text-center mb-10">
        <p className="text-xs text-brand-400 font-semibold uppercase tracking-widest mb-1">Help Center</p>
        <h1 className="font-display font-bold text-3xl text-white">Frequently Asked Questions</h1>
      </div>
      {loading ? (
        <LoadingSpinner size="lg" text="Loading FAQs…"/>
      ) : faqs.length === 0 ? (
        <p className="text-center text-gray-500">No FAQs available yet.</p>
      ) : (
        <div className="space-y-2">
          {faqs.map(f => <FaqItem key={f.id} item={f}/>)}
        </div>
      )}
    </div>
  );
}
