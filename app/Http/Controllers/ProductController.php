<?php

namespace App\Http\Controllers;

use Exception;
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
        try{
            $products = Product::paginate(5);
            return response()->json($products,200);
        }catch(Exception $error){
            return response()->json(["error" => "Internal server error","message" => "There was a problem connecting to the database"],500);
        }
        
    }


    function delete($id) {
        try {
            $product = Product::find($id);
            if(!$product) {
                return response()->json(["result" => "Error", "message" => "The product doesn't exist or has been deleted already"],404);
            }

        
            if($product->file_path) {
                Storage::disk("public")->delete($product->file_path);
            }
            $product->delete();
            return response()->json(["result" => "Success", "message" => "Product deleted"],200);
        }catch(Exception $error) {
            return response()->json(["result" => "Error", "message" => "Couldn't delete product"],500);
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
        try {
            $products = Product::where("name","Like","%$key%")->get();
            return response()->json($products,200);
        }catch(Exception $error) {
            return response()->json(["result" => "error", "message" => "Database failed, please try again"], 500);
        }
        
       
    }



}
