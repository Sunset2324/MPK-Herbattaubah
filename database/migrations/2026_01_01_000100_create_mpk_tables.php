<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_code', 30)->unique();
            $table->string('name', 150)->index();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('address', 255)->nullable();
            $table->foreignId('sponsor_id')->nullable()->index()->constrained('members')->nullOnDelete();
            $table->string('status', 20)->default('aktif');
            $table->date('joined_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('slug', 160)->unique();
            $table->string('category', 50)->default('Herbal');
            $table->text('description')->nullable();
            $table->unsignedInteger('price_member')->nullable();
            $table->unsignedInteger('price_retail')->nullable();
            $table->unsignedInteger('price_outside')->nullable();
            $table->string('image', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->index()->constrained('members')->cascadeOnDelete();
            $table->date('purchase_date')->index();
            $table->unsignedBigInteger('amount');
            $table->string('note', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 60)->primary();
            $table->text('value')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('products');
        Schema::dropIfExists('members');
    }
};
