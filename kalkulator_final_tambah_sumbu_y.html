<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Kalkulator Titik Diferensial + Sumbu Y</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { padding:18px; }
    #chart { max-width:920px; height:440px; }
    .point-list { max-height:300px; overflow:auto; }
    .warn { color:#b44; font-weight:600; }
    .small-note { font-size:0.9rem; color:#666; }
  </style>
</head>
<body>
  <div class="container">
    <h3>Kalkulator Titik Diferensial</h3>

    <div class="mb-2">
      <input id="fn" class="form-control" placeholder="Masukkan fungsi f(x), contoh: x^3 - 3*x + 1" />
    </div>

    <div class="mb-2 d-flex gap-2 align-items-center">
      <button id="btnCalc" class="btn btn-primary">Cari Titik Diferensial (f'(x)=0)</button>
      <button id="btnY" class="btn btn-secondary d-none">Cari Titik Sumbu Y (f(0))</button>
      <div id="status" class="ms-3"></div>
    </div>

    <div class="mb-3">
      <strong>f(x):</strong> <span id="showFn">-</span><br>
      <strong>f'(x):</strong> <span id="showDeriv">-</span>
      <!-- <div class="small-note">Console (F12) menunjukkan log loader & error.</div> -->
    </div>

    <div class="row">
      <div class="col-md-8">
        <canvas id="chart"></canvas>
      </div>
      <div class="col-md-4">
        <h5>Titik Ditemukan</h5>
        <div id="points" class="point-list list-group mb-2"></div>

        <h6>Titik Sumbu-Y</h6>
        <div class="list-group mb-2">
          <div id="yInterceptBox" class="list-group-item">(0, -)</div>
        </div>

        <div class="small-note">Tips: gunakan format `sin(x)`, `x^2`, `1/x`, `exp(x)`, dll.</div>
      </div>
    </div>
  </div>

  <!-- ============================
       SCRIPT LOADER & APP START
       ============================ -->

  <script>
  // tiny loader that returns a Promise
  function loadScript(src) {
    return new Promise((resolve, reject) => {
      const s = document.createElement('script');
      s.src = src;
      s.async = false;
      s.onload = () => { console.log('Loaded', src); resolve(src); };
      s.onerror = (e) => { console.warn('Failed to load', src); reject(new Error('Failed to load ' + src)); };
      document.head.appendChild(s);
    });
  }

  // load nerdamer files sequentially (local required)
  async function loadNerdamerLocal() {
    const files = ['js/nerdamer.core.js','js/Algebra.js','js/Calculus.js','js/Solve.js'];
    const results = [];
    for (const f of files) {
      try {
        await loadScript(f);
        results.push({file:f, ok:true});
      } catch(e) {
        console.warn(e.message);
        results.push({file:f, ok:false});
      }
    }
    return results;
  }

  // load Chart.js: try local first, else CDN
  async function loadChartLocalOrCdn() {
    try {
      await loadScript('js/chart.umd.min.js');
      return { ok:true, src:'local' };
    } catch(e) {
      console.warn('Local Chart load failed, trying CDN...');
      try {
        await loadScript('https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js');
        return { ok:true, src:'cdn' };
      } catch(e2) {
        console.error('Chart failed to load from CDN too.');
        return { ok:false };
      }
    }
  }

  // main startup: load libs then init app
  (async function startup(){
    const nerdamerRes = await loadNerdamerLocal();
    const chartRes = await loadChartLocalOrCdn();

    initApp({
      hasNerdamer: (typeof nerdamer !== 'undefined'),
      nerdamerFiles: nerdamerRes,
      hasChart: (typeof Chart !== 'undefined'),
      chartSource: chartRes.ok ? chartRes.src : null
    });
  })();
  </script>

  <!-- ============================
       MAIN APP: initApp(config)
       ============================ -->
  <script>
  function initApp(cfg) {
    const statusEl = document.getElementById('status');
    const fnInput = document.getElementById('fn');
    const showFn = document.getElementById('showFn');
    const showDeriv = document.getElementById('showDeriv');
    const btnCalc = document.getElementById('btnCalc');
    const btnY = document.getElementById('btnY');
    const pointsDiv = document.getElementById('points');
    const yBox = document.getElementById('yInterceptBox');
    let chart = null;
    let lastYIntercept = null;

    // show lib status
    let statusHtml = '';
    if (cfg.hasNerdamer) statusHtml += '<span style="color:green"></span>';
    else statusHtml += '<span class="warn"></span>';
    if (cfg.hasChart) statusHtml += '  <span style="color:green"></span>';
    else statusHtml += '  <span class="warn"></span>';
    statusEl.innerHTML = statusHtml;
    console.log('initApp cfg=', cfg);

    // evaluator: prefer nerdamer, else simple JS Function fallback
    function safeEval(expr, scope = {}) {
      if (cfg.hasNerdamer) {
        try { return Number(nerdamer(expr, scope).evaluate().text()); }
        catch (e) { console.warn('nerdamer eval failed for', expr, e); return NaN; }
      }
      // simple fallback (user must use JS math functions)
      try {
        const jsExpr = expr.replace(/\^/g, '**')
                          .replace(/sin\(/g, 'Math.sin(')
                          .replace(/cos\(/g, 'Math.cos(')
                          .replace(/tan\(/g, 'Math.tan(')
                          .replace(/exp\(/g, 'Math.exp(')
                          .replace(/log\(/g, 'Math.log(')
                          .replace(/sqrt\(/g, 'Math.sqrt(')
                          .replace(/abs\(/g, 'Math.abs(');
        const f = new Function('x','with(Math){ return '+jsExpr+' }');
        return Number(f(scope.x));
      } catch (e) {
        console.error('Fallback eval failed for', expr, e);
        return NaN;
      }
    }

    function numericDerivative(expr, x) {
      const h = 1e-6;
      const f1 = safeEval(expr, {x: x + h});
      const f2 = safeEval(expr, {x: x - h});
      if (!isFinite(f1) || !isFinite(f2)) return NaN;
      return (f1 - f2) / (2 * h);
    }

    function trySymbolicDerivative(expr) {
      if (!cfg.hasNerdamer) return null;
      try { return nerdamer(`diff(${expr}, x)`).toString(); }
      catch (e) { console.warn('Symbolic derivative failed:', e); return null; }
    }

    function trySymbolicSolve(derivExpr) {
      if (!cfg.hasNerdamer) return [];
      try { const sols = nerdamer.solve(derivExpr, 'x'); return Array.isArray(sols) ? sols : []; }
      catch (e) { console.warn('Symbolic solve failed:', e); return []; }
    }

    function findCriticalPoints(fnExpr) {
      showDeriv.textContent = '-';
      const symDeriv = trySymbolicDerivative(fnExpr);
      if (symDeriv) {
        showDeriv.textContent = symDeriv;
        const sols = trySymbolicSolve(symDeriv);
        const pts = [];
        if (Array.isArray(sols) && sols.length > 0) {
          sols.forEach(s => {
            try {
              const xnum = Number(nerdamer(s).evaluate().text());
              if (isFinite(xnum)) {
                const y = safeEval(fnExpr, {x: xnum});
                if (isFinite(y)) pts.push({ x: Number(xnum.toFixed(8)), y: Number(y.toFixed(8)) });
              }
            } catch (e) { console.warn('Parsing solution failed:', s, e); }
          });
          // dedupe
          const uniq = []; const seen = {};
          pts.forEach(p=>{ const k=p.x.toFixed(8); if(!seen[k]){seen[k]=1;uniq.push(p);} });
          return { deriv: symDeriv, points: uniq };
        }
      } else {
        showDeriv.textContent = '(symbolic failed - using numeric derivative)';
      }

      // numeric scanning fallback
      const pts = [];
      for (let a = -20; a < 20; a += 0.5) {
        const fa = numericDerivative(fnExpr, a);
        const fb = numericDerivative(fnExpr, a + 0.5);
        if (!isFinite(fa) || !isFinite(fb)) continue;
        if (Math.abs(fa) < 1e-9) {
          const y = safeEval(fnExpr, {x: a});
          if (isFinite(y)) pts.push({ x: Number(a.toFixed(8)), y: Number(y.toFixed(8)) });
        } else if (fa * fb < 0) {
          // bisection
          let left = a, right = a + 0.5, mid, fleft = fa;
          for (let it=0; it<60; it++) {
            mid = (left+right)/2;
            const fm = numericDerivative(fnExpr, mid);
            if (!isFinite(fm)) break;
            if (Math.abs(fm) < 1e-8) break;
            if (fleft * fm <= 0) { right = mid; } else { left = mid; fleft = fm; }
          }
          const y = safeEval(fnExpr, {x: mid});
          if (isFinite(y)) pts.push({ x: Number(mid.toFixed(8)), y: Number(y.toFixed(8)) });
        }
      }
      // dedupe
      const uniq = []; const seen = {};
      pts.forEach(p=>{ const k=p.x.toFixed(8); if(!seen[k]){seen[k]=1;uniq.push(p);} });
      return { deriv: symDeriv || null, points: uniq };
    }

    function renderChart(expr, criticalPoints = []) {
      if (!cfg.hasChart) {
        console.warn('Chart.js not available — skipping render.');
        return;
      }

      // auto-range based on critical points
      let minX = -10, maxX = 10;
      if (criticalPoints && criticalPoints.length > 0) {
        const xs = criticalPoints.map(p=>p.x);
        const minCrit = Math.min(...xs);
        const maxCrit = Math.max(...xs);
        minX = Math.floor(minCrit) - 2;
        maxX = Math.ceil(maxCrit) + 2;
        const LIMIT = 100;
        if (minX < -LIMIT) minX = -LIMIT;
        if (maxX > LIMIT) maxX = LIMIT;
        if (maxX - minX < 4) { minX -= 2; maxX += 2; }
      }

      // adapt sampling step
      const range = maxX - minX;
      const step = range <= 40 ? 0.1 : (range <= 100 ? 0.25 : 0.5);

      const xs = [], ys = [];
      for (let x=minX; x<=maxX; x = Number((x + step).toFixed(8))) {
        xs.push(Number(x.toFixed(4)));
        const y = safeEval(expr, {x});
        ys.push(isFinite(y) ? y : null);
      }

      if (chart) chart.destroy();
      const ctx = document.getElementById('chart').getContext('2d');

      // critical points scatter
      const scatterPoints = criticalPoints.map(p=>({x:p.x, y:p.y}));

      // y-intercept point (if available)
      const yPt = (lastYIntercept !== null && isFinite(Number(lastYIntercept))) ? [{ x:0, y: Number(lastYIntercept) }] : [];

      chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: xs,
          datasets: [
            { label: expr, data: ys, spanGaps:true, tension:0.2 },
            { label: "Titik kritis (f'=0)", data: scatterPoints, type:'scatter', pointRadius:6, showLine:false },
            { label: "Sumbu-Y (f(0))", data: yPt, type:'scatter', pointStyle:'rectRot', pointRadius:8, showLine:false }
          ]
        },
        options: {
          animation:false,
          scales: {
            x: { type:'linear', position:'bottom', title:{display:true, text:'x'}, min: minX, max: maxX },
            y: { title:{display:true, text:'y'} }
          },
          plugins: { legend:{display:true} }
        }
      });
    }

    function listPoints(points) {
      pointsDiv.innerHTML = '';
      if (!points || points.length === 0) {
        pointsDiv.innerHTML = '<div class="list-group-item">Tidak ada titik kritis ditemukan.</div>';
        return;
      }
      points.forEach(p=>{
        const item = document.createElement('div');
        item.className = 'list-group-item';
        item.innerHTML = `<strong>x = ${p.x}</strong><br>f(x) = ${p.y}`;
        pointsDiv.appendChild(item);
      });
    }

    // --- new: compute & show Y-intercept ---
    function computeYIntercept(expr) {
      const yVal = safeEval(expr, {x:0});
      lastYIntercept = (isFinite(yVal) ? Number(yVal) : null);
      yBox.textContent = lastYIntercept === null ? '(0, -)' : `(0, ${lastYIntercept})`;
      // update chart to show the y-point (if chart available)
      try { renderChart(expr, []); } catch(e){ /* ignore */ }
      return lastYIntercept;
    }

    // event handlers
    btnCalc.addEventListener('click', ()=>{
      const expr = fnInput.value.trim();
      if (!expr) { alert('Masukkan fungsi dulu (pakai x sebagai variabel).'); return; }
      showFn.textContent = expr;
      try {
        const res = findCriticalPoints(expr);
        listPoints(res.points);
        renderChart(expr, res.points);
      } catch (e) {
        console.error('Unhandled error while processing:', e);
        alert('Terjadi error saat memproses fungsi. Lihat console (F12) untuk detail.');
      }
    });

    btnY.addEventListener('click', ()=>{
      const expr = fnInput.value.trim();
      if (!expr) { alert('Masukkan fungsi dulu.'); return; }
      computeYIntercept(expr);
      // also ensure chart updates with existing critical points if any
      try {
        // try to re-find critical points so chart shows both types
        const res = findCriticalPoints(expr);
        listPoints(res.points);
        renderChart(expr, res.points);
      } catch(e) {
        console.warn('Error when updating chart after y-intercept compute', e);
      }
    });

    // optional: press Enter to compute (plot)
    fnInput.addEventListener('keydown', (e)=>{
      if (e.key === 'Enter') {
        e.preventDefault();
        document.getElementById('btnCalc').click();
      }
    });

    // default example
    fnInput.value = 'sin(x)';
    // auto-run once on load to show initial chart & y-intercept
    setTimeout(()=> {
      try {
        document.getElementById('btnCalc').click();
        document.getElementById('btnY').click();
      } catch(e){ /* ignore */ }
    }, 120);

  } // end initApp
  </script>
</body>
</html>
