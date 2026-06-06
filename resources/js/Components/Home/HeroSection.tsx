import { Link } from '@inertiajs/react';
import { CalendarCheck } from 'lucide-react';
import { Button } from '@/Components/ui/button';
import { homeContent } from '@/content/homeContent';
import type { Locale, RestaurantSettings } from '@/types';

export function HeroSection({ locale, settings }: { locale: Locale; settings: RestaurantSettings }) {
  const copy = homeContent[locale].hero;

  return (
    <section className="relative min-h-[82vh] overflow-hidden bg-slate-950 text-white">
      <img
        className="absolute inset-0 h-full w-full object-cover opacity-70"
        src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1800&q=80"
        alt=""
      />
      <div className="absolute inset-0 bg-gradient-to-b from-slate-950/45 via-slate-950/20 to-slate-950/70" />
      <div className="relative mx-auto flex min-h-[82vh] max-w-6xl flex-col justify-end px-6 pb-16 pt-32">
        <p className="mb-4 text-sm font-semibold uppercase tracking-[0.18em] text-teal-100">
          {settings.name}
        </p>
        <h1 className="max-w-3xl text-5xl font-semibold leading-tight md:text-7xl">
          {copy.title}
        </h1>
        <p className="mt-5 max-w-xl text-lg leading-8 text-slate-100">{copy.subtitle}</p>
        <div className="mt-8">
          <Button asChild>
            <Link href="/reservar">
              <CalendarCheck className="h-5 w-5" />
              {copy.reserve}
            </Link>
          </Button>
        </div>
      </div>
    </section>
  );
}
