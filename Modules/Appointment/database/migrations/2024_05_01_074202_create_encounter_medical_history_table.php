<?php

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
        Schema::create('encounter_medical_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('encounter_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('type')->nullable(); 
            $table->string('title')->nullable(); 
            $table->integer('is_from_template')->default(0); 
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();   
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('encounter_medical_history');
    }
};
