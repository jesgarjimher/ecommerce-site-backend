<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Storage;

class ProductController extends Controller
{
    //
    function addProduct(Request $req) {
        $product = new Product;
        $product->file_path = $req->file("file")->store("products","public");
        $product->name = $req->input("name");
        $product->price = $req->input("price");
        $product->description = $req->input("description");
        $product->save();
        return $product;
    }

    function list() {
        return Product::all();
    }


    function delete($id) {
        $result = Product::where("id",$id)->delete();
        if($result) {
            return ["result" => "Product has been deleted"];
        }else {
            return ["result" => "Operation failed"];
        }
    }

    function getProduct($id) {
        return Product::find($id);
    }

    function editProduct(Request $req, $id) {
        $product = Product::find($id);

        if(!$product) {
            return response()->json(["result" => "Product not found"], 404);
        }

        $product->name = $req->input("name");
        $product->price = $req->input("price");
        $product->description = $req->input("description");

        if($req->hasFile("file")) {
            if($product->file_path) { //if a photo already exists
                Storage::disk("public")->delete($product->file_path);
            }
            
            $product->file_path = $req->file("file")->store("products","public");

        }

        $product->save();

        return $product;
    }



}
