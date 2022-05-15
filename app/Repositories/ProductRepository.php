<?php
namespace App\Repositories;
use App\Models\Product;

class ProductRepository{

    public function create($uuid, $sku, $nombre_producto, $precio, $estado, $user_id){
        $product['uuid'] = $uuid;
        $product['sku'] = $sku;
        $product['nombre_producto'] = $nombre_producto;
        $product['precio'] = $precio;
        $product['estado'] = $estado;
        $product['user_id'] = $user_id;
        return Product::create($product);
    }

    public function update($uuid, $sku, $nombre_producto, $precio, $estado){
        $product = $this->find($uuid);
        $product->sku = $sku;
        $product->nombre_producto = $nombre_producto;
        $product->precio = $precio;
        $product->estado = $estado;
        $product->save();
        return $product;
    }

    public function find($uuid){
        return Product::Where('uuid', '=', $uuid)->first();
    }

    public function delete($uuid){
        $product = $this->find($uuid);
        return $product->delete();
    }
}