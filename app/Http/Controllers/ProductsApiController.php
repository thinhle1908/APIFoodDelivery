<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use  App\Http\Controllers\CategoryProductController;
use App\Models\Category;
use App\Models\Category_Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;

use function Ramsey\Uuid\v1;

class ProductsApiController extends Controller
{
    /**
     * Get List Product
     * @OA\Get (
     *     path="/api/Products",
     *     tags={"ToDo"},
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 type="array",
     *                 property="rows",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="_id",
     *                         type="number",
     *                         example="1"
     *                     ),
     *                     @OA\Property(
     *                         property="title",
     *                         type="string",
     *                         example="example title"
     *                     ),
     *                     @OA\Property(
     *                         property="content",
     *                         type="string",
     *                         example="example content"
     *                     ),
     *                     @OA\Property(
     *                         property="updated_at",
     *                         type="string",
     *                         example="2021-12-11T09:25:53.000000Z"
     *                     ),
     *                     @OA\Property(
     *                         property="created_at",
     *                         type="string",
     *                         example="2021-12-11T09:25:53.000000Z"
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $Products =  Product::where('visible', 1)->get();
        $data_category = [];
        foreach ($Products as $Product) {
            $data_category = $Product->categories;
            $img = $Product->image;
            $image = file_get_contents(storage_path("app\apiDocs\\" . $img));
            $Product->image =  base64_encode($image);
            $Product->categories;
        }
        return response()->json([
            'Product' => $Products,

        ]);
    }
    /**
     * Create Product
     * @OA\Product (
     *     path="/api/Product",
     *     tags={"ToDo"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="object",
     *                      @OA\Property(
     *                          property="title",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="content",
     *                          type="string"
     *                      )
     *                 ),
     *                 example={
     *                     "title":"example title",
     *                     "content":"example content"
     *                }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="success",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="number", example=1),
     *              @OA\Property(property="title", type="string", example="title"),
     *              @OA\Property(property="content", type="string", example="content"),
     *              @OA\Property(property="updated_at", type="string", example="2021-12-11T09:25:53.000000Z"),
     *              @OA\Property(property="created_at", type="string", example="2021-12-11T09:25:53.000000Z"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="invalid",
     *          @OA\JsonContent(
     *              @OA\Property(property="msg", type="string", example="fail"),
     *          )
     *      )
     * )
     */
    public function store(Request $request)
    {

        if (Gate::allows('admin-only', auth()->user())) {
            request()->validate([
                'product_name' => 'bail|required|string|max:255',
                'price' => 'bail|required|numeric',
                'image' => 'image|required',
                'qty' => 'required|numeric',
                'description' => 'bail|required|string',
                'arr_category' => 'required|string'
            ]);
            //Move Image to forder and get name image
            //cach1
            // $nameimg = $request->file('image')->getClientOriginalName();
            // $request->file('image')->move('../storage/app/apiDocs', $nameimg);
            //cach 2

            $request->file('image')->store('apiDocs');
            $file = $request->file('image');
            $nameimg = $file->hashName();

            $arr_category = $request->input('arr_category');
            $arr_category = explode(',', $arr_category);
            $last_Product = Product::orderBy('id', 'desc')->take(1)->get();
            $id_last_product = $last_Product[0]->id;
            $categoryproductcontroller = new CategoryProductController();
            foreach ($arr_category as $key => $value) {
                $categoryproductcontroller->store($request, $id_last_product + 1, $value);
            }
            //save in db
            $product = new Product();
            $product->product_name = $request->product_name;
            $product->price = $request->price;
            $product->image = $nameimg;
            $product->description = $request->description;
            $product->qty = $request->qty;
            $product->save();
            return response()->json([
                "product_name" =>   $product->product_name,
                "price" =>   $product->price,
                "image" =>   $product->image,
                "description" =>   $product->description,
                "qty" =>   $product->qty,
            ]);
        } else {
            abort(403);
        }
    }
    /**
     * Update Product
     * @OA\Put (
     *     path="/api/Products/{Product}",
     *     tags={"ToDo"},
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="object",
     *                      @OA\Property(
     *                          property="title",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="content",
     *                          type="string"
     *                      )
     *                 ),
     *                 example={
     *                     "title":"example title",
     *                     "content":"example content"
     *                }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="success",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="number", example=1),
     *              @OA\Property(property="title", type="string", example="title"),
     *              @OA\Property(property="content", type="string", example="content"),
     *              @OA\Property(property="updated_at", type="string", example="2021-12-11T09:25:53.000000Z"),
     *              @OA\Property(property="created_at", type="string", example="2021-12-11T09:25:53.000000Z")
     *          )
     *      )
     * )
     */
    public function update(Product $product, Request $request)
    {
        if (Gate::allows('admin-warehouse_staff', auth()->user())) {
            request()->validate([
                'product_name' => 'bail|required|string|max:255',
                'price' => 'bail|required|numeric',
                'image' => 'image|required',
                'qty' => 'required|numeric',
                'description' => 'bail|required|string',
                'arr_category' => 'required|string'
            ]);
            //Delete Image
            $oldProductID = $product->id;
            $oldProduct = Product::find($oldProductID);
            $image_path = "../storage/app/apiDocs//" . $oldProduct->image;  // Value is not URL but directory file path
            if (File::exists($image_path)) {
                File::delete($image_path);
            }
            // Move Image to forder and get name image

            //cach 1
            //$nameimg = $request->file('image')->getClientOriginalName();
            //$request->file('image')->move('../storage/app/apiDocs', $nameimg);
            //cach 2
            $request->file('image')->store('apiDocs');
            $file = $request->file('image');
            $nameimg = $file->hashName();


            $arr_category = $request->input('arr_category');
            $arr_category = explode(',', $arr_category);
            $id_product = $product->id;
            $categoryproductcontroller = new CategoryProductController();
            $categoryproductcontroller->destroyByID($id_product, $request);
            foreach ($arr_category as $key => $value) {
                $categoryproductcontroller->store($request, $id_product, $value);
            }

            $success = $product->update([
                'product_name' => request('product_name'),
                'price' => request('price'),
                'image' => $nameimg,
                'qty' => request('qty'),
                'description' => request('description'),
            ]);
            return response()->json([
                'success' => $success
            ]);
        } else {
            abort(403);
        }
    }
    /**
     * Delete Todo
     * @OA\Delete (
     *     path="/api/Products/{Product}",
     *     tags={"ToDo"},
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="delete todo success")
     *         )
     *     )
     * )
     */
    public function destroy(Product $product)
    {
        if (Gate::allows('admin-warehouse_staff', auth()->user())) {
            $success = $product->update([
                'visible' => 0
            ]);
            return response()->json([
                'success' => $success
            ]);
        } else {
            abort(403);
        }
    }
    public function search(Request $request)
    {
        $keyword = $request->input('keyword');
        $Products = Product::where('product_name', 'like', "%$keyword%")->orWhere('description', 'like', "%$keyword%")->where('visible', 1)->get();
        $data_category = [];
        foreach ($Products as $Product) {
            $data_category = $Product->categories;
            $img = $Product->image;
            $image = file_get_contents(storage_path("app\apiDocs\\" . $img));
            $Product->image =  base64_encode($image);
        }
        return response()->json([
            'Product' => $Products
        ]);
    }
    public function getProductByID(Product $product)
    {


        // foreach ($product->categories as $category) {
        //     var_dump($category->category_name);
        // }
        if ($product->visible == 1) {
            $img = $product->image;
            $image = file_get_contents(storage_path("app\apiDocs\\" . $img));
            //Call categories
            $product->categories;
            $img = $product->image;
            $image = file_get_contents(storage_path("app\apiDocs\\" . $img));
            $product->image =  base64_encode($image);
            return response()->json([
                'product' => $product

            ]);
        } else {
            abort(403);
        }
    }
    public function getImage(Product $product)
    {

        if (Gate::allows('admin-warehouse_staff', auth()->user())) {
            // $url = 'http://127.0.0.1:8000/api/products/image/4'; 
            // $image = file_get_contents(public_path('images/' . $product->image));
            $img = $product->image;
            $image = file_get_contents(public_path("images\\" . $img));
            return [
                'image' => base64_encode($image)
            ];
            //test
            // $img = $product->image; 
            // $image= public_path("images\\".$img);
            // $image.str_replace($image,"/",'\\');
            return response()->json([
                'image' => $image
            ]);
        } else {
            abort(403);
        }
    }
}
