import React from 'react'
import { apiJson } from '../lib/api'
import TopBar from '../components/TopBar'
import VideoModal from '../components/VideoModal'
import { requireRole } from '../lib/auth'

type VideoItem = {
  id: number
  title: string | null
  description: string | null
  videoName: string | null
  teacherFirstName: string
  teacherLastName: string
}

type MeResponse = {
  id: number | null
  email: string
  roles: string[]
}

export default function VideosPage() {
  const [me, setMe] = React.useState<MeResponse | null>(null)
  const [videos, setVideos] = React.useState<VideoItem[]>([])
  const [loading, setLoading] = React.useState(true)
  const [error, setError] = React.useState<string | null>(null)
  const [videoToOpen, setVideoToOpen] = React.useState<{ title: string; url: string } | null>(null)

  const trackRef = React.useRef<HTMLDivElement | null>(null)

  function scrollByPage(dir: -1 | 1) {
    const el = trackRef.current
    if (!el) return
    const step = Math.max(200, el.clientWidth - 20)
    el.scrollBy({ left: dir * step, behavior: 'smooth' })
  }

  React.useEffect(() => {
    let cancelled = false

    async function load() {
      setLoading(true)
      setError(null)

      try {
        const user = await requireRole('ROLE_ETUDIANT', ['ROLE_USER'])
        if (cancelled) return
        setMe(user)

        const items = await apiJson<VideoItem[]>('/api/videos')
        if (cancelled) return
        setVideos(items)
      } catch {
        if (cancelled) return
        setError('Accès refusé')
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
      {videoToOpen && (
        <VideoModal title={videoToOpen.title} url={videoToOpen.url} onClose={() => setVideoToOpen(null)} />
      )}

      <TopBar title="Vidéos" />

      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 18 }}>
        <div>
          <div className="muted">Consulte les vidéos mises à disposition par tes professeurs.</div>
          <div style={{ marginTop: 8, display: 'flex', gap: 10, flexWrap: 'wrap' }}>
            <a href="/qcms" className="badge">← Retour QCM & Documents</a>
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
        <section className="card">
          <div className="card-body">
            <div className="carousel">
              <div className="carousel-header">
                <div style={{ display: 'flex', alignItems: 'baseline', gap: 10 }}>
                  <h2 style={{ margin: 0 }}>Vidéos</h2>
                  <span className="badge">{videos.length} au total</span>
                </div>
                <div className="carousel-actions">
                  <button type="button" className="carousel-icon-btn" onClick={() => scrollByPage(-1)} aria-label="Précédent">←</button>
                  <button type="button" className="carousel-icon-btn" onClick={() => scrollByPage(1)} aria-label="Suivant">→</button>
                </div>
              </div>

              {videos.length === 0 ? (
                <div className="list-item">Aucune vidéo pour le moment.</div>
              ) : (
                <div className="carousel-track" ref={trackRef}>
                  {videos.map((v) => {
                    const backendBase = 'http://0.0.0.0:8000'
                    const url = v.videoName ? `${backendBase}/uploads/videos/${v.videoName}` : null
                    const title = v.title || `Vidéo #${v.id}`
                    const teacher = `${v.teacherFirstName}${v.teacherLastName ? ' ' + v.teacherLastName : ''}`

                    return (
                      <div key={v.id} className="carousel-item">
                        <div className="list-item" style={{ padding: 0, overflow: 'hidden', height: '100%' }}>
                          {/* Miniature/preview vidéo */}
                          <div style={{ borderBottom: '1px solid rgba(255,255,255,0.10)', background: 'rgba(0,0,0,0.18)' }}>
                            {url ? (
                              <button
                                type="button"
                                onClick={() => setVideoToOpen({ title, url })}
                                style={{
                                  padding: 0,
                                  width: '100%',
                                  border: 'none',
                                  background: 'transparent',
                                  borderRadius: 0,
                                  boxShadow: 'none',
                                  cursor: 'pointer',
                                }}
                                aria-label={`Lire la vidéo ${title}`}
                              >
                                <video
                                  preload="metadata"
                                  muted
                                  playsInline
                                  style={{ width: '100%', height: 180, objectFit: 'contain', display: 'block', background: 'black' }}
                                >
                                  <source src={url} type="video/mp4" />
                                  Votre navigateur ne supporte pas la vidéo.
                                </video>
                              </button>
                            ) : (
                              <div style={{ height: 180, display: 'grid', placeItems: 'center' }} className="muted">
                                Indisponible
                              </div>
                            )}
                          </div>

                          <div style={{ padding: 12 }}>
                            <div style={{ fontWeight: 900 }}>{title}</div>
                            <div className="muted" style={{ fontSize: 12, marginTop: 6 }}>Prof: {teacher}</div>
                            <div className="muted" style={{ fontSize: 13, marginTop: 6 }}>{v.description || 'Vidéo'}</div>

                            <div style={{ marginTop: 10, display: 'flex', gap: 12, flexWrap: 'wrap' }}>
                              {url ? (
                                <>
                                  <button type="button" onClick={() => setVideoToOpen({ title, url })}>Regarder</button>
                                  <a href={url} target="_blank" rel="noreferrer" className="muted">Télécharger</a>
                                </>
                              ) : (
                                <span className="muted">Indisponible</span>
                              )}
                            </div>
                          </div>
                        </div>
                      </div>
                    )
                  })}
                </div>
              )}
            </div>
          </div>
        </section>
      )}
    </div>
  )
}
