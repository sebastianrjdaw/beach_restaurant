const images = [
  'https://images.unsplash.com/photo-1514933651103-005eec06c04b?auto=format&fit=crop&w=900&q=80',
  'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=900&q=80',
  'https://images.unsplash.com/photo-1559339352-11d035aa65de?auto=format&fit=crop&w=900&q=80',
];

export function GallerySection() {
  return (
    <section className="bg-slate-950 px-6 py-20">
      <div className="mx-auto grid max-w-6xl gap-4 md:grid-cols-3">
        {images.map((src) => (
          <img key={src} className="h-72 w-full rounded-md object-cover" src={src} alt="" />
        ))}
      </div>
    </section>
  );
}
