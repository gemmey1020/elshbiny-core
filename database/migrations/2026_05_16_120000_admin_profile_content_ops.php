<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default('buyer')->index()->after('password');
            $table->string('phone')->nullable()->after('role');
            $table->string('company_name')->nullable()->after('phone');
            $table->text('address')->nullable()->after('company_name');
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0)->index()->after('slug');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->unsignedInteger('sort_order')->default(0)->index()->after('sku');
        });

        Schema::table('consultations', function (Blueprint $table): void {
            $table->string('interest_type')->nullable()->after('message');
            $table->string('source_page')->nullable()->after('interest_type');
            $table->string('utm_source')->nullable()->after('source_page');
            $table->string('utm_medium')->nullable()->after('utm_source');
            $table->string('utm_campaign')->nullable()->after('utm_medium');
        });

        Schema::create('quote_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('actor_type');
            $table->string('field');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE quotes MODIFY status ENUM('pending', 'contacted', 'won', 'lost', 'cancelled') NOT NULL DEFAULT 'pending'");
            DB::statement("ALTER TABLE products MODIFY price_visibility ENUM('public', 'authenticated', 'hidden') NOT NULL DEFAULT 'authenticated'");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("UPDATE quotes SET status = 'lost' WHERE status = 'cancelled'");
            DB::statement("ALTER TABLE quotes MODIFY status ENUM('pending', 'contacted', 'won', 'lost') NOT NULL DEFAULT 'pending'");
            DB::statement("ALTER TABLE products MODIFY price_visibility ENUM('public', 'authenticated', 'hidden') NOT NULL DEFAULT 'public'");
        }

        Schema::dropIfExists('quote_logs');

        Schema::table('consultations', function (Blueprint $table): void {
            $table->dropColumn([
                'interest_type',
                'source_page',
                'utm_source',
                'utm_medium',
                'utm_campaign',
            ]);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('sort_order');
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn('sort_order');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['role', 'phone', 'company_name', 'address']);
        });
    }
};
