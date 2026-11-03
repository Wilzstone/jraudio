<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('detail_transaksi', function (Blueprint $table) {
            $table->unsignedBigInteger('harga_mitra')->nullable()->after('harga_satuan');
        });
        Schema::create('notifikasi_pembayaran', function (Blueprint $table) {
            $table->bigIncrements('id_notifikasi');
            $table->integer('id_pembayaran')->unique();
            $table->integer('id_transaksi');
            $table->enum('jenis', ['DP','Pelunasan','Denda']);
            $table->longText('pesan');
            $table->enum('status', ['Disiapkan','Ditandai Terkirim'])->default('Disiapkan');
            $table->timestamp('ditandai_terkirim_at')->nullable();
            $table->unsignedInteger('ditandai_oleh')->nullable();
            $table->timestamps();
            $table->foreign('id_pembayaran')->references('id_pembayaran')->on('pembayaran')->cascadeOnDelete();
            $table->foreign('id_transaksi')->references('id_transaksi')->on('transaksi')->cascadeOnDelete();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('notifikasi_pembayaran');
        Schema::table('detail_transaksi', fn (Blueprint $table) => $table->dropColumn('harga_mitra'));
    }
};
