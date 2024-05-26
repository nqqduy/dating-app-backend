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
        Schema::create('comments', function(Blueprint $table) {
            $table->increments('id');
            $table->text('content');
            $table->dateTime('date');
            $table->integer('userId')->unsigned();
            $table->integer('postId')->unsigned();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // SET FOREIGN
            $table->foreign('userId')->references('id')->on('users');
            $table->foreign('postId')->references('id')->on('posts');
        });     }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('comments');
    }
};
