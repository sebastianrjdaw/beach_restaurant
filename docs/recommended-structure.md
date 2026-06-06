# Recommended Structure

```txt
app/
  Enums/
  Http/
    Controllers/Public/
    Requests/
  Models/
  Services/
database/
  migrations/
  seeders/
resources/js/
  Components/
    Home/
    Reservation/
    ui/
  Pages/
routes/
  web.php
tests/
  Feature/
  Unit/
```

The public booking flow should stay in controllers, requests, services and Inertia pages. Admin CRUD belongs in Filament resources so phone and web reservations use the same models and services.
