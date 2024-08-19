<?php

namespace App\Http\Controllers;

//modelos
use App\Models\User;
use App\Models\Cliente;
use App\Models\MercadoLocal;
use App\Models\Vendedor;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationItem;
//request
use Illuminate\Http\Request;
use App\Http\Request\UserRequest;
use App\Http\Request\ClienteRequest;
use App\Http\Request\MercadoLocalRequest;
use App\Http\Request\VendedorRequest;
use App\Http\Request\CartRequest;
use App\Http\Request\ReservationRequest;
use App\Http\Request\ProductRequest;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;



/**
 * Class UsuariosController
 * @package App/Http/Controllers
 */

 class UsuariosController extends Controller{


    //VER MERCADOS LOCALES O INDEX
    public function index(){
        $mercadoLocals = MercadoLocal::paginate();
        $vendedors = Vendedor::paginate();

        $iVendedors = (request()->input('page', 1) - 1) * $vendedors->perPage();
        $iMercadoLocals = (request()->input('page', 1) - 1) * $mercadoLocals->perPage();

        // Retorna la vista 'UserHome' con los datos paginados
        return view('UserHome', compact('vendedors', 'mercadoLocals'))
        ->with('iVendedors', $iVendedors)
        ->with('iMercadoLocals', $iMercadoLocals);
    }

    //VER MERCADO Y SUS PUESTOS
    public function mercado($id)
    {
        // Buscar el mercado local por ID
        $mercadoLocal = MercadoLocal::find($id);

        // Obtener los vendedores con fk_mercado igual al ID del mercado local con paginación
        $vendedors = Vendedor::where('fk_mercado', $id)->paginate();

        // Retornar la vista con ambos datos
        return view('UserPuestosVendedores', compact('mercadoLocal', 'vendedors'))
            ->with('i', (request()->input('page', 1) - 1) * $vendedors->perPage());
    }

    //VER VENDEDOR Y SUS PRODUCTOS
    public function vendedor($id)
    {
        $vendedor = Vendedor::find($id);

        if(!$vendedor) {
            return redirect()->back()->with('error','Vendedor no encontrado');
        }

        //Esta variable es para sacar el nombre del fk__mercadolocal
        $mercadoLocal = $vendedor->mercadoLocal;

        //esta varaible es para sacar los productos
        $products = Product::where('fk_vendedors',$id)->paginate();

        return view('UserProductosDeUnPuesto', compact('vendedor','mercadoLocal','products'))->with('i',(request()->input('page',1) - 1) * $products->perPage());
    }

    //VER EL PRODCUTO
    public function producto($id)
    {
        // Obtener el producto específico por su ID
        $product = Product::find($id);

        // Obtener todos los productos con paginación
        $products = Product::where('id', '!=', $id)->paginate();
        $vendedor = $product->vendedor;

        // Retornar la vista con ambos datos
        return view('UserProductoEnEspecifico', compact('product', 'products','vendedor'))
            ->with('i', (request()->input('page', 1) - 1) * $products->perPage());
    }

    //AGREGAR EL PRODUCTO AL CARRITO
    public function addcarrito(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $quantity = $request->input('quantity');

        $cartItem = Cart::where('fk_product', $product->id)
                        ->where('fk_user', Auth::id())
                        ->first();

        if ($cartItem) {
            $cartItem->quantity += $quantity;
            $cartItem->subtotal = $cartItem->quantity * $cartItem->product->price;
            $cartItem->save();
        } else {
            Cart::create([
                'fk_product' => $product->id,
                'fk_user' => Auth::id(),
                'quantity' => $quantity,
                'subtotal' => $quantity * $product->price
            ]);
        }

        return redirect()->route('usuarios.carrito')->with('success', 'Producto agregado al carrito correctamente.');
    }

    public function carrito()
    {
        try {
            $userid = Auth::id();

            $cartItems = Cart::with('product')->where('fk_user', $userid)->get();
            $total = $cartItems->reduce(fn ($carry, $item) => $carry + ($item->product->price * $item->quantity), 0);
            return view('UserCarritoGeneral', compact('cartItems', 'total', 'userid'));
        } catch (\Exception $e) {
            \Log::error('Error en carrito: ' . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error interno del servidor'], 500);
        }
    }
    public function reservar(Request $request)
    {
        DB::beginTransaction();

        try {
            // Crear la reserva con el campo 'fk_user' incluido
            $reservation = Reservation::create([
                'fk_user' => Auth::id(), // Asegúrate de que este campo coincide con tu esquema
                'total' => 0, // Se actualizará después

            ]);

            // Obtener los artículos del carrito
            $cartItems = Cart::where('fk_user', Auth::id())->get();

            $total = 0;

            foreach ($cartItems as $item) {
                // Crear elementos de reserva
                ReservationItem::create([
                    'fk_reservation' => $reservation->id,
                    'fk_product' => $item->fk_product,
                    'quantity' => $item->quantity,
                    'nombre'=>$item->product->nombre,
                    'subtotal' => $item->subtotal,
                    'fk_vendedors' => $item->product->vendedor->id,
                    'fk_mercados' => $item->product->vendedor->fk_mercado,
                    'precio' => $item->product->price// Ajusta según la lógica
                ]);

                // Calcular el total
                $total += $item->subtotal;
            }

            // Actualizar el total de la reserva
            $reservation->total = $total;
            $reservation->save();

            // Vaciar el carrito
            Cart::where('fk_user', Auth::id())->delete();

            DB::commit();

            return redirect()->route('usuarios.reservas')->with('success', 'Reserva creada con éxito.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al crear la reserva: ' . $e->getMessage());
        }
    }
    public function reservas()
    {
        // Obtener las reservas del usuario autenticado con los ítems y productos relacionados
        $reservations = Reservation::where('fk_user', Auth::id())->with('items.product')->get();

        return view('UserEstadoReservas', compact('reservations'));
    }




    /**
     * CAMBIAR ESTADO RESERVAS
     */
    public function publicarestadoreserva(Request $request, $id)
        {
            // Obtener el ReservationItem por ID
            $item = ReservationItem::find($id);

            // Verificar si el item fue encontrado
            if (!$item) {
                return redirect()->route('usuarios.reservas')->with('error', 'El ítem de la reserva no fue encontrado.');
            }

            // Verificar si el item pertenece al vendedor con id = 1
            if ($item->reservation->user->id == Auth::id()) {
                // Validar el estado enviado
                $estadoValido = ['enviado', 'sin_existencias', 'en_espera', 'sin_espera', 'en_entrega', 'recibido', 'sin_recibir', 'problemas', 'archivado'];
                $nuevoEstado = $request->input('estado');

                if (in_array($nuevoEstado, $estadoValido)) {
                    // Actualizar el estado del item
                    $item->estado = $nuevoEstado;
                    $item->save();


                    /**
                     * SIN EXISTENCIAS
                     */
                    // Verificar si todos los items relacionados tienen estado 'en_entrega'
                    $fk_reservation = $item->fk_reservation;
                    $todosEnEntrega = ReservationItem::where('fk_reservation', $fk_reservation)
                        ->where('estado', '!=', 'sin_existencias')
                        ->count() == 0;

                    if ($todosEnEntrega) {
                        // Actualizar el estado de la reserva a 'en_entrega'
                        $reserva = Reservation::find($fk_reservation);
                        $reserva->estado = 'sin_existencias';
                        $reserva->save();
                    }
                    /**
                     * EN ESPERA
                     */
                    // Verificar si todos los items relacionados tienen estado 'en_entrega'
                    $fk_reservation = $item->fk_reservation;
                    $todosEnEntrega = ReservationItem::where('fk_reservation', $fk_reservation)
                        ->where('estado', '!=', 'en_espera')
                        ->count() == 0;

                    if ($todosEnEntrega) {
                        // Actualizar el estado de la reserva a 'en_entrega'
                        $reserva = Reservation::find($fk_reservation);
                        $reserva->estado = 'en_espera';
                        $reserva->save();
                    }
                    /**
                     * SIN ESPERA
                     */
                    // Verificar si todos los items relacionados tienen estado 'en_entrega'
                    $fk_reservation = $item->fk_reservation;
                    $todosEnEntrega = ReservationItem::where('fk_reservation', $fk_reservation)
                        ->where('estado', '!=', 'sin_espera')
                        ->count() == 0;

                    if ($todosEnEntrega) {
                        // Actualizar el estado de la reserva a 'en_entrega'
                        $reserva = Reservation::find($fk_reservation);
                        $reserva->estado = 'sin_espera';
                        $reserva->save();
                    }





                    /**
                     * En ENTREGA
                     */
                    // Verificar si todos los items relacionados tienen estado 'en_entrega'
                    $fk_reservation = $item->fk_reservation;
                    $todosEnEntrega = ReservationItem::where('fk_reservation', $fk_reservation)
                        ->where('estado', '!=', 'en_entrega')
                        ->count() == 0;

                    if ($todosEnEntrega) {
                        // Actualizar el estado de la reserva a 'en_entrega'
                        $reserva = Reservation::find($fk_reservation);
                        $reserva->estado = 'en_entrega';
                        $reserva->save();
                    }
                    /**
                     * SIN RECIBIR
                     */
                    // Verificar si todos los items relacionados tienen estado 'en_entrega'
                    $fk_reservation = $item->fk_reservation;
                    $todosEnEntrega = ReservationItem::where('fk_reservation', $fk_reservation)
                        ->where('estado', '!=', 'sin_recibir')
                        ->count() == 0;

                    if ($todosEnEntrega) {
                        // Actualizar el estado de la reserva a 'en_entrega'
                        $reserva = Reservation::find($fk_reservation);
                        $reserva->estado = 'sin_recibir';
                        $reserva->save();
                    }
                    /**
                     * PROBLEMA
                     */
                    // Verificar si todos los items relacionados tienen estado 'en_entrega'
                    $fk_reservation = $item->fk_reservation;
                    $todosEnEntrega = ReservationItem::where('fk_reservation', $fk_reservation)
                        ->where('estado', '!=', 'problema')
                        ->count() == 0;

                    if ($todosEnEntrega) {
                        // Actualizar el estado de la reserva a 'en_entrega'
                        $reserva = Reservation::find($fk_reservation);
                        $reserva->estado = 'problema';
                        $reserva->save();
                    }
                    /**
                     * RECIBIDO
                     */
                    // Verificar si todos los items relacionados tienen estado 'en_entrega'
                    $fk_reservation = $item->fk_reservation;
                    $todosEnEntrega = ReservationItem::where('fk_reservation', $fk_reservation)
                        ->where('estado', '!=', 'recibido')
                        ->count() == 0;

                    if ($todosEnEntrega) {
                        // Actualizar el estado de la reserva a 'en_entrega'
                        $reserva = Reservation::find($fk_reservation);
                        $reserva->estado = 'recibido';
                        $reserva->save();
                    }


                    // Redireccionar a la vista o hacer otra acción
                    return redirect()->route('usuarios.reservas')->with('success', 'El estado de la reserva ha sido actualizado.');
                } else {
                    // Estado no válido
                    return redirect()->route('usuarios.reservas')->with('error', 'El estado proporcionado no es válido.');
                }
            } else {
                // Si no pertenece al vendedor correcto, mostrar un error
                return redirect()->route('usuarios.reservas')->with('error', 'No tienes permiso para actualizar este item.');
            }
        }


 }

