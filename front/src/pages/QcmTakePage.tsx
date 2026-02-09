import React from 'react'
import { apiJson } from '../lib/api'
import TopBar from '../components/TopBar'

type Reponse = {
  id: number
  label: string
}

type Question = {
  id: number
  label: string
  reponses: Reponse[]
}

type Qcm = {
  id: number
  title: string
  questions: Question[]
}

type CorrectionItem = {
  questionId: number
  questionLabel: string
  selected: { id: number; label: string } | null
  correct: { id: number; label: string } | null
  isCorrect: boolean
}

type SubmitResponse = {
  attemptId: number
  score: number
  total: number
  correction?: CorrectionItem[]
}

type Attempt = {
  id: number
  qcmId: number
  score: number
  total: number
  submittedAt: string
}

function getQcmIdFromPath(pathname: string): number | null {
  // attend /qcms/:id
  const m = pathname.match(/^\/qcms\/(\d+)\/?$/)
  if (!m) return null
  const id = Number(m[1])
  return Number.isFinite(id) ? id : null
}

export default function QcmTakePage() {
  const qcmId = getQcmIdFromPath(window.location.pathname)

  const [qcm, setQcm] = React.useState<Qcm | null>(null)
  const [answers, setAnswers] = React.useState<Record<number, number>>({})
  const [submitting, setSubmitting] = React.useState(false)
  const [result, setResult] = React.useState<SubmitResponse | null>(null)
  const [alreadyDone, setAlreadyDone] = React.useState<Attempt | null>(null)
  const [error, setError] = React.useState<string | null>(null)

  React.useEffect(() => {
    let cancelled = false

    async function load() {
      setError(null)
      setResult(null)
      setAlreadyDone(null)

      if (!qcmId) {
        setError('URL invalide (id manquant).')
        return
      }

      try {
        // 1) chargement du QCM
        const data = await apiJson<Qcm>(`/api/qcms/${qcmId}`)
        if (cancelled) return
        setQcm(data)

        // 2) vérifier si déjà fait
        const attempts = await apiJson<Attempt[]>('/api/my/qcm-attempts')
        if (cancelled) return
        const existing = attempts.find((a) => a.qcmId === qcmId) || null
        if (existing) {
          setAlreadyDone(existing)
          setResult({ attemptId: existing.id, score: existing.score, total: existing.total })
        }
      } catch (e) {
        if (cancelled) return
        setError(e instanceof Error ? e.message : 'Erreur inconnue')
        window.location.assign('/')
      }
    }

    load()
    return () => {
      cancelled = true
    }
  }, [qcmId])

  function setAnswer(questionId: number, reponseId: number) {
    setAnswers((prev) => ({ ...prev, [questionId]: reponseId }))
  }

  async function onSubmit(e: React.FormEvent) {
    e.preventDefault()
    if (!qcmId) return
    if (alreadyDone) return

    setSubmitting(true)
    setError(null)

    try {
      const payload = { answers }
      const res = await fetch(`/api/qcms/${qcmId}/submit`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        credentials: 'include',
        body: JSON.stringify(payload),
      })

      if (!res.ok) {
        let msg = `Erreur (${res.status})`
        try {
          const data = await res.json()
          msg = data?.error ?? data?.message ?? msg
        } catch {
          // ignore
        }
        throw new Error(msg)
      }

      const data = (await res.json()) as SubmitResponse
      setResult(data)
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Erreur inconnue')
    } finally {
      setSubmitting(false)
    }
  }

  if (!qcmId) {
    return (
      <div style={{ maxWidth: 800, margin: '40px auto', fontFamily: 'system-ui, sans-serif' }}>
        <a href="/qcms">← Retour</a>
        <h1>Passer un QCM</h1>
        <div style={{ color: 'crimson' }}>URL invalide : id manquant.</div>
      </div>
    )
  }

  return (
    <div className="app-shell" style={{ maxWidth: 800 }}>
      <TopBar title={qcm ? qcm.title : 'Passer un QCM'} />

      <a href="/qcms">← Retour à la liste</a>

      {!qcm && !error && <div style={{ marginTop: 16 }}>Chargement…</div>}
      {error && <div style={{ marginTop: 16, color: 'crimson' }}>{error}</div>}

      {qcm && (
        <>

          {result ? (
            <div className="card" style={{ marginTop: 16 }}>
              <div className="card-body">
                <div style={{ fontWeight: 900, fontSize: 20 }}>
                  Score : {result.score} / {result.total}
                </div>
                {alreadyDone && (
                  <div className="muted" style={{ marginTop: 8 }}>
                    Vous avez déjà réalisé ce QCM le {new Date(alreadyDone.submittedAt).toLocaleString()}.
                  </div>
                )}

                {/* Correction */}
                {result.correction && result.correction.length > 0 && (
                  <div style={{ marginTop: 16 }}>
                    <h2 style={{ margin: '0 0 10px 0' }}>Correction</h2>
                    <div style={{ display: 'grid', gap: 10 }}>
                      {result.correction.map((c, idx) => (
                        <div key={c.questionId} className="list-item">
                          <div style={{ display: 'flex', justifyContent: 'space-between', gap: 12, alignItems: 'baseline' }}>
                            <div style={{ fontWeight: 900 }}>
                              {idx + 1}. {c.questionLabel}
                            </div>
                            <span className={c.isCorrect ? 'badge badge-success' : 'badge badge-danger'}>
                              {c.isCorrect ? 'Correct' : 'Faux'}
                            </span>
                          </div>

                          <div style={{ marginTop: 8, display: 'grid', gap: 6 }}>
                            <div className="muted">
                              Ta réponse : <span style={{ color: 'rgba(255,255,255,0.92)' }}>{c.selected?.label ?? '—'}</span>
                            </div>
                            <div className="muted">
                              Bonne réponse : <span style={{ color: 'rgba(255,255,255,0.92)' }}>{c.correct?.label ?? '—'}</span>
                            </div>
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                )}

                <div style={{ marginTop: 12 }}>
                  <a href="/qcms">Revenir à la liste</a>
                </div>
              </div>
            </div>
          ) : (
            <form onSubmit={onSubmit} style={{ display: 'grid', gap: 16, marginTop: 16 }}>
              {qcm.questions.map((question, index) => (
                <div key={question.id} style={{ border: '1px solid #e5e5e5', borderRadius: 8, padding: 12 }}>
                  <div style={{ fontWeight: 700 }}>
                    {index + 1}. {question.label}
                  </div>

                  <div style={{ display: 'grid', gap: 8, marginTop: 10 }}>
                    {question.reponses.map((rep) => {
                      const checked = answers[question.id] === rep.id
                      return (
                        <label key={rep.id} style={{ display: 'flex', gap: 10, alignItems: 'center', cursor: 'pointer' }}>
                          <input
                            type="radio"
                            name={`q_${question.id}`}
                            checked={checked}
                            onChange={() => setAnswer(question.id, rep.id)}
                          />
                          <span>{rep.label}</span>
                        </label>
                      )
                    })}
                  </div>
                </div>
              ))}

              <button type="submit" disabled={submitting}>
                {submitting ? 'Envoi…' : 'Valider et voir mon score'}
              </button>
            </form>
          )}
        </>
      )}
    </div>
  )
}
