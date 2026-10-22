<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>PHP memory lab</title>
<style>body{margin:0;background:#101827;color:#eef2ff;font:18px/1.6 system-ui}main{max-width:900px;margin:auto;padding:48px 24px}h1{font-size:48px;line-height:1.1}small,a{color:#a5d9ff}.card{background:#1c2940;padding:24px;border-radius:16px;margin:24px 0}button{padding:12px 18px;border:0;border-radius:8px;background:#9ce6cd;color:#102820;font:inherit;cursor:pointer}select{font:inherit;padding:10px;max-width:100%}.state{border-left:4px solid #9ce6cd;padding:16px;margin-top:16px;min-height:100px}footer{font-size:15px}strong{color:#9ce6cd}
.diagram{margin-top:24px;padding:16px;background:#101827;border:1px solid #40516b;border-radius:12px}.diagram-canvas{overflow-x:auto}.diagram svg{display:block;width:100%;min-width:600px;height:auto}.scroll-hint{display:none}.diagram text{fill:#eef2ff;font-family:system-ui}.diagram .caption{font-size:13px;fill:#bac9de}.diagram .box{fill:#1c2940;stroke:#9ce6cd;stroke-width:2}.diagram .slot{fill:#254f47;stroke:#9ce6cd}.diagram .empty{fill:#172136;stroke:#8a9ab3;stroke-dasharray:5 4}.diagram .danger{fill:#512d35;stroke:#ffacac}.diagram .edge{stroke:#9ce6cd;stroke-width:3;fill:none}.diagram[data-warning="true"] .edge{stroke:#ffacac;stroke-dasharray:7 5}.diagram[data-warning="true"] #arrow-tip{fill:#ffacac}.diagram .legend{font-size:14px;margin:8px 0 0;color:#bac9de}button:disabled{opacity:.45;cursor:default}button:focus-visible,select:focus-visible{outline:3px solid #a5d9ff;outline-offset:4px}
@media(max-width:540px){.card{padding:16px}.diagram{padding:6px}h1{font-size:36px}main{padding:24px 12px}.diagram .legend{padding:8px}.scroll-hint{display:block;font-size:14px;color:#bac9de}}
</style></head>
<body><main><small>LEARN / PHP2SHELL / OCTOBER 2026</small><h1>When memory outlives its owner.</h1><p>A small Laravel app exploring two PHP engine vulnerabilities. The interactive diagram below models memory ownership; it does not corrupt memory or send attack requests.</p>
<div class="card"><strong>Runtime: PHP {{ PHP_VERSION }}</strong><p>This app uses a patched PHP runtime. A development server is not a reproduction of Apache mod_php or PHP-FPM.</p></div>
<div class="card"><h2>Step through the failure</h2><label for="bug">Choose a vulnerability</label><br><select id="bug"><option value="oom">CVE-2026-109618: out-of-memory corruption</option><option value="stale">CVE-2026-109619: stale global state</option></select>
<figure class="diagram" id="diagram" data-warning="false">
<div class="diagram-canvas" tabindex="0" role="region" aria-label="Memory diagram; scroll horizontally on small screens">
<svg viewBox="0 0 760 270" role="img" aria-labelledby="diagram-title diagram-desc">
<title id="diagram-title">Out-of-memory corruption: step 1 of 4</title>
<desc id="diagram-desc">PHP initialization references a partially initialized request object. Two fields are ready and two are not yet initialized.</desc>
<defs><marker id="arrow" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="8" markerHeight="8" orient="auto-start-reverse"><path id="arrow-tip" d="M 0 0 L 10 5 L 0 10 z" fill="#9ce6cd"/></marker></defs>
<text x="24" y="28" id="phase-label" font-size="17">Request initialization</text>
<rect class="box" x="24" y="64" width="210" height="100" rx="12"/>
<text x="129" y="100" text-anchor="middle" font-size="18" id="owner-label">PHP initialization</text>
<text class="caption" x="129" y="129" text-anchor="middle" id="owner-detail">Internal request state</text>
<path class="edge" d="M 234 114 H 420" marker-end="url(#arrow)"/>
<text class="caption" x="329" y="88" text-anchor="middle" id="reference-label">Reference</text>
<rect class="box" id="memory-box" x="434" y="52" width="302" height="132" rx="12"/>
<text x="585" y="83" text-anchor="middle" font-size="17" id="memory-label">Request memory</text>
<g id="memory-slots">
<rect class="slot" x="452" y="100" width="58" height="38" rx="5"/><rect class="slot" x="521" y="100" width="58" height="38" rx="5"/><rect class="slot empty" x="590" y="100" width="58" height="38" rx="5"/><rect class="slot empty" x="659" y="100" width="58" height="38" rx="5"/>
</g>
<text class="caption" x="585" y="164" text-anchor="middle" id="memory-detail">2 fields ready / 2 unfinished</text>
<text x="380" y="225" text-anchor="middle" font-size="16" id="outcome-label">Initialization is still in progress</text>
<text class="caption" x="380" y="251" text-anchor="middle">Conceptual model · blocks represent internal data fields</text>
</svg>
</div><p class="scroll-hint">Scroll the diagram sideways to follow the reference.</p>
<figcaption class="legend">Solid green: initialized data. Dashed outline: unfinished or freed data. Pink: invalid state being used.</figcaption>
</figure>
<noscript><p>Enable JavaScript to advance the diagram. The initial memory state is shown above.</p></noscript>
<div class="state" id="state" role="status" aria-live="polite"></div><p><button id="next">Next step</button> <button id="reset">Reset</button></p></div>
<div class="card"><h2>Why can a memory bug become code execution?</h2><p>PHP's interpreter is native software. If it treats invalid bytes as a trusted internal object, it can make decisions using attacker-influenced data. Depending on the reachable bug and server configuration, that can cause a crash, disclose memory, or redirect execution.</p><p>A successful attacker runs with the PHP worker's permissions. That can expose database credentials, customer data, sessions, or writable site files. It does not automatically grant administrator access to the operating system.</p></div>
<div class="card"><h2>What this means for WordPress and Laravel</h2><p>Both rely on the PHP engine. Some demonstrated paths execute before application code, so application login and input validation cannot cover them. Server configuration affects exploitability; using PHP alone does not prove a working attack exists against a particular site.</p><p><strong>For site owners:</strong> update PHP, restart all workers, and verify the web-serving runtime. Updating WordPress, plugins, or Composer dependencies alone does not patch PHP.</p><p><strong>For visitors:</strong> PHP runs on the website's server. Visiting a PHP page does not install vulnerable PHP on your computer. A compromised site could still steal information you submit, abuse your session, or serve malicious content.</p></div>
<footer><p>Primary sources: <a href="https://www.php.net/archive/2026.php#2026-10-21-5">PHP security announcement</a>, <a href="https://github.com/php/php-src/security/advisories/GHSA-w8hw-w9fx-j79w">OOM advisory</a>, <a href="https://github.com/php/php-src/security/advisories/GHSA-6wmf-4rv5-p3rp">stale-state advisory</a>.</p></footer>
</main><script>
const sequences={oom:['1 / Allocate: PHP starts constructing internal request data. Some fields are not ready yet.','2 / Interrupt: an allocation fails. The old error path abandons initialization while incomplete state remains reachable.','3 / Clean up: shutdown treats that incomplete state as valid. Invalid memory can be read or used.','4 / Consequence: depending on the path and environment, the outcome can include remote code execution. The patch changes out-of-memory handling so shutdown does not process this incomplete state.'],stale:['1 / Request A: a persistent worker keeps a reference to data owned by this request.','2 / End request A: the data is freed, but a missed reset leaves a stale reference. Think of an old room number after the room has been reassigned.','3 / Request B: the same worker accesses the old reference before initialization resets it. The bytes may now belong to something else.','4 / Consequence: interpreting those bytes as the old object can disclose memory or corrupt execution. The fix repairs the lifetime and reset handling.']};const diagrams={
oom:[
['Request initialization','PHP initialization','Internal request state','Reference','Request memory','2 fields ready / 2 unfinished','Initialization is still in progress','partial',false],
['Memory allocation fails','Initialization exits','Early exit on OOM','Still reachable','Incomplete object','Unfinished fields remain','The operation exits before the object is ready','partial',true],
['Request shutdown','PHP cleanup','Assumes a valid object','Reads invalid state','Incomplete object','Invalid fields are consumed','Cleanup uses data that was never fully initialized','invalid',true],
['Possible outcome on vulnerable PHP','PHP worker','Worker permissions','Invalid access','Corrupted execution','Crash / disclosure / possible RCE','Patched PHP prevents cleanup from seeing this invalid state','invalid',true]
],
stale:[
['Request A is running','Persistent worker','Global state','Valid reference','Request A memory','Data belongs to request A','The worker retains a reference to request-owned data','valid',false],
['Request A ends','Persistent worker','Reset was missed','Stale reference','Freed memory','Request A no longer owns it','Memory is freed, but the reference survives','freed',true],
['Request B reaches the same worker','Persistent worker','Before normal reset','Uses stale reference','Memory may be reused','Old contents are no longer valid','The same address may now contain unrelated data','invalid',true],
['Possible outcome on vulnerable PHP','PHP worker','Worker permissions','Invalid access','Misinterpreted data','Crash / disclosure / possible RCE','Patched PHP repairs state lifetime and reset handling','invalid',true]
]};
function renderDiagram(){
const frame=diagrams[bug.value][step];
const ids=['phase-label','owner-label','owner-detail','reference-label','memory-label','memory-detail','outcome-label'];
ids.forEach((id,i)=>document.getElementById(id).textContent=frame[i]);
document.getElementById('diagram').dataset.warning=String(frame[8]);
document.getElementById('memory-box').setAttribute('class',frame[7]==='invalid'?'box danger':frame[7]==='freed'?'box empty':'box');
document.querySelectorAll('#memory-slots rect').forEach((slot,i)=>slot.setAttribute('class',frame[7]==='invalid'?'slot danger':frame[7]==='freed'||(frame[7]==='partial'&&i>1)?'slot empty':'slot'));
document.getElementById('diagram-title').textContent=`${bug.value==='oom'?'Out-of-memory corruption':'Stale global state'}: step ${step+1} of 4`;
document.getElementById('diagram-desc').textContent=frame.slice(0,7).join('. ');
}
let step=0;const bug=document.querySelector('#bug'),state=document.querySelector('#state');function render(){state.textContent=sequences[bug.value][step];renderDiagram();document.querySelector('#next').disabled=step===3}document.querySelector('#next').onclick=()=>{step=Math.min(step+1,3);render()};document.querySelector('#reset').onclick=bug.onchange=()=>{step=0;render()};render();
</script></body></html>