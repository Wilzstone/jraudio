<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('mitra')) {
            Schema::create('mitra', function (Blueprint $t) {
                $t->bigIncrements('id_mitra');
                $t->string('nama_mitra', 150);
                $t->string('nama_kontak', 120)->nullable();
                $t->string('no_hp', 30)->nullable();
                $t->text('alamat')->nullable();
                $t->text('catatan')->nullable();
                $t->enum('status', ['Aktif','Nonaktif'])->default('Aktif');
                $t->timestamps();
            });
        }
        if (!Schema::hasTable('barang_mitra')) {
            Schema::create('barang_mitra', function (Blueprint $t) {
                $t->bigIncrements('id_barang_mitra');
                $t->unsignedBigInteger('id_mitra');
                $t->string('nama_barang', 160);
                $t->string('satuan', 30)->default('unit');
                $t->unsignedBigInteger('harga_mitra')->default(0);
                $t->unsignedBigInteger('harga_pelanggan')->default(0);
                $t->enum('status', ['Aktif','Nonaktif'])->default('Aktif');
                $t->text('catatan')->nullable();
                $t->timestamps();
                $t->foreign('id_mitra')->references('id_mitra')->on('mitra')->restrictOnDelete();
            });
        }
        if (!Schema::hasColumn('pengajuan_detail','id_barang_mitra')) {
            Schema::table('pengajuan_detail', function (Blueprint $t) {
                $t->unsignedBigInteger('id_barang_mitra')->nullable()->after('id_barang');
                $t->foreign('id_barang_mitra')->references('id_barang_mitra')->on('barang_mitra')->nullOnDelete();
            });
        }
        if (!Schema::hasColumn('detail_transaksi','id_barang_mitra')) {
            Schema::table('detail_transaksi', function (Blueprint $t) {
                $t->unsignedBigInteger('id_barang_mitra')->nullable()->after('id_barang');
                $t->foreign('id_barang_mitra')->references('id_barang_mitra')->on('barang_mitra')->nullOnDelete();
            });
        }
        if (!Schema::hasColumn('pengajuan_detail','termasuk_paket')) Schema::table('pengajuan_detail',fn(Blueprint $t)=>$t->boolean('termasuk_paket')->default(false));
        if (!Schema::hasColumn('detail_transaksi','termasuk_paket')) Schema::table('detail_transaksi',fn(Blueprint $t)=>$t->boolean('termasuk_paket')->default(false));
        if (!Schema::hasColumn('detail_transaksi','dikembalikan_ke_mitra_at')) {
            Schema::table('detail_transaksi', function (Blueprint $t) {
                $t->timestamp('dikembalikan_ke_mitra_at')->nullable();
            });
        }
    }
    public function down(): void {
        if (Schema::hasColumn('detail_transaksi','termasuk_paket')) Schema::table('detail_transaksi',fn(Blueprint $t)=>$t->dropColumn('termasuk_paket'));
        if (Schema::hasColumn('pengajuan_detail','termasuk_paket')) Schema::table('pengajuan_detail',fn(Blueprint $t)=>$t->dropColumn('termasuk_paket'));
        if (Schema::hasColumn('detail_transaksi','dikembalikan_ke_mitra_at')) Schema::table('detail_transaksi',fn(Blueprint $t)=>$t->dropColumn('dikembalikan_ke_mitra_at'));
        if (Schema::hasColumn('detail_transaksi','id_barang_mitra')) Schema::table('detail_transaksi',function(Blueprint $t){$t->dropForeign(['id_barang_mitra']);$t->dropColumn('id_barang_mitra');});
        if (Schema::hasColumn('pengajuan_detail','id_barang_mitra')) Schema::table('pengajuan_detail',function(Blueprint $t){$t->dropForeign(['id_barang_mitra']);$t->dropColumn('id_barang_mitra');});
        Schema::dropIfExists('barang_mitra');Schema::dropIfExists('mitra');
    }
};
