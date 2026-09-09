<style>
.finance-page{--fin-in:#147d5a;--fin-out:#be454c;display:grid;gap:22px}
[data-theme="dark"] .finance-page{--fin-in:#70d4b3;--fin-out:#f29a9e}
.finance-page .finance-hero{display:flex;justify-content:space-between;align-items:center;gap:18px;flex-wrap:wrap}
.finance-heading{display:flex;align-items:center;gap:14px}
.finance-page h1,.finance-page h2,.finance-page h3,.finance-page p{margin:0}
.finance-page h1{font-size:28px}.finance-page h2{font-size:19px}.finance-page h3{font-size:17px}
.finance-subtitle{color:var(--text-soft);font-size:13px;margin-top:6px!important;line-height:1.7}
.finance-icon-tile{width:48px;height:48px;border-radius:16px;background:#f3ebdf;color:#806344;display:grid;place-items:center;flex:0 0 48px}
.finance-icon-tile .icon{width:25px;height:25px;flex-basis:25px}
.finance-page .finance-button{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 17px;border-radius:12px;text-decoration:none;background:var(--accent);color:#202739;font-size:13px;font-weight:800;border:1px solid transparent;cursor:pointer}
.finance-page .finance-button.secondary{background:var(--surface-soft);color:var(--text);border-color:var(--border)}
.finance-toolbar{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
.finance-tabs{display:flex;padding:5px;gap:5px;background:var(--surface-soft);border:1px solid var(--border);border-radius:15px}
.finance-tabs a{display:flex;align-items:center;gap:8px;padding:10px 16px;text-decoration:none;color:var(--text-soft);border-radius:10px;font-weight:800;font-size:13px}
.finance-tabs a.active{background:var(--surface);color:var(--text);box-shadow:0 3px 12px #0000000c}
.finance-filter{display:flex;align-items:center;gap:9px;flex-wrap:wrap}
.finance-filter select{width:auto;min-width:220px;max-width:100%;margin:0}
.finance-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
.finance-summary article{display:flex;align-items:center;gap:15px;background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:22px;min-width:0}
.finance-summary span{color:var(--text-soft);font-size:12px;font-weight:700}
.finance-summary strong{display:block;margin-top:7px;font-size:25px;overflow-wrap:anywhere}
.finance-summary small{font-size:12px;font-weight:600;color:var(--text-soft)}
.finance-page .finance-in{color:var(--fin-in)}.finance-page .finance-out{color:var(--fin-out)}
.finance-summary .finance-icon-tile{color:#806344}
.finance-section-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px}
.finance-section-head h2{display:flex;align-items:center;gap:9px}
.finance-count{padding:5px 10px;border-radius:9px;background:var(--surface-soft);color:var(--text-soft);font-size:12px}
.finance-accounts{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,340px),1fr));gap:18px;align-items:start}
.finance-account{background:var(--surface);border:1px solid var(--border);border-radius:22px;overflow:hidden;min-width:0;box-shadow:0 8px 28px #00000005}
.finance-account-head{padding:22px;border-bottom:1px solid var(--border);background:linear-gradient(125deg,var(--surface),var(--surface-soft))}
.finance-account-title{display:flex;align-items:center;gap:12px}
.finance-account-title>div:nth-child(2){flex:1;min-width:0;overflow-wrap:anywhere}
.finance-status{display:inline-flex;align-items:center;gap:5px;color:var(--fin-in);font-size:11px}
.finance-status:before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor}
.finance-status.inactive{color:var(--text-soft)}
.finance-account-balance{display:flex;align-items:baseline;gap:9px;margin-top:20px}
.finance-account-balance strong{font-size:30px;line-height:1.3;overflow-wrap:anywhere}
.finance-account-balance small{color:var(--text-soft);font-size:13px}
.finance-transaction{padding:20px;display:grid;gap:15px}
.finance-page .finance-field{display:grid;gap:7px;min-width:0}
.finance-page .finance-field label{font-size:12px;font-weight:800;color:var(--text-soft)}
.finance-page .finance-field input,.finance-page .finance-field select,.finance-page .finance-field textarea{margin:0;width:100%;box-sizing:border-box}
.finance-page .finance-field textarea{resize:vertical;min-height:70px}
.finance-operation{display:grid;grid-template-columns:1fr 1fr;gap:8px;border:0;padding:0;margin:0;min-width:0}
.finance-operation legend{position:absolute;width:1px;height:1px;padding:0;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
.finance-choice{position:relative;cursor:pointer}
.finance-choice input{position:absolute;opacity:0;width:1px!important;height:1px}
.finance-choice span{display:flex;align-items:center;justify-content:center;gap:8px;border:1px solid var(--border);background:var(--surface-soft);border-radius:12px;padding:12px;font-size:13px;font-weight:800;transition:background .15s}
.finance-choice input:checked+span{color:var(--fin-in);border-color:var(--fin-in);background:color-mix(in srgb,var(--fin-in) 10%,var(--surface))}
.finance-choice.withdraw input:checked+span{color:var(--fin-out);border-color:var(--fin-out);background:color-mix(in srgb,var(--fin-out) 10%,var(--surface))}
.finance-choice input:focus-visible+span{outline:3px solid var(--accent);outline-offset:3px}
.finance-page .finance-transaction .finance-submit{background:var(--fin-in);color:var(--surface)}
.finance-page .finance-transaction:has(input[value="withdrawal"]:checked) .finance-submit{background:var(--fin-out)}
.finance-account-links{display:flex;gap:16px;padding:0 20px 18px;flex-wrap:wrap}
.finance-account-links a{display:flex;align-items:center;gap:6px;text-decoration:none;font-size:12px;font-weight:800;color:var(--text-soft)}
.finance-edit{border-top:1px solid var(--border);padding:15px 20px}
.finance-edit summary,.finance-create summary{cursor:pointer;font-size:13px;font-weight:800;display:flex;align-items:center;gap:8px;list-style:none}
.finance-edit summary::-webkit-details-marker,.finance-create summary::-webkit-details-marker{display:none}
.finance-edit form{display:grid;gap:12px;margin-top:16px}
.finance-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.finance-create{background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:22px;scroll-margin-top:20px}
.finance-create[open] summary{margin-bottom:20px}
.finance-create form{display:grid;gap:16px}
.finance-check{display:flex;align-items:center;gap:8px;font-size:13px}
.finance-page .finance-check input{width:auto;margin:0}
.finance-notice{padding:14px 18px;border-radius:14px;border:1px solid var(--border);background:var(--surface);color:var(--fin-in);font-size:13px}
.finance-notice.error{color:var(--fin-out)}
.finance-history{background:var(--surface);border:1px solid var(--border);border-radius:22px;padding:22px;min-width:0}
.finance-table-wrap{overflow-x:auto}
.finance-history table{min-width:780px;margin:0;width:100%}
.finance-history th,.finance-history td{white-space:nowrap}
.finance-history td:last-child{white-space:normal;min-width:150px;max-width:280px}
.finance-empty{text-align:center;padding:40px 20px;color:var(--text-soft);border:1px dashed var(--border);border-radius:20px;grid-column:1/-1}
.finance-empty .finance-icon-tile{margin:0 auto 14px}.finance-empty p{margin-bottom:16px}
@media(max-width:700px){.finance-summary{grid-template-columns:1fr}.finance-summary article{padding:17px}.finance-summary strong{font-size:23px}.finance-form-grid{grid-template-columns:1fr}.finance-filter{width:100%}.finance-filter select{flex:1;min-width:0}.finance-hero>.finance-button{width:100%}.finance-page h1{font-size:23px}.finance-account-balance strong{font-size:26px}.finance-history{padding:16px}}
</style>
