<?php

use App\Http\Controllers\OrderFinalController;
use App\Mail\VendorOrderNotification;
use Illuminate\Http\Request;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

it('really sends one vendor email with a large body', function () {
    // Force real delivery, regardless of phpunit.xml (which usually sets array)
    config([
        'mail.default' => 'smtp',
        'queue.default' => 'sync', // in case the mailable implements ShouldQueue
    ]);
    Mail::purge('smtp'); // drop any cached mailer instance

    // Record what the transport really sent (do NOT use Event::fake here)
    $sent = [];
    Event::listen(MessageSent::class, function (MessageSent $event) use (&$sent) {
        $sent[] = $event->message;
    });

    if (!Schema::hasColumn('dt_order_allocation', 'sub_products')) {
        Schema::table('dt_order_allocation', function ($table) {
            $table->text('sub_products')->nullable();
        });
    }

    $vendorEmail = 'numansproduction101@gmail.com';
    $vendorName = 'Test Vendor';
    $emailBody = str_repeat('Long vendor order instructions. ', 10000);
    $orderIds = range(1, 12);

    DB::table('dt_vendor')->insert([
        'vendor_ID' => 1,
        'vendor_comp_name' => $vendorName,
        'vendor_email' => $vendorEmail,
        'message' => '',
    ]);

    DB::table('email_body')->insert([
        'email_id' => 1,
        'email_role' => 'vendor',
        'email_body' => $emailBody,
    ]);

    foreach ($orderIds as $index => $orderId) {
        $order = [
            'order_ID' => $orderId,
            'order_customer_ID' => null,
            'order_customer_name' => 'Test Customer',
            'order_vendor_ID' => 1,
            'order_vendor_name' => $vendorName,
            'order_product_ID' => $orderId,
            'order_product_style' => sprintf('STYLE-%02d', $index + 1),
            'order_product_color' => 'Black',
            'order_product_size' => '2',
            'order_quantity' => 1,
            'given_by_invntry' => 0,
            'given_by_onway' => 0,
            'order_cost' => '10.00',
            'order_purchase_price' => '12.00',
            'order_note' => '',
            'purchase_id' => 'PURCHASE-' . $orderId,
            'onway_vndr_prchs_ids' => '',
            'onway_cstmr_prchs_ids' => '',
            'order_status' => 'Pending',
        ];

        DB::table('dt_order_final')->insert(array_merge($order, [
            'final_ID' => $orderId,
            'created_at_final' => now(),
        ]));
        DB::table('dt_order')->insert($order);
    }

    app(OrderFinalController::class)->confirmVendor(
        Request::create('/admin/final-orders/confirm-vendor', 'POST', [
            'orders' => $orderIds,
            'dateNow' => '2026-11-01',
        ])
    );

    // Exactly one message actually went through SMTP
    expect($sent)->toHaveCount(1);

    $message = $sent[0];
    expect(collect($message->getTo())->map->getAddress()->all())->toContain($vendorEmail);

    $html = (string) $message->getHtmlBody();
    expect(strlen($html))->toBeGreaterThan(250_000)
        ->and($html)->toContain('STYLE-01')
        ->and($html)->toContain('STYLE-12');
})
    ->group('real-mail')
    ->skip(fn() => !env('RUN_REAL_MAIL_TEST'), 'Set RUN_REAL_MAIL_TEST=true to send a real email');
