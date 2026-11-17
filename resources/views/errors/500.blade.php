@include('errors.minimal', [
    'code' => 500,
    'title' => 'Error interno del servidor',
    'message' => 'Ocurrió un problema inesperado. Inténtalo nuevamente en unos minutos o contacta al administrador.',
])
