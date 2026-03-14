import { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { getPage } from '../api';
import LoadingSpinner from '../components/LoadingSpinner';

export default function CmsPage() {
  const { type }  = useParams();
  const [page, setPage]     = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    setLoading(true);
    getPage(type)
      .then(r => setPage(r.data.data || null))
      .catch(() => {})
      .finally(() => setLoading(false));
  }, [type]);

  if (loading) return <LoadingSpinner size="lg" text="Loading page…"/>;

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-20">
      {page ? (
        <>
          <h1 className="font-display font-bold text-3xl text-white mb-8">{page.title}</h1>
          <div className="card p-6 prose prose-invert prose-sm max-w-none
            prose-headings:font-display prose-a:text-brand-400
            prose-p:text-gray-400 prose-li:text-gray-400"
            dangerouslySetInnerHTML={{ __html: page.description || page.content }}
          />
        </>
      ) : (
        <div className="text-center py-24">
          <p className="text-4xl mb-3">📄</p>
          <p className="text-gray-500">Page not found.</p>
        </div>
      )}
    </div>
  );
}
