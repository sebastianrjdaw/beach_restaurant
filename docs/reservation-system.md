# Sistema de reservas

Este documento resume las funcionalidades actuales del sistema de reservas de Restaurante A Saina, como funciona el flujo publico y que parametros puede editar el administrador.

## Accesos

Web publica:

```txt
http://localhost:8000
```

Formulario publico de reservas:

```txt
http://localhost:8000/reservar
```

Panel de administracion:

```txt
http://localhost:8000/admin
```

Credenciales de desarrollo:

```txt
Email: admin@example.com
Password: password
```

Si el usuario no existe despues de ejecutar tests o reiniciar datos:

```bash
docker compose exec app php artisan db:seed --force
```

El entorno Docker tambien ejecuta explicitamente `AdminUserSeeder` en cada arranque del servicio `app`, por lo que el usuario de desarrollo debe recrearse automaticamente.

Comando dedicado para recrear solo el usuario admin:

```bash
docker compose exec app php artisan db:seed --class=AdminUserSeeder --force
```

## Flujo de reserva publica

El cliente accede desde la landing mediante el boton `Reservar mesa`.

El formulario solicita:

- Fecha.
- Hora.
- Numero de comensales.
- Nombre.
- Telefono.
- Email.
- Preferencia de zona: interior o terraza.
- Comentarios o alergias.

El idioma preferido no se pregunta en el formulario. El sistema guarda el idioma a partir del idioma en el que el usuario esta viendo la web:

```txt
/reservar?lang=es
/reservar?lang=en
```

La preferencia de zona se guarda dentro de los comentarios de la reserva para que el responsable la vea en el panel.

## Disponibilidad publica

La disponibilidad se calcula con:

- Horarios registrados.
- Duracion por defecto de cada reserva.
- Intervalo entre horas disponibles.
- Mesas activas y capacidad.
- Reservas existentes en estado `pending` o `confirmed`.
- Bloqueos manuales.
- Limites operativos por turno.
- Fecha maxima reservable.
- Hora actual, si la reserva es para hoy.

En la landing se muestran todos los turnos del dia. Los turnos no reservables aparecen deshabilitados.

Ejemplos:

- Si hoy son las `19:30`, un turno de `13:00` aparece deshabilitado.
- Si no quedan mesas suficientes para 6 personas, ese turno aparece deshabilitado para 6 personas.
- Si se supera el limite publico de reservas o comensales por turno, ese turno no se ofrece online.

Aunque el frontend deshabilita horas no validas, el backend vuelve a validar:

- No permite reservar una hora pasada.
- No permite reservar mas alla de los dias maximos configurados.
- No permite reservar si ya no hay disponibilidad real.

## Estados de reserva

Estados disponibles:

- `pending`: reserva solicitada, pendiente de confirmar.
- `pending_email_verification`: reserva pendiente de que el cliente confirme su email.
- `confirmed`: reserva confirmada.
- `cancelled`: reserva cancelada.
- `completed`: reserva completada.
- `no_show`: el cliente no se presento.

Origenes disponibles:

- `web`: reserva creada desde la web publica.
- `phone`: reserva telefonica.
- `admin`: reserva creada manualmente desde el panel.

## Panel de administracion

El panel usa Filament y esta pensado para funcionar en escritorio, tablet y movil.

### Dashboard

Muestra metricas rapidas:

- Reservas pendientes.
- Reservas de hoy.
- Comensales previstos hoy.

### Reservas

Ruta:

```txt
/admin/reservations
```

Permite:

- Ver el listado de reservas.
- Filtrar por estado.
- Filtrar por hoy.
- Filtrar por rango de fechas.
- Buscar por cliente.
- Crear nuevas reservas.
- Editar reservas existentes.
- Confirmar reservas pendientes.
- Marcar como completada.
- Marcar como no-show.
- Cancelar reservas.

Al crear una reserva desde admin, el sistema usa la misma logica de asignacion de mesas que la reserva publica.

### Planning diario

Ruta:

```txt
/admin/daily-reservation-planner
```

Permite seleccionar un dia y ver los turnos registrados para esa fecha.

Por cada turno muestra:

- Hora de inicio.
- Hora de fin.
- Numero de reservas activas.
- Numero de comensales.
- Mesas asignadas.
- Capacidad total de las mesas asignadas.
- Limites configurados para reservas y comensales.
- Aviso si se supera algun limite operativo.
- Detalle de cada reserva del turno.

El detalle de cada reserva incluye:

- Cliente.
- Numero de comensales.
- Telefono.
- Email.
- Estado.
- Mesas asignadas.
- Comentarios.

Esta vista esta pensada para que el responsable pueda revisar desde movil o tablet como queda el servicio de comida o cena.

El planning se refresca automaticamente cada pocos segundos. Si una reserva se confirma o modifica desde otra pantalla, la vista se actualiza sin recargar manualmente.

Acciones rapidas disponibles desde cada reserva:

- Confirmar.
- Llamar al cliente.
- Marcar como completada.
- Marcar como no-show.
- Cancelar.
- Mover fecha y hora.

Al mover una reserva:

- Se recalcula la hora de fin segun la duracion configurada.
- Se comprueba disponibilidad real de mesas.
- Se reasignan mesas disponibles.
- Se puede enviar email automatico al cliente avisando del cambio.
- Hay una opcion marcada como `WhatsApp futuro`, preparada para integrar un proveedor WhatsApp mas adelante.

### Configuracion de reservas

Ruta:

```txt
/admin/reservation-settings
```

Parametros editables:

- `Dias maximos reservables`: cuantos dias hacia delante permite reservar la web.
- `Duracion reserva`: duracion estimada de cada reserva, en minutos.
- `Intervalo entre horas`: separacion entre slots de reserva.
- `Maximo reservas por hora`: limite operativo de reservas para un mismo turno.
- `Maximo comensales por hora`: limite operativo de personas para un mismo turno.
- `Modo confirmacion web`: manual, automatica o automatica con verificacion email.
- `Caducidad verificacion`: minutos durante los que el enlace de verificacion es valido.
- `Antelacion minima`: minutos minimos antes de la hora de reserva.
- `Min. comensales online`.
- `Max. comensales online`.
- `Grupo grande desde`: umbral para forzar revision manual.
- `Grupos grandes requieren revision manual`.
- `Zona preferida estricta`.
- `Permitir cancelaciones publicas`.
- `Margen minimo para cancelar`: horas minimas antes de la reserva.

Estos limites afectan al canal online. En admin, si una reserva telefonica o interna supera los limites, el sistema muestra aviso pero permite crearla.

## Comportamiento de reservas telefonicas o internas

Las reservas creadas desde administracion pueden tener origen `phone` o `admin`.

Si se supera el limite de reservas o comensales por turno:

- El sistema avisa.
- La reserva se puede crear igualmente.

Esto permite gestionar casos extraordinarios:

- Clientes habituales.
- Eventos puntuales.
- Ajustes manuales de sala.
- Reservas aceptadas directamente por el responsable.

## Modos de confirmacion web

El campo `web_reservation_confirmation_mode` controla como entra una reserva creada desde la web publica.

Valores:

- `manual`: la reserva entra como `pending` y el responsable la confirma desde admin.
- `auto`: la reserva entra directamente como `confirmed` si hay disponibilidad real.
- `auto_with_email_verification`: la reserva entra como `pending_email_verification` y el cliente recibe un email con enlace de confirmacion.

Los grupos grandes pueden forzar revision manual aunque el modo sea `auto`, si esta activo:

```txt
large_party_requires_manual_confirmation = true
large_party_threshold = 8
```

## Emails automaticos

El sistema envia emails basicos al cliente cuando hay email disponible:

- Reserva recibida y pendiente.
- Reserva confirmada.
- Confirmacion de email.
- Reserva cancelada.

En local se usa la configuracion de mail del proyecto. Si `MAIL_MAILER=log`, los emails quedan en logs.

## Cancelacion publica

Cada reserva tiene un `public_token`.

Ruta publica:

```txt
/reservas/{token}/cancelar
```

Comportamiento:

- Muestra resumen de la reserva.
- Permite indicar motivo de cancelacion.
- Cambia el estado a `cancelled`.
- Guarda `cancelled_at` y `cancel_reason`.
- Envia email de cancelacion si la reserva tiene email.

Si falta menos del margen configurado en `min_hours_before_public_cancellation`, se bloquea la cancelacion publica y se pide llamar al restaurante.

## Preferencia de zona

La preferencia de zona ya no se guarda dentro de comentarios.

Campo real:

```txt
reservations.preferred_area_id
```

Relación:

```txt
reservations.preferred_area_id -> areas.id
```

El cliente puede seleccionar:

- Sin preferencia.
- Interior.
- Terraza.

Si `strict_area_preference` es `true`, la disponibilidad publica solo considera mesas de esa zona.

Si `strict_area_preference` es `false`, el sistema intenta priorizar la zona preferida, pero puede asignar mesas de otra zona si hace falta.

## Parametros tecnicos principales

Tabla `restaurant_settings`:

- `default_reservation_duration`
- `reservation_interval`
- `max_days_in_advance`
- `max_reservations_per_slot`
- `max_guests_per_slot`
- `web_reservation_confirmation_mode`
- `email_verification_expiration_minutes`
- `allow_public_cancellations`
- `min_hours_before_public_cancellation`
- `strict_area_preference`
- `min_guests_online`
- `max_guests_online`
- `large_party_requires_manual_confirmation`
- `large_party_threshold`
- `min_minutes_before_reservation`
- `timezone`
- `default_locale`
- `locales`

Tabla `opening_hours`:

- `weekday`
- `opens_at`
- `closes_at`
- `is_closed`
- `label`

Ejemplo de turnos actuales:

- Comida: `13:00` a `16:30`.
- Cena: `20:30` a `23:30`.

Tabla `tables`:

- `area_id`
- `name`
- `capacity`
- `is_active`

Las mesas activas son las que se usan para calcular disponibilidad y asignacion.

## Comandos utiles

Ejecutar migraciones:

```bash
docker compose exec app php artisan migrate --force
```

Sembrar datos demo:

```bash
docker compose exec app php artisan db:seed --force
```

Publicar assets de Filament:

```bash
docker compose exec app php artisan filament:assets
```

Limpiar caches:

```bash
docker compose exec app php artisan optimize:clear
```

Ejecutar tests:

```bash
docker compose exec app php artisan test
```

Compilar frontend:

```bash
docker compose exec app npm run build
```
