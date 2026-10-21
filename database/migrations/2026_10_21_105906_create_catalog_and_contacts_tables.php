<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 180);
            $table->string('model', 150)->nullable();
            $table->string('sku', 80)->nullable()->unique();
            $table->enum('speed', ['75', '76', '77', '78', '79']);
            $table->unsignedInteger('shuttlecocks_per_tube');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['brand_id', 'name']);
        });

        foreach (['suppliers', 'buyers'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->string('name', 180);
                $table->string('email', 254)->nullable();
                $table->string('phone', 50)->nullable();
                $table->text('address')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('name');
                $table->index('email');
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE products ADD CONSTRAINT products_shuttlecocks_per_tube_positive CHECK (shuttlecocks_per_tube > 0)');
        } elseif (DB::getDriverName() === 'sqlite') {
            DB::unprepared(<<<'SQL'
                CREATE TRIGGER products_shuttlecocks_per_tube_insert
                BEFORE INSERT ON products
                FOR EACH ROW WHEN NEW.shuttlecocks_per_tube <= 0
                BEGIN SELECT RAISE(ABORT, 'shuttlecocks_per_tube must be positive'); END
            SQL);
            DB::unprepared(<<<'SQL'
                CREATE TRIGGER products_shuttlecocks_per_tube_update
                BEFORE UPDATE OF shuttlecocks_per_tube ON products
                FOR EACH ROW WHEN NEW.shuttlecocks_per_tube <= 0
                BEGIN SELECT RAISE(ABORT, 'shuttlecocks_per_tube must be positive'); END
            SQL);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::unprepared('DROP TRIGGER IF EXISTS products_shuttlecocks_per_tube_insert');
            DB::unprepared('DROP TRIGGER IF EXISTS products_shuttlecocks_per_tube_update');
        }

        Schema::dropIfExists('buyers');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('products');
        Schema::dropIfExists('brands');
    }
};
