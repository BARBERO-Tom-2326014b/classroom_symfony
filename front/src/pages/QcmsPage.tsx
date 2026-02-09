import React from 'react'
import { apiJson } from '../lib/api'
import PdfModal from '../components/PdfModal'

type QcmListItem = {
  id: number
  title: string
}

type Attempt = {
  id: number
  qcmId: number
  score: number
  total: number
  submittedAt: string
}

type DocumentItem = {
  id: number
  title: string
  description: string | null
  pdfName: string | null
  autor: string | null
}

type MeResponse = {
  id: number | null
  email: string
  roles: string[]
}

export default function QcmsPage() {
  const [me, setMe] = React.useState<MeResponse | null>(null)
  const [qcms, setQcms] = React.useState<QcmListItem[]>([])
  const [attempts, setAttempts] = React.useState<Record<number, Attempt>>({})
  const [documents, setDocuments] = React.useState<DocumentItem[]>([])
  const [loading, setLoading] = React.useState(true)
  const [error, setError] = React.useState<string | null>(null)
  const [pdfToOpen, setPdfToOpen] = React.useState<{ title: string; url: string } | null>(null)

  React.useEffect(() => {
    let cancelled = false

    async function load() {
      setLoading(true)
      setError(null)

      try {
        const user = await apiJson<MeResponse>('/api/me')
        if (cancelled) return
        setMe(user)

        const [qcmItems, attemptItems, docItems] = await Promise.all([
          apiJson<QcmListItem[]>('/api/qcms'),
          apiJson<Attempt[]>('/api/my/qcm-attempts'),
          apiJson<DocumentItem[]>('/api/documents'),
        ])
        if (cancelled) return

        setQcms(qcmItems)
        setDocuments(docItems)

        const map: Record<number, Attempt> = {}
        for (const a of attemptItems) {
          map[a.qcmId] = a
        }
        setAttempts(map)
      } catch (e) {
        if (cancelled) return
        setError(e instanceof Error ? e.message : 'Erreur inconnue')
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
    <div className="app-shell">
      {pdfToOpen && (
        <PdfModal
          title={pdfToOpen.title}
          url={pdfToOpen.url}
          onClose={() => setPdfToOpen(null)}
        />
      )}

      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 18 }}>
        <div>
          <h1 style={{ margin: 0 }}>Espace QCM & Documents</h1>
          <div className="muted" style={{ marginTop: 4 }}>
            Passe tes QCM (1 seule tentative) et consulte tes documents.
          </div>
        </div>
        {me && (
          <div style={{ fontSize: 14, textAlign: 'right' }}>
            <div style={{ fontWeight: 800 }}>{me.email}</div>
            <div className="muted">{me.roles.join(', ')}</div>
          </div>
        )}
      </div>

      {loading && <div className="muted">Chargement…</div>}
      {error && <div className="badge badge-danger">{error}</div>}

      {!loading && !error && (
        <div className="grid-2">
          <section className="card">
            <div className="card-header">
              <h2 style={{ margin: 0 }}>QCM</h2>
              <span className="badge">{qcms.length} au total</span>
            </div>
            <div className="card-body">
              {qcms.length === 0 ? (
                <div className="list-item">Aucun QCM pour le moment.</div>
              ) : (
                <ul className="list">
                  {qcms.map((q) => {
                    const attempt = attempts[q.id]
                    const done = Boolean(attempt)

                    return (
                      <li key={q.id} className="list-item">
                        <div style={{ fontWeight: 900 }}>#{q.id} — {q.title}</div>

                        {done ? (
                          <div style={{ marginTop: 10, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                            <span className="badge badge-success">
                              Terminé — Score {attempt.score}/{attempt.total}
                            </span>
                            <span className="muted" style={{ fontSize: 12 }}>
                              {new Date(attempt.submittedAt).toLocaleString()}
                            </span>
                          </div>
                        ) : (
                          <div style={{ marginTop: 10, display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                            <a href={`/qcms/${q.id}`} style={{ fontWeight: 800 }}>
                              Passer le QCM
                            </a>
                            <span className="badge">1 tentative max</span>
                          </div>
                        )}
                      </li>
                    )
                  })}
                </ul>
              )}
            </div>
          </section>

          <section className="card">
            <div className="card-header">
              <h2 style={{ margin: 0 }}>Documents</h2>
              <span className="badge">{documents.length} au total</span>
            </div>
            <div className="card-body">
              {documents.length === 0 ? (
                <div className="list-item">Aucun document pour le moment.</div>
              ) : (
                <ul className="list">
                  {documents.map((d) => {
                    // IMPORTANT: /uploads/... est servi par le backend. En dev, Vite proxy ne gère pas ça.
                    // Donc on pointe directement vers le backend.
                    const backendBase = 'http://0.0.0.0:8000'
                    const url = d.pdfName ? `${backendBase}/uploads/documents/${d.pdfName}` : null

                    return (
                      <li key={d.id} className="list-item">
                        <div style={{ fontWeight: 900 }}>{d.title}</div>
                        <div className="muted" style={{ fontSize: 13, marginTop: 4 }}>
                          {d.description || 'PDF'}
                        </div>
                        {d.autor && (
                          <div className="muted" style={{ fontSize: 12, marginTop: 6 }}>Auteur: {d.autor}</div>
                        )}

                        <div style={{ marginTop: 10, display: 'flex', gap: 12, flexWrap: 'wrap' }}>
                          {url ? (
                            <>
                              <button type="button" onClick={() => setPdfToOpen({ title: d.title, url })}>
                                Ouvrir le PDF
                              </button>
                              <a href={url} target="_blank" rel="noreferrer" className="muted">
                                Télécharger
                              </a>
                            </>
                          ) : (
                            <span className="muted">Indisponible</span>
                          )}
                        </div>
                      </li>
                    )
                  })}
                </ul>
              )}
            </div>
          </section>
        </div>
      )}
    </div>
  )
}
