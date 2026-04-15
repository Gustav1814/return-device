import { createBrowserRouter, Navigate, RouterProvider } from 'react-router-dom';
import { AppShell } from './components/AppShell';
import { RequireSaasAuth } from './components/RequireSaasAuth';
import { ToastProvider } from './context/ToastContext';
import { PublicOrderPage } from './screens/PublicOrderPage';
import { routes } from './routes';
import { getSaasBasename } from './runtimeBase';

/**
 * Authenticated area uses a pathless parent so URLs like `/order` are not captured by the
 * dashboard layout (a parent with `path: '/'` can match `/order` and run RequireSaasAuth with no child).
 */
const authedChildren = routes
  .filter((r) => r.path !== '/login')
  .map((r) => {
    if (r.path === '/') {
      return { index: true as const, element: r.element };
    }
    const path = r.path.startsWith('/') ? r.path.slice(1) : r.path;
    return { path, element: r.element };
  });

const loginRoute = routes.find((r) => r.path === '/login');

const router = createBrowserRouter(
  [
    {
      path: 'login',
      element: loginRoute?.element ?? <Navigate to="/dashboard" replace />,
    },
    {
      path: 'order',
      element: <PublicOrderPage />,
    },
    {
      element: (
        <RequireSaasAuth>
          <AppShell />
        </RequireSaasAuth>
      ),
      children: authedChildren,
    },
  ],
  { basename: getSaasBasename() },
);

export default function App() {
  return (
    <ToastProvider>
      <RouterProvider router={router} />
    </ToastProvider>
  );
}
