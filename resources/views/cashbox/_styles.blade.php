<style>
.finance-page{--fin-in:#147d5a;--fin-out:#be454c;display:grid;gap:20px;color:var(--text)}
[data-theme="dark"] .finance-page{--fin-in:#70d4b3;--fin-out:#f29a9e}
.finance-page h1,.finance-page h2,.finance-page h3,.finance-page p{margin:0}
.finance-page h1{font-size:27px}.finance-page h2{font-size:18px}.finance-page h3{font-size:15px}
.finance-hero,.finance-toolbar,.finance-section-head{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap}
.finance-heading,.finance-account-title{display:flex;align-items:center;gap:13px;min-width:0}
.finance-account-title>div{min-width:0;overflow-wrap:anywhere}
.finance-subtitle{color:var(--text-soft);font-size:12px;margin-top:5px!important;line-height:1.7}
.finance-icon-tile{width:44px;height:44px;border-radius:14px;background:#f3ebdf;color:#806344;display:grid;place-items:center;flex:0 0 44px}
.finance-icon-tile .icon{width:23px;height:23px;flex-basis:23px}
.finance-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.finance-page .finance-button{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:10px 14px;border-radius:10px;text-decoration:none;background:var(--accent);color:#202739;font-size:12px;font-weight:800;border:1px solid transparent;cursor:pointer;white-space:nowrap}
.finance-page .finance-button.secondary{background:var(--surface);color:var(--text);border-color:var(--border)}
.finance-page .finance-button:hover{filter:brightness(.97)}
.finance-page .finance-button:focus-visible,.finance-page summary:focus-visible{outline:3px solid var(--accent);outline-offset:3px}
.finance-page .finance-button:disabled{opacity:.55;cursor:wait}
.finance-page .finance-button.deposit-action{color:var(--fin-in)}
.finance-page .finance-button.withdraw-action{color:var(--fin-out)}
.finance-page .finance-button.icon-only{padding:10px}
.finance-tabs{display:flex;gap:4px;padding:4px;border:1px solid var(--border);border-radius:13px;background:var(--surface-soft)}
.finance-tabs a{display:flex;align-items:center;gap:8px;padding:9px 15px;text-decoration:none;color:var(--text-soft);border-radius:9px;font-weight:800;font-size:12px}
.finance-tabs a.active{background:var(--surface);color:var(--text);box-shadow:0 2px 8px #00000008}
.finance-filter{display:flex;align-items:center;gap:8px;max-width:100%}
.finance-filter select{width:230px;max-width:100%;margin:0;padding:11px;font-size:13px}
.finance-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
.finance-summary article{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:18px 21px;min-width:0}
.finance-summary span{color:var(--text-soft);font-size:12px;font-weight:700}
.finance-summary strong{display:block;margin-top:9px;font-size:25px;overflow-wrap:anywhere;line-height:1.4}
.finance-summary small{font-size:11px;font-weight:600;color:var(--text-soft)}
.finance-page .finance-in{color:var(--fin-in)}.finance-page .finance-out{color:var(--fin-out)}
.finance-section-head{padding:20px 22px;border-bottom:1px solid var(--border)}
.finance-count{padding:5px 9px;border-radius:8px;background:var(--surface-soft);color:var(--text-soft);font-size:11px}
.finance-list,.finance-statement-panel{background:var(--surface);border:1px solid var(--border);border-radius:18px;overflow:hidden;min-width:0}
.finance-list-head,.finance-row{display:grid;grid-template-columns:minmax(190px,1fr) minmax(140px,.5fr) 370px;align-items:center;gap:22px;padding:20px 22px}
.finance-list-head{background:var(--surface-soft);padding-block:11px;color:var(--text-soft);font-size:11px;font-weight:700}
.finance-row>.finance-actions{justify-content:flex-end}
.finance-row+.finance-row{border-top:1px solid var(--border)}
.finance-row-balance{display:flex;align-items:baseline;gap:7px;flex-wrap:wrap}
.finance-row-balance strong{font-size:21px;overflow-wrap:anywhere}.finance-row-balance small{font-size:11px;color:var(--text-soft)}
.finance-status{display:inline-flex;align-items:center;gap:5px;color:var(--fin-in);font-size:10px;margin-top:6px}
.finance-status:before{content:'';width:5px;height:5px;border-radius:50%;background:currentColor}.finance-status.inactive{color:var(--text-soft)}
.finance-empty{text-align:center;padding:45px 20px;color:var(--text-soft)}
.finance-empty .finance-icon-tile{margin:0 auto 14px}.finance-empty p{margin-bottom:16px}
.finance-sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
.finance-dialog{width:min(570px,calc(100% - 28px));max-height:calc(100dvh - 40px);margin:auto;padding:0;background:var(--surface);color:var(--text);border:1px solid var(--border);border-radius:20px;box-shadow:0 24px 90px #0005;overflow:auto}
.finance-dialog::backdrop{background:#0b1227a6;backdrop-filter:blur(3px)}
.finance-dialog-head{display:flex;justify-content:space-between;align-items:center;gap:16px;padding:22px;border-bottom:1px solid var(--border)}
.finance-page .finance-close{width:32px;height:32px;padding:0;display:grid;place-items:center;flex:0 0 32px;font-size:25px;line-height:1;background:var(--surface-soft);color:var(--text-soft);border:1px solid var(--border);border-radius:9px;cursor:pointer}
.finance-dialog-body,.finance-transaction{padding:22px;display:grid;gap:16px}
.finance-dialog-footer{display:flex;justify-content:flex-end;gap:10px;margin-top:4px}
.finance-field{display:grid;gap:7px;min-width:0}.finance-page .finance-field label{font-size:12px;font-weight:700;color:var(--text-soft)}
.finance-field input,.finance-field select,.finance-field textarea{margin:0;width:100%;box-sizing:border-box;font-size:14px}
.finance-field textarea{resize:vertical;min-height:70px}
.finance-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.finance-check{display:flex;align-items:center;gap:8px;font-size:13px}.finance-page .finance-check input{width:auto;margin:0}
.finance-operation{display:grid;grid-template-columns:1fr 1fr;gap:8px;border:0;padding:0;margin:0;min-width:0}
.finance-choice{position:relative;cursor:pointer}.finance-choice input{position:absolute;opacity:0;width:1px!important;height:1px}
.finance-choice span{display:flex;align-items:center;justify-content:center;gap:8px;border:1px solid var(--border);background:var(--surface-soft);border-radius:10px;padding:12px;font-size:13px;font-weight:800}
.finance-choice input:checked+span{color:var(--fin-in);border-color:var(--fin-in);background:color-mix(in srgb,var(--fin-in) 8%,var(--surface))}
.finance-choice.withdraw input:checked+span{color:var(--fin-out);border-color:var(--fin-out);background:color-mix(in srgb,var(--fin-out) 8%,var(--surface))}
.finance-choice input:focus-visible+span{outline:3px solid var(--accent);outline-offset:3px}
.finance-page .finance-submit{background:var(--fin-in);color:var(--surface);min-width:120px}
.finance-page .finance-transaction:has(input[value="withdrawal"]:checked) .finance-submit{background:var(--fin-out)}
.finance-text-link{display:inline-flex;align-items:center;gap:6px;text-decoration:none;color:var(--text-soft);font-size:12px}
.finance-notice{padding:13px 16px;border-radius:12px;border:1px solid var(--border);background:var(--surface);color:var(--fin-in);font-size:13px}.finance-notice.error{color:var(--fin-out)}
.finance-statement-identity{display:flex;justify-content:space-between;gap:16px;align-items:center;padding:22px;background:var(--surface);border:1px solid var(--border);border-radius:18px}
.finance-statement-current{text-align:end;color:var(--text-soft);font-size:12px}.finance-statement-current strong{display:block;margin-top:6px;color:var(--text);font-size:22px}
.finance-period-form{display:flex;align-items:end;gap:12px;flex-wrap:wrap;padding:18px 22px;background:var(--surface);border:1px solid var(--border);border-radius:16px}
.finance-period-form .finance-field{flex:1;min-width:155px}
.finance-period-form .finance-field input{padding:10px 13px}
[data-theme="dark"] .finance-field input[type="date"]{color-scheme:dark}
.finance-statement-summary{grid-template-columns:repeat(4,minmax(0,1fr))}
.finance-statement-summary strong{font-size:22px}
.finance-table-wrap{overflow-x:auto}
.finance-statement-table{width:100%;min-width:920px;border-collapse:collapse;border-spacing:0;margin:0}
.finance-statement-table th{font-size:11px;padding:13px 14px;background:var(--surface-soft);color:var(--text-soft);text-align:start;border-radius:0}
.finance-statement-table td{padding:14px;font-size:12px;border-bottom:1px solid var(--border);background:transparent;border-radius:0;vertical-align:top}
.finance-statement-table .numeric{white-space:nowrap;text-align:end;font-variant-numeric:tabular-nums}
.finance-statement-table .statement-date{white-space:nowrap}.finance-statement-table .statement-reference{max-width:175px;overflow-wrap:anywhere;font-size:10px;line-height:1.5}
.finance-statement-table .statement-description{min-width:165px;max-width:280px;white-space:normal;overflow-wrap:anywhere}
.statement-description strong{display:block;font-size:12px}.statement-description small{display:block;font-size:11px;color:var(--text-soft);margin-top:4px;line-height:1.6}
.finance-statement-table .balance-cell{font-weight:800;background:var(--surface-soft)}
.finance-statement-table .statement-opening td{background:var(--surface-soft);color:var(--text-soft)}
.finance-statement-table tfoot td{background:var(--surface-soft);font-weight:800;border-top:2px solid var(--border);border-bottom:0}
.finance-statement-footnote{font-size:11px;color:var(--text-soft);line-height:1.7}
@media(max-width:1150px){.finance-list-head{display:none}.finance-row{grid-template-columns:minmax(0,1fr) auto}.finance-row>.finance-actions{grid-column:1/-1;justify-content:flex-end}.finance-statement-summary{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:700px){.finance-page{gap:16px}.finance-page h1{font-size:23px}.finance-hero>.finance-actions{width:100%}.finance-hero>.finance-actions>*{flex:1}.finance-summary{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.finance-summary:not(.finance-statement-summary) article:first-child{grid-column:1/-1}.finance-summary article{padding:14px 16px}.finance-summary strong{font-size:21px}.finance-filter{width:100%}.finance-filter select{flex:1;min-width:0;width:auto}.finance-row{padding:17px;gap:14px}.finance-row-balance{display:block;text-align:end}.finance-row-balance strong{display:block;font-size:18px}.finance-row>.finance-actions{justify-content:flex-start;gap:6px}.finance-row .finance-button{padding:9px 10px;font-size:11px}.finance-form-grid{grid-template-columns:1fr}.finance-dialog-body,.finance-transaction,.finance-dialog-head{padding:18px}.finance-statement-identity{align-items:flex-start;flex-direction:column}.finance-statement-current{text-align:start}.finance-period-form{padding:16px}.finance-section-head{padding:17px}.finance-account-title .finance-icon-tile{width:38px;height:38px;flex-basis:38px}}
</style>
