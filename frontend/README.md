# Device Return - White-label B2B SaaS Frontend

This is a complete static frontend for **Device Return**, an IT asset and device return logistics platform.

## Features
- **White-labeling**: Dynamic branding via `company.json` (logo, colors, theme).
- **Multi-tenant**: Supports `partner` and `platform` operator modes.
- **Responsive Dashboard**: Modular widgets with visibility controls.
- **Dark/Light Mode**: Full theme support via CSS variables.
- **Interactive Charts**: Powered by Chart.js.
- **Mock API**: Local JSON data with simulated latency.

## Tech Stack
- **HTML5**: Semantic structure.
- **CSS3**: Modern tokens, CSS variables, and utility classes.
- **JS (ES Modules)**: Vanilla JavaScript for logic and interactivity.
- **Chart.js**: Data visualization.
- **Lucide Icons**: Clean, consistent iconography.

## Folder Structure
- `/assets/css/`: Tokens, component styles, and page-specific CSS.
- `/assets/js/`: Configuration, theme management, navigation, and chart logic.
- `/data/`: Mock JSON data for companies, dashboards, and orders.
- `/pages/`: Individual HTML screens.
- `/partials/`: Shared HTML components (sidebar, header, etc.).

## Usage
Open `index.html` in a browser to start. Use the `?brand=demo` query parameter to see different branding.
Toggle between `partner` and `platform` modes in `assets/js/app.config.js`.

## Mobile Support
Optimized for desktop (1280px+) down to tablet (768px). Mobile views use a collapsed sidebar and stacked cards.
