import { homeContent } from '@/content/homeContent';
import type { Locale } from '@/types';

export function MenuSection({ locale }: { locale: Locale }) {
  const copy = homeContent[locale].menu;

  return (
    <section id="carta" className="bg-white px-5 py-20 text-[#1C1C1C] sm:px-6 lg:py-24">
      <div className="mx-auto max-w-6xl">
        <div className="grid gap-6 md:grid-cols-[0.9fr_1.1fr] md:items-end">
          <div>
            <p className="text-xs font-semibold uppercase tracking-[0.22em] text-[#355E53]">{copy.eyebrow}</p>
            <h2 className="mt-4 font-serif text-4xl leading-tight text-[#0E3A47] sm:text-5xl">{copy.title}</h2>
          </div>
          <p className="text-sm leading-6 text-stone-600 md:text-right">{copy.note}</p>
        </div>

        <div className="mt-12 grid gap-x-10 gap-y-12 lg:grid-cols-2">
          {copy.categories.map((category) => (
            <section key={category.title} className="border-t border-[#D8C3A5] pt-5">
              <h3 className="font-serif text-2xl text-[#0E3A47]">{category.title}</h3>
              <div className="mt-5 space-y-4">
                {category.items.map(([name, price]) => (
                  <article key={`${category.title}-${name}`} className="grid grid-cols-[1fr_auto] gap-4">
                    <h4 className="text-sm font-semibold text-stone-800 sm:text-base">{name}</h4>
                    <p className="whitespace-nowrap text-sm font-semibold text-[#355E53]">{price}</p>
                  </article>
                ))}
              </div>
            </section>
          ))}
        </div>
      </div>
    </section>
  );
}
