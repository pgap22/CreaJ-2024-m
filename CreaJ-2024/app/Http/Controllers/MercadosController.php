<?php

namespace App\Http\Controllers;

//modelos
use App\Models\User;
use App\Models\Clientes;
use App\Models\MercadoLocal;
use App\Models\Vendedor;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationItem;
//request
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Requests\ClienteRequest;
use App\Http\Requests\MercadoLocalRequest;
use App\Http\Requests\VendedorRequest;
use App\Http\Requests\CartRequest;
use App\Http\Requests\ReservationRequest;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\VendedorRequest as RequestsVendedorRequest;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;


/**
 * Class UsuariosController
 * @package App/Http/Controllers
 */

 class MercadosController extends Controller
 {
    public function index(){
        $id = 1;
        // Buscar el mercado local por ID
    $mercadoLocal = MercadoLocal::find($id);

    // Obtener los vendedores con fk_mercado igual al ID del mercado local con paginación
    $vendedors = Vendedor::where('fk_mercado', $id)->paginate();

    // Retornar la vista con ambos datos
    return view('MercadoHome', compact('mercadoLocal', 'vendedors'))
        ->with('i', (request()->input('page', 1) - 1) * $vendedors->perPage());


    }
    function editar($id){
        $mercadoLocal = MercadoLocal::findOrFail($id); // Encontrar mercado local por ID

        // Retornar vista 'AdminEditarMercado' con el mercado local a editar
        return view('AdminEditarMercado', compact('mercadoLocal'));
    }



    /**
     * VENDEDORES
     */
    public function listavendedores(){
        $fk_mercado = 1;//session('fk_mercado');

        // Filtrar los vendedores por el mercado de la sesión activa
        $vendedores = Vendedor::where('fk_mercado', $fk_mercado)->paginate();

        return view('MercadoListadoVendedores', compact('vendedores'))
            ->with('i', (request()->input('page', 1) - 1) * $vendedores->perPage());

    }
    public function editarvendedor($id){
        //MercadoEditarVendedor.blade.php
        $vendedor = Vendedor::find($id);
        $mercados = MercadoLocal::all();
        return view('MercadoEditarVendedor', compact('vendedor', 'mercados'));
    }
    public function actualizarvendedor(VendedorRequest $request ,$id){
        $request->validate([
            'password' => 'nullable|string|min:8|confirmed',
            'nombre' => 'required|string|max:255',
            'nombre_del_local' => 'required|string|max:255',
            'imagen_de_referencia' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'clasificacion' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'telefono' => 'required|string|max:255',
            'numero_puesto' => 'required|integer|unique:vendedors,numero_puesto,' . $id,
            'fk_mercado' => 'required|exists:mercado_locals,id',
        ]);

        // Encontrar el vendedor por ID
        $vendedor = Vendedor::findOrFail($id);

        // Actualizar los campos
        $vendedor->usuario = $request->input('usuario');
        $vendedor->ROL = $request->input('ROL');

        // Si la contraseña se envía, actualiza, de lo contrario, deja la existente
        if ($request->filled('password')) {
            $vendedor->password = bcrypt($request->input('password'));
        }

        $vendedor->nombre = $request->input('nombre');
        $vendedor->nombre_del_local = $request->input('nombre_del_local');
        $vendedor->clasificacion = $request->input('clasificacion');
        $vendedor->apellidos = $request->input('apellidos');
        $vendedor->telefono = $request->input('telefono');
        $vendedor->numero_puesto = $request->input('numero_puesto');
        $vendedor->fk_mercado = $request->input('fk_mercado');

        // Manejar la imagen de referencia
        if ($request->hasFile('imagen_de_referencia')) {
            // Guardar la imagen
            $imageName = time() . '.' . $request->imagen_de_referencia->extension();
            $request->imagen_de_referencia->move(public_path('imgs'), $imageName);

            // Eliminar la imagen antigua si existe
            if ($vendedor->imagen_de_referencia && file_exists(public_path('imgs/' . $vendedor->imagen_de_referencia))) {
                unlink(public_path('imgs/' . $vendedor->imagen_de_referencia));
            }

            // Actualizar la referencia en la base de datos
            $vendedor->imagen_de_referencia = $imageName;
        }

        // Guardar los cambios en la base de datos
        $vendedor->save();

        // Redireccionar o devolver una respuesta
        return redirect()->route('mercados.listavendedores', $id)->with('success', 'Vendedor actualizado correctamente.');
    }
    public function agregarvendedor(){
        //Mercado Registrar Vendedor
        $vendedor = new Vendedor();
        $mercados = MercadoLocal::all(); // Suponiendo que tu modelo de mercados se llama MercadoLocal
        return view('MercadoRegistrarVendedor', compact('vendedor', 'mercados'));
    }
    public function guardarvendedor(VendedorRequest $request){
        $validator = Validator::make($request->all(), [
            'usuario' => 'required|email|unique:vendedors',
            'imagen_de_referencia' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'nombre' => 'required|string|max:255',
            'nombre_del_local' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'telefono' => 'required|string|max:20|unique:vendedors',
            'numero_puesto' => 'required|integer',
            'password' => 'required|string|min:8|confirmed',  // Regla de longitud mínima
            'clasificacion' => 'required|string|max:255',
            'fk_mercado' => 'required|exists:mercado_locals,id',
        ], [
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);


        // Verificar si la validación falla
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Verificar si ya existen datos iguales en la base de datos
        if (Vendedor::where('usuario', $request->usuario)->exists() || Vendedor::where('telefono', $request->telefono)->exists()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ya existe un vendedor con el mismo Correo Electrónico o Teléfono.');
        }

        // Verificar si el numero_puesto ya existe en el mismo mercado
        if (Vendedor::where('fk_mercado', $request->fk_mercado)->where('numero_puesto', $request->numero_puesto)->exists()) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ya existe un vendedor con el mismo Numero de Puesto en este Mercado.');
        }


        // Crear vendedor si la validación pasa y no hay datos repetidos
        $vendedor = new Vendedor();
        $vendedor->usuario = $request->usuario;
        $vendedor->nombre = $request->nombre;
        $vendedor->nombre_del_local = $request->nombre_del_local;
        $vendedor->apellidos = $request->apellidos;
        $vendedor->telefono = $request->telefono;
        $vendedor->numero_puesto = $request->numero_puesto;
        $vendedor->fk_mercado = $request->fk_mercado;
        $vendedor->password = Hash::make($request->password); // Encriptar la contraseña
        $vendedor->clasificacion = $request->clasificacion;

        // Manejar la carga de la imagen
        if ($request->hasFile('imagen_de_referencia')) {
            $file = $request->file('imagen_de_referencia');
            $imageName = $request->nombre . '_' . $request->nombre_del_local . '.png';

            // Movimiento
            $file->move(public_path('imgs'), $imageName);
            // Guardar
            $vendedor->imagen_de_referencia = $imageName;
        }

        $vendedor->save();

        return redirect()->route('mercados.listadovendedores')
            ->with('success', 'Vendedor creado exitosamente.');
    }

    
    public function vervendedor($id){
        //puestodelvendedor
        $vendedor = Vendedor::find($id);

        if(!$vendedor) {
            return redirect()->back()->with('error','Vendedor no encontrado');
        }

        //Esta variable es para sacar el nombre del fk__mercadolocal
        $mercadoLocal = $vendedor->mercadoLocal;

        //esta varaible es para sacar los productos
        $products = Product::where('fk_vendedors',$id)->paginate();

        return view('MercadoPuestoVendedor', compact('vendedor','mercadoLocal','products'))->with('i',(request()->input('page',1) - 1) * $products->perPage());

    }
    public function eliminarvendedor($id){
        $vendedor = Vendedor::find($id);

        // Eliminar el usuario en la tabla `users`
        User::where('usuario', $vendedor->usuario)->delete();

        $vendedor->delete();

        return redirect()->route('mercados.vervendedor')
            ->with('success', 'Vendedor eliminado exitosamente.');

    }

    /**
     * RESERVAS
     */
    public function reservas(){
         // Obtener el ID del mercado del usuario autenticado
    $fk_mercado = session('fk_mercado');

    // Filtrar las reservas por el mercado al que pertenecen
    $reservations = Reservation::whereHas('vendedors', function ($query) use ($fk_mercado) {
        $query->where('fk_mercado', $fk_mercado);
    })->with('items.product')->get();

    return view('UserEstadoReservas', compact('reservations'));

    }
    public function reservadelvendedor(){

    }
    public function editarreservas(){

    }

    public function verproducto($id)
{
    // Obtener el producto específico
    $product = Product::find($id);

    // Verificar si el producto existe antes de proceder
    if (!$product) {
        return redirect()->back()->with('error', 'Producto no encontrado.');
    }

    // Obtener el vendedor del producto
    $vendedor = $product->vendedor;

    // Obtener los productos que comparten la misma llave foránea del vendedor,
    // pero excluyendo el producto actual
    $products = Product::where('fk_vendedors', $product->fk_vendedors)
        ->where('id', '!=', $id)
        ->paginate();

    // Retornar la vista con los datos del producto y otros productos del mismo vendedor
    return view('MercadoProductoEspecifico', compact('product', 'products', 'vendedor'))
        ->with('i', (request()->input('page', 1) - 1) * $products->perPage());
}


 }
