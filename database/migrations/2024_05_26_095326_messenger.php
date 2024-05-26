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
        Schema::create('messenger', function(Blueprint $table) {
            $table->increments('id');
            $table->text('content');
            $table->integer('senderId')->unsigned();
            $table->integer('receiveId')->unsigned();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // SET FOREIGN
            $table->foreign('senderId')->references('id')->on('users');
            $table->foreign('receiveId')->references('id')->on('users');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('messenger');
    }
};
