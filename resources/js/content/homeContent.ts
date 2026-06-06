import type { Locale } from '@/types';

export const restaurantBrand = {
  name: 'Restaurante A Saina',
  address: 'Avenida de Saina, 30',
  city: 'Valdovino',
  province: 'A Coruna',
  country: 'Spain',
  phone: '+34 981 48 00 00',
  email: 'reservas@asaina.test',
};

export const reservationStatuses = ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'];

export const suggestedShifts = {
  lunch: ['13:00', '13:30', '14:00', '14:30', '15:00'],
  dinner: ['20:30', '21:00', '21:30', '22:00'],
};

const menuCategories = [
  {
    title: 'Entrantes frios',
    items: [
      ['Ensalada clasica', '9,50 EUR'],
      ['Ensalada mixta', '12,00 EUR'],
      ['Ensaladilla rusa casera', '8,50 EUR'],
      ['Salpicon de marisco', '16,00 EUR'],
      ['Empanada gallega del dia', '7,50 EUR'],
    ],
  },
  {
    title: 'Entrantes calientes',
    items: [
      ['Pulpo a feira', '18,00 EUR'],
      ['Mejillones al vapor', '12,00 EUR'],
      ['Almejas a la marinera', '19,00 EUR'],
      ['Navajas a la plancha', '16,50 EUR'],
      ['Chipirones fritos', '14,00 EUR'],
      ['Croquetas caseras de marisco', '10,50 EUR'],
    ],
  },
  {
    title: 'Pescados',
    items: [
      ['Bacalao a la gallega', '18,50 EUR'],
      ['Bacalao a la plancha', '17,50 EUR'],
      ['Rodaballo salvaje a la plancha', 's/m'],
      ['Lubina salvaje con allada gallega', 's/m'],
      ['Rape en salsa marinera', '22,00 EUR'],
      ['Lenguado a la plancha', 's/m'],
      ['Calamares de la ria', '16,00 EUR'],
    ],
  },
  {
    title: 'Mariscos',
    items: [
      ['Percebes', 's/m'],
      ['Zamburinas a la plancha', '18,00 EUR'],
      ['Langostinos cocidos', '16,00 EUR'],
      ['Mariscada A Saina', 's/m'],
      ['Berberechos al vapor', '15,00 EUR'],
    ],
  },
  {
    title: 'Arroces',
    items: [
      ['Paella de marisco', '19,00 EUR/persona'],
      ['Arroz marinero', '21,00 EUR/persona'],
      ['Arroz con bogavante', 's/m'],
      ['Paella mixta', '17,00 EUR/persona'],
    ],
  },
  {
    title: 'Carnes y postres',
    items: [
      ['Churrasco gallego', '16,00 EUR'],
      ['Entrecot a la plancha', '22,00 EUR'],
      ['Secreto iberico', '18,00 EUR'],
      ['Tarta de queso casera', '6,00 EUR'],
      ['Flan de huevo', '5,00 EUR'],
      ['Canitas rellenas de crema', '6,50 EUR'],
    ],
  },
];

const galleryImages = [
  {
    src: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
    alt: 'Vista aerea de la Playa da Frouxeira en Valdovino',
  },
  {
    src: 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?auto=format&fit=crop&w=900&q=80',
    alt: 'Mesa con pescado fresco frente al mar',
  },
  {
    src: 'https://images.unsplash.com/photo-1571997478779-2adcbbe9ab2f?auto=format&fit=crop&w=900&q=80',
    alt: 'Marisco gallego de temporada',
  },
  {
    src: 'https://images.unsplash.com/photo-1516684732162-798a0062be99?auto=format&fit=crop&w=900&q=80',
    alt: 'Arroz marinero para compartir',
  },
  {
    src: 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80',
    alt: 'Atardecer atlantico en Valdovino',
  },
  {
    src: 'https://images.unsplash.com/photo-1552566626-52f8b828add9?auto=format&fit=crop&w=900&q=80',
    alt: 'Terraza junto a la playa',
  },
];

export const homeContent = {
  es: {
    seo: {
      title: 'Restaurante A Saina | Pescado, marisco y arroces en Valdovino',
      description:
        'Restaurante A Saina en Valdovino, junto a la Playa da Frouxeira. Cocina gallega, pescados frescos, mariscos, arroces y reservas online.',
      keywords:
        'restaurante valdovino, restaurante a frouxeira, comer en valdovino, marisco valdovino, pescado fresco galicia, restaurante playa frouxeira, arroces valdovino',
    },
    nav: {
      about: 'Nosotros',
      specialities: 'Especialidades',
      menu: 'Carta',
      location: 'Ubicacion',
      reserve: 'Reservar',
    },
    hero: {
      eyebrow: 'A Saina, Valdovino',
      title: 'Cocina gallega frente al Atlantico',
      subtitle: 'Pescados frescos, mariscos y arroces junto a la Playa da Frouxeira, en Valdovino.',
      reserve: 'Reservar mesa',
      menu: 'Ver carta',
      short:
        'Un restaurante para disfrutar del producto del mar, la calma de la costa gallega y una mesa con sabor a Valdovino.',
    },
    about: {
      title: 'Una mesa junto al mar',
      body:
        'En A Saina cocinamos con respeto por el producto y por la tradicion. Nuestra cocina nace del Atlantico: pescados frescos, mariscos de temporada, arroces para compartir y platos gallegos de siempre, servidos en un entorno unico junto a la Playa da Frouxeira.',
      highlight: 'Aqui el mar no es decoracion. Es parte de la experiencia.',
    },
    specialities: {
      title: 'Producto de lonja, brasa suave y sobremesa larga',
      items: [
        {
          title: 'Pescados frescos',
          description:
            'Rodaballo, lubina, bacalao, rape y pescado de temporada preparado a la plancha o al estilo gallego.',
        },
        {
          title: 'Mariscos',
          description: 'Percebes, mejillones, almejas, navajas y marisco segun mercado.',
        },
        {
          title: 'Arroces',
          description: 'Arroces marineros y paellas para compartir, elaborados con producto fresco.',
        },
        {
          title: 'Cocina gallega',
          description: 'Pulpo, empanada, carnes, postres caseros y sabores tradicionales.',
        },
      ],
    },
    experience: {
      title: 'Comer en Valdovino sabe diferente',
      body:
        'La Playa da Frouxeira es uno de los paisajes mas especiales de la costa de Ferrolterra: arena blanca, dunas, viento atlantico, surf y una laguna natural que acompana al arenal. A Saina recoge ese caracter: cocina sencilla, producto fresco y una experiencia pensada para disfrutar sin prisa.',
    },
    reservation: {
      title: 'Reserva tu mesa',
      eyebrow: 'Reserva online',
      body:
        'Elige dia, hora y numero de personas. Te confirmaremos la reserva para que solo tengas que venir a disfrutar.',
      microcopy: 'Reserva tu mesa frente al Atlantico.',
      languages: [
        ['ES', 'Reserva tu mesa frente al Atlantico.'],
        ['GL', 'Reserva a tua mesa fronte ao Atlantico.'],
        ['EN', 'Book your table by the Atlantic.'],
        ['FR', 'Reservez votre table face a l Atlantique.'],
        ['DE', 'Reservieren Sie Ihren Tisch am Atlantik.'],
      ],
    },
    menu: {
      title: 'Carta demo',
      eyebrow: 'Producto gallego y mercado',
      note:
        'Los precios y platos son contenido ficticio para demo. En produccion deben ser revisados y aprobados por el restaurante.',
      categories: menuCategories,
    },
    gallery: {
      title: 'Galeria',
      images: galleryImages,
    },
    location: {
      title: 'En la Playa da Frouxeira',
      body:
        'Nos encontraras en Avenida de Saina, 30, Valdovino, A Coruna. Un lugar perfecto para comer despues de un paseo por la playa, una jornada de surf o una escapada por la costa gallega.',
      hours: 'Comidas de 13:00 a 15:00. Cenas de 20:30 a 22:00.',
      directions: 'Como llegar',
      call: 'Llamar ahora',
      reserve: 'Reservar mesa',
    },
  },
  en: {
    seo: {
      title: 'Restaurante A Saina | Fish, seafood and rice in Valdovino',
      description:
        'Restaurante A Saina in Valdovino, by Playa da Frouxeira. Galician cooking, fresh fish, seafood, rice dishes and online reservations.',
      keywords:
        'restaurant valdovino, restaurant a frouxeira, seafood valdovino, fresh fish galicia, beach restaurant frouxeira',
    },
    nav: {
      about: 'About',
      specialities: 'Specialities',
      menu: 'Menu',
      location: 'Location',
      reserve: 'Book',
    },
    hero: {
      eyebrow: 'A Saina, Valdovino',
      title: 'Galician cooking by the Atlantic',
      subtitle: 'Fresh fish, seafood and rice dishes next to Playa da Frouxeira, in Valdovino.',
      reserve: 'Book a table',
      menu: 'View menu',
      short:
        'A restaurant for enjoying Atlantic produce, the calm of the Galician coast and a table with the taste of Valdovino.',
    },
    about: {
      title: 'A table by the sea',
      body:
        'At A Saina we cook with respect for produce and tradition. Our kitchen comes from the Atlantic: fresh fish, seasonal seafood, rice dishes to share and classic Galician plates served beside Playa da Frouxeira.',
      highlight: 'Here, the sea is not decoration. It is part of the experience.',
    },
    specialities: {
      title: 'Market produce, gentle cooking and time at the table',
      items: [
        {
          title: 'Fresh fish',
          description: 'Turbot, sea bass, cod, monkfish and seasonal catches grilled or cooked Galician style.',
        },
        {
          title: 'Seafood',
          description: 'Goose barnacles, mussels, clams, razor clams and seafood depending on the market.',
        },
        {
          title: 'Rice dishes',
          description: 'Seafood rice and paellas to share, prepared with fresh produce.',
        },
        {
          title: 'Galician cooking',
          description: 'Octopus, empanada, meats, homemade desserts and traditional flavours.',
        },
      ],
    },
    experience: {
      title: 'Eating in Valdovino tastes different',
      body:
        'Playa da Frouxeira is one of Ferrolterra coast most special landscapes: white sand, dunes, Atlantic wind, surf and a natural lagoon beside the beach. A Saina reflects that character with simple cooking, fresh produce and an unhurried experience.',
    },
    reservation: {
      title: 'Book your table',
      eyebrow: 'Online reservations',
      body: 'Choose date, time and party size. We will confirm your booking so you only have to come and enjoy.',
      microcopy: 'Book your table by the Atlantic.',
      languages: [
        ['ES', 'Reserva tu mesa frente al Atlantico.'],
        ['GL', 'Reserva a tua mesa fronte ao Atlantico.'],
        ['EN', 'Book your table by the Atlantic.'],
        ['FR', 'Reservez votre table face a l Atlantique.'],
        ['DE', 'Reservieren Sie Ihren Tisch am Atlantik.'],
      ],
    },
    menu: {
      title: 'Demo menu',
      eyebrow: 'Galician produce and market cooking',
      note: 'Prices and dishes are fictitious demo content. They must be reviewed and approved before production.',
      categories: menuCategories,
    },
    gallery: {
      title: 'Gallery',
      images: galleryImages,
    },
    location: {
      title: 'At Playa da Frouxeira',
      body:
        'Find us at Avenida de Saina, 30, Valdovino, A Coruna. A perfect place to eat after a beach walk, a surf session or a trip along the Galician coast.',
      hours: 'Lunch from 13:00 to 15:00. Dinner from 20:30 to 22:00.',
      directions: 'Directions',
      call: 'Call now',
      reserve: 'Book a table',
    },
  },
} satisfies Record<
  Locale,
  {
    seo: {
      title: string;
      description: string;
      keywords: string;
    };
    nav: Record<string, string>;
    hero: {
      eyebrow: string;
      title: string;
      subtitle: string;
      reserve: string;
      menu: string;
      short: string;
    };
    about: {
      title: string;
      body: string;
      highlight: string;
    };
    specialities: {
      title: string;
      items: {
        title: string;
        description: string;
      }[];
    };
    experience: {
      title: string;
      body: string;
    };
    reservation: {
      title: string;
      eyebrow: string;
      body: string;
      microcopy: string;
      languages: string[][];
    };
    menu: {
      title: string;
      eyebrow: string;
      note: string;
      categories: {
        title: string;
        items: string[][];
      }[];
    };
    gallery: {
      title: string;
      images: {
        src: string;
        alt: string;
      }[];
    };
    location: {
      title: string;
      body: string;
      hours: string;
      directions: string;
      call: string;
      reserve: string;
    };
  }
>;
