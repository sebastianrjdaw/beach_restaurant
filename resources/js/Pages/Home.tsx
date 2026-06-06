import { Head } from '@inertiajs/react';
import { GallerySection } from '@/Components/Home/GallerySection';
import { HeroSection } from '@/Components/Home/HeroSection';
import { LocationSection } from '@/Components/Home/LocationSection';
import { MenuSection } from '@/Components/Home/MenuSection';
import { homeContent } from '@/content/homeContent';
import type { Locale, Menu, RestaurantSettings } from '@/types';

export default function Home({
  locale = 'es',
  settings,
  menus,
}: {
  locale: Locale;
  settings: RestaurantSettings;
  menus: Menu[];
}) {
  return (
    <>
      <Head title={settings.name} />
      <HeroSection locale={locale} settings={settings} />
      <section className="bg-stone-50 px-6 py-16">
        <div className="mx-auto max-w-6xl text-lg leading-8 text-slate-700">
          {homeContent[locale].intro}
        </div>
      </section>
      <MenuSection locale={locale} menus={menus} />
      <GallerySection />
      <LocationSection locale={locale} settings={settings} />
    </>
  );
}
