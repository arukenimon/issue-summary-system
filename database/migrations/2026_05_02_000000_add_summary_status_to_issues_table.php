<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            // Tracks the async summary lifecycle: pending on create, ready once the
            // job succeeds, failed if the job exhausts its retries.
            $table->enum('summary_status', ['pending', 'ready', 'failed'])
                ->default('pending')
                ->after('next_action');
        });
    }

    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->dropColumn('summary_status');
        });
    }
};
