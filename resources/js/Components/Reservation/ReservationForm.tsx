import { FormEvent, useEffect, useState } from 'react';
import { useForm } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { DatePicker } from '@/Components/Reservation/DatePicker';
import { TimeSlotPicker } from '@/Components/Reservation/TimeSlotPicker';
import type { Area, Locale, TimeSlot } from '@/types';

type ReservationFormData = {
  reservation_date: string;
  start_time: string;
  party_size: number;
  preferred_area_id: string;
  customer_name: string;
  customer_email: string;
  customer_phone: string;
  locale: 'es' | 'en';
  comments: string;
};

function formatDateInputValue(date: Date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');

  return `${year}-${month}-${day}`;
}

export function ReservationForm({
  areas,
  locale = 'es',
  maxDaysInAdvance = 30,
}: {
  areas: Area[];
  locale?: Locale;
  maxDaysInAdvance?: number;
}) {
  const today = formatDateInputValue(new Date());
  const maxDate = new Date();
  maxDate.setDate(maxDate.getDate() + maxDaysInAdvance);
  const [slots, setSlots] = useState<TimeSlot[]>([]);
  const [loadingSlots, setLoadingSlots] = useState(false);
  const { data, setData, transform, post, processing, errors } = useForm<ReservationFormData>({
    reservation_date: today,
    start_time: '',
    party_size: 2,
    preferred_area_id: '',
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    locale,
    comments: '',
  });

  useEffect(() => {
    const controller = new AbortController();
    const params = new URLSearchParams({
      date: data.reservation_date,
      party_size: String(data.party_size),
      preferred_area_id: data.preferred_area_id,
    });

    setLoadingSlots(true);
    fetch(`/reservas/disponibilidad?${params.toString()}`, { signal: controller.signal })
      .then((response) => response.json())
      .then((payload: { slots: TimeSlot[] }) => {
        setSlots(payload.slots);
        if (!payload.slots.some((slot) => slot.time === data.start_time && (slot.is_available ?? true))) {
          setData('start_time', '');
        }
      })
      .catch((error) => {
        if (error.name !== 'AbortError') {
          setSlots([]);
        }
      })
      .finally(() => {
        if (!controller.signal.aborted) {
          setLoadingSlots(false);
        }
      });

    return () => controller.abort();
  }, [data.reservation_date, data.party_size, data.preferred_area_id]);

  function submit(event: FormEvent) {
    event.preventDefault();
    const comments = data.comments;

    transform((formData) => ({
      ...formData,
      locale,
      comments,
    }));
    post('/reservar');
  }

  return (
    <form className="grid gap-6" onSubmit={submit}>
      <div className="grid gap-4 sm:grid-cols-2">
        <DatePicker
          max={formatDateInputValue(maxDate)}
          value={data.reservation_date}
          onChange={(value) => setData('reservation_date', value)}
        />
        <label className="grid gap-2 text-sm font-medium">
          Comensales
          <input
            className="h-11 rounded-md border border-slate-300 bg-white px-3"
            min={1}
            max={20}
            type="number"
            value={data.party_size}
            onChange={(event) => setData('party_size', Number(event.target.value))}
          />
        </label>
      </div>

      <TimeSlotPicker
        loading={loadingSlots}
        slots={slots}
        value={data.start_time}
        onChange={(value) => setData('start_time', value)}
      />
      {errors.start_time && <p className="text-sm text-red-700">{errors.start_time}</p>}

      <div className="grid gap-4 sm:grid-cols-2">
        <label className="grid gap-2 text-sm font-medium">
          Nombre
          <input className="h-11 rounded-md border border-slate-300 bg-white px-3" value={data.customer_name} onChange={(event) => setData('customer_name', event.target.value)} />
          {errors.customer_name && <span className="text-red-700">{errors.customer_name}</span>}
        </label>
        <label className="grid gap-2 text-sm font-medium">
          Telefono
          <input className="h-11 rounded-md border border-slate-300 bg-white px-3" value={data.customer_phone} onChange={(event) => setData('customer_phone', event.target.value)} />
        </label>
      </div>

      <div className="grid gap-4 sm:grid-cols-2">
        <label className="grid gap-2 text-sm font-medium">
          Email
          <input className="h-11 rounded-md border border-slate-300 bg-white px-3" type="email" value={data.customer_email} onChange={(event) => setData('customer_email', event.target.value)} />
          {errors.customer_email && <span className="text-red-700">{errors.customer_email}</span>}
        </label>
        <label className="grid gap-2 text-sm font-medium">
          Interior o terraza
          <select
            className="h-11 rounded-md border border-slate-300 bg-white px-3"
            value={data.preferred_area_id}
            onChange={(event) => setData('preferred_area_id', event.target.value)}
          >
            <option value="">Sin preferencia</option>
            {areas.map((area) => (
              <option key={area.id} value={String(area.id)}>
                {area.name[locale] ?? area.name.es}
              </option>
            ))}
          </select>
        </label>
      </div>

      <label className="grid gap-2 text-sm font-medium">
        Comentarios o alergias
        <textarea className="min-h-28 rounded-md border border-slate-300 bg-white p-3" value={data.comments} onChange={(event) => setData('comments', event.target.value)} />
      </label>

      <Button disabled={processing || !data.start_time} type="submit">
        Confirmar reserva
      </Button>
    </form>
  );
}
