export default function LoadingSpinner({ size = 'md', text = '' }) {
  const sizes = { sm: 'w-5 h-5', md: 'w-8 h-8', lg: 'w-12 h-12' };
  return (
    <div className="flex flex-col items-center justify-center gap-3 py-12">
      <div className={`${sizes[size]} border-[3px] border-white/10 border-t-brand-500 rounded-full animate-spin`}/>
      {text && <p className="text-sm text-gray-500">{text}</p>}
    </div>
  );
}

export function SkeletonCard() {
  return (
    <div className="card overflow-hidden animate-pulse">
      <div className="h-48 bg-dark-600 shimmer"/>
      <div className="p-4 space-y-3">
        <div className="h-4 bg-dark-600 rounded-lg w-3/4 shimmer"/>
        <div className="h-3 bg-dark-600 rounded-lg w-1/2 shimmer"/>
        <div className="h-2 bg-dark-600 rounded-full shimmer"/>
        <div className="flex justify-between">
          <div className="h-5 bg-dark-600 rounded-lg w-1/4 shimmer"/>
          <div className="h-5 bg-dark-600 rounded-lg w-1/4 shimmer"/>
        </div>
      </div>
    </div>
  );
}
