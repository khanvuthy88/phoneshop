<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Constants\Message;
use App\Constants\StockTransaction;
use App\Constants\StockType;
use App\Models\Adjustment;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Models\StockHistory;
use App\Http\Requests\AdjustmentRequest;
use App\Traits\FileHandling;

use Auth;

class AdjustmentController extends Controller
{
    use FileHandling;

    /** @var string Folder name to store adjustment document */
    private $documentFolder = 'adjustment';

    /**
     * Display a listing of stock adjustments.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        if(!Auth::user()->can('stock.adjust.browse')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }
        $adjustments = Adjustment::query();

        $itemCount = $adjustments->count();
        $adjustments = $adjustments->sortable()->latest()->paginate(paginationCount());
        $offset = offset($request->page);

        return view('adjustment.index', compact('adjustments', 'itemCount', 'offset'));
    }

    /**
     * Show form to create stock adjustment.
     *
     * @param Adjustment $adjustment
     *
     * @return Response
     */
    public function create(Adjustment $adjustment)
    {
        if(!Auth::user()->can('stock.adjust')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }
        $title = trans('app.create');
        $stockTypes = stockTypes();
        $warehouses = Branch::getAll();
        $products = Product::getAll();

        if (old('warehouse') && old('product')) {
            $productStockQty = (ProductWarehouse::selectQuery(old('warehouse'), old('product'))->first()->quantity ?? 0);
        } else {
            $productStockQty = trans('app.n/a');
        }

        return view('adjustment.form', compact(
            'products',
            'productStockQty',
            'stockTypes',
            'title',
            'warehouses'
        ));
    }

    /**
     * Show form to edit an existing stock adjustment.
     *
     * @param Adjustment $adjustment
     *
     * @return Response
     */
    public function edit(Adjustment $adjustment)
    {
        abort(404);
    }

    /**
     * Save new stock adjustment.
     *
     * @param AdjustmentRequest $request
     *
     * @return Response
     */
    public function save(AdjustmentRequest $request)
    {
        if(!Auth::user()->can('stock.adjust')){
            return back()->with([Message::ERROR_KEY=>trans('message.no_permission'), 'alert-type' => 'warning'], 403);
        }

        $isStockOut = ($request->action == StockType::STOCK_OUT);
        $inStockProduct = ProductWarehouse::selectQuery($request->warehouse, $request->product)->first();

        // Check if quantity of stock-out adjustment is larger than in-stock quantity
        if ($isStockOut && (empty($inStockProduct) || $request->quantity > $inStockProduct->quantity)) {
            return back()->withInput()->withErrors([
                Message::ERROR_KEY => trans('message.product_qty_lte_stock_qty'),
            ]);
        }

        $adjustment = new Adjustment();
        $adjustment->creator_id = auth()->user()->id;
        $adjustment->warehouse_id = $request->warehouse;
        $adjustment->product_id = $request->product;
        $adjustment->action = $request->action;
        $adjustment->adjustment_date = dateIsoFormat($request->adjustment_date);
        $adjustment->quantity = $request->quantity;
        $adjustment->reason = $request->reason;

        if ($adjustment->save()) {
            // Update product stock
            $productWarehouse = ($inStockProduct ?? new ProductWarehouse());
            $productWarehouse->warehouse_id = $request->warehouse;
            $productWarehouse->product_id = $request->product;
            $productWarehouse->quantity = ($isStockOut ? ($productWarehouse->quantity - $request->quantity)
                                        : ($productWarehouse->quantity + $request->quantity));
            $productWarehouse->save();

            // Save stock history
            $stockHistory = new StockHistory();
            $stockHistory->transaction = StockTransaction::ADJUSTMENT;
            $stockHistory->transaction_id = $adjustment->id;
            $stockHistory->transaction_date = dateIsoFormat($request->adjustment_date);
            $stockHistory->warehouse_id = $request->warehouse;
            $stockHistory->product_id = $request->product;
            $stockHistory->stock_type = $request->action;
            $stockHistory->quantity = $request->quantity;
            $stockHistory->save();
        }

        session()->flash(Message::SUCCESS_KEY, trans('message.item_saved_success'));
        return redirect(route('adjustment.index'));
    }

    /**
     * Get stock quantity of a product in a warehouse.
     *
     * @param Request $request
     * @param int $warehouseId
     * @param int $productId
     *
     * @return int|string
     */
    public function getStockQuantity(Request $request, $warehouseId, $productId)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        $stockQty = (ProductWarehouse::selectQuery($warehouseId, $productId)->first()->quantity ?? 0);
        return $stockQty;
    }
}
