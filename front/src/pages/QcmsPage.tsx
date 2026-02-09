import React from 'react'
import { apiJson } from '../lib/api'

type QcmListItem = {
  id: number
  title: string
}

type MeResponse = {
  id: number | null
  email: string
  roles: string[]
}

export default function QcmsPage() {
  const [me, setMe] = React.useState<MeResponse | null>(null)
  const [qcms, setQcms] = React.useState<QcmListItem[]>([])
  const [loading, setLoading] = React.useState(true)
  const [error, setError] = React.useState<string | null>(null)

  React.useEffect(() => {
    let cancelled = false

    async function load() {
      setLoading(true)
      setError(null)

      try {
        const user = await apiJson<MeResponse>('/api/me')
        if (cancelled) return
        setMe(user)

        // Si pas prof, on pourrait afficher un message. Ici on charge quand même, l'API est protégée.
        const items = await apiJson<QcmListItem[]>('/api/qcms')
        if (cancelled) return
        setQcms(items)
      } catch (e) {
        if (cancelled) return
        setError(e instanceof Error ? e.message : 'Erreur inconnue')

        // Si non connecté -> retour login
        // (apiJson lève une erreur sur 401)
        window.location.assign('/')
      } finally {
        if (!cancelled) setLoading(false)
      }
    }

    load()
    return () => {
      cancelled = true
    }
  }, [])

  return (
    <div style={{ maxWidth: 800, margin: '40px auto', fontFamily: 'system-ui, sans-serif' }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 }}>
        <h1 style={{ margin: 0 }}>Tous mes QCM</h1>
        {me && (
          <div style={{ color: '#666' }}>
            {me.email} ({me.roles.join(', ')})
          </div>
        )}
      </div>

      {loading && <div>Chargement…</div>}
      {error && <div style={{ color: 'crimson' }}>{error}</div>}

      {!loading && !error && qcms.length === 0 && (
        <div style={{ background: '#f6f6f6', padding: 12, borderRadius: 8 }}>
          Aucun QCM pour le moment.
        </div>
      )}

      {!loading && !error && qcms.length > 0 && (
        <ul style={{ listStyle: 'none', padding: 0, display: 'grid', gap: 10 }}>
          {qcms.map((q) => (
            <li key={q.id} style={{ border: '1px solid #e5e5e5', padding: 12, borderRadius: 8 }}>
              <div style={{ fontWeight: 700 }}>#{q.id} — {q.title}</div>
              <div style={{ marginTop: 8 }}>
                <a href={`/qcms/${q.id}`}>
                  Passer le QCM
                </a>
              </div>
            </li>
          ))}
        </ul>
      )}
    </div>
  )
}
