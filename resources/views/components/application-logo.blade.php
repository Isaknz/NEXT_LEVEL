{{-- El logo vive en public/img para no depender de que los assets esten
     compilados: si el build de Vite no esta, el login seguira mostrando el
     logo en lugar de un espacio vacio. --}}
<img
    src="{{ asset('img/logo_next.png') }}"
    alt="Logo de Next Level School"
    {{ $attributes }}
>
