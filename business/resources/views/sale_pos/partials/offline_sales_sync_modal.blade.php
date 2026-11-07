<!-- Offline Sales Sync Modal -->
<div class="modal fade" id="offline_sales_sync_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-sync-alt"></i> Sync Offline Sales
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Offline Sales Queue</strong> — Sales made while offline are listed below. Select which sales to sync now.
                        </div>
                    </div>
                </div>

                <!-- Offline Banner -->
                <div id="offline-mode-banner" class="alert alert-warning hide">
                    <i class="fas fa-wifi"></i> <strong>No Internet Connection</strong> — You are currently offline. Sales will be queued and synced automatically when you reconnect.
                </div>

                <!-- Sync Controls -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-checkbox" style="font-weight:normal;cursor:pointer;">
                            <input type="checkbox" id="select-all-offline-sales">
                            &nbsp;Select All Sales
                        </label>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="button" id="sync-selected-sales-btn" class="btn btn-success" disabled>
                            <i class="fas fa-sync"></i> Sync Selected (<span id="selected-sales-count">0</span>)
                        </button>
                    </div>
                </div>

                <!-- Sales List -->
                <div id="offline-sales-container">
                    <div class="text-center py-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Loading offline sales...</p>
                    </div>
                </div>

                <!-- Empty State -->
                <div id="no-offline-sales" class="text-center py-5 hide">
                    <i class="fas fa-check-circle fa-3x text-success"></i>
                    <h4 class="mt-3">No Pending Offline Sales</h4>
                    <p class="text-muted">All offline sales have been synced to the server.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.offline-sale-item {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 12px;
    padding: 15px;
    background: #fafafa;
    cursor: pointer;
    transition: background 0.2s, border-color 0.2s;
}
.offline-sale-item:hover { background: #f0f8ff; border-color: #007bff; }
.offline-sale-item.selected { background: #e7f3ff; border-color: #007bff; box-shadow: 0 2px 4px rgba(0,123,255,.1); }
.offline-sale-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.offline-sale-id { font-weight: bold; color: #007bff; font-size: 0.85em; word-break: break-all; }
.offline-sale-time { font-size: 0.85em; color: #666; }
.offline-sale-status { padding: 2px 8px; border-radius: 12px; font-size: 0.75em; font-weight: bold; }
.status-pending { background: #fff3cd; color: #856404; }
.status-failed { background: #f8d7da; color: #721c24; }
.offline-sale-items { margin: 10px 0; padding: 10px; background: white; border-radius: 4px; }
.item-row { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid #f0f0f0; }
.item-row:last-child { border-bottom: none; }
.item-name { flex: 1; font-weight: 500; }
.item-quantity { color: #666; min-width: 50px; text-align: center; }
.item-price { font-weight: bold; color: #28a745; min-width: 80px; text-align: right; }
.offline-sale-total { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; padding-top: 10px; border-top: 2px solid #e0e0e0; }
.total-label { font-weight: bold; font-size: 1.1em; }
.total-amount { font-weight: bold; font-size: 1.2em; color: #007bff; }
.sync-error-message { background: #f8d7da; color: #721c24; padding: 8px; border-radius: 4px; margin-top: 8px; font-size: 0.85em; }
.sale-checkbox { display: flex; align-items: center; gap: 8px; }
.sale-checkbox input[type="checkbox"] { transform: scale(1.2); }
.customer-info { color: #666; font-size: 0.9em; margin: 5px 0; }
.payment-method { display: inline-block; padding: 2px 6px; background: #e9ecef; border-radius: 3px; font-size: 0.8em; margin-left: 8px; }
</style>
