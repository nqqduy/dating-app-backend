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
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['authorId']);
            $table->foreign('authorId')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('friends', function (Blueprint $table) {
            $table->dropForeign(['requestId']);
            $table->dropForeign(['responseId']);
            $table->foreign('requestId')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('responseId')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign(['userId']);
            $table->dropForeign(['postId']);
            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('postId')->references('id')->on('posts')->onDelete('cascade');
        });

        Schema::table('messenger', function (Blueprint $table) {
            $table->dropForeign(['senderId']);
            $table->dropForeign(['receiveId']);
            $table->foreign('senderId')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiveId')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['authorId']);
        });

        Schema::table('friends', function (Blueprint $table) {
            $table->dropForeign(['requestId']);
            $table->dropForeign(['responseId']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign(['userId']);
            $table->dropForeign(['postId']);
        });

        Schema::table('messenger', function (Blueprint $table) {
            $table->dropForeign(['senderId']);
            $table->dropForeign(['receiveId']);
        });
    }
};
