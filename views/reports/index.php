<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>

<style>
:root{
  --ifk-green:#96bc17;
  --ifk-navy:#00325a;
  --ifk-sky:#00a6dc;
}
#ifk-reports-page *{box-sizing:border-box}

/* ===== page wrap ===== */
.ifk-report-wrap{background:#f4f6f9;border-radius:18px;padding:18px;}
.ifk-page-head{display:flex;align-items:flex-end;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px;}
.ifk-page-head .ifk-title{margin:0;color:var(--ifk-navy);font-weight:1000;letter-spacing:-.02em;}
.ifk-page-head .ifk-sub{margin:4px 0 0;color:#6b7280;font-size:12px;}
.ifk-page-actions{display:flex;gap:10px;flex-wrap:wrap;align-items:center;}
.ifk-page-actions .btn{border-radius:12px;font-weight:900;}
.ifk-divider{height:1px;background:#e8edf5;margin:12px 0 0;}
.ifk-grid-gap{margin-top:14px;}

.ifk-report-box{
  background:#fff;border:1px solid #eef0f4;border-radius:18px;
  box-shadow:0 10px 28px rgba(0,0,0,0.06);
  padding:18px;margin-bottom:18px;
}
.ifk-box-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:12px;}
.ifk-box-head h4{margin:0;font-weight:1000;color:var(--ifk-navy);letter-spacing:-.01em;}
.ifk-box-head .ifk-box-tools{display:flex;gap:10px;flex-wrap:wrap;align-items:center;}
.ifk-box-head .btn{border-radius:12px;font-weight:900;}

.ifk-title{margin:0 0 14px;color:var(--ifk-navy);font-weight:900}

/* ===== KPI ===== */
.ifk-kpi-card{
  border-radius:18px;background:#fff;border:1px solid #eef0f4;
  box-shadow:0 10px 28px rgba(0,0,0,0.06);
  padding:14px 16px;margin-bottom:16px;position:relative;overflow:hidden;
}
.ifk-kpi-card:after{content:'';position:absolute;right:-22px;top:-22px;width:80px;height:80px;border-radius:24px;opacity:.10;background:currentColor;}
.kpi-accent{height:5px;border-radius:999px;margin:-14px -16px 12px}
.kpi-title{font-size:11px;text-transform:uppercase;letter-spacing:.10em;color:#6b7280;margin:0 0 6px;font-weight:900}
.kpi-value{font-size:26px;font-weight:1000;margin:0;color:#111827;letter-spacing:-.02em}
.kpi-sub{font-size:12px;color:#6b7280;margin-top:6px}

.kpi-accent{height:4px;border-radius:99px;margin:-14px -14px 12px}
.kpi-title{font-size:12px;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin:0 0 6px}
.kpi-value{font-size:24px;font-weight:900;margin:0;color:#111827}
.kpi-sub{font-size:12px;color:#6b7280;margin-top:6px}
.kpi-navy  .kpi-accent{background:var(--ifk-navy)}
.kpi-sky   .kpi-accent{background:var(--ifk-sky)}
.kpi-green .kpi-accent{background:var(--ifk-green)}
.kpi-amber .kpi-accent{background:#f59e0b}

/* ===== filters ===== */
.ifk-filter-head{
  display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;
}
.ifk-filter-head h4{margin:0;font-weight:900;color:var(--ifk-navy)}
.ifk-filter-tools{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.ifk-filter-tools .btn{border-radius:10px;font-weight:800}
.ifk-adv{
  background:#f8fafc;border:1px dashed #e5e7eb;border-radius:12px;padding:12px;margin-top:12px;display:none;
}
.ifk-adv.show{display:block}
.ifk-filter-label{font-size:11px;font-weight:1000;color:#4b5563;margin:0 0 6px;text-transform:uppercase;letter-spacing:.08em}
.ifk-filter-row .form-control{border-radius:12px;height:42px}
.ifk-filter-row .input-group .form-control{height:42px}
.ifk-filter-row .input-group-addon{border-radius:12px 0 0 12px}


/* Tabs */
.nav-tabs>li>a{font-weight:900;border-radius:12px 12px 0 0;padding:12px 14px}
.nav-tabs{border-bottom:1px solid #e8edf5}
.nav-tabs>li.active>a{border-bottom:3px solid var(--ifk-sky)!important;color:var(--ifk-navy)!important;background:#fff}

.nav-tabs>li.active>a{border-bottom:3px solid var(--ifk-sky)!important;color:var(--ifk-navy)!important}

/* Table */
.ifk-table th{
  background:#f3f6fb;text-transform:uppercase;font-size:11px;font-weight:1000;color:#4b5563;
  white-space:nowrap;letter-spacing:.08em;border-bottom:1px solid #e8edf5;
}
.ifk-table{border-radius:14px;overflow:hidden}
.ifk-table td{padding:12px 14px;font-size:14px;vertical-align:middle;border-color:#eef2f7}
.ifk-table tr:hover td{background:#fbfdff}

.ifk-table td{padding:12px 14px;font-size:14px;vertical-align:middle}
.ifk-table tr:hover td{background:#f9fafc}

/* Badge */
.status-badge{padding:6px 10px;border-radius:999px;font-size:12px;font-weight:900;display:inline-block;white-space:nowrap}
.st-green{background:#dcfce7;color:#166534}
.st-blue{background:#dbeafe;color:#1e40af}
.st-red{background:#fee2e2;color:#b91c1c}
.st-yellow{background:#fef9c3;color:#92400e}
.st-gray{background:#e5e7eb;color:#111827}
.st-entry{background:#ede9fe;color:#5b21b6}  /* NEW: cho status entry = Đã nhập cảnh */

/* Pipeline horizontal */
.ifk-pipe-row{display:flex;gap:12px;flex-wrap:wrap;align-items:stretch}
.ifk-pipe-item{
  flex:1 1 180px; min-width:180px;
  border:1px solid #eef2f7;border-radius:16px;
  padding:12px 12px 10px;background:#fff;
  box-shadow:0 6px 16px rgba(0,0,0,0.04);
}
.ifk-pipe-top{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:8px}
.ifk-pipe-count{font-weight:1000;color:var(--ifk-navy);font-size:18px;white-space:nowrap}
.ifk-pipe-key{color:#6b7280;font-size:12px;margin-top:6px;word-break:break-word}
.ifk-pipe-mini{height:6px;border-radius:999px;background:#eef2f7;overflow:hidden;margin-top:10px}
.ifk-pipe-mini > span{display:block;height:100%;width:0}

/* Charts */
.ifk-chart-box{position:relative;height:280px;width:100%;}
.ifk-chart-box canvas{position:absolute;inset:0;width:100%!important;height:100%!important;}
.ifk-muted{color:#6b7280}

@media (max-width:768px){
  .ifk-report-wrap{padding:12px;border-radius:12px}
  .ifk-report-box{padding:12px;border-radius:12px}
  .kpi-value{font-size:20px}
}

/* === Layout upgrade: pipeline top + bigger charts === */
.ifk-pipe-row--top{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;}
@media (max-width: 1200px){.ifk-pipe-row--top{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;}}
@media (max-width: 768px){.ifk-pipe-row--top{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;}}

.ifk-pipe-row--top .ifk-pipe-item{min-width:0}
.ifk-chart-area{position:relative}
.ifk-chart-area--big{height:440px}
.ifk-chart-area--mini{height:280px}
@media (max-width: 767px){
  .ifk-chart-area--big{height:360px}
  .ifk-chart-area--mini{height:240px}
}

.ifk-sec-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;}
.ifk-sec-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
.ifk-pill{background:#eef7ff;color:#00325a;border:1px solid #dbeafe;padding:6px 10px;border-radius:999px;font-weight:700;font-size:12px;}
.ifk-chart-controls{display:flex;align-items:center;gap:8px;}
.ifk-select{border:1px solid #e5e7eb;border-radius:10px;padding:6px 10px;font-weight:700;font-size:12px;background:#fff;}

/* Charts fix */
.ifk-chart-area{position:relative;}
.ifk-chart-area canvas{width:100% !important;height:100% !important;display:block;}
.ifk-chart-holder{position:relative;}
.ifk-chart-holder.is-empty canvas{display:none;}
.ifk-chart-holder.is-empty .ifk-empty{display:flex;}
.ifk-empty{display:none;align-items:center;justify-content:center;height:100%;color:#6b7280;font-weight:700;}


/* ===== IFK Reports UI polish (brand + charts) ===== */
:root{
  --ifk-green:#96bc17;
  --ifk-navy:#00325a;
  --ifk-sky:#00a6dc;
  --ifk-bg:#f4f7fb;
  --ifk-card:#ffffff;
  --ifk-muted:#6b7280;
  --ifk-border:rgba(15,23,42,.08);
}
.ifk-rpt-wrap{background:var(--ifk-bg);}
.ifk-rpt-card{
  background:var(--ifk-card);
  border:1px solid var(--ifk-border);
  box-shadow:0 14px 40px rgba(2,8,23,.08);
}
.ifk-rpt-card .ifk-rpt-head{
  border-bottom:1px dashed rgba(15,23,42,.10);
}
.ifk-rpt-title{color:var(--ifk-navy);}
.ifk-rpt-sub{color:var(--ifk-muted);}

/* pipeline cards */
.ifk-pipe-item{
  border:1px solid rgba(0,50,90,.09);
  background:linear-gradient(180deg, rgba(255,255,255,.98), rgba(255,255,255,.92));
}
.ifk-pipe-pill{
  border:1px solid rgba(0,166,220,.18);
  background:rgba(0,166,220,.08);
  color:var(--ifk-navy);
}
.ifk-pipe-key{color:rgba(0,50,90,.65);}
.ifk-pipe-val{color:var(--ifk-navy);}

/* charts layout */
.ifk-charts-wrap{
  display:grid;
  grid-template-columns: 1.2fr .8fr;
  gap:16px;
  align-items:stretch;
}
@media (max-width: 1200px){
  .ifk-charts-wrap{grid-template-columns:1fr;}
}
.ifk-chart-panel{
  background:linear-gradient(180deg, rgba(0,50,90,.03), rgba(0,166,220,.02));
  border:1px solid rgba(0,50,90,.08);
  border-radius:18px;
  padding:14px 14px 10px;
  position:relative;
  overflow:hidden;
}
.ifk-chart-panel:before{
  content:"";
  position:absolute; inset:-40px -60px auto auto;
  width:180px; height:180px;
  background:radial-gradient(circle at 30% 30%, rgba(150,188,23,.20), rgba(0,166,220,0));
  pointer-events:none;
}
.ifk-chart-head{
  display:flex; align-items:center; justify-content:space-between;
  gap:10px; margin-bottom:8px;
}
.ifk-chart-head .ifk-chart-title{font-weight:900;color:var(--ifk-navy);}
.ifk-chart-head .ifk-chart-note{color:rgba(0,50,90,.55); font-weight:700;}
.ifk-chart-canvas{
  width:100% !important;
  height:420px !important;
  display:block;
}
.ifk-chart-canvas.sm{height:320px !important;}
.ifk-chart-canvas.xs{height:260px !important;}
.ifk-chart-empty{
  height:420px;
  display:flex; align-items:center; justify-content:center;
  border:1px dashed rgba(0,50,90,.18);
  border-radius:16px;
  color:rgba(0,50,90,.60);
  font-weight:800;
  background:rgba(255,255,255,.7);
}

/* monthly area should be bigger */
.ifk-chart-area--big .ifk-chart-canvas{height:520px !important;}
.ifk-chart-area--big .ifk-chart-empty{height:520px;}

/* make legend nicer */
.ifk-chart-panel .chartjs-legend{font-size:12px;}


/* ================================
   PRO charts polish
================================ */
.ifk-charts-section{margin-top:18px;}
.ifk-charts-section .ifk-box{background:linear-gradient(180deg,#ffffff 0%, #fbfcff 100%); border:1px solid rgba(2,6,23,.06); box-shadow:0 12px 40px rgba(2,6,23,.06);}
.ifk-charts-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:14px;}
.ifk-charts-grid .ifk-col-6{grid-column:span 6;}
.ifk-charts-grid .ifk-col-4{grid-column:span 4;}
.ifk-charts-grid .ifk-col-3{grid-column:span 3;}
@media(max-width:1600px){.ifk-charts-grid .ifk-col-6{grid-column:span 12;}.ifk-charts-grid .ifk-col-4{grid-column:span 6;}.ifk-charts-grid .ifk-col-3{grid-column:span 6;}}
@media(max-width:768px){.ifk-charts-grid .ifk-col-4,.ifk-charts-grid .ifk-col-3{grid-column:span 12;}}
.ifk-box-head{padding:14px 16px;border-bottom:1px solid rgba(2,6,23,.06);}
.ifk-box-title{font-weight:900;letter-spacing:.2px}
.ifk-box-sub{opacity:.75}
.ifk-chart-area{position:relative;min-height:320px;padding:12px 10px;background:
  radial-gradient(1200px 260px at 0% 0%, rgba(0,166,220,.08), transparent 55%),
  radial-gradient(900px 220px at 100% 0%, rgba(150,188,23,.10), transparent 50%),
  radial-gradient(800px 220px at 50% 100%, rgba(0,50,90,.08), transparent 55%);
  border-radius:14px;
}
.ifk-chart-area.big{min-height:380px;}
.ifk-chart-area.mini{min-height:280px;}
.ifk-chart-area canvas{width:100% !important;height:100% !important;}
.ifk-chart-legend{margin-top:10px;display:flex;flex-wrap:wrap;gap:10px;align-items:center}
.ifk-pill{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;background:#f1f5f9;border:1px solid rgba(2,6,23,.06);font-weight:700}
.ifk-dot{width:10px;height:10px;border-radius:50%}


/* Pipeline status colors (per key) */
.ifk-pipe-item[data-key="applied"]{--pipe:#00a6dc;}
.ifk-pipe-item[data-key="interview_scheduled"]{--pipe:#00325a;}
.ifk-pipe-item[data-key="pass"]{--pipe:#96bc17;}
/*.ifk-pipe-item[data-key="prepare_documents"]{--pipe:#f59e0b;}
.ifk-pipe-item[data-key="done_documents"]{--pipe:#10b981;}
.ifk-pipe-item[data-key="docs_done"]{--pipe:#10b981;}
.ifk-pipe-item[data-key="done"]{--pipe:#4bbeec;}
.ifk-pipe-item[data-key="waiting_coe"]{--pipe:#6366f1;}
.ifk-pipe-item[data-key="got_coe"]{--pipe:#06b6d4;}*/
.ifk-pipe-item[data-key="docs_preparing"]{--pipe:#f59e0b;}
.ifk-pipe-item[data-key="docs_done"]{--pipe:#10b981;}
.ifk-pipe-item[data-key="done"]{--pipe:#4bbeec;}
.ifk-pipe-item[data-key="coe_waiting"]{--pipe:#6366f1;}
.ifk-pipe-item[data-key="has_coe"]{--pipe:#06b6d4;}
.ifk-pipe-item[data-key="visa_processing"]{--pipe:#8b5cf6;}
.ifk-pipe-item[data-key="ticket_booking"]{--pipe:#ef4444;}
.ifk-pipe-item[data-key="pre_departure"]{--pipe:#fb7185;}
.ifk-pipe-item[data-key="entry"]{--pipe:#22c55e;}
.ifk-pipe-item[data-key="returned"]{--pipe:#64748b;}
.ifk-pipe-item[data-key="cancelled"]{--pipe:#0f172a;}
.ifk-pipe-item{border-left:6px solid var(--pipe,#00a6dc);}
.ifk-pipe-item .ifk-pill{background:rgba(255,255,255,.65);backdrop-filter:saturate(180%) blur(6px);}


/* Use pipeline color */
.ifk-pipe-item{border-top:5px solid var(--pipe,#00a6dc); box-shadow:0 10px 30px rgba(2,6,23,.06);}
.ifk-pipe-item .ifk-pill{border-color:rgba(2,6,23,.06);}
.ifk-pipe-item[data-key] .ifk-pill{background:rgba(255,255,255,.72);}
.ifk-pipe-item .ifk-pipe-num{color:var(--pipe,#00a6dc);}
.ifk-pipe-item .ifk-pipe-bar span{background:linear-gradient(90deg, var(--pipe,#00a6dc), rgba(255,255,255,.35));}


/* === PRO charts layout === */
/* Legacy chart boxes (ifk-box) */
.ifk-box{background:#fff;border-radius:18px;border:1px solid #eef2f7;box-shadow:0 10px 35px rgba(0,0,0,.06);overflow:hidden;}
.ifk-box-head{padding:14px 16px 10px;border-bottom:1px solid #f1f5f9;display:flex;align-items:flex-start;justify-content:space-between;gap:12px;}
.ifk-box-title{display:flex;align-items:center;gap:10px;font-weight:900;color:#0f172a;}
.ifk-box-title i{font-size:16px;}
.ifk-box-sub{font-size:12px;font-weight:700;color:#64748b;margin-top:2px;white-space:nowrap;}
.ifk-chart-area{padding:14px 16px;}
.ifk-chart-area--mini{height:300px;}
.ifk-chart-area--big{height:380px;}
.ifk-chart-area canvas{width:100% !important;height:100% !important;}
/* Give charts section breathing room */
.sc-charts-bottom,.ifk-charts-bottom{margin-top:18px;clear:both;}

.sc-charts-bottom{margin-top:18px;}
.sc-charts-bottom .sc-card-body{padding:18px 18px 10px;}
.sc-charts-title{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0 0 10px;}
.sc-charts-title h4{margin:0;font-weight:900;font-size:16px;color:#00325a;letter-spacing:.2px;}
.sc-charts-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:14px;}
.sc-chart-box{grid-column:span 3;background:#fff;border-radius:18px;box-shadow:0 10px 35px rgba(0,0,0,.06);border:1px solid #eef2f7;overflow:hidden;}
.sc-chart-box .sc-hd{padding:14px 16px 10px;display:flex;align-items:center;justify-content:space-between;gap:10px;border-bottom:1px solid #f1f5f9;}
.sc-chart-box .sc-hd .t{font-weight:900;color:#0f172a;font-size:13px;}
.sc-chart-box .sc-hd .sub{font-weight:700;color:#64748b;font-size:12px;}
.sc-chart-box .sc-bd{padding:14px 16px;}
.sc-chart-box canvas{width:100% !important;height:260px !important;}
.sc-chart-wide{grid-column:span 12;}
.sc-chart-wide canvas{height:320px !important;}
@media (max-width: 1400px){
  .sc-chart-box{grid-column:span 4;}
}
@media (max-width: 992px){
  .sc-chart-box{grid-column:span 6;}
  .sc-chart-box canvas{height:240px !important;}
}
@media (max-width: 576px){
  .sc-chart-box{grid-column:span 12;}
  .sc-chart-wide canvas{height:260px !important;}
}

/* ===== CHARTS PRO FIX (responsive + stable sizing) ===== */
.ifk-charts{margin-top:18px;}
/* make bootstrap columns equal-height for charts */
.ifk-charts .row{display:flex;flex-wrap:wrap;margin-left:-9px;margin-right:-9px;}
.ifk-charts .row > [class*="col-"]{padding-left:9px;padding-right:9px;display:flex;}
.ifk-charts .ifk-box{width:100%;display:flex;flex-direction:column;}
.ifk-charts .ifk-box-body{flex:1;display:flex;flex-direction:column;}
/* chart area: fixed height, canvas fills 100% to avoid "vỡ" */
.ifk-chart-area{position:relative;flex:1;min-height:260px;}
.ifk-chart-area--mini{height:260px}
.ifk-chart-area--big{height:520px}
.ifk-chart-area canvas{position:absolute;inset:0;width:100% !important;height:100% !important;display:block;}
/* responsive heights */
@media (max-width: 991px){
  .ifk-chart-area--big{height:420px}
  .ifk-chart-area--mini{height:240px}
}
@media (max-width: 767px){
  .ifk-charts .row > [class*="col-"]{width:100% !important;}
  .ifk-chart-area--big{height:360px}
  .ifk-chart-area--mini{height:240px}
}
/* prevent card overflow */
.ifk-box{overflow:hidden;}

/* ===== V8 Layout/Chart Fix ===== */
.ifk-reports-wrap, .ifk-wrap, .content, #wrapper { overflow-x: hidden; }
.ifk-charts { width:100%; max-width:100%; }
.ifk-charts .row{ margin-left:0 !important; margin-right:0 !important; }
.ifk-charts .row > [class*="col-"]{ padding-left:0 !important; padding-right:0 !important; }
.ifk-charts .row.ifk-charts-row{ display:flex; flex-wrap:wrap; gap:14px; }
.ifk-charts .row.ifk-charts-row:before, .ifk-charts .row.ifk-charts-row:after{ display:none !important; }
.ifk-box{ overflow:hidden; }
.ifk-box .ifk-box-body{ padding:14px 14px 10px; }
.ifk-box .ifk-box-title{ font-weight:900; letter-spacing:.2px; }
.ifk-box canvas{ display:block; width:100% !important; height:100% !important; }
#ifkMonthlyWrap{ height:420px; }
@media (max-width: 1200px){
  .ifk-charts .row.ifk-charts-row{ gap:12px; }
}
@media (max-width: 991px){
  .ifk-box{ height:300px; }
  #ifkMonthlyWrap{ height:360px; }
}
@media (max-width: 767px){
  .ifk-charts .row.ifk-charts-row{ flex-direction:column; }
  .ifk-box{ height:280px; }
  #ifkMonthlyWrap{ height:320px; }
}
/* make charts look less washed out */
.ifk-box{ background: linear-gradient(180deg, rgba(0,50,90,.04), rgba(0,50,90,.02) 55%, rgba(150,188,23,.03)); border:1px solid rgba(0,50,90,.08); }
.ifk-box:before{ content:''; position:absolute; inset:0; pointer-events:none; background: radial-gradient(900px 220px at 20% 0%, rgba(0,166,220,.10), transparent 55%), radial-gradient(900px 220px at 80% 0%, rgba(150,188,23,.10), transparent 55%); opacity:.9; }
.ifk-box{ position:relative; }
.ifk-box > *{ position:relative; }



/* === IFK UI PATCH V9: keep charts inside content + solid white bg === */
.ifk-report-wrap, .ifk-charts-section{max-width:100% !important; overflow-x:hidden !important;}
.ifk-charts-section{background:#fff !important; border-radius:18px; padding:14px; margin-top:16px;}
.ifk-charts-row{display:grid !important; grid-template-columns:repeat(12,1fr) !important; gap:14px !important;}
.ifk-chart-panel{background:#fff !important; border:1px solid rgba(0,50,90,.10) !important; border-radius:18px !important; box-shadow:0 10px 30px rgba(17,24,39,.05) !important; overflow:hidden !important; min-width:0 !important;}
.ifk-chart-panel canvas{display:block; width:100% !important; height:100% !important;}
.ifk-chart-body{height:260px !important;}
.ifk-chart-body.ifk-chart-body--wide{height:340px !important;}
@media (max-width:1200px){
  .ifk-chart-panel{grid-column:span 6 !important;}
}
@media (max-width:768px){
  .ifk-chart-panel{grid-column:span 12 !important;}
  .ifk-chart-body{height:240px !important;}
  .ifk-chart-body.ifk-chart-body--wide{height:300px !important;}
}
/* Prevent any accidental overlay from pseudo elements */
.ifk-chart-panel:before, .ifk-chart-panel:after{pointer-events:none;}


/* ===============================
   IFK Charts Layout FIX (PRO)
   - keep inside container
   - prevent overflow to sidebar
   - consistent card heights
=============================== */
.ifk-charts-section{max-width:100%; overflow:hidden;}
.ifk-charts-row{margin-left:0 !important;margin-right:0 !important;}
.ifk-charts-row>[class*="col-"]{padding-left:10px !important;padding-right:10px !important;}
.ifk-box{background:#fff;border:1px solid rgba(15,23,42,.06);border-radius:18px;box-shadow:0 12px 32px rgba(2,6,23,.06);overflow:hidden;}
.ifk-box-head{padding:14px 16px 0;}
.ifk-box-title{display:flex;align-items:center;gap:10px;font-weight:900;color:#0b2440;}
.ifk-box-sub{margin-top:4px;color:#64748b;font-weight:600;font-size:12px;}
.ifk-chart-area{position:relative;max-width:100%;overflow:hidden;padding:10px 14px 14px;}
.ifk-chart-area--mini{height:240px;}
.ifk-chart-area--big{height:340px;}
.ifk-chart-area canvas{display:block !important; width:100% !important; height:100% !important; max-width:100% !important;}
/* Fix Chart.js inline size weirdness in bootstrap columns */
.ifk-chart-canvas{max-width:100% !important;}
/* Responsive */
@media (max-width: 991px){
  .ifk-chart-area--mini{height:220px;}
  .ifk-chart-area--big{height:300px;}
}
@media (max-width: 767px){
  .ifk-charts-row>[class*="col-"]{padding-left:8px !important;padding-right:8px !important;}
  .ifk-chart-area--mini{height:210px;}
  .ifk-chart-area--big{height:280px;}
}

/* ===== Stats (NO canvas) ===== */
.ifk-stats-wrap{margin-top:18px;}
.ifk-card-head{display:flex;align-items:flex-end;justify-content:space-between;gap:12px;margin-bottom:10px}
.ifk-card-title{font-weight:900;color:#0b2239;font-size:16px}
.ifk-card-sub{color:#64748b;font-size:12px;font-weight:700}
.ifk-stats-grid{display:grid;grid-template-columns:1.35fr 1fr 0.85fr;gap:14px}
.ifk-stats-card{background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:18px;box-shadow:0 12px 35px rgba(2,6,23,.06);padding:14px;min-width:0}
.ifk-stats-title{font-weight:900;color:#0b2239;margin-bottom:10px}
.ifk-empty{padding:14px;border-radius:14px;background:#f8fafc;color:#64748b;font-weight:700}
.ifk-stat-row{display:grid;grid-template-columns:190px 1fr 54px;gap:10px;align-items:center;margin:8px 0;min-width:0}
.ifk-stat-label{font-weight:800;color:#0b2239;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ifk-stat-val{font-weight:900;text-align:right;color:#0b2239}
.ifk-stat-bar{height:10px;background:#eef2f7;border-radius:999px;overflow:hidden}
.ifk-stat-fill{display:block;height:100%;background:var(--ifk-c,#00a6dc);border-radius:999px;box-shadow:0 8px 18px rgba(0,0,0,.08)}
.ifk-toplist{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:8px}
.ifk-topitem{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:14px;background:#f8fafc;border:1px solid rgba(15,23,42,.06)}
.ifk-topdot{width:10px;height:10px;border-radius:999px;background:var(--ifk-c,#00a6dc);box-shadow:0 6px 18px rgba(0,0,0,.12)}
.ifk-topname{font-weight:800;color:#0b2239;flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ifk-topval{font-weight:900;color:#0b2239}
.ifk-mini-kpi{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:12px}
.ifk-mini{background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:14px;padding:10px 10px}
.ifk-mini-lbl{font-size:11px;color:#64748b;font-weight:800}
.ifk-mini-val{font-size:18px;font-weight:900;color:#0b2239;line-height:1.1;margin-top:2px}
.ifk-rate{display:flex;flex-direction:column;align-items:flex-start;gap:2px;margin-top:2px}
.ifk-rate-num{font-size:40px;font-weight:1000;color:#00325a;letter-spacing:-1px}
.ifk-rate-sub{font-weight:800;color:#64748b}
.ifk-rate-bar{height:12px;background:#eef2f7;border-radius:999px;overflow:hidden;margin-top:12px}
.ifk-rate-fill{display:block;height:100%;background:linear-gradient(90deg,#96bc17,#00a6dc);border-radius:999px}
.ifk-rate-meta{display:flex;justify-content:space-between;margin-top:8px;color:#64748b;font-weight:800}
@media (max-width:1200px){.ifk-stats-grid{grid-template-columns:1fr}}
@media (max-width:640px){.ifk-stat-row{grid-template-columns:1fr;gap:6px}.ifk-stat-val{text-align:left}}
/* ===== /Stats ===== */

/* Simple CSS chart (stable, no canvas) */
.ifk-simplechart{padding:6px 2px 2px;}
.ifk-simplechart-top{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:6px 4px 12px;}
.ifk-simplechart-name{font-weight:900;font-size:15px;color:#0f172a;}
.ifk-simplechart-total{font-size:13px;color:#64748b;}
.ifk-chartbar{height:18px;border-radius:999px;background:#eef2f7;overflow:hidden;display:flex;box-shadow:inset 0 0 0 1px rgba(15,23,42,.05);}
.ifk-chartbar-seg{height:100%;min-width:3px;opacity:.95;}
.ifk-chartbar-seg:hover{opacity:1;filter:saturate(1.1) brightness(1.02);}
.ifk-legend{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px 14px;margin:14px 4px 6px;}
@media(max-width:900px){.ifk-legend{grid-template-columns:1fr;}}
.ifk-legend-item{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:12px;background:#fff;border:1px solid rgba(15,23,42,.06);}
.ifk-legend-dot{width:10px;height:10px;border-radius:999px;flex:0 0 10px;}
.ifk-legend-name{flex:1 1 auto;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.ifk-legend-val{font-weight:900;color:#0f172a;margin-left:auto;}
.ifk-legend-pct{font-size:12px;color:#64748b;min-width:52px;text-align:right;}


/* ===== SaaS dashboard upgrades (keeps old logic) ===== */
.bg-gradient-navy{background:linear-gradient(135deg,#00325a,#1e3c72);}
.bg-gradient-sky{background:linear-gradient(135deg,#00a6dc,#2a9df4);}
.bg-gradient-green{background:linear-gradient(135deg,#11998e,#38ef7d);}
.bg-gradient-purple{background:linear-gradient(135deg,#6a11cb,#2575fc);}

.ifk-kpi-modern{
  border-radius:16px; color:#fff; padding:18px 18px 16px;
  box-shadow:0 12px 30px rgba(0,0,0,.10);
  position:relative; overflow:hidden; margin-bottom:16px;
}
.ifk-kpi-modern:before{
  content:''; position:absolute; right:-40px; top:-40px;
  width:120px; height:120px; border-radius:28px;
  background:rgba(255,255,255,.16); transform:rotate(18deg);
}
.ifk-kpi-top{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;position:relative;z-index:1;}
.ifk-kpi-label{font-size:12px;text-transform:uppercase;letter-spacing:.10em;opacity:.9;font-weight:900;}
.ifk-kpi-value{font-size:28px;font-weight:1000;letter-spacing:-.02em;line-height:1.1;margin-top:6px;}
.ifk-kpi-sub{font-size:12px;opacity:.9;margin-top:8px;}
.ifk-kpi-icon{
  width:44px;height:44px;border-radius:14px;
  display:flex;align-items:center;justify-content:center;
  background:rgba(255,255,255,.18);
  font-size:18px;
}
.ifk-kpi-suffix{font-size:16px;font-weight:900;opacity:.95;margin-left:2px;}

.ifk-saas-card{
  background:#fff;border:1px solid rgba(15,23,42,.08);
  border-radius:18px;
  box-shadow:0 14px 40px rgba(2,8,23,.08);
  padding:14px 14px 10px;
  margin-bottom:18px;
}
.ifk-saas-head{display:flex;align-items:flex-end;justify-content:space-between;gap:12px;margin-bottom:8px;}
.ifk-saas-title{font-weight:1000;color:var(--ifk-navy);letter-spacing:-.01em;}
.ifk-saas-title i{color:var(--ifk-sky);margin-right:8px;}
.ifk-saas-sub{color:#6b7280;font-size:12px;margin-top:4px;}

.ifk-chart-wrap{position:relative;height:320px;}
.ifk-chart-wrap--line{height:360px;}
.ifk-chart-wrap canvas{position:absolute;inset:0;width:100%!important;height:100%!important;}
.ifk-chart-wrap .ifk-empty{
  position:absolute; inset:0;
  display:none; align-items:center; justify-content:center;
  color:#6b7280; font-weight:800;
  background:linear-gradient(180deg, rgba(0,50,90,.03), rgba(0,50,90,.01));
  border-radius:14px;
}
.ifk-chart-wrap.is-empty .ifk-empty{display:flex;}
.ifk-chart-row{margin-top:4px;}


/* ===== IFK DASHBOARD FIX (layout + pipeline) ===== */
.ifk-container{max-width:1600px;margin:0 auto;padding-right:10px;padding-left:10px}
.container-fluid{padding-right:10px!important;padding-left:10px!important}
.content{padding-right:10px!important}
.content-wrapper{padding-right:15px!important}

.panel_saas{background:#fff;border-radius:18px;box-shadow:0 8px 30px rgba(16,24,40,.06);border:1px solid #eef2f7}

/* Pipeline grid cards */
.ifk-pipeline-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:18px}
.ifk-pipeline-card{background:#f9fbfd;border-radius:16px;padding:18px;border:1px solid #e5edf5;position:relative;transition:.25s ease}
.ifk-pipeline-card:hover{transform:translateY(-4px);box-shadow:0 12px 25px rgba(0,76,153,.08)}
.ifk-pipeline-card .status-title{font-weight:800;font-size:15px;color:#1e293b}
.ifk-pipeline-card .status-key{font-size:13px;color:#94a3b8;margin-top:6px}
.ifk-pipeline-card .status-count{position:absolute;top:16px;right:16px;font-weight:1000;font-size:18px}

/* IFK border accents */
.ifk-border-blue{border-left:5px solid #1e88e5}
.ifk-border-green{border-left:5px solid #2ecc71}
.ifk-border-orange{border-left:5px solid #f39c12}
.ifk-border-purple{border-left:5px solid #7c4dff}
.ifk-border-red{border-left:5px solid #e74c3c}
.ifk-border-teal{border-left:5px solid #00bcd4}
.ifk-border-dark{border-left:5px solid #2c3e50}

</style>

<?php
/* ================= helpers ================= */
function ifk_opt($val, $cur){ return ((string)$val === (string)$cur) ? 'selected' : ''; }
function ifk_text($v, $default='—'){ return ($v === '' || $v === null) ? $default : $v; }
//
function ifk_date_dmY($date, $default = '—'){
  $date = trim((string)$date);
  if ($date === '' || $date === '0000-00-00') return $default;

  $ts = strtotime($date);
  if (!$ts) return $default;

  return date('d-m-Y', $ts);
}
//
function ifk_status_class($key){
  $k = strtolower((string)$key);
  $known = [
    'pass' => 'st-green','passed'=>'st-green','fail' => 'st-red',
    'cancelled'=>'st-red','canceled'=>'st-red','cancel'=>'st-red',
   'docs_done'=>'st-green',        // ✅ thêm
'done_documents'=>'st-green','entry'=>'st-entry','in_japan'=>'st-blue','returned'=>'st-yellow',
    'waiting_coe'=>'st-blue','got_coe'=>'st-blue','coe_waiting'=>'st-blue','coe_received'=>'st-green',
    'interview_scheduled'=>'st-blue','prepare_documents'=>'st-yellow','visa_processing'=>'st-blue',
    'ticket_booking'=>'st-yellow','pre_departure'=>'st-yellow',
    'applied'=>'st-gray','not_updated'=>'st-gray',
  ];
  if (isset($known[$k])) return $known[$k];
  if (strpos($k,'fail')!==false || strpos($k,'cancel')!==false || strpos($k,'reject')!==false) return 'st-red';
  if (strpos($k,'pass')!==false || strpos($k,'done')!==false || strpos($k,'success')!==false) return 'st-green';
  if (strpos($k,'wait')!==false || strpos($k,'interview')!==false || strpos($k,'coe')!==false || strpos($k,'visa')!==false) return 'st-blue';
  if (strpos($k,'prepare')!==false || strpos($k,'ticket')!==false || strpos($k,'pre')!==false || strpos($k,'return')!==false) return 'st-yellow';
  return 'st-gray';
}

function ifk_hex_rgba($hex, $alpha=0.12){
  $hex = ltrim((string)$hex,'#');
  if(strlen($hex)===3){
    $r = hexdec(str_repeat($hex[0],2));
    $g = hexdec(str_repeat($hex[1],2));
    $b = hexdec(str_repeat($hex[2],2));
  } else {
    $r = hexdec(substr($hex,0,2));
    $g = hexdec(substr($hex,2,2));
    $b = hexdec(substr($hex,4,2));
  }
  $a = max(0,min(1,(float)$alpha));
  return "rgba($r,$g,$b,$a)";
}

function ifk_status_color($key){
  $k = strtolower(trim((string)$key));
  $map = [
    'not_updated'         => '#9ca3af',
    'applied'             => '#00a6dc',
    'interview_scheduled' => '#00325a',
    'pass'                => '#96bc17',
    'passed'              => '#96bc17',
    'fail'                => '#ef4444',
    'prepare_documents'   => '#f59e0b',
    'docs_preparing'      => '#f59e0b',    
    'docs_done'           => '#22c55e',
    'done_documents'      => '#22c55e',
    'waiting_coe'         => '#6366f1',
    'coe_waiting'         => '#6366f1',
    'got_coe'             => '#8b5cf6',
    'has_coe'             => '#8b5cf6',
    'visa_processing'     => '#0ea5e9',
    'ticket_booking'      => '#fb7185',
    'pre_departure'       => '#f97316',
    'entry'               => '#00a6dc',
    'in_japan'            => '#96bc17',
    'returned'            => '#14b8a6',
    'cancelled'           => '#6b7280',
    'canceled'            => '#6b7280',
    'done'                => '#16a34a',
    'received'            => '#94a3b8',    
  ];
  if(isset($map[$k])) return $map[$k];
  // stable hash -> pleasant palette
  $palette = ['#00a6dc','#00325a','#96bc17','#f59e0b','#8b5cf6','#0ea5e9','#fb7185','#10b981','#14b8a6','#ef4444','#6b7280'];
  $h = 0; for($i=0;$i<strlen($k);$i++){ $h = ($h*31 + ord($k[$i])) % 100000; }
  return $palette[$h % count($palette)];
}

function ifk_status_badge($key, $label=''){
  $cls = ifk_status_class($key);
  $text = ($label !== '' ? $label : ((string)$key !== '' ? (string)$key : '—'));
  return '<span class="status-badge '.$cls.'">'.htmlspecialchars($text).'</span>';
}

/* ================= inputs from controller ================= */
$tab     = $tab ?? ($this->input->get('tab') ?: 'job_orders');
$section = $section ?? ($this->input->get('section') ?: 'summary');
$filters = $filters ?? [];
$report  = $report ?? [];
$title   = $title ?? 'BÁO CÁO TỔNG HỢP';

/**
 * ✅ Dùng đúng trạng thái như các view "Ứng tuyển" & "Đơn tuyển"
 * - Cả 2 view đều filter bằng name="status" và dùng $status_list
 */
$status_list = $status_list ?? [];
if (!is_array($status_list)) $status_list = [];

/* interview_result list (lọc nâng cao / hiển thị) */
$interview_status_list = $interview_status_list ?? [
  ''     => '— Chưa đánh giá —',
  'pass' => 'Đạt',
  'fail' => 'Rớt',
];

$fallback_progress_list = [
  'not_updated'         => 'Chưa cập nhật',
  'applied'             => 'Đã nộp đơn',
  'interview_scheduled' => 'Đã lên lịch phỏng vấn',
  'pass'                => 'Đạt',
  //'passed'              => 'Đạt',
  'fail'                => 'Rớt',

  //'prepare_documents'   => 'Đang làm hồ sơ',
  'docs_preparing'      => 'Đang làm hồ sơ',

  //'done_documents'      => 'Đã hoàn tất hồ sơ',
  'docs_done'           => 'Đã hoàn tất hồ sơ',

  //'waiting_coe'         => 'Chờ kết quả COE',
  'coe_waiting'         => 'Chờ kết quả COE',

  //'got_coe'             => 'Đã có COE – chờ nhập cảnh',
  'has_coe'             => 'Đã có COE – chờ nhập cảnh',
  //'coe_done'            => 'Đã có COE – chờ nhập cảnh',

  'visa_processing'     => 'Đang làm visa',
  'ticket_booking'      => 'Mua vé nhập cảnh',
  'pre_departure'       => 'Chuẩn bị bay',

  'entry'               => 'Đã nhập cảnh',
  'in_japan'            => 'Đang ở Nhật',
  'returned'            => 'Đã về nước',

  'cancelled'           => 'Đã hủy',
  'canceled'            => 'Đã hủy',
  'done'                => 'Hoàn thành chương trình',
  'received'            => 'Tiếp nhận đơn',
];

/* genders (filters) */
$gender_list = $gender_list ?? [
  ''   => 'Tất cả',
  'M'  => 'Nam',
  'F'  => 'Nữ',
  '1'  => 'Nam',
  '2'  => 'Nữ',
];

/* KPI + chart data from controller */
$kpi      = $kpi ?? ['total_jobs'=>0,'total_apps'=>0,'passed'=>0,'rate'=>0];
$pipeline = $pipeline ?? [];
$monthly  = $monthly ?? [];

// Chart title (tab-aware)
$chart_title = 'Thống kê theo tháng';
$chart_hint  = 'Bar chart';
if ($tab === 'job_orders') { $chart_title = 'Đơn tuyển theo tháng'; $chart_hint = 'Theo ngày phỏng vấn'; }
elseif ($tab === 'progress') { $chart_title = 'Nhập cảnh theo tháng'; $chart_hint = 'Theo ngày nhập cảnh'; }
else { $chart_title = 'Ứng tuyển theo tháng'; $chart_hint = 'Theo ngày ứng tuyển'; }

/* status list per tab */
$job_status_list      = ($tab === 'job_orders') ? $status_list : ($job_status_list ?? []);
$progress_status_list = ($tab !== 'job_orders') ? $status_list : ($progress_status_list ?? []);

if (empty($job_status_list) && $tab === 'job_orders') $job_status_list = [];
if (empty($progress_status_list) && $tab !== 'job_orders') $progress_status_list = $fallback_progress_list;

/* Year options */
/*$year_now = (int)date('Y');
$year_min = $year_now - 3;
$year_max = $year_now + 1;*/

$report_years = $report_years ?? [];
if (empty($report_years)) {
  $year_now = (int)date('Y');
  $report_years = range($year_now - 3, $year_now + 1);
}

/* Export urls (controller xử lý export nếu có) */
$base_q = $_GET;
unset($base_q['export']);
$export_csv  = admin_url('internship_management/reports?'.http_build_query(array_merge($base_q,['export'=>'csv'])));
$export_xlsx = admin_url('internship_management/reports?'.http_build_query(array_merge($base_q,['export'=>'xlsx'])));
?>

<div id="wrapper">
  <div class="content" id="ifk-reports-page">
    <div class="row">
      <div class="col-md-12">

        <div class="ifk-report-wrap">

          <div class="ifk-page-head">
            <div>
              <h3 class="ifk-title"><?= html_escape($title); ?></h3>
              <div class="ifk-sub">Báo cáo tổng hợp theo Đơn tuyển / Ứng tuyển / Tiến độ • Trạng thái đồng bộ từ view gốc</div>

            <div class="ifk-report-switch" style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
              <?php
                $baseSummary = admin_url('internship_management/reports?section=summary&tab=' . urlencode($tab));
                // Force tab=progress when entering management so we always have student-level rows for aggregations
                $baseMgmt    = admin_url('internship_management/reports?section=management&tab=progress');
              ?>
              <a href="<?= $baseSummary; ?>" class="btn btn-default <?= ($section!=='management' ? 'btn-info' : ''); ?>" style="border-radius:12px;font-weight:900;">
                <i class="fa fa-layer-group"></i> BÁO CÁO TỔNG HỢP
              </a>
          
            </div>

            </div>
            <!-- <div class="ifk-page-actions">
              <a class="btn btn-default" href="<?= $export_csv; ?>" title="Xuất CSV"><i class="fa fa-download"></i> CSV</a>
              <a class="btn btn-default" href="<?= $export_xlsx; ?>" title="Xuất Excel"><i class="fa fa-file-excel-o"></i> Excel</a>
              <a class="btn btn-default" href="<?= admin_url('internship_management/reports?tab='.urlencode($tab)); ?>"><i class="fa fa-refresh"></i> Reset</a>
            </div> -->
          </div>
          <div class="ifk-divider"></div>

          <!-- FILTERS -->
          <div class="ifk-report-box">
            <div class="ifk-box-head">
              <h4><i class="fa fa-filter" style="color:var(--ifk-sky);margin-right:8px;"></i>Bộ lọc báo cáo</h4>
              <div class="ifk-box-tools">
                <button type="button" class="btn btn-default" id="btnToggleAdv">
                  <i class="fa fa-sliders"></i> Nâng cao
                </button>

                <a class="btn btn-default" href="<?= $export_csv; ?>" title="Xuất CSV (controller xử lý export)">
                  <i class="fa fa-download"></i> CSV
                </a>
                <a class="btn btn-default" href="<?= $export_xlsx; ?>" title="Xuất Excel (controller xử lý export)">
                  <i class="fa fa-file-excel-o"></i> Excel
                </a>

                <a class="btn btn-default" href="<?= admin_url('internship_management/reports?tab='.urlencode($tab)); ?>">
                  <i class="fa fa-refresh"></i> Reset
                </a>
              </div>
            </div>

            <form method="get" action="<?= admin_url('internship_management/reports'); ?>" style="margin-top:14px;">
              <input type="hidden" name="tab" value="<?= html_escape($tab); ?>">

              <!-- ===== basic row ===== -->
              <div class="row ifk-filter-row">
                <div class="col-md-2">
                  <div class="ifk-filter-label">Năm</div>
                  <select class="form-control" name="year">
                    <option value="">Tất cả</option>
                    <!-- <?php for ($y=$year_min;$y<=$year_max;$y++): ?>
                      <option value="<?= $y ?>" <?= ifk_opt($y, $filters['year'] ?? ''); ?>><?= $y ?></option>
                    <?php endfor; ?> -->
                    <?php foreach ($report_years as $y): $y = (int)$y; ?>
                      <option value="<?= $y ?>" <?= ifk_opt($y, $filters['year'] ?? ''); ?>><?= $y ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="col-md-2">
                  <div class="ifk-filter-label">Tháng</div>
                  <select class="form-control" name="month">
                    <option value="">Tất cả</option>
                    <?php for ($m=1;$m<=12;$m++): ?>
                      <option value="<?= $m ?>" <?= ifk_opt($m, $filters['month'] ?? ''); ?>>Tháng <?= $m ?></option>
                    <?php endfor; ?>
                  </select>
                </div>

                <div class="col-md-3">
                  <div class="ifk-filter-label">Tìm kiếm</div>
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                    <input type="text" class="form-control" name="search"
                           value="<?= html_escape($filters['search'] ?? ''); ?>"
                           placeholder="<?= ($tab==='job_orders'?'Công ty / ngành / địa chỉ':'Tên SV / trường / ngành / công ty'); ?>">
                  </div>
                </div>

                <div class="col-md-3">
                  <div class="ifk-filter-label">Ngành</div>
                  <input type="text" class="form-control" name="major"
                         value="<?= html_escape($filters['major'] ?? ''); ?>"
                         placeholder="VD: Điều dưỡng, Cơ khí...">
                </div>

                <div class="col-md-2">
                  <div class="ifk-filter-label">Trạng thái</div>

                  <select class="form-control" name="status">
                    <option value="">Tất cả</option>
                    <?php
                      $list_for_status = ($tab === 'job_orders') ? $job_status_list : $progress_status_list;
                      foreach ($list_for_status as $k=>$lbl):
                    ?>
                      <option value="<?= html_escape($k) ?>" <?= ifk_opt($k, $filters['status'] ?? ''); ?>>
                        <?= html_escape($lbl) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>

                </div>
              </div>

              <!-- ===== advanced ===== -->
              <div class="ifk-adv" id="advBox">
                <div class="row">
                  <?php if ($tab === 'applications'): ?>
                    <div class="col-md-3">
                      <div class="ifk-filter-label">Trường</div>
                      <input type="text" class="form-control" name="school"
                             value="<?= html_escape($filters['school'] ?? ''); ?>"
                             placeholder="Tên trường">
                    </div>

                    <div class="col-md-3">
                      <div class="ifk-filter-label">Giới tính</div>
                      <select class="form-control" name="gender">
                        <?php foreach ($gender_list as $k=>$lbl): ?>
                          <option value="<?= html_escape($k) ?>" <?= ifk_opt($k, $filters['gender'] ?? ''); ?>><?= html_escape($lbl) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <div class="col-md-3">
                      <div class="ifk-filter-label">Kết quả PV</div>
                      <select class="form-control" name="interview_result">
                        <?php foreach ($interview_status_list as $k=>$lbl): ?>
                          <option value="<?= html_escape($k) ?>" <?= ifk_opt($k, $filters['interview_result'] ?? ''); ?>><?= html_escape($lbl) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <div class="col-md-3">
                      <div class="ifk-filter-label">Khoảng ngày ứng tuyển</div>
                      <div class="row">
                        <div class="col-xs-6">
                          <input type="text" class="form-control datepicker" name="date_from"
                                 value="<?= html_escape($filters['date_from'] ?? ''); ?>" placeholder="Từ ngày">
                        </div>
                        <div class="col-xs-6">
                          <input type="text" class="form-control datepicker" name="date_to"
                                 value="<?= html_escape($filters['date_to'] ?? ''); ?>" placeholder="Đến ngày">
                        </div>
                      </div>
                    </div>

                  <?php elseif ($tab === 'progress'): ?>
                    <div class="col-md-3">
                      <div class="ifk-filter-label">Doanh nghiệp</div>
                      <input type="text" class="form-control" name="company"
                             value="<?= html_escape($filters['company'] ?? ''); ?>"
                             placeholder="Tên công ty / đơn vị">
                    </div>

                    <div class="col-md-3">
                      <div class="ifk-filter-label">Kết quả PV</div>
                      <select class="form-control" name="interview_result">
                        <?php foreach ($interview_status_list as $k=>$lbl): ?>
                          <option value="<?= html_escape($k) ?>" <?= ifk_opt($k, $filters['interview_result'] ?? ''); ?>><?= html_escape($lbl) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <div class="col-md-3">
                      <div class="ifk-filter-label">Ngày nhập cảnh</div>
                      <div class="row">
                        <div class="col-xs-6">
                          <input type="text" class="form-control datepicker" name="entry_from"
                                 value="<?= html_escape($filters['entry_from'] ?? ''); ?>" placeholder="Từ ngày">
                        </div>
                        <div class="col-xs-6">
                          <input type="text" class="form-control datepicker" name="entry_to"
                                 value="<?= html_escape($filters['entry_to'] ?? ''); ?>" placeholder="Đến ngày">
                        </div>
                      </div>
                    </div>

                    <div class="col-md-3">
                      <div class="ifk-filter-label">Ngày về nước</div>
                      <div class="row">
                        <div class="col-xs-6">
                          <input type="text" class="form-control datepicker" name="return_from"
                                 value="<?= html_escape($filters['return_from'] ?? ''); ?>" placeholder="Từ ngày">
                        </div>
                        <div class="col-xs-6">
                          <input type="text" class="form-control datepicker" name="return_to"
                                 value="<?= html_escape($filters['return_to'] ?? ''); ?>" placeholder="Đến ngày">
                        </div>
                      </div>
                    </div>

                  <?php else: /* job_orders */ ?>
                    <div class="col-md-3">
                      <div class="ifk-filter-label">Doanh nghiệp</div>
                      <input type="text" class="form-control" name="company"
                             value="<?= html_escape($filters['company'] ?? ''); ?>"
                             placeholder="Tên công ty / đơn vị">
                    </div>

                    <div class="col-md-3">
                      <div class="ifk-filter-label">Đợt tuyển (Round)</div>
                      <input type="number" class="form-control" name="round_no"
                             value="<?= html_escape($filters['round_no'] ?? ''); ?>"
                             placeholder="VD: 1,2,3...">
                    </div>

                    <div class="col-md-3">
                      <div class="ifk-filter-label">Ngày phỏng vấn</div>
                      <div class="row">
                        <div class="col-xs-6">
                          <input type="text" class="form-control datepicker" name="interview_from"
                                 value="<?= html_escape($filters['interview_from'] ?? ''); ?>" placeholder="Từ ngày">
                        </div>
                        <div class="col-xs-6">
                          <input type="text" class="form-control datepicker" name="interview_to"
                                 value="<?= html_escape($filters['interview_to'] ?? ''); ?>" placeholder="Đến ngày">
                        </div>
                      </div>
                    </div>

                    <div class="col-md-3">
                      <div class="ifk-filter-label">Nhập cảnh</div>
                      <div class="row">
                        <div class="col-xs-6">
                          <input type="text" class="form-control datepicker" name="entry_from"
                                 value="<?= html_escape($filters['entry_from'] ?? ''); ?>" placeholder="Từ ngày">
                        </div>
                        <div class="col-xs-6">
                          <input type="text" class="form-control datepicker" name="entry_to"
                                 value="<?= html_escape($filters['entry_to'] ?? ''); ?>" placeholder="Đến ngày">
                        </div>
                      </div>
                    </div>

                    <?php endif; ?>
                </div>
              </div>

             <div style="margin-top:12px; display:flex; gap:10px; justify-content:flex-end;">
  <button type="submit" class="btn btn-primary">
    <i class="fa fa-filter"></i> Lọc
  </button>
</div>

</form>

          <!-- TABS + TABLE -->
          <div class="ifk-report-box">
            <?php if ($section === 'management'): ?>
            <?php $this->load->view('internship_management/reports/management'); ?>
          <?php else: ?>
<ul class="nav nav-tabs">
              <li class="<?= $tab == 'job_orders' ? 'active' : '' ?>">
                <a href="<?= admin_url('internship_management/reports?tab=job_orders'); ?>">Đơn tuyển</a>
              </li>
              <li class="<?= $tab == 'applications' ? 'active' : '' ?>">
                <a href="<?= admin_url('internship_management/reports?tab=applications'); ?>">Ứng tuyển</a>
              </li>
              <li class="<?= $tab == 'progress' ? 'active' : '' ?>">
                <a href="<?= admin_url('internship_management/reports?tab=progress'); ?>">Tiến độ</a>
              </li>
            </ul>

            <div style="padding-top:14px;">

              <?php if ($tab == 'applications'): ?>
                <div class="table-responsive">
                  <table class="table table-bordered table-hover ifk-table">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Họ tên</th>
                        <th>Trường</th>
                        <th>Đơn tuyển</th>
                        <th>Ngành</th>
                        <th>Ngày ứng tuyển</th>
                        <th>Kết quả PV</th>
                        <th>Tiến độ hồ sơ (status)</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($report)): foreach ($report as $r): ?>
                    
                      <?php
                        // giống view Ứng tuyển: progress lấy từ dossier_progress hoặc status
                        /*$progress = $r['dossier_progress'] ?? ($r['status'] ?? 'not_updated');
                        $progress_label = $progress_status_list[$progress] ?? ($fallback_progress_list[$progress] ?? (string)$progress);*/
                        
                        //$progress = $r['dossier_progress'] ?? ($r['status'] ?? 'not_updated');
                        $progress = $r['status'] ?? ($r['dossier_progress'] ?? 'not_updated');
                        $progress_key = function_exists('im_normalize_status') ? im_normalize_status($progress) : $progress;
                        $progress_label = $progress_status_list[$progress_key]
                            ?? ($fallback_progress_list[$progress_key] ?? (function_exists('im_status_label_vi') ? im_status_label_vi($progress_key) : (string)$progress_key));

                        $ir = $r['interview_result'] ?? ($r['interview'] ?? '');
                        $ir_label = $interview_status_list[$ir] ?? ((string)$ir ?: '—');

                        $company = $r['company_name_vi'] ?? ($r['company_name'] ?? ($r['job_name'] ?? ''));
                        $school  = $r['school_name_vi'] ?? ($r['school_name'] ?? ($r['school'] ?? ''));
                        $major   = $r['major_vi'] ?? ($r['major'] ?? '');
                        $apply   = $r['apply_date'] ?? ($r['datecreated'] ?? '');
                      ?>
                      <tr>
                        <td><?= (int)($r['id'] ?? 0) ?></td>
                        <td><?= html_escape($r['full_name'] ?? '') ?></td>
                        <td><?= html_escape(ifk_text($school,'')) ?></td>
                        <td><?= html_escape(ifk_text($company,'')) ?></td>
                        <td><?= html_escape(ifk_text($major,'')) ?></td>
                        <!--<td><?= !empty($apply) ? _d($apply) : '—' ?></td> -->
                        <td><?= ifk_date_dmY($apply) ?></td>
                        <td><?= ifk_status_badge($ir, $ir_label) ?></td>
                        <td><?= ifk_status_badge($progress, $progress_label) ?></td>
                      </tr>
                    <?php endforeach; else: ?>
                      <tr><td colspan="8" class="text-center text-muted">Không có dữ liệu.</td></tr>
                    <?php endif; ?>
                    </tbody>
                  </table>
                </div>

              <?php elseif ($tab == 'progress'): ?>
                <div class="table-responsive">
                  <table class="table table-bordered table-hover ifk-table">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Họ tên</th>
                        <th>Doanh nghiệp</th>
                        <th>Ngày PV</th>
                        <th>Kết quả PV</th>
                        <th>Tiến độ hồ sơ (status)</th>
                        <th>Nhập cảnh</th>
                        <th>Về nước</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($report)): foreach ($report as $r): ?>
                      <?php
                        $company = $r['company_name_vi'] ?? ($r['company_name'] ?? '');
                        $interview_date = $r['interview_date'] ?? '';
                        $entry_date = $r['entry_date'] ?? '';
                        $return_date = $r['return_date'] ?? '';

                        $ir = $r['interview_result'] ?? '';
                        $ir_label = $interview_status_list[$ir] ?? ((string)$ir ?: '—');

                        // tiến độ vẫn lấy status hoặc dossier_progress
                        /*$progress = $r['dossier_progress'] ?? ($r['status'] ?? 'not_updated');
                        $progress_label = $progress_status_list[$progress] ?? ($fallback_progress_list[$progress] ?? (string)$progress);*/
                        
                        //$progress = $r['dossier_progress'] ?? ($r['status'] ?? 'not_updated');
                        $progress = $r['status'] ?? ($r['dossier_progress'] ?? 'not_updated');
                        $progress_key = function_exists('im_normalize_status') ? im_normalize_status($progress) : $progress;
                        $progress_label = $progress_status_list[$progress_key]
                            ?? ($fallback_progress_list[$progress_key] ?? (function_exists('im_status_label_vi') ? im_status_label_vi($progress_key) : (string)$progress_key));
                        
                      ?>
                      <tr>
                        <td><?= (int)($r['id'] ?? 0) ?></td>
                        <td><?= html_escape($r['full_name'] ?? '') ?></td>
                        <td><?= html_escape(ifk_text($company,'')) ?></td>
                        <!-- <td><?= !empty($interview_date) ? _d($interview_date) : '—' ?></td> -->
                        <td><?= ifk_date_dmY($interview_date) ?></td>
                        <td><?= ifk_status_badge($ir, $ir_label) ?></td>
                        <td><?= ifk_status_badge($progress, $progress_label) ?></td>
                        <td><?= ifk_date_dmY($entry_date) ?></td>
                        <td><?= ifk_date_dmY($return_date) ?></td>
                        <!--<td><?= !empty($entry_date) ? _d($entry_date) : '—' ?></td> -->
                        <!--<td><?= !empty($return_date) ? _d($return_date) : '—' ?></td> -->
                      </tr>
                    <?php endforeach; else: ?>
                      <tr><td colspan="8" class="text-center text-muted">Không có dữ liệu.</td></tr>
                    <?php endif; ?>
                    </tbody>
                  </table>
                </div>

              <?php else: /* job_orders */ ?>
                <div class="table-responsive">
                  <table class="table table-bordered table-hover ifk-table">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Doanh nghiệp</th>
                        <th>Ngành</th>
                        <th class="text-center">SL Tuyển</th>
                        <th>Ngày PV</th>
                        <th>Nhập cảnh</th>
                        <th>Về nước</th>
                        <th class="text-center">Ứng tuyển</th>
                        <th>Trạng thái đơn (status)</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($report)): foreach ($report as $r): ?>
                      <?php
                        /*$st = $r['status'] ?? ($r['job_status'] ?? '');
                        $st_label = $job_status_list[$st] ?? ($r['status_label'] ?? ((string)$st ?: '—'));*/
                        
                        $st_raw = trim((string)($r['status'] ?? ($r['job_status'] ?? '')));
                        $st = function_exists('im_job_order_normalize_status')
                            ? im_job_order_normalize_status($st_raw)
                            : $st_raw;
                        
                        $st_label = $job_status_list[$st]
                            ?? (function_exists('im_job_order_status_label')
                                ? im_job_order_status_label($st, 'vi')
                                : ($r['status_label'] ?? ((string)$st ?: '—')));
                        

                        $company  = $r['company_name_vi'] ?? ($r['company_name'] ?? '');
                        $major    = $r['major_vi'] ?? ($r['major'] ?? '');
                      $qty = $r['qty_total']                 // ✅ key đúng từ Reports_model
  ?? $r['so_luong']
  ?? $r['recruit_quantity']
  ?? $r['quantity_total']
  ?? $r['quantity']
  ?? $r['qty']
  ?? $r['recruit_qty']
  ?? $r['quantity_need']
  ?? $r['qty_required']
  ?? $r['total_quantity']
  ?? 0;

$qty = (int)$qty;

$qty = (int)$qty;
                        $apps     = $r['total_applications'] ?? ($r['applied_total'] ?? 0);

                        $interview_date = $r['interview_date'] ?? '';
                        $entry_date     = $r['entry_date'] ?? '';
                        $return_date    = $r['return_date'] ?? '';
                      ?>
                      <tr>
                        <td><?= (int)($r['id'] ?? 0) ?></td>
                        <td><?= html_escape(ifk_text($company,'')) ?></td>
                        <td><?= html_escape(ifk_text($major,'')) ?></td>
                        <td class="text-center"><strong><?= (int)$qty ?></strong></td>
                        <!-- <td><?= !empty($interview_date) ? _d($interview_date) : '—' ?></td>
                        <td><?= !empty($entry_date) ? _d($entry_date) : '—' ?></td>
                        <td><?= !empty($return_date) ? _d($return_date) : '—' ?></td> -->
                        <td><?= ifk_date_dmY($interview_date) ?></td>
                        <td><?= ifk_date_dmY($entry_date) ?></td>
                        <td><?= ifk_date_dmY($return_date) ?></td>
                        <td class="text-center"><strong><?= (int)$apps ?></strong></td>
                        <td><?= ifk_status_badge($st, $st_label) ?></td>
                      </tr>
                    <?php endforeach; else: ?>
                      <tr><td colspan="9" class="text-center text-muted">Không có dữ liệu.</td></tr>
                    <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              <?php endif; ?>

            </div>
          <?php endif; ?>

          </div>

        
   

      <?php
        $pipeline_total_all = 0;
        if (!empty($pipeline_totals) && is_array($pipeline_totals)) {
          foreach($pipeline_totals as $__v){ $pipeline_total_all += (int)$__v; }
        }
        $pipeline_total_all = max(0, (int)$pipeline_total_all);
      ?>

      <?php if($pipeline_total_all <= 0): ?>
        <div class="ifk-empty">
          <div class="ifk-empty-icon"><i class="fa fa-pie-chart"></i></div>
          <div class="ifk-empty-title">Chưa có dữ liệu để vẽ biểu đồ</div>
          <div class="ifk-empty-sub">Hãy thử đổi bộ lọc hoặc chọn khoảng thời gian có dữ liệu.</div>
        </div>
      <?php else: ?>
        <div class="ifk-simplechart">
          <div class="ifk-simplechart-top">
            <div class="ifk-simplechart-name">Phân bổ trạng thái (Pipeline)</div>
            <div class="ifk-simplechart-total">Tổng: <strong><?php echo number_format($pipeline_total_all); ?></strong></div>
          </div>

          <div class="ifk-chartbar" role="img" aria-label="Phân bổ trạng thái pipeline">
            <?php foreach($pipeline_statuses as $stKey => $stLabel):
              $cnt = isset($pipeline_totals[$stKey]) ? (int)$pipeline_totals[$stKey] : 0;
              if($cnt <= 0) continue;
              $pct = ($pipeline_total_all > 0) ? round(($cnt / $pipeline_total_all) * 100, 2) : 0;
              $col = ifk_status_color($stKey);
            ?>
              <div class="ifk-chartbar-seg"
                   style="width:<?php echo $pct; ?>%;background:<?php echo $col; ?>;"
                   title="<?php echo htmlspecialchars($stLabel, ENT_QUOTES, 'UTF-8'); ?>: <?php echo number_format($cnt); ?> (<?php echo $pct; ?>%)">
              </div>
            <?php endforeach; ?>
          </div>

          <div class="ifk-legend">
            <?php foreach($pipeline_statuses as $stKey => $stLabel):
              $cnt = isset($pipeline_totals[$stKey]) ? (int)$pipeline_totals[$stKey] : 0;
              if($cnt <= 0) continue;
              $pct = ($pipeline_total_all > 0) ? round(($cnt / $pipeline_total_all) * 100, 2) : 0;
              $col = ifk_status_color($stKey);
            ?>
              <div class="ifk-legend-item">
                <span class="ifk-legend-dot" style="background:<?php echo $col; ?>"></span>
                <span class="ifk-legend-name"><?php echo htmlspecialchars($stLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="ifk-legend-val"><?php echo number_format($cnt); ?></span>
                <span class="ifk-legend-pct"><?php echo $pct; ?>%</span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
    <!-- /STATS -->

 <!-- KPI + CHARTS (SaaS) -->
              <div class="row">
            <div class="col-md-3">
              <div class="ifk-kpi-modern bg-gradient-navy">
                <div class="ifk-kpi-top">
                  <div>
                    <div class="ifk-kpi-label">Đơn tuyển</div>
                    <div class="ifk-kpi-value" data-count="<?= (int)($kpi['total_jobs'] ?? 0); ?>">0</div>
                    <div class="ifk-kpi-sub">Tổng số job orders</div>
                  </div>
                  <div class="ifk-kpi-icon"><i class="fa fa-briefcase"></i></div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="ifk-kpi-modern bg-gradient-sky">
                <div class="ifk-kpi-top">
                  <div>
                    <div class="ifk-kpi-label">Ứng viên</div>
                    <div class="ifk-kpi-value" data-count="<?= (int)($kpi['total_apps'] ?? 0); ?>">0</div>
                    <div class="ifk-kpi-sub">Tổng số ứng viên / lượt apply</div>
                  </div>
                  <div class="ifk-kpi-icon"><i class="fa fa-users"></i></div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="ifk-kpi-modern bg-gradient-green">
                <div class="ifk-kpi-top">
                  <div>
                    <div class="ifk-kpi-label">Đạt PV</div>
                    <div class="ifk-kpi-value" data-count="<?= (int)($kpi['passed'] ?? 0); ?>">0</div>
                    <div class="ifk-kpi-sub">interview_result = pass</div>
                  </div>
                  <div class="ifk-kpi-icon"><i class="fa fa-check-circle"></i></div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="ifk-kpi-modern bg-gradient-purple">
                <div class="ifk-kpi-top">
                  <div>
                    <div class="ifk-kpi-label">Tỷ lệ đậu</div>
                    <div class="ifk-kpi-value"><span data-count="<?= (float)($kpi['rate'] ?? 0); ?>">0</span><span class="ifk-kpi-suffix">%</span></div>
                    <div class="ifk-kpi-sub">Passed / Total</div>
                  </div>
                  <div class="ifk-kpi-icon"><i class="fa fa-percent"></i></div>
                </div>
              </div>
            </div>
          </div>

          <div class="row ifk-chart-row">
            <div class="col-md-6">
              <div class="ifk-saas-card">
                <div class="ifk-saas-head">
                  <div>
                    <div class="ifk-saas-title"><i class="fa fa-pie-chart"></i> Phân bổ Pipeline</div>
                    <div class="ifk-saas-sub">Theo bộ lọc hiện tại</div>
                  </div>
                </div>
                <div class="ifk-chart-wrap">
                  <canvas id="ifkPipelinePie"></canvas>
                  <div class="ifk-empty">Không có dữ liệu để hiển thị</div>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="ifk-saas-card">
                <div class="ifk-saas-head">
                  <div>
                    <div class="ifk-saas-title"><i class="fa fa-bar-chart"></i> So sánh số lượng</div>
                    <div class="ifk-saas-sub">Theo trạng thái pipeline</div>
                  </div>
                </div>
                <div class="ifk-chart-wrap">
                  <canvas id="ifkPipelineBar"></canvas>
                  <div class="ifk-empty">Không có dữ liệu để hiển thị</div>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12">
              <div class="ifk-saas-card">
                <div class="ifk-saas-head">
                  <div>
                    <div class="ifk-saas-title"><i class="fa fa-line-chart"></i> <?= html_escape($chart_title); ?></div>
                    <div class="ifk-saas-sub"><?= html_escape($chart_hint); ?></div>
                  </div>
                </div>
                <div class="ifk-chart-wrap ifk-chart-wrap--line">
                  <canvas id="ifkMonthlyLine"></canvas>
                  <div class="ifk-empty">Không có dữ liệu để hiển thị</div>
                </div>
              </div>
            </div>
          </div>

          <!-- PIPELINE (HORIZONTAL - TOP) -->
          <div class="row">
            <div class="col-md-12">
              <div class="ifk-box">
                <div class="ifk-box-head">
                  <div class="ifk-box-title">
                    <i class="fa fa-sitemap" style="color:#00a6dc"></i>
                    <span>Pipeline theo trạng thái</span>
                  </div>
                  <div class="ifk-box-sub">Theo bộ lọc hiện tại</div>
                </div>

                <div class="ifk-pipeline-grid">
                  <?php foreach(($pipeline ?? []) as $p): 
                        $k = (string)($p['key'] ?? '');
                        $borderClass = 'ifk-border-blue';
                        if(stripos($k,'pass') !== false) $borderClass='ifk-border-green';
                        if(stripos($k,'cancel') !== false) $borderClass='ifk-border-red';
                        if(stripos($k,'visa') !== false) $borderClass='ifk-border-purple';
                        if(stripos($k,'ticket') !== false) $borderClass='ifk-border-orange';
                        if(stripos($k,'coe') !== false) $borderClass='ifk-border-teal';
                  ?>
                    <div class="ifk-pipeline-card <?= $borderClass ?>" data-key="<?= html_escape($k) ?>">
                      <div class="status-title"><?= html_escape($p['label'] ?? $k) ?></div>
                      <div class="status-key"><?= html_escape($k) ?></div>
                      <div class="status-count" style="color:<?= html_escape(ifk_status_color($k) ?? '#1e88e5') ?>"><?= (int)($p['total'] ?? 0) ?></div>
                    </div>
                  <?php endforeach; ?>
                </div>

              </div>
            </div>
          </div>

          
</div><!-- /wrap -->

      </div>
    </div>
  </div>
</div>


<?php
// ===== chart datasets (based on old logic) =====
$__pipe_labels = [];
$__pipe_values = [];
$__pipe_colors = [];
foreach(($pipeline ?? []) as $__p){
  $k = (string)($__p['key'] ?? '');
  $lab = (string)($__p['label'] ?? $k);
  $val = (int)($__p['total'] ?? 0);
  if($val <= 0) continue;
  $__pipe_labels[] = $lab;
  $__pipe_values[] = $val;
  $__pipe_colors[] = ifk_status_color($k);
}

// Monthly: expect controller passes [['label'=>'2026-01','total'=>...], ...] or ['labels'=>[],'values'=>[]]
$__m_labels = [];
$__m_values = [];
if(!empty($monthly)){
  if(isset($monthly['labels']) && isset($monthly['values'])){
    $__m_labels = array_values($monthly['labels']);
    $__m_values = array_map('intval', $monthly['values']);
  } else {
    foreach($monthly as $__m){
      $lab = (string)($__m['label'] ?? $__m['month'] ?? '');
      $val = (int)($__m['total'] ?? $__m['count'] ?? 0);
      if($lab === '') continue;
      $__m_labels[] = $lab;
      $__m_values[] = $val;
    }
  }
}

// Fallback monthly from $report if controller doesn't provide it
if(empty($__m_labels) && !empty($report)){
  $bucket = [];
  foreach($report as $__r){
    $d = '';
    if($tab === 'job_orders') $d = $__r['interview_date'] ?? $__r['date_interview'] ?? $__r['datecreated'] ?? '';
    elseif($tab === 'progress') $d = $__r['entry_date'] ?? $__r['date_entry'] ?? $__r['datecreated'] ?? '';
    else $d = $__r['apply_date'] ?? $__r['datecreated'] ?? '';
    if(!$d) continue;
    $ts = strtotime($d);
    if(!$ts) continue;
    $key = date('Y-m', $ts);
    $bucket[$key] = ($bucket[$key] ?? 0) + 1;
  }
  ksort($bucket);
  $__m_labels = array_keys($bucket);
  $__m_values = array_values($bucket);
}
?>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  // ===== KPI count animation =====
  const easeOutCubic = t => 1 - Math.pow(1-t, 3);
  document.querySelectorAll('[data-count]').forEach(el=>{
    const raw = el.getAttribute('data-count');
    const target = Number(raw);
    if(!isFinite(target)) return;
    const isFloat = String(raw).includes('.');
    const dur = 700;
    const start = performance.now();
    function tick(now){
      const p = Math.min(1, (now-start)/dur);
      const v = target * easeOutCubic(p);
      el.textContent = isFloat ? v.toFixed(1) : Math.round(v).toString();
      if(p<1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  });

  // ===== chart helpers =====
  const pipeLabels = <?= json_encode($__pipe_labels, JSON_UNESCAPED_UNICODE); ?>;
  const pipeValues = <?= json_encode($__pipe_values); ?>;
  const pipeColors = <?= json_encode($__pipe_colors); ?>;

  const monthLabels = <?= json_encode($__m_labels, JSON_UNESCAPED_UNICODE); ?>;
  const monthValues = <?= json_encode($__m_values); ?>;

  function setEmpty(wrapper, isEmpty){
    if(!wrapper) return;
    wrapper.classList.toggle('is-empty', !!isEmpty);
  }

  // PIE
  const pieWrap = document.getElementById('ifkPipelinePie')?.parentElement;
  if(pipeLabels.length && document.getElementById('ifkPipelinePie')){
    setEmpty(pieWrap, false);
    new Chart(document.getElementById('ifkPipelinePie'), {
      type:'pie',
      data:{labels:pipeLabels, datasets:[{data:pipeValues, backgroundColor:pipeColors, borderWidth:1}]},
      options:{
        responsive:true, maintainAspectRatio:false,
        plugins:{legend:{position:'bottom', labels:{boxWidth:12, boxHeight:12}}}
      }
    });
  } else setEmpty(pieWrap, true);

  // BAR
  const barWrap = document.getElementById('ifkPipelineBar')?.parentElement;
  if(pipeLabels.length && document.getElementById('ifkPipelineBar')){
    setEmpty(barWrap, false);
    new Chart(document.getElementById('ifkPipelineBar'), {
      type:'bar',
      data:{labels:pipeLabels, datasets:[{label:'Số lượng', data:pipeValues, backgroundColor:'#4e73df'}]},
      options:{
        responsive:true, maintainAspectRatio:false,
        scales:{y:{beginAtZero:true, ticks:{precision:0}}},
        plugins:{legend:{display:false}}
      }
    });
  } else setEmpty(barWrap, true);

  // LINE
  const lineWrap = document.getElementById('ifkMonthlyLine')?.parentElement;
  if(monthLabels.length && document.getElementById('ifkMonthlyLine')){
    setEmpty(lineWrap, false);
    new Chart(document.getElementById('ifkMonthlyLine'), {
      type:'line',
      data:{labels:monthLabels, datasets:[{label:'Số lượng', data:monthValues, tension:.35, fill:false, borderWidth:2, pointRadius:2}]},
      options:{
        responsive:true, maintainAspectRatio:false,
        scales:{y:{beginAtZero:true, ticks:{precision:0}}},
        plugins:{legend:{display:false}}
      }
    });
  } else setEmpty(lineWrap, true);
})();
</script>

<?php init_tail(); ?>