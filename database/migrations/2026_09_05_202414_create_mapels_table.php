<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mapel', function (Blueprint $table) {
            $table->id(); // Auto increment (unsignedBigInteger)
            $table->string('kodemapel');
            $table->string('namamapel');
            $table->timestamp('createdat')->useCurrent();
            $table->timestamp('updatedat')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('deletedat')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mapel');
    }
};