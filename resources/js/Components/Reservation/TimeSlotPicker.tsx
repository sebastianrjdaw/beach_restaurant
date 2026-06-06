import { cn } from '@/lib/utils';
import type { TimeSlot } from '@/types';

export function TimeSlotPicker({
  slots,
  value,
  onChange,
}: {
  slots: TimeSlot[];
  value: string;
  onChange: (value: string) => void;
}) {
  return (
    <div className="grid gap-2">
      <p className="text-sm font-medium">Hora</p>
      <div className="grid grid-cols-3 gap-2 sm:grid-cols-4">
        {slots.map((slot) => (
          <button
            key={slot.time}
            className={cn(
              'h-11 rounded-md border border-slate-300 bg-white text-sm font-semibold transition hover:border-teal-700',
              value === slot.time && 'border-teal-700 bg-teal-700 text-white',
            )}
            type="button"
            onClick={() => onChange(slot.time)}
          >
            {slot.label}
          </button>
        ))}
      </div>
    </div>
  );
}
