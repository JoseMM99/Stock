<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Models\User;
use App\Models\Product;
use Uuid;

class ProductController extends Controller
{
    protected $product_repository;
    protected $user_repository;

    public function __construct(ProductController $product, UserRepository $user){
        $this->product_repository = $product;
        $this->user_repository = $user;
    }

    public function register(Request $request){
        $validator = Validator::make($request->all(),[
            'sku' => 'required|string',
            'nombre_producto' => 'required|string|max:60',
            'cantidad' => 'required|integer|min:1',
            'precio' => 'required|integer',
            'estado' => 'required|boolean',
        ]);


        if($validator->fails()){
            //Log::warning('ProductController - register - Falta un campo por llenar');
            return response()->json($validator->errors()->toJson(), 400);
        }
        try{
            $product = $this->product_repository->create(
                Uuid::generate()->string,
                $request->get('sku'),
                $request->get('nombre_producto'),
                $request->get('cantidad'),
                $request->get('precio'),
                $request->get('estado'),
                $data = User::with('user')->findOrFail(Auth::id()) 
            );

            //var_dump($product);
            //die();
            
            //Log::info('ProductController - register - Se creó un nuevo producto');
            return response()->json(compact('product'),201);

        }catch(\Exception $ex){
            //Log::emergency('ProductController - register - Ocurrio un error');
            return response()->json(['error'=>$ex->getMessage()]);
        }
    }

}
