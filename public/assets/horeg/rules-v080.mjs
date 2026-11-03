/* JR Audio Horeg Garage v0.7 — angka dan zona ilustratif untuk GAME, bukan perhitungan keselamatan operasional. */
export const CREW = ['Adit','Dika','Kepin','Galen','Andra','Nio'];
export const VEHICLES = {
  pickup: {name:'Pickup',sub:'Rear sound wall',width:2.12,length:3.20,payload:1350,maxHeight:3.65,maxSideOffset:.53,maxLongOffset:1.28,wheelbase:3.4,rigHeight:3.30,rigCols:4,rigRows:4,ampMax:5000,genMax:'gen5'},
  truck: {name:'Truk',sub:'Dinding sound tinggi',width:2.48,length:4.80,payload:3300,maxHeight:4.70,maxSideOffset:.70,maxLongOffset:1.98,wheelbase:4.8,rigHeight:4.30,rigCols:5,rigRows:5,ampMax:12000,genMax:'gen12'},
  towing: {name:'Towing',sub:'Panggung bergerak',width:2.55,length:6.20,payload:6100,maxHeight:5.10,maxSideOffset:.85,maxLongOffset:2.85,wheelbase:5.9,rigHeight:4.70,rigCols:6,rigRows:6,ampMax:20000,genMax:'gen20'}
};
export const PARTS = {
  sub18: {label:'Subwoofer 18"',category:'sound',short:'SUB18',w:.77,d:.68,h:.73,mass:65,audioW:850,stack:true,maxTop:125,emoji:'🔊',hint:'Dinding bass, menghadap belakang'},
  sub21: {label:'Subwoofer 21"',category:'sound',short:'SUB21',w:.87,d:.78,h:.86,mass:95,audioW:1300,stack:true,maxTop:160,emoji:'🔊',hint:'Bass besar di ujung belakang'},
  mid15: {label:'Midrange 15"',category:'sound',short:'MID',w:.63,d:.56,h:.58,mass:36,audioW:420,stack:true,maxTop:65,emoji:'🔉',hint:'Susun di atas sub, muka belakang'},
  hornmini: {label:'Horn Mini (stack 3–7)',category:'sound',short:'HM',w:.48,d:.35,h:.235,mass:9,audioW:45,stack:true,maxTop:65,emoji:'📣',hint:'Horn kecil; pilih kolom untuk menumpuk hingga 7 lapis'},
  horn: {label:'Horn / Tweeter',category:'sound',short:'HORN',w:.58,d:.48,h:.34,mass:17,audioW:100,stack:true,maxTop:0,emoji:'📣',hint:'Kumpulan horn di sisi belakang'},
  line: {label:'Line Array Mini',category:'sound',short:'ARRAY',w:.68,d:.51,h:.40,mass:32,audioW:380,stack:true,maxTop:65,emoji:'🔈',hint:'Mid-high bagian belakang'},
  amp: {label:'Rack Power Amplifier',category:'control',short:'AMP',w:.60,d:.56,h:.83,mass:75,drawW:0,ampW:20000,emoji:'⚡',hint:'Zona operator / rack audio'},
  dsp: {label:'DSP / Crossover',category:'control',short:'DSP',w:.44,d:.38,h:.32,mass:11,drawW:40,emoji:'🎛️',hint:'Zona operator / rack audio'},
  mixer: {label:'Mixer 16 Channel',category:'control',short:'MIX',w:.62,d:.47,h:.24,mass:18,drawW:90,emoji:'🎚️',hint:'Meja mixing di zona operator'},
  gen5: {label:'Genset 5 kVA (game)',category:'genset',short:'GEN5',w:.78,d:.65,h:.70,mass:175,genW:5000,emoji:'🔌',hint:'Otomatis depan-tengah bak'},
  gen12: {label:'Genset 12 kVA (game)',category:'genset',short:'GEN12',w:1.06,d:.84,h:.92,mass:370,genW:12000,emoji:'🔌',hint:'Depan-tengah, lebih berat'},
  gen20: {label:'Genset 20 kVA (game)',category:'genset',short:'GEN20',w:1.22,d:1.03,h:1.08,mass:610,genW:20000,emoji:'🔌',hint:'Khusus towing • di depan-tengah'},
  rig: {label:'Rigging belakang',category:'rig',short:'RIG',w:0,d:0,h:2.22,mass:86,rig:true,emoji:'▤',hint:'Rangka gantung di belakang sound (bukan di atas bak)' },
  led: {label:'LED Bar RGB',category:'light',short:'LED',w:.75,d:.22,h:.22,mass:10,drawW:65,requiresRig:true,emoji:'🌈',hint:'Pilih sel horizontal × vertikal pada rigging'},
  strobe: {label:'Lampu Strobo',category:'light',short:'STROBE',w:.46,d:.38,h:.39,mass:12,drawW:120,requiresRig:true,emoji:'✨',hint:'Custom posisi pada rigging (kolom × baris)'},
  moving: {label:'Moving Head',category:'light',short:'MOVE',w:.43,d:.38,h:.52,mass:22,drawW:290,requiresRig:true,emoji:'💡',hint:'Custom posisi pada truss horizontal/vertikal'},
  spot: {label:'Lampu Sorot',category:'light',short:'SPOT',w:.44,d:.34,h:.39,mass:18,drawW:210,requiresRig:true,emoji:'🔦',hint:'Custom posisi, termasuk tiang rigging vertikal'},
  wall: {label:'Video Wall 16:9',category:'video',short:'LED WALL',w:1.80,d:.16,h:1.0125,mass:125,drawW:360,requiresRig:true,emoji:'📺',hint:'Video wall menggantung di belakang sound'}
};
const EPS=1e-5;
const makeId=()=>`part_${Date.now()}_${Math.random().toString(36).slice(2,9)}`;
export const round=(n,dec=2)=>Math.round(n*10**dec)/10**dec;
export const zoneOf=(key)=>PARTS[key]?.category==='sound'?'sound':PARTS[key]?.category==='genset'?'genset':PARTS[key]?.rig||PARTS[key]?.requiresRig?'rig':'operator';
export function zoneBounds(vehicleId,zone){ const v=VEHICLES[vehicleId],L=v.length; if(zone==='sound')return [-L/2,-L/6]; if(zone==='operator')return [-L/6,L/2]; if(zone==='genset')return [L/6,L/2]; return [-L/2,-L/6]; }
export function footprint(it){const p=PARTS[it.key];return it.rot%2?[p.d,p.w]:[p.w,p.d];}
export function overlapsXZ(a,b){const[aw,ad]=footprint(a),[bw,bd]=footprint(b);return Math.abs(a.x-b.x)<(aw+bw)/2-EPS&&Math.abs(a.z-b.z)<(ad+bd)/2-EPS;}
function fullySupported(a,b){const [w,d]=footprint(a),[bw,bd]=footprint(b);return Math.abs(a.x-b.x)+w/2<=bw/2+.015&&Math.abs(a.z-b.z)+d/2<=bd/2+.015;}
export const totalMass=items=>items.reduce((n,i)=>n+(PARTS[i.key]?.mass||0),0);
export function stats(items,vehicleId){
 const v=VEHICLES[vehicleId],mass=totalMass(items),speakerDemand=items.reduce((s,i)=>s+(PARTS[i.key].audioW||0),0);
 const ampCount=items.filter(i=>i.key==='amp').length;
 const ampOutput=ampCount?v.ampMax:0; // Satu rack amplifier kelas MAX sesuai kendaraan.
 const supply=items.reduce((s,i)=>s+(PARTS[i.key].genW||0),0);
 const otherDraw=items.reduce((s,i)=>s+(PARTS[i.key].drawW||0),0);
 const demand=Math.ceil((speakerDemand? speakerDemand/0.90 : 0)+otherDraw);
 const balance=mass?items.reduce((s,i)=>s+i.x*PARTS[i.key].mass,0)/mass:0;
 const balanceLong=mass?items.reduce((s,i)=>s+i.z*PARTS[i.key].mass,0)/mass:0;
 const height=items.reduce((s,i)=>Math.max(s,PARTS[i.key].rig?v.rigHeight: i.y+PARTS[i.key].h),0);
 return {mass,demand,supply,ampOutput,speakerDemand,balance,balanceLong,height,payload:v.payload,maxHeight:v.maxHeight,ampMax:v.ampMax};
}
function zoneCheck(vehicleId,item){
 const v=VEHICLES[vehicleId],p=PARTS[item.key],zone=zoneOf(item.key),[w,d]=footprint(item);
 if(Math.abs(item.x)+w/2>v.width/2+.001||Math.abs(item.z)+d/2>v.length/2+.001)return 'Tidak muat: perangkat melewati batas bak.';
 if(zone==='sound'){
  if(item.rot!==0)return 'Speaker wajib menghadap belakang; arah kabinet terkunci.';
  const aligned=-v.length/2+d/2+.12;
  if(Math.abs(item.z-aligned)>.032)return 'Sound hanya satu baris kedalaman: dinding belakang, bukan dua baris ke depan.';
 }
 if(zone==='operator'){
  const [min,max]=zoneBounds(vehicleId,'operator');if(item.z-d/2<min-.001||item.z+d/2>max+.001)return 'Mixer, DSP, dan rack harus di area tengah/depan operator (bukan dinding speaker belakang).';
  // Koridor kecil untuk figur operator, supaya ruang operator tidak habis tertutup perangkat.
  if(Math.abs(item.x-(-v.width*.30))<(w+.40)/2-EPS && Math.abs(item.z-.08)<(d+.63)/2-EPS)return 'Sisakan ruang operator di tengah-kiri bak.';
 }
 if(zone==='genset'){
  const frontTarget=v.length/2-d/2-.10;
  if(Math.abs(item.x)>.04||Math.abs(item.z-frontTarget)>.06)return 'Genset otomatis berada paling depan dan tepat di tengah bak.';
 }
 return null;
}
function validPlacement(items,item,vehicleId){
 const v=VEHICLES[vehicleId],p=PARTS[item.key];const err=zoneCheck(vehicleId,item);if(err)return {ok:false,error:err};
 const collider=items.filter(t=>!PARTS[t.key].rig&&!PARTS[t.key].requiresRig&&overlapsXZ(t,item));
 if(!collider.length)return {ok:true,y:0,level:1};
 if(!p.stack)return {ok:false,error:'Area sudah terisi. Perangkat operator dan genset tidak bisa ditumpuk.'};
 const candidates=collider.filter(t=>PARTS[t.key].stack&&fullySupported(item,t)).sort((a,b)=>b.y+PARTS[b.key].h-a.y-PARTS[a.key].h);
 for(const parent of candidates){
  const y=round(parent.y+PARTS[parent.key].h,4);
  if(PARTS[parent.key].maxTop<p.mass || parent.level>=(item.key==='hornmini'?10:7))continue;
  const collided=collider.some(t=>t.y<y+p.h-EPS&&t.y+PARTS[t.key].h>y+EPS);
  if(!collided)return {ok:true,y,level:parent.level+1};
 }
 return {ok:false,error:'Tumpukan tidak memiliki penopang penuh, melewati batas tumpukan, atau beban box bawah tidak cukup.'};
}
// Rigging menggantung di belakang ujung bak, terpisah dari bidang muka speaker.
// Semua koordinat z di sini relatif terhadap titik tengah bak.
export const rigZ = vehicleId => -VEHICLES[vehicleId].length/2 - .44; // Menggantung 44 cm di belakang ujung bak
export const rigLimits = vehicleId => ({cols:VEHICLES[vehicleId].rigCols,rows:VEHICLES[vehicleId].rigRows});
export function rigPose(vehicleId, col, row, key){
 const v=VEHICLES[vehicleId],p=PARTS[key],{cols,rows}=rigLimits(vehicleId);
 const w=v.width-.20;
 const x=round(-w/2+w*(col+.5)/cols,4);
 const centerY=round(.26+(v.rigHeight-.34)*(row+.5)/rows,4);
 return {x,z:rigZ(vehicleId),y:round(centerY-p.h/2,4),rot:0,level:1,col,row};
}
export function wallPose(vehicleId){
 const v=VEHICLES[vehicleId],p=PARTS.wall;
 const h=(v.width-.32)*9/16;
 return {x:0,z:rigZ(vehicleId)-.055,y:round(v.rigHeight*.64-h/2,4),rot:0,level:1};
}
function rigCollision(a,b,vehicleId){
 const v=VEHICLES[vehicleId];
 const size=key=>key==='wall'?{w:v.width-.32,h:(v.width-.32)*9/16}:PARTS[key];
 const pa=size(a.key),pb=size(b.key);
 return Math.abs(a.x-b.x)<(pa.w+pb.w)/2+.045 && Math.abs((a.y+pa.h/2)-(b.y+pb.h/2))<(pa.h+pb.h)/2+.045;
}
function canAttachRig(items,vehicleId,key,id,slot){
 const p=PARTS[key],v=VEHICLES[vehicleId];
 if(!items.some(i=>i.key==='rig'))return {ok:false,error:'Pasang rigging gantung di belakang dinding speaker terlebih dahulu.'};
 let pos;
 if(key==='wall'){
  if(items.some(i=>i.key==='wall'))return {ok:false,error:'Video wall hanya satu per rigging.'};
  pos=wallPose(vehicleId);
 } else {
  if(!slot || !Number.isInteger(slot.col) || !Number.isInteger(slot.row))return {ok:false,error:'Pilih posisi lampu pada GRID rigging terlebih dahulu (kolom × baris).'};
  if(slot.col<0 || slot.col>=v.rigCols || slot.row<0 || slot.row>=v.rigRows)return {ok:false,error:`Slot rigging di luar batas ${v.rigCols} × ${v.rigRows}.`};
  pos=rigPose(vehicleId,slot.col,slot.row,key);
 }
 if(pos.y<-.001 || pos.y+(key==='wall'?(v.width-.32)*9/16:p.h)>v.rigHeight+.05 || pos.y+(key==='wall'?(v.width-.32)*9/16:p.h)>v.maxHeight+.001)return {ok:false,error:'Perangkat rigging melewati batas tinggi.'};
 const candidate={id:id??makeId(),key,...pos};
 if(items.some(i=>PARTS[i.key].requiresRig && rigCollision(i,candidate,vehicleId)))return {ok:false,error:'Posisi tersebut bertabrakan dengan lampu / video wall lain. Pilih slot lain.'};
 return {ok:true,item:candidate};
}
export function canAdd(items,vehicleId,key,x=0,z=0,rot=0,id,slot){
 const v=VEHICLES[vehicleId],p=PARTS[key];if(!v||!p)return {ok:false,error:'Kendaraan/perangkat tidak ditemukan.'};
 if(totalMass(items)+p.mass>v.payload+EPS)return {ok:false,error:`Overload! Total melewati batas ${v.payload} kg pada ${v.name} (aturan game).`};
 if(key==='amp' && items.some(i=>i.key==='amp'))return {ok:false,error:'Cukup satu rack amplifier MAX per kendaraan. Kapasitasnya otomatis menyesuaikan kendaraan.'};
 if(p.rig){if(items.some(i=>PARTS[i.key].rig))return {ok:false,error:'Hanya satu rigging di ujung belakang kendaraan.'};if(v.rigHeight>v.maxHeight)return {ok:false,error:'Rigging terlalu tinggi.'};return {ok:true,item:{id:id??makeId(),key,x:0,z:rigZ(vehicleId),rot:0,y:0,level:1}};}
 if(p.requiresRig)return canAttachRig(items,vehicleId,key,id,slot);
 if(p.genW){if(key==='gen20' && vehicleId!=='towing')return {ok:false,error:'Genset 20 kVA hanya tersedia untuk towing (game).'};if(key==='gen12' && vehicleId==='pickup')return {ok:false,error:'Pickup hanya mendukung genset 5 kVA (game).'};if(items.some(i=>PARTS[i.key].genW))return {ok:false,error:'Hanya satu genset di area depan-tengah bak.'};z=v.length/2-p.d/2-.10;x=0;rot=0;}
 const it={id:id??makeId(),key,x:round(x,3),z:round(z,3),rot:((rot%2)+2)%2,y:0,level:1};
 const result=validPlacement(items,it,vehicleId);if(!result.ok)return result;
 it.y=result.y;it.level=result.level;
 if(it.y+p.h>v.maxHeight+EPS)return {ok:false,error:`Tumpukan melewati tinggi ${v.maxHeight.toFixed(2)} m (aturan game).`};
 return {ok:true,item:it};
}
export function autoAdd(items,vehicleId,key,slot){
 const p=PARTS[key],v=VEHICLES[vehicleId];if(!p||!v)return {ok:false,error:'Pilihan tidak ditemukan.'};
 if(p.rig||p.genW)return canAdd(items,vehicleId,key);
 if(p.requiresRig)return canAdd(items,vehicleId,key,0,0,0,undefined,slot);
 const zone=zoneOf(key),[from,to]=zoneBounds(vehicleId,zone),zPositions=[],xPositions=[];
 if(zone==='sound') zPositions.push(round(-v.length/2+p.d/2+.12,3));
 else for(let z=from+p.d/2+.01;z<=to-p.d/2+.001;z+=.10)zPositions.push(round(z,3));
 for(let x=-v.width/2+p.w/2+.01;x<=v.width/2-p.w/2+.001;x+=.10)xPositions.push(round(x,3));
 const pairs=[];for(const z of zPositions)for(const x of xPositions)pairs.push([x,z]);
 // Speaker: bangun dinding dari ujung belakang ke atas; peralatan lain: dekat sisi bak untuk menyisakan ruang operator.
 pairs.sort((a,b)=> zone==='sound' ? a[1]-b[1]||Math.abs(b[0])-Math.abs(a[0]) : Math.abs(b[0])-Math.abs(a[0])||a[1]-b[1]);
 for(let pass=0;pass<2;pass++)for(const [x,z] of pairs)for(const rot of zone==='sound'?[0]:[0,1]){
   const r=canAdd(items,vehicleId,key,x,z,rot);if(r.ok&&(pass===0?r.item.y===0:r.item.y>0))return r;
 }
 return {ok:false,error:`Tidak ada ruang ${zone==='sound'?'pada dinding sound belakang':'pada area operator'} untuk ${p.label}. Coba truk/towing atau atur ulang.`};
}
// Explicit left / center / right anchors. Never silently place speakers elsewhere.
export function placeSoundColumn(items,vehicleId,key,column){
 const p=PARTS[key],v=VEHICLES[vehicleId];
 if(p.category!=='sound')return {ok:false,error:'Pilihan ini hanya untuk speaker.'};
 if(!['left','center','right'].includes(column))return {ok:false,error:'Pilih kolom kiri, tengah, atau kanan.'};
 const x=column==='center'?0:(column==='left'?-1:1)*(v.width/2-p.w/2-.025);
 const z=-v.length/2+p.d/2+.12;
 return canAdd(items,vehicleId,key,x,z,0);
}
export function moveRigItem(items,vehicleId,id,col,row){
 const selected=items.find(i=>i.id===id);
 if(!selected || !PARTS[selected.key]?.requiresRig || selected.key==='wall')return {ok:false,error:'Pilih lampu yang dapat dipindahkan pada rigging.'};
 const others=items.filter(i=>i.id!==id);
 const next=canAdd(others,vehicleId,selected.key,0,0,0,id,{col,row});
 if(!next.ok)return next;
 return {ok:true,items:items.map(i=>i.id===id?next.item:i)};
}
export function moveItem(items,vehicleId,id,dx,dz,rotate=false){
 const chosen=items.find(i=>i.id===id);if(!chosen)return {ok:false,error:'Pilih komponen terlebih dahulu.'};
 const p=PARTS[chosen.key];if(p.rig||p.requiresRig||p.genW)return {ok:false,error:'Untuk lampu gunakan GRID rigging; genset dan rangka punya posisi khusus.'};
 if(p.category==='sound'&&Math.abs(dz)>.001)return {ok:false,error:'Sound dikunci satu baris belakang. Geser hanya ke kiri atau kanan.'};
 if(p.category==='sound'&&rotate)return {ok:false,error:'Speaker menghadap belakang, jadi rotasi dikunci.'};
 const others=items.filter(i=>i.id!==id);
 const next=canAdd(others,vehicleId,chosen.key,chosen.x+dx,chosen.z+dz,rotate?1-chosen.rot:chosen.rot,id);
 if(!next.ok)return next;
 // Semua speaker di atas harus tetap didukung sesudah perpindahan.
 for(const upper of others.filter(i=>i.y>0&&!PARTS[i.key].requiresRig)){
  const supported=[...others.filter(i=>i.id!==upper.id),next.item].some(q=>PARTS[q.key].stack&&fullySupported(upper,q)&&Math.abs(q.y+PARTS[q.key].h-upper.y)<.012);
  if(!supported)return {ok:false,error:'Tidak boleh digeser: speaker di atas akan kehilangan penopang.'};
 }
 return {ok:true,items:items.map(i=>i.id===id?next.item:i)};
}
export function removeItem(items,id){
 if(!items.some(i=>i.id===id))return items;
 const removed=new Set([id]);let changed=true;
 while(changed){changed=false;for(const it of items){if(removed.has(it.id))continue;
  if(PARTS[it.key].requiresRig&&!items.some(q=>PARTS[q.key].rig&&!removed.has(q.id))){removed.add(it.id);changed=true;continue;}
  if(it.y>0&&!PARTS[it.key].requiresRig){const support=items.some(q=>q.id!==it.id&&!removed.has(q.id)&&PARTS[q.key].stack&&fullySupported(it,q)&&Math.abs(q.y+PARTS[q.key].h-it.y)<.012);if(!support){removed.add(it.id);changed=true;}}
 }}return items.filter(it=>!removed.has(it.id));
}
export function inspection(items,vehicleId,driver,operator){
 const v=VEHICLES[vehicleId],s=stats(items,vehicleId),errors=[];
 if(!items.some(i=>PARTS[i.key].audioW))errors.push('Belum ada speaker belakang.');
 if(!items.some(i=>PARTS[i.key].ampW))errors.push('Belum ada rack amplifier.');
 if(!items.some(i=>i.key==='mixer'))errors.push('Belum ada mixer di area operator.');
 if(!items.some(i=>PARTS[i.key].genW))errors.push('Belum ada genset depan-tengah.');
 if(s.supply<s.demand)errors.push(`Daya game kurang: butuh ${s.demand} W, genset menyediakan ${s.supply} W.`);
 if(s.ampOutput<s.speakerDemand)errors.push(`Speaker ${s.speakerDemand} W melebihi amplifier MAX ${s.ampOutput} W untuk ${v.name}. Kurangi speaker atau pilih kendaraan lebih besar.`);
 if(s.mass>v.payload)errors.push('Muatan melebihi kapasitas game.');
 if(s.height>v.maxHeight)errors.push('Ketinggian rakitan melewati batas game.');
 if(Math.abs(s.balance)>v.maxSideOffset)errors.push('Muatan terlalu berat di satu sisi.');
 if(Math.abs(s.balanceLong)>v.maxLongOffset)errors.push('Distribusi depan–belakang terlalu tidak seimbang.');
 if(!CREW.includes(driver))errors.push('Pilih sopir dari daftar.');
 if(!CREW.includes(operator))errors.push('Pilih operator dari daftar.');
 if(driver&&operator&&driver===operator)errors.push('Sopir dan operator harus orang berbeda.');
 if(items.some(i=>PARTS[i.key].requiresRig)&&!items.some(i=>PARTS[i.key].rig))errors.push('Lampu/video wall memerlukan rigging.');
 if(items.some(i=>i.key==='rig'&&Math.abs(i.z-rigZ(vehicleId))>.03))errors.push('Rigging harus menggantung di belakang sound.');
 return {ok:errors.length===0,errors,stats:s};
}
