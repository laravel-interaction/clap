<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplauseTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(
            config('clap.table_names.pivot'),
            static function (Blueprint $table): void {
                config('clap.uuids') ? $table->uuid('uuid') : $table->bigIncrements('id');
                $table->unsignedBigInteger(config('clap.column_names.user_foreign_key'))
                    ->index()
                    ->comment('user_id');
                $table->morphs('clappable');
                $table->timestamps();
                $table->index([config('clap.column_names.user_foreign_key'), 'clappable_type', 'clappable_id']);
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('clap.table_names.applause'));
    }
}
