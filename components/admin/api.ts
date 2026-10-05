'use client';

/** JSON fetch helper for the admin. Throws Error(message) on any failure so forms can show it. */
export async function api<T = any>(path: string, init?: { method?: string; body?: unknown }): Promise<T> { // eslint-disable-line @typescript-eslint/no-explicit-any
  const res = await fetch(path, {
    method: init?.method ?? (init?.body ? 'POST' : 'GET'),
    headers: init?.body ? { 'Content-Type': 'application/json' } : undefined,
    body: init?.body ? JSON.stringify(init.body) : undefined,
    credentials: 'same-origin',
  });
  const data = await res.json().catch(() => ({}));
  if (!res.ok || data.ok === false) {
    if (res.status === 401 && !path.includes('/auth/')) window.location.href = '/admin/login';
    throw new Error(data.error || `Request failed (${res.status})`);
  }
  return data;
}
