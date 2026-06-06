import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { ReservationForm } from '@/Components/Reservation/ReservationForm';
import type { RestaurantSettings } from '@/types';

export default function ReservationCreate({ settings }: { settings: RestaurantSettings }) {
  return (
    <>
      <Head title="Reservar" />
      <main className="min-h-screen bg-stone-50 px-6 py-10">
        <div className="mx-auto max-w-3xl">
          <Link className="inline-flex items-center gap-2 text-sm font-semibold text-teal-800" href="/">
            <ArrowLeft className="h-4 w-4" />
            Volver
          </Link>
          <div className="mt-8 rounded-md bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
            <p className="text-sm font-semibold uppercase tracking-[0.18em] text-teal-700">{settings.name}</p>
            <h1 className="mt-3 text-3xl font-semibold">Reserva tu mesa</h1>
            <p className="mt-3 text-slate-600">Elige fecha, hora y dejanos tus datos para confirmar la reserva.</p>
            <div className="mt-8">
              <ReservationForm />
            </div>
          </div>
        </div>
      </main>
    </>
  );
}
