<?php

namespace _200Code_Ventas\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use _200Code_Ventas\Http\Requests\ArticuloFormRequest;
use _200Code_Ventas\Articulo;
use DB;

class ArticuloController extends Controller
{
    public function __construct()
    {

    }

    public function index(Request $request)
    {
        if ($request) {
            $query = trim($request->get('searchText'));
            $articulos = DB::table('articulo as a')
                ->join('categoria as c', 'a.id_categoria', '=', 'c.id_categoria')
                ->select('a.id_articulo', 'a.nombre', 'a.codigo', 'a.stock', 'c.nombre as categoria', 'a.descripcion', 'a.imagen', 'a.estado')
                ->where('a.nombre', 'LIKE', '%' . $query . '%')
                ->orwhere('a.codigo', 'LIKE', '%' . $query . '%')
                ->orderBy('a.id_categoria', 'desc')
                ->paginate(7);
            return view('almacen.articulo.index', [
                "articulos" => $articulos,
                "searchText" => $query
            ]);
        }
    }

    public function create()
    {
        $categorias = DB::table('categoria')->where('condicion', '=', '1')->get();
        return view('almacen.articulo.create', [
            'categorias' => $categorias
        ]);
    }

    public function store(ArticuloFormRequest $request)
    {
        $articulo = new Articulo();
        $articulo->id_categoria = $request->get('id_categoria');
        $articulo->codigo = $request->get('codigo');
        $articulo->nombre = $request->get('nombre');
        $articulo->stock = $request->get('stock');
        $articulo->descripcion = $request->get('descripcion');
        $articulo->estado = 'Activo';

        if (Input::hasFile('imagen')) {
            $file = Input::file('imagen');
            $file->move(public_path() . '/imagenes/articulos/', $file->getClientOriginalName());
            $articulo->imagen = $file->getClientOriginalName();
        }
        $articulo->save();

        return Redirect::to('almacen/articulo');
    }

    public function show($id)
    {
        return view("almacen.articulo.show", [
            "articulo" => Articulo::findOrFail($id)
        ]);
    }

    public function edit($id)
    {
        $articulo = Articulo::findOrFail($id);
        $categorias = DB::table('categoria')->where('condicion', '=', '1')->get();

        return view("almacen.articulo.edit", [
            "articulo" => $articulo,
            "categorias" => $categorias
        ]);
    }

    public function update(ArticuloFormRequest $request, $id)
    {
        $articulo = Articulo::findOrFail($id);

        $articulo->id_categoria = $request->get('id_categoria');
        $articulo->codigo = $request->get('codigo');
        $articulo->nombre = $request->get('nombre');
        $articulo->stock = $request->get('stock');
        $articulo->descripcion = $request->get('descripcion');

        if (Input::hasFile('imagen')) {
            $file = Input::file('imagen');
            $file->move(public_path() . '/imagenes/articulos/', $file->getClientOriginalName());
            $articulo->imagen = $file->getClientOriginalName();
        }
        $articulo->update();
        return Redirect::to('almacen/articulo');
    }

    public function destroy($id)
    {
        $articulo = Articulo::findOrFail($id);
        $articulo->estado = 'Inactivo';
        $articulo->update();

        return Redirect::to('almacen/articulo');
    }
}
