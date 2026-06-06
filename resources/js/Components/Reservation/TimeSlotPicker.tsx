import { cn } from '@/lib/utils';
import type { TimeSlot } from '@/types';

export function TimeSlotPicker({
  slots,
  value,
  onChange,
  loading = false,
}: {
  slots: TimeSlot[];
  value: string;
  onChange: (value: string) => void;
  loading?: boolean;
}) {
  return (
    <div className="grid gap-2" aria-busy={loading}>
      <div className="flex items-center justify-between gap-3">
        <p className="text-sm font-medium">Hora</p>
        {loading && <span className="h-2 w-2 rounded-full bg-[#355E53]" aria-hidden="true" />}
      </div>
      <div className="grid grid-cols-3 gap-2 sm:grid-cols-4">
        {slots.map((slot) => {
          const isAvailable = slot.is_available ?? true;

          return (
            <button
              key={slot.time}
              className={cn(
                'h-11 rounded-md border border-slate-300 bg-white text-sm font-semibold transition hover:border-teal-700',
                value === slot.time && 'border-teal-700 bg-teal-700 text-white',
                !isAvailable && 'cursor-not-allowed border-slate-200 bg-slate-100 text-slate-400 line-through hover:border-slate-200',
              )}
              disabled={!isAvailable}
              title={!isAvailable ? slot.disabled_reason ?? 'No disponible' : undefined}
              type="button"
              onClick={() => isAvailable && onChange(slot.time)}
            >
              {slot.label}
            </button>
          );
        })}
      </div>
      {slots.length === 0 && (
        <p className="rounded-md bg-stone-100 px-3 py-2 text-sm text-stone-600">
          No hay turnos registrados para este dia.
        </p>
      )}
    </div>
  );
}
