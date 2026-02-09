import './App.css'
import LoginPage from './pages/LoginPage'
import QcmsPage from './pages/QcmsPage'
import QcmTakePage from './pages/QcmTakePage'

function App() {
  const path = window.location.pathname

  if (path === '/qcms') {
    return <QcmsPage />
  }

  if (path.startsWith('/qcms/')) {
    return <QcmTakePage />
  }

  return <LoginPage />
}

export default App
