import { Navigate } from 'react-router-dom';
import { RequireRrCompany } from './components/RequireRrCompany';
import { DashboardPage } from './screens/DashboardPage';
import { LoginPage } from './screens/LoginPage';
import { OrdersInProgressPage } from './screens/OrdersInProgressPage';
import { OrdersCompletedPage } from './screens/OrdersCompletedPage';
import { UsersPage } from './screens/UsersPage';
import { CompaniesPage } from './screens/CompaniesPage';
import { CompanyDetailPage } from './screens/CompanyDetailPage';
import { CouponsPage } from './screens/CouponsPage';
import { CommissionsPage } from './screens/CommissionsPage';
import { OrderCreatePage } from './screens/OrderCreatePage';
import { OrderBulkImportPage } from './screens/OrderBulkImportPage';
import { OrderDetailPage } from './screens/OrderDetailPage';
import { OrderEditPage } from './screens/OrderEditPage';
import { OrderPaymentPage } from './screens/OrderPaymentPage';
import { ApiDocsPage } from './screens/ApiDocsPage';
import { SettingsThemePage } from './screens/SettingsThemePage';
import { SettingsPricesPage } from './screens/SettingsPricesPage';
import { PlaceholderPage } from './screens/PlaceholderPage';

export const routes = [
  { path: '/', element: <Navigate to="/dashboard" replace /> },
  { path: '/login', element: <LoginPage /> },
  { path: '/dashboard', element: <DashboardPage /> },
  { path: '/orders/in-progress', element: <OrdersInProgressPage /> },
  { path: '/orders/completed', element: <OrdersCompletedPage /> },
  { path: '/orders/new', element: <OrderCreatePage /> },
  { path: '/orders/bulk', element: <OrderBulkImportPage /> },
  { path: '/orders/:itemId', element: <OrderDetailPage /> },
  { path: '/orders/:itemId/edit', element: <OrderEditPage /> },
  { path: '/orders/:itemId/payment', element: <OrderPaymentPage /> },
  { path: '/users', element: <UsersPage /> },
  {
    path: '/companies/:companyId',
    element: (
      <RequireRrCompany>
        <CompanyDetailPage />
      </RequireRrCompany>
    ),
  },
  {
    path: '/companies',
    element: (
      <RequireRrCompany>
        <CompaniesPage />
      </RequireRrCompany>
    ),
  },
  {
    path: '/coupons',
    element: (
      <RequireRrCompany>
        <CouponsPage />
      </RequireRrCompany>
    ),
  },
  {
    path: '/commissions',
    element: (
      <RequireRrCompany>
        <CommissionsPage />
      </RequireRrCompany>
    ),
  },
  { path: '/settings/theme', element: <SettingsThemePage /> },
  { path: '/settings/prices', element: <SettingsPricesPage /> },
  { path: '/api-docs', element: <ApiDocsPage /> },
];

