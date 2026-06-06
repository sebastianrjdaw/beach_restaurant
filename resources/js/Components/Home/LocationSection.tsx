import { MapPin, Phone } from 'lucide-react';
import { homeContent } from '@/content/homeContent';
import type { Locale, RestaurantSettings } from '@/types';

export function LocationSection({ locale, settings }: { locale: Locale; settings: RestaurantSettings }) {
  const copy = homeContent[locale].location;

  return (
    <section className="bg-stone-50 px-6 py-20">
      <div className="mx-auto grid max-w-6xl gap-10 md:grid-cols-[1fr_1.2fr]">
        <div>
          <h2 className="text-3xl font-semibold">{copy.title}</h2>
          <div className="mt-6 space-y-4 text-slate-700">
            <p className="flex gap-3">
              <MapPin className="mt-1 h-5 w-5 text-teal-700" />
              <span>{[settings.address, settings.city, settings.country].filter(Boolean).join(', ')}</span>
            </p>
            <p className="flex gap-3">
              <Phone className="mt-1 h-5 w-5 text-teal-700" />
              <span>{settings.phone}</span>
            </p>
            <p>{copy.hours}</p>
          </div>
        </div>
        <div className="min-h-80 rounded-md bg-[url('https://images.unsplash.com/photo-1526772662000-3f88f10405ff?auto=format&fit=crop&w=1200&q=80')] bg-cover bg-center" />
      </div>
    </section>
  );
}
