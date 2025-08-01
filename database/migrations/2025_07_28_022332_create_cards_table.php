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
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('card_number')->unique();
            $table->string('qr_code')->nullable();
            $table->enum('status',['available','active',' blocked'])->default('available');
            $table->decimal('balance',10,2)->default(0.00);

            $table->foreignId('member_id')->nullable()->constrained('members')->onDelete('set null');
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cards', function(Blueprint $table){
            $table->dropForeign(['member_id']);
            $table->dropColumn('member_id');
            $table->dropColumn('organization_id');
        });
        Schema::dropIfExists('cards');
    }
};
