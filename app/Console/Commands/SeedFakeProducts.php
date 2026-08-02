<?php

namespace App\Console\Commands;

use App\Models\SubProduct;
use App\Models\Vendor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedFakeProducts extends Command
{
    /**
     * php artisan products:seed-fake --styles=5000 --vendors=15
     *
     * --styles   number of distinct product styles to create (default 5000)
     * --vendors  number of fake vendors to create if none exist (default 15)
     * --min-colors / --max-colors  color variants per style (default 1-4)
     * --truncate flush dt_product first
     */
    protected $signature = 'products:seed-fake
        {--styles=5000 : Number of distinct product styles to create}
        {--vendors=15 : Number of vendors to create if none exist}
        {--min-colors=1 : Minimum color variants per style}
        {--max-colors=4 : Maximum color variants per style}
        {--truncate : Truncate dt_product before seeding}';

    protected $description = 'Bulk-generate fake product rows for load/performance testing the products DataTable';

    protected $sizeRanges = ['2-16', '0-14', '00-24', '0-22'];

    public function handle()
    {
        $styleCount = (int) $this->option('styles');
        $vendorCount = (int) $this->option('vendors');
        $minColors = (int) $this->option('min-colors');
        $maxColors = (int) $this->option('max-colors');

        if ($this->option('truncate')) {
            $this->warn('Truncating dt_product...');
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('dt_product')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        // dt_vendor's own columns/timestamps vary by install, so build the
        // vendor insert dynamically rather than assuming created_at/updated_at exist.
        $vendorHasTimestamps = Schema::hasColumn('dt_vendor', 'created_at');

        $vendors = Vendor::all(['vendor_ID', 'vendor_comp_name']);
        if ($vendors->isEmpty()) {
            $this->info("No vendors found, creating {$vendorCount} fake vendors...");
            $vendorRows = [];
            for ($i = 0; $i < $vendorCount; $i++) {
                $row = ['vendor_comp_name' => fake()->company()];
                if ($vendorHasTimestamps) {
                    $row['created_at'] = now();
                    $row['updated_at'] = now();
                }
                $vendorRows[] = $row;
            }
            DB::table('dt_vendor')->insert($vendorRows);
            $vendors = Vendor::all(['vendor_ID', 'vendor_comp_name']);
        }

        $subProductNames = SubProduct::pluck('sub_product_name')->toArray();
        if (empty($subProductNames)) {
            $subProductNames = ['Belt', 'Scarf', 'Gloves', 'Hat', 'Bag', 'Wallet'];
        }

        $colorPalette = [
            'Black',
            'White',
            'Red',
            'Blue',
            'Navy',
            'Grey',
            'Green',
            'Beige',
            'Brown',
            'Pink',
            'Purple',
            'Yellow',
            'Orange',
            'Teal',
            'Maroon',
            'Ivory',
        ];

        $totalRows = 0;
        $chunkSize = 500;
        $buffer = [];

        // Load styles already in the table so repeated runs of this command
        // always add genuinely new styles instead of occasionally colliding
        // with ones inserted by a previous run (fake()->unique() only
        // dedupes within a single execution, not across runs).
        $existingStyles = array_flip(
            DB::table('dt_product')->pluck('product_style')->all()
        );

        $this->info("Generating {$styleCount} styles (colors {$minColors}-{$maxColors} each)...");
        $bar = $this->output->createProgressBar($styleCount);
        $bar->start();

        for ($s = 0; $s < $styleCount; $s++) {
            $vendor = $vendors->random();

            do {
                $style = strtolower(fake()->unique()->bothify('ST-####??'));
            } while (isset($existingStyles[$style]));
            $existingStyles[$style] = true;
            $factoryStyle = strtolower(fake()->bothify('FS-#####'));
            $sizeRange = $this->sizeRanges[array_rand($this->sizeRanges)];
            $cost = fake()->randomFloat(2, 3, 80);
            $wholesale = round($cost * fake()->randomFloat(2, 1.5, 3), 2);
            $link = 'https://example.com/products/' . $style;
            $image = 'https://picsum.photos/seed/' . $style . '/300/300';
            $versionYear = fake()->numberBetween(2021, 2026);

            $numColors = fake()->numberBetween($minColors, $maxColors);
            $colors = (array) fake()->randomElements($colorPalette, min($numColors, count($colorPalette)));

            $numSubProducts = fake()->numberBetween(0, 3);
            $subProducts = $numSubProducts > 0
                ? fake()->randomElements($subProductNames, min($numSubProducts, count($subProductNames)))
                : [];

            foreach ($colors as $color) {
                $buffer[] = [
                    'product_style' => $style,
                    'factory_style' => $factoryStyle,
                    'product_color' => strtolower($color),
                    'product_size_range' => $sizeRange,
                    // product_cost / product_wholesale_price are varchar columns in dt_product,
                    // so cast to string explicitly.
                    'product_cost' => (string) $cost,
                    'product_wholesale_price' => (string) $wholesale,
                    'product_vendor_ID' => $vendor->vendor_ID,
                    'product_vendor_name' => $vendor->vendor_comp_name,
                    'product_link' => $link,
                    'product_image' => $image,
                    'sub_products' => json_encode($subProducts),
                    'version_year' => $versionYear,
                    'product_status' => fake()->numberBetween(0, 1),
                    'show_from_inventory' => fake()->numberBetween(0, 1),
                ];

                $totalRows++;

                if (count($buffer) >= $chunkSize) {
                    DB::table('dt_product')->insert($buffer);
                    $buffer = [];
                }
            }

            $bar->advance();
        }

        if (!empty($buffer)) {
            DB::table('dt_product')->insert($buffer);
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. Inserted {$totalRows} product rows across {$styleCount} styles.");

        return Command::SUCCESS;
    }
}
