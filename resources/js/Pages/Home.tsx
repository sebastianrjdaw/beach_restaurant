import { Head, Link } from '@inertiajs/react';
import { CalendarCheck, Fish, Phone, Settings, Shell, UtensilsCrossed, Waves } from 'lucide-react';
import type { ReactNode } from 'react';
import { Button } from '@/Components/ui/button';
import { GallerySection } from '@/Components/Home/GallerySection';
import { LocationSection } from '@/Components/Home/LocationSection';
import { MenuSection } from '@/Components/Home/MenuSection';
import { homeContent, restaurantBrand, suggestedShifts } from '@/content/homeContent';
import type { Locale, Menu, RestaurantSettings } from '@/types';

const specialityIcons = [Fish, Shell, Waves, UtensilsCrossed];

const languageOptions = [
  { code: 'ES', label: 'Espanol', flag: '🇪🇸', href: '/?lang=es', enabled: true },
  { code: 'GL', label: 'Galego', flag: 'GL', href: '#', enabled: false },
  { code: 'EN', label: 'English', flag: '🇬🇧', href: '/?lang=en', enabled: true },
  { code: 'FR', label: 'Francais', flag: '🇫🇷', href: '#', enabled: false },
  { code: 'DE', label: 'Deutsch', flag: '🇩🇪', href: '#', enabled: false },
];

function NavLink({ href, children }: { href: string; children: ReactNode }) {
  return (
    <a
      href={href}
      className="group relative py-2 transition hover:text-white"
    >
      {children}
      <span className="absolute bottom-0 left-0 h-px w-full origin-left scale-x-0 bg-[#D8C3A5] transition-transform duration-300 ease-out group-hover:scale-x-100" />
    </a>
  );
}

function LanguageSelector({ locale }: { locale: Locale }) {
  return (
    <div className="hidden items-center gap-1.5 md:flex" aria-label="Seleccion de idioma">
      {languageOptions.map((option) => {
        const active = option.href.endsWith(`lang=${locale}`);
        const baseClass =
          'flex h-9 w-9 items-center justify-center rounded-full border text-base shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#D8C3A5]';
        const stateClass = active
          ? 'border-[#D8C3A5] bg-[#D8C3A5] text-[#0E3A47]'
          : option.enabled
            ? 'border-white/25 bg-white/10 text-white hover:border-[#D8C3A5] hover:bg-white/20'
            : 'cursor-not-allowed border-white/10 bg-white/5 text-white/45';

        if (!option.enabled) {
          return (
            <span
              key={option.code}
              className={`${baseClass} ${stateClass}`}
              title={`${option.label} proximamente`}
              aria-label={`${option.label} proximamente`}
            >
              <span aria-hidden="true" className={option.flag.length > 2 ? undefined : 'text-[10px] font-bold'}>
                {option.flag}
              </span>
            </span>
          );
        }

        return (
          <a
            key={option.code}
            className={`${baseClass} ${stateClass}`}
            href={option.href}
            title={option.label}
            aria-label={option.label}
          >
            <span aria-hidden="true" className={option.flag.length > 2 ? undefined : 'text-[10px] font-bold'}>
              {option.flag}
            </span>
          </a>
        );
      })}
    </div>
  );
}

function AboutSection({ locale }: { locale: Locale }) {
  const copy = homeContent[locale].about;

  return (
    <section id="nosotros" className="bg-[#F7F3EC] px-5 py-20 text-[#1C1C1C] sm:px-6 lg:py-28">
      <div className="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-end">
        <div>
          <p className="text-xs font-semibold uppercase tracking-[0.22em] text-[#355E53]">Restaurante A Saina</p>
          <h2 className="mt-4 font-serif text-4xl leading-tight text-[#0E3A47] sm:text-5xl">{copy.title}</h2>
        </div>
        <div className="space-y-7">
          <p className="text-lg leading-8 text-stone-700">{copy.body}</p>
          <p className="border-l-2 border-[#D8C3A5] pl-5 font-serif text-2xl leading-9 text-[#0E3A47]">
            {copy.highlight}
          </p>
        </div>
      </div>
    </section>
  );
}

function SpecialitiesSection({ locale }: { locale: Locale }) {
  const copy = homeContent[locale].specialities;

  return (
    <section id="especialidades" className="bg-white px-5 py-20 sm:px-6 lg:py-24">
      <div className="mx-auto max-w-6xl">
        <div className="max-w-2xl">
          <p className="text-xs font-semibold uppercase tracking-[0.22em] text-[#355E53]">Especialidades</p>
          <h2 className="mt-4 font-serif text-4xl leading-tight text-[#0E3A47] sm:text-5xl">{copy.title}</h2>
        </div>
        <div className="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-4">
          {copy.items.map((item, index) => {
            const Icon = specialityIcons[index] ?? Fish;

            return (
              <article key={item.title} className="rounded-md border border-stone-200 bg-[#F7F3EC] p-6">
                <div className="flex h-11 w-11 items-center justify-center rounded-md bg-[#0E3A47] text-white">
                  <Icon className="h-5 w-5" />
                </div>
                <h3 className="mt-6 text-xl font-semibold text-[#1C1C1C]">{item.title}</h3>
                <p className="mt-3 text-sm leading-6 text-stone-700">{item.description}</p>
              </article>
            );
          })}
        </div>
      </div>
    </section>
  );
}

function ExperienceSection({ locale }: { locale: Locale }) {
  const copy = homeContent[locale].experience;

  return (
    <section className="relative overflow-hidden bg-[#0E3A47] px-5 py-20 text-white sm:px-6 lg:py-28">
      <img
        className="absolute inset-0 h-full w-full object-cover opacity-25"
        src="https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=1800&q=80"
        alt=""
      />
      <div className="absolute inset-0 bg-[#0E3A47]/70" />
      <div className="relative mx-auto grid max-w-6xl gap-10 md:grid-cols-[1fr_1.2fr] md:items-center">
        <h2 className="font-serif text-4xl leading-tight sm:text-5xl">{copy.title}</h2>
        <p className="text-lg leading-8 text-[#F7F3EC]">{copy.body}</p>
      </div>
    </section>
  );
}

function ReservationPreviewSection({ locale }: { locale: Locale }) {
  const copy = homeContent[locale].reservation;
  const reservationHref = `/reservar?lang=${locale}`;

  return (
    <section id="reservas" className="bg-[#F7F3EC] px-5 py-20 sm:px-6 lg:py-24">
      <div className="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[0.95fr_1.05fr] lg:items-center">
        <div>
          <p className="text-xs font-semibold uppercase tracking-[0.22em] text-[#355E53]">{copy.eyebrow}</p>
          <h2 className="mt-4 font-serif text-4xl leading-tight text-[#0E3A47] sm:text-5xl">{copy.title}</h2>
          <p className="mt-5 text-lg leading-8 text-stone-700">{copy.body}</p>
          <div className="mt-8 flex flex-wrap gap-3">
            <Button asChild className="bg-[#0E3A47] hover:bg-[#355E53]">
              <Link href={reservationHref}>
                <CalendarCheck className="h-5 w-5" />
                {homeContent[locale].hero.reserve}
              </Link>
            </Button>
            <a
              className="inline-flex h-11 items-center justify-center gap-2 rounded-md border border-[#D8C3A5] bg-white px-5 text-sm font-semibold text-[#0E3A47] transition hover:bg-stone-100"
              href={`tel:${restaurantBrand.phone.replaceAll(' ', '')}`}
            >
              <Phone className="h-4 w-4" />
              {homeContent[locale].location.call}
            </a>
          </div>
        </div>
        <div className="rounded-md bg-white p-6 shadow-sm ring-1 ring-stone-200">
          <div className="grid gap-5 sm:grid-cols-2">
            <div>
              <p className="text-sm font-semibold text-[#0E3A47]">Comida</p>
              <div className="mt-3 flex flex-wrap gap-2">
                {suggestedShifts.lunch.map((slot) => (
                  <span key={slot} className="rounded-md bg-[#F7F3EC] px-3 py-2 text-sm text-stone-700">
                    {slot}
                  </span>
                ))}
              </div>
            </div>
            <div>
              <p className="text-sm font-semibold text-[#0E3A47]">Cena</p>
              <div className="mt-3 flex flex-wrap gap-2">
                {suggestedShifts.dinner.map((slot) => (
                  <span key={slot} className="rounded-md bg-[#F7F3EC] px-3 py-2 text-sm text-stone-700">
                    {slot}
                  </span>
                ))}
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}

export default function Home({
  locale = 'es',
}: {
  locale: Locale;
  settings: RestaurantSettings;
  menus: Menu[];
  }) {
  const copy = homeContent[locale] ?? homeContent.es;
  const reservationHref = `/reservar?lang=${locale}`;

  return (
    <>
      <Head title={copy.seo.title}>
        <meta name="description" content={copy.seo.description} />
        <meta name="keywords" content={copy.seo.keywords} />
      </Head>
      <main className="bg-[#F7F3EC] font-sans text-[#1C1C1C]">
        <section className="relative min-h-[92vh] overflow-hidden bg-[#0E3A47] text-white">
          <img
            className="absolute inset-0 h-full w-full object-cover"
            src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=2200&q=85"
            alt=""
          />
          <div className="absolute inset-0 bg-gradient-to-b from-[#0E3A47]/80 via-[#0E3A47]/35 to-[#0E3A47]/90" />
          <header className="relative z-10 mx-auto flex max-w-7xl items-center justify-between gap-5 px-5 py-5 sm:px-6">
            <a className="font-serif text-2xl font-semibold text-white" href="#">
              Restaurante A Saina
            </a>
            <nav className="hidden items-center gap-7 text-sm font-semibold text-white/85 lg:flex">
              <NavLink href="#nosotros">
                {copy.nav.about}
              </NavLink>
              <NavLink href="#especialidades">
                {copy.nav.specialities}
              </NavLink>
              <NavLink href="#carta">
                {copy.nav.menu}
              </NavLink>
              <NavLink href="#ubicacion">
                {copy.nav.location}
              </NavLink>
            </nav>
            <div className="flex items-center gap-3">
              <LanguageSelector locale={locale} />
              <a
                className="flex h-9 w-9 items-center justify-center rounded-full border border-white/20 bg-white/10 text-white transition hover:border-[#D8C3A5] hover:bg-white/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#D8C3A5]"
                href="/admin"
                title="Panel admin"
                aria-label="Panel admin"
              >
                <Settings className="h-4 w-4" />
              </a>
              <Button asChild className="bg-[#D8C3A5] text-[#0E3A47] hover:bg-[#F7F3EC]">
                <Link href={reservationHref}>
                  <CalendarCheck className="h-4 w-4" />
                  {copy.nav.reserve}
                </Link>
              </Button>
            </div>
          </header>
          <div className="relative z-10 mx-auto flex min-h-[calc(92vh-5rem)] max-w-7xl flex-col justify-end px-5 pb-16 pt-20 sm:px-6 lg:pb-20">
            <p className="text-xs font-semibold uppercase tracking-[0.22em] text-[#D8C3A5]">{copy.hero.eyebrow}</p>
            <h1 className="mt-5 max-w-4xl font-serif text-5xl leading-[1.02] sm:text-7xl lg:text-8xl">{copy.hero.title}</h1>
            <p className="mt-6 max-w-2xl text-xl leading-8 text-[#F7F3EC]">{copy.hero.subtitle}</p>
            <p className="mt-4 max-w-xl text-base leading-7 text-white/80">{copy.hero.short}</p>
            <div className="mt-9 flex flex-wrap gap-3">
              <Button asChild className="bg-[#D8C3A5] text-[#0E3A47] hover:bg-[#F7F3EC]">
                <Link href={reservationHref}>
                  <CalendarCheck className="h-5 w-5" />
                  {copy.hero.reserve}
                </Link>
              </Button>
              <a
                className="inline-flex h-11 items-center justify-center rounded-md border border-white/35 px-5 text-sm font-semibold text-white transition hover:bg-white/10"
                href="#carta"
              >
                {copy.hero.menu}
              </a>
            </div>
          </div>
        </section>

        <AboutSection locale={locale} />
        <SpecialitiesSection locale={locale} />
        <ExperienceSection locale={locale} />
        <MenuSection locale={locale} />
        <ReservationPreviewSection locale={locale} />
        <GallerySection locale={locale} />
        <LocationSection locale={locale} settings={restaurantBrand} />
      </main>
    </>
  );
}
