<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    //
    function addProduct(Request $req) {

        $rules = [
            "name" => "required|min:3",
            "price" => "required|numeric",
            "file" => "required|image|mimes:jpeg,png,jpg|max:2048",
            "description" => "required"
        ];

        $validator = Validator::make($req->all(), $rules);
        
        if($validator->fails()) {
            return response()->json([
                "status" => "error",
                "errors" => $validator->errors()
            ],422);
        }

        $product = new Product;
        $product->file_path = $req->file("file")->store("products","public");
        $product->name = $req->input("name");
        $product->price = $req->input("price");
        $product->description = $req->input("description");
        $product->save();
        return response()->json(["status" => "success","data" => $product],201);
    }

    function list() {
        //return Product::all(); for all
        return Product::paginate(5);
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
        $product = Product::find($id);
        if(!$product) {
            return response()->json(["message" => "Product not found"],404);
        }
        return $product;
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


    function search($key) {
        return Product::where("name","Like","%$key%")->get();
    }



}
