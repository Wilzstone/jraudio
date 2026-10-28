<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('pengajuan_sewa', function (Blueprint $table) {
            $table->integer('id_pelanggan')->nullable()->after('id_pengajuan');
            $table->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan')->nullOnDelete();
        });
        Schema::table('transaksi', function (Blueprint $table) {
            $table->text('alamat_lokasi_acara')->nullable();
            $table->string('link_google_maps', 2048)->nullable();
        });
    }
    public function down(): void {
        Schema::table('transaksi', fn (Blueprint $table) => $table->dropColumn(['alamat_lokasi_acara','link_google_maps']));
        Schema::table('pengajuan_sewa', function (Blueprint $table) {
            $table->dropForeign(['id_pelanggan']); $table->dropColumn('id_pelanggan');
        });
    }
};
