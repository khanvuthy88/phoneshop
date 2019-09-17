@switch ($purchase->purchase_status)
    @case (PurchaseStatus::RECEIVED)
    <label class="badge badge-success">{{ purchaseStatuses($purchase->purchase_status) }}</label>
    @break
    @case (PurchaseStatus::ORDERED)
    <label class="badge badge-warning">{{ purchaseStatuses($purchase->purchase_status) }}</label>
    @break
@endswitch
