import { homeContent } from '@/content/homeContent';
import type { Locale, Menu } from '@/types';

function t(value: Record<string, string> | null | undefined, locale: Locale) {
  return value?.[locale] ?? value?.es ?? '';
}

export function MenuSection({ locale, menus }: { locale: Locale; menus: Menu[] }) {
  return (
    <section className="bg-white px-6 py-20">
      <div className="mx-auto max-w-6xl">
        <h2 className="text-3xl font-semibold">{homeContent[locale].menu.title}</h2>
        <div className="mt-8 grid gap-8 md:grid-cols-2">
          {menus.flatMap((menu) =>
            menu.categories.map((category) => (
              <div key={category.id} className="border-t border-slate-200 pt-5">
                <h3 className="text-xl font-semibold">{t(category.name, locale)}</h3>
                <div className="mt-4 space-y-4">
                  {category.items.map((item) => (
                    <article key={item.id} className="grid grid-cols-[1fr_auto] gap-4">
                      <div>
                        <h4 className="font-medium">{t(item.name, locale)}</h4>
                        <p className="mt-1 text-sm leading-6 text-slate-600">{t(item.description, locale)}</p>
                      </div>
                      <p className="font-semibold">{item.price ? `${item.price} EUR` : ''}</p>
                    </article>
                  ))}
                </div>
              </div>
            )),
          )}
        </div>
      </div>
    </section>
  );
}
