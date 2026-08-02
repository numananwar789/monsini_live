<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SeedFakeOrders extends Command
{
    /**
     * php artisan orders:seed-fake --count=5000 --status=Pending
     *
     * --count     number of fake order rows to create (default 5000)
     * --status    order_status to assign; use "mixed" to randomize across
     *             Pending/Accepted/Shipped/Cancelled (default: Pending)
     * --truncate  empty the orders table first
     */
    protected $signature = 'orders:seed-fake
        {--count=5000 : Number of fake orders to create}
        {--status=Pending : order_status value to assign, or "mixed" to randomize}
        {--truncate : Truncate the orders table before seeding}';

    protected $description = 'Bulk-generate fake order rows for load/performance testing the Orders DataTable';

    protected $statuses = ['Pending', 'Accepted', 'Shipped', 'Cancelled'];

    /**
     * Inspects the real table structure and returns default values for any
     * column that is NOT NULL, has no default, and isn't auto-increment —
     * i.e. columns we'd otherwise have no way of knowing exist (like
     * "onway_vndr_prchs_ids"). Chosen by column type so inserts don't fail.
     */
    protected function buildAutoFillDefaults(string $table): array
    {
        $columns = DB::select("SHOW COLUMNS FROM `{$table}`");
        $autoFill = [];

        foreach ($columns as $col) {
            $field = $col->Field;
            $isNullable = $col->Null === 'YES';
            $hasDefault = $col->Default !== null;
            $isAutoIncrement = str_contains(strtolower($col->Extra ?? ''), 'auto_increment');

            if ($isNullable || $hasDefault || $isAutoIncrement) {
                continue;
            }

            $autoFill[$field] = $this->defaultValueForType($col->Type);
        }

        return $autoFill;
    }

    protected function defaultValueForType(string $type): mixed
    {
        $type = strtolower($type);

        if (str_contains($type, 'json')) {
            return json_encode([]);
        }
        if (str_contains($type, 'int')) {
            return 0;
        }
        if (str_contains($type, 'decimal') || str_contains($type, 'float') || str_contains($type, 'double')) {
            return 0;
        }
        if (str_contains($type, 'datetime') || str_contains($type, 'timestamp')) {
            return now()->format('Y-m-d H:i:s');
        }
        if (str_contains($type, 'date')) {
            return now()->format('Y-m-d');
        }
        if (str_contains($type, 'time')) {
            return now()->format('H:i:s');
        }

        // varchar/char/text/longtext/json fallback
        return '';
    }

    public function handle()
    {
        $count = (int) $this->option('count');
        $status = $this->option('status');
        $mixedStatus = strtolower($status) === 'mixed';

        $table = (new Order())->getTable();

        if (!Schema::hasTable($table)) {
            $this->error("Table \"{$table}\" does not exist. Check Order::getTable() / your orders migration.");
            return Command::FAILURE;
        }

        $columns = Schema::getColumnListing($table);

        if ($this->option('truncate')) {
            $this->warn("Truncating {$table}...");
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table($table)->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $customers = Customer::all();
        $vendors = Vendor::all();
        $products = Product::inRandomOrder()->limit(3000)->get();

        if ($customers->isEmpty()) {
            $this->error('No customers found. Seed/create customers first — needed for order_customer_name.');
            return Command::FAILURE;
        }
        if ($vendors->isEmpty()) {
            $this->error('No vendors found. Run "php artisan products:seed-fake" first (it creates vendors), or seed vendors directly.');
            return Command::FAILURE;
        }
        if ($products->isEmpty()) {
            $this->error('No products found. Run "php artisan products:seed-fake" first.');
            return Command::FAILURE;
        }

        // Defaults for any NOT NULL / no-default column we don't explicitly
        // populate below (e.g. onway_vndr_prchs_ids). Computed once up front.
        $autoFillDefaults = $this->buildAutoFillDefaults($table);
        if (!empty($autoFillDefaults)) {
            $this->comment('Auto-filling NOT NULL columns with no explicit value: ' . implode(', ', array_keys($autoFillDefaults)));
        }

        $chunkSize = 500;
        $buffer = [];
        $total = 0;
        $lastCandidateKeys = [];

        $this->info("Generating {$count} fake orders into `{$table}`...");
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        for ($i = 0; $i < $count; $i++) {
            $customer = $customers->random();
            $vendor = $vendors->random();
            $product = $products->random();

            $rowStatus = $mixedStatus
                ? $this->statuses[array_rand($this->statuses)]
                : $status;

            $givenByInventory = fake()->boolean(15) ? fake()->numberBetween(1, 5) : 0;
            $givenByOnway = ($givenByInventory === 0 && fake()->boolean(10)) ? fake()->numberBetween(1, 5) : 0;

            // Every plausible column name this table might have. Only the
            // ones that actually exist on the table (per Schema::getColumnListing)
            // get inserted — anything else is silently dropped below, so this
            // is safe to run even if some of these guesses are wrong.
            $candidate = [
                'order_GUID' => (string) Str::uuid(),
                'purchase_id' => fake()->bothify('PO-######'),
                'order_customer_ID' => $customer->cust_ID ?? $customer->id ?? null,
                'order_customer_name' => $customer->cust_comp_name ?? fake()->company(),
                'order_vendor_ID' => $vendor->vendor_ID ?? $vendor->id ?? null,
                'order_vendor_name' => $vendor->vendor_comp_name,
                'order_product_ID' => $product->product_ID ?? $product->id ?? null,
                'order_product_style' => $product->product_style,
                'order_product_color' => $product->product_color,
                'sub_products' => json_encode([]),
                'order_product_size' => $product->product_size_range,
                'order_quantity' => fake()->numberBetween(1, 20),
                'order_wear_date' => fake()->dateTimeBetween('now', '+3 months')->format('Y-m-d'),
                'given_by_invntry' => $givenByInventory,
                'given_by_onway' => $givenByOnway,
                'order_cost' => $product->product_cost,
                'order_purchase_price' => $product->product_wholesale_price,
                'order_status' => $rowStatus,
                'user_flag' => fake()->userName(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $lastCandidateKeys = array_keys($candidate);

            // Keep only keys that are real columns on this table, then layer
            // in defaults for any other NOT NULL column we don't know about.
            $row = array_intersect_key($candidate, array_flip($columns));
            $row = array_merge($autoFillDefaults, $row);

            $buffer[] = $row;
            $total++;

            if (count($buffer) >= $chunkSize) {
                DB::table($table)->insert($buffer);
                $buffer = [];
            }

            $bar->advance();
        }

        if (!empty($buffer)) {
            DB::table($table)->insert($buffer);
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. Inserted {$total} fake orders into `{$table}`.");

        // Flag any real table columns still left completely unset, in case
        // they're NOT NULL without a default and caused a failed insert above.
        $ignorable = ['order_ID', 'id'];
        $handled = array_merge($lastCandidateKeys, array_keys($autoFillDefaults));
        $unmapped = array_values(array_diff($columns, $handled, $ignorable));
        if (!empty($unmapped)) {
            $this->comment('Columns on this table left unset (should be nullable/have a default): ' . implode(', ', $unmapped));
        }

        return Command::SUCCESS;
    }
}
