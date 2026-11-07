<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Transaction;
use App\Product;
use Carbon\Carbon;

class SyncPosData extends Command
{
    protected $signature = 'pos:sync';
    protected $description = 'Synchronize offline POS sales and catalog with live cloud server';

    public function handle()
    {
        $cloudUrl = config('app.cloud_sync_url', 'https://business.jadidkhata.com');

        $this->info("Checking connection to {$cloudUrl}...");
        try {
            $ping = Http::timeout(4)->get("{$cloudUrl}/api/sync/ping");
            if (!$ping->successful()) {
                $this->warn("Cloud server is not reachable. Staying in offline mode.");
                return 0;
            }
        } catch (\Exception $e) {
            $this->warn("Offline: " . $e->getMessage());
            return 0;
        }

        $this->info("Online! Starting two-way sync...");

        // 1. Upstream Sync: Push local pending sales to Cloud
        $this->syncUpstreamSales($cloudUrl);

        // 2. Downstream Sync: Pull updated products & prices from Cloud
        $this->syncDownstreamCatalog($cloudUrl);

        $this->info("Sync completed successfully.");
        return 0;
    }

    protected function syncUpstreamSales($cloudUrl)
    {
        $pending = Transaction::where('sync_status', 'pending')
            ->limit(50)
            ->get();

        if ($pending->isEmpty()) {
            $this->info("No pending offline sales to upload.");
            return;
        }

        $this->info("Uploading " . $pending->count() . " offline sales...");

        foreach ($pending as $sale) {
            try {
                $lines = DB::table('transaction_sell_lines')->where('transaction_id', $sale->id)->get();
                $payments = DB::table('transaction_payments')->where('transaction_id', $sale->id)->get();

                $payload = [
                    'client_uuid' => $sale->client_uuid ?? (string) \Illuminate\Support\Str::uuid(),
                    'transaction' => $sale->toArray(),
                    'lines' => $lines->toArray(),
                    'payments' => $payments->toArray()
                ];

                $response = Http::timeout(8)->post("{$cloudUrl}/api/sync/push", $payload);

                if ($response->successful()) {
                    $sale->sync_status = 'synced';
                    $sale->synced_at = Carbon::now();
                    $sale->save();
                    $this->info("Synced sale: {$sale->invoice_no}");
                }
            } catch (\Exception $e) {
                $this->error("Failed syncing sale {$sale->invoice_no}: " . $e->getMessage());
                break;
            }
        }
    }

    protected function syncDownstreamCatalog($cloudUrl)
    {
        $lastSync = Product::max('updated_at') ?? '2000-01-01 00:00:00';
        $this->info("Checking updates since {$lastSync}...");

        try {
            $response = Http::timeout(10)->get("{$cloudUrl}/api/sync/pull", [
                'since' => $lastSync
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $products = $data['products'] ?? [];

                foreach ($products as $prod) {
                    $local = Product::find($prod['id']);
                    if ($local) {
                        $local->update($prod);
                    } else {
                        Product::create($prod);
                    }
                }
                $this->info("Pulled " . count($products) . " updated products from cloud.");
            }
        } catch (\Exception $e) {
            $this->warn("Downstream sync warning: " . $e->getMessage());
        }
    }
}

