<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Constants\BranchType;
use App\Constants\Message;
use App\Constants\PurchaseStatus;
use App\Constants\StockTransaction;
use App\Constants\StockType;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\StockHistory;
use App\Http\Requests\PurchaseRequest;
use App\Traits\FileHandling;
use Auth;

class PurchaseController extends Controller
{
    use FileHandling;

    /** @var string Folder name to store purchase document */
    private $documentFolder = 'purchase';

    /**
     * Display a listing of product purchases.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        if(!Auth::user()->can('po.browse')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        $purchases = Purchase::query();

        $itemCount = $purchases->count();
        $purchases = $purchases->sortable()->latest()->paginate(paginationCount());
        $offset = offset($request->page);

        return view('purchase.index', compact('itemCount', 'offset', 'purchases'));
    }

    /**
     * Show form to create product purchase.
     *
     * @param Purchase $purchase
     *
     * @return Response
     */
    public function create(Purchase $purchase)
    {
        if(!Auth::user()->can('po.add')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        $title = trans('app.create');
        $warehouses = Branch::getAll();
        $products = Product::getAll();
        $purchaseStatuses = purchaseStatuses();
        $purchasedProducts = old('products') ?? []; // When form validation has error

        return view('purchase.form', compact(
            'products',
            'purchase',
            'purchasedProducts',
            'purchaseStatuses',
            'title',
            'warehouses'
        ));
    }

    /**
     * Show form to edit an existing purchase.
     *
     * @param Purchase $purchase
     *
     * @return Response
     */
    public function edit(Purchase $purchase)
    {
        if(!Auth::user()->can('po.edit')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        abort(404);
    }

    /**
     * Show purchase detail.
     *
     * @param Purchase $purchase
     *
     * @return Response
     */
    public function show(Purchase $purchase)
    {
        if(!Auth::user()->can('po.browse')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        return view('purchase.show', compact('purchase'));
    }

    /**
     * Save new purchase.
     *
     * @param PurchaseRequest $request
     *
     * @return Response
     */
    public function save(PurchaseRequest $request)
    {
        if(!Auth::user()->can('po.add') && !Auth::user()->can('po.edit')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        $productIds = array_column($request->products, 'id');
        $purchasedProductCount = Product::whereIn('id', $productIds)->count();

        // Check if IDs of purchased product (s) are invalid
        if ($purchasedProductCount != count($productIds)) {
            return back()->withInput()->withErrors([
                Message::ERROR_KEY => trans('message.invalid_product_data'),
            ]);
        }

        $purchase = new Purchase();
        $purchase->creator_id = auth()->user()->id;
        $purchase->warehouse_id = $request->warehouse;
        $purchase->purchase_date = dateIsoFormat($request->purchase_date);
        $purchase->reference_no = $request->invoice_id;
        $purchase->total_quantity = array_sum(array_column($request->products, 'quantity'));
        $purchase->total_cost = decimalNumber($request->total_cost);
        $purchase->total_discount = decimalNumber($request->discount);
        $purchase->shipping_cost = decimalNumber($request->shipping_cost);
        $purchase->note = $request->note;
        $purchase->purchase_status= $request->status;

        if (isset($request->document)) {
            $purchase->document = $this->uploadFile($this->documentFolder, $request->document);
        }

        if ($purchase->save()) {
            foreach ($request->products as $product) {
                // Save purchase detail
                $purchaseDetail = new PurchaseDetail();
                $purchaseDetail->purchase_id = $purchase->id;
                $purchaseDetail->product_id = $product['id'];
                $purchaseDetail->quantity = $product['quantity'];
                $purchaseDetail->save();

                if ($request->status == PurchaseStatus::RECEIVED) {
                    // Update or insert product quantity to warehouse
                    $productWarehouse = (ProductWarehouse::selectQuery($request->warehouse, $product['id'])->first()
                                        ?? new ProductWarehouse());
                    $productWarehouse->product_id = $product['id'];
                    $productWarehouse->warehouse_id = $request->warehouse;
                    $productWarehouse->quantity += $product['quantity'];
                    $productWarehouse->save();

                    // Save stock history
                    $stockHistory = new StockHistory();
                    $stockHistory->transaction = StockTransaction::PURCHASE;
                    $stockHistory->transaction_id = $purchase->id;
                    $stockHistory->transaction_date = dateIsoFormat($request->purchase_date);
                    $stockHistory->warehouse_id = $request->warehouse;
                    $stockHistory->product_id = $product['id'];
                    $stockHistory->stock_type = StockType::STOCK_IN;
                    $stockHistory->quantity = $product['quantity'];
                    $stockHistory->save();
                }
            }
        }

        session()->flash(Message::SUCCESS_KEY, trans('message.item_saved_success'));
        return redirect(route('purchase.index'));
    }
}
