<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pengajuan_sewa', function (Blueprint $table) {
            $table->bigIncrements('id_pengajuan');
            $table->string('kode', 32)->unique();
            $table->string('token_hash', 64)->unique();
            $table->string('nama_pelanggan', 100);
            $table->string('no_hp', 30);
            $table->string('jenis_acara', 50);
            $table->string('lokasi_acara', 180)->nullable();
            $table->date('tanggal_acara');
            $table->string('rekomendasi_paket', 30);
            $table->unsignedInteger('estimasi_min')->nullable();
            $table->unsignedInteger('estimasi_max')->nullable();
            $table->longText('kebutuhan_json');
            $table->enum('status', ['Menunggu', 'Revisi', 'Disetujui', 'Batal'])->default('Menunggu');
            $table->text('catatan_admin')->nullable();
            $table->unsignedInteger('harga_final')->nullable();
            $table->unsignedInteger('biaya_genset')->default(0);
            $table->integer('id_transaksi')->nullable()->unique();
            $table->unsignedInteger('diproses_oleh')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
            $table->foreign('id_transaksi')->references('id_transaksi')->on('transaksi')->nullOnDelete();
        });
        Schema::create('pengajuan_detail', function (Blueprint $table) {
            $table->bigIncrements('id_pengajuan_detail');
            $table->unsignedBigInteger('id_pengajuan');
            $table->integer('id_barang')->nullable();
            $table->string('nama_barang', 150);
            $table->string('peran', 100)->nullable();
            $table->unsignedInteger('qty');
            $table->unsignedInteger('jumlah_vendor')->default(0);
            $table->enum('tipe_item', ['Barang', 'External'])->default('Barang');
            $table->string('sumber_external', 150)->nullable();
            $table->timestamps();
            $table->foreign('id_pengajuan')->references('id_pengajuan')->on('pengajuan_sewa')->cascadeOnDelete();
            $table->foreign('id_barang')->references('id_barang')->on('barang')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_detail');
        Schema::dropIfExists('pengajuan_sewa');
    }
};
