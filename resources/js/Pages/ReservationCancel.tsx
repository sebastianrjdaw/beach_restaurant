import { FormEvent } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { AlertTriangle } from 'lucide-react';
import { Button } from '@/Components/ui/button';

type ReservationSummary = {
  customer_name: string;
  reservation_date: string;
  start_time: string;
  party_size: number;
  status: string;
};

export default function ReservationCancel({
  canCancel,
  reservation,
  token,
}: {
  canCancel: boolean;
  reservation: ReservationSummary;
  token: string;
}) {
  const { data, setData, post, processing, errors } = useForm({
    cancel_reason: '',
  });

  function submit(event: FormEvent) {
    event.preventDefault();
    post(`/reservas/${token}/cancelar`);
  }

  return (
    <>
      <Head title="Cancelar reserva" />
      <main className="grid min-h-screen place-items-center bg-[#F7F3EC] px-5 py-10 text-[#1C1C1C]">
        <section className="w-full max-w-lg rounded-md bg-white p-7 shadow-sm ring-1 ring-stone-200">
          <AlertTriangle className="h-10 w-10 text-[#0E3A47]" />
          <h1 className="mt-5 font-serif text-3xl text-[#0E3A47]">Cancelar reserva</h1>
          <div className="mt-5 space-y-2 text-sm text-stone-700">
            <p><strong>Nombre:</strong> {reservation.customer_name}</p>
            <p><strong>Fecha:</strong> {reservation.reservation_date}</p>
            <p><strong>Hora:</strong> {reservation.start_time}</p>
            <p><strong>Personas:</strong> {reservation.party_size}</p>
          </div>

          {canCancel ? (
            <form className="mt-6 grid gap-4" onSubmit={submit}>
              <label className="grid gap-2 text-sm font-medium">
                Motivo de cancelacion
                <textarea
                  className="min-h-28 rounded-md border border-stone-300 p-3"
                  value={data.cancel_reason}
                  onChange={(event) => setData('cancel_reason', event.target.value)}
                />
              </label>
              {errors.cancel_reason && <p className="text-sm text-red-700">{errors.cancel_reason}</p>}
              {errors.cancel && <p className="text-sm text-red-700">{errors.cancel}</p>}
              <Button disabled={processing} type="submit">
                Confirmar cancelacion
              </Button>
            </form>
          ) : (
            <p className="mt-6 rounded-md bg-stone-100 p-4 text-sm leading-6 text-stone-700">
              Para cancelar esta reserva, por favor llama directamente al restaurante.
            </p>
          )}

          <Link className="mt-6 inline-block text-sm font-semibold text-[#0E3A47]" href="/">
            Volver al restaurante
          </Link>
        </section>
      </main>
    </>
  );
}
