<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersChatsMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_chats_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('receiver_id');
            $table->unsignedBigInteger('chat_id');
            $table->unsignedBigInteger('message_id');
            $table->boolean('viewed')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('users_chats_messages', function (Blueprint $table) {
            $table->foreign('receiver_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('chat_id')->references('id')->on('chats')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('message_id')->references('id')->on('messages')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_chats_messages', function (Blueprint $table) {
            $table->dropForeign('users_chats_messages_receiver_id_foreign');
            $table->dropForeign('users_chats_messages_chat_id_foreign');
            $table->dropForeign('users_chats_messages_message_id_foreign');
        });
        
        Schema::dropIfExists('users_chats_messages');
    }
}
