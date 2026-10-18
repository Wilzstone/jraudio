@include('public.header')
<main class="section" style="max-width:1100px;margin:0 auto;padding-top:36px">
    <div class="section-title"><span class="eyebrow">JR Audio Smart System</span><h2>Hasil Rekomendasi</h2>
        <p>{{ ucfirst($input['jenis_acara']) }} · {{ number_format((int)$input['jumlah_peserta'],0,',','.') }} peserta · {{ ucfirst($input['lokasi']) }} · {{ $input['tanggal_acara'] }}</p>
    </div>
    <div class="smart-result">
        @if(count($hasil['barang']))
            <div class="smart-table-wrap"><table class="smart-table"><thead><tr><th>Barang</th><th>Qty</th><th>Harga/unit</th><th>Subtotal</th><th>Alasan</th></tr></thead><tbody>
                @foreach($hasil['barang'] as $row)
                    <tr>
                        <td><strong>{{ $row['nama_barang'] }}</strong><small>{{ $row['kategori'] }} · stok sekarang {{ $row['stok_saat_ini'] }}</small></td>
                        <td>{{ $row['jumlah'] }}</td><td>Rp {{ number_format($row['harga_satuan'],0,',','.') }}</td>
                        <td>Rp {{ number_format($row['subtotal'],0,',','.') }}</td><td>{{ $row['alasan'] }}</td>
                    </tr>
                @endforeach
            </tbody></table></div>
        @else
            <p>Tidak ada barang yang dapat disarankan dari stok yang tersedia sekarang. Hubungi JR Audio untuk konsultasi.</p>
        @endif
        <div class="smart-totals"><div><strong>Estimasi total</strong><p>Harga sewa satuan (bukan harga paket)</p></div><strong>Rp {{ number_format($hasil['total'],0,',','.') }}</strong></div>
        <p><strong>Anggaran:</strong> Rp {{ number_format($hasil['anggaran'],0,',','.') }} —
            {{ $hasil['sesuai_anggaran'] ? 'Estimasi dalam anggaran' : 'Estimasi melewati anggaran' }}</p>
        <p><strong>Paket yang bisa dikonsultasikan:</strong> {{ $hasil['paket_hint'] }}. Nama paket hanya alternatif diskusi, bukan otomatis daftar yang dihitung di atas.</p>
    </div>
    <div class="smart-notes"><h3>Catatan penting</h3><ul>@foreach($hasil['catatan'] as $note)<li>{{ $note }}</li>@endforeach</ul></div>
    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:22px">
        <a href="{{ route('rekomendasi.index') }}" class="btn-primary">Ubah Detail Acara</a>
        <a target="_blank" rel="noopener" href="https://wa.me/6282331745445?text={{ rawurlencode('Halo JR Audio, saya ingin konsultasi kebutuhan sound system untuk '.$input['jenis_acara'].' dengan '.$input['jumlah_peserta'].' peserta pada '.$input['tanggal_acara'].'. Estimasi dari website Rp '.number_format($hasil['total'],0,',','.').'. Mohon rekomendasi teknis dan ketersediaan.') }}" class="btn-primary">Konsultasi via WhatsApp</a>
    </div>
</main>
@include('public.footer')
