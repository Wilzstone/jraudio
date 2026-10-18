<?php
namespace App\Services;

use App\Models\Barang;
use App\Models\Paket;
use Illuminate\Support\Collection;

/**
 * Aturan prototipe dikurasi dari kategori barang JR Audio.
 * BUKAN simulasi akustik, bukan AI generatif, bukan pemeriksaan jadwal reservasi.
 */
class RecommendationService
{
    public function rekomendasikan(array $input): array
    {
        $stok = Barang::query()->where('stok','>',0)->orderBy('harga_sewa')->get();
        return $this->dariKatalog($input, $stok);
    }

    /** Method terpisah agar mesin aturan bisa diuji dengan data barang tanpa database. */
    public function dariKatalog(array $input, Collection $katalog): array
    {
        $peserta=(int)$input['jumlah_peserta'];
        $lokasi=$input['lokasi'];
        $jenis=$input['jenis_acara'];
        $kegiatan=$input['kegiatan'];
        $budget=(int)$input['anggaran'];
        $musik=in_array($kegiatan,['musik_live','musik_rekaman'],true);
        $besar=$peserta>150 || ($lokasi==='outdoor' && $peserta>90);
        $catatan=[];
        $items=[];$terpilih=[];

        $tambahkan=function (string $kategori,int $qty,string $alasan,?string $preferensi=null) use ($katalog,&$items,&$catatan,&$terpilih) {
            $opsi=$katalog->filter(fn($b)=>mb_strtolower((string)$b->kategori)===mb_strtolower($kategori) && !in_array($b->id_barang,$terpilih,true));
            if($preferensi){
                $prioritas=$opsi->first(fn($b)=>stripos($b->nama_barang,$preferensi)!==false);
                if($prioritas) $opsi=$opsi->sortByDesc(fn($b)=>$b->id_barang===$prioritas->id_barang?1:0);
            }
            $b=$opsi->first();
            if (!$b) { $catatan[]='Perangkat kategori '.$kategori.' belum tersedia di katalog saat ini. Perlu konsultasi langsung.'; return; }
            $tersedia=(int)$b->stok;
            $diambil=min($qty,$tersedia);
            if($diambil<$qty) $catatan[]='Stok '.$b->nama_barang.' hanya '.$tersedia.' unit; kebutuhan awal '.$qty.' unit. Mohon konsultasi.';
            if($diambil<=0)return;
            $terpilih[]=$b->id_barang;
            $items[]=[
                'id_barang'=>(int)$b->id_barang,'nama_barang'=>(string)$b->nama_barang,
                'kategori'=>(string)$b->kategori,'jumlah'=>$diambil,'jumlah_dianjurkan'=>$qty,
                'harga_satuan'=>(int)$b->harga_sewa,'subtotal'=>(int)$b->harga_sewa*$diambil,
                'alasan'=>$alasan,'stok_saat_ini'=>$tersedia
            ];
        };

        $speakerQty=$besar?4:2;
        $pilihArray=$besar || $musik || $jenis==='pernikahan';
        $tambahkan('Speaker',$speakerQty,
            'Speaker utama untuk penyampaian suara ke area acara; jumlah awal harus diverifikasi dengan luas area dan spesifikasi daya.',
            $pilihArray?'Array':'Huper');
        $tambahkan('Microphone',$kegiatan==='musik_live'?2:($kegiatan==='pidato'?2:1),
            'Menunjang MC, pembicara atau pengisi acara. Jumlah akhir mengikuti kebutuhan mikrofon yang dipakai bersamaan.');
        $tambahkan('Mixer',1,'Mengatur beberapa sumber audio dan volume selama acara.');
        if($musik||$jenis==='pernikahan'){
            $tambahkan('Subwoofer',$besar?4:2,'Untuk reproduksi frekuensi rendah pada musik; kebutuhan pastinya perlu pengecekan teknisi.');
        }
        if($kegiatan==='pidato'||$kegiatan==='musik_live'){
            $tambahkan('Stand',$kegiatan==='musik_live'?2:1,'Penopang mikrofon untuk pembicara atau penampil.');
        }
        if(($input['butuh_genset']??false)){
            $tambahkan('Genset',1,'Opsi sumber listrik tambahan sesuai permintaan; kapasitasnya wajib diperiksa terhadap daya total dan lokasi.');
        }
        $total=array_sum(array_column($items,'subtotal'));
        if($total>$budget){
            $catatan[]='Estimasi melebihi anggaran Rp '.number_format($budget,0,',','.').'; rekomendasi tidak otomatis mengurangi perangkat penting. Diskusikan penyesuaian dengan JR Audio.';
        }
        $catatan[]='Stok adalah stok saat ini, BELUM jaminan tersedia pada tanggal acara.';
        $catatan[]='Estimasi harga hanya berdasarkan harga sewa satuan pada tabel barang; belum memasukkan transportasi, instalasi, operator, pajak, atau biaya lainnya.';
        $catatan[]='Kecocokan speaker/amplifier, cakupan suara, penempatan, dan kebutuhan daya perlu diperiksa oleh teknisi JR Audio.';
        $paketHint=$jenis==='pernikahan'||$besar?'Paket 5000':'Paket 2500';
        return [
            'barang'=>$items,'total'=>$total,'anggaran'=>$budget,'sesuai_anggaran'=>$total<=$budget,
            'catatan'=>array_values(array_unique($catatan)),
            'paket_hint'=>$paketHint,'aturan'=>'rule-based-v1'
        ];
    }
}
