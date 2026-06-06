function formatDateInputValue(date: Date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');

  return `${year}-${month}-${day}`;
}

export function DatePicker({
  value,
  onChange,
  max,
}: {
  value: string;
  onChange: (value: string) => void;
  max?: string;
}) {
  return (
    <label className="grid gap-2 text-sm font-medium">
      Fecha
      <input
        className="h-11 rounded-md border border-slate-300 bg-white px-3"
        max={max}
        min={formatDateInputValue(new Date())}
        type="date"
        value={value}
        onChange={(event) => onChange(event.target.value)}
      />
    </label>
  );
}
