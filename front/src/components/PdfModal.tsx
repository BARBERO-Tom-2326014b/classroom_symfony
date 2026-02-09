import React from 'react'

type Props = {
  title: string
  url: string
  onClose: () => void
}

export default function PdfModal({ title, url, onClose }: Props) {
  React.useEffect(() => {
    function onKeyDown(e: KeyboardEvent) {
      if (e.key === 'Escape') onClose()
    }
    window.addEventListener('keydown', onKeyDown)
    return () => window.removeEventListener('keydown', onKeyDown)
  }, [onClose])

  return (
    <div className="pdf-modal-backdrop" onMouseDown={onClose}>
      <div className="pdf-modal" onMouseDown={(e) => e.stopPropagation()}>
        <div className="pdf-modal-bar">
          <div style={{ fontWeight: 800 }}>{title}</div>
          <div style={{ display: 'flex', gap: 10, alignItems: 'center' }}>
            <a href={url} target="_blank" rel="noreferrer">Ouvrir dans un onglet</a>
            <button type="button" onClick={onClose}>Fermer</button>
          </div>
        </div>
        <iframe className="pdf-modal-iframe" src={url} title={title} />
      </div>
    </div>
  )
}
