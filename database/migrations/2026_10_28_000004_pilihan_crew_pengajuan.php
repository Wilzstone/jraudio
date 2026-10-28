<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pengajuan_sewa', function (Blueprint $table) {
            $table->string('pilihan_crew', 20)->nullable()->after('rekomendasi_paket');
        });
    }
    public function down(): void
    {
        Schema::table('pengajuan_sewa', fn (Blueprint $table) => $table->dropColumn('pilihan_crew'));
    }
};
