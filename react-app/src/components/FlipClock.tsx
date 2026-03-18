import { useState, useEffect, useRef, Fragment } from 'react';
import './FlipClock.css';

function FlipDigit({ value, label }: { value: number; label: string }) {
  const cur = String(value).padStart(2, '0');
  const prevRef = useRef(cur);
  const [anim, setAnim] = useState<{ from: string; to: string } | null>(null);

  useEffect(() => {
    if (cur !== prevRef.current) {
      setAnim({ from: prevRef.current, to: cur });
      prevRef.current = cur;
      const t = setTimeout(() => setAnim(null), 600);
      return () => clearTimeout(t);
    }
  }, [cur]);

  return (
    <div className="flip-unit-container">
      <div className="flip-unit">
        {/* Static layers always show current value */}
        <div className="segment top"><span>{cur}</span></div>
        <div className="segment bottom"><span>{cur}</span></div>

        {/* Animated overlays on value change */}
        {anim && (
          <>
            <div className="segment top flip-away" key={`a-${anim.from}-${anim.to}`}>
              <span>{anim.from}</span>
            </div>
            <div className="segment bottom flip-in" key={`b-${anim.from}-${anim.to}`}>
              <span>{anim.to}</span>
            </div>
          </>
        )}
      </div>
      <span className="flip-label">{label}</span>
    </div>
  );
}

export default function FlipClock({ countdown }: {
  countdown: { days: number; hours: number; minutes: number; seconds: number };
}) {
  const units: [number, string][] = [
    [countdown.days, 'Days'],
    [countdown.hours, 'Hrs'],
    [countdown.minutes, 'Min'],
    [countdown.seconds, 'Sec'],
  ];

  return (
    <div className="flip-clock">
      {units.map(([value, label], i) => (
        <Fragment key={label}>
          {i > 0 && <span className="flip-separator">:</span>}
          <FlipDigit value={value} label={label} />
        </Fragment>
      ))}
    </div>
  );
}
