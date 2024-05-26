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

        Schema::create('friends', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('requestId')->unsigned();
            $table->integer('responseId')->unsigned();
            $table->enum('status', ['APPROVED', 'PENDING']);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // SET FOREIGN
            $table->foreign('requestId')->references('id')->on('users');
            $table->foreign('responseId')->references('id')->on('users');
        });    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('friends');
    }
};
