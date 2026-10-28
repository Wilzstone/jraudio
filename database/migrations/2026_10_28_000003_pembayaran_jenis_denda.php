<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Periksa data sebelum menambah pembatas database (jangan diam-diam menghapus record ganda).
        $duplikat = DB::table('pengembalian')->select('id_transaksi')->groupBy('id_transaksi')
            ->havingRaw('COUNT(*) > 1')->first();
        if ($duplikat) throw new \RuntimeException('Ada pengembalian ganda pada transaksi #'.$duplikat->id_transaksi.'. Audit data dahulu sebelum migrasi.');
        DB::statement("ALTER TABLE pembayaran MODIFY jenis_pembayaran ENUM('DP','Pelunasan','Denda') NOT NULL");
        if (!Schema::hasIndex('pengembalian', 'jr_pengembalian_transaksi_unique')) {
            Schema::table('pengembalian', function (Blueprint $table) {
                $table->unique('id_transaksi', 'jr_pengembalian_transaksi_unique');
            });
        }
    }
    public function down(): void
    {
        if (DB::table('pembayaran')->where('jenis_pembayaran', 'Denda')->exists()) {
            throw new \RuntimeException('Rollback dibatalkan: ada pembayaran jenis Denda. Backup data terlebih dahulu.');
        }
        if (Schema::hasIndex('pengembalian','jr_pengembalian_transaksi_unique')) {
            Schema::table('pengembalian', fn(Blueprint $table) => $table->dropUnique('jr_pengembalian_transaksi_unique'));
        }
        DB::statement("ALTER TABLE pembayaran MODIFY jenis_pembayaran ENUM('DP','Pelunasan') NOT NULL");
    }
};
