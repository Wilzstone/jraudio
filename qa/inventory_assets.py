#!/usr/bin/env python3
"""Indeks asset statis/dinamis (read only; kandidat tidak boleh dihapus otomatis)."""
from pathlib import Path
import json
ROOT=Path(__file__).resolve().parents[1]
files=[p for p in ROOT.rglob('*') if p.is_file() and 'qa' not in p.parts and p.suffix in ('.php','.js','.mjs','.css','.blade.php','.html')]
text='\n'.join(p.read_text(errors='replace') for p in files if p.stat().st_size<1800000)
public=ROOT/'public'
rows=[]
for f in sorted(public.rglob('*')):
    if not f.is_file(): continue
    rel=f.relative_to(public).as_posix()
    byname=f.name in text
    bypath=rel in text
    if rel.startswith('assets/img/barang/'):
        status='DIPAKAI DINAMIS / CEK DB'; reason='Nama foto barang disimpan di database; jangan hapus otomatis'
    elif rel.startswith('assets/horeg/crew/') or rel.startswith('assets/horeg/music/'):
        status='DIPAKAI DINAMIS / CEK GAME';reason='Aset crew atau audio dimuat oleh kode game secara dinamis'
    elif bypath or byname:
        status='REFERENSI DITEMUKAN';reason='Nama/path ditemukan di kode sumber'
    else:
        status='KANDIDAT REVIEW MANUAL';reason='Tidak ditemukan referensi literal; periksa browser/network dan database sebelum hapus'
    rows.append({'file':rel,'size_kb':round(f.stat().st_size/1024,1),'status':status,'catatan':reason})
summary={}
for r in rows:summary[r['status']]=summary.get(r['status'],0)+1
report={'catatan':'Tidak ada file yang dihapus. Deteksi tidak membuktikan file tidak terpakai.','files':rows,'summary':summary}
out=ROOT/'qa/inventory_assets_v11.json'
out.write_text(json.dumps(report,ensure_ascii=False,indent=2),encoding='utf-8')
print('Asset:',len(rows),'summary:',summary)
for r in rows:
 if r['status']=='KANDIDAT REVIEW MANUAL':print('KANDIDAT',r['file'])
