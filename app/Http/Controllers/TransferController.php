<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Constants\BranchType;
use App\Constants\Message;
use App\Constants\StockTransaction;
use App\Constants\StockTransferStatus;
use App\Constants\StockType;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Models\Transfer;
use App\Models\TransferDetail;
use App\Models\StockHistory;
use App\Http\Requests\TransferRequest;
use App\Traits\FileHandling;
use Auth;

class TransferController extends Controller
{
    use FileHandling;

    /** @var string Folder name to store transfer document */
    private $documentFolder = 'transfer';

    /**
     * Display a listing of stock transfers.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        if(!Auth::user()->can('stock.transfer.browse')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        $transfers = Transfer::query();

        $itemCount = $transfers->count();
        $transfers = $transfers->sortable()->latest()->paginate(paginationCount());
        $offset = offset($request->page);

        return view('transfer.index', compact('itemCount', 'offset', 'transfers'));
    }

    /**
     * Show form to create stock transfer.
     *
     * @param Transfer $transfer
     *
     * @return Response
     */
    public function create(Transfer $transfer)
    {
        if(!Auth::user()->can('stock.transfer')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        $title = trans('app.create');
        $warehouses = Branch::getAll();
        $transferStatuses = stockTransferStatuses();
        $transferredProducts = old('products') ?? []; // When form validation has error
        $products = [];

        if (old('original_warehouse')) {
            $products = $this->getProductsInStock(old('original_warehouse'));
        }

        return view('transfer.form', compact(
            'products',
            'title',
            'transfer',
            'transferredProducts',
            'transferStatuses',
            'warehouses'
        ));
    }

    /**
     * Show form to edit an existing stock transfer.
     *
     * @param Transfer $transfer
     *
     * @return Response
     */
    public function edit(Transfer $transfer)
    {
        if(!Auth::user()->can('stock.transfer')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        abort(404);
    }

    /**
     * Show stock transfer detail.
     *
     * @param Transfer $transfer
     *
     * @return Response
     */
    public function show(Transfer $transfer)
    {
        if(!Auth::user()->can('stock.transfer.browse')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }
        return view('transfer.show', compact('transfer'));
    }

    /**
     * Save new stock transfer.
     *
     * @param TransferRequest $request
     *
     * @return Response
     */
    public function save(TransferRequest $request)
    {
        if(!Auth::user()->can('stock.transfer')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }
        
        // Check quantity of each product if it's larger than in-stock quantity
        foreach ($request->products as $product) {
            $inStockProduct = ProductWarehouse::where('warehouse_id', $request->original_warehouse)
                            ->where('product_id', $product['id'])->first();
            if (empty($inStockProduct)) {
                return back()->withInput()->withErrors([
                    Message::ERROR_KEY => trans('message.invalid_product_data'),
                ]);
            } elseif ($product['quantity'] > $inStockProduct->quantity) {
                return back()->withInput()->withErrors([
                    Message::ERROR_KEY => trans('message.product_qty_lte_stock_qty'),
                ]);
            }
        }

        $transfer = new Transfer();
        $transfer->creator_id = auth()->user()->id;
        $transfer->from_warehouse_id = $request->original_warehouse;
        $transfer->to_warehouse_id = $request->target_warehouse;
        $transfer->transfer_date = dateIsoFormat($request->transfer_date);
        $transfer->reference_no = $request->invoice_id;
        $transfer->total_quantity = array_sum(array_column($request->products, 'quantity'));
        $transfer->shipping_cost = decimalNumber($request->shipping_cost);
        $transfer->note = $request->note;
        $transfer->status = $request->status;

        if (isset($request->document)) {
            $transfer->document = $this->uploadFile($this->documentFolder, $request->document);
        }

        if ($transfer->save()) {
            foreach ($request->products as $product) {
                // Save transfer detail
                $transferDetail = new TransferDetail();
                $transferDetail->transfer_id = $transfer->id;
                $transferDetail->product_id = $product['id'];
                $transferDetail->quantity = $product['quantity'];
                $transferDetail->save();

                if ($request->status == StockTransferStatus::COMPLETED) {
                    // Update stock of original warehouse and save stock history
                    $originalWarehouse = ProductWarehouse::selectQuery($request->original_warehouse, $product['id'])->first();
                    $originalWarehouse->quantity -= $product['quantity'];
                    $originalWarehouse->save();

                    $stockHistory = new StockHistory();
                    $stockHistory->transaction = StockTransaction::TRANSFER;
                    $stockHistory->transaction_id = $transfer->id;
                    $stockHistory->transaction_date = dateIsoFormat($request->transfer_date);
                    $stockHistory->warehouse_id = $request->original_warehouse;
                    $stockHistory->product_id = $product['id'];
                    $stockHistory->stock_type = StockType::STOCK_OUT;
                    $stockHistory->quantity = $product['quantity'];
                    $stockHistory->save();

                    // Update or insert stock of target warehouse and save stock history
                    $targetWarehouse = (ProductWarehouse::selectQuery($request->target_warehouse, $product['id'])->first()
                                        ?? new ProductWarehouse());
                    $targetWarehouse->product_id = $product['id'];
                    $targetWarehouse->warehouse_id = $request->target_warehouse;
                    $targetWarehouse->quantity += $product['quantity'];
                    $targetWarehouse->save();

                    $stockHistory = new StockHistory();
                    $stockHistory->transaction = StockTransaction::TRANSFER;
                    $stockHistory->transaction_id = $transfer->id;
                    $stockHistory->transaction_date = dateIsoFormat($request->transfer_date);
                    $stockHistory->warehouse_id = $request->target_warehouse;
                    $stockHistory->product_id = $product['id'];
                    $stockHistory->stock_type = StockType::STOCK_IN;
                    $stockHistory->quantity = $product['quantity'];
                    $stockHistory->save();
                }
            }
        }

        session()->flash(Message::SUCCESS_KEY, trans('message.item_saved_success'));
        return redirect(route('transfer.index'));
    }

    /**
     * Get all products in a specific warehouse.
     *
     * @param int $warehouseId
     *
     * @return Product|array Product data object or empty array
     */
    private function getProductsInStock($warehouseId)
    {
        $products = [];
        $productsInStock = ProductWarehouse::where('warehouse_id', $warehouseId)->get()->toArray();

        if (count($productsInStock) > 0) {
            $products = array_map(function ($value) {
                $product = Product::where('id', $value['product_id'])->first();
                $product->stock_qty = $value['quantity'];
                return $product;
            }, $productsInStock);
        }

        return $products;
    }

    /**
     * Get products by warehouse through AJAX request.
     *
     * @param Request $request
     * @param int $warehouseId
     *
     * @return Json
     */
    public function getProducts(Request $request, $warehouseId)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        $products = $this->getProductsInStock($warehouseId);
        return response()->json(['products' => $products]);
    }
}
