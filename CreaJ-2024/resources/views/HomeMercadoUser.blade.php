<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
    <title>Home Mercado User</title>
</head>
<body>
    <div class="mx-auto max-w-lg mt-10 mb-32"> <!-- Añadido un margen inferior -->
        <div class="bottom-bar fixed bottom-[5%] left-0 right-0 flex justify-center">
            <div class="bg-gray-800 rounded-2xl w-60 h-10 flex justify-around">
                <div class="flex items-center">
                    <button><img class="w-4" src="{{ asset('imgs/casa2.png') }}" alt="User Icon"></button>
                </div>
        
                <div class="flex items-center">
                    <button><img class="w-4" src="{{ asset('imgs/casa2.png') }}" alt="User Icon"></button>
                </div>
        
                <div class="flex items-center">
                    <button><img class="w-4" src="{{ asset('imgs/casa2.png') }}" alt="User Icon"></button>
                </div>
                <div class="flex items-center">
                    <button><img class="w-4" src="{{ asset('imgs/casa2.png') }}" alt="User Icon"></button>
                </div>
            </div>
        </div>

        <div class="mt-14 border-4 w-[90%] mx-auto"> 
            
            <div class="flex justify-between"> <!--Contenedor Principal-->
                <div>
                    <div>
                        Nombre del Mercado
                    </div>
                    <div class="font-bold">
                        Los mejores Precios
                    </div>
                </div>

                <div>
                    <img class="w-5 mt-[50%]" src="{{ asset('imgs/usuario.png') }}" alt="User Icon">
                </div>
            </div><!--Fin Principal-->
                
              
        </div>
    </div>

</body>
</html>