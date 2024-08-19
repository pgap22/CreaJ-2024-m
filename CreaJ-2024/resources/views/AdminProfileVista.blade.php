<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
    <title>AdminProfileVista</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
        }
    </style>
</head>
<body>

    <div>
        <div class="hidden md:flex p-4 bg-white items-center justify-between shadow-md">
            <a href="{{ route('admin.index') }}">
            <h1 class="text-3xl md:text-4xl lg:text-5xl font-semibold">
                 Mini <span class="text-purple-600"><b>Admin</b></span>
            </h1>
            </a>
            <div class="flex gap-8">
                <a href="{{ route('admin.index') }}"
                    class="font-semibold uppercase text-sm lg:text-base hover:text-gray-300 px-2 py-1">Mercados</a>
                  <a href="{{ route('admin.vendedores') }}"
                    class="font-semibold uppercase text-sm lg:text-base hover:text-gray-300 px-2 py-1">Vendedores</a>
                <a href="{{ route('admin.clientes') }}"
                    class="font-semibold uppercase text-sm lg:text-base hover:text-gray-300 px-2 py-1">Clientes</a>
                    <a href="{{ route('AdminProfileVista')}}"
                    class="font-semibold uppercase text-sm lg:text-base hover:text-white hover:bg-black border border-black px-2 py-1 rounded-md">
                        Perfil
                    </a>
            </div>
        </div>

         <div class=""> <!-- Añadido un margen inferior -->
            <!--INICIO DE NAVBAR MOBIL-->
            <div class="bottom-bar fixed bottom-[1%] left-0 right-0 flex justify-center md:hidden">
                <div class="bg-gray-900 rounded-2xl w-64 h-14 flex justify-around ">
                    <div class="flex items-center  ">
                        <a href="{{ route('admin.index') }}" class=" bg-white rounded-full p-[0.25rem] "><img class="w-6" src="{{ asset('imgs/HomeSelectedIcon.png') }}" alt="User Icon"></a>
                    </div>
                    <div class="flex items-center">
                        <a href="{{ route('admin.vendedores') }}"><img class="w-6" src="{{ asset('imgs/VendedorIcon.png') }}" alt="User Icon"></a>
                    </div>
                    <div class="flex items-center">
                        <a href="{{ route('admin.clientes') }}" ><img class="w-6" src="{{ asset('imgs/ClienteIcon.png') }}" alt="User Icon"></a>
                    </div>
                    <div class="flex items-center">
                <?php $id = 1; ?>
                        <a href="{{ route('AdminProfileVista')}}"  ><img class="w-6" src="{{ asset('imgs/UserIcon.png') }}" alt="User Icon"></a>
                    </div>
                </div>
                <!--FIN DE NAVBAR MOBIL-->
            </div>

            <!--FIN DE NAVBAR MOBIL-->
        </div>
        <div class="bg-violet-500 h-auto pb-[4rem] pt-[2rem] w-full flex items-center justify-center">
            <h3 class="text-[3rem] font-bold lg:text-[5rem]">Mini<span class="text-white ml-2">Shop</span></h3>
        </div>

        <div class="flex justify-center mt-5">
            <img class="w-20 bg-white rounded-full shadow-md  " src="{{ asset('imgs/usuario.png') }}" alt="User Icon">
        </div>
        <div class="flex justify-center mt-2 ">
            <img class="w-3 h-3 ml-1" src="{{ asset('imgs/estrella.png') }}" alt="User Icon">
            <img class="w-3 h-3 ml-1" src="{{ asset('imgs/estrella.png') }}" alt="User Icon">
            <img class="w-3 h-3 ml-1" src="{{ asset('imgs/estrella.png') }}" alt="User Icon">
            <img class="w-3 h-3 ml-1" src="{{ asset('imgs/estrella.png') }}" alt="User Icon">
            <img class="w-3 h-3 ml-1" src="{{ asset('imgs/estrella.png') }}" alt="User Icon">
            <h3 class="text-[10px]"> <span class="ml-2">5.0</span></h3>
        </div>
        <div class="text-center mt-3">
            <h3 class="text-xs">Administrador de MiniShop</h3>
            <h3 class="font-bold">Administrador General</h3>
            <h3 class="text-xs">administracion@minishop.sv</h3>
        </div>
        @auth
        <div> {{ Auth::user()->nombre }}
            {{ Auth::user()->apellido }} </div>
        <div> {{ Auth::user()->telefono }} </div>
    @endauth

        <div class="w-[50%] mx-auto mt-16">
            <div class=" mx-auto flex items-center">
                <img class="w-5" src="{{ asset('imgs/ReservasIcon.png') }}" alt="User Icon">
                <h3 class="flex-grow text-left font-bold ml-3">Historial De pedidos</h3>
            </div>

            <div class=" mx-auto flex items-center mt-10">
                <img class="w-5" src="{{ asset('imgs/BuzonIcon.png') }}" alt="User Icon">
                <h3 class="flex-grow text-left font-bold ml-5">Estado De pedidos</h3>
            </div>

            <div class=" mx-auto flex items-center mt-10">
                <img class="w-5" src="{{ asset('imgs/addIcon.png') }}" alt="User Icon">
                <h3 class="flex-grow text-left font-bold ml-5">Agregar Mercado</h3>
            </div>

            <form action="{{ route('logout') }}" method="GET">
                @csrf
                <div class="mx-auto flex items-center mt-10">
                    <img class="w-5" src="{{ asset('imgs/tuerca.png') }}" alt="User Icon">
                    <button type="submit" class="flex-grow text-left font-bold ml-5">Cerrar Cuenta</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
