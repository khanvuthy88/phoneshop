<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Constants\FormType;
use App\Constants\Message;
use App\Constants\SaleStatus;
use App\Constants\SaleType;
use App\Constants\StockTransaction;
use App\Constants\StockType;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\StockHistory;
use App\Models\Staff;
use App\Traits\FileHandling;
use App\Http\Requests\SaleRequest;

use DB;
use Auth;

class SaleController extends Controller
{
    use FileHandling;

    /** @var string Folder name to store sale document */
    private $documentFolder = 'sale';

    /**
     * Display a listing of product sales.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        if(!Auth::user()->can('sale.browse')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        $sales = Sale::saleType();

        $itemCount = $sales->count();
        $sales = $sales->sortable()->latest()->paginate(paginationCount());
        $offset = offset($request->page);

        return view('sale.index', compact('itemCount', 'offset', 'sales'));
    }

    /**
     * Show form to create sale.
     *
     * @param Sale $sale
     *
     * @return Response
     */
    public function create(Sale $sale)
    {
        if(!Auth::user()->can('sale.add')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        $title = trans('app.create');
        $formType = FormType::CREATE_TYPE;
        $branches = Branch::getAll();
        $clients = Client::orderBy('is_default', 'desc')->orderBy('name')->get();
        $products = Product::getAll();
        $agents = [];
        if (isAdmin() && old('branch') !== null) { // When form validation has error
            $agents = Staff::where('branch_id', old('branch'))->orderBy('name')->get();
        }

        return view('sale.form', compact(
            'agents',
            'branches',
            'clients',
            'formType',
            'products',
            'sale',
            'title'
        ));
    }

    /**
     * Save new purchase.
     *
     * @param PurchaseRequest $request
     *
     * @return Response
     */
    public function save(SaleRequest $request)
    {
        if(!Auth::user()->can('sale.add') && !Auth::user()->can('sale.edit')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        $productIds = array_column($request->products, 'id');
        $productCount = Product::whereIn('id', $productIds)->count();

        // Check if IDs of purchased product (s) are invalid
        if ($productCount != count($productIds)) {
            return back()->withInput()->withErrors([
                Message::ERROR_KEY => trans('message.invalid_product_data'),
            ]);
        }

        if (isAdmin()) {
            $validationRules = [
                'branch' => 'required|integer',
                'agent' => 'required|integer',
            ];
            $this->validate($request, $validationRules);
        }

        try {
            //inline new client added
            if(isset($request->phone_number) && !empty($request->phone_number)){
                $new_client = new Client();
                $new_client->name = empty($request->client_name)? '(Walkin)':$request->client_name;
                $new_client->first_phone = $request->phone_number;
                $new_client->save();
            }
            $new_sale = true;
            if(!empty($request->sale_id)){
                $sale = $sale_before = Sale::findOrFail($request->sale_id);
                $new_sale = false;
            }else{
                $sale = new Sale();
                $sale->creator_id = auth()->user()->id;
            }
            if (isAdmin()) {
                $sale->warehouse_id = $request->branch;
                $sale->staff_id = $request->agent;
            }else{
                $staff = auth()->user()->staff;
                if($staff){
                    $sale->warehouse_id = $staff->branch_id;
                    $sale->staff_id = $staff->id;
                }
            }
            $sale->client_id = isset($new_client)? $new_client->id:$request->client;
            $sale->sale_date = dateIsoFormat($request->sale_date);
            $sale->total_quantity = array_sum(array_column($request->products, 'quantity'));
            $sale->total_price = array_sum(array_map(function($item) { 
                                    return $item['quantity'] * $item['price']; 
                                }, $request->products));
            $sale->note = $request->note;
            
            $sale->type = SaleType::SALE;
            $sale->status = $request->status;
            //TO DO with these colunms
            //-------------
            $sale->staff_note = '';
            $sale->paid_amount = $request->paid_amount; //decimalNumber(...);
            //$sale->total_commission = decimalNumber(...);
            $sale->sale_code = '';
            $sale->reference_no = '';
            $sale->discount_type = '';
            $sale->total_discount = 0; //decimalNumber(...);
            $sale->shipping_amount = 0; //decimalNumber(...);
            $sale->shipping_note = '';
            $sale->total_tax = 0;
            //--------------

            $sale->grand_total = $sale->total_price - $sale->total_discount + $sale->total_tax;

            DB::beginTransaction();

            if ($sale->save()) {
                //Add sale items again
                $edit_add_line = [0];
                foreach ($request->products as $product) {
                    if(isset($product['item_id']) && !empty($product['item_id']))
                        $orderDetail = SaleDetail::findOrFail($product['item_id']);
                    else
                        $orderDetail = new SaleDetail();

                    // Save ordered product detail
                    $orderDetail->sale_id = $sale->id;
                    $orderDetail->product_id = $product['id'];
                    $orderDetail->quantity = $product['quantity'];
                    $orderDetail->unit_price = $product['price'];
                    $orderDetail->returned_quantity = 0;
                    $orderDetail->grand_total = $product['price'] * $product['quantity'];
                    $orderDetail->save();
                    $edit_add_line[] = $orderDetail->id;

                    if ($request->status == SaleStatus::DONE) {
                        // Update product stock in selected warehouse
                        $productWarehouse = (ProductWarehouse::selectQuery($request->branch, $product['id'])->first()
                                            ?? new ProductWarehouse());
                        $productWarehouse->product_id = $product['id'];
                        $productWarehouse->warehouse_id = $request->branch;
                        $productWarehouse->quantity -= $product['quantity'];
                        $productWarehouse->save();

                        // Save stock history
                        $stockHistory = new StockHistory();
                        $stockHistory->transaction = StockTransaction::SALE;
                        $stockHistory->transaction_id = $sale->id;
                        $stockHistory->transaction_date = dateIsoFormat($request->sale_date);
                        $stockHistory->warehouse_id = $request->branch;
                        $stockHistory->product_id = $product['id'];
                        $stockHistory->stock_type = StockType::STOCK_OUT;
                        $stockHistory->quantity = $product['quantity'];
                        $stockHistory->save();
                    }
                }

                //remove all previous sale items
                if(!$new_sale){
                    $item_removed = SaleDetail::where('sale_id', $sale->id)->whereNotIn('id', $edit_add_line)->get();
                    if($sale_before->status == SaleStatus::DONE){
                        //increase product stock when Final sale is updated
                        foreach ($item_removed as $_item) {
                            ProductWarehouse::where('product_id', $_item->product_id)->where('warehouse_id', $sale_before->warehouse_id)->increment('quantity', $_item->quantity);
                        }
                        //remove it now
                        SaleDetail::where('sale_id', $sale->id)->whereNotIn('id', $edit_add_line)->delete();
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            // $msg = trans("messages.something_went_wrong");
                
            // if (get_class($e) == \App\Exceptions\PurchaseSellMismatch::class) {
            //     $msg = $e->getMessage();
            // }

            session()->flash(Message::SAVE_FAILURE_VALUE, trans('message.item_saved_fail'));
            return redirect()->back()->withInput()->withErrors([
                Message::ERROR_KEY => trans('message.item_saved_fail'),
            ]);
        }

        session()->flash(Message::SUCCESS_KEY, trans('message.item_saved_success'));
        return redirect(route('sale.index'));
    }

    /**
     * Show form to create sale.
     *
     * @param Sale $sale
     *
     * @return Response
     */
    public function edit($id)
    {
        if(!Auth::user()->can('sale.edit')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }
        $sale = Sale::findOrFail($id);
        $details = $sale->details;

        $title = trans('app.edit');
        $formType = FormType::EDIT_TYPE;
        $clients = Client::orderBy('is_default', 'desc')->orderBy('name')->get();
        $products = Product::getAll();
        $agents = [];
        if (isAdmin()){
            $branches = Branch::getAll();
            $agents = Staff::where('branch_id', old('branch', $sale->warehouse_id))->orderBy('name')->get();
        }

        return view('sale.edit', compact(
            'agents',
            'branches',
            'clients',
            'formType',
            'products',
            'sale',
            'title',
            'details'
        ));
    }
}
