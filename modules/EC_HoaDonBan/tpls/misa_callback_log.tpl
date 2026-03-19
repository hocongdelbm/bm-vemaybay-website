{literal}
<style>
.misa-log-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}
.misa-log-header h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: #1a3c5e;
}
.misa-badge {
    background: #1a3c5e;
    color: #fff;
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 20px;
    font-weight: 500;
    margin-left: 8px;
}
.misa-filter-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f4f6f9;
    border: 1px solid #dce3ec;
    border-radius: 8px;
    padding: 10px 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.misa-filter-bar label {
    font-size: 13px;
    font-weight: 600;
    color: #555;
}
.misa-filter-bar select {
    font-size: 13px;
    padding: 5px 10px;
    border: 1px solid #c5cdd8;
    border-radius: 5px;
    background: #fff;
    color: #222;
}
.misa-stats { display: flex; gap: 12px; margin-bottom: 16px; }
.stat-num { font-size: 18px; font-weight: 700; }
.misa-tabs {
    display: flex;
    gap: 4px;
    border-bottom: 2px solid #dce3ec;
}
.misa-tab-btn {
    padding: 8px 20px;
    font-size: 13px;
    font-weight: 600;
    border: none;
    background: transparent;
    color: #888;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    border-radius: 5px 5px 0 0;
}
.misa-tab-btn:hover  { color: #1a3c5e; background: #f4f6f9; }
.misa-tab-btn.active { color: #1a3c5e; border-bottom-color: #1a3c5e; background: #fff; }
.tab-count {
    background: #dce3ec;
    color: #555;
    font-size: 11px;
    padding: 1px 7px;
    border-radius: 20px;
    font-weight: 700;
    margin-left: 4px;
}
.misa-tab-btn.active .tab-count       { background: #1a3c5e; color: #fff; }
.misa-tab-btn.error-tab .tab-count    { background: #f5b8b3; color: #c0392b; }
.misa-tab-btn.error-tab.active .tab-count { background: #c0392b; color: #fff; }
.misa-tab-panel {
    display: none;
    background: #fff;
    border: 1px solid #dce3ec;
    border-top: none;
    border-radius: 0 0 8px 8px;
}
.misa-tab-panel.active { display: block; }
.misa-log-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 14px;
    background: #f8f9fb;
    border-bottom: 1px solid #dce3ec;
    flex-wrap: wrap;
    gap: 8px;
}
.misa-search-wrap input {
    font-size: 12px;
    padding: 5px 10px;
    border: 1px solid #c5cdd8;
    border-radius: 5px;
    width: 240px;
}
.misa-btn-sm {
    padding: 4px 12px;
    font-size: 12px;
    border: 1px solid #c5cdd8;
    border-radius: 5px;
    background: #fff;
    color: #444;
    cursor: pointer;
}
.misa-btn-sm:hover { background: #f0f0f0; }
.misa-log-body {
    max-height: 100vh;
    overflow-y: auto;
}
.misa-log-line {
    display: flex;
    padding: 3px 14px;
    border-bottom: 1px solid #f2f4f7;
    border-left: 3px solid transparent;
}
.misa-log-line:hover         { background: #f5f8fc; }
.misa-log-line.line-error    { background: #fff8f8; border-left-color: #e74c3c; }
.misa-log-line.line-error:hover { background: #fef0f0; }
.misa-log-line.hidden        { display: none !important; }
.log-num   { color: #bbb; min-width: 40px; user-select: none; font-size: 11px; }
.log-ts    { color: #888; min-width: 155px; flex-shrink: 0; }
.log-level { min-width: 60px; font-weight: 700; flex-shrink: 0; }
.log-level.INFO  { color: #27ae60; }
.log-level.ERROR { color: #e74c3c; }
.log-content { color: #333; word-break: break-all; flex: 1; }
.misa-filepath {
    font-size: 11px;
    color: #999;
    font-family: monospace;
    padding: 4px 14px 8px;
    border-top: 1px solid #f0f0f0;
}
</style>
{/literal}

<div class="misa-log-wrap">

    <div class="misa-log-header">
        <h2>
            MISA Callback Log
            <span class="misa-badge">AMIS Ke toan</span>
        </h2>
    </div>

    <form method="GET" action="" class="misa-filter-bar">
        {foreach from=$smarty.get key=k item=v}
            {if $k != 'year' && $k != 'month'}
                <input type="hidden" name="{$k|escape}" value="{$v|escape}">
            {/if}
        {/foreach}

        <label>Năm:</label>
        <select name="year" class="form-select w-fit-content" onchange="this.form.submit()">
            {foreach from=$availableYears item=y}
                <option value="{$y}" {if $y == $selectedYear}selected{/if}>{$y}</option>
            {/foreach}
        </select>

        <label>Tháng:</label>
        <select name="month" class="form-select w-fit-content">
            {foreach from=$months key=num item=label}
                {assign var="hasData" value=false}
                {foreach from=$availableMonths item=am}
                    {if $am == $num}{assign var="hasData" value=true}{/if}
                {/foreach}
                <option value="{$num}" {if $num == $selectedMonth}selected{/if} {if not $hasData}style="color:#bbb"{/if}>{$label} {if not $hasData} (trống){/if}</option>
            {/foreach}
        </select>

        <button type="submit" class="btn btn-primary">Xem log</button>
    </form>

    <div class="misa-tabs">
        <button class="misa-tab-btn active" onclick="switchTab('info', this)">
            Info log <span class="tab-count">{$logLines|@count}</span>
        </button>
        <button class="misa-tab-btn error-tab" onclick="switchTab('error', this)">
            Error log <span class="tab-count">{$errorLines|@count}</span>
        </button>
        <button class="misa-tab-btn request-tab" onclick="switchTab('request', this)">
            Request log <span class="tab-count">{$requestLines|@count}</span>
        </button>
    </div>

    <div id="tab-info" class="misa-tab-panel active">
        <div class="misa-log-toolbar">
            <div class="misa-search-wrap">
                <input type="text" id="searchInfo" placeholder="Tìm kiếm trong log..." oninput="filterLog('info')">
            </div>
            <div class="misa-toolbar-right">
                <button class="misa-btn-sm" onclick="clearSearch('info')">Xóa filter</button>
                <button class="misa-btn-sm" onclick="scrollBot('log-body-info')">Cuối trang</button>
            </div>
        </div>
        <div class="misa-log-body" id="log-body-info">
            {if $hasLog}
                {foreach from=$logLines item=line name=lp}
                    <div class="misa-log-line line-info log-row-info">
                        <span class="log-num">{$smarty.foreach.lp.iteration}</span>
                        <span class="log-ts">{$line|substr:0:19}</span>
                        <span class="log-level INFO">[INFO]</span>
                        <span class="log-content">{$line|escape}</span>
                    </div>
                {/foreach}
            {else}
                <div class="misa-empty text-dark text-center p-3">
                    <p>Không có dữ liệu log cho <strong>{$months[$selectedMonth]} {$selectedYear}</strong></p>
                </div>
            {/if}
        </div>
        <div class="misa-filepath">{$logFile}</div>
    </div>

    <div id="tab-error" class="misa-tab-panel">
        <div class="misa-log-toolbar">
            <div class="misa-search-wrap">
                <input type="text" id="searchError" placeholder="Tìm kiếm trong error log..." oninput="filterLog('error')">
            </div>
            <div class="misa-toolbar-right">
                <button class="misa-btn-sm" onclick="clearSearch('error')">Xóa filter</button>
                <button class="misa-btn-sm" onclick="scrollBot('log-body-error')">Cuối trang</button>
            </div>
        </div>
        <div class="misa-log-body" id="log-body-error">
            {if $hasError}
                {foreach from=$errorLines item=line name=lp}
                    <div class="misa-log-line line-error log-row-error">
                        <span class="log-num">{$smarty.foreach.lp.iteration}</span>
                        <span class="log-ts">{$line|substr:0:19}</span>
                        <span class="log-level ERROR">[ERROR]</span>
                        <span class="log-content">{$line|escape}</span>
                    </div>
                {/foreach}
            {else}
                <div class="misa-empty text-dark text-center p-3">
                    <p>Không có lỗi nào trong <strong>{$months[$selectedMonth]} {$selectedYear}</strong></p>
                </div>
            {/if}
        </div>
        <div class="misa-filepath">{$errorFile}</div>
    </div>

    <div id="tab-request" class="misa-tab-panel">
        <div class="misa-log-toolbar">
            <div class="misa-search-wrap">
                <input type="text" id="searchRequest" placeholder="Tìm kiếm trong request log..." oninput="filterLog('request')">
            </div>
            <div class="misa-toolbar-right">
                <button class="misa-btn-sm" onclick="clearSearch('request')">Xóa filter</button>
                <button class="misa-btn-sm" onclick="scrollBot('log-body-request')">Cuối trang</button>
            </div>
        </div>
        <div class="misa-log-body" id="log-body-request">
            {if $hasRequest}
                {foreach from=$requestLines item=line name=lp}
                    <div class="misa-log-line line-request log-row-request">
                        <span class="log-num">{$smarty.foreach.lp.iteration}</span>
                        <span class="log-ts d-none">{$line|substr:0:19}</span>
                        <span class="log-content">{$line|escape}</span>
                    </div>
                {/foreach}
            {else}
                <div class="misa-empty text-dark text-center p-3">
                    <p>Không có lỗi nào trong <strong>{$months[$selectedMonth]} {$selectedYear}</strong></p>
                </div>
            {/if}
        </div>
        <div class="misa-filepath">{$requestFile}</div>
    </div>
</div>

{literal}
<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.misa-tab-panel').forEach(function(p) {
        p.classList.remove('active');
    });
    document.querySelectorAll('.misa-tab-btn').forEach(function(b) {
        b.classList.remove('active');
    });
    document.getElementById('tab-' + tab).classList.add('active');
    btn.classList.add('active');
}

function filterLog(type) {
    let id = type === 'info' ? 'searchInfo' : 'searchError';
    let kw = document.getElementById(id).value.toLowerCase();
    document.querySelectorAll('.log-row-' + type).forEach(function(row) {
        let match = kw === '' || row.textContent.toLowerCase().indexOf(kw) !== -1;
        row.classList.toggle('hidden', !match);
    });
}

function clearSearch(type) {
    let id = type === 'info' ? 'searchInfo' : 'searchError';
    document.getElementById(id).value = '';
    filterLog(type);
}

function scrollBot(id) {
    let el = document.getElementById(id);
    if (el) el.scrollTop = el.scrollHeight;
}
</script>
{/literal}
