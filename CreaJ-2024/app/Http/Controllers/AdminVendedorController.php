<?php

namespace App\Http\Controllers;

use App\Models\Vendedor;
use App\Models\Cliente;
use App\Models\User; // Asegúrate de incluir el modelo User
use Illuminate\Http\Request; // Asegúrate de incluir Request
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class AdminVendedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vendedors = Vendedor::paginate();
        return view('AdminListadoVendedores', compact('vendedors'))
            ->with('i', (request()->input('page', 1) - 1) * $vendedors->perPage());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $vendedor = new Vendedor();
        $mercados = MercadoLocal::all(); // Suponiendo que tu modelo de mercados se llama MercadoLocal
        return view('AdminRegistrarVendedor', compact('vendedor', 'mercados'));

    }

    /**
     * Store a newly created resource in storage.
     */


     public function store(VendedorRequest $request)
     {
         // Validar los datos del formulario
         $validator = Validator::make($request->all(), [
             'usuario' => 'required|email|unique:vendedors',
             'imagen_de_referencia' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
             'nombre' => 'required|string|max:255',
             'nombre_del_local' => 'required|string|max:255',
             'apellidos' => 'required|string|max:255',
             'telefono' => 'required|string|max:20|unique:vendedors',
             'numero_puesto' => 'required|integer',
             'password' => 'required|string|min:8|confirmed',
             'clasificacion' => 'required|string|max:255',
             'fk_mercado' => 'required|exists:mercado_locals,id',
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

         return redirect()->route('admin-vendedors.index')
             ->with('success', 'Vendedor creado exitosamente.');
     }


    /* Display the specified resource.
     */
    public function show($id)
    {
        $vendedor = Vendedor::find($id);
        return view('vendedor.show', compact('vendedor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $vendedor = Vendedor::find($id);
        return view('AdminEditarVendedor', compact('vendedor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validar los datos del formulario
        $validator = Validator::make($request->all(), [
            'usuario' => 'required|email|unique:vendedors,usuario,' . $id . '|unique:users,usuario,' . $id,
            'nombre' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'telefono' => 'required|string|max:20|unique:vendedors,telefono,' . $id . '|unique:users,telefono,' . $id,
            'numero_puesto' => 'required|string|max:255|unique:vendedors,numero_puesto,' . $id,
            'fk_mercado' => 'required|exists:mercado_locals,id',
            'contrasena' => 'nullable|string|min:8|confirmed',
        ]);

        // Verificar si la validación falla
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $vendedor = Vendedor::find($id);

        // Actualizar campos del vendedor
        $vendedor->usuario = $request->usuario;
        $vendedor->nombre = $request->nombre;
        $vendedor->apellidos = $request->apellidos;
        $vendedor->telefono = $request->telefono;
        $vendedor->numero_puesto = $request->numero_puesto;
        $vendedor->fk_mercado = $request->fk_mercado;
        if ($request->filled('contrasena')) {
            $vendedor->contrasena = bcrypt($request->contrasena); // Encriptar la contraseña
        }
        $vendedor->save();

        // Actualizar el usuario en la tabla `users`
        $user = User::where('usuario', $request->usuario)->first();
        if ($user) {
            $user->usuario = $request->usuario;
            $user->nombre = $request->nombre;
            $user->apellido = $request->apellidos;
            $user->telefono = $request->telefono;
            if ($request->filled('contrasena')) {
                $user->password = Hash::make($request->contrasena);
            }
            $user->save();
        }

        return redirect()->route('admin-vendedors.index')
            ->with('success', 'Vendedor actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $vendedor = Vendedor::find($id);

        // Eliminar el usuario en la tabla `users`
        User::where('usuario', $vendedor->usuario)->delete();

        $vendedor->delete();

        return redirect()->route('admin-vendedors.index')
            ->with('success', 'Vendedor eliminado exitosamente.');
    }
}
