<!doctype html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Kalkulator Titik Diferensial</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 18px;
        }

        #chart {
            max-width: 920px;
            height: 480px;
        }

        .point-list {
            max-height: 360px;
            overflow: auto;
        }

        .warn {
            color: #b44;
            font-weight: 600;
        }

        .small-note {
            font-size: 0.9rem;
            color: #666;
        }
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
            <!-- <div class="small-note">Console (F12) menampilkan log loader & error.</div> -->
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

                <div class="small-note">Tips: pakai `sin(x)`, `x^2`, `1/x`, `exp(x)`, `sqrt(x)`, dll.</div>
            </div>
        </div>
    </div>

    <footer class="text-center mt-5 py-3 bg-primary border-top text-white">
        <div class="container">
            <strong>Nama:</strong> At'thoriq Huseini Subakuh &nbsp; | &nbsp;
            <strong>NIM:</strong> 411251104
        </div>
    </footer>

    <!-- loader helper -->
    <script>
        function loadScript(src) {
            return new Promise((resolve, reject) => {
                const s = document.createElement('script');
                s.src = src;
                s.async = false;
                s.onload = () => {
                    console.log('Loaded', src);
                    resolve(src);
                };
                s.onerror = (e) => {
                    console.warn('Failed to load', src);
                    reject(new Error('Failed to load ' + src));
                };
                document.head.appendChild(s);
            });
        }
    </script>

    <!-- load nerdamer and chart -->
    <script>
        async function loadNerdamerLocal() {
            const files = ['js/nerdamer.core.js', 'js/Algebra.js', 'js/Calculus.js', 'js/Solve.js'];
            const res = [];
            for (const f of files) {
                try {
                    await loadScript(f);
                    res.push({
                        file: f,
                        ok: true
                    });
                } catch (e) {
                    console.warn(e.message);
                    res.push({
                        file: f,
                        ok: false
                    });
                }
            }
            return res;
        }

        async function loadChart() {
            try {
                await loadScript('js/chart.umd.min.js');
                return {
                    ok: true,
                    src: 'local'
                };
            } catch (e) {
                console.warn('Local Chart failed, trying CDN');
                try {
                    await loadScript('https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js');
                    return {
                        ok: true,
                        src: 'cdn'
                    };
                } catch (e2) {
                    console.error('Chart.js failed to load');
                    return {
                        ok: false
                    };
                }
            }
        }

        (async function startup() {
            const nerdRes = await loadNerdamerLocal();
            const chartRes = await loadChart();
            initApp({
                hasNerdamer: (typeof nerdamer !== 'undefined'),
                nerdamerFiles: nerdRes,
                hasChart: chartRes.ok,
                chartSource: chartRes.src || null
            });
        })();
    </script>

    <!-- main app -->
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

            // status
            let statusHtml = '';
            statusHtml += cfg.hasNerdamer ? '<span style="color:green"></span>' : '<span class="warn"></span>';
            statusHtml += cfg.hasChart ? '  <span style="color:green"></span>' : '  <span class="warn"></span>';
            statusEl.innerHTML = statusHtml;

            // evaluator
            function safeEval(expr, scope = {}) {
                if (cfg.hasNerdamer) {
                    try {
                        return Number(nerdamer(expr, scope).evaluate().text());
                    } catch (e) {
                        console.warn('nerdamer eval failed for', expr, e);
                        return NaN;
                    }
                }
                try {
                    const jsExpr = expr.replace(/\^/g, '**')
                        .replace(/sin\(/g, 'Math.sin(')
                        .replace(/cos\(/g, 'Math.cos(')
                        .replace(/tan\(/g, 'Math.tan(')
                        .replace(/exp\(/g, 'Math.exp(')
                        .replace(/log\(/g, 'Math.log(')
                        .replace(/sqrt\(/g, 'Math.sqrt(')
                        .replace(/abs\(/g, 'Math.abs(');
                    const f = new Function('x', 'with(Math){ return ' + jsExpr + ' }');
                    return Number(f(scope.x));
                } catch (e) {
                    console.error('Fallback eval failed for', expr, e);
                    return NaN;
                }
            }

            function numericDerivative(expr, x) {
                const h = 1e-6;
                const f1 = safeEval(expr, {
                    x: x + h
                });
                const f2 = safeEval(expr, {
                    x: x - h
                });
                if (!isFinite(f1) || !isFinite(f2)) return NaN;
                return (f1 - f2) / (2 * h);
            }

            function trySymbolicDerivative(expr) {
                if (!cfg.hasNerdamer) return null;
                try {
                    return nerdamer(`diff(${expr}, x)`).toString();
                } catch (e) {
                    console.warn('Symbolic derivative failed:', e);
                    return null;
                }
            }

            function trySymbolicSolve(derivExpr) {
                if (!cfg.hasNerdamer) return [];
                try {
                    const sols = nerdamer.solve(derivExpr, 'x');
                    return Array.isArray(sols) ? sols : [];
                } catch (e) {
                    console.warn('Symbolic solve failed:', e);
                    return [];
                }
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
                                    const y = safeEval(fnExpr, {
                                        x: xnum
                                    });
                                    if (isFinite(y)) pts.push({
                                        x: Number(xnum.toFixed(8)),
                                        y: Number(y.toFixed(8))
                                    });
                                }
                            } catch (e) {
                                console.warn('Parsing solution failed:', s, e);
                            }
                        });
                        const uniq = [];
                        const seen = {};
                        pts.forEach(p => {
                            const k = p.x.toFixed(8);
                            if (!seen[k]) {
                                seen[k] = 1;
                                uniq.push(p);
                            }
                        });
                        return {
                            deriv: symDeriv,
                            points: uniq
                        };
                    }
                } else {
                    showDeriv.textContent = '(symbolic failed - using numeric derivative)';
                }

                // numeric fallback scanning
                const pts = [];
                for (let a = -20; a < 20; a += 0.5) {
                    const fa = numericDerivative(fnExpr, a);
                    const fb = numericDerivative(fnExpr, a + 0.5);
                    if (!isFinite(fa) || !isFinite(fb)) continue;
                    if (Math.abs(fa) < 1e-9) {
                        const y = safeEval(fnExpr, {
                            x: a
                        });
                        if (isFinite(y)) pts.push({
                            x: Number(a.toFixed(8)),
                            y: Number(y.toFixed(8))
                        });
                    } else if (fa * fb < 0) {
                        let left = a,
                            right = a + 0.5,
                            mid, fleft = fa;
                        for (let it = 0; it < 60; it++) {
                            mid = (left + right) / 2;
                            const fm = numericDerivative(fnExpr, mid);
                            if (!isFinite(fm)) break;
                            if (Math.abs(fm) < 1e-8) break;
                            if (fleft * fm <= 0) {
                                right = mid;
                            } else {
                                left = mid;
                                fleft = fm;
                            }
                        }
                        const y = safeEval(fnExpr, {
                            x: mid
                        });
                        if (isFinite(y)) pts.push({
                            x: Number(mid.toFixed(8)),
                            y: Number(y.toFixed(8))
                        });
                    }
                }
                const uniq = [];
                const seen = {};
                pts.forEach(p => {
                    const k = p.x.toFixed(8);
                    if (!seen[k]) {
                        seen[k] = 1;
                        uniq.push(p);
                    }
                });
                return {
                    deriv: symDeriv || null,
                    points: uniq
                };
            }

            // label generator A,B,C,...,AA...
            function labelFromIndex(i) {
                const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                let s = '';
                let n = i + 1;
                while (n > 0) {
                    const rem = (n - 1) % 26;
                    s = letters[rem] + s;
                    n = Math.floor((n - 1) / 26);
                }
                return s;
            }

            function renderChart(expr, criticalPoints = []) {
                if (!cfg.hasChart) return;
                // determine range
                let minX = -10,
                    maxX = 10;
                if (criticalPoints && criticalPoints.length > 0) {
                    const xs = criticalPoints.map(p => p.x);
                    const minCrit = Math.min(...xs);
                    const maxCrit = Math.max(...xs);
                    minX = Math.floor(minCrit) - 2;
                    maxX = Math.ceil(maxCrit) + 2;
                    const LIMIT = 100;
                    if (minX < -LIMIT) minX = -LIMIT;
                    if (maxX > LIMIT) maxX = LIMIT;
                    if (maxX - minX < 4) {
                        minX -= 2;
                        maxX += 2;
                    }
                }

                const range = maxX - minX;
                const step = range <= 40 ? 0.1 : (range <= 100 ? 0.25 : 0.5);

                const xs = [],
                    ys = [];
                for (let x = minX; x <= maxX; x = Number((x + step).toFixed(8))) {
                    xs.push(Number(x.toFixed(4)));
                    const y = safeEval(expr, {
                        x
                    });
                    ys.push(isFinite(y) ? y : null);
                }

                // prepare critical points with label property
                const crit = (criticalPoints || []).map((p, i) => ({
                    x: p.x,
                    y: p.y,
                    label: labelFromIndex(i)
                }));
                const yPt = (lastYIntercept !== null && isFinite(Number(lastYIntercept))) ? [{
                    x: 0,
                    y: Number(lastYIntercept),
                    label: 'Y'
                }] : [];

                if (chart) chart.destroy();
                const ctx = document.getElementById('chart').getContext('2d');

                const dsLine = {
                    label: expr,
                    data: ys,
                    spanGaps: true,
                    tension: 0.2,
                    borderColor: '#1f77b4',
                    pointRadius: 3
                };
                const dsCrit = {
                    label: "Titik kritis (f'=0)",
                    data: crit,
                    type: 'scatter',
                    pointRadius: 6,
                    showLine: false,
                    backgroundColor: '#e76f51'
                };
                const dsY = {
                    label: "Sumbu-Y (f(0))",
                    data: yPt,
                    type: 'scatter',
                    pointStyle: 'rectRot',
                    pointRadius: 8,
                    showLine: false,
                    backgroundColor: '#f4a261'
                };

                const config = {
                    type: 'line',
                    data: {
                        labels: xs,
                        datasets: [dsLine, dsCrit, dsY]
                    },
                    options: {
                        animation: false,
                        scales: {
                            x: {
                                type: 'linear',
                                position: 'bottom',
                                title: {
                                    display: true,
                                    text: 'x'
                                },
                                min: minX,
                                max: maxX
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'y'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true
                            },
                            tooltip: {
                                callbacks: {
                                    // show label A,B,C for critical points & 'Y' for y-intercept
                                    label: function(context) {
                                        const raw = context.raw;
                                        // critical/y points we stored as object with .label
                                        if (raw && raw.label) {
                                            // format numbers nicely
                                            const xs = (raw.x !== undefined) ? Number(raw.x).toFixed(6) : context.parsed.x;
                                            const ys = (raw.y !== undefined) ? Number(raw.y).toFixed(6) : context.parsed.y;
                                            return `Titik ${raw.label}: (x=${xs}, y=${ys})`;
                                        }
                                        // fallback for line points
                                        const px = context.parsed.x,
                                            py = context.parsed.y;
                                        return `(${px}, ${py})`;
                                    }
                                }
                            }
                        }
                    }
                };

                chart = new Chart(ctx, config);
            }

            function listPoints(points) {
                pointsDiv.innerHTML = '';
                if (!points || points.length === 0) {
                    pointsDiv.innerHTML = '<div class="list-group-item">Tidak ada titik kritis ditemukan.</div>';
                    return;
                }
                points.forEach((p, i) => {
                    const lab = labelFromIndex(i);
                    const item = document.createElement('div');
                    item.className = 'list-group-item';
                    item.innerHTML = `<strong>${lab}. x = ${p.x}</strong><br>f(x) = ${p.y}`;
                    pointsDiv.appendChild(item);
                });
            }

            // compute y-intercept
            function computeYIntercept(expr) {
                const yVal = safeEval(expr, {
                    x: 0
                });
                lastYIntercept = (isFinite(yVal) ? Number(yVal) : null);
                yBox.textContent = lastYIntercept === null ? '(0, -)' : `(0, ${lastYIntercept})`;
                return lastYIntercept;
            }

            // handlers
            btnCalc.addEventListener('click', () => {
                const expr = fnInput.value.trim();
                if (!expr) {
                    alert('Masukkan fungsi dulu (pakai x sebagai variabel).');
                    return;
                }
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

            btnY.addEventListener('click', () => {
                const expr = fnInput.value.trim();
                if (!expr) {
                    alert('Masukkan fungsi dulu.');
                    return;
                }
                computeYIntercept(expr);
                try {
                    const res = findCriticalPoints(expr);
                    listPoints(res.points);
                    renderChart(expr, res.points);
                } catch (e) {
                    console.warn('Error when updating chart after y-intercept compute', e);
                }
            });

            // Enter key to plot
            fnInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    document.getElementById('btnCalc').click();
                }
            });

            // default example + auto-run
            fnInput.value = 'sin(x) + cos(2*x)';
            setTimeout(() => {
                try {
                    btnCalc.click();
                    btnY.click();
                } catch (e) {}
            }, 120);
        } // end initApp
    </script>
</body>

</html>