import { Head, Link } from '@inertiajs/react';
import { CheckCircle2 } from 'lucide-react';
import { Button } from '@/Components/ui/button';

export default function ReservationSuccess({ confirmationCode }: { confirmationCode: string }) {
  return (
    <>
      <Head title="Reserva confirmada" />
      <main className="grid min-h-screen place-items-center bg-stone-50 px-6">
        <section className="max-w-md rounded-md bg-white p-8 text-center shadow-sm ring-1 ring-slate-200">
          <CheckCircle2 className="mx-auto h-12 w-12 text-teal-700" />
          <h1 className="mt-5 text-3xl font-semibold">Reserva recibida</h1>
          <p className="mt-3 text-slate-600">Tu codigo de confirmacion es {confirmationCode}.</p>
          <Button asChild className="mt-8">
            <Link href="/">Volver al restaurante</Link>
          </Button>
        </section>
      </main>
    </>
  );
}
