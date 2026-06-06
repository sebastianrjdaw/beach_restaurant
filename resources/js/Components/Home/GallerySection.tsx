import { homeContent } from '@/content/homeContent';
import type { Locale } from '@/types';

export function GallerySection({ locale }: { locale: Locale }) {
  const copy = homeContent[locale].gallery;

  return (
    <section className="bg-[#0E3A47] px-5 py-20 text-white sm:px-6 lg:py-24">
      <div className="mx-auto max-w-6xl">
        <p className="text-xs font-semibold uppercase tracking-[0.22em] text-[#D8C3A5]">{copy.title}</p>
        <div className="mt-8 grid auto-rows-[16rem] gap-4 md:grid-cols-4">
          {copy.images.map((image, index) => (
            <img
              key={image.alt}
              className={[
                'h-full w-full rounded-md object-cover',
                index === 0 ? 'md:col-span-2 md:row-span-2' : '',
                index === 3 ? 'md:col-span-2' : '',
              ].join(' ')}
              src={image.src}
              alt={image.alt}
            />
          ))}
        </div>
      </div>
    </section>
  );
}
