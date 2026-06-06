import type { Locale } from '@/types';

export const homeContent = {
  es: {
    hero: {
      title: 'Restaurante de playa',
      subtitle: 'Cocina mediterranea, producto fresco y mesas frente al mar.',
      reserve: 'Reservar mesa',
    },
    intro:
      'Un restaurante junto al mar con arroces, pescado fresco y cocteles al atardecer. Un espacio relajado para comer sin prisa, compartir mesa y quedarse mirando el Mediterraneo.',
    menu: {
      title: 'Carta',
    },
    location: {
      title: 'Ubicacion y horarios',
      hours: 'Abierto de martes a domingo para comidas y cenas.',
    },
  },
  en: {
    hero: {
      title: 'Beach restaurant',
      subtitle: 'Mediterranean cooking, fresh produce and seaside tables.',
      reserve: 'Book a table',
    },
    intro:
      'A seaside restaurant with rice dishes, fresh fish and sunset cocktails. A relaxed place to eat slowly, share the table and linger by the Mediterranean.',
    menu: {
      title: 'Menu',
    },
    location: {
      title: 'Location and hours',
      hours: 'Open Tuesday to Sunday for lunch and dinner.',
    },
  },
} satisfies Record<
  Locale,
  {
    hero: {
      title: string;
      subtitle: string;
      reserve: string;
    };
    intro: string;
    menu: {
      title: string;
    };
    location: {
      title: string;
      hours: string;
    };
  }
>;
