# 🧩 Hooks y API

TuReserva incluye filtros y acciones personalizadas:

```php
do_action('tureserva_after_booking_save', $reserva_id);
apply_filters('tureserva_alojamiento_precio_final', $precio, $alojamiento_id);
