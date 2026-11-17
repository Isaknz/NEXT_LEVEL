@include('errors.minimal', [
    'code' => 429,
    'title' => 'Demasiadas solicitudes',
    'message' => 'Has realizado demasiadas solicitudes en poco tiempo. Espera unos minutos e inténtalo nuevamente.',
])
