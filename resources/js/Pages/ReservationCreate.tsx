import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { ReservationForm } from '@/Components/Reservation/ReservationForm';
import type { Locale, RestaurantSettings } from '@/types';

export default function ReservationCreate({ locale = 'es', settings }: { locale: Locale; settings: RestaurantSettings }) {
  return (
    <>
      <Head title="Reservar mesa | Restaurante A Saina" />
      <main className="min-h-screen bg-[#F7F3EC] px-5 py-10 text-[#1C1C1C] sm:px-6">
        <div className="mx-auto max-w-4xl">
          <Link className="inline-flex items-center gap-2 text-sm font-semibold text-[#0E3A47]" href={`/?lang=${locale}`}>
            <ArrowLeft className="h-4 w-4" />
            Volver
          </Link>
          <div className="mt-8 grid overflow-hidden rounded-md bg-white shadow-sm ring-1 ring-stone-200 lg:grid-cols-[0.85fr_1.15fr]">
            <div className="relative min-h-72 bg-[#0E3A47] p-8 text-white">
              <img
                className="absolute inset-0 h-full w-full object-cover opacity-45"
                src="https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?auto=format&fit=crop&w=1000&q=80"
                alt=""
              />
              <div className="absolute inset-0 bg-[#0E3A47]/60" />
              <div className="relative flex h-full flex-col justify-end">
                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-[#D8C3A5]">
                  {settings.name || 'Restaurante A Saina'}
                </p>
                <h1 className="mt-4 font-serif text-4xl leading-tight">Reserva tu mesa frente al Atlantico</h1>
                <p className="mt-4 leading-7 text-white/85">
                  Elige dia, hora, numero de personas y preferencia de sala. Te confirmaremos la reserva cuanto antes.
                </p>
              </div>
            </div>
            <div className="p-6 sm:p-8">
              <ReservationForm locale={locale} maxDaysInAdvance={settings.max_days_in_advance ?? 30} />
            </div>
          </div>
        </div>
      </main>
    </>
  );
}
