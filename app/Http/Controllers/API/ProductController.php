<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Product;
use App\ProductCategory;
use Validator;
use App\Http\Resources\Product as ProductResource;
use App\Http\Resources\ProductCategory as CategoryResource;

class ProductController extends Controller
{/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        $products = Product::all();
        $category = ProductCategory::all();
        $success['products'] = ProductResource::collection($products);
        $success['categories'] = $category;
        return response()->json(['success' => $success]);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
   
        $validator = Validator::make($input, [
            'name' => 'required|unique:products', 
            'description' => 'required|string|max:255', 
            'category' => 'required|integer', 
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'required|boolean', 
        ]);
   
        if($validator->fails()){
            $error['msg'] = $validator->errors();
            return response()->json(['error' => $error]);  
        }
   
        $file_name = $request->image->getClientOriginalName();
        $image_path = $request->image->storeAs('images', $file_name, 'public');

        $product = new Product;       
        $product->name = $request->name;
        $product->desc = $request->description;
        $product->cat_id = $request->category;
        $product->image = $image_path;
        $product->is_active = $request->active;
        $product->shopuser_id = auth()->user()->id;
        $result = $product->save();   

        // $product = Product::create($input);
        if($result){
            $success['msg'] = 'Product saved successfully.';
            $success['products'] = new ProductResource($product);
            return response()->json(['success' => $success]);
        }else{
            $error['msg'] = 'Something went wrong on saving product.';
            return response()->json(['error' => $error]);
        }
    } 
   
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $product = Product::find($id);
  
        if (is_null($product)) {
            $error['msg'] = 'Product not found.';
            return response()->json(['error' => $error]);
        }
   
        $success['msg'] = 'Product retrieved successfully.';
        $success['products'] = new ProductResource($product);
        return response()->json(['success' => $success]);
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $input = $request->all();
   
        $validator = Validator::make($input, [
            'name' => 'required',
            'detail' => 'required'
        ]);
   
        if($validator->fails()){
            $error['msg'] = $validator->errors();
            return response()->json(['error' => $error]);      
        }
   
        $product->name = $input['name'];
        $product->detail = $input['detail'];
        $product->save();

        $success['msg'] = 'Product updated successfully.';
        $success['products'] = new ProductResource($product);
        return response()->json(['success' => $success]);
   
    }
   
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product->delete();
   
        $success['msg'] = 'Product deleted successfully.';
        return response()->json(['success' => $success]);
    }
}
