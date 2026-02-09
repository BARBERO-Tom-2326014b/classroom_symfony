export async function apiFetch(input: RequestInfo | URL, init: RequestInit = {}) {
  const headers = new Headers(init.headers);

  // Par défaut on parle JSON côté API
  if (!headers.has('Accept')) headers.set('Accept', 'application/json');

  return fetch(input, {
    ...init,
    headers,
    // Important: on veut garder la session Symfony (cookie) en dev
    credentials: init.credentials ?? 'include',
  });
}

export async function apiJson<T>(input: RequestInfo | URL, init: RequestInit = {}): Promise<T> {
  const res = await apiFetch(input, init);

  if (!res.ok) {
    let msg = `Erreur API (${res.status})`;
    try {
      const data = await res.json();
      msg = (data && (data.message || data.error)) ?? msg;
    } catch {
      // ignore
    }
    throw new Error(msg);
  }

  return (await res.json()) as T;
}
