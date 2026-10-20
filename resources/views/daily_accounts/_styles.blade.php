<style>
.da{direction:rtl;color:var(--text);max-width:1500px;margin:auto;font-family:Tajawal,sans-serif}
.da *{box-sizing:border-box}.da h1{font-size:30px;margin:0 0 5px}.da h2{font-size:18px;margin:0 0 18px}.da p{margin:6px 0;color:var(--text-soft)}
.da-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}.da-badge{background:var(--surface-soft);color:var(--accent-dark);border-radius:20px;padding:8px 15px;font-size:13px}
.da-actions{display:flex;gap:10px;flex-wrap:wrap;margin:20px 0}.da button,.da .da-btn{font:inherit;cursor:pointer;border:1px solid var(--border);background:var(--surface);color:var(--text);border-radius:10px;padding:10px 17px;text-decoration:none;display:inline-block}
.da .da-primary{background:#a58b65;color:#fff;border-color:#a58b65}.da .da-danger{color:#ae4e4e;border-color:#ecd5d5}.da button:hover,.da .da-btn:hover{filter:brightness(.97)}
.da-panel{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:22px;margin-bottom:20px;box-shadow:0 3px 15px #5e4a2a05}
.da-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:14px}.da label{display:flex;flex-direction:column;gap:7px;font-size:13px;color:var(--text-soft)}
.da input,.da select,.da textarea{font:inherit;min-width:0;width:100%;border:1px solid var(--border);background:var(--surface-soft);border-radius:9px;padding:10px;color:var(--text)}
.da textarea{resize:vertical;min-height:85px}.da input:focus,.da select:focus,.da textarea:focus{outline:2px solid var(--accent);outline-offset:1px}
.da-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:15px;margin:20px 0}.da-stat{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:20px;min-height:130px}
.da-stat:first-child{background:var(--surface-soft);border-color:var(--border)}.da-stat>span{color:var(--text-soft);font-size:13px}.da-stat strong{display:block;margin:12px 0 5px;font-size:26px;font-weight:800;overflow-wrap:anywhere}.da-stat small{color:var(--text-soft)}.da-total-stat .da-dual-money{gap:8px}.da-total-stat .da-currency-line{font-size:30px!important;line-height:1.2;font-weight:900;letter-spacing:.2px}
.da-two{display:grid;grid-template-columns:1fr 1fr;gap:20px}.da-table-wrap{overflow:auto}.da table{width:100%;border-collapse:collapse;text-align:right}.da th{color:var(--text-soft);background:var(--surface-soft);font-size:13px;font-weight:500}.da td,.da th{padding:13px 12px;border-bottom:1px solid var(--border);vertical-align:middle}
.da .da-ledger{min-width:960px}.da .da-money{direction:ltr;unicode-bidi:isolate;white-space:nowrap;font-weight:700}.da td a{color:var(--accent-dark);text-decoration:none}.da-row-actions{display:flex;gap:6px}.da-row-actions button,.da-row-actions .da-btn{padding:6px 10px;font-size:12px}
.da-dual-money{display:flex!important;flex-direction:column;align-items:flex-start;gap:4px}.da-currency-line{display:block;direction:ltr;unicode-bidi:isolate}.da-currency-line.iqd{color:#7b5d31}.da-currency-line.usd{color:#287052}
.da-empty{text-align:center;padding:38px!important;color:var(--text-soft)}.da-chart{height:300px;position:relative}.da-subscroll{max-height:400px;overflow:auto}
.da-dialog{width:min(620px,95vw);max-height:90vh;overflow:auto;border:1px solid var(--border);border-radius:18px;padding:26px;color:var(--text)}
.da-dialog::backdrop{background:#30271955;backdrop-filter:blur(3px)}.da-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:15px}.da-wide{grid-column:1/-1}.da-alert{padding:14px;border-radius:10px;background:#edf6ee;color:#356547;margin-bottom:15px}.da-errors{background:#fff0ee;color:#a14c41}
.da-pagination{display:flex;align-items:center;justify-content:space-between;margin-top:18px;gap:10px;flex-wrap:wrap}.da-pagination a{color:var(--accent-dark);text-decoration:none}.da-note{max-width:300px;overflow-wrap:anywhere;white-space:pre-wrap}
@media(max-width:1100px){.da-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.da-two{grid-template-columns:1fr}}
@media(max-width:650px){.da-stats,.da-grid,.da-form-grid{grid-template-columns:1fr}.da-panel{padding:15px}.da-head{align-items:flex-start;gap:10px}.da h1{font-size:25px}.da-badge{display:none}.da-actions>*{flex:1;text-align:center}.da-stat{min-height:110px}.da-total-stat .da-currency-line{font-size:25px!important}.da-two{gap:0}}
.da{color:var(--text)}
.da-panel,.da-stat,.da-dialog{background:var(--surface);color:var(--text);border-color:var(--border)}
.da p,.da label,.da-stat>span,.da-stat small,.da-empty{color:var(--text-soft)}
.da input,.da select,.da textarea{background:var(--surface-soft);color:var(--text);border-color:var(--border)}
.da button,.da .da-btn{background:var(--surface);color:var(--text);border-color:var(--border)}
.da .da-primary{background:var(--accent);border-color:var(--accent);color:#fff}
.da th,.da-stat:first-child,.da-badge{background:var(--surface-soft);color:var(--text-soft);border-color:var(--border)}
.da td,.da th{border-color:var(--border)}
.da tbody tr:hover{background:var(--surface-soft)}
html[data-theme="dark"] .da{color-scheme:dark;color:var(--text)}
html[data-theme="dark"] .da :is(h1,h2,h3,strong){color:var(--text)}
html[data-theme="dark"] .da :is(.da-panel,.da-stat,.da-dialog){background:var(--surface);color:var(--text);border-color:var(--border)}
html[data-theme="dark"] .da :is(.da-stat:first-child,.da-badge,th){background:var(--surface-soft);color:var(--text-soft)}
html[data-theme="dark"] .da :is(button,.da-btn){background:var(--surface);color:var(--text);border-color:var(--border)}
html[data-theme="dark"] .da .da-primary{background:var(--accent);color:#172039;border-color:var(--accent)}
html[data-theme="dark"] .da :is(input,select,textarea){color-scheme:dark;background:var(--surface-soft);color:var(--text);border-color:var(--border)}
html[data-theme="dark"] .da td{background:var(--surface)}
html[data-theme="dark"] .da tbody tr:hover td{background:var(--surface-soft)}
[data-theme="dark"] .da .da-currency-line.iqd,[data-theme="dark"] .da td a,[data-theme="dark"] .da-pagination a{color:#e0c18f}
[data-theme="dark"] .da .da-currency-line.usd{color:#70d4b3}
[data-theme="dark"] .da .da-danger{color:#f29a9e;border-color:#733f50}
[data-theme="dark"] .da-alert{background:#193d35;color:#9be2c3}
[data-theme="dark"] .da-errors{background:#442c38;color:#ffc0ba}
</style>
