export function PlaceholderPage({ title }: { title: string }) {
  return (
    <div>
      <h1 className="text-2xl font-bold text-slate-900">{title}</h1>
      <p className="mt-2 text-slate-600">
        This page is wired in React Router. Next we’ll connect it to Laravel APIs.
      </p>
    </div>
  );
}

