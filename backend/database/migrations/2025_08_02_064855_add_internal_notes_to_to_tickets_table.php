<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->unsignedBigInteger('internal_notes')->nullable()->after('user_id');

                $table->foreign('internal_notes')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropForeign(['internal_notes']);
                $table->dropColumn('internal_notes');
            });
        });
    }
};
