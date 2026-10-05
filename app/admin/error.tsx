'use client';

// Shown when an admin page crashes (for example the database is unreachable) instead of a bare Cloudflare error.
export default function AdminError({ reset }: { error: Error; reset: () => void }) {
  return (
    <div className="ad-auth"><div className="ad-auth-box">
      <h1>Something went wrong</h1>
      <p className="ad-muted">The admin could not load this page. This is usually a temporary database connection problem.</p>
      <button className="ad-btn big" onClick={reset}>Try again</button>
      <p className="ad-muted small">If it keeps happening, open <a href="/api/health" style={{ textDecoration: 'underline' }}>/api/health</a> and send the message it shows.</p>
    </div></div>
  );
}
