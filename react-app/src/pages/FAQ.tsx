import { useState } from 'react';

const faqs = [
  {
    question: 'What is Itanagar Choice?',
    answer: 'Itanagar Choice is an online platform where users can easily purchase tickets for verified events and participate in promotional draw activities through a secure digital system.',
  },
  {
    question: 'How can I buy a ticket?',
    answer: 'You can purchase a ticket by selecting the event on <strong>theitanagarchoice.com</strong>, entering the required details, and completing the payment through the available payment methods.',
  },
  {
    question: 'How will I receive my ticket?',
    answer: 'After successful payment, your ticket will be automatically sent to your registered WhatsApp number or email address. You may also be able to view it on the website.',
  },
  {
    question: 'How can I check if my ticket is valid?',
    answer: 'You can verify your ticket by using the <strong>"Check Ticket"</strong> feature on the website and entering your ticket number.',
  },
  {
    question: 'Where can I see the results?',
    answer: 'Results of the draw or event will be published in the <strong>"Results"</strong> section of the website once the official draw is completed.',
  },
  {
    question: 'Is my payment secure?',
    answer: 'Yes. All payments are processed through secure payment gateways such as Razorpay to ensure safe and protected transactions.',
  },
  {
    question: 'Can I cancel or refund my ticket?',
    answer: 'Ticket cancellation or refund depends on the event policy. Please refer to the <a href="/refunds">Refund &amp; Cancellation Policy</a> on the website for more information.',
  },
  {
    question: 'What should I do if I do not receive my ticket?',
    answer: 'If you do not receive your ticket after payment:<ul class="mt-2 list-disc pl-5 space-y-1"><li>Check your email spam folder</li><li>Check your WhatsApp messages</li><li>Contact our support team for assistance</li></ul>',
  },
  {
    question: 'How can I contact support?',
    answer: 'You can contact the Itanagar Choice support team through the <a href="/contact">Contact Us</a> page on the website.',
  },
];

function FaqItem({ item }: { item: { question: string; answer: string } }) {
  const [open, setOpen] = useState(false);
  return (
    <div className="card overflow-hidden">
      <button onClick={() => setOpen(o => !o)}
        className="w-full flex items-center justify-between px-5 py-4 text-left hover:bg-gray-50 transition-colors">
        <span className="text-sm font-semibold text-gray-900 pr-4">{item.question}</span>
        <svg className={`w-4 h-4 text-gray-400 shrink-0 transition-transform ${open ? 'rotate-180' : ''}`}
          fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7"/>
        </svg>
      </button>
      {open && (
        <div className="px-5 pb-4 text-sm text-gray-600 leading-relaxed border-t border-gray-100">
          <p className="pt-3" dangerouslySetInnerHTML={{ __html: item.answer }}/>
        </div>
      )}
    </div>
  );
}

export default function FAQ() {
  return (
    <div className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-20">
      <div className="text-center mb-10">
        <p className="text-xs text-brand-600 font-semibold uppercase tracking-widest mb-1">Help Center</p>
        <h1 className="font-display font-bold text-3xl text-gray-900">Frequently Asked Questions</h1>
      </div>
      <div className="space-y-2">
        {faqs.map((f, i) => <FaqItem key={i} item={f}/>)}
      </div>
    </div>
  );
}
