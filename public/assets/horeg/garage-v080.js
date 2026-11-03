import * as THREE from 'three';
import { mergeGeometries } from 'three/addons/utils/BufferGeometryUtils.js';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import {VEHICLES,PARTS,CREW,zoneOf,stats,inspection,autoAdd,canAdd,moveItem,moveRigItem,removeItem,placeSoundColumn,rigZ,rigLimits,rigPose,wallPose} from './rules-v080.mjs';

import {DANCES,damp,angleDelta,mixedDance,hardcaseLayout,buildRoutes} from './motion-v080.mjs';

// JR AUDIO · HOREG GARAGE + PARADE v0.8.0. Seluruh berat/dimensi adalah angka game ilustratif.
const $=id=>document.getElementById(id);
const state={vehicle:'pickup',color:'#e34d52',tab:'sound',stage:1,rigSlot:null,items:[],selected:null,driver:'',operator:'',night:true,history:[],strapped:false,paradeRunning:false,paradeSpeed:8,paradeCam:'follow',paradeT:0,routeChoice:'village',fieldStopped:false,soundColumn:'center',pendingLightKey:null,beamMode:'rainbow'};
const fmt=(v,dec=0)=>Number(v).toLocaleString('id-ID',{maximumFractionDigits:dec,minimumFractionDigits:dec});
let toastTimer;function toast(message,bad=false){const el=$('toast');el.textContent=message;el.classList.toggle('bad',bad);el.classList.add('show');clearTimeout(toastTimer);toastTimer=setTimeout(()=>el.classList.remove('show'),4200);$('status-line').textContent=message;}
const saveHistory=()=>{state.history.push(JSON.stringify(state.items));if(state.history.length>25)state.history.shift();};
const scene=new THREE.Scene();scene.background=new THREE.Color('#121b29');scene.fog=new THREE.FogExp2('#121b29',.012);
const host=$('garage-canvas');const camera=new THREE.PerspectiveCamera(39,1,.1,650);
let renderer;
try{renderer=new THREE.WebGLRenderer({antialias:true,powerPreference:'high-performance'});}catch(err){$('garage-loading').textContent='Tidak bisa mengaktifkan WebGL. Cek driver dan hardware acceleration.';throw err;}
renderer.setPixelRatio(Math.min(devicePixelRatio||1,2));renderer.outputColorSpace=THREE.SRGBColorSpace;renderer.toneMapping=THREE.ACESFilmicToneMapping;renderer.toneMappingExposure=1.55;renderer.shadowMap.enabled=true;renderer.shadowMap.type=THREE.PCFSoftShadowMap;host.appendChild(renderer.domElement);
const controls=new OrbitControls(camera,renderer.domElement);controls.enableDamping=true;controls.minDistance=4.0;controls.maxDistance=22;controls.maxPolarAngle=Math.PI/2.02;controls.enablePan=false;controls.autoRotateSpeed=1.2;
renderer.domElement.addEventListener('pointerdown',e=>activePointers.add(e.pointerId),true);
renderer.domElement.addEventListener('pointerup',e=>activePointers.delete(e.pointerId),true);
renderer.domElement.addEventListener('pointercancel',e=>activePointers.delete(e.pointerId),true);
window.addEventListener('blur',()=>{clearActivePointers();},true);
const hemi=new THREE.HemisphereLight('#b5d9ff','#35404b',2.0);scene.add(hemi);
const sun=new THREE.DirectionalLight('#fff5e7',3.9);sun.position.set(-4,11,7);sun.castShadow=true;sun.shadow.mapSize.set(1024,1024);sun.shadow.camera.left=-16;sun.shadow.camera.right=16;sun.shadow.camera.top=16;sun.shadow.camera.bottom=-16;scene.add(sun);
const glowBlue=new THREE.PointLight('#4d93f6',135,24);glowBlue.position.set(7,7,-4);scene.add(glowBlue);
const glowWarm=new THREE.PointLight('#ffb65a',105,20);glowWarm.position.set(-7,5,2);scene.add(glowWarm);
const mat=(c,metal=.12,rough=.65,opts={})=>new THREE.MeshStandardMaterial({color:c,metalness:metal,roughness:rough,...opts});
const dark=mat('#111826',.3,.67),steel=mat('#9dabbc',.8,.3),rubber=mat('#111317',.05,.98),glass=mat('#8ec1d9',.12,.17,{transparent:true,opacity:.19,depthWrite:false,side:THREE.DoubleSide}),bedMat=mat('#394350',.56,.66),chrome=mat('#bdc7d0',.85,.22);
function cube(parent,size,pos,material){const mesh=new THREE.Mesh(new THREE.BoxGeometry(...size),material);mesh.position.set(...pos);mesh.castShadow=true;mesh.receiveShadow=true;parent.add(mesh);return mesh;}
function cyl(parent,radius,depth,segments,pos,material){const mesh=new THREE.Mesh(new THREE.CylinderGeometry(radius,radius,depth,segments),material);mesh.position.set(...pos);mesh.castShadow=true;parent.add(mesh);return mesh;}
const ground=cube(scene,[200,.18,200],[0,-.16,0],mat('#263242',.05,.92));ground.receiveShadow=true;
const grid=new THREE.GridHelper(40,40,'#51677c','#334356');grid.position.y=-.061;scene.add(grid);
const garageWalls=[];
for(const x of [-13,13]){const wall=cube(scene,[.3,9,30],[x,4.5,0],mat('#1c2838'));wall.userData.axis='x';garageWalls.push(wall);}
const backWall=cube(scene,[30,9,.3],[0,4.5,-12],mat('#1b2637'));backWall.userData.axis='z';garageWalls.push(backWall);
let garageSign;
for(let x=-10;x<=10;x+=5)cube(scene,[3.3,.10,.18],[x,7.5,-11.75],new THREE.MeshBasicMaterial({color:'#3a86ad'}));
const pad=cube(scene,[8,.12,12],[0,-.02,-1],mat('#1b2736',.5,.49));pad.receiveShadow=true;
for(const x of [-3.95,3.95])cube(scene,[.08,.05,12],[x,.04,-1],new THREE.MeshBasicMaterial({color:'#df514f'}));
function makeSign(){const can=document.createElement('canvas');can.width=1024;can.height=256;const cx=can.getContext('2d');cx.fillStyle='#162334';cx.fillRect(0,0,1024,256);cx.strokeStyle='#e95559';cx.lineWidth=10;cx.strokeRect(8,8,1008,240);cx.fillStyle='#fff';cx.textAlign='center';cx.font='bold 102px Arial';cx.fillText('JR AUDIO',512,132);cx.font='bold 31px Arial';cx.fillStyle='#f2666b';cx.fillText('HOREG SOUND & LIGHTING LAB',512,207);const t=new THREE.CanvasTexture(can);t.colorSpace=THREE.SRGBColorSpace;const m=new THREE.Mesh(new THREE.PlaneGeometry(5.8,1.45),new THREE.MeshBasicMaterial({map:t}));m.position.set(0,5,-11.72);scene.add(m);garageSign=m;}
makeSign();
// Everything static in the workshop is grouped, so the game can switch to a road scene without a page refresh.
const garageEnvironment=new THREE.Group();
for(const obj of [...scene.children])if(obj.isMesh||obj.isGridHelper){scene.remove(obj);garageEnvironment.add(obj);}
scene.add(garageEnvironment);
const mobileRoot=new THREE.Group();scene.add(mobileRoot);
let vehicleRoot=new THREE.Group();mobileRoot.add(vehicleRoot);
let partRoot=new THREE.Group();mobileRoot.add(partRoot);
let paintMeshes=[],selectOutline,wheelPivots=[],steeringPivots=[];
let currentSpeed=0,smoothedSteer=0;
let frontDisplayCanvas=null,frontDisplayTexture=null,frontEyeCanvas=null,frontEyeTexture=null;
let frontDisplays=[],eyeDisplays=[],headlightMats=[],tailLightMats=[],brakeLightMats=[],hazardLightMats=[],frontSpotLights=[];
let lastVehicleWorldPos=new THREE.Vector3();
const lastFreeTruckPos=new THREE.Vector3();
let lastFreeTruckYaw=0;
const activePointers=new Set();
function clearActivePointers(){for(const id of activePointers){try{if(renderer.domElement.hasPointerCapture(id))renderer.domElement.releasePointerCapture(id);}catch(e){/* browser may have released it already */}}activePointers.clear();}


function makeCanvasTexture(w,h){const c=document.createElement('canvas');c.width=w;c.height=h;const t=new THREE.CanvasTexture(c);t.colorSpace=THREE.SRGBColorSpace;return {canvas:c,ctx:c.getContext('2d'),texture:t};}
// Mata dekoratif di kaca depan: sepasang mata besar yang melirik kiri/kanan dan berkedip.
function drawEyes(ctx,w,h,time){
 ctx.clearRect(0,0,w,h);
 const glance=Math.sin(time*.72)*.30+Math.sin(time*.23)*.11;
 const phase=time%7.7;
 const blink=Math.max(0,1-Math.abs(phase-7.52)/.18);
 const eyeWidth=w*.36, eyeHeight=h*.40*(1-.86*blink), cy=h*.54;
 for(const side of [-1,1]){
  const cx=w*.5+side*w*.247;
  ctx.save();ctx.translate(cx,cy);
  // Kelopak yang miring, kiri dan kanan simetris.
  ctx.beginPath();
  ctx.moveTo(-eyeWidth*.52,eyeHeight*.12);
  ctx.quadraticCurveTo(-eyeWidth*.28,-eyeHeight*.64,eyeWidth*.51,-eyeHeight*.31);
  ctx.quadraticCurveTo(eyeWidth*.43,eyeHeight*.53,-eyeWidth*.52,eyeHeight*.12);
  ctx.closePath();ctx.fillStyle='#fa2639';ctx.fill();
  ctx.strokeStyle='#ff8a92';ctx.lineWidth=12;ctx.stroke();
  ctx.save();ctx.clip();
  const px=glance*eyeWidth*.39;
  ctx.fillStyle='#fff7ef';ctx.beginPath();ctx.ellipse(px,-eyeHeight*.03,eyeWidth*.14,eyeHeight*.48,0,0,Math.PI*2);ctx.fill();
  ctx.fillStyle='#e7464d';ctx.beginPath();ctx.ellipse(px+eyeWidth*.025,-eyeHeight*.03,eyeWidth*.062,eyeHeight*.30,0,0,Math.PI*2);ctx.fill();
  ctx.fillStyle='#fff';ctx.beginPath();ctx.arc(px-eyeWidth*.015,-eyeHeight*.19,eyeWidth*.028,0,Math.PI*2);ctx.fill();
  ctx.restore();ctx.restore();
 }
}
function drawFrontBanner(ctx,w,h,time=0){ctx.clearRect(0,0,w,h);ctx.fillStyle='rgba(2,6,14,.82)';ctx.fillRect(0,0,w,h);ctx.strokeStyle='rgba(255,110,118,.55)';ctx.lineWidth=4;ctx.strokeRect(3,3,w-6,h-6);ctx.font='bold 26px Arial';ctx.textAlign='center';ctx.fillStyle='#ffef99';ctx.fillText('JR AUDIO',w/2,26);ctx.font='bold 18px Arial';const slogan=' • HOREG SOUND LIGHTING • RUNNING TEXT ATAU INFO PEMESANAN BISA LANGSUNG DM • ';const textW=ctx.measureText(slogan).width;const offset=-(time*90%textW);for(let x=offset;x<w+textW;x+=textW){ctx.fillStyle='white';ctx.fillText(slogan,x+w/2,50);} }

function disposeGroup(group){
 const shared=new Set([dark,steel,rubber,glass,bedMat,chrome]);
 const keepTextures=new Set([frontDisplayTexture,frontEyeTexture,lampGlowTexture,...Object.values(faceTextures),...textureCache.values()]);
 const materials=new Set(),textures=new Set();
 group.traverse(obj=>{if(obj.geometry)obj.geometry.dispose();for(const m of Array.isArray(obj.material)?obj.material:[obj.material])if(m&&!shared.has(m)){materials.add(m);if(m.map&&!keepTextures.has(m.map))textures.add(m.map);}});
 materials.forEach(m=>m.dispose());textures.forEach(t=>t.dispose());group.clear();
}
function buildVehicle(){
 disposeGroup(vehicleRoot);paintMeshes=[];frontDisplays=[];eyeDisplays=[];headlightMats=[];tailLightMats=[];brakeLightMats=[];hazardLightMats=[];frontSpotLights=[];const v=VEHICLES[state.vehicle];const l=v.length,w=v.width;
 const paint=mat(state.color,.41,.33),cabRoof=mat(state.color,.36,.44);
 const isPickup=state.vehicle==='pickup',isTowing=state.vehicle==='towing';
 // Bagian depan kendaraan +Z. Bak belakang berawal Z=-length dan berakhir Z=0.
 cube(vehicleRoot,[w+.04,.34,l+2.2],[0,.90,(1.15-l)/2],dark);
 const cabDepth=isPickup?1.56:isTowing?1.75:1.84;
 const cabY=isPickup?1.94:2.07,cabHeight=isPickup?1.10:1.37;
 // Kabin berongga dengan kaca bening, bukan solid cube yang menutupi figur supir.
 const low=cube(vehicleRoot,[w*.94,.48,cabDepth],[0,cabY-cabHeight/2+.25,.85],paint);paintMeshes.push(low);
 const roof=cube(vehicleRoot,[w*.96,.12,cabDepth+.12],[0,cabY+cabHeight/2+.045,.85],cabRoof);paintMeshes.push(roof);
 const frontZ=.85+cabDepth/2,backZ=.85-cabDepth/2;
 for(const sx of [-1,1]){
  for(const zz of [frontZ-.07,backZ+.08]){const pillar=cube(vehicleRoot,[.09,cabHeight-.45,.11],[sx*w*.445,cabY+.12,zz],paint);paintMeshes.push(pillar);}
  const lowerDoor=cube(vehicleRoot,[.06,.48,cabDepth*.78],[sx*w*.464,cabY-cabHeight*.25,.85],paint);paintMeshes.push(lowerDoor);
  cube(vehicleRoot,[.025,.54,cabDepth*.74],[sx*w*.477,cabY+.10,.85],glass);
  cube(vehicleRoot,[.17,.12,.24],[sx*w*.56,cabY+.10,frontZ-.20],dark);
 }
 const windshield=cube(vehicleRoot,[w*.84,.57,.018],[0,cabY+.12,frontZ-.024],glass);
 if(!frontDisplayTexture){const pack=makeCanvasTexture(1024,96);frontDisplayCanvas=pack.canvas;frontDisplayTexture=pack.texture;}
 const frontPanel=new THREE.Mesh(new THREE.PlaneGeometry(w*.78,.22),new THREE.MeshBasicMaterial({map:frontDisplayTexture,transparent:true,side:THREE.DoubleSide}));frontPanel.position.set(0,cabY+.02,frontZ-.01);vehicleRoot.add(frontPanel);frontDisplays.push(frontPanel);
 if(!frontEyeTexture){const pack2=makeCanvasTexture(1024,320);frontEyeCanvas=pack2.canvas;frontEyeTexture=pack2.texture;}
 const eyes=new THREE.Mesh(new THREE.PlaneGeometry(w*.88,.49),new THREE.MeshBasicMaterial({map:frontEyeTexture,transparent:true,side:THREE.DoubleSide}));eyes.position.set(0,cabY+.18,frontZ+.017);vehicleRoot.add(eyes);eyeDisplays.push(eyes);
 cube(vehicleRoot,[w*.86,.48,.04],[0,cabY-.31,backZ],paint);
 const bonnet=cube(vehicleRoot,[w*.93,isPickup?.36:.23,isPickup?1:.42],[0,1.22,isPickup?1.8:1.83],paint);paintMeshes.push(bonnet);
 const headZ=isPickup?2.27:2.12;
 for(const side of [-1,1]){const mFront=new THREE.MeshBasicMaterial({color:'#fff4b8'});const lamp=cube(vehicleRoot,[.32,.18,.06],[side*w*.34,1.30,headZ],mFront);headlightMats.push(mFront);const hazardFront=new THREE.MeshBasicMaterial({color:'#261400'});cube(vehicleRoot,[.10,.10,.04],[side*w*.56,1.03,headZ+.02],hazardFront);hazardLightMats.push(hazardFront);const spot=new THREE.SpotLight('#dfefff',0,32,Math.PI/8,.40,1.35);spot.position.set(side*w*.34,1.32,headZ-.06);spot.target.position.set(side*w*.34,0,headZ+18);vehicleRoot.add(spot);vehicleRoot.add(spot.target);frontSpotLights.push(spot);} 
 cube(vehicleRoot,[w*.61,.25,.08],[0,1.29,headZ],dark);
 cube(vehicleRoot,[w+.07,.14,.2],[0,.87,headZ+.08],chrome);
 for(const side of [-1,1]){const tailM=new THREE.MeshBasicMaterial({color:'#2a0909'});cube(vehicleRoot,[.18,.11,.03],[side*w*.34,1.18,-l-.08],tailM);tailLightMats.push(tailM);const brakeM=new THREE.MeshBasicMaterial({color:'#2f0505'});cube(vehicleRoot,[.24,.12,.03],[side*w*.18,1.18,-l-.08],brakeM);brakeLightMats.push(brakeM);const hazRear=new THREE.MeshBasicMaterial({color:'#261400'});cube(vehicleRoot,[.10,.10,.03],[side*w*.48,1.05,-l-.08],hazRear);hazardLightMats.push(hazRear);} 
 // Bak flatbed: tiga zona memanjang, sisi belakang dinding speaker, depan genset.
 cube(vehicleRoot,[w,.14,l],[0,1.235,-l/2],bedMat);
 const zoneColors=['#db6d5e','#50b9b7','#e4bd67'];
 for(let i=0;i<3;i++){
  const z=-l+(i+.5)*l/3;
  cube(vehicleRoot,[w-.20,.008,l/3-.05],[0,1.315,z],new THREE.MeshBasicMaterial({color:zoneColors[i],transparent:true,opacity:.17,depthWrite:false}));
 }
 const sideHeight=isTowing?.19:isPickup?.36:.38;
 for(const side of [-1,1]){
  const rail=cube(vehicleRoot,[.095,sideHeight,l],[side*(w/2-.045),1.34+sideHeight/2,-l/2],paint);paintMeshes.push(rail);
  cube(vehicleRoot,[.085,.055,l],[side*(w/2-.04),1.35+sideHeight,-l/2],chrome);
 }
 const tail=cube(vehicleRoot,[w,.18,.10],[0,1.34,-l-.02],paint);paintMeshes.push(tail);
 cube(vehicleRoot,[w+.08,.11,.20],[0,.87,-l-.15],chrome);
 if(isTowing){ // Flatbed towing: rangka baja belakang, dek rendah, dan akses loading.
  for(const side of [-1,1]){
   cube(vehicleRoot,[.14,.22,l],[side*(w/2-.13),.99,-l/2],chrome);
   cube(vehicleRoot,[.10,.60,.12],[side*(w/2-.12),.62,-l-.30],steel);
  }
  cube(vehicleRoot,[w*.80,.055,.53],[0,1.11,-l-.37],bedMat);
  for(const x of [-.45,.45])cube(vehicleRoot,[.07,.02,.55],[x,1.152,-l-.38],chrome);
 }
 const wheelZ=isPickup?-1.53:isTowing?-l+.85:-l+.78;
 wheelPivots=[];steeringPivots=[];
 for(const side of [-1,1])for(const z of [1.45,wheelZ,...(isTowing?[wheelZ+1.10]:[])]){
  const steering=new THREE.Group();steering.position.set(side*(w/2+.03),.48,z);vehicleRoot.add(steering);
  if(z===1.45)steeringPivots.push(steering);const wheel=new THREE.Group();steering.add(wheel);wheelPivots.push(wheel);
  const tire=cyl(wheel,.48,.30,24,[0,0,0],rubber);tire.rotation.z=Math.PI/2;
  const rim=cyl(wheel,.25,.32,20,[side*.015,0,0],chrome);rim.rotation.z=Math.PI/2;
  // Visible rotating 5-spoke marks, so the vehicle no longer appears to glide.
  for(let n=0;n<5;n++){const a=n*2*Math.PI/5;
   const spoke=cube(wheel,[.04,.055,.23],[side*.179,Math.cos(a)*.17,Math.sin(a)*.17],mat('#394d5a',.65,.32));
   spoke.rotation.x=a;
  }
 }
 const lineMat=new THREE.MeshBasicMaterial({color:'#9ed3d9',transparent:true,opacity:.16});
 for(let z=-l;z<=0;z+=.32)cube(vehicleRoot,[w-.14,.003,.01],[0,1.325,z],lineMat);
 for(let x=-w/2+.08;x<=w/2-.08;x+=.4)cube(vehicleRoot,[.012,.003,l-.05],[x,1.326,-l/2],lineMat);
 controls.target.set(0,1.55,-Math.min(1.5,l/2));setCamera('perspective');
 $('viewer-title').textContent=`${v.name} · Rear Sound Wall`;
 drawCrew();
}
// Video wall dibuat dari canvas teks (tanpa gambar/logo invoice).
const faceTextures={};
const crewPortrait=(name)=>`/assets/horeg/crew/${name.toLowerCase()}.jpg`;
const crewFace=(name)=>`/assets/horeg/crew/${name.toLowerCase()}-face.jpg`;
function drawCrew(){
 if(window._horegCrewRoot){vehicleRoot.remove(window._horegCrewRoot);disposeGroup(window._horegCrewRoot);}
 const v=VEHICLES[state.vehicle],crew=new THREE.Group();window._horegCrewRoot=crew;vehicleRoot.add(crew);
 const skin=mat('#c9916f'),shirt=mat('#1c658d'),shirt2=mat('#ca703a');
 function person(name,x,z,atY,material,scale=1,seated=false){
  const g=new THREE.Group();g.position.set(x,atY,z);g.scale.setScalar(scale);
  cube(g,[.31,.60,.23],[0,.57,0],material);
  if(seated){
   for(const sx of [-1,1]){cube(g,[.12,.24,.32],[sx*.105,.20,.13],dark);cube(g,[.12,.12,.28],[sx*.105,.12,.38],dark);}
  }else{cube(g,[.11,.49,.13],[-.10,.16,0],dark);cube(g,[.11,.49,.13],[.10,.16,0],dark);}
  cube(g,[.10,.38,.10],[-.23,.59,0],skin);cube(g,[.10,.38,.10],[.23,.59,0],skin);
  const head=new THREE.Mesh(new THREE.SphereGeometry(.166,20,16),skin);head.position.y=1.06;g.add(head);
  const hair=new THREE.Mesh(new THREE.SphereGeometry(.174,18,14,0,Math.PI*2,0,Math.PI*.34),mat('#18191d'));hair.position.y=1.10;g.add(hair);
  if(name){
   const plane=new THREE.Mesh(new THREE.PlaneGeometry(.29,.30),new THREE.MeshBasicMaterial({color:'#ffffff',transparent:true,side:THREE.DoubleSide,depthWrite:false}));
   plane.position.set(0,1.06,.172);g.add(plane);
   if(!faceTextures[name])faceTextures[name]=new THREE.TextureLoader().load(crewFace(name));
   plane.material.map=faceTextures[name];plane.material.needsUpdate=true;
  }
  crew.add(g);
 }
 if(state.driver)person(state.driver,-.39,1.07,1.22,shirt2,.8,true);
 operatorDancer=null;
 if(state.operator){
  const op=articulatedPerson(crew,-v.width*.30,-v.length/2+.08,'#1c658d',.86);op.position.y=1.34;op.rotation.y=Math.PI;operatorDancer=op;
  if(!faceTextures[state.operator])faceTextures[state.operator]=new THREE.TextureLoader().load(crewFace(state.operator));
  faceTextures[state.operator].colorSpace=THREE.SRGBColorSpace;
  const face=new THREE.Mesh(new THREE.PlaneGeometry(.28,.29),new THREE.MeshBasicMaterial({map:faceTextures[state.operator],transparent:true}));face.position.z=.173;op.userData.dancer.head.add(face);
 }
 renderCrewCards();
}
function renderCrewCards(){
 const holder=$('crew-photos');if(!holder)return;
 holder.innerHTML='';
 for(const name of CREW){const div=document.createElement('div');div.className='crew-avatar'+(state.driver===name?' selected-driver':'')+(state.operator===name?' selected-operator':'');
 const image=document.createElement('img');image.src=crewPortrait(name);image.alt='Foto kru '+name;image.loading='lazy';
 const label=document.createElement('span');label.textContent=name;div.append(image,label);holder.appendChild(div);}
}

function labelTexture(text){const c=document.createElement('canvas');c.width=256;c.height=80;const cx=c.getContext('2d');cx.fillStyle='#e7eef7';cx.font='bold 38px Arial';cx.textAlign='center';cx.fillText(text,128,51);const t=new THREE.CanvasTexture(c);t.colorSpace=THREE.SRGBColorSpace;return t;}
const textureCache=new Map();function textBadge(parent,label,y,h){if(!textureCache.has(label))textureCache.set(label,labelTexture(label));const m=new THREE.Mesh(new THREE.PlaneGeometry(Math.max(.28,Math.min(1.0,label.length*.076)),h),new THREE.MeshBasicMaterial({map:textureCache.get(label),transparent:true,side:THREE.DoubleSide}));m.position.set(0,y,.003);m.userData.badge=true;parent.add(m);}
function mkSpeaker(g,p){const m=mat('#181f28',.2,.85);cube(g,[p.w,p.h,p.d],[0,0,0],m);const front=p.d/2+.008;const ring=mat('#626e7a',.7,.34);const cone=mat('#10151c',.10,.7);const count=p.h>.6?2:1;
 for(let n=0;n<count;n++){const radius=Math.min(p.w*.29,p.h/(count*2.5));const y=(n-(count-1)/2)*p.h*.40;const c=cyl(g,radius,.024,32,[0,y,front],ring);c.rotation.x=Math.PI/2;const inner=cyl(g,radius*.72,.03,32,[0,y,front+.025],cone);inner.rotation.x=Math.PI/2;}
 for(const sx of [-1,1])for(const sy of [-1,1])cube(g,[.035,.055,.03],[sx*(p.w/2-.065),sy*(p.h/2-.065),front],steel);
}
let lampGlowTexture=null;
function getLampGlowTexture(){
 if(lampGlowTexture)return lampGlowTexture;
 const c=document.createElement('canvas');c.width=c.height=128;const ctx=c.getContext('2d');
 const grad=ctx.createRadialGradient(64,64,3,64,64,61);
 grad.addColorStop(0,'rgba(255,255,255,.95)');grad.addColorStop(.22,'rgba(255,255,255,.53)');grad.addColorStop(1,'rgba(255,255,255,0)');
 ctx.fillStyle=grad;ctx.fillRect(0,0,128,128);lampGlowTexture=new THREE.CanvasTexture(c);return lampGlowTexture;
}
// v0.8: finite, feathered beams; no exposure mapping that washes RGB to white.
function createBeam(parent,length,radius,y,z){
 const material=new THREE.ShaderMaterial({
  uniforms:{tint:{value:new THREE.Color('#ff0066')},strength:{value:.32}},
  vertexShader:`varying vec2 vUv; void main(){vUv=uv;gl_Position=projectionMatrix*modelViewMatrix*vec4(position,1.0);}`,
  fragmentShader:`uniform vec3 tint;uniform float strength;varying vec2 vUv;
   void main(){float dist=1.0-vUv.y;float fade=1.0-smoothstep(.18,1.0,dist);
    float edge=pow(abs(sin(vUv.x*6.2831853)),.35);
    gl_FragColor=vec4(tint,strength*fade*(.5+.5*edge));
    #include <colorspace_fragment>
   }`,
  transparent:true,depthWrite:false,side:THREE.FrontSide,toneMapped:false,fog:false
 });
 const beam=new THREE.Mesh(new THREE.CylinderGeometry(.045,radius,length,24,16,true),material);
 beam.rotation.x=Math.PI/2;beam.position.set(0,y,z-length/2);beam.userData.isEffect=true;
 beam.castShadow=false;beam.receiveShadow=false;parent.add(beam);return beam;
}
function solidBounds(root){
 root.updateWorldMatrix(true,true);const box=new THREE.Box3();
 root.traverse(o=>{if(!o.isMesh||o.userData.isEffect)return; if(!o.geometry.boundingBox)o.geometry.computeBoundingBox();box.union(o.geometry.boundingBox.clone().applyMatrix4(o.matrixWorld));});
 return box;
}
let caseRoot=null,caseCount=0,lastCaseLayout='',lastFramedLayout='';
function redrawHardcases(){
 const v=VEHICLES[state.vehicle],layout=hardcaseLayout(v,state.items,PARTS),layoutKey=JSON.stringify([state.vehicle,layout]);
 if(caseRoot&&layoutKey===lastCaseLayout)return;lastCaseLayout=layoutKey;
 if(caseRoot){mobileRoot.remove(caseRoot);disposeGroup(caseRoot);}
 caseRoot=new THREE.Group();caseRoot.name='Auto hardcases';mobileRoot.add(caseRoot);
 caseCount=layout.length;
 const shell=mat('#191e24',.18,.88),edge=mat('#a8b4c0',.85,.27),rub=mat('#080c10',.05,.9);
 for(const [index,b] of layout.entries()){
  const g=new THREE.Group();g.position.set(b.x,1.32,b.z-v.length/2);caseRoot.add(g);
  const {w,h,d}=b,bottom=.095;
  cube(g,[w,h,d],[0,bottom+h/2,0],shell);
  // Silver extrusion around the base, lid and all vertical corners.
  for(const y of [bottom,bottom+h*.79,bottom+h]){
   for(const z of [-d/2,d/2])cube(g,[w+.018,.024,.027],[0,y,z],edge);
   for(const x of [-w/2,w/2])cube(g,[.027,.024,d+.018],[x,y,0],edge);
  }
  for(const x of [-w/2,w/2])for(const z of [-d/2,d/2]){
   cube(g,[.025,h,.025],[x,bottom+h/2,z],edge);
   for(const y of [bottom+.025,bottom+h-.025])cube(g,[.075,.065,.075],[x,y,z],edge);
   const wheel=cyl(g,.046,.035,10,[x*.8,.047,z*.76],rub);wheel.rotation.z=Math.PI/2;
   cube(g,[.025,.035,.03],[x*.8,.09,z*.76],edge);
  }
  for(const z of [-d/2-.019,d/2+.019]){
   // Recessed handle, folding grip, butterfly latches and visible rivets.
   cube(g,[.23,.11,.012],[0,bottom+h*.42,z],edge);
   cube(g,[.18,.066,.014],[0,bottom+h*.42,z+Math.sign(z)*.008],rub);
   cube(g,[.15,.018,.026],[0,bottom+h*.43,z+Math.sign(z)*.024],edge);
   for(const x of [-w*.32,w*.32]){
    cube(g,[.065,.09,.022],[x,bottom+h*.79,z],edge);
    cube(g,[.025,.045,.032],[x,bottom+h*.79,z+Math.sign(z)*.015],rub);
   }
   for(const x of [-w*.40,0,w*.40])for(const y of [bottom+.018,bottom+h-.016]){
    const r=new THREE.Mesh(new THREE.SphereGeometry(.008,6,4),edge);r.position.set(x,y,z);g.add(r);
   }
  }
  const tag=new THREE.Mesh(new THREE.PlaneGeometry(w*.66,.08),new THREE.MeshBasicMaterial({map:labelTexture('JR • '+String(index+1).padStart(2,'0')),transparent:true,toneMapped:false}));
  tag.position.set(0,bottom+h*.18,d/2+.035);g.add(tag);
 }
 $('case-count').textContent=caseCount+' hardcase otomatis di ruang kosong';
}
// Hinged shoulders, elbows, hips and knees for readable individual poses.
let operatorDancer=null;
const danceAudio={bass:0,treble:0,energy:0,beat:0,bpm:120,lastOnset:-10,average:.1,previous:0};
function articulatedPerson(parent,x,z,shirtColor,scale=.87,skinColor='#c88c66'){
 const g=new THREE.Group();g.position.set(x,0,z);g.scale.setScalar(scale);parent.add(g);
 const rig=new THREE.Group();g.add(rig);const skin=mat(skinColor),shirt=mat(shirtColor);
 const torso=new THREE.Group();torso.position.y=.67;rig.add(torso);
 cube(torso,[.33,.54,.23],[0,.26,0],shirt);
 const head=new THREE.Group();head.position.y=.67;torso.add(head);
 const skull=new THREE.Mesh(new THREE.SphereGeometry(.17,10,8),skin);head.add(skull);
 const hair=new THREE.Mesh(new THREE.SphereGeometry(.177,10,8,0,Math.PI*2,0,1.25),dark);hair.position.y=.03;head.add(hair);
 const arms=[],elbows=[],legs=[],knees=[];
 for(const k of [-1,1]){
  const arm=new THREE.Group();arm.position.set(k*.235,.47,0);torso.add(arm);
  cube(arm,[.105,.24,.115],[0,-.12,0],shirt);
  const elbow=new THREE.Group();elbow.position.y=-.24;arm.add(elbow);cube(elbow,[.085,.23,.09],[0,-.115,0],skin);
  const hand=new THREE.Mesh(new THREE.SphereGeometry(.057,8,6),skin);hand.position.y=-.25;elbow.add(hand);
  arms.push(arm);elbows.push(elbow);
  const leg=new THREE.Group();leg.position.set(k*.10,.64,0);rig.add(leg);cube(leg,[.12,.29,.145],[0,-.145,0],dark);
  const knee=new THREE.Group();knee.position.y=-.29;leg.add(knee);cube(knee,[.11,.28,.13],[0,-.14,0],dark);
  cube(knee,[.14,.07,.24],[0,-.31,.04],mat('#d5d7d9',.05,.8));legs.push(leg);knees.push(knee);
 }
 const seedIndex=Math.floor(rnd()*97);
 g.userData.dancer={rig,torso,head,arms,elbows,legs,knees,seed:seedIndex,amount:0};g.userData.limbs=arms;
 return g;
}
function poseDancer(g,time,dt,active,amount=1){
 const d=g.userData.dancer;if(!d)return;
 // Different start delay/rest windows and phrase offsets per person.
 const b=danceAudio.beat+d.seed*.37,rest=(Math.floor(b/12)+d.seed)%9===0;
 d.amount=damp(d.amount,active&&!rest?amount:0,4,dt);
 const pose=mixedDance(danceAudio.beat,d.seed),a=d.amount*(.55+Math.min(.45,danceAudio.energy));
 d.rig.position.set(pose.hipX*a,Math.max(-.025,pose.hipY*a),pose.hipZ*a);
 d.torso.rotation.set(pose.bodyX*a,pose.bodyY*a,pose.bodyZ*a);d.head.rotation.y=pose.head*a;
 d.arms[0].rotation.set(pose.armLX*a,0,.08+pose.armLZ*a);d.arms[1].rotation.set(pose.armRX*a,0,-.08+pose.armRZ*a);
 d.elbows[0].rotation.x=-pose.elbowL*a;d.elbows[1].rotation.x=-pose.elbowR*a;
 d.legs[0].rotation.x=pose.legL*a;d.legs[1].rotation.x=pose.legR*a;d.knees[0].rotation.x=pose.kneeL*a;d.knees[1].rotation.x=pose.kneeR*a;
 if(a<.05){d.torso.rotation.z=Math.sin(time*.9+d.seed)*.015;}
}
function animateOperator(time,dt){
 if(!operatorDancer)return;
 poseDancer(operatorDancer,time,dt,state.stage===5&&state.fieldStopped&&!audio.paused,danceAudio.energy>.015?1:0);
}
let garageCameraName='perspective';
function garageOcclusion(){
 if(state.stage===5)return;
 // Only walls between camera and truck fade; the vehicle stays fully visible.
 for(const wall of garageWalls){const hidden=wall.userData.axis==='x'?Math.sign(camera.position.x)===Math.sign(wall.position.x)&&Math.abs(camera.position.x)>11.7:camera.position.z<-10.8;
  wall.visible=!hidden;}
 garageSign.visible=camera.position.z>-10.8;
}

function makePartMesh(item){
 const p=PARTS[item.key],v=VEHICLES[state.vehicle];const g=new THREE.Group();g.userData.partId=item.id;
 if(p.rig){
  // Truss tergantung BEHIND the rear of the vehicle, not installed over the bed.
  const width=v.width-.20,h=v.rigHeight,z=-v.length/2+item.z;
  const pipe=mat('#b9c4d1',.73,.31),brace=mat('#748998',.7,.4);
  const local=new THREE.Group();g.add(local);
  for(const side of [-1,1]){
   const x=side*width/2;
   for(const zz of [-.16,.16])cube(local,[.055,h,.055],[x,h/2,zz],pipe);
   cube(local,[.06,.07,.41],[x,h,0],pipe);
   // Light cross braces create short truss on the REAR of vehicle.
   for(let i=0;i<4;i++){
    const bar=cube(local,[.045,.06,.38],[x,.36+i*(h-.45)/4,0],brace);
    bar.rotation.x=(i%2?1:-1)*.42;
   }
  }
  for(const zz of [-.16,.16]){
   cube(local,[width+.12,.07,.07],[0,h,zz],pipe);
   cube(local,[width+.12,.06,.06],[0,h-.18,zz],pipe);
  }
  // Rigging menggantung BEHIND speaker wall. Upper outriggers extend from the rear of the vehicle.
  // No platform frame covering the floor of the cargo bed.
  for(const side of [-1,1]){
   const anchorX=side*(width/2-.08);
   const beam=cube(local,[.09,.09,.66],[anchorX,h-.03,.40],steel);
   const support=cube(local,[.06,.06,.69],[anchorX,h*.62,.26],brace);support.rotation.x=-.40;
  }
  // Bilah horizontal untuk tiap baris lampu, termasuk slot yang turun secara vertikal.
  const {rows,cols}=rigLimits(state.vehicle);
  for(let row=0;row<rows;row++){
   const y=.26+(h-.34)*(row+.5)/rows;
   cube(local,[width,.035,.035],[0,y,.01],brace);
  }
  for(let col=0;col<cols;col++){
   const x=-width/2+width*(col+.5)/cols;
   cube(local,[.028,h-.19,.028],[x,h/2,-.10],brace);
  }
  g.position.set(0,1.32,z);return g;
 }
 const rigItem=Boolean(p.requiresRig);
 g.rotation.y=p.category==='sound'?Math.PI:(item.rot||0)*Math.PI/2; // Muka speaker -Z, menghadap jalan di belakang kendaraan.
 g.position.set(item.x,1.32+item.y+p.h/2,-v.length/2+item.z);
 if(p.category==='sound')mkSpeaker(g,p);
 else if(p.genW){
  cube(g,[p.w,p.h,p.d],[0,0,0],mat('#d5b25b',.25,.48));
  cube(g,[p.w*.68,p.h*.2,p.d*.5],[0,p.h*.21,0],mat('#303948'));
  for(const sx of [-1,1])for(const sz of [-1,1]){const wheel=cyl(g,.09,.06,16,[sx*p.w*.37,-p.h*.43,sz*p.d*.32],rubber);wheel.rotation.z=Math.PI/2;}
  textBadge(g,'GENSET',0,.20);
 }else if(p.ampW){
  cube(g,[p.w,p.h,p.d],[0,0,0],mat('#2e333f',.27,.53));
  for(let i=0;i<5;i++){cube(g,[p.w*.78,.09,.02],[0,-p.h*.34+i*.13,p.d/2+.016],mat('#7d8b9a'));for(const side of [-1,1])cube(g,[.035,.035,.03],[side*.18,-p.h*.34+i*.13,p.d/2+.04],new THREE.MeshBasicMaterial({color:i%2?'#4de4ae':'#ffbd5b'}));}
 }else if(item.key==='mixer'||item.key==='dsp'){
  cube(g,[p.w,p.h,p.d],[0,0,0],mat('#3a4655',.36,.45));
  for(let i=0;i<6;i++)cyl(g,.023,.017,10,[-p.w*.34+i*p.w*.14,p.h/2+.016,0],chrome);
  textBadge(g,item.key.toUpperCase(),0,.13);
 }else if(item.key==='wall'){
  const width=v.width-.32, height=width*9/16;
  cube(g,[width,height,p.d],[0,(height-p.h)/2,0],mat('#0c1320',.36,.47));
  const canvas=document.createElement('canvas');canvas.width=640;canvas.height=360;
  const tex=new THREE.CanvasTexture(canvas);tex.colorSpace=THREE.SRGBColorSpace;
  const display=new THREE.Mesh(new THREE.PlaneGeometry(width*.96,height*.94),new THREE.MeshBasicMaterial({map:tex,side:THREE.DoubleSide}));
  display.position.set(0,(height-p.h)/2,-p.d/2-.018);display.rotation.y=Math.PI;g.add(display);
  g.userData.wallCanvas=canvas;g.userData.wallTexture=tex;
 }else if(item.key==='led'){
  cube(g,[p.w,p.h,p.d],[0,0,0],dark);
  for(let i=0;i<7;i++)cube(g,[.055,.06,.016],[-p.w*.37+i*.08,0,-p.d/2-.025],new THREE.MeshBasicMaterial({color:new THREE.Color().setHSL(i/7,.9,.55)}));
 }else if(item.key==='strobe'){
  cube(g,[p.w,p.h,p.d],[0,0,0],mat('#354154'));
  g.userData.strobePanels=[];
  for(let i=0;i<3;i++){const panel=cube(g,[.10,p.h*.46,.03],[(i-1)*.13,0,-p.d/2-.03],new THREE.MeshBasicMaterial({color:'#ff0066',toneMapped:false}));g.userData.strobePanels.push(panel);}
  textBadge(g,'STROBE',-.22,.09);
 }else if(item.key==='spot'){
  cube(g,[p.w*.8,.08,p.d*.65],[0,-p.h*.4,0],dark);
  for(const side of [-1,1])cube(g,[.035,.24,.06],[side*.17,-.02,0],steel);
  const swivel=new THREE.Group();g.add(swivel);g.userData.spotHead=swivel;
  const bowl=cyl(swivel,.13,.20,18,[0,.02,0],mat('#303b49',.7,.28));bowl.rotation.x=Math.PI/2;
  const lens=new THREE.Mesh(new THREE.CircleGeometry(.105,18),new THREE.MeshBasicMaterial({color:'#ffb000',side:THREE.DoubleSide,toneMapped:false}));lens.position.set(0,.02,-.105);swivel.add(lens);g.userData.spotLens=lens;
  g.userData.spotBeam=createBeam(swivel,28,1.8,.02,-.12);
  textBadge(g,'SWEEP',-.22,.09);
 }else if(item.key==='moving'){
  cube(g,[p.w,.12,p.d],[0,-p.h*.39,0],dark);
  for(const side of [-1,1])cube(g,[.10,.24,.12],[side*p.w*.33,-.02,0],steel);
  const headRig=new THREE.Group();g.add(headRig);
  const head=cyl(headRig,.16,.24,16,[0,p.h*.12,0],steel);head.rotation.z=Math.PI/2;
  const lens=new THREE.Mesh(new THREE.SphereGeometry(.08,12,8),new THREE.MeshBasicMaterial({color:'#70dcff'}));lens.position.set(0,p.h*.12,-p.d*.25);headRig.add(lens);
  g.userData.movingHead=headRig;g.userData.movingLens=lens;
  g.userData.beam=createBeam(headRig,125,4.8,p.h*.12,-p.d*.25);
  lens.material.toneMapped=false;

 }
 if(p.requiresRig)g.traverse(m=>{if(m.userData.badge){m.rotation.y=Math.PI;m.position.z=-p.d/2-.05;}});
 // Halo glow for all fixtures, scaled to lamp size (stylized glow, not real-world beam calculation).
 if(['led','strobe','spot','moving'].includes(item.key)){
  const glowTex=getLampGlowTexture();
  const glow=new THREE.Sprite(new THREE.SpriteMaterial({map:glowTex,color:item.key==='spot'?'#fff3ae':'#88d8ff',transparent:true,opacity:.6,depthWrite:false,blending:THREE.NormalBlending,toneMapped:false,fog:false}));
  glow.userData.isEffect=true;
  glow.position.set(0,0,-p.d/2-.065);glow.scale.set(Math.max(.7,p.w*1.9),Math.max(.62,p.h*2),1);(g.userData.movingHead||g.userData.spotHead||g).add(glow);g.userData.glow=glow;
 }
 return g;
}

const partMeshes=new Map();function redrawParts(){disposeGroup(partRoot);partMeshes.clear();selectOutline=null;
 for(const item of [...state.items].sort((a,b)=> (a.key==='rig'? -1 : b.key==='rig'?1:0))){const m=makePartMesh(item);partRoot.add(m);partMeshes.set(item.id,m);
 m.traverse(obj=>{if(obj.isMesh)obj.userData.partId=item.id;});
 if(item.id===state.selected){const bounds=solidBounds(m);if(!bounds.isEmpty()){const size=new THREE.Vector3();const center=new THREE.Vector3();bounds.getSize(size);bounds.getCenter(center);const wire=new THREE.Box3Helper(bounds,0x6af9e5);partRoot.add(wire);selectOutline=wire;}}
 }
 redrawHardcases();
 $('viewport-count').textContent=`${state.items.length} PERANGKAT · ${caseCount} CASE`;
 const framedKey=JSON.stringify([state.vehicle,state.items]);
 if(state.stage!==5&&framedKey!==lastFramedLayout){lastFramedLayout=framedKey;setCamera(garageCameraName);}
}
function setCamera(name){
 garageCameraName=name;
 const v=VEHICLES[state.vehicle];
 const bounds=solidBounds(vehicleRoot).union(solidBounds(partRoot));if(caseRoot)bounds.union(solidBounds(caseRoot));
 const focus=bounds.getCenter(new THREE.Vector3());
 const vfov=THREE.MathUtils.degToRad(camera.fov),hfov=2*Math.atan(Math.tan(vfov/2)*Math.max(.4,camera.aspect));
 const dirs={perspective:new THREE.Vector3(1,.44,1),front:new THREE.Vector3(0,.13,1),rear:new THREE.Vector3(0,.08,-1),side:new THREE.Vector3(1,.20,0),top:new THREE.Vector3(.001,1,.001)};
 const dir=(dirs[name]||dirs.perspective).normalize(),right=new THREE.Vector3().crossVectors(new THREE.Vector3(0,1,0),dir).normalize(),up=new THREE.Vector3().crossVectors(dir,right);
 let distance=4;
 for(const x of [bounds.min.x,bounds.max.x])for(const y of [bounds.min.y,bounds.max.y])for(const z of [bounds.min.z,bounds.max.z]){
  const point=new THREE.Vector3(x,y,z).sub(focus),near=point.dot(dir);
  distance=Math.max(distance,near+Math.abs(point.dot(right))*1.15/Math.tan(hfov/2),near+Math.abs(point.dot(up))*1.15/Math.tan(vfov/2));
 }
 controls.autoRotate=false;controls.enabled=true;controls.enablePan=false;controls.minDistance=3;controls.maxDistance=Math.max(55,distance+10);
 controls.maxPolarAngle=Math.PI/2.02;controls.target.copy(focus);
 camera.position.copy(focus).addScaledVector(dir,distance);
 camera.lookAt(focus);controls.saveState();controls.reset();garageOcclusion();
 document.querySelectorAll('[data-camera]').forEach(e=>e.classList.toggle('active',e.dataset.camera===name));
 $('toggle-spin').classList.remove('active');
}
function updateLight(){
 const inParade=state.stage===5;
 const nightParade=state.night&&inParade;
 const bg=nightParade?'#060b15':state.night?'#182538':'#a6cbe5';
 scene.background.set(bg);scene.fog.color.set(bg);
 scene.fog.density=nightParade?.008:state.night?.0048:.004;
 hemi.intensity=nightParade?1.30:state.night?2.6:3.0;
 sun.intensity=nightParade?1.5:state.night?3.4:4.7;
 glowBlue.intensity=nightParade?115:state.night?145:25;
 glowWarm.intensity=nightParade?90:state.night?105:20;
 renderer.toneMappingExposure=nightParade?1.35:1.55;
 $('toggle-night').classList.toggle('active',state.night);
 $('toggle-night').textContent=state.night?'☾ Malam':'☀ Siang';
}
function setStage(next){
 if(next<1||next>5)return;
 if(next===5){
  const result=inspection(state.items,state.vehicle,state.driver,state.operator);
  if(!result.ok){toast('Belum siap berangkat: '+result.errors[0],true);setStage(4);return;}
  if(!state.strapped){toast('Jalankan animasi ikat dari samping dahulu.',true);setStage(4);return;}
  enterParade();
 }else if(state.stage===5)leaveParade();
 state.stage=next;
 updateLight();
 document.querySelectorAll('[data-stage-panel]').forEach(el=>{el.hidden=Number(el.dataset.stagePanel)!==next;});
 document.querySelectorAll('button.step').forEach(el=>{const n=Number(el.dataset.stage);el.classList.toggle('active',n===next);el.classList.toggle('done',n<next);el.setAttribute('aria-current',n===next?'step':'false');});
 $('stage-back').hidden=next===1;$('stage-next').hidden=next>=4;$('save-build').hidden=next===5;
 $('garage-toolbar').hidden=next===5;
 $('stage-next').textContent=next===1?'Lanjut rakit →':next===2?'Pilih kru →':'Ikat & berangkat →';
 if(next===2){renderRigGrid();catalogRender();}if(next===4)updateChecks();
 if(next===4)setCamera('side');else if(next===3)setCamera('front');
 if(next===5){$('viewer-title').textContent=VEHICLES[state.vehicle].name+' · Auto Parade';}
}
function vehicleButtons(){const host=$('vehicle-list');host.innerHTML='';for(const [id,v] of Object.entries(VEHICLES)){
 const b=document.createElement('button');b.type='button';b.className='vehicle-card '+(id===state.vehicle?'active':'');b.innerHTML=`<span class="vehicle-glyph">${id==='pickup'?'🛻':id==='towing'?'🚛':'🚚'}</span><span class="vehicle-copy"><strong>${v.name}</strong><small>${fmt(v.payload)} kg · ${v.width.toFixed(2)} × ${v.length.toFixed(2)} m · rig ${v.rigCols}×${v.rigRows}</small></span><span class="tick">${id===state.vehicle?'✓':'→'}</span>`;
 b.addEventListener('click',()=>{if(id===state.vehicle)return;if(state.items.length&&!confirm('Ganti kendaraan akan mengosongkan rakitan di game. Lanjutkan?'))return;
  state.history=[];state.vehicle=id;state.items=[];state.selected=null;state.rigSlot=null;state.strapped=false;clearStraps();buildVehicle();renderUI();toast(`Kendaraan: ${v.name}`);});host.appendChild(b);
}}
function catalogRender(){document.querySelectorAll('[data-tab]').forEach(el=>el.classList.toggle('active',el.dataset.tab===state.tab));const host=$('catalog');host.innerHTML='';
 Object.entries(PARTS).filter(([key,p])=>p.category===state.tab).forEach(([key,p])=>{const b=document.createElement('button');b.type='button';b.className='catalog-card';b.innerHTML=`<span class="part-emoji">${p.emoji}</span><span class="part-info"><strong>${p.label}</strong><small>${p.mass} kg · ${p.requiresRig?'Pilih slot rig terlebih dahulu':p.rig?'Posisi rig tetap':'Perangkat bak'}</small><em>${p.hint}</em></span><span class="plus">＋</span>`;b.addEventListener('click',()=>add(key));host.appendChild(b);});
 $('rig-grid-panel').hidden=!['light','rig','video'].includes(state.tab)&&!PARTS[state.items.find(i=>i.id===state.selected)?.key]?.requiresRig;
}
function rigCellStatus(col,row){const dummy=rigPose(state.vehicle,col,row,'led');const used=state.items.filter(i=>PARTS[i.key].requiresRig);const n=used.find(i=>Number.isInteger(i.col)&&i.col===col&&i.row===row);const wall=used.find(i=>i.key==='wall');
 const overlapWall=wall&&Math.abs(dummy.x-wall.x)<(PARTS.led.w+PARTS.wall.w)/2+.045 && Math.abs((dummy.y+PARTS.led.h/2)-(wall.y+PARTS.wall.h/2))<(PARTS.led.h+PARTS.wall.h)/2+.045;
 return n?'busy':overlapWall?'blocked':'free';}
function renderRigGrid(){const v=VEHICLES[state.vehicle],host=$('rig-grid');host.innerHTML='';host.style.gridTemplateColumns=`repeat(${v.rigCols},minmax(0,1fr))`;host.style.gridTemplateRows=`repeat(${v.rigRows},1fr)`;
 $('rig-grid-caption').textContent=`${v.rigCols} mendatar × ${v.rigRows} menurun`;
 // Row dari atas ke bawah untuk tampilan seperti permukaan rig.
 for(let row=v.rigRows-1;row>=0;row--)for(let col=0;col<v.rigCols;col++){
  const b=document.createElement('button');b.type='button';const busy=rigCellStatus(col,row);b.className='rig-cell '+busy+(state.rigSlot&&state.rigSlot.col===col&&state.rigSlot.row===row?' active':'');b.textContent=`${col+1}-${v.rigRows-row}`;
  b.title=`Kolom ${col+1}, tingkat ${v.rigRows-row} dari atas (${busy})`;b.setAttribute('aria-label',b.title);
  b.addEventListener('click',()=>{const selected=state.items.find(i=>i.id===state.selected);
   if(selected&&PARTS[selected.key].requiresRig&&selected.key!=='wall'){
    const r=moveRigItem(state.items,state.vehicle,selected.id,col,row);
    if(!r.ok)return toast(r.error,true);mutationItems(r.items);state.rigSlot={col,row};toast('Lampu berhasil dipindahkan ke grid rigging.');return;
   }
   state.rigSlot={col,row};renderRigGrid();$('rig-hint').textContent=`Posisi dipilih: kolom ${col+1}, baris ${v.rigRows-row} dari atas. Tambah lampu dari katalog.`;
  });host.appendChild(b);
 }
 if(!state.rigSlot)$('rig-hint').textContent='Pilih satu slot untuk lampu (dapat di tiang vertikal).';
}
function updateInstalled(){
 const host=$('item-list');host.innerHTML='';
 if(!state.items.length)host.innerHTML='<div class="empty">Belum ada perangkat. Pilih dulu lokasi speaker, lalu tambahkan.</div>';
 for(const it of state.items){
  const p=PARTS[it.key],row=document.createElement('div');row.className='installed-row';
  const select=document.createElement('button');select.type='button';select.className='installed-item '+(it.id===state.selected?'active':'');
  select.innerHTML=`<span>${p.emoji}</span><span class="part-info"><strong>${p.label}</strong><small>${p.mass} kg${Number.isInteger(it.col)?` · slot ${it.col+1}/${it.row+1}`:''}</small></span>`;
  select.addEventListener('click',()=>{state.selected=it.id;if(p.requiresRig&&it.key!=='wall'){state.tab='light';state.rigSlot={col:it.col,row:it.row};}redrawParts();updateInstalled();catalogRender();renderRigGrid();});
  const del=document.createElement('button');del.type='button';del.className='delete-item';del.textContent='✕';del.title='Hapus '+p.label;del.setAttribute('aria-label','Hapus '+p.label);
  del.addEventListener('click',()=>{const before=state.items.length;mutationItems(removeItem(state.items,it.id));toast('Dihapus '+(before-state.items.length)+' perangkat (termasuk yang kehilangan penopang).');});
  row.append(select,del);host.appendChild(row);
 }
 const sel=state.items.find(i=>i.id===state.selected);$('adjust-panel').hidden=!sel;
 if(sel){$('selected-title').textContent=PARTS[sel.key].label;$('edit-hint').textContent=PARTS[sel.key].requiresRig?'Pindahkan lampu lewat pop-up grid rigging.':'Geser 20 cm. Speaker tetap menghadap belakang.';}
 $('item-count').textContent=state.items.length+' ITEM';
}
function updateChecks(){const r=inspection(state.items,state.vehicle,state.driver,state.operator),s=r.stats,v=VEHICLES[state.vehicle];
 $('mass-text').textContent=`${fmt(s.mass)} / ${fmt(s.payload)} kg`;$('mass-fill').style.width=Math.min(100,100*s.mass/s.payload)+'%';$('mass-fill').classList.toggle('warning',s.mass/s.payload>.85);
 $('power-text').textContent=`${fmt(s.demand)} / ${fmt(s.supply)} W`;$('power-fill').style.width=(s.supply?Math.min(100,100*s.demand/s.supply):(s.demand?100:0))+'%';$('power-fill').classList.toggle('warning',s.demand>s.supply);
 $('height-text').textContent=fmt(s.height,2)+' / '+fmt(v.maxHeight,2)+' m';$('balance-text').textContent=fmt(Math.abs(s.balance),2)+' m';$('balance-long-text').textContent=fmt(Math.abs(s.balanceLong),2)+' m';
 $('ready-label').textContent=r.ok?'SIAP':'PERLU DICEK';$('ready-label').className=r.ok?'ready':'not-ready';
 $('checks').innerHTML='';const checks=[[s.mass<=s.payload,'Muatan sesuai batas game'],[s.height<=s.maxHeight,'Tinggi rakitan sesuai batas'],[Math.abs(s.balance)<=v.maxSideOffset,'Keseimbangan kiri/kanan'],[Math.abs(s.balanceLong)<=v.maxLongOffset,'Keseimbangan depan/belakang'],[s.supply>=s.demand&&s.supply>0,'Daya genset mencukupi'],[s.ampOutput>=s.speakerDemand&&s.speakerDemand>0,'Daya amplifier mencukupi'],[Boolean(state.driver&&state.operator&&state.driver!==state.operator),'Tim berbeda dan lengkap']];
 for(const [ok,label] of checks){const el=document.createElement('div');el.className='check '+(ok?'ok':'warn');el.textContent=(ok?'✓ ':'! ')+label;$('checks').appendChild(el);}
 const live=$('live-health');
 if(live){
  const alerts=[];
  if(s.mass>v.payload*.88)alerts.push('Muatan mendekati batas.');
  if(s.speakerDemand>0&&!state.items.some(i=>i.key==='amp'))alerts.push('Tambahkan rack amplifier MAX.');
  else if(s.speakerDemand>s.ampOutput)alerts.push('Speaker melebihi amplifier MAX '+fmt(v.ampMax)+' W.');
  if(s.demand>0&&!s.supply)alerts.push('Tambahkan genset yang sesuai kendaraan.');
  else if(s.demand>s.supply)alerts.push('Genset kurang '+fmt(s.demand-s.supply)+' W (simulasi).');
  live.classList.toggle('warning',alerts.length>0);
  live.textContent=`${fmt(s.mass)}/${fmt(v.payload)} kg · Amp MAX ${fmt(v.ampMax)} W · Daya ${fmt(s.demand)}/${fmt(s.supply)} W`+(alerts.length?' ⚠ '+alerts.join(' '):' ✓ Dalam batas daya & berat saat ini.');
 }
 $('summary-vehicle').textContent=v.name;$('summary-count').textContent=String(state.items.length);$('summary-load').textContent=`${fmt(s.mass)} / ${fmt(v.payload)} kg`;$('summary-crew').textContent=state.driver&&state.operator?`${state.driver} / ${state.operator}`:'Belum lengkap';
}
function renderUI(){vehicleButtons();catalogRender();redrawParts();updateInstalled();renderRigGrid();updateChecks();}
function buildChanged(){state.strapped=false;clearStraps();$('strap-fill').style.width='0%';$('strap-status').textContent='Rakitan berubah, perlu inspeksi dan ikat ulang.';}
function add(key){
 if(PARTS[key].requiresRig&&key!=='wall'&&!state.placingFromPicker){openRigPicker(key);return;}
 const slot=PARTS[key].requiresRig&&key!=='wall'?state.rigSlot:null;
 const current=(PARTS[key].genW)?state.items.filter(it=>!PARTS[it.key].genW):state.items;
 const r=PARTS[key].category==='sound'?placeSoundColumn(current,state.vehicle,key,state.soundColumn):autoAdd(current,state.vehicle,key,slot);
 if(!r.ok)return toast(r.error,true);
 saveHistory();buildChanged();state.items=[...current,r.item];state.selected=r.item.id;renderUI();
 const s=stats(state.items,state.vehicle);
 const warning=s.speakerDemand>0&&s.ampOutput<s.speakerDemand?'Amplifier MAX tidak cukup — kurangi speaker.':s.demand>s.supply?'Daya genset kurang; pilih kelas genset yang lebih besar.':'';
 toast(warning||`${PARTS[key].label} terpasang${PARTS[key].category==='sound'?' di kolom '+state.soundColumn:''}.`,Boolean(warning));
 if(PARTS[key].requiresRig){state.rigSlot=null;closeRigPicker();}
}
function buildBalancedSample(vehicleId){
 const v=VEHICLES[vehicleId],items=[],note=[];
 const add=(key,x=null)=>{
  const p=PARTS[key];const z=p.category==='sound'?-v.length/2+p.d/2+.12:0;
  const candidate=x===null?autoAdd(items,vehicleId,key):canAdd(items,vehicleId,key,x,z,0);
  if(!candidate.ok)return {ok:false,error:key+': '+candidate.error};
  items.push(candidate.item);return {ok:true};
 };
 // 2 kolom pada pickup; 3 kolom pada truk dan towing. Simetris kiri/tengah/kanan.
 const cols=vehicleId==='pickup'?[-.42,.42]:[-.79,0,.79];
 const genset=vehicleId==='pickup'?'gen5':vehicleId==='truck'?'gen12':'gen20';
 // Ruang operator, genset dan rangka wajib diisi sebelum sound agar berat total terbaca.
 for(const key of ['amp','mixer','dsp',genset,'rig']){const result=add(key);if(!result.ok)return {ok:false,error:result.error};}
 function layer(key){
  const draft=[];
  for(const x of cols){const p=PARTS[key],z=-v.length/2+p.d/2+.12;
   const result=canAdd([...items,...draft],vehicleId,key,x,z,0);
   if(!result.ok)return {ok:false,error:result.error};draft.push(result.item);
  }
  const all=[...items,...draft],s=stats(all,vehicleId);
  // Reserve a little electrical headroom for lamps.
  const margin=vehicleId==='pickup'?200:vehicleId==='truck'?340:450;
  if(s.speakerDemand>v.ampMax||s.demand+margin>s.supply||s.mass>v.payload)return {ok:false,error:'Sudah mendekati kapasitas daya/muatan yang disiapkan untuk lighting.'};
  items.push(...draft);return {ok:true};
 }
 // Full lower wall; two levels when the selected vehicle can support it.
 if(!layer('sub18').ok)return {ok:false,error:'Subwoofer dasar tidak bisa dipasang.'};
 if(vehicleId!=='pickup'){
  const second=layer('sub18');if(!second.ok)note.push('Subwoofer lapis kedua dibatasi daya.');
 }
 const mid=layer('mid15');if(!mid.ok)return {ok:false,error:'Mid tidak muat dalam konfigurasi '+v.name+'.'};
 // Horn kecil naik simetris per LAPIS, tidak mengisi satu sisi lebih dulu.
 const target=vehicleId==='pickup'?5:vehicleId==='truck'?6:7;
 for(let n=0;n<target;n++){const layerResult=layer('hornmini');if(!layerResult.ok)break;}
 // Komposisi lampu simetris; prioritaskan sisi dan atas, video wall opsional jika ada slot aman.
 const lightPairs=[
  ['moving',0,0],['moving',v.rigCols-1,0],
  ['strobe',0,1],['strobe',v.rigCols-1,1],
  ['spot',0,v.rigRows-1],['spot',v.rigCols-1,v.rigRows-1],
  ['led',0,Math.floor(v.rigRows/2)],['led',v.rigCols-1,Math.floor(v.rigRows/2)]
 ];
 // Video wall disusun sebelum lampu untuk menghindari persilangan komponen.
 const wall=autoAdd(items,vehicleId,'wall');if(wall.ok)items.push(wall.item);
 for(let i=0;i<lightPairs.length;i+=2){
  const pair=[],left=lightPairs[i],right=lightPairs[i+1];
  for(const [key,col,row] of [left,right]){const result=autoAdd([...items,...pair],vehicleId,key,{col,row});if(!result.ok){pair.length=0;break;}pair.push(result.item);}
  if(pair.length===2){const s=stats([...items,...pair],vehicleId);if(s.demand<=s.supply&&s.mass<=v.payload)items.push(...pair);}
 }
 const s=stats(items,vehicleId);
 if(s.demand>s.supply||s.speakerDemand>s.ampOutput)return {ok:false,error:'Kapasitas daya contoh belum sesuai.'};
 return {ok:true,items,note:note.join(' ')};
}
$('sample-build').addEventListener('click',()=>{
 if(state.items.length&&!confirm('Muat contoh simetris '+VEHICLES[state.vehicle].name+' akan mengganti rakitan saat ini?'))return;
 const result=buildBalancedSample(state.vehicle);
 if(!result.ok)return toast('Contoh gagal: '+result.error,true);
 state.items=result.items;state.driver='Adit';state.operator='Dika';state.selected=null;state.rigSlot=null;state.history=[];buildChanged();
 $('driver').value=state.driver;$('operator').value=state.operator;buildVehicle();renderUI();
 const horns=state.items.filter(i=>i.key==='hornmini').length;
 toast('Contoh '+VEHICLES[state.vehicle].name+': sound simetris, '+horns+' horn mini, lighting berpasangan. '+result.note);
});
function mutationItems(items){saveHistory();buildChanged();state.items=items;state.selected=items.some(x=>x.id===state.selected)?state.selected:null;renderUI();}
document.querySelectorAll('[data-sound-column]').forEach(b=>b.addEventListener('click',()=>{
 state.soundColumn=b.dataset.soundColumn;
 document.querySelectorAll('[data-sound-column]').forEach(x=>x.classList.toggle('active',x===b));
 toast('Lokasi speaker: '+({left:'kiri',center:'tengah',right:'kanan'}[state.soundColumn])+'. Tidak akan dipindah otomatis.');
}));
$('open-rig-picker').addEventListener('click',()=>openRigPicker(null));
$('close-rig-picker').addEventListener('click',closeRigPicker);
$('rig-picker').addEventListener('click',e=>{if(e.target.id==='rig-picker')closeRigPicker();});
function closeRigPicker(){ $('rig-picker').hidden=true;state.pendingLightKey=null;}
function openRigPicker(key){
 state.pendingLightKey=key;state.tab='light';
 const box=$('rig-picker-grid'),v=VEHICLES[state.vehicle];box.innerHTML='';
 box.style.gridTemplateColumns=`repeat(${v.rigCols},1fr)`;
 $('rig-picker-title').textContent=key?'Pilih slot: '+PARTS[key].label:'Pilih slot lampu rigging';
 for(let row=v.rigRows-1;row>=0;row--)for(let col=0;col<v.rigCols;col++){
  const b=document.createElement('button');b.type='button';b.textContent=`${col+1}-${v.rigRows-row}`;
  b.className='rig-cell '+rigCellStatus(col,row);
  b.addEventListener('click',()=>{
   if(state.pendingLightKey){const key=state.pendingLightKey;state.rigSlot={col,row};closeRigPicker();state.placingFromPicker=true;try{add(key);}finally{state.placingFromPicker=false;}return;}
   const it=state.items.find(x=>x.id===state.selected);
   if(it&&PARTS[it.key].requiresRig&&it.key!=='wall'){
    const result=moveRigItem(state.items,state.vehicle,it.id,col,row);
    if(!result.ok)return toast(result.error,true);mutationItems(result.items);closeRigPicker();toast('Lampu dipindahkan.');return;
   }
   state.rigSlot={col,row};closeRigPicker();renderRigGrid();toast('Slot '+(col+1)+'-'+(v.rigRows-row)+' dipilih, lalu klik jenis lampu.');
  });box.appendChild(b);
 }
 $('rig-picker').hidden=false;
}
$('catalog-tabs').addEventListener('click',e=>{const b=e.target.closest('button[data-tab]');if(!b)return;state.tab=b.dataset.tab;catalogRender();renderRigGrid();});
$('new-light-slot').addEventListener('click',()=>{state.selected=null;state.rigSlot=null;updateInstalled();redrawParts();renderRigGrid();toast('Pilih sel kosong untuk memasang lampu baru.');});
$('paint-picker').addEventListener('click',e=>{const b=e.target.closest('[data-color]');if(!b)return;state.color=b.dataset.color;buildVehicle();redrawParts();document.querySelectorAll('.swatch').forEach(t=>t.classList.toggle('active',t===b));});
document.querySelectorAll('[data-camera]').forEach(b=>b.addEventListener('click',()=>setCamera(b.dataset.camera)));
$('toggle-spin').addEventListener('click',()=>{controls.autoRotate=!controls.autoRotate;$('toggle-spin').classList.toggle('active',controls.autoRotate);});
$('toggle-night').addEventListener('click',()=>{state.night=!state.night;updateLight();});
for(const role of ['driver','operator'])$(role).addEventListener('change',e=>{state[role]=e.target.value;drawCrew();updateChecks();buildChanged();toast(`${role==='driver'?'Supir':'Operator'}: ${state[role]||'belum dipilih'}`);});
$('remove-selected').addEventListener('click',()=>{if(!state.selected)return;mutationItems(removeItem(state.items,state.selected));toast('Komponen dihapus.');});
$('rotate-selected').addEventListener('click',()=>{const r=moveItem(state.items,state.vehicle,state.selected,0,0,true);if(!r.ok)return toast(r.error,true);mutationItems(r.items);});
$('duplicate-selected').addEventListener('click',()=>{const it=state.items.find(i=>i.id===state.selected);if(it)add(it.key);});
document.querySelectorAll('[data-nudge]').forEach(b=>b.addEventListener('click',()=>{const d={'+x':[.2,0],'-x':[-.2,0],'+z':[0,.2],'-z':[0,-.2]}[b.dataset.nudge];const r=moveItem(state.items,state.vehicle,state.selected,...d);if(!r.ok)return toast(r.error,true);mutationItems(r.items)}));
$('undo-last').addEventListener('click',()=>{if(!state.history.length)return toast('Tidak ada perubahan.');buildChanged();state.items=JSON.parse(state.history.pop());state.selected=null;renderUI();});
$('clear-build').addEventListener('click',()=>{if(state.items.length&&confirm('Kosongkan perangkat di bak?'))mutationItems([]);});
$('stage-next').addEventListener('click',()=>setStage(state.stage+1));$('stage-back').addEventListener('click',()=>setStage(state.stage-1));
document.querySelectorAll('.step').forEach(b=>b.addEventListener('click',()=>setStage(Number(b.dataset.stage))));
const STORAGE='jr_audio_horeg_build_v6';
$('save-build').addEventListener('click',()=>{try{localStorage.setItem(STORAGE,JSON.stringify({version:6,vehicle:state.vehicle,color:state.color,items:state.items,driver:state.driver,operator:state.operator,beamMode:state.beamMode}));toast('Rakitan disimpan di browser (bukan database).');}catch(e){toast('Penyimpanan browser gagal.',true);}});
function load(){try{const saved=JSON.parse(localStorage.getItem(STORAGE)||'null');if(!saved||saved.version!==6||!VEHICLES[saved.vehicle])return;
 state.vehicle=saved.vehicle;state.color=saved.color||state.color;state.driver=CREW.includes(saved.driver)?saved.driver:'';state.operator=CREW.includes(saved.operator)?saved.operator:'';state.beamMode=saved.beamMode==='white'?'white':'rainbow';
 document.querySelectorAll('[data-beam-mode]').forEach(select=>select.value=state.beamMode);
 // Validasi ulang setiap komponen agar data browser tidak menerobos pembatas game.
 state.items=[];for(const it of (Array.isArray(saved.items)?saved.items.slice(0,110):[])){if(!PARTS[it.key])continue;
 const slot=PARTS[it.key].requiresRig&&it.key!=='wall'?{col:it.col,row:it.row}:null;
 const r=canAdd(state.items,state.vehicle,it.key,Number(it.x)||0,Number(it.z)||0,Number(it.rot)||0,String(it.id||''),slot);
 if(r.ok)state.items.push(r.item);}
 $('driver').value=state.driver;$('operator').value=state.operator;
}catch(e){console.warn('Gagal memuat rakitan:',e);}}
const modal=$('result-modal');const closeModal=()=>modal.hidden=true;$('close-modal').addEventListener('click',closeModal);$('modal-ok').addEventListener('click',closeModal);modal.addEventListener('click',e=>{if(e.target===modal)closeModal();});document.addEventListener('keydown',e=>{if(e.key==='Escape')closeModal();});
function showInspection(){const r=inspection(state.items,state.vehicle,state.driver,state.operator);$('modal-heading').textContent=r.ok?'✓ RAKITAN LULUS SIMULASI':'⚠ RAKITAN BELUM SIAP';const body=$('result-details');body.innerHTML='';const p=document.createElement('p');p.textContent=r.ok?'Pemeriksaan game lulus. Sekarang jalankan animasi pengikatan sebelum parade.':'Perlu diperbaiki sebelum tali dapat dipasang:';body.appendChild(p);if(!r.ok){const ul=document.createElement('ul');for(const msg of r.errors){const li=document.createElement('li');li.textContent=msg;ul.appendChild(li);}body.appendChild(ul);}const caveat=document.createElement('p');caveat.className='caveat';caveat.textContent='Ini hanya pemeriksaan virtual. Tidak menjamin kelaikan kendaraan/rigging asli.';body.appendChild(caveat);modal.hidden=false;}
$('parade-check').addEventListener('click',showInspection);
// Tali ratchet divisualkan dari tepi atas/samping dinding sound ke titik ikat dek.
// Bukan simulasi kekuatan tali atau prosedur pengamanan dunia nyata.
let straps=new THREE.Group();vehicleRoot.add(straps);let tieAnimation=null;
function clearStraps(){if(tieAnimation){cancelAnimationFrame(tieAnimation);tieAnimation=null;const button=$('start-straps');if(button)button.disabled=false;}if(straps.parent)straps.parent.remove(straps);disposeGroup(straps);straps=new THREE.Group();vehicleRoot.add(straps);state.strapped=false;}
function strapBounds(){
 const sound=state.items.filter(i=>PARTS[i.key].category==='sound'),v=VEHICLES[state.vehicle];
 return {top:1.32+Math.max(.8,...sound.map(i=>i.y+PARTS[i.key].h)),
  left:Math.min(-.35,...sound.map(i=>i.x-PARTS[i.key].w/2))-.08,
  right:Math.max(.35,...sound.map(i=>i.x+PARTS[i.key].w/2))+.08,
  rear:-v.length+.12};
}
function makeStrap(kind){
 const b=strapBounds(),v=VEHICLES[state.vehicle],y=b.top+.12;
 const points=kind==='across' ? [
  new THREE.Vector3(b.right+.12,1.36,b.rear+.26),
  new THREE.Vector3(b.right+.12,y*.72,b.rear+.12),
  new THREE.Vector3(b.right+.05,y,b.rear+.12),
  new THREE.Vector3(0,y+.025,b.rear+.12),
  new THREE.Vector3(b.left-.05,y,b.rear+.12),
  new THREE.Vector3(b.left-.12,y*.72,b.rear+.12),
  new THREE.Vector3(b.left-.12,1.36,b.rear+.26)
 ] : [
  new THREE.Vector3(0,y+.02,b.rear+.18),
  new THREE.Vector3(0,y+.22,b.rear+.48),
  new THREE.Vector3(0,2.9,-v.length*.28),
  new THREE.Vector3(0,2.75,.74) // Top-middle cabin
 ];
 const track=new THREE.CatmullRomCurve3(points,false,'centripetal',.2);
 const geo=new THREE.TubeGeometry(track,96,.032,7,false);geo.setDrawRange(0,0);
 const mesh=new THREE.Mesh(geo,new THREE.MeshStandardMaterial({color:'#ff9c36',roughness:.54}));straps.add(mesh);
 return {geo};
}
$('start-straps').addEventListener('click',()=>{const result=inspection(state.items,state.vehicle,state.driver,state.operator);if(!result.ok)return showInspection();
 if(tieAnimation)return;clearStraps();state.strapped=false;setCamera('side');const across=makeStrap('across'),cabin=makeStrap('cabin');const started=performance.now(),duration=3600;
 $('start-straps').disabled=true;$('strap-status').textContent='Menarik tali ratchet… krek…';
 function frame(now){const p=Math.min(1,(now-started)/duration);
  for(const [index,entry] of [across,cabin].entries()){
   const progress=Math.min(1,Math.max(0,(p-index*.24)/.76));
   const count=entry.geo.index.count;
   entry.geo.setDrawRange(0,Math.floor(count*progress/3)*3);
  }
  $('strap-fill').style.width=Math.round(p*100)+'%';
  $('strap-status').textContent=p<.48?'Tali dari kanan ke kiri melintasi atas sound…':p<.95?'Tali penghubung ke tengah atap kabin… krek!':'Tali visual terpasang.';
  if(p<1)tieAnimation=requestAnimationFrame(frame);else{tieAnimation=null;state.strapped=true;$('start-straps').disabled=false;toast('Krek! Tali samping terpasang. Sekarang bisa mulai Auto Parade.');}
 }
 tieAnimation=requestAnimationFrame(frame);
});
// ========= AUTO PARADE v0.7: continuous winding loop, optional field detour =========
const paradeEnvironment=new THREE.Group();scene.add(paradeEnvironment);paradeEnvironment.visible=false;
const P=(x,z)=>new THREE.Vector3(x,0,z);
const routing=buildRoutes(THREE),routePaths=routing.paths;
const FIELD_STOP=routing.stop,entryAt=routing.entryAt,exitAt=routing.exitAt;
const paradeLengths=Object.fromEntries(Object.entries(routePaths).map(([k,v])=>[k,v.getLength()]));
const roadPaths=Object.values(routePaths);
const junctionState={fieldRequested:false,cameraAnchor:null};
const routeDistances=[];
function buildRoad(curve){
 const N=Math.ceil(curve.getLength()/1.4),road=mat('#353a43',.03,.97),line=new THREE.MeshBasicMaterial({color:'#e2c776'});
 for(let i=0;i<N;i++){
  const a=curve.getPointAt(i/N),b=curve.getPointAt((i+1)/N),d=b.clone().sub(a),mid=a.clone().add(b).multiplyScalar(.5);
  const block=cube(paradeEnvironment,[6.0,.082,d.length()+.28],[mid.x,0,mid.z],road);block.rotation.y=Math.atan2(d.x,d.z);
  if(i%7<3){const dash=cube(paradeEnvironment,[.09,.015,d.length()*.82],[mid.x,.052,mid.z],line);dash.rotation.y=block.rotation.y;}
 }
}
function nearestRoadDistance(p){let best=Infinity;for(const path of roadPaths)for(let t=0;t<=1;t+=.007){const q=path.getPointAt(t),d=(p.x-q.x)**2+(p.z-q.z)**2;if(d<best)best=d;}return Math.sqrt(best);}
let seed=8347;function rnd(){seed=(Math.imul(seed,1664525)+1013904223)>>>0;return seed/4294967296;}
const reserved=[];
function clearSpot(x,z,radius){return reserved.every(q=>Math.hypot(q.x-x,q.z-z)>q.r+radius+.55);}
function reserve(x,z,r){reserved.push({x,z,r});}
const crowdStatic=[],fieldAudience=[];let spectatorClock=0;
// Crowd stays in place along road; only field spectators step closer during a stop.
function animateSpectators(t,dt){
 spectatorClock+=dt;
 const dance=state.fieldStopped&&state.routeChoice==='field'&&!audio.paused&&danceAudio.energy>.015;
 for(let i=0;i<crowdStatic.length;i++)poseDancer(crowdStatic[i],t,dt,false);
 for(let i=0;i<fieldAudience.length;i++){
  const c=fieldAudience[i],origin=c.userData.origin;
  const progress=state.fieldStopped&&state.routeChoice==='field'?1:0;
  c.userData.approach=damp(c.userData.approach,progress,.6,dt);
  const along=c.userData.approach;
  c.position.x=origin.x+(c.userData.target.x-origin.x)*along;
  c.position.z=origin.z+(c.userData.target.z-origin.z)*along;
  const ready=along>.80+(i%4)*.04;
  poseDancer(c,t,dt,dance&&ready&&i%4!==0,.65+(i%5)*.08);
  if(!ready&&along>.01&&along<.98){const d=c.userData.dancer;d.legs[0].rotation.x=Math.sin(t*4+i)*.22;d.legs[1].rotation.x=-Math.sin(t*4+i)*.22;}
 }
}
function makePerson(parent,x,z,shirtColor,scale=.87){
 return articulatedPerson(parent,x,z,shirtColor,scale,['#dca67d','#9c6247','#c88c66'][Math.floor(rnd()*3)]);
}
function addEnvironmentDetails(){
 const pole=mat('#344452',.7,.5),stone=mat('#b8ada0',.05,.9),wood=mat('#795440'),warm=new THREE.MeshBasicMaterial({color:'#ffd58b',toneMapped:false});
 // Roadside lamps, flags and planter bases respect road/building clearance.
 for(let i=0;i<32;i++){
  const p=routePaths.village.getPointAt((i+.3)/32),tan=routePaths.village.getTangentAt((i+.3)/32),side=i%2?1:-1;
  const x=p.x-tan.z*side*7.4,z=p.z+tan.x*side*7.4;
  if(!clearSpot(x,z,.5)||nearestRoadDistance({x,z})<6.5)continue;
  const g=new THREE.Group();g.position.set(x,0,z);paradeEnvironment.add(g);
  cube(g,[.50,.22,.50],[0,.11,0],stone);cyl(g,.065,4.3,8,[0,2.25,0],pole);
  cube(g,[.78,.06,.08],[.30,4.34,0],pole);cube(g,[.4,.10,.27],[.58,4.27,0],dark);cube(g,[.31,.035,.20],[.58,4.20,0],warm);
  if(i%2===0){cube(g,[.38,.24,.02],[.23,3.3,0],mat('#c6323f'));cube(g,[.38,.24,.02],[.23,3.06,0],mat('#eee8d9'));}
  reserve(x,z,.6);
 }
 // Lit pavilion and bunting framing the field, outside road and audience lanes.
 for(const x of [11.4,28.6]){cyl(paradeEnvironment,.10,5,8,[x,2.75,89],pole);}
 const banner=new THREE.Mesh(new THREE.PlaneGeometry(12,1.2),new THREE.MeshBasicMaterial({map:labelTexture('JR AUDIO'),color:'#ffd7a2',side:THREE.DoubleSide,toneMapped:false}));
 banner.position.set(20,4.2,89);banner.rotation.y=Math.PI;paradeEnvironment.add(banner);
 for(let i=0;i<23;i++){
  const x=10+i*.90,y=5.2-Math.sin(i/22*Math.PI)*.7;
  const bulb=new THREE.Mesh(new THREE.SphereGeometry(.055,6,5),warm);bulb.position.set(x,y,87.8);paradeEnvironment.add(bulb);
 }
 for(const x of [-1,43])for(const z of [84,91]){
  if(!clearSpot(x,z,1.3))continue;
  const g=new THREE.Group();g.position.set(x,0,z);paradeEnvironment.add(g);
  cube(g,[2,.10,.48],[0,.56,0],wood);cube(g,[2,.43,.08],[0,.85,-.23],wood);
  for(const side of [-1,1])cube(g,[.10,.52,.42],[side*.8,.26,0],pole);reserve(x,z,1.3);
 }
 // A few rounded treetops break up the old identical cone silhouettes.
 for(let i=0;i<35;i++){
  const x=-110+rnd()*210,z=-67+rnd()*160;
  if(nearestRoadDistance({x,z})<8||!clearSpot(x,z,2.2)||Math.abs(x-20)<29&&Math.abs(z-73)<28)continue;
  cyl(paradeEnvironment,.14,2.6,7,[x,1.3,z],wood);
  const leaves=mat(i%2?'#315d42':'#487454',.02,.94);
  for(let j=0;j<3;j++){const crown=new THREE.Mesh(new THREE.IcosahedronGeometry(1.25,0),leaves);crown.position.set(x+(j-1)*.65,2.8+(j%2)*.7,z);paradeEnvironment.add(crown);}reserve(x,z,2.4);
 }
 batchStaticEnvironment();
}
function batchStaticEnvironment(){
 // Merge static scenery once; animated skeletons remain separate.
 paradeEnvironment.updateWorldMatrix(true,true);const groups=new Map(),remove=[];
 paradeEnvironment.traverse(o=>{
  if(!o.isMesh||!o.material||Array.isArray(o.material))return;
  for(let a=o.parent;a&&a!==paradeEnvironment;a=a.parent)if(a.userData.dancer)return;
  const m=o.material;
  const key=[m.type,m.color?.getHex(),m.emissive?.getHex(),m.emissiveIntensity,m.roughness,m.metalness,m.map?.uuid,m.side,m.toneMapped,m.transparent,m.opacity].join(':');
  if(!groups.has(key))groups.set(key,{material:m,geometries:[]});
  const geo=o.geometry.clone().applyMatrix4(o.matrixWorld);groups.get(key).geometries.push(geo);remove.push(o);
 });
 for(const o of remove){o.parent.remove(o);o.geometry.dispose();}
 for(const {material,geometries} of groups.values()){
  const geometry=mergeGeometries(geometries,false);geometries.forEach(g=>g.dispose());
  if(!geometry)continue;const mesh=new THREE.Mesh(geometry,material);mesh.receiveShadow=true;mesh.castShadow=true;paradeEnvironment.add(mesh);
 }
}

function buildParadeMap(){
 const ground=new THREE.Mesh(new THREE.PlaneGeometry(265,250),mat('#456a4a',.01,.98));ground.rotation.x=-Math.PI/2;ground.position.y=-.06;paradeEnvironment.add(ground);
 roadPaths.forEach(buildRoad);
 // Lapangan as the optional stop. Keep buildings away from both routes and stage.
 const field=cube(paradeEnvironment,[53,.035,47],[20,.015,73],mat('#547b51',.02,.98));
 const stage=cube(paradeEnvironment,[16,.5,7],[20,.26,86],mat('#232d39'));
 cube(paradeEnvironment,[16,.9,.30],[20,1,89.4],mat('#617da6'));
 const roof=mat('#453b49'),walls=[mat('#bb876b'),mat('#e2b78d'),mat('#8ba5bc')],leaf=mat('#366748'),trunk=mat('#5d4238');
 for(let i=0;i<125;i++){
  const x=-113+rnd()*225,z=-73+rnd()*175,rad=4.6;
  if(nearestRoadDistance({x,z})<11 || Math.abs(x-20)<29&&Math.abs(z-72)<29 ||!clearSpot(x,z,rad))continue;
  const house=new THREE.Group();house.position.set(x,0,z);
  const w=3.3+rnd()*1.6,d=2.9+rnd()*1.4;
  cube(house,[w,2.5,d],[0,1.25,0],walls[i%walls.length]);
  for(const side of [-1,1]){const cap=cube(house,[w*.57,.13,d+.45],[side*w*.245,2.85,0],roof);cap.rotation.z=-side*.35;}
  cube(house,[.68,1.62,.06],[-w*.25,.81,d/2+.04],mat('#534237'));
  cube(house,[w+.35,.10,.65],[0,.08,d/2+.25],bedMat);
  cube(house,[.75,.9,.045],[.55,1.4,d/2+.025],mat('#efbc72',.05,.6,{emissive:'#dc8b36',emissiveIntensity:.4}));
  cube(house,[.035,.92,.06],[.55,1.4,d/2+.05],dark);cube(house,[.77,.035,.06],[.55,1.4,d/2+.05],dark);
  paradeEnvironment.add(house);reserve(x,z,5.6);
 }
 for(let i=0;i<155;i++){
  const x=-115+rnd()*228,z=-75+rnd()*181;
  if(nearestRoadDistance({x,z})<6.5 || Math.abs(x-20)<29&&Math.abs(z-72)<29||!clearSpot(x,z,2.6))continue;
  const tree=new THREE.Group();tree.position.set(x,0,z);
  cyl(tree,.16,2.2,7,[0,1.1,0],trunk);
  const cone=new THREE.Mesh(new THREE.ConeGeometry(1.35,3.3,7),leaf);cone.position.y=3.40;tree.add(cone);
  paradeEnvironment.add(tree);reserve(x,z,2.9);
 }
 // Spectators on the roadside, but at least >4m from road center and not overlapping houses/trees.
 const colors=['#ef9c76','#4b8ed7','#dfa34d','#bb91e8','#68b4a7'];
 for(let i=0;i<185;i++){
  const path=routePaths.village,t=(i+.5)/185,p=path.getPointAt(t),tan=path.getTangentAt(t),normal=new THREE.Vector3(-tan.z,0,tan.x);
  const s=i%2?1:-1;const x=p.x+normal.x*s*(4.8+rnd()*1.8),z=p.z+normal.z*s*(4.8+rnd()*1.8);
  if(nearestRoadDistance({x,z})<3.8||!clearSpot(x,z,.48)||Math.abs(x-20)<30&&Math.abs(z-64)<20)continue;
  const watcher=makePerson(paradeEnvironment,x,z,colors[i%colors.length]);watcher.rotation.y=Math.atan2(p.x-x,p.z-z);reserve(x,z,.85);crowdStatic.push(watcher);
 }
 // Parallel approach lanes keep people separated; entry/exit road stays clear.
 for(let row=0;row<5;row++)for(let col=0;col<13;col++){
  const x=7+col*2.05,z=76+row*1.8,i=row*13+col;
  const target={x,z:71+row*1.8};
  if(x>12&&x<29&&z>82)continue;
  if(!clearSpot(x,z,.6)||nearestRoadDistance(target)<4.8)continue;
  const fan=makePerson(paradeEnvironment,x,z,colors[i%colors.length],.84+(.025*(i%3)));
  fan.rotation.y=Math.atan2(FIELD_STOP.x-x,FIELD_STOP.z-z);
  fan.userData.origin={x,z};fan.userData.target=target;fan.userData.approach=0;
  fieldAudience.push(fan);reserve(x,z,.70);
 }
 addEnvironmentDetails();

}
buildParadeMap();
function route(){return routePaths[state.routeChoice];}
function placeParadeTruck(dt=0){
 const path=route(),t=Math.min(1,Math.max(0,state.paradeT)),p=path.getPointAt(t),tan=path.getTangentAt(t);
 const yaw=Math.atan2(tan.x,tan.z),delta=angleDelta(mobileRoot.rotation.y,yaw);
 mobileRoot.position.set(p.x,.02,p.z);
 mobileRoot.rotation.y=dt?mobileRoot.rotation.y+delta*(1-Math.exp(-10*dt)):yaw;
 const look=path.getTangentAt(Math.min(1,t+Math.max(.004,currentSpeed*.18/paradeLengths[state.routeChoice])));
 const bend=angleDelta(yaw,Math.atan2(look.x,look.z));
 smoothedSteer=damp(smoothedSteer,THREE.MathUtils.clamp(bend*1.7,-.42,.42),8,dt||1);
 for(const wheel of steeringPivots)wheel.rotation.y=smoothedSteer;
}
function setRoadsideAudienceAnchor(){
 const near=crowdStatic.slice().sort((a,b)=>a.position.distanceToSquared(mobileRoot.position)-b.position.distanceToSquared(mobileRoot.position))[0];
 if(near)junctionState.cameraAnchor=new THREE.Vector3(near.position.x,2.4,near.position.z);
 else junctionState.cameraAnchor=new THREE.Vector3(-45,2.5,-45);
}
function enterParade(){
 garageEnvironment.visible=false;paradeEnvironment.visible=true;controls.enabled=false;controls.autoRotate=false;
 currentSpeed=0;state.paradeRunning=true;state.fieldStopped=false;state.paradeT=0;state.routeChoice='village';junctionState.fieldRequested=false;junctionState.cameraAnchor=null;
 state.paradeCam='follow';clearActivePointers();controls.enabled=false;controls.enablePan=false;
 document.querySelectorAll('[data-parade-camera]').forEach(b=>b.classList.toggle('active',b.dataset.paradeCamera==='follow'));
 placeParadeTruck();lastVehicleWorldPos.copy(mobileRoot.position);lastFreeTruckPos.copy(mobileRoot.position);lastFreeTruckYaw=mobileRoot.rotation.y;
 $('viewer-title').textContent=VEHICLES[state.vehicle].name+' · Kampung Horeg';
 $('overlay-mode').textContent='AUTO PARADE · RUTE BERCABANG';$('parade-pause').textContent='⏸ Pause';
 updateRouteControls();toast('Auto Parade berjalan terus dalam loop. Pilih Lapangan jika ingin berhenti.');
}
function leaveParade(){
 currentSpeed=0;smoothedSteer=0;for(const wheel of steeringPivots)wheel.rotation.y=0;
 state.paradeRunning=false;
 state.fieldStopped=false;
 clearActivePointers();
 controls.enabled=false;
 controls.autoRotate=false;
 mobileRoot.position.set(0,0,0);
 mobileRoot.rotation.set(0,0,0);
 garageEnvironment.visible=true;
 paradeEnvironment.visible=false;
 $('overlay-mode').textContent='JR AUDIO · HOREG LAB';
 $('parade-player').pause();
 junctionState.cameraAnchor=null;
 setCamera('perspective');
 $('viewer-title').textContent=VEHICLES[state.vehicle].name+' · Garasi 3D';
}
function updateRouteControls(){
 $('route-choice').value=(junctionState.fieldRequested||state.routeChoice==='field')?'field':'village';
 $('route-progress').textContent=state.fieldStopped?'Berhenti di lapangan. Penonton mendekat. Klik Jalan lagi untuk kembali ke loop.':state.routeChoice==='field'?'Menuju Lapangan Horeg…':state.routeChoice==='exit'?'Keluar lapangan menuju jalan utama…':junctionState.fieldRequested?'Tujuan lapangan dipilih — kendaraan menuju persimpangan.':'Loop kampung berjalan tanpa henti (lurus, belok kanan & kiri).';
 $('continue-route').textContent='▶ Jalan lagi — kembali ke loop';
 $('continue-route').hidden=!state.fieldStopped;
}
function updateParadeCamera(dt){
 const dir=mobileRoot.rotation.y;
 if(state.paradeCam==='free'){
  // Camera ORBIT bebas: gerakkan kamera/target hanya sebesar perpindahan truk.
  // Sudut yang dipilih pemain TIDAK ditimpa oleh posisi Follow setiap frame.
  const delta=mobileRoot.position.clone().sub(lastFreeTruckPos);
  // Pertahankan sudut pilihan pemain RELATIF terhadap arah kendaraan ketika belok.
  const yawDelta=Math.atan2(Math.sin(dir-lastFreeTruckYaw),Math.cos(dir-lastFreeTruckYaw));
  const offset=camera.position.clone().sub(controls.target).applyAxisAngle(new THREE.Vector3(0,1,0),yawDelta);
  controls.target.add(delta);
  camera.position.copy(controls.target).add(offset);
  lastFreeTruckPos.copy(mobileRoot.position);lastFreeTruckYaw=dir;
  controls.enabled=true;
  controls.update();
  return;
 }
 controls.enabled=false;
 if(state.paradeCam==='audience'){
  if(!junctionState.cameraAnchor)setRoadsideAudienceAnchor();
  camera.position.lerp(junctionState.cameraAnchor,1-Math.exp(-6*dt));
  camera.lookAt(mobileRoot.position.clone().add(new THREE.Vector3(0,2.35,0)));
  return;
 }
 const offset=state.paradeCam==='drone'?new THREE.Vector3(0,23,-.01):
  state.paradeCam==='side'?new THREE.Vector3(15,6,-4):
  state.paradeCam==='rear'?new THREE.Vector3(0,5,-17):new THREE.Vector3(8,7,-15);
 if(state.paradeCam!=='drone')offset.applyAxisAngle(new THREE.Vector3(0,1,0),dir);
 camera.position.lerp(mobileRoot.position.clone().add(offset),1-Math.exp(-6*dt));
 camera.lookAt(mobileRoot.position.clone().add(new THREE.Vector3(0,2.4,0)));
}
$('route-choice').addEventListener('change',e=>{
 junctionState.fieldRequested=e.target.value==='field';
 if(!junctionState.fieldRequested&&state.routeChoice==='field')toast('Sudah berada di jalan lapangan. Selesaikan kunjungan, lalu lanjut loop.');
 else toast(junctionState.fieldRequested?'Lapangan dijadwalkan di persimpangan berikutnya.':'Parade kembali fokus ke loop kampung.');
 updateRouteControls();
});
$('continue-route').addEventListener('click',()=>{
 if(!state.fieldStopped)return;
 state.routeChoice='exit';state.fieldStopped=false;state.paradeT=0;state.paradeRunning=true;
 junctionState.fieldRequested=false;junctionState.cameraAnchor=null;
 placeParadeTruck();$('parade-pause').textContent='⏸ Pause';updateRouteControls();
 toast('Jalan lagi! Truk keluar dari lapangan dan menyambung loop kampung.');
});
$('launch-parade').addEventListener('click',()=>setStage(5));
$('parade-back').addEventListener('click',()=>setStage(2));
$('parade-pause').addEventListener('click',()=>{if(state.fieldStopped){$('continue-route').click();return;}state.paradeRunning=!state.paradeRunning;$('parade-pause').textContent=state.paradeRunning?'⏸ Pause':'▶ Lanjut';});
$('parade-speed').addEventListener('input',e=>{state.paradeSpeed=Number(e.target.value);$('parade-speed-label').textContent=state.paradeSpeed+' m/s';});
document.querySelectorAll('[data-beam-mode]').forEach(select=>select.addEventListener('change',()=>{
 state.beamMode=select.value==='white'?'white':'rainbow';
 document.querySelectorAll('[data-beam-mode]').forEach(other=>other.value=state.beamMode);
 toast(state.beamMode==='white'?'Moving beam: putih.':'Moving beam: warna-warni.');
}));
document.querySelectorAll('[data-parade-camera]').forEach(b=>b.addEventListener('click',()=>{
 state.paradeCam=b.dataset.paradeCamera;
 if(state.paradeCam==='audience')setRoadsideAudienceAnchor();
 if(state.paradeCam==='free'){
  clearActivePointers();controls.enabled=true;controls.enablePan=true;controls.minDistance=2.8;controls.maxDistance=75;
  controls.maxPolarAngle=Math.PI/2-.035;
  camera.position.copy(mobileRoot.position).add(new THREE.Vector3(9,5,10));
  controls.target.copy(mobileRoot.position).add(new THREE.Vector3(0,2,0));
  controls.saveState();controls.reset();
  lastFreeTruckPos.copy(mobileRoot.position);lastFreeTruckYaw=mobileRoot.rotation.y;
 }else{clearActivePointers();controls.enabled=false;}
 document.querySelectorAll('[data-parade-camera]').forEach(x=>x.classList.toggle('active',x===b));
}));
const musicInput=$('music-file'),audio=$('parade-player');
const builtInTracks=[
 {name:'Demo: Kampung Beat (original)',url:'/assets/horeg/music/kampung-beat.ogg'},
 {name:'Demo: Parade Malam (original)',url:'/assets/horeg/music/parade-malam.ogg'}
];
let tracks=[...builtInTracks],playlistIndex=0,uploads=[],analyser=null,audioContext=null,audioSource=null,frequency=null;
audio.preload='auto';
function renderPlaylist(){
 const el=$('music-track');el.innerHTML='';tracks.forEach((t,i)=>{
  const opt=document.createElement('option');opt.value=String(i);opt.textContent=t.name;el.appendChild(opt);
 });el.value=String(playlistIndex);
}
async function selectTrack(index,autoplay=false){
 playlistIndex=(index+tracks.length)%tracks.length;
 const t=tracks[playlistIndex];audio.src=t.url;
 $('music-track').value=String(playlistIndex);$('music-name').textContent=t.name;
 if(autoplay)try{await audio.play();}catch(err){toast('Klik ▶ Play pada pemutar untuk mengaktifkan audio.');}
}
renderPlaylist();selectTrack(0);
$('music-track').addEventListener('change',e=>selectTrack(Number(e.target.value),true));
musicInput.addEventListener('change',async e=>{
 const files=[...(e.target.files||[])].filter(f=>f.type.startsWith('audio/')||/\.(mp3|wav|ogg|m4a|flac)$/i.test(f.name));
 if(!files.length)return;
 uploads.forEach(x=>URL.revokeObjectURL(x));uploads=[];
 tracks=[...builtInTracks,...files.map(f=>{const u=URL.createObjectURL(f);uploads.push(u);return {name:'Upload: '+f.name,url:u};})];
 renderPlaylist();await selectTrack(builtInTracks.length,true);
});
audio.addEventListener('ended',()=>{
 // Loop the current song or automatically continue through the playlist, with no silent stop.
 selectTrack($('music-repeat').value==='one'?playlistIndex:playlistIndex+1,true);
});

let lastFrontDisplayUpdate=-Infinity;
function updateVehicleEffects(time){
 const blink=state.fieldStopped && state.stage===5 ? (Math.sin(time*16)>0?1:0) : 0;
 const head=state.night?1:0;const tail=state.night?.55:0;const brake=state.fieldStopped?1:(state.stage===5&&(!state.paradeRunning||state.routeChoice==='field'&&(1-state.paradeT)*paradeLengths.field<14)?1:0);
 for(const m of headlightMats)m.color.set(head?0xfff4ba:0x282018);
 for(const m of tailLightMats)m.color.set(tail?0xa61919:0x240606);
 for(const m of brakeLightMats)m.color.set(brake?0xff2020:0x300707);
 for(const m of hazardLightMats)m.color.set(blink?0xffb030:0x2b1404);
 for(const spot of frontSpotLights){spot.intensity=head?65:0;spot.distance=42;spot.angle=Math.PI/8;}
 if(time-lastFrontDisplayUpdate>.055){
  lastFrontDisplayUpdate=time;
  if(frontDisplayCanvas&&frontDisplayTexture){const ctx=frontDisplayCanvas.getContext('2d');drawFrontBanner(ctx,frontDisplayCanvas.width,frontDisplayCanvas.height,time);frontDisplayTexture.needsUpdate=true;}
  if(frontEyeCanvas&&frontEyeTexture){const ctx=frontEyeCanvas.getContext('2d');drawEyes(ctx,frontEyeCanvas.width,frontEyeCanvas.height,time);frontEyeTexture.needsUpdate=true;}
 }
}

audio.addEventListener('play',()=>{
 try{if(!audioContext){audioContext=new (window.AudioContext||window.webkitAudioContext)();
  audioSource=audioContext.createMediaElementSource(audio);analyser=audioContext.createAnalyser();
  analyser.fftSize=256;frequency=new Uint8Array(analyser.frequencyBinCount);audioSource.connect(analyser);analyser.connect(audioContext.destination);}
  audioContext.resume();
 }catch(err){console.warn('Visualizer audio tidak aktif:',err);}
});
window.addEventListener('pagehide',()=>uploads.forEach(u=>URL.revokeObjectURL(u)),{once:true});
let lastWallTime=-Infinity;
function animateAudioVisuals(time,dt){
 let bass=.10,treble=.1;
 if(analyser&&frequency&&!audio.paused){analyser.getByteFrequencyData(frequency);
  bass=Array.from(frequency.slice(1,12)).reduce((s,n)=>s+n,0)/(11*255);
  treble=Array.from(frequency.slice(28,64)).reduce((s,n)=>s+n,0)/(36*255);
 }else if(state.stage===5){bass=.13+Math.sin(time*3)*.08;treble=.12+Math.sin(time*7)*.06;}
 const playing=!audio.paused&&!!analyser;
 danceAudio.energy=damp(danceAudio.energy,playing?bass+treble*.35:0,7,dt);
 danceAudio.average=damp(danceAudio.average,bass,.9,dt);
 if(playing&&bass>Math.max(.10,danceAudio.average*1.22)&&bass>danceAudio.previous+.015&&time-danceAudio.lastOnset>.28){
  const interval=time-danceAudio.lastOnset;
  if(interval>.32&&interval<.85)danceAudio.bpm=damp(danceAudio.bpm,60/interval,2,.25);
  danceAudio.lastOnset=time;
 }
 danceAudio.previous=bass;danceAudio.bass=bass;danceAudio.treble=treble;
 if(playing)danceAudio.beat+=dt*danceAudio.bpm/60;
 let index=0;
 for(const item of state.items){
  if(!['led','strobe','moving','spot','wall'].includes(item.key))continue;
  const obj=partMeshes.get(item.id);if(!obj)continue;
  const level=Math.min(2.4,.25+bass*2.5+treble*.8),pulse=Math.max(.08,Math.min(1,bass*1.8));
  obj.traverse(m=>{if(!m.isMesh||!m.material)return;
   if(m.material.emissive&&m.material.emissive.getHex()!==0)m.material.emissiveIntensity=level;
   if(item.key==='led'&&m.material.isMeshBasicMaterial&&m.material.color&&!m.material.map)m.material.color.setHSL((time*.12+index*.14)%1,.90,.30+Math.min(.34,bass*.4));

  });
  if(obj.userData.glow){obj.userData.glow.material.opacity=.22+Math.min(.68,level*.25);obj.userData.glow.scale.multiplyScalar(1);}
  if(obj.userData.strobePanels){
   const palette=['#ff005a','#00ef78','#6412ff','#00bfff','#ff7a00','#ff00ef'];
   obj.userData.strobePanels.forEach((panel,i)=>{
    const flash=Math.sin(time*(7+bass*8)+i*2.1+index)>.18;
    panel.material.color.set(palette[(i+index+Math.floor(time/4))%palette.length]).multiplyScalar(flash?1:.035);
   });
   if(obj.userData.glow){obj.userData.glow.material.color.copy(obj.userData.strobePanels[1].material.color);obj.userData.glow.material.opacity=.25;}
  }
  if(obj.userData.beam){
   const u=obj.userData.beam.material.uniforms;
   u.strength.value=.21+Math.min(.24,bass*.30+treble*.13);
   if(state.beamMode==='white')u.tint.value.set('#ffffff');else u.tint.value.setHSL((time*.035+index*.193)%1,1,.39);
   obj.userData.movingLens.material.color.copy(u.tint.value);
   if(obj.userData.glow){obj.userData.glow.material.color.copy(u.tint.value);obj.userData.glow.material.opacity=.42;}
  }
  if(obj.userData.spotHead){
   const head=obj.userData.spotHead;
   head.rotation.y=Math.sin(time*.62+index*1.7)*.92;
   head.rotation.x=-.22+Math.cos(time*.78+index)*.31;
   const color=obj.userData.spotBeam.material.uniforms.tint.value; color.setHSL((index*.19+time*.026)%1,1,.40);
   obj.userData.spotBeam.material.uniforms.strength.value=.17+bass*.2;
   obj.userData.spotLens.material.color.copy(color);
   if(obj.userData.glow)obj.userData.glow.material.color.copy(color);
  }
  if(item.key==='moving'&&obj.userData.movingHead){obj.userData.movingHead.rotation.y=Math.sin(time*.48+index)*1.05;
   obj.userData.movingHead.rotation.x=-.20+Math.sin(time*.65+index)*.43;}
  if(item.key==='wall'&&obj.userData.wallCanvas&&time-lastWallTime>.055){
   lastWallTime=time;
   const ctx=obj.userData.wallCanvas.getContext('2d'),w=640,h=360,t=time;
   ctx.fillStyle='#050c1b';ctx.fillRect(0,0,w,h);
   for(let i=0;i<22;i++){
    const energy=.17+Math.abs(Math.sin(t*1.8+i*.47))*(.17+pulse*.55);
    ctx.fillStyle=`hsl(${(t*45+i*15)%360},92%,${25+energy*32}%)`;
    ctx.fillRect(i*w/22,h*(1-energy),w/22-3,h*energy);
   }
   ctx.fillStyle='rgba(3,8,19,.9)';ctx.fillRect(0,66,w,218);
   ctx.textAlign='center';ctx.shadowColor='#ff4155';ctx.shadowBlur=18;
   ctx.fillStyle='#f4f8ff';ctx.font='bold 83px Arial';ctx.fillText('JR AUDIO',w/2,164);
   ctx.shadowBlur=0;ctx.fillStyle='#ff6678';ctx.font='bold 27px Arial';ctx.fillText('SOUND • LIGHTING • HOREG',w/2,205);
   ctx.save();ctx.beginPath();ctx.rect(15,232,610,48);ctx.clip();
   ctx.fillStyle='#111a2c';ctx.fillRect(15,232,610,48);
   const marquee=' ✦ JR AUDIO ✦ HOREG SOUND & LIGHTING ✦ INFO PEMESANAN LANGSUNG DM ✦ ';
   ctx.font='bold 23px Arial';ctx.fillStyle='#ffe483';ctx.textAlign='left';
   const tw=ctx.measureText(marquee).width;const ox=-(t*115%tw);
   for(let x=ox;x<640;x+=tw)ctx.fillText(marquee,x,264);
   ctx.restore();
   obj.userData.wallTexture.needsUpdate=true;
  }
  index++;
 }
}
// ========= END PARADE =========
// Klik item pada model untuk memilih. Rotasi kamera tetap menggunakan drag OrbitControls.
const raycaster=new THREE.Raycaster(),mouse=new THREE.Vector2();let down=null;
renderer.domElement.addEventListener('pointerdown',e=>{down={x:e.clientX,y:e.clientY};});
renderer.domElement.addEventListener('pointerup',e=>{if(state.stage===5)return;if(!down||Math.hypot(e.clientX-down.x,e.clientY-down.y)>6)return;down=null;const b=renderer.domElement.getBoundingClientRect();mouse.set((e.clientX-b.left)/b.width*2-1,-((e.clientY-b.top)/b.height*2-1));raycaster.setFromCamera(mouse,camera);const hits=raycaster.intersectObjects(partRoot.children,true);const found=hits.find(h=>h.object.userData.partId);if(found){state.selected=found.object.userData.partId;const selected=state.items.find(i=>i.id===state.selected);if(selected&&PARTS[selected.key].requiresRig){state.tab=selected.key==='wall'?'video':'light';state.rigSlot=selected.key==='wall'?null:{col:selected.col,row:selected.row};}redrawParts();updateInstalled();catalogRender();renderRigGrid();}});
function resize(){const w=Math.max(1,host.clientWidth),h=Math.max(1,host.clientHeight);camera.aspect=w/h;camera.updateProjectionMatrix();renderer.setSize(w,h,false);}const observer=new ResizeObserver(resize);observer.observe(host);
load();buildVehicle();updateLight();renderUI();setStage(1);document.querySelectorAll('.swatch').forEach(b=>b.classList.toggle('active',b.dataset.color===state.color));resize();setCamera('perspective');window.__horegGarageReady=true;$('garage-loading').remove();toast('Garage v0.8.0 siap: sound satu baris, rigging belakang, Auto Parade.');
let frame=0;const clock=new THREE.Clock();
function tick(){
 frame=requestAnimationFrame(tick);const dt=Math.min(.06,clock.getDelta()),tnow=performance.now()/1000;
 animateAudioVisuals(tnow,dt);
 if(state.stage===5){
  const remaining=(1-state.paradeT)*paradeLengths[state.routeChoice];
  let desired=state.paradeRunning?state.paradeSpeed:0;
  if(state.routeChoice==='field')desired=Math.min(desired,Math.sqrt(Math.max(0,2*2.4*remaining)));
  const tangent=route().getTangentAt(Math.min(1,state.paradeT));
  const ahead=route().getTangentAt(Math.min(1,state.paradeT+5/paradeLengths[state.routeChoice]));
  const curvature=Math.abs(angleDelta(Math.atan2(tangent.x,tangent.z),Math.atan2(ahead.x,ahead.z)));
  desired=Math.min(desired,Math.max(3,11/(1+curvature*4)));
  currentSpeed=damp(currentSpeed,desired,desired<currentSpeed?3.5:1.4,dt);
  if(state.fieldStopped)currentSpeed=0;
  if(currentSpeed>.015&&!state.fieldStopped){
   const previous=state.paradeT;
   state.paradeT+=dt*currentSpeed/paradeLengths[state.routeChoice];
   if(state.routeChoice==='village'){
    if(junctionState.fieldRequested&&previous<entryAt&&state.paradeT>=entryAt){
     const excess=(state.paradeT-entryAt)*paradeLengths.village;
     state.routeChoice='field';state.paradeT=excess/paradeLengths.field;junctionState.fieldRequested=false;
     if(state.paradeCam==='audience')junctionState.cameraAnchor=new THREE.Vector3(-32,2.4,25);
     updateRouteControls();toast('Belok menuju Lapangan Horeg!');
    }else if(state.paradeT>=1)state.paradeT%=1;
   }else if(state.paradeT>=1){
    if(state.routeChoice==='field'){
     state.paradeT=1;currentSpeed=0;state.fieldStopped=true;state.paradeRunning=false;
     if(state.paradeCam==='audience')junctionState.cameraAnchor=new THREE.Vector3(12,2.35,73);
     $('parade-pause').textContent='▶ Jalan lagi';updateRouteControls();toast('Sampai di lapangan! Putar musik untuk mulai joget.');
    }else{
     const excess=(state.paradeT-1)*paradeLengths.exit;
     state.routeChoice='village';state.paradeT=exitAt+excess/paradeLengths.village;state.fieldStopped=false;
     junctionState.cameraAnchor=null;updateRouteControls();toast('Kembali ke loop kampung.');
    }
   }
   for(const wheel of wheelPivots)wheel.rotation.x+=dt*currentSpeed/.48;
   placeParadeTruck(dt);
  }
  animateSpectators(tnow,dt);updateParadeCamera(dt);
 }else{controls.update();garageOcclusion();}
 animateOperator(tnow,dt);updateVehicleEffects(tnow);renderer.render(scene,camera);
}tick();
window.addEventListener('pagehide',()=>{cancelAnimationFrame(frame);observer.disconnect();controls.dispose();renderer.dispose();},{once:true});
