export const appConfig = {
  tenantMode: "partner", // "partner" | "platform"
  apiDelay: 500,
  defaultTheme: "light",
  version: "1.0.0"
};

export const navigationConfig = [
  {
    id: "dashboard",
    label: "Dashboard",
    icon: "layout-grid",
    path: "/frontend/index.html",
    requiresPlatform: false
  },
  {
    id: "orders-inprogress",
    label: "In Progress Orders",
    icon: "refresh-cw",
    path: "/frontend/pages/orders-inprogress.html",
    requiresPlatform: false
  },
  {
    id: "orders-completed",
    label: "Completed Orders",
    icon: "check-circle",
    path: "/frontend/pages/orders-completed.html",
    requiresPlatform: false
  },
  {
    id: "users",
    label: "Users",
    icon: "users",
    path: "/frontend/pages/users.html",
    requiresPlatform: false
  },
  {
    id: "companies",
    label: "Companies",
    icon: "building-2",
    path: "/frontend/pages/companies.html",
    requiresPlatform: false
  },
  {
    id: "coupons",
    label: "Coupon",
    icon: "ticket",
    path: "/frontend/pages/coupons.html",
    requiresPlatform: false
  },
  {
    id: "commissions",
    label: "Commission",
    icon: "banknote",
    path: "/frontend/pages/commissions.html",
    requiresPlatform: false
  },
  {
    id: "prices",
    label: "Price Settings",
    icon: "dollar-sign",
    path: "/frontend/pages/settings-prices.html",
    requiresPlatform: false
  },
  {
    id: "settings",
    label: "Settings",
    icon: "settings",
    path: "/frontend/pages/settings-theme.html",
    requiresPlatform: false,
    group: "System"
  },
  {
    id: "api",
    label: "API Integration",
    icon: "code",
    path: "/frontend/pages/api-docs.html",
    requiresPlatform: false,
    group: "System"
  }
];
