#!/usr/bin/env python3
"""Audit statis aman, read-only; jalankan: python qa/static_audit.py --out qa/audit_results.json"""
from pathlib import Path
from collections import Counter
import argparse,json,re,subprocess,sys,os
ROOT=Path(__file__).resolve().parents[1]
parser=argparse.ArgumentParser()
parser.add_argument('--out',default='')
args=parser.parse_args()
checks=[]
def add(name, status, detail, file=''):
    checks.append({'pemeriksaan':name,'status':status,'keterangan':detail,'file':file})

def lint(cmd,files, label):
    passed,failed,skipped=0,[],0
    for f in files:
        try:
            proc=subprocess.run([*cmd,str(f)],stdout=subprocess.PIPE,stderr=subprocess.STDOUT,text=True,timeout=10)
            if proc.returncode==0:passed+=1
            else:failed.append({'file':str(f.relative_to(ROOT)),'output':proc.stdout.strip()[:350]})
        except (OSError,subprocess.TimeoutExpired) as e:
            skipped+=1;failed.append({'file':str(f.relative_to(ROOT)),'output':str(e)[:120]})
    add(label,'LULUS' if not failed else 'PERLU DICEK', f'{passed} lolos, {len(failed)} gagal, {skipped} timeout/alat tidak ada'+ ('; contoh: '+str(failed[:3]) if failed else ''))
    return passed,failed
php=list(ROOT.glob('app/**/*.php'))+list(ROOT.glob('routes/*.php'))+list(ROOT.glob('config/*.php'))+list(ROOT.glob('database/migrations/*.php'))+list(ROOT.glob('tests/**/*.php'))
php_count,php_failed=lint(['php','-l'],php,'Sintaks PHP')
js=[p for p in (ROOT/'public/assets').rglob('*') if p.suffix in {'.js','.mjs'} and '.min.js' not in p.name]
js_count,js_failed=lint(['node','--check'],js,'Sintaks JavaScript (nonminified)')
missing_views=[]
for f in list((ROOT/'app').rglob('*.php'))+list((ROOT/'resources/views').rglob('*.blade.php')):
    s=f.read_text(errors='replace')
    for name in re.findall(r"(?:view\s*\(|@include\s*\()\s*['\"]([a-zA-Z0-9._-]+)['\"]",s):
        if not (ROOT/'resources/views'/Path(*name.split('.'))).with_suffix('.blade.php').exists():
            missing_views.append((str(f.relative_to(ROOT)),name))
add('Referensi view() dan @include', 'LULUS' if not missing_views else 'PERLU DICEK', str(missing_views[:20]) if missing_views else 'Semua referensi view literal ditemukan')
# periksa pemanggilan asset() literal sederhana; hindari URL dinamis
missing_assets=[]
for f in (ROOT/'resources/views').rglob('*.blade.php'):
    s=f.read_text(errors='replace')
    for rel in re.findall(r"asset\(\s*['\"]([^'\"{}]+)['\"]\s*\)",s):
        if not (ROOT/'public'/rel).is_file():missing_assets.append((str(f.relative_to(ROOT)),rel))
add('Referensi asset() literal', 'LULUS' if not missing_assets else 'PERLU DICEK', str(missing_assets[:25]) if missing_assets else 'Semua asset() literal ada')
rt=(ROOT/'routes/web.php').read_text(errors='replace')
controllerdir=ROOT/'app/Http/Controllers'
method_missing=[]
for controller,method in re.findall(r"(\w+Controller)::class,\s*['\"](\w+)['\"]",rt):
    p=controllerdir/(controller+'.php')
    if not p.exists() or not re.search(r'\bfunction\s+'+re.escape(method)+r'\s*\(',p.read_text(errors='replace')):
        method_missing.append(controller+'@'+method)
add('Route ke metode controller', 'LULUS' if not method_missing else 'PERLU DICEK', str(method_missing) if method_missing else 'Semua pasangan controller-metode ditemukan')
for rel in ['resources/views/games/horeg/garage.blade.php','public/assets/horeg/garage-v080.js','public/assets/horeg/rules-v080.mjs','public/assets/horeg/motion-v080.mjs','public/assets/horeg/boot-v080.js','public/assets/horeg/garage-v080.css','public/favicon.ico']:
    add('Integrasi Horeg/favicon','LULUS' if (ROOT/rel).is_file() else 'GAGAL', 'Tersedia' if (ROOT/rel).is_file() else 'Tidak ada',rel)
add('Rahasia dalam ZIP/source','LULUS' if not (ROOT/'.env').exists() else 'KRITIS','Tidak ada .env asli' if not (ROOT/'.env').exists() else '.env tidak boleh dibagikan')
add('composer.lock','PERLU DICEK' if not (ROOT/'composer.lock').exists() else 'LULUS','Tidak ada; versi dependency tidak terkunci' if not (ROOT/'composer.lock').exists() else 'Ada lockfile')
add('vendor/autoload.php','BELUM DIUJI' if not (ROOT/'vendor/autoload.php').exists() else 'ADA','Laravel feature/integration tests hanya bisa dijalankan jika composer install sudah dilakukan')
add('Migration tabel pengajuan v1','PERHATIAN','Database sebelumnya sudah memiliki tabel pengajuan_sewa; jangan jalankan semua migrate sebelum menyelaraskan tabel migrations','database/migrations/2026_10_28_000001_create_jr_pengajuan_tables.php')
# Static indicators, not definitive bugs:
legacy=sorted(p.name for p in (ROOT/'database').glob('*.sql'))
add('SQL impor dan patch historis','INFORMASI', 'File SQL lama tidak dieksekusi otomatis: '+', '.join(legacy))
add('Route game publik','LULUS' if "Route::view('/game/horeg'" in rt else 'GAGAL','/game/horeg terdaftar tanpa middleware auth')
add('Logo tab non-globe','LULUS' if (ROOT/'public/favicon.ico').is_file() and 'logo-jraudio.png' in (ROOT/'resources/views/public/header.blade.php').read_text() else 'GAGAL','Logo JR Audio asli dipakai di header; favicon JR dipasang untuk tab browser')
summary=Counter(x['status'] for x in checks)
report={'jenis':'AUDIT STATIS - bukan hasil uji fungsi aplikasi', 'total_php':len(php),'total_js_nonmin':len(js),'php_lolos':php_count,'js_lolos':js_count, 'pemeriksaan':checks,'summary':dict(summary),'php_gagal':php_failed,'js_gagal':js_failed,'missing_views':missing_views,'missing_assets':missing_assets,'missing_methods':method_missing}
out=json.dumps(report,ensure_ascii=False,indent=2)
if args.out:
    file=Path(args.out); file.parent.mkdir(parents=True,exist_ok=True); file.write_text(out,encoding='utf-8')
print(f'Audit statis: {len(php)} PHP, {len(js)} JS; '+', '.join(f'{k}={v}' for k,v in summary.items()))
for item in checks:
    if item['status'] not in ('LULUS','ADA','INFORMASI'):
        print(' -',item['status'],item['pemeriksaan'],item['keterangan'][:220])
sys.exit(1 if php_failed or js_failed or missing_views or missing_assets or method_missing else 0)
