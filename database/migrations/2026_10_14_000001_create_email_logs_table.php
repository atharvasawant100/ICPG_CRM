<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Delivery log for every outgoing email.
     *
     * A row is written the moment an email is queued, so a stuck queue is
     * visible here rather than silently swallowed. Status then walks
     * queued -> sent, or queued -> failed with the transport error attached.
     */
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();

            // nullOnDelete: the log must survive the deletion of the actor.
            $table->foreignId('user_id')->nullable()
                ->constrained('users')->nullOnDelete();

            // InvoiceMail — lets a row be traced back to the class that built it.
            $table->string('mailable')->index();

            // invoice | overdue_reminder — the business reason for the send.
            $table->string('type')->index();

            $table->string('recipient_email')->index();
            $table->string('recipient_name')->nullable();
            $table->string('subject');

            // queued | sent | failed
            $table->string('status')->index();

            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('error_message')->nullable();

            // invoice_id, invoice_number, customer_id: enough to find the record
            // without hard foreign keys, so a deleted invoice keeps its trail.
            $table->json('context')->nullable();

            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->timestamps();

            // The log page filters on status inside a date window.
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};
