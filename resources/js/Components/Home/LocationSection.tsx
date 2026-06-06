import { Link } from '@inertiajs/react';
import { CalendarCheck, MapPinned, Navigation, Phone } from 'lucide-react';
import { Button } from '@/Components/ui/button';
import { homeContent } from '@/content/homeContent';
import type { Locale, RestaurantSettings } from '@/types';

export function LocationSection({ locale, settings }: { locale: Locale; settings: RestaurantSettings }) {
  const copy = homeContent[locale].location;
  const address = [settings.address, settings.city, settings.country].filter(Boolean).join(', ');
  const directionsUrl = 'https://maps.app.goo.gl/i9jVdorLVL46kdwP8';
  const phone = settings.phone ?? '';

  return (
    <section id="ubicacion" className="bg-[#F7F3EC] px-5 py-20 text-[#1C1C1C] sm:px-6 lg:py-24">
      <div className="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:items-stretch">
        <div className="flex flex-col justify-between">
          <div>
            <p className="text-xs font-semibold uppercase tracking-[0.22em] text-[#355E53]">Ubicacion</p>
            <h2 className="mt-4 font-serif text-4xl leading-tight text-[#0E3A47] sm:text-5xl">{copy.title}</h2>
            <p className="mt-5 text-lg leading-8 text-stone-700">{copy.body}</p>
          </div>
          <div className="mt-8 space-y-4 text-stone-700">
            <p className="flex gap-3">
              <MapPinned className="mt-1 h-5 w-5 shrink-0 text-[#355E53]" />
              <span>{address}</span>
            </p>
            <p className="flex gap-3">
              <Phone className="mt-1 h-5 w-5 shrink-0 text-[#355E53]" />
              <span>{phone}</span>
            </p>
            <p>{copy.hours}</p>
          </div>
          <div className="mt-8 flex flex-wrap gap-3">
            <a
              className="inline-flex h-11 items-center justify-center gap-2 rounded-md bg-[#0E3A47] px-5 text-sm font-semibold text-white transition hover:bg-[#355E53]"
              href={directionsUrl}
              rel="noreferrer"
              target="_blank"
            >
              <Navigation className="h-4 w-4" />
              {copy.directions}
            </a>
            <a
              className="inline-flex h-11 items-center justify-center gap-2 rounded-md border border-[#D8C3A5] bg-white px-5 text-sm font-semibold text-[#0E3A47] transition hover:bg-stone-100"
              href={`tel:${phone.replaceAll(' ', '')}`}
            >
              <Phone className="h-4 w-4" />
              {copy.call}
            </a>
            <Button asChild className="bg-[#355E53] hover:bg-[#0E3A47]">
              <Link href="/reservar">
                <CalendarCheck className="h-4 w-4" />
                {copy.reserve}
              </Link>
            </Button>
          </div>
        </div>
        <div className="min-h-[28rem] rounded-md bg-[url('https://images.unsplash.com/photo-1523906834658-6e24ef2386f9?auto=format&fit=crop&w=1400&q=80')] bg-cover bg-center shadow-sm" />
      </div>
    </section>
  );
}
