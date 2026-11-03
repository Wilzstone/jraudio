// Shared deterministic motion and layout math. No assets or network dependencies.
export const DANCES=['Brazil / Passinho','Pargoy','Pikki Pikki','Gento','Slickback / Jubi Slide','Renegade','Wednesday'];
export const damp=(a,b,rate,dt)=>a+(b-a)*(1-Math.exp(-rate*dt));
export const angleDelta=(a,b)=>Math.atan2(Math.sin(b-a),Math.cos(b-a));
export function dancePose(style,beat){
 const q=beat*Math.PI*2,s=Math.sin(q),c=Math.cos(q),half=Math.sin(q/2),p=Math.max(0,s);
 const r={hipX:0,hipY:0,hipZ:0,bodyX:0,bodyY:0,bodyZ:0,head:0,armLX:0,armRX:0,armLZ:.1,armRZ:-.1,elbowL:.25,elbowR:.25,legL:0,legR:0,kneeL:0,kneeR:0};
 switch(style%7){
 case 0: Object.assign(r,{hipY:.045*p,bodyY:.22*half,bodyZ:.07*s,armLX:.65*s,armRX:-.65*s,elbowL:.8,elbowR:.8,legL:.44*s,legR:-.44*s,kneeL:.55*Math.max(0,-s),kneeR:.55*p});break;
 case 1: Object.assign(r,{hipX:.035*half,hipY:.025*c,bodyZ:.19*half,bodyX:.13*p,armLX:-.65+.4*s,armRX:-.65-.4*s,armLZ:.45,armRZ:-.45,elbowL:.8+.3*c,elbowR:.8-.3*c,legL:.15*s,legR:-.15*s,kneeL:.18*p,kneeR:.18*p});break;
 case 2: Object.assign(r,{hipY:.023*p,bodyZ:.07*half,armLX:-.45,armRX:-.45,armLZ:.3+.13*s,armRZ:-.3+.13*s,elbowL:1.2+.24*s,elbowR:1.2-.24*s,head:.08*half});break;
 case 3: Object.assign(r,{hipY:-.045*p,bodyX:.13*p,bodyY:.22*half,armLX:-.8+.6*s,armRX:-.8-.6*s,armLZ:.4*c,armRZ:.4*c,elbowL:1+.4*c,elbowR:1-.4*c,legL:.34*half,legR:-.34*half,kneeL:.25*p,kneeR:.25*p});break;
 case 4: Object.assign(r,{hipX:.055*half,hipY:.035*Math.abs(s),bodyX:-.1,armLX:-.3*s,armRX:.3*s,elbowL:.65,elbowR:.65,legL:.55*s,legR:-.55*s,kneeL:.85*Math.max(0,-s),kneeR:.85*p});break;
 case 5: Object.assign(r,{hipY:.025*p,bodyY:.22*half,bodyZ:.07*c,armLX:-.9+.4*c,armRX:-.9-.4*c,armLZ:.65*s,armRZ:.65*s,elbowL:.8+.6*half,elbowR:.8-.6*half,legL:.17*s,legR:-.17*s,head:.15*half});break;
 case 6: Object.assign(r,{bodyY:.15*half,bodyZ:.12*s,head:-.25*half,armLX:-.7+.7*half,armRX:-.7-.7*half,armLZ:.9+.18*c,armRZ:-.9+.18*c,elbowL:1.1+.4*s,elbowR:1.1-.4*s,legL:.12*s,legR:-.12*s});break;
 }return r;
}
export function mixedDance(beat,seed){
 const b=beat+seed*2.37,block=Math.floor(b/24),within=b-block*24;
 const prev=((block-1+seed)%7+7)%7,next=((block+seed)%7+7)%7;
 const a=dancePose(prev,b),z=dancePose(next,b),u=Math.min(1,within/2),blend=u*u*(3-2*u);
 for(const k of Object.keys(z))z[k]=a[k]+(z[k]-a[k])*blend;
 return z;
}
export function hardcaseLayout(v,items,parts){
 if(!items.length)return [];
 const occupied=items.filter(i=>!parts[i.key].requiresRig&&!parts[i.key].rig).map(i=>{const p=parts[i.key];return {x:i.x,z:i.z,w:i.rot%2?p.d:p.w,d:i.rot%2?p.w:p.d};});
 // Standing operator envelope includes animated arms, feet and a small access gap.
 occupied.push({x:-v.width*.30,z:.08,w:.94,d:1.04});
 const cases=[],w=v.length<3.5?.35:Math.min(.67,v.width*.29),d=v.length<3.5?.34:.50;
 for(let z=-v.length/2+.97;z<v.length/2-d/2-.12;z+=v.length<3.5?.43:.62){
  for(const x of [v.width/2-w/2-.13,-v.width/2+w/2+.13]){
   const box={x,z,w,d,h:.43};
   if(occupied.some(o=>Math.abs(x-o.x)<(w+o.w)/2+.09&&Math.abs(z-o.z)<(d+o.d)/2+.09))continue;
   cases.push(box);occupied.push(box);if(cases.length>=Math.min(8,Math.floor(v.length*1.2)))return cases;
  }
 }return cases;
}
// Arc-length parameterization keeps metres/second consistent through every turn.
export function buildRoutes(T){
 const P=(x,z)=>new T.Vector3(x,0,z);
 const pts=[P(-85,-52),P(-59,-55),P(-36,-50),P(-29,-31),P(-7,-31),P(4,-49),P(34,-48),P(57,-31),P(60,-6),P(42,6),P(34,24),P(12,26),P(-5,12),P(-23,12),P(-42,26),P(-63,31),P(-85,18),P(-99,-3),P(-100,-29)];
 const village=new T.CatmullRomCurve3(pts,true,'centripetal');village.arcLengthDivisions=4096;village.updateArcLengths();
 const closest=loc=>{let at=0,best=Infinity;for(let i=0;i<=12000;i++){const t=i/12000,d=village.getPointAt(t).distanceToSquared(loc);if(d<best){best=d;at=t;}}return at;};
 const entryAt=closest(P(-23,12)),exitAt=closest(P(34,24));
 const entry=village.getPointAt(entryAt),exit=village.getPointAt(exitAt),stop=P(20,64);
 const inDir=village.getTangentAt(entryAt),outDir=village.getTangentAt(exitAt),stopDir=P(1,0);
 const field=new T.CurvePath();
 // First handle inherits the main-road tangent; the last inherits the exit tangent.
 field.add(new T.CubicBezierCurve3(entry,entry.clone().addScaledVector(inDir,11),P(-27,25),P(-18,38)));
 field.add(new T.CubicBezierCurve3(P(-18,38),P(-9,51),stop.clone().addScaledVector(stopDir,-15),stop));
 const leave=new T.CurvePath();
 leave.add(new T.CubicBezierCurve3(stop,stop.clone().addScaledVector(stopDir,16),P(57,61),P(56,45)));
 leave.add(new T.CubicBezierCurve3(P(56,45),P(55,29),exit.clone().addScaledVector(outDir,-12),exit));
 for(const curve of [field,leave]){curve.arcLengthDivisions=2048;curve.updateArcLengths();}
 return {paths:{village,field,exit:leave},entryAt,exitAt,stop};
}
