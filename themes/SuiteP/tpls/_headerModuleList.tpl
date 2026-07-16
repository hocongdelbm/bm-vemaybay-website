{literal}

<script>
     $(document).ready(function() {
          const currentURL = window.location.href;
          const currentURLQuery = window.location.search;
          const AGENT_STATUS = $('#agent_status').val();

          const themeToggleItems = document.querySelectorAll("[data-bs-theme-value]");
          const htmlElement = document.documentElement; 
          const themeIcon = document.querySelector(".theme-icon-active");

          // SVG icons
          const sunIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" data-icon="sun" class="m-0 bi bi-brightness-high" viewBox="0 0 16 16"><path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6m0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/></svg>`;
          const moonIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" data-icon="moon" class="m-0 bi bi-moon" viewBox="0 0 16 16"><path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278M4.858 1.311A7.27 7.27 0 0 0 1.025 7.71c0 4.02 3.279 7.276 7.319 7.276a7.32 7.32 0 0 0 5.205-2.162q-.506.063-1.029.063c-4.61 0-8.343-3.714-8.343-8.29 0-1.167.242-2.278.681-3.286"/></svg>`;
                         
          // Kiểm tra trạng thái đã lưu trong localStorage
          const savedTheme = localStorage.getItem("theme") || "light";
          htmlElement.setAttribute("data-bs-theme", savedTheme);

          if(themeIcon){
               themeIcon.innerHTML = savedTheme === "light" ? sunIcon : moonIcon;
          }

          if(themeToggleItems){
               themeToggleItems.forEach(item => {
                    item.classList.toggle("active", item.getAttribute("data-bs-theme-value") === savedTheme);
               });

               themeToggleItems.forEach(item => {
                    item.addEventListener("click", function () {
                         console.warn('click');
                         const selectedTheme = this.getAttribute("data-bs-theme-value");
                         htmlElement.setAttribute("data-bs-theme", selectedTheme);
                         localStorage.setItem("theme", selectedTheme);
     
                         themeIcon.innerHTML = selectedTheme === "light" ? sunIcon : moonIcon;
     
                         // Cập nhật class active
                         themeToggleItems.forEach(el => el.classList.remove("active"));
                         this.classList.add("active");
                    });
               });
          }
     });

     function setCookie(name, value, seconds = 0) {
               var expires = "";
               if (seconds > 0) {
                    var date = new Date();
                    date.setTime(date.getTime() + seconds * 1000);
                    expires = "; expires=" + date.toUTCString();
                    document.cookie = name + "_exp=" + date.toUTCString() + expires + "; path=/";
               } else {
                    var change_stt_exp = getCookie(name + "_exp");
                    expires = "; expires=" + change_stt_exp;
               }
               document.cookie = name + "=" + (value || "") + expires + "; path=/";
     }

     function getCookie(name) {
          var nameEQ = name + "=";
          var ca = document.cookie.split(';');
          for (var i = 0; i < ca.length; i++) {
               var c = ca[i];
               while (c.charAt(0) == ' ') c = c.substring(1, c.length);
               if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
          }
          return 0;
     }

</script>
{/literal}

<!-- ======= Header ======= -->
<div class="container-fluid">
     <div class="row">
          <div class="col-md-12">
               <nav class="header-navbar layout-navbar navbar default-layout-navbar fixed-top py-0 px-3 d-flex">
                    <div class="d-flex header-navbar__top w-100 align-items-center">
                         <!-- LOGO -->
                         <div class="navbar-brand-wrapper d-flex align-items-center">
                              <a class="brand-logo" href="index.php?module=Home&action=index">
                                   <img src="themes/SuiteP/images/home/company_logo.png" alt="">
                              </a>
                         </div>
                    
                         <!-- SIDEBAR HORIZONTAL -->
                         {if $THEME_CONFIG.display_sidebar}
                         <aside id="sidebar-horizontal" class="sidebar sidebar-horizontal d-flex align-items-center">
                              <ul class="list-menu-horizontal sidebar-nav d-flex align-items-center gap-2" id="sidebar-nav">
                                   <!-- Action Side Bar -->
                                   {if $USE_GROUP_TABS}
                                   {assign var="groupSelected" value=false}
                                   {foreach from=$groupTabs item=modules key=group name=groupList}

                                   {capture name=extraparams assign=extraparams}parentTab={$group}{/capture}
                                        {if not $smarty.foreach.groupList.last}
                                        <li class="nav-item">
                                             <a href="#" class="nav-link collapsed">
                                                  {if $group == 'Hỗ trợ' || $group == 'Support'}
                                                       <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-question-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286zm1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94z"/></svg>
                                                  {elseif $group == 'Bán hàng' || $group == 'Sales'}
                                                       <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-basket3 menu-icon" viewBox="0 0 16 16"><path d="M5.757 1.071a.5.5 0 0 1 .172.686L3.383 6h9.234L10.07 1.757a.5.5 0 1 1 .858-.514L13.783 6H15.5a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H.5a.5.5 0 0 1-.5-.5v-1A.5.5 0 0 1 .5 6h1.717L5.07 1.243a.5.5 0 0 1 .686-.172zM3.394 15l-1.48-6h-.97l1.525 6.426a.75.75 0 0 0 .729.574h9.606a.75.75 0 0 0 .73-.574L15.056 9h-.972l-1.479 6h-9.21z"/></svg>
                                                  {elseif $group == 'Quản lý' || $group == 'Marketing'}
                                                       <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-graph-up-arrow" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M0 0h1v15h15v1H0V0Zm10 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4.9l-3.613 4.417a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61L13.445 4H10.5a.5.5 0 0 1-.5-.5Z"/></svg>
                                                  {elseif $group == 'Hoạt động' || $group == 'Activities' || $group == 'Activity'}
                                                       <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-activity" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M6 2a.5.5 0 0 1 .47.33L10 12.036l1.53-4.208A.5.5 0 0 1 12 7.5h3.5a.5.5 0 0 1 0 1h-3.15l-1.88 5.17a.5.5 0 0 1-.94 0L6 3.964 4.47 8.171A.5.5 0 0 1 4 8.5H.5a.5.5 0 0 1 0-1h3.15l1.88-5.17A.5.5 0 0 1 6 2Z"/></svg>
                                                  {elseif $group == 'Kết hợp' || $group == 'Collaboration'}
                                                       <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-layers" viewBox="0 0 16 16">
                                                       <path d="M8.235 1.559a.5.5 0 0 0-.47 0l-7.5 4a.5.5 0 0 0 0 .882L3.188 8 .264 9.559a.5.5 0 0 0 0 .882l7.5 4a.5.5 0 0 0 .47 0l7.5-4a.5.5 0 0 0 0-.882L12.813 8l2.922-1.559a.5.5 0 0 0 0-.882l-7.5-4zm3.515 7.008L14.438 10 8 13.433 1.562 10 4.25 8.567l3.515 1.874a.5.5 0 0 0 .47 0l3.515-1.874zM8 9.433 1.562 6 8 2.567 14.438 6 8 9.433z"/>
                                                       </svg>
                                                  {elseif $group == 'Kế toán' || $group == 'Accounting'}
                                                       <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-currency-exchange" viewBox="0 0 16 16">
                                                            <path d="M0 5a5.002 5.002 0 0 0 4.027 4.905 6.46 6.46 0 0 1 .544-2.073C3.695 7.536 3.132 6.864 3 5.91h-.5v-.426h.466V5.05c0-.046 0-.093.004-.135H2.5v-.427h.511C3.236 3.24 4.213 2.5 5.681 2.5c.316 0 .59.031.819.085v.733a3.46 3.46 0 0 0-.815-.082c-.919 0-1.538.466-1.734 1.252h1.917v.427h-1.98c-.003.046-.003.097-.003.147v.422h1.983v.427H3.93c.118.602.468 1.03 1.005 1.229a6.5 6.5 0 0 1 4.97-3.113A5.002 5.002 0 0 0 0 5zm16 5.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0zm-7.75 1.322c.069.835.746 1.485 1.964 1.562V14h.54v-.62c1.259-.086 1.996-.74 1.996-1.69 0-.865-.563-1.31-1.57-1.54l-.426-.1V8.374c.54.06.884.347.966.745h.948c-.07-.804-.779-1.433-1.914-1.502V7h-.54v.629c-1.076.103-1.808.732-1.808 1.622 0 .787.544 1.288 1.45 1.493l.358.085v1.78c-.554-.08-.92-.376-1.003-.787H8.25zm1.96-1.895c-.532-.12-.82-.364-.82-.732 0-.41.311-.719.824-.809v1.54h-.005zm.622 1.044c.645.145.943.38.943.796 0 .474-.37.8-1.02.86v-1.674l.077.018z"/>
                                                       </svg>
                                                  {elseif $group == 'HCNS' || $group == 'Human Resources'}
                                                       <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16">
                                                            <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8Zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022ZM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816ZM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/>
                                                       </svg>
                                                  {elseif $group == 'Liên hệ' || $group == 'Contacts'}
                                                       <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-journal-text" viewBox="0 0 16 16">
                                                       <path d="M5 10.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5zm0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
                                                       <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2z"/>
                                                       <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1z"/>
                                                       </svg>
                                                  {elseif $group == 'Khách hàng' || $group == 'Customer'}
                                                       <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16">
                                                            <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8Zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022ZM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816ZM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/>
                                                       </svg>
                                                  {elseif $group == 'Trang chủ' || $group == 'Home'}
                                                       <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house" viewBox="0 0 16 16">
                                                            <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5ZM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5 5 5Z"/>
                                                       </svg>
                                                  {elseif $group == 'Booking'}
                                                       <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-airplane" viewBox="0 0 16 16">
                                                            <path d="M6.428 1.151C6.708.591 7.213 0 8 0s1.292.592 1.572 1.151C9.861 1.73 10 2.431 10 3v3.691l5.17 2.585a1.5 1.5 0 0 1 .83 1.342V12a.5.5 0 0 1-.582.493l-5.507-.918-.375 2.253 1.318 1.318A.5.5 0 0 1 10.5 16h-5a.5.5 0 0 1-.354-.854l1.319-1.318-.376-2.253-5.507.918A.5.5 0 0 1 0 12v-1.382a1.5 1.5 0 0 1 .83-1.342L6 6.691V3c0-.568.14-1.271.428-1.849Zm.894.448C7.111 2.02 7 2.569 7 3v4a.5.5 0 0 1-.276.447l-5.448 2.724a.5.5 0 0 0-.276.447v.792l5.418-.903a.5.5 0 0 1 .575.41l.5 3a.5.5 0 0 1-.14.437L6.708 15h2.586l-.647-.646a.5.5 0 0 1-.14-.436l.5-3a.5.5 0 0 1 .576-.411L15 11.41v-.792a.5.5 0 0 0-.276-.447L9.276 7.447A.5.5 0 0 1 9 7V3c0-.432-.11-.979-.322-1.401C8.458 1.159 8.213 1 8 1c-.213 0-.458.158-.678.599Z"/>
                                                       </svg>
                                                  {elseif $group == 'Tất cả' || $group == 'All'}
                                                       <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-border-all" viewBox="0 0 16 16">
                                                            <path d="M0 0h16v16H0V0zm1 1v6.5h6.5V1H1zm7.5 0v6.5H15V1H8.5zM15 8.5H8.5V15H15V8.5zM7.5 15V8.5H1V15h6.5z"/>
                                                       </svg>
                                                  {elseif $group == 'Khác' || $group == 'Other'}
                                                       <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
                                                            <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/>
                                                       </svg>
                                                  
                                                  {/if}
                                                  <span class="group_name menu-title me-2">{$group}</span>
                                                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-down menu-arrow me-0" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/></svg>
                                             </a>
                                             <ul class="sidebar-nav sub-menu nav-content collapse box-list"> 
                                             
                                             {foreach from=$modules.modules item=module key=modulekey} 
                                                  {* if $modulekey != $smarty.request.module *} 
                                                  <li class="box-item">
                                                       {capture name=moduleTabId assign=moduleTabId}moduleTab_{$smarty.foreach.moduleList.index}_{$module}{/capture}
                                                       <a href="{sugar_link id=$moduleTabId module=$modulekey link_only=1 data=$module extraparams=$extraparams}">
                                                            {$module}
                                                       </a>
                                                  </li>
                                             {/foreach}

                                             {foreach from=$modules.extra item=submodulename key=submodule}
                                                  <li class="box-item box-item__extra">
                                                       <a href="{sugar_link module=$submodule link_only=1 extraparams=$extraparams}">
                                                            {$submodulename}
                                                       </a>
                                                  </li>
                                             {/foreach}
                                             </ul>
                                        </li>
                                        {/if}
                                   {/foreach}
                                   {else}
                                        <li class="navbar-brand-container nav-item">
                                             <a class="navbar-brand with-home-icon nav-link" href="index.php?module=Home&action=index">
                                                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house" viewBox="0 0 16 16">
                                                       <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5ZM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5 5 5Z"/>
                                                  </svg>
                                             </a>
                                        </li>
                                   {/if}
                              </ul> 
                         </aside>
                         {/if}
                         <!-- END SIDEBAR HORIZONTAL -->
                    
                         <div class="navbar-menu-wrapper d-flex align-items-stretch">                    
                              <ul class="navbar-nav navbar-nav-right">
                                   <!-- ONLINE STATUS -->
                                   {if $AUTHENTICATED}
                                   <li id="change_user_status" class="agent-status" style="display:none;">
                                        <button type="button" class="agent-status__pill" id="agent_status_pill">
                                             <span class="agent-status__dot" id="agent_status_dot"></span>
                                             <span class="agent-status__text" id="agent_status_text">Online</span>
                                             <svg class="agent-status__caret" xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/></svg>
                                        </button>
                                        <ul class="agent-status__menu" id="agent_status_menu">
                                             <li class="agent-status__option" data-status="Available">
                                                  <span class="agent-status__dot agent-status__dot--online"></span>
                                                  <span class="agent-status__label">Online</span>
                                                  <svg class="agent-status__tick" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M13.485 1.929a.75.75 0 0 1 .086 1.056l-7 8.5a.75.75 0 0 1-1.11.05l-3.5-3.5a.75.75 0 1 1 1.06-1.06l2.93 2.93 6.478-7.87a.75.75 0 0 1 1.056-.086z"/></svg>
                                             </li>
                                             <li class="agent-status__option" data-status="On Break">
                                                  <span class="agent-status__dot agent-status__dot--busy"></span>
                                                  <span class="agent-status__label">Busy</span>
                                                  <svg class="agent-status__tick" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M13.485 1.929a.75.75 0 0 1 .086 1.056l-7 8.5a.75.75 0 0 1-1.11.05l-3.5-3.5a.75.75 0 1 1 1.06-1.06l2.93 2.93 6.478-7.87a.75.75 0 0 1 1.056-.086z"/></svg>
                                             </li>
                                             <li class="agent-status__option" data-status="Logged Out">
                                                  <span class="agent-status__dot agent-status__dot--offline"></span>
                                                  <span class="agent-status__label">Offline</span>
                                                  <svg class="agent-status__tick" xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M13.485 1.929a.75.75 0 0 1 .086 1.056l-7 8.5a.75.75 0 0 1-1.11.05l-3.5-3.5a.75.75 0 1 1 1.06-1.06l2.93 2.93 6.478-7.87a.75.75 0 0 1 1.056-.086z"/></svg>
                                             </li>
                                        </ul>
                                   </li>

                                   <li class="call-phone__wrap">
                                        <a class="call-phone__circle-img" id="call-phone__circle"></a>
                                        <div class="call-label">
                                             <span class="text-black" id="agent-number"></span>
                                        </div>
                                        <div class="call-phone__numpad" style="display: none;">
                                             <div class="numpad">
                                                  <div class="d-flex flex-column gap-3">
                                                       <h2 class="m-0 fs-5 text-primary fw-bold">BM Voices</h2>
                                                       <div class="flex-start gap-3">
                                                           <label for="select-phone-outbound" class="label text-nowrap">Số gọi ra:</label>
                                                           <select name="select-phone-outbound" id="select-phone-outbound" class="box-select w-100">
                                                                <option value=""></option>
                                                                {if isset($list_phone_choose_outbound) && $list_phone_choose_outbound|@count > 0}
                                                                      {foreach from=$list_phone_choose_outbound key=network_provider item=phones}
                                                                           <optgroup label="{$network_provider|capitalize}">
                                                                                {foreach from=$phones item=phone}
                                                                                     {if $phone.proxy|default:''|trim != ''}
                                                                                          <option value="{$phone.name|trim}@{$phone.proxy|trim}">
                                                                                               {$phone.name|trim} {if $phone.brand_name|trim != ''} ({$phone.brand_name|lower|capitalize}){/if}
                                                                                          </option>
                                                                                     {/if}
                                                                                {/foreach}
                                                                           </optgroup>
                                                                      {/foreach}
                                                                 {/if}
                                                            </select>
                                                       </div>
                                                       <div class="flex-start gap-3">
                                                           <label for="select-phone-support" class="label text-nowrap">Hotline hãng:</label>
                                                           <select name="select-phone-support" id="select-phone-support" class="box-select w-100">
                                                                <option value=""></option>
                                                                {if isset($list_phone_airline_support) && $list_phone_airline_support|@count > 0}
                                                                      {foreach from=$list_phone_airline_support key=phone item=label}
                                                                           <option value="{$phone|trim}">
                                                                                {$phone|trim} - {$label|trim}
                                                                           </option>
                                                                      {/foreach}
                                                                 {/if}
                                                            </select>
                                                       </div>
                                                       <div class="form-control inputBoxes flex-between">
                                                           <label for="call_voiceip_main_number" class="icon-country">
                                                               <svg width="25" height="25" viewBox="0 0 36 36" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" preserveAspectRatio="xMidYMid meet"><g stroke-width="0"></g><g stroke-linecap="round" stroke-linejoin="round"></g><g><path fill="#DA251D" d="M32 5H4a4 4 0 0 0-4 4v18a4 4 0 0 0 4 4h28a4 4 0 0 0 4-4V9a4 4 0 0 0-4-4z"></path><path fill="#FF0" d="M19.753 16.037L18 10.642l-1.753 5.395h-5.672l4.589 3.333l-1.753 5.395L18 21.431l4.589 3.334l-1.753-5.395l4.589-3.333z"></path></g></svg>
                                                           </label>
                                                           <input id="call_voiceip_main_number" type="text" oninput="addNumber_oninput(this)" name="call_voiceip_main_number" placeholder="Nhập số điện thoại" />
                                                       </div>
                                                       <div class="numbers">
                                                           <button class="number" type="button" name="1" onclick="addNumber(this)">
                                                               <span class="pin_font">1</span>
                                                               <span class="pin_text"></span>
                                                           </button>
                                                           <button class="number" type="button" name="2" onclick="addNumber(this)">
                                                               <span class="pin_font">2</span>
                                                               <span class="pin_text">ABC</span>
                                                           </button>
                                                           <button class="number" type="button" name="3" onclick="addNumber(this)">
                                                               <span class="pin_font">3</span>
                                                               <span class="pin_text">DEF</span>
                                                           </button>
                                                           <button class="number" type="button" name="4" onclick="addNumber(this)">
                                                               <span class="pin_font">4</span>
                                                               <span class="pin_text">GHI</span>
                                                           </button>
                                                           <button class="number" type="button" name="5" onclick="addNumber(this)">
                                                               <span class="pin_font">5</span>
                                                               <span class="pin_text">JKL</span>
                                                           </button>
                                                           <button class="number" type="button" name="6" onclick="addNumber(this)">
                                                               <span class="pin_font">6</span>
                                                               <span class="pin_text">MNO</span>
                                                           </button>
                                                           <button class="number" type="button" name="7" onclick="addNumber(this)">
                                                               <span class="pin_font">7</span>
                                                               <span class="pin_text">PQRS</span>
                                                           </button>
                                                           <button class="number" type="button" name="8" onclick="addNumber(this)">
                                                               <span class="pin_font">8</span>
                                                               <span class="pin_text">TUV</span>
                                                           </button>
                                                           <button class="number" type="button" name="9" onclick="addNumber(this)">
                                                               <span class="pin_font">9</span>
                                                               <span class="pin_text">WXYZ</span>
                                                           </button>
                                                           <button class="number" type="button" name="del" onclick="deleteAll()">
                                                               <span class="pin_font">AC</span>
                                                           </button>
                                                           <button class="number" type="button" name="0" onclick="addNumber(this)">
                                                               <span class="pin_font">0</span>
                                                               <span class="pin_text">
                                                                   <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                                                                       <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"></path>
                                                                   </svg>
                                                               </span>
                                                           </button>
                                                           <button class="number" type="button" name="clear" onclick="removeNumber(this)">
                                                               <span class="pin_font">
                                                                   <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="currentColor" class="bi bi-backspace" viewBox="0 0 16 16">
                                                                       <path d="M5.83 5.146a.5.5 0 0 0 0 .708L7.975 8l-2.147 2.146a.5.5 0 0 0 .707.708l2.147-2.147 2.146 2.147a.5.5 0 0 0 .707-.708L9.39 8l2.146-2.146a.5.5 0 0 0-.707-.708L8.683 7.293 6.536 5.146a.5.5 0 0 0-.707 0z"></path>
                                                                       <path d="M13.683 1a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-7.08a2 2 0 0 1-1.519-.698L.241 8.65a1 1 0 0 1 0-1.302L5.084 1.7A2 2 0 0 1 6.603 1zm-7.08 1a1 1 0 0 0-.76.35L1 8l4.844 5.65a1 1 0 0 0 .759.35h7.08a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"></path>
                                                                   </svg>
                                                               </span>
                                                           </button>
                                                       </div>
                                                       <div class="call-button--wrap flex-between">
                                                            <button class="btn btn-calling flex-fill btn-voiceip-calling btn-voiceip-calling-zalo" type="button" id="btn-voiceip-main-zalo">
                                                                 Zalo
                                                            </button>
                                                           <button class="btn-calling flex-fill btn-voiceip-calling btn-voiceip-calling-teco" type="button" id="btn-voiceip-main-calling">
                                                               Gọi
                                                           </button>
                                                       </div>
                                                   </div>
                                             </div>
                                        </div>
                                   </li>
                                   {/if}

                                   <!-- DARK MODE -->
                                   {if $CURRENT_USER == 'hungnh'}
                                   <li class="item-theme-mode nav-item dropdown me-2 me-xl-0">
                                        <a class="nav-link dropdown-toggle hide-arrow theme-icon-active" id="nav-theme" href="javascript:void(0);" data-bs-toggle="dropdown">
                                          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-brightness-high" viewBox="0 0 16 16">
                                             <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6m0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/>
                                           </svg>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end box-list min-w-150 position-absolute" aria-labelledby="nav-theme-text">
                                          <li class="box-item mb-2">
                                            <a type="button" class="rounded flex-start active" data-bs-theme-value="light">
                                             <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" data-icon="sun" class="m-0 bi bi-brightness-high" viewBox="0 0 16 16">
                                                  <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6m0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/>
                                             </svg>
                                              <span>Sáng</span>
                                            </a>
                                          </li>
                                          <li class="box-item">
                                            <a type="button" class="rounded flex-start" data-bs-theme-value="dark">
                                             <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" data-icon="moon" class="m-0 bi bi-moon" viewBox="0 0 16 16">
                                                  <path d="M6 .278a.77.77 0 0 1 .08.858 7.2 7.2 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277q.792-.001 1.533-.16a.79.79 0 0 1 .81.316.73.73 0 0 1-.031.893A8.35 8.35 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.75.75 0 0 1 6 .278M4.858 1.311A7.27 7.27 0 0 0 1.025 7.71c0 4.02 3.279 7.276 7.319 7.276a7.32 7.32 0 0 0 5.205-2.162q-.506.063-1.029.063c-4.61 0-8.343-3.714-8.343-8.29 0-1.167.242-2.278.681-3.286"/>
                                             </svg>
                                             <span>Dark</span>
                                            </a>
                                          </li>
                                        </ul>
                                   </li>
                                   {/if}

                                   <!-- QUICK CREATE -->
                                   <li  id="quickcreatetop" class="nav-item navbar-dropdown dropdown-shortcuts dropdown">
                                        <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="true">
                                             <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M10 3H4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1zM9 9H5V5h4v4zm5 2h6a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1h-6a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1zm1-6h4v4h-4V5zM3 20a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v6zm2-5h4v4H5v-4zm8 5a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-6a1 1 0 0 0-1-1h-6a1 1 0 0 0-1 1v6zm2-5h4v4h-4v-4z"></path></svg>
                                         </a>
                                         <div class="dropdown-menu dropdown-menu-end p-0">
                                             <div class="dropdown-menu-header border-bottom">
                                                 <div class="dropdown-header d-flex align-items-center py-2 bg-primary">
                                                     <h5 class="text-white mb-0 me-auto">Tạo nhanh</h5>
                                                 </div>
                                             </div>
                                             <div class="dropdown-shortcuts-list">
                                                  <div class="row row-bordered overflow-visible g-0">
                                                       <div class="dropdown-shortcuts-item col">
                                                            <span class="dropdown-shortcuts-icon bg-label-secondary rounded-circle mb-2">
                                                                 <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-file-earmark-text" viewBox="0 0 16 16">
                                                                      <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/>
                                                                      <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/>
                                                                 </svg>
                                                            </span>
                                                            <a class="stretched-link" href="index.php?module=EC_Receipt_Voucher&action=EditView&return_module=EC_Receipt_Voucher&return_action=DetailView">{$APP.LBL_QUICK_CREATE}{sugar_translate module="EC_Receipt_Voucher" label="LBL_MODULE_NAME"}</a>
                                                       </div>
                              
                                                       <div class="dropdown-shortcuts-item col">
                                                            <span class="dropdown-shortcuts-icon bg-label-secondary rounded-circle mb-2">
                                                                 <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-file-earmark-text" viewBox="0 0 16 16">
                                                                      <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/>
                                                                      <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/>
                                                                 </svg>
                                                            </span>
                                                            <a class="stretched-link" href="index.php?module=EC_Payment_Voucher&action=EditView&return_module=EC_Payment_Voucher&return_action=DetailView">{$APP.LBL_QUICK_CREATE}{sugar_translate module="EC_Payment_Voucher" label="LBL_MODULE_NAME"}</a>
                                                       </div>
                                                  </div>
                                                  <div class="row row-bordered overflow-visible g-0">
                                                       <div class="dropdown-shortcuts-item col">
                                                            <span class="dropdown-shortcuts-icon bg-label-secondary rounded-circle mb-2">
                                                                 <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-file-earmark-text" viewBox="0 0 16 16">
                                                                      <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/>
                                                                      <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/>
                                                                 </svg>
                                                            </span>
                                                            <a class="stretched-link" href="index.php?module=EC_HoanVe&action=EditView&return_module=EC_HoanVe&return_action=DetailView">{$APP.LBL_QUICK_CREATE}{sugar_translate module="EC_HoanVe" label="LBL_MODULE_NAME"}</a>
                                                       </div>
                              
                                                       <div class="dropdown-shortcuts-item col">
                                                            <span class="dropdown-shortcuts-icon bg-label-secondary rounded-circle mb-2">
                                                                 <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-file-earmark-text" viewBox="0 0 16 16">
                                                                      <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/>
                                                                      <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/>
                                                                 </svg>
                                                            </span>
                                                            <a class="stretched-link" href="index.php?module=EC_LeaveAbsences&action=EditView&return_module=EC_LeaveAbsences&return_action=DetailView">{$APP.LBL_QUICK_CREATE}{sugar_translate module="EC_LeaveAbsences" label="LBL_MODULE_NAME"}</a>
                                                       </div>
                                                  </div>
                                                  <div class="row row-bordered overflow-visible g-0">
                                                       <div class="dropdown-shortcuts-item col">
                                                            <span class="dropdown-shortcuts-icon bg-label-secondary rounded-circle mb-2">
                                                                 <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-file-earmark-text" viewBox="0 0 16 16">
                                                                      <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/>
                                                                      <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/>
                                                                 </svg>
                                                            </span>
                                                            <a class="stretched-link" href="index.php?module=EC_ChuyenTienNoiBo&action=EditView&return_module=EC_ChuyenTienNoiBo&return_action=DetailView">{$APP.LBL_QUICK_CREATE}{sugar_translate module="EC_ChuyenTienNoiBo" label="LBL_MODULE_NAME"}</a>
                                                       </div>
                              
                                                       <div class="dropdown-shortcuts-item col">
                                                            <span class="dropdown-shortcuts-icon bg-label-secondary rounded-circle mb-2">
                                                                 <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-file-earmark-text" viewBox="0 0 16 16">
                                                                      <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/>
                                                                      <path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/>
                                                                 </svg>
                                                            </span>
                                                            <a class="stretched-link" href="index.php?module=EC_HoaDonBan&action=EditView&return_module=EC_HoaDonBan&return_action=DetailView">{$APP.LBL_QUICK_CREATE}{sugar_translate module="EC_HoaDonBan" label="LBL_MODULE_NAME"}</a>
                                                       </div>
                                                  </div>
                                             </div>
                                         </div>
                                   </li>
                         
                                   <!-- Notifications -->
                                   <li id="desktop_notifications" class="nav-item dropdown desktop_notifications">
                                        <a class="nav-link count-indicator dropdown-toggle hide-arrow" id="notificationDropdown" href="javascript:void(0)" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="true">
                                             <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: #bba8bff5"><path d="M19 13.586V10c0-3.217-2.185-5.927-5.145-6.742C13.562 2.52 12.846 2 12 2s-1.562.52-1.855 1.258C7.185 4.074 5 6.783 5 10v3.586l-1.707 1.707A.996.996 0 0 0 3 16v2a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1v-2a.996.996 0 0 0-.293-.707L19 13.586zM19 17H5v-.586l1.707-1.707A.996.996 0 0 0 7 14v-4c0-2.757 2.243-5 5-5s5 2.243 5 5v4c0 .266.105.52.293.707L19 16.414V17zm-7 5a2.98 2.98 0 0 0 2.818-2H9.182A2.98 2.98 0 0 0 12 22z"></path></svg>
                                             <span class="alert_count count-symbol"></span>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end navbar-dropdown box-list" aria-labelledby="notificationDropdown">
                                             <div class="d-flex flex-column gap-2">
                                                  <div class="notification-header">
                                                       <div class="flex-between gap-2">
                                                           <h3 class="notification-title">Thông báo</h3>
                                                           <div class="dropdown">
                                                                 <button type="button" class="btn-more-notify btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="true">
                                                                      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
                                                                           <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3"/>
                                                                      </svg>
                                                                 </button>
                                                                 <div class="dropdown-more-notify dropdown-menu dropdown-menu-end">
                                                                      <a class="flex-start gap-1 dropdown-item cursor-pointer clear-all-alerts-btn" href="javascript:void(0);">
                                                                           <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M5 20a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8h2V6h-4V4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v2H3v2h2zM9 4h6v2H9zM8 8h9v12H7V8z"></path><path d="M9 10h2v8H9zm4 0h2v8h-2z"></path></svg>
                                                                           <span class="">{sugar_translate label="LBL_CLEARALL"}</span>
                                                                      </a>
                                                                      <a class="flex-start gap-1 dropdown-item cursor-pointer mark-all-alerts-btn" href="javascript:void(0);">
                                                                           <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="m10 15.586-3.293-3.293-1.414 1.414L10 18.414l9.707-9.707-1.414-1.414z"></path></svg>
                                                                           <span class="">Đánh dấu tất cả đã đọc</span>
                                                                      </a>
                                                                 </div>
                                                            </div>
                                                       </div>
                                                       <div class="flex-between gap-2">
                                                            <div class="flex-start">
                                                                 <div onclick="Alerts.getAlertUnread();" class="notification-show notification-unread active"><span>Chưa đọc</span></div>
                                                                <div onclick="Alerts.getAllAlerts();" class="notification-show notification-all"><span>Tất cả</span></div>
                                                            </div>
                                                            <a href="index.php?module=Alerts&action=Listview&return_module=Alerts&return_action=index" class="notification-view-all"><span>Xem tất cả</span></a>
                                                       </div>
                                                  </div>
                                                  <div class="notification-body mb-2" id="alerts">
                                                       {$APP.LBL_EMAIL_ERROR_VIEW_RAW_SOURCE}
                                                  </div>

                                                  {if $IS_ADMIN}
                                                  <div class="notification-footer">
                                                       <a href="index.php?module=Alerts&action=EditView&return_module=Alerts&return_action=index" class="btn btn-primary w-100 py-2">Tạo thông báo</a>
                                                  </div>
                                                  {/if}
                                             </div>
                                        </div>
                                   </li>
                         
                                   <!-- PROFILE -->
                                   <li id="globalLinks" class="nav-item nav-profile dropdown">
                                        <a class="nav-link dropdown-toggle" id="profileDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                             <div class="nav-profile-img">
                                                  {$CURRENT_USER_PHOTO}
                                                  <div class="nav-profile-text d-flex align-items-center hide-mobile">
                                                       <p class="text-black">{$CURRENT_USER}</p>
                                                  </div>
                                             </div>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-end navbar-dropdown box-list user-dropdown user-menu" aria-labelledby="profileDropdown">
                                        <li class="box-item">
                                             <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                             <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0Zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4Zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10Z"/>
                                             </svg>
                                             <a href='index.php?module=Users&action=EditView&record={$CURRENT_USER_ID}'>
                                             {$APP.LBL_PROFILE}
                                             </a>
                                        </li>
                                        {foreach from=$GCLS item=GCL name=gcl key=gcl_key}
                                             <li class="box-item">
                                                  {if $gcl_key == 'employees'} <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16"><path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8Zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022ZM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816ZM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/></svg>{/if}
                                                  {if $gcl_key == 'admin'} <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16"><path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/><path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z"/></svg>{/if}
                                                  {if $gcl_key == 'training'} <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-question-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286zm1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94z"/></svg>{/if}
                                                  {if $gcl_key == 'about'} <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/></svg>{/if}
                         
                                                  <a id="{$gcl_key}_link" href="{$GCL.URL}" {if !empty($GCL.ONCLICK)} onclick="{$GCL.ONCLICK}" {/if} {if !empty($GCL.TARGET)} target="{$GCL.TARGET}" {/if}>{$GCL.LABEL}</a>
                                             </li>
                                        {/foreach}
                                        <li class="box-item">
                                             <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-right" viewBox="0 0 16 16">
                                             <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/>
                                             <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
                                             </svg>
                                             <a role="menuitem" id="logout_link" href='{$LOGOUT_LINK}'>{$LOGOUT_LABEL}</a>
                                        </li>
                                        </ul>
                                   </li>
                              </ul>
                              
                              <!-- ICON TOGGLER SIDEBAR MOBILE-->
                              <button class="menu-mobile-icon navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
                                   <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z"></path></svg>
                              </button>
                         </div>
                    </div>

                    <div class="d-flex header-navbar__bottom">
                         <!-- RECENT VIEW -->
                         <div class="view-recent__wrap d-flex gap-3 align-items-center">
                              <h3>Xem gần đây: </h3>
                              <ul class="view-recent__list col-lg-12 col-md-12 d-flex gap-2">
                              {foreach from=$recentRecords item=item name=lastViewed}
                                   {if $item.module_name != 'Emails' && $item.module_name != 'InboundEmail' && $item.module_name != 'EmailAddresses'}<!--Check to ensure that recently viewed emails or email addresses are not displayed in the recently viewed panel.-->
                                        {if $smarty.foreach.lastViewed.index < 10}
                                        <li class="view-recent__item">
                                             <a class="d-flex gap-1 align-items-center cursor-pointer" title="{sugar_translate module=$item.module_name label=LBL_MODULE_NAME}" accessKey="{$smarty.foreach.lastViewed.iteration}" href="{sugar_link module=$item.module_name action='DetailView' record=$item.item_id link_only=1}" class="view-recent__link">
                                                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-alarm" viewBox="0 0 16 16">
                                                       <path d="M8.5 5.5a.5.5 0 0 0-1 0v3.362l-1.429 2.38a.5.5 0 1 0 .858.515l1.5-2.5A.5.5 0 0 0 8.5 9V5.5z"/>
                                                       <path d="M6.5 0a.5.5 0 0 0 0 1H7v1.07a7.001 7.001 0 0 0-3.273 12.474l-.602.602a.5.5 0 0 0 .707.708l.746-.746A6.97 6.97 0 0 0 8 16a6.97 6.97 0 0 0 3.422-.892l.746.746a.5.5 0 0 0 .707-.708l-.601-.602A7.001 7.001 0 0 0 9 2.07V1h.5a.5.5 0 0 0 0-1h-3zm1.038 3.018a6.093 6.093 0 0 1 .924 0 6 6 0 1 1-.924 0zM0 3.5c0 .753.333 1.429.86 1.887A8.035 8.035 0 0 1 4.387 1.86 2.5 2.5 0 0 0 0 3.5zM13.5 1c-.753 0-1.429.333-1.887.86a8.035 8.035 0 0 1 3.527 3.527A2.5 2.5 0 0 0 13.5 1z"/>
                                                  </svg>
                                                  <span>
                                                       {$item.item_summary_short}
                                                  </span>
                                             </a>
                                        </li>
                                        {/if}
                                   {/if}
                              {/foreach}
                              </ul>
                         </div>
                    </div>

               </nav>
               <!-- End Header -->
          </div>
     </div>
</div>

{if $THEME_CONFIG.display_sidebar}
<aside id="sidebar" class="sidebar sidebar-current sidebar-offcanvas">
     <div class="sidebar-vertical-wrap position-relative">
          <!-- SIDEBAR HORIZONTAL -->
          {if $THEME_CONFIG.display_sidebar}
          <aside id="sidebar-horizontal-mobile" class="sidebar-mobile sidebar-horizontal-mobile d-lg-none">
               <ul class="list-menu-horizontal-mobile sidebar-nav" id="sidebar-nav">
               
                    <!-- Action Side Bar -->
                    {if $USE_GROUP_TABS}
                    {assign var="groupSelected" value=false}
                    {foreach from=$groupTabs item=modules key=group name=groupList}

                    {capture name=extraparams assign=extraparams}parentTab={$group}{/capture}
                         {if not $smarty.foreach.groupList.last}
                         <li class="nav-item">
                              <a href="#grouptab_{$smarty.foreach.groupList.index}_{$smarty.foreach.groupList.index}" class="nav-link collapsed" data-bs-toggle="collapse" aria-controls="grouptab_{$smarty.foreach.groupList.index}_{$smarty.foreach.groupList.index}">
                                   {if $group == 'Hỗ trợ' || $group == 'Support'}
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-question-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.314.343 1.314 1.168 0 .635-.374.927-.965 1.371-.673.489-1.206 1.06-1.168 1.987l.003.217a.25.25 0 0 0 .25.246h.811a.25.25 0 0 0 .25-.25v-.105c0-.718.273-.927 1.01-1.486.609-.463 1.244-.977 1.244-2.056 0-1.511-1.276-2.241-2.673-2.241-1.267 0-2.655.59-2.75 2.286zm1.557 5.763c0 .533.425.927 1.01.927.609 0 1.028-.394 1.028-.927 0-.552-.42-.94-1.029-.94-.584 0-1.009.388-1.009.94z"/></svg>
                                   {elseif $group == 'Bán hàng' || $group == 'Sales'}
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-basket3 menu-icon" viewBox="0 0 16 16"><path d="M5.757 1.071a.5.5 0 0 1 .172.686L3.383 6h9.234L10.07 1.757a.5.5 0 1 1 .858-.514L13.783 6H15.5a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H.5a.5.5 0 0 1-.5-.5v-1A.5.5 0 0 1 .5 6h1.717L5.07 1.243a.5.5 0 0 1 .686-.172zM3.394 15l-1.48-6h-.97l1.525 6.426a.75.75 0 0 0 .729.574h9.606a.75.75 0 0 0 .73-.574L15.056 9h-.972l-1.479 6h-9.21z"/></svg>
                                   {elseif $group == 'Quản lý' || $group == 'Marketing'}
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-graph-up-arrow" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M0 0h1v15h15v1H0V0Zm10 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4.9l-3.613 4.417a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61L13.445 4H10.5a.5.5 0 0 1-.5-.5Z"/></svg>
                                   {elseif $group == 'Hoạt động' || $group == 'Activities' || $group == 'Activity'}
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-activity" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M6 2a.5.5 0 0 1 .47.33L10 12.036l1.53-4.208A.5.5 0 0 1 12 7.5h3.5a.5.5 0 0 1 0 1h-3.15l-1.88 5.17a.5.5 0 0 1-.94 0L6 3.964 4.47 8.171A.5.5 0 0 1 4 8.5H.5a.5.5 0 0 1 0-1h3.15l1.88-5.17A.5.5 0 0 1 6 2Z"/></svg>
                                   {elseif $group == 'Kết hợp' || $group == 'Collaboration'}
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-layers" viewBox="0 0 16 16">
                                        <path d="M8.235 1.559a.5.5 0 0 0-.47 0l-7.5 4a.5.5 0 0 0 0 .882L3.188 8 .264 9.559a.5.5 0 0 0 0 .882l7.5 4a.5.5 0 0 0 .47 0l7.5-4a.5.5 0 0 0 0-.882L12.813 8l2.922-1.559a.5.5 0 0 0 0-.882l-7.5-4zm3.515 7.008L14.438 10 8 13.433 1.562 10 4.25 8.567l3.515 1.874a.5.5 0 0 0 .47 0l3.515-1.874zM8 9.433 1.562 6 8 2.567 14.438 6 8 9.433z"/>
                                        </svg>
                                   {elseif $group == 'Kế toán' || $group == 'Accounting'}
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-currency-exchange" viewBox="0 0 16 16">
                                             <path d="M0 5a5.002 5.002 0 0 0 4.027 4.905 6.46 6.46 0 0 1 .544-2.073C3.695 7.536 3.132 6.864 3 5.91h-.5v-.426h.466V5.05c0-.046 0-.093.004-.135H2.5v-.427h.511C3.236 3.24 4.213 2.5 5.681 2.5c.316 0 .59.031.819.085v.733a3.46 3.46 0 0 0-.815-.082c-.919 0-1.538.466-1.734 1.252h1.917v.427h-1.98c-.003.046-.003.097-.003.147v.422h1.983v.427H3.93c.118.602.468 1.03 1.005 1.229a6.5 6.5 0 0 1 4.97-3.113A5.002 5.002 0 0 0 0 5zm16 5.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0zm-7.75 1.322c.069.835.746 1.485 1.964 1.562V14h.54v-.62c1.259-.086 1.996-.74 1.996-1.69 0-.865-.563-1.31-1.57-1.54l-.426-.1V8.374c.54.06.884.347.966.745h.948c-.07-.804-.779-1.433-1.914-1.502V7h-.54v.629c-1.076.103-1.808.732-1.808 1.622 0 .787.544 1.288 1.45 1.493l.358.085v1.78c-.554-.08-.92-.376-1.003-.787H8.25zm1.96-1.895c-.532-.12-.82-.364-.82-.732 0-.41.311-.719.824-.809v1.54h-.005zm.622 1.044c.645.145.943.38.943.796 0 .474-.37.8-1.02.86v-1.674l.077.018z"/>
                                        </svg>
                                   {elseif $group == 'HCNS' || $group == 'Human Resources'}
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16">
                                             <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8Zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022ZM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816ZM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/>
                                        </svg>
                                   {elseif $group == 'Liên hệ' || $group == 'Contacts'}
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-journal-text" viewBox="0 0 16 16">
                                        <path d="M5 10.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5zm0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
                                        <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2z"/>
                                        <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1z"/>
                                        </svg>
                                   {elseif $group == 'Khách hàng' || $group == 'Customer'}
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people" viewBox="0 0 16 16">
                                             <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8Zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.274.274 0 0 1-.014.002H7.022ZM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816ZM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/>
                                        </svg>
                                   {elseif $group == 'Trang chủ' || $group == 'Home'}
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house" viewBox="0 0 16 16">
                                             <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5ZM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5 5 5Z"/>
                                        </svg>
                                   {elseif $group == 'Booking'}
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-airplane" viewBox="0 0 16 16">
                                             <path d="M6.428 1.151C6.708.591 7.213 0 8 0s1.292.592 1.572 1.151C9.861 1.73 10 2.431 10 3v3.691l5.17 2.585a1.5 1.5 0 0 1 .83 1.342V12a.5.5 0 0 1-.582.493l-5.507-.918-.375 2.253 1.318 1.318A.5.5 0 0 1 10.5 16h-5a.5.5 0 0 1-.354-.854l1.319-1.318-.376-2.253-5.507.918A.5.5 0 0 1 0 12v-1.382a1.5 1.5 0 0 1 .83-1.342L6 6.691V3c0-.568.14-1.271.428-1.849Zm.894.448C7.111 2.02 7 2.569 7 3v4a.5.5 0 0 1-.276.447l-5.448 2.724a.5.5 0 0 0-.276.447v.792l5.418-.903a.5.5 0 0 1 .575.41l.5 3a.5.5 0 0 1-.14.437L6.708 15h2.586l-.647-.646a.5.5 0 0 1-.14-.436l.5-3a.5.5 0 0 1 .576-.411L15 11.41v-.792a.5.5 0 0 0-.276-.447L9.276 7.447A.5.5 0 0 1 9 7V3c0-.432-.11-.979-.322-1.401C8.458 1.159 8.213 1 8 1c-.213 0-.458.158-.678.599Z"/>
                                        </svg>
                                   {elseif $group == 'Tất cả' || $group == 'All'}
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-border-all" viewBox="0 0 16 16">
                                             <path d="M0 0h16v16H0V0zm1 1v6.5h6.5V1H1zm7.5 0v6.5H15V1H8.5zM15 8.5H8.5V15H15V8.5zM7.5 15V8.5H1V15h6.5z"/>
                                        </svg>
                                   {elseif $group == 'Khác' || $group == 'Other'}
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots" viewBox="0 0 16 16">
                                             <path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/>
                                        </svg>
                                   
                                   {/if}
                                   <span class="group_name menu-title me-2">{$group}</span>
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-down menu-arrow me-0" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/></svg>
                              </a>
                              <ul class="sidebar-nav sub-menu nav-content collapse box-list" id="grouptab_{$smarty.foreach.groupList.index}_{$smarty.foreach.groupList.index}"> 
                              
                              {foreach from=$modules.modules item=module key=modulekey} 
                                   {* if $modulekey != $smarty.request.module *} 
                                   <li class="box-item">
                                   {capture name=moduleTabId assign=moduleTabId}moduleTab_{$smarty.foreach.moduleList.index}_{$module}{/capture}
                                   <a href="{sugar_link id=$moduleTabId module=$modulekey link_only=1 data=$module extraparams=$extraparams}">
                                        {$module}
                                   </a>
                                   </li>
                              {/foreach}

                              {foreach from=$modules.extra item=submodulename key=submodule}
                                   <li class="box-item">
                                        <a href="{sugar_link module=$submodule link_only=1 extraparams=$extraparams}">
                                             {$submodulename}
                                        </a>
                                   </li>
                              {/foreach}
                              </ul>
                         </li>
                         {/if}
                    {/foreach}
                    {else}
                         <li class="navbar-brand-container nav-item">
                              <a class="navbar-brand with-home-icon nav-link" href="index.php?module=Home&action=index">
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house" viewBox="0 0 16 16">
                                        <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5ZM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5 5 5Z"/>
                                   </svg>
                              </a>
                         </li>
                    {/if}
                    <!-- ./end Action Side Bar -->
               </ul> 
          </aside>
          {/if}
          <!-- END SIDEBAR HORIZONTAL -->

          <div class="line-highlight"><span></span></div>

          <ul class="sidebar-nav" id="sidebar-nav">
               <!-- Action Side Bar -->
               {if $USE_GROUP_TABS}
               {assign var="groupSelected" value=false}
               {foreach from=$moduleTopMenu item=module key=name name=moduleList}

               {if $name == $MODULE_TAB}
               <li class="nav-item nav-current-item">
                    <a href="{sugar_link id=" moduleTab_$name" link_only=1 module=$name data=$module class="active_module_link" }" data-module="{$module}" class="nav-link nav-link-current">
                         {if $name == 'EC_Vouchers'}
                         <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-tags" viewBox="0 0 16 16">
                              <path d="M3 2v4.586l7 7L14.586 9l-7-7H3ZM2 2a1 1 0 0 1 1-1h4.586a1 1 0 0 1 .707.293l7 7a1 1 0 0 1 0 1.414l-4.586 4.586a1 1 0 0 1-1.414 0l-7-7A1 1 0 0 1 2 6.586V2Z"/>
                              <path d="M5.5 5a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1Zm0 1a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z"/>
                              <path d="M1 7.086V2a1 1 0 0 1 1-1v5.086a1 1 0 0 0 .293.707l7 7L8.586 14.5l-7-7A1 1 0 0 1 1 7.086Z"/>
                         </svg>
                         {else}
                         <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-grid" viewBox="0 0 16 16">
                              <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zM2.5 2a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zM1 10.5A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3zm6.5.5A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3zm1.5-.5a.5.5 0 0 0-.5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 0-.5-.5h-3z"/>
                         </svg>
                         {/if}
                         <span class="group_name menu-title">{$module}</span>
                    </a>
     
                    {* check, is there any recent items *}
                    {assign var=foundRecents value=false}
                    {foreach from=$recentRecords item=item name=lastViewed}
                    {if $item.module_name == $name}
                         {assign var=foundRecents value=true}
                    {/if}
                    {/foreach}
     
                    {* check, is there any favorite items *}
                    {assign var=foundFavorits value=false}
                    {foreach from=$favoriteRecords item=item name=lastViewed}
                    {if $item.module_name == $name}
                         {assign var=foundFavorits value=true}
                    {/if}
                    {/foreach}
     
                    {if $foundRecents || $foundFavorits || (is_array($shortcutTopMenu.$name) && count($shortcutTopMenu.$name) > 0)}
                    <ul class="sidebar-nav sub-menu nav-content collapse d-block">
                         <li class="nav-item current-module-action-links">
                         <ul class="sidebar-nav sub-menu">
                         {if is_array($shortcutTopMenu.$name) && count($shortcutTopMenu.$name) > 0}
                              {foreach from=$shortcutTopMenu.$name item=item}
                              {if $item.URL == "-"}
                              {*<li><a></a><span>&nbsp;</span></li>*}
                              {else}
                              <li class="nav-item">
                              <a class="nav-link" href="{$item.URL}">
                              {if strstr($item.LABEL|lower, 'create') || strstr($item.LABEL|lower, 'tạo') || strstr($item.LABEL|lower, 'thêm')}
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2Z"/>
                                   </svg>
                              {elseif strstr($item.LABEL|lower, 'phát hành voucher') || strstr($item.LABEL|lower, 'phat hanh voucher')}
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-ticket-perforated" viewBox="0 0 16 16">
                                        <path d="M4 4.85v.9h1v-.9H4Zm7 0v.9h1v-.9h-1ZM4 7.1V8h1v-.9H4Zm7 0V8h1v-.9h-1ZM4 9.35v.9h1v-.9H4Zm7 0v.9h1v-.9h-1Z"/>
                                        <path d="M1.5 3A1.5 1.5 0 0 0 0 4.5V6a.5.5 0 0 0 .5.5 1.5 1.5 0 1 1 0 3 .5.5 0 0 0-.5.5v1.5A1.5 1.5 0 0 0 1.5 13h13a1.5 1.5 0 0 0 1.5-1.5V10a.5.5 0 0 0-.5-.5 1.5 1.5 0 0 1 0-3A.5.5 0 0 0 16 6V4.5A1.5 1.5 0 0 0 14.5 3h-13ZM1 4.5a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 .5.5v1.05a2.5 2.5 0 0 0 0 4.9v1.05a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-1.05a2.5 2.5 0 0 0 0-4.9V4.5Z"/>
                                        <path d="M7.5 4.5a.5.5 0 0 1 1 0V7H11a.5.5 0 0 1 0 1H8.5v2.5a.5.5 0 0 1-1 0V8H5a.5.5 0 0 1 0-1h2.5V4.5Z"/>
                                   </svg>
                              {elseif strstr($item.LABEL|lower, 'voucher') || strstr($item.LABEL|lower, 'mã giảm') || strstr($item.LABEL|lower, 'ma giam')}
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-tags" viewBox="0 0 16 16">
                                        <path d="M3 2v4.586l7 7L14.586 9l-7-7H3ZM2 2a1 1 0 0 1 1-1h4.586a1 1 0 0 1 .707.293l7 7a1 1 0 0 1 0 1.414l-4.586 4.586a1 1 0 0 1-1.414 0l-7-7A1 1 0 0 1 2 6.586V2Z"/>
                                        <path d="M5.5 5a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1Zm0 1a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z"/>
                                        <path d="M1 7.086V2a1 1 0 0 1 1-1v5.086a1 1 0 0 0 .293.707l7 7L8.586 14.5l-7-7A1 1 0 0 1 1 7.086Z"/>
                                   </svg>
                              {elseif strstr($item.LABEL|lower, 'view') || strstr($item.LABEL|lower, 'xem')}
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>
                              {elseif strstr($item.LABEL|lower, 'd/s') || strstr($item.LABEL|lower, 'danh sách')}
                                   <svg width="16px" height="16px" stroke-width="2.1" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" color="#000000"><path d="M8 6L20 6" stroke="#000000" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"></path><path d="M4 6.01L4.01 5.99889" stroke="#000000" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"></path><path d="M4 12.01L4.01 11.9989" stroke="#000000" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"></path><path d="M4 18.01L4.01 17.9989" stroke="#000000" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"></path><path d="M8 12L20 12" stroke="#000000" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"></path><path d="M8 18L20 18" stroke="#000000" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                              {elseif strstr($item.LABEL|lower, 'ngân hàng')}
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bank" viewBox="0 0 16 16">
                                        <path d="m8 0 6.61 3h.89a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5H15v7a.5.5 0 0 1 .485.38l.5 2a.498.498 0 0 1-.485.62H.5a.498.498 0 0 1-.485-.62l.5-2A.501.501 0 0 1 1 13V6H.5a.5.5 0 0 1-.5-.5v-2A.5.5 0 0 1 .5 3h.89L8 0ZM3.777 3h8.447L8 1 3.777 3ZM2 6v7h1V6H2Zm2 0v7h2.5V6H4Zm3.5 0v7h1V6h-1Zm2 0v7H12V6H9.5ZM13 6v7h1V6h-1Zm2-1V4H1v1h14Zm-.39 9H1.39l-.25 1h13.72l-.25-1Z"/>
                                   </svg>
                              {elseif strstr($item.LABEL|lower, 'báo cáo') || strstr($item.LABEL|lower, 'phân tích') || strstr($item.LABEL|lower, 'thống kê')}
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-graph-up-arrow" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M0 0h1v15h15v1H0V0Zm10 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4.9l-3.613 4.417a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61L13.445 4H10.5a.5.5 0 0 1-.5-.5Z"/>
                                   </svg>
                              {elseif strstr($item.LABEL|lower, 'phục hồi')}
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-counterclockwise" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2v1z"/>
                                        <path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466z"/>
                                   </svg>
                              {elseif strstr($item.LABEL|lower, 'sim')}
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sim" viewBox="0 0 16 16">
                                        <path d="M2 1.5A1.5 1.5 0 0 1 3.5 0h7.086a1.5 1.5 0 0 1 1.06.44l1.915 1.914A1.5 1.5 0 0 1 14 3.414V14.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 14.5v-13zM3.5 1a.5.5 0 0 0-.5.5v13a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V3.414a.5.5 0 0 0-.146-.353l-1.915-1.915A.5.5 0 0 0 10.586 1H3.5z"/>
                                        <path d="M5.5 4a.5.5 0 0 0-.5.5V6h2.5V4h-2zm3 0v2H11V4.5a.5.5 0 0 0-.5-.5h-2zM11 7H5v2h6V7zm0 3H8.5v2h2a.5.5 0 0 0 .5-.5V10zm-3.5 2v-2H5v1.5a.5.5 0 0 0 .5.5h2zM4 4.5A1.5 1.5 0 0 1 5.5 3h5A1.5 1.5 0 0 1 12 4.5v7a1.5 1.5 0 0 1-1.5 1.5h-5A1.5 1.5 0 0 1 4 11.5v-7z"/>
                                   </svg>
                              {elseif strstr($item.LABEL|lower, 'sms')}
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chat-left-dots" viewBox="0 0 16 16">
                                        <path d="M14 1a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H4.414A2 2 0 0 0 3 11.586l-2 2V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12.793a.5.5 0 0 0 .854.353l2.853-2.853A1 1 0 0 1 4.414 12H14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                                        <path d="M5 6a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0zm4 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                   </svg>
                              {elseif strstr($item.LABEL|lower, 'ngày')}
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar-check" viewBox="0 0 16 16">
                                        <path d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                                        <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                                   </svg>
                              {elseif strstr($item.LABEL|lower, 'bonus')}
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-award" viewBox="0 0 16 16">
                                        <path d="M9.669.864 8 0 6.331.864l-1.858.282-.842 1.68-1.337 1.32L2.6 6l-.306 1.854 1.337 1.32.842 1.68 1.858.282L8 12l1.669-.864 1.858-.282.842-1.68 1.337-1.32L13.4 6l.306-1.854-1.337-1.32-.842-1.68L9.669.864zm1.196 1.193.684 1.365 1.086 1.072L12.387 6l.248 1.506-1.086 1.072-.684 1.365-1.51.229L8 10.874l-1.355-.702-1.51-.229-.684-1.365-1.086-1.072L3.614 6l-.25-1.506 1.087-1.072.684-1.365 1.51-.229L8 1.126l1.356.702 1.509.229z"/>
                                        <path d="M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1 4 11.794z"/>
                                   </svg>
                              {elseif strstr($item.LABEL|lower, 'nhân viên')}
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-check" viewBox="0 0 16 16">
                                        <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.548 1.17-1.951a.5.5 0 1 1 .858.514ZM11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/>
                                        <path d="M8.256 14a4.474 4.474 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10c.26 0 .507.009.74.025.226-.341.496-.65.804-.918C9.077 9.038 8.564 9 8 9c-5 0-6 3-6 4s1 1 1 1h5.256Z"/>
                                   </svg>
                              {elseif strstr($item.LABEL|lower, 'doanh thu')}
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-coin" viewBox="0 0 16 16">
                                        <path d="M5.5 9.511c.076.954.83 1.697 2.182 1.785V12h.6v-.709c1.4-.098 2.218-.846 2.218-1.932 0-.987-.626-1.496-1.745-1.76l-.473-.112V5.57c.6.068.982.396 1.074.85h1.052c-.076-.919-.864-1.638-2.126-1.716V4h-.6v.719c-1.195.117-2.01.836-2.01 1.853 0 .9.606 1.472 1.613 1.707l.397.098v2.034c-.615-.093-1.022-.43-1.114-.9H5.5zm2.177-2.166c-.59-.137-.91-.416-.91-.836 0-.47.345-.822.915-.925v1.76h-.005zm.692 1.193c.717.166 1.048.435 1.048.91 0 .542-.412.914-1.135.982V8.518l.087.02z"/>
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                        <path d="M8 13.5a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11zm0 .5A6 6 0 1 0 8 2a6 6 0 0 0 0 12z"/>
                                   </svg>
                              {elseif strstr($item.LABEL|lower, 'doanh số')}
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cash-coin" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M11 15a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm5-4a5 5 0 1 1-10 0 5 5 0 0 1 10 0z"/>
                                        <path d="M9.438 11.944c.047.596.518 1.06 1.363 1.116v.44h.375v-.443c.875-.061 1.386-.529 1.386-1.207 0-.618-.39-.936-1.09-1.1l-.296-.07v-1.2c.376.043.614.248.671.532h.658c-.047-.575-.54-1.024-1.329-1.073V8.5h-.375v.45c-.747.073-1.255.522-1.255 1.158 0 .562.378.92 1.007 1.066l.248.061v1.272c-.384-.058-.639-.27-.696-.563h-.668zm1.36-1.354c-.369-.085-.569-.26-.569-.522 0-.294.216-.514.572-.578v1.1h-.003zm.432.746c.449.104.655.272.655.569 0 .339-.257.571-.709.614v-1.195l.054.012z"/>
                                        <path d="M1 0a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h4.083c.058-.344.145-.678.258-1H3a2 2 0 0 0-2-2V3a2 2 0 0 0 2-2h10a2 2 0 0 0 2 2v3.528c.38.34.717.728 1 1.154V1a1 1 0 0 0-1-1H1z"/>
                                        <path d="M9.998 5.083 10 5a2 2 0 1 0-3.132 1.65 5.982 5.982 0 0 1 3.13-1.567z"/>
                                   </svg>
                              {elseif strstr($item.LABEL|lower, 'recheck')}
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2-all" viewBox="0 0 16 16">
                                        <path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l7-7zm-4.208 7-.896-.897.707-.707.543.543 6.646-6.647a.5.5 0 0 1 .708.708l-7 7a.5.5 0 0 1-.708 0z"></path>
                                        <path d="m5.354 7.146.896.897-.707.707-.897-.896a.5.5 0 1 1 .708-.708z"></path>
                                   </svg>
                              {elseif strstr($item.LABEL|lower, 'sổ')}
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-wallet" viewBox="0 0 16 16">
                                        <path d="M0 3a2 2 0 0 1 2-2h13.5a.5.5 0 0 1 0 1H15v2a1 1 0 0 1 1 1v8.5a1.5 1.5 0 0 1-1.5 1.5h-12A2.5 2.5 0 0 1 0 12.5V3zm1 1.732V12.5A1.5 1.5 0 0 0 2.5 14h12a.5.5 0 0 0 .5-.5V5H2a1.99 1.99 0 0 1-1-.268zM1 3a1 1 0 0 0 1 1h12V2H2a1 1 0 0 0-1 1z"/>
                                   </svg>
                              {elseif strstr($item.LABEL|lower, 'thiết lập')}
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16">
                                        <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/>
                                        <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115l.094-.319z"/>
                                   </svg>
                              {elseif strstr($item.LABEL|lower, 'bảng') || strstr($item.LABEL|lower, 'mua vào - bán ra')}
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-table" viewBox="0 0 16 16">
                                        <path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zm15 2h-4v3h4V4zm0 4h-4v3h4V8zm0 4h-4v3h3a1 1 0 0 0 1-1v-2zm-5 3v-3H6v3h4zm-5 0v-3H1v2a1 1 0 0 0 1 1h3zm-4-4h4V8H1v3zm0-4h4V4H1v3zm5-3v3h4V4H6zm4 4H6v3h4V8z"/>
                                   </svg>
                              {elseif strstr($item.LABEL|lower, 'import') || strstr($item.LABEL|lower, 'nhập')}
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/></svg>
                              {elseif strstr($item.LABEL|lower, 'login')}
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-in-right" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0v-2z"/>
                                        <path fill-rule="evenodd" d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
                                   </svg>
                              {elseif strstr($item.LABEL|lower, 'logout')}
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-in-left" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M10 3.5a.5.5 0 0 0-.5-.5h-8a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5h8a.5.5 0 0 0 .5-.5v-2a.5.5 0 0 1 1 0v2A1.5 1.5 0 0 1 9.5 14h-8A1.5 1.5 0 0 1 0 12.5v-9A1.5 1.5 0 0 1 1.5 2h8A1.5 1.5 0 0 1 11 3.5v2a.5.5 0 0 1-1 0v-2z"/>
                                        <path fill-rule="evenodd" d="M4.146 8.354a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H14.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3z"/>
                                   </svg>
                              {elseif strstr($item.LABEL|lower, 'tồn kho')}
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-archive" viewBox="0 0 16 16">
                                        <path d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1V2zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5H2zm13-3H1v2h14V2zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
                                   </svg>
                              {elseif strstr($item.LABEL|lower, 'xuất vé')}
                                   <svg fill="#000000" height="18px" width="18px" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512.16 512.16" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g transform="translate(1 1)"> <g> <g> <path d="M272.067,336.147H408.6c5.12,0,8.533-3.413,8.533-8.533v-76.8c0-5.12-3.413-8.533-8.533-8.533H272.067 c-5.12,0-8.533,3.413-8.533,8.533v76.8C263.533,332.733,266.947,336.147,272.067,336.147z M280.6,259.347h119.467v59.733H280.6 V259.347z"></path> <path d="M41.667,225.213h68.267c5.12,0,8.533-3.413,8.533-8.533s-3.413-8.533-8.533-8.533H41.667 c-5.12,0-8.533,3.413-8.533,8.533S36.547,225.213,41.667,225.213z"></path> <path d="M144.067,225.213h68.267c5.12,0,8.533-3.413,8.533-8.533s-3.413-8.533-8.533-8.533h-68.267 c-5.12,0-8.533,3.413-8.533,8.533S138.947,225.213,144.067,225.213z"></path> <path d="M41.667,259.347H152.6c5.12,0,8.533-3.413,8.533-8.533s-3.413-8.533-8.533-8.533H41.667c-5.12,0-8.533,3.413-8.533,8.533 S36.547,259.347,41.667,259.347z"></path> <path d="M212.333,242.28h-25.6c-5.12,0-8.533,3.413-8.533,8.533s3.413,8.533,8.533,8.533h25.6c5.12,0,8.533-3.413,8.533-8.533 S217.453,242.28,212.333,242.28z"></path> <path d="M503.32,136.467c-5.973-7.68-13.653-11.947-23.04-12.8l-20.48-2.482V97.213V80.147c0-18.773-15.36-34.133-34.133-34.133 H33.133C14.36,46.013-1,61.373-1,80.147v17.067v68.267v187.733c0,15.413,10.357,28.518,24.453,32.718 c-0.43,17.262,12.631,32.248,30.161,33.842l394.24,44.373c0.853,0,2.56,0,3.413,0c17.067,0,32.427-12.8,34.133-29.013 l25.6-273.92C511.853,152.68,509.293,143.293,503.32,136.467z M16.067,105.747h426.667v22.187v29.013H16.067V105.747z M33.133,63.08h392.533c9.387,0,17.067,7.68,17.067,17.067v8.533H16.067v-8.533C16.067,70.76,23.747,63.08,33.133,63.08z M16.067,353.213v-179.2h426.667v179.2c0,9.387-7.68,17.067-17.067,17.067H33.987h-0.853 C23.747,370.28,16.067,362.6,16.067,353.213z M493.933,157.8l-25.6,273.92c-0.853,9.387-9.387,16.213-18.773,15.36 L56.173,402.707c-8.533-0.853-14.507-7.68-15.36-15.36h384.853c18.773,0,34.133-15.36,34.133-34.133V165.48v-28.16l19.627,1.707 c4.267,0,8.533,2.56,11.093,5.973C493.08,148.413,494.787,153.533,493.933,157.8z"></path> </g> </g> </g> </g></svg>
                              {elseif strstr($item.LABEL|lower, 'tcb')}
                                   <svg fill="currentColor" height="18" width="18" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 484.849 484.849" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M262.797,274.552c30.206,30.206,63.252,49.446,81.428,47.771c13.579-1.252,30.7-19.862,30.7-19.862l-38.945-38.944 l-15.532,15.532L258.3,216.901l15.532-15.532l-38.945-38.944c0,0-18.611,17.12-19.862,30.699 C213.351,211.3,232.59,244.345,262.797,274.552z"></path> <path d="M49.924,0v113.712h-22.5v30h22.5v83.712h-25v30h25v83.712h-25v30h25v113.712h410V0H49.924z M79.924,371.136h25v-30h-25 v-83.712h25v-30h-25v-83.712h25v-30h-25V30h50v424.849h-50V371.136z M429.924,454.849h-270V30h270V454.849z"></path> </g> </g></svg>
                              {elseif strstr($item.LABEL|lower, 'cập nhật')}
                                   <svg width="18" height="18" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" fill="currentColor"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <rect x="0" fill="none" width="20" height="20"></rect> <g> <path d="M10.2 3.28c3.53 0 6.43 2.61 6.92 6h2.08l-3.5 4-3.5-4h2.32c-.45-1.97-2.21-3.45-4.32-3.45-1.45 0-2.73.71-3.54 1.78L4.95 5.66C6.23 4.2 8.11 3.28 10.2 3.28zm-.4 13.44c-3.52 0-6.43-2.61-6.92-6H.8l3.5-4c1.17 1.33 2.33 2.67 3.5 4H5.48c.45 1.97 2.21 3.45 4.32 3.45 1.45 0 2.73-.71 3.54-1.78l1.71 1.95c-1.28 1.46-3.15 2.38-5.25 2.38z"></path> </g> </g></svg>
                              {elseif strstr($item.LABEL|lower, 'bxh')}
                                   <svg width="18" height="18" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <rect width="48" height="48" fill="white" fill-opacity="0.01"></rect> <rect x="4" y="18" width="13" height="24" stroke="currentColor" fill="#fff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"></rect> <rect x="17" y="6" width="13" height="36" stroke="currentColor" fill="#fff" stroke-width="4" stroke-linejoin="round"></rect> <rect x="30" y="26" width="13" height="16" stroke="currentColor" fill="#fff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"></rect> </g></svg>
                              {elseif strstr($item.LABEL|lower, 'website')}
                                   <svg fill="currentColor" height="18" width="18" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512.005 512.005" xml:space="preserve"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <g> <path d="M505.756,475.587l-48.25-48.25c7.505-12.703,11.828-27.511,11.828-43.336c0-47.131-38.202-85.333-85.333-85.333 s-85.333,38.202-85.333,85.333s38.202,85.333,85.333,85.333c15.825,0,30.632-4.323,43.336-11.828l48.25,48.25 c8.331,8.331,21.839,8.331,30.17,0C514.087,497.425,514.087,483.918,505.756,475.587z M341.334,384.001 c0-23.567,19.099-42.667,42.667-42.667s42.667,19.099,42.667,42.667c0,11.758-4.755,22.403-12.447,30.12 c-0.017,0.017-0.036,0.031-0.053,0.048s-0.031,0.036-0.048,0.053c-7.717,7.691-18.362,12.447-30.12,12.447 C360.433,426.667,341.334,407.568,341.334,384.001z"></path> <path d="M277.334,490.386V277.334h213.333c11.782,0,21.333-9.551,21.333-21.333C512,117.878,395.628,3.143,257.554,0.074 c-0.81-0.058-1.62-0.065-2.429-0.031c-1.081-0.014-2.158-0.042-3.242-0.042C112.681,0.001,0,114.698,0,256.001 s112.681,256,251.883,256c0.695,0,1.381-0.039,2.059-0.104c0.678,0.065,1.364,0.104,2.059,0.104 c11.782,0,21.333-9.551,21.333-21.333v-0.256C277.334,490.403,277.334,490.394,277.334,490.386z M43.705,277.334h84.938 c1.741,29.058,7.031,57.177,15.604,83.808c-19.501,7.311-38.28,16.564-56.092,27.668 C63.773,357.601,47.786,319.264,43.705,277.334z M88.883,122.27c17.636,11.131,36.241,20.446,55.579,27.841 c-8.698,26.86-14.063,55.237-15.819,84.556H43.705C47.827,192.322,64.092,153.645,88.883,122.27z M468.199,234.667h-84.826 c-1.735-29.349-7.091-57.741-15.792-84.613c18.507-7.095,36.346-15.958,53.318-26.484 C446.656,154.877,463.781,193.177,468.199,234.667z M351.795,110.406c-9.83-20.59-21.801-39.969-35.762-57.83 c27.065,8.526,52.061,22.244,73.679,39.914C377.512,99.451,364.842,105.435,351.795,110.406z M234.667,127.171 c-11.079-0.871-22.025-2.426-32.797-4.626c9.097-17.882,20.076-34.613,32.797-49.924V127.171z M234.667,169.937v64.73h-63.253 c1.706-25.041,6.39-49.223,13.854-72.082C201.409,166.331,217.914,168.801,234.667,169.937z M277.334,72.586 c12.744,15.318,23.737,32.049,32.842,49.928c-10.789,2.213-21.749,3.777-32.842,4.651V72.586z M160.239,110.465 c-14.101-5.358-27.757-11.894-40.848-19.566c22.773-19.021,49.55-33.233,78.837-41.135 C183.314,68.408,170.591,88.763,160.239,110.465z M159.961,400.817c10.454,21.997,23.352,42.616,38.504,61.486 c-29.762-7.993-56.939-22.498-79.964-41.946C131.795,412.664,145.656,406.133,159.961,400.817z M234.667,439.144 c-12.788-15.371-23.814-32.166-32.937-50.117c10.822-2.124,21.817-3.575,32.937-4.356V439.144z M234.667,341.908 c-16.787,1.017-33.333,3.384-49.524,7.027c-7.391-22.71-12.033-46.725-13.729-71.601h63.253V341.908z M277.334,169.935 c16.767-1.141,33.289-3.617,49.449-7.379c7.463,22.863,12.135,47.052,13.822,72.111h-63.27V169.935z"></path> </g> </g> </g> </g></svg>
                              {else}
                                   <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/></svg>
                              {/if}
                              <span>{$item.LABEL} {$shortcutTopMenu.name}</span> 
                              </a>
                              </li>
                              {/if}
                              {/foreach}
                         {/if}
                         </ul>
                         </li>
                    </ul>
                    {/if}
               </li>
               {/if}
               {/foreach}
           
               {else}
                    <li class="navbar-brand-container nav-item">
                         <a class="navbar-brand with-home-icon nav-link" href="index.php?module=Home&action=index">
                             <span class="suitepicon suitepicon-action-home"></span>
                         </a>
                    </li>
               {/if}
               <!-- ./end Action Side Bar -->
          </ul> 
     </div>
</aside>
{/if}

<!-- ICON TOGGLER SIDEBAR -->
<button id="buttontoggle" class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
     <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16">
          <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
     </svg>
</button>
