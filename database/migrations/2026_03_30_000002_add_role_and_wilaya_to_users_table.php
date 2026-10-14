<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->unique()->after('id');
            $table->foreignId('wilaya_id')->nullable()->constrained('wilayas')->nullOnDelete()->after('email');
            $table->string('role', 50)->default('dass')->after('wilaya_id');
            $table->boolean('is_active')->default(true)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['wilaya_id']);
            $table->dropColumn(['username', 'wilaya_id', 'role', 'is_active']);
        });
    }
};
