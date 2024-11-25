{literal}
<style>
     .card[class*=card-border-shadow-] {
          position: relative;
          border-bottom: none;
          transition: all .2s ease-in-out;
          z-index: 1;
     }

     .card[class*=card-border-shadow-]:hover {
          box-shadow: 0 .25rem .75rem 0 rgba(34, 48, 62, .14);
     }

     .card[class*=card-border-shadow-]:hover::after {
          border-bottom-width: 3px;
     }

     .card[class*=card-border-shadow-]::after {
          content: "";
          position: absolute;
          bottom: 0;
          left: 0;
          width: 100%;
          height: 100%;
          border-bottom-width: 2px;
          border-bottom-style: solid;
          border-radius: .375rem;
          transition: all .2s ease-in-out;
          z-index: -1;
     }

     .card {
          --bs-card-border-width: 0;
          --bs-card-border-color: #e4e6e8;
          background-clip: padding-box;
          box-shadow: 0 .1875rem .5rem 0 rgba(34, 48, 62, .1);
          border: var(--bs-card-border-width) solid var(--bs-card-border-color);
     }

     .card.card-border-shadow-primary::after {
          border-bottom-color: #c3c4ff;
     }

     .card.card-border-shadow-primary:hover::after {
          border-bottom-color: #696cff;
     }

     .card.card-border-shadow-success::after {
          border-bottom-color: #c6f1af;
     }

     .card.card-border-shadow-success:hover::after {
          border-bottom-color: #71dd37;
     }

     .card.card-border-shadow-warning::after {
          border-bottom-color: #fd9;
     }

     .card.card-border-shadow-warning:hover::after {
          border-bottom-color: #ffab00;
     }

     .card.card-border-shadow-danger::after {
          border-bottom-color: #ffb2a5;
     }

     .card.card-border-shadow-danger:hover::after {
          border-bottom-color: #ff3e1d;
     }

     .card.card-border-shadow-info::after {
          border-bottom-color: #9ae7f7;
     }

     .card.card-border-shadow-info:hover::after {
          border-bottom-color: #03c3ec;
     }

     .card.card-border-shadow-dark::after {
          border-bottom-color: #aaabb3;
     }

     .card.card-border-shadow-dark:hover::after {
          border-bottom-color: #2b2c40;
     }

     .bg-label-primary {
          background-color: #e7e7ff !important;
          color: #696cff !important;
     }

     .bg-label-success {
          background-color: #e8fadf !important;
          color: #71dd37 !important;
     }

     .bg-label-warning {
          background-color: #fff2d6 !important;
          color: #ffab00 !important;
     }

     .bg-label-danger {
          background-color: #ffe0db !important;
          color: #ff3e1d !important;
     }

     .bg-label-dark {
          background-color: #dddde0 !important;
          color: #2b2c40 !important;
     }

     .box-icon {
          position: relative;
          width: 2.375rem;
          height: 2.375rem;
          cursor: pointer;
     }

     .text-heading {
          --bs-text-opacity: 1;
          color: #384551 !important;
     }

     .text-muted {
          --bs-text-opacity: 1;
          color: #a7acb2 !important;
     }

     .box-icon .box-icon-initial {
          position: absolute;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          text-transform: uppercase;
          display: flex;
          align-items: center;
          justify-content: center;
          color: #fff;
          background-color: #eeedf0;
          font-size: .9375rem;
     }

     .box-call__direction .card p{
          font-size: 18px;
          line-height: 22px;
          color: var(--text-color);
          font-weight: 600;
     }

</style>
<script>
     $(document).ready(function() {
          $("#month_select").change(function() {
               $("#from_date").val($(this).find("option:selected").data("from-date"));
               $("#to_date").val($(this).find("option:selected").data("to-date"));
               $("#ec_search_form").submit();
          });
     });
</script>
{/literal}

<div class="title-wrap d-flex align-items-center justify-content-between gap-2">
     <h1 class="title">Thống kê cuộc gọi</h1>
	<svg xmlns="http://www.w3.org/2000/svg" id="filter_report" width="32" height="32" fill="currentColor" class="bi bi-filter d-xxl-none d-xl-none d-lg-none d-block hide-landscape" viewBox="0 0 16 16">
		<path d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5"/>
	</svg>
</div>

<div class="box-section mb-3 position-relative">
	<div class="overlay-mobile"></div>

     <form action="index.php" method="post" name="search_form" id="ec_search_form">
          <input type="hidden" name="module" value="Calls"/>
          <input type="hidden" name="action" value="summary"/>
          <input type="hidden" name="from_date" id="from_date" value="{$FROM_DATE}">
          <input type="hidden" name="to_date" id="to_date" value="{$TO_DATE}">

          <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" class="bi bi-dash-lg search_form--dash d-xl-none d-lg-none d-md-none d-block" viewBox="0 0 16 16">
               <path fill-rule="evenodd" d="M2 8a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11A.5.5 0 0 1 2 8"></path>
          </svg>
 
          <div class="d-flex align-items-center gap-2 action--wrap">
               <select class="box-select" id="month_select" name="month_select">{$MONTH_SELECT}</select>
          </div>
     </form>
</div>


<div class="box-call__direction">
     <div class="row">
          <div class="col-lg-3 col-sm-6">
               <div class="card card-border-shadow-primary h-100">
                    <div class="card-body">
                         <div class="d-flex align-items-center mb-2">
                              <div class="box-icon me-3">
                                   <span class="box-icon-initial rounded bg-label-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M16.712 13.288a.999.999 0 0 0-1.414 0l-1.594 1.594c-.739-.22-2.118-.72-2.992-1.594s-1.374-2.253-1.594-2.992l1.594-1.594a.999.999 0 0 0 0-1.414l-4-4a.999.999 0 0 0-1.414 0L2.586 6c-.38.38-.594.902-.586 1.435.023 1.424.4 6.37 4.298 10.268S15.142 21.977 16.566 22h.028c.528 0 1.027-.208 1.405-.586l2.712-2.712a.999.999 0 0 0 0-1.414l-3.999-4zM16.585 20c-1.248-.021-5.518-.356-8.873-3.712C4.346 12.922 4.02 8.637 4 7.414l2.005-2.005 2.586 2.586-1.293 1.293a1 1 0 0 0-.272.912c.024.115.611 2.842 2.271 4.502s4.387 2.247 4.502 2.271a.993.993 0 0 0 .912-.271l1.293-1.293 2.586 2.586L16.585 20z"></path><path d="m16.795 5.791-4.497 4.497 1.414 1.414 4.497-4.497L21.005 10V2.995H14z"></path></svg>
                                   </span>
                              </div>
                              <h4 class="mb-0">{$TOTAL_INBOUND}</h4>
                         </div>
                         <p class="mb-2">Cuộc gọi đi</p>
                         <p class="mb-0">
                              <span class="text-heading fw-medium me-2">+18.2%</span>
                              <span class="text-muted">So với hôm qua</span>
                         </p>
                    </div>
               </div>
          </div>
          <div class="col-lg-3 col-sm-6">
               <div class="card card-border-shadow-success h-100">
                    <div class="card-body">
                         <div class="d-flex align-items-center mb-2">
                              <div class="box-icon me-3">
                                   <span class="box-icon-initial rounded bg-label-success">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M16.712 13.288a.999.999 0 0 0-1.414 0l-1.597 1.596c-.824-.245-2.166-.771-2.99-1.596-.874-.874-1.374-2.253-1.594-2.992l1.594-1.594a.999.999 0 0 0 0-1.414l-4-4a1.03 1.03 0 0 0-1.414 0l-2.709 2.71c-.382.38-.597.904-.588 1.437.022 1.423.396 6.367 4.297 10.268C10.195 21.6 15.142 21.977 16.566 22h.028c.528 0 1.027-.208 1.405-.586l2.712-2.712a.999.999 0 0 0 0-1.414l-3.999-4zM16.585 20c-1.248-.021-5.518-.356-8.874-3.712C4.343 12.92 4.019 8.636 4 7.414l2.004-2.005L8.59 7.995 7.297 9.288c-.238.238-.34.582-.271.912.024.115.611 2.842 2.271 4.502s4.387 2.247 4.502 2.271a.994.994 0 0 0 .912-.271l1.293-1.293 2.586 2.586L16.585 20z"></path><path d="M15.795 6.791 13.005 4v6.995H20l-2.791-2.79 4.503-4.503-1.414-1.414z"></path></svg>
                                   </span>
                              </div>
                              <h4 class="mb-0">{$TOTAL_OUTBOUND}</h4>
                         </div>
                         <p class="mb-2">Cuộc gọi đến</p>
                         <p class="mb-0">
                              <span class="text-heading fw-medium me-2">+18.2%</span>
                              <span class="text-muted">So với hôm qua</span>
                         </p>
                    </div>
               </div>
          </div>
          <div class="col-lg-3 col-sm-6">
               <div class="card card-border-shadow-danger h-100">
                    <div class="card-body">
                         <div class="d-flex align-items-center mb-2">
                              <div class="box-icon me-3">
                                   <span class="box-icon-initial rounded bg-label-danger">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M10.09 12.5a8.92 8.92 0 0 1-1-2.2l1.59-1.59a1 1 0 0 0 0-1.42l-4-4a1 1 0 0 0-1.41 0L2.59 6A2 2 0 0 0 2 7.44 15.44 15.44 0 0 0 5.62 17L2.3 20.29l1.41 1.42 18-18-1.41-1.42zM7 15.55a13.36 13.36 0 0 1-3-8.13l2-2L8.59 8 7.3 9.29a1 1 0 0 0-.27.92 11 11 0 0 0 1.62 3.73zm9.71-2.26a1 1 0 0 0-1.41 0l-1.6 1.6-.34-.12-1.56 1.55a12.06 12.06 0 0 0 2 .66 1 1 0 0 0 .91-.27l1.3-1.3L18.59 18l-2 2A13.61 13.61 0 0 1 10 18.1l-1.43 1.45a15.63 15.63 0 0 0 8 2.45 2 2 0 0 0 1.43-.58l2.71-2.71a1 1 0 0 0 0-1.42z"></path></svg>
                                   </span>
                              </div>
                              <h4 class="mb-0">{$TOTAL_MISSED}</h4>
                         </div>
                         <p class="mb-2">Cuộc gọi nhỡ</p>
                         <p class="mb-0">
                              <span class="text-heading fw-medium me-2">+18.2%</span>
                              <span class="text-muted">So với hôm qua</span>
                         </p>
                    </div>
               </div>
          </div>
          <div class="col-lg-3 col-sm-6">
               <div class="card card-border-shadow-dark h-100">
                    <div class="card-body">
                         <div class="d-flex align-items-center mb-2">
                              <div class="box-icon me-3">
                                   <span class="box-icon-initial rounded bg-label-dark">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-mic" viewBox="0 0 16 16">
                                             <path d="M3.5 6.5A.5.5 0 0 1 4 7v1a4 4 0 0 0 8 0V7a.5.5 0 0 1 1 0v1a5 5 0 0 1-4.5 4.975V15h3a.5.5 0 0 1 0 1h-7a.5.5 0 0 1 0-1h3v-2.025A5 5 0 0 1 3 8V7a.5.5 0 0 1 .5-.5"/>
                                             <path d="M10 8a2 2 0 1 1-4 0V3a2 2 0 1 1 4 0zM8 0a3 3 0 0 0-3 3v5a3 3 0 0 0 6 0V3a3 3 0 0 0-3-3"/>
                                        </svg>
                                   </span>
                              </div>
                              <h4 class="mb-0">{$TOTAL_INTERNAL}</h4>
                         </div>
                         <p class="mb-2">Nội bộ
                         <p class="mb-0">
                              <span class="text-heading fw-medium me-2">+18.2%</span>
                              <span class="text-muted">So với hôm qua</span>
                         </p>
                    </div>
               </div>
          </div>
     </div>
</div>