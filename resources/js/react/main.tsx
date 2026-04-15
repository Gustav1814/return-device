import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import App from './App';
import { bootstrapDocumentTheme } from './theme/applyDocumentTheme';
import '../../css/react.css';
import '../../css/saas/tokens.css';
import '../../css/saas/components.css';
import '../../css/saas/pages.css';

bootstrapDocumentTheme();

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <App />
  </StrictMode>,
);
