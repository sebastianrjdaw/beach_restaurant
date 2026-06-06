export type Locale = 'es' | 'en';

export type Translated = Record<Locale, string> & Record<string, string>;

export type RestaurantSettings = {
  id?: number;
  name: string;
  description?: Translated | null;
  phone?: string | null;
  email?: string | null;
  address?: string | null;
  city?: string | null;
  country?: string | null;
  max_days_in_advance?: number | null;
  max_reservations_per_slot?: number | null;
  max_guests_per_slot?: number | null;
};

export type TimeSlot = {
  time: string;
  ends_at: string;
  label: string;
  shift?: string;
  is_available?: boolean;
  disabled_reason?: string | null;
};

export type MenuItem = {
  id: number;
  name: Translated;
  description?: Translated | null;
  price?: string | null;
};

export type MenuCategory = {
  id: number;
  name: Translated;
  items: MenuItem[];
};

export type Menu = {
  id: number;
  name: Translated;
  description?: Translated | null;
  categories: MenuCategory[];
};
