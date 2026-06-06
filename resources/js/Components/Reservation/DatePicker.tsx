export function DatePicker({
  value,
  onChange,
}: {
  value: string;
  onChange: (value: string) => void;
}) {
  return (
    <label className="grid gap-2 text-sm font-medium">
      Fecha
      <input
        className="h-11 rounded-md border border-slate-300 bg-white px-3"
        min={new Date().toISOString().slice(0, 10)}
        type="date"
        value={value}
        onChange={(event) => onChange(event.target.value)}
      />
    </label>
  );
}
