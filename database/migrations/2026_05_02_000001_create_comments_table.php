<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            // Cascade so comments are removed with their parent issue. The index is
            // created automatically by constrained() and powers the hasMany lookup.
            $table->foreignId('issue_id')->constrained()->cascadeOnDelete();
            $table->string('author_name');
            $table->text('body');
            // Comments are immutable once posted, so only a creation timestamp is kept.
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
