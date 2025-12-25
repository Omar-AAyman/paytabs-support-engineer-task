<?php

use App\Enums\OrderStatus;
use App\Enums\ShippingMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->decimal('cart_total', 10, 2);
            $table->enum('shipping_method', array_column(ShippingMethod::cases(), 'value'))->default(ShippingMethod::Pickup->value);
            $table->enum('status', array_column(OrderStatus::cases(), 'value'))->default(OrderStatus::Pending->value);
            $table->string('customer_email');
            $table->string('customer_name');
            $table->text('address');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
