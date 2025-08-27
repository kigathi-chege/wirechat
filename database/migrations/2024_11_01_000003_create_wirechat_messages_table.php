<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Namu\WireChat\Facades\WireChat;
use Namu\WireChat\Models\Conversation;
use Namu\WireChat\Models\Message;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $usesUuid = WireChat::usesUuid();
        $tableName = (new Message)->getTable();

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($usesUuid) {
                $table->id();

                if ($usesUuid) {
                    $table->uuid('conversation_id');
                } else {
                    $table->unsignedBigInteger('conversation_id');
                }
                $table->foreign('conversation_id')->references('id')->on((new Conversation)->getTable())->cascadeOnDelete();

                $table->unsignedBigInteger('sendable_id');
                $table->string('sendable_type');

                $table->unsignedBigInteger('reply_id')->nullable();
                $table->foreign('reply_id')->references('id')->on((new Message)->getTable())->nullOnDelete();

                $table->text('body')->nullable();
                $table->string('type')->default('text');

                $table->timestamp('kept_at')->nullable()->comment('filled when a message is kept from disappearing');

                $table->softDeletes();
                $table->timestamps();

                // Indexes for optimization
                $table->index(['conversation_id']);
                $table->index(['sendable_id', 'sendable_type']);
            });
        }

        if (!Schema::hasColumn($tableName, 'openai_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('openai_id')->nullable();
            });
        }

        if (!Schema::hasColumn($tableName, 'open_a_i_thread_run_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignIdFor(\App\Models\OpenAIThreadRun::class)->nullable()->constrained()->cascadeOnDelete();
            });
        }

        if (!Schema::hasColumn($tableName, 'openai_thread_run_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('openai_thread_run_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists((new Message)->getTable());
    }
};
