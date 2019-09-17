<?php

namespace App\Http\Controllers;

use App\Constants\UserRole;
use Illuminate\Http\Request;
use App\Constants\FormType;
use App\Constants\Message;
use App\Models\ExtendedProperty;
use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Models\Branch;
use App\Http\Requests\ProductRequest;
use App\Traits\FileHandling;
use Illuminate\Support\Facades\DB;
use Auth;
class ProductController extends Controller
{
    use FileHandling;

    /** @var string  Folder name to store image */
    private $imageFolder = 'product';

    public function __construct()
    {
        //$this->middleware('role:'. UserRole::ADMIN);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * 
     * @return Response
     */
    public function index(Request $request)
    {
        if(!Auth::user()->can('product.browse')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        $products = Product::query();
        if(!empty($request->location)){
            $location= $request->location;
            $product_list= ProductWarehouse::select('product_id')->where('warehouse_id', $location)->get();
            $products = Product::whereIn('id',$product_list);
        }

        if (!empty($request->brand)) {
            $products = $products->where('brand', $request->brand);
        }

        if (!empty($request->search)) {
            $products = $products->where(function ($query) use ($request) {
                $searchText = $request->search;
                $query->where('name', 'like', '%' . $searchText . '%')
                    ->orWhere('serial_number', 'like', '%' . $searchText . '%')
                    ->orWhere('price', $searchText);
            });
        }

        if(!empty($request->prod_type)){
            $products = $products->where('category_id', $request->prod_type);
        }
        if(!empty($request->brand)){
            $products = $products->where('brand', $request->brand);
        }

        $itemCount = $products->count();
        $products = $products->sortable()->orderBy('name')->paginate(paginationCount());
        // $products = $products->orderBy('name')->paginate(paginationCount());
        $offset = offset($request->page);
        $brands = brands();
        $locations= Branch::getall();
        $productCategories = ExtendedProperty::allProductCategories();
        
        return view('product/index', compact('brands','locations', 'itemCount', 'offset', 'products', 'productCategories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Product $product
     *
     * @return Response
     */
    public function create(Product $product)
    {
        if(!Auth::user()->can('product.add')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        $title = trans('app.create');
        $formType = FormType::CREATE_TYPE;
        $productCategories = ExtendedProperty::allProductCategories();
        $brands = ExtendedProperty::allBrands();
        
        return view('product/form', compact(
            'brands', 'formType', 'product', 'productCategories', 'title'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Product $product
     *
     * @return Response
     */
    public function edit(Product $product)
    {
        if(!Auth::user()->can('product.edit')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        $title = trans('app.edit');
        $formType = FormType::EDIT_TYPE;
        $productCategories = ExtendedProperty::allProductCategories();
        $brands = ExtendedProperty::allBrands();

        return view('product/form', compact(
            'brands', 'formType', 'product', 'productCategories', 'title'
        ));
    }

    /**
     * Display the specified resource.
     *
     * @param Product $product
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        if(!Auth::user()->can('product.browse')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        $title = trans('app.detail');
        $formType = FormType::SHOW_TYPE;

        return view('product/form', compact('formType', 'product', 'title'));
    }
    
    /**
     * Save new or existing product.
     *
     * @param ProductRequest $request
     * @param Product $product
     *
     * @return Response
     */
    public function save(ProductRequest $request, Product $product)
    {
        if(!Auth::user()->can('product.add') && !Auth::user()->can('product.edit')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        $product->name = $request->name;
        $product->category_id = $request->category;
        $product->brand = $request->brand;
        $product->code = $request->product_code;
        $product->sku = $request->product_sku;
        $product->cost = $request->cost;
        $product->price = $request->price;
        $product->alert_quantity = $request->alert_quantity;
        $product->description = $request->description;
        
        if (!empty($request->photo)) {
            $product->photo = $this->uploadImage($this->imageFolder, $request->photo);
        }

        $product->save();
        session()->flash(Message::SUCCESS_KEY, trans('message.item_saved_success'));
        return redirect()->route('product.index');
    }

    /**
     * Show a listing of warehouses of a product.
     *
     * @param Product $product
     *
     * @return Response
     */
    public function warehouseList(Product $product)
    {
        if(!Auth::user()->can('product.browse')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        $stocks = ProductWarehouse::where('product_id', $product->id)->get();
        $itemCount = $stocks->count();
        return view('product.warehouse', compact('itemCount', 'product', 'stocks'));
    }

    /**
     * Show a listing of warehouses of a product.
     *
     * @param Product $product
     *
     * @return Response
     */
    public function stockLevel(Request $request)
    {
        if(!Auth::user()->can('product.stock')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        $productCategories = ExtendedProperty::allProductCategories();
        $brands = ExtendedProperty::allBrands();
        
        $stocks = ProductWarehouse::where('product_id', $product->id)->get();
        $itemCount = $stocks->count();
        return view('product.warehouse', compact('itemCount', 'product', 'stocks'));
    }

    /**
     * Delete product.
     *
     * @param Product $product
     *
     * @return Response
     */
    public function destroy(Product $product)
    {
        if(!Auth::user()->can('product.delete')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        abort(404);
    }
}
