SUGAR.measurements = {
  "breakpoints": {
    "x-small": 750,
    "small": 768,
    "medium": 992,
    "large": 1130,
    "x-large": 1250
  }
};

SUGAR.loaded_once = false;

$(document).ready(function () {
  loadSidebar();

  $("ul.clickMenu").each(function (index, node) {
    $(node).sugarActionMenu();
  });
  // Back to top animation
  $('#backtotop').click(function (event) {
    event.preventDefault();
    $('html, body').animate({ scrollTop: 0 }, 500); // Scroll speed to the top
  });
});

YAHOO.util.Event.onAvailable('sitemapLinkSpan', function () {
  document.getElementById('sitemapLinkSpan').onclick = function () {
    ajaxStatus.showStatus(SUGAR.language.get('app_strings', 'LBL_LOADING_PAGE'));
    var smMarkup = '';
    var callback = {
      success: function (r) {
        ajaxStatus.hideStatus();
        document.getElementById('sm_holder').innerHTML = r.responseText;
        with (document.getElementById('sitemap').style) {
          display = "block";
          position = "absolute";
          right = 0;
          top = 80;
        }
        document.getElementById('sitemapClose').onclick = function () {
          document.getElementById('sitemap').style.display = "none";
        }
      }
    }
    postData = 'module=Home&action=sitemap&GetSiteMap=now&sugar_body_only=true';
    YAHOO.util.Connect.asyncRequest('POST', 'index.php', callback, postData);
  }
});
function IKEADEBUG() {
  var moduleLinks = document.getElementById('moduleList').getElementsByTagName("a");
  moduleLinkMouseOver = function () {
    var matches = /grouptab_([0-9]+)/i.exec(this.id);
    var tabNum = matches[1];
    var moduleGroups = document.getElementById('subModuleList').getElementsByTagName("span");
    for (var i = 0; i < moduleGroups.length; i++) {
      if (i == tabNum) {
        moduleGroups[i].className = 'selected';
      }
      else {
        moduleGroups[i].className = '';
      }
    }
    var groupList = document.getElementById('moduleList').getElementsByTagName("li");
    var currentGroupItem = tabNum;
    for (var i = 0; i < groupList.length; i++) {
      var aElem = groupList[i].getElementsByTagName("a")[0];
      if (aElem == null) {
        continue;
      }
      var classStarter = 'notC';
      if (aElem.id == "grouptab_" + tabNum) {
        classStarter = 'c';
        currentGroupItem = i;
      }
      var spanTags = groupList[i].getElementsByTagName("span");
      for (var ii = 0; ii < spanTags.length; ii++) {
        if (spanTags[ii].className == null) {
          continue;
        }
        var oldClass = spanTags[ii].className.match(/urrentTab.*/);
        spanTags[ii].className = classStarter + oldClass;
      }
    }
    var menuHandle = moduleGroups[tabNum];
    var parentMenu = groupList[currentGroupItem];
    if (menuHandle && parentMenu) {
      updateSubmenuPosition(menuHandle, parentMenu);
    }
  };
  for (var i = 0; i < moduleLinks.length; i++) {
    moduleLinks[i].onmouseover = moduleLinkMouseOver;
  }
};
function updateSubmenuPosition(menuHandle, parentMenu) {
  var left = '';
  if (left == "") {
    p = parentMenu;
    var left = 0;
    while (p && p.tagName.toUpperCase() != 'BODY') {
      left += p.offsetLeft;
      p = p.offsetParent;
    }
  }
  var bw = checkBrowserWidth();
  if (!parentMenu) {
    return;
  }
  var groupTabLeft = left + (parentMenu.offsetWidth / 2);
  var subTabHalfLength = 0;
  var children = menuHandle.getElementsByTagName('li');
  for (var i = 0; i < children.length; i++) {
    if (children[i].className == 'subTabMore' || children[i].parentNode.className == 'cssmenu') {
      continue;
    }
    subTabHalfLength += parseInt(children[i].offsetWidth);
  }
  if (subTabHalfLength != 0) {
    subTabHalfLength = subTabHalfLength / 2;
  }
  var totalLengthInTheory = subTabHalfLength + groupTabLeft;
  if (subTabHalfLength > 0 && groupTabLeft > 0) {
    if (subTabHalfLength >= groupTabLeft) {
      left = 1;
    } else {
      left = groupTabLeft - subTabHalfLength;
    }
  }
  if (totalLengthInTheory > bw) {
    var differ = totalLengthInTheory - bw;
    left = groupTabLeft - subTabHalfLength - differ - 2;
  }
  if (left >= 0) {
    menuHandle.style.marginLeft = left + 'px';
  }
}
YAHOO.util.Event.onDOMReady(function () {
  if (document.getElementById('subModuleList')) {
    var parentMenu = false;
    var moduleListDom = document.getElementById('moduleList');
    if (moduleListDom != null) {
      var parentTabLis = moduleListDom.getElementsByTagName("li");
      var tabNum = 0;
      for (var ii = 0; ii < parentTabLis.length; ii++) {
        var spans = parentTabLis[ii].getElementsByTagName("span");
        for (var jj = 0; jj < spans.length; jj++) {
          if (spans[jj].className.match(/currentTab.*/)) {
            tabNum = ii;
          }
        }
      }
      var parentMenu = parentTabLis[tabNum];
    }
    var moduleGroups = document.getElementById('subModuleList').getElementsByTagName("span");
    for (var i = 0; i < moduleGroups.length; i++) {
      if (moduleGroups[i].className.match(/selected/)) {
        tabNum = i;
      }
    }
    var menuHandle = moduleGroups[tabNum];
    if (menuHandle && parentMenu) {
      updateSubmenuPosition(menuHandle, parentMenu);
    }
  }
});
SUGAR.themes = SUGAR.namespace("themes");
SUGAR.append(SUGAR.themes, {
  allMenuBars: {}, setModuleTabs: function (html) {
    var el = document.getElementById('ajaxHeader');
    if (el) {
      $('#ajaxHeader').html(html);
      loadSidebar();
      if ($(window).width() < 979) {
        $('#bootstrap-container').removeClass('main');
      }
    }
  }, actionMenu: function () {
    $("ul.clickMenu").each(function (index, node) {
      $(node).sugarActionMenu();
    });
  }, loadModuleList: function () {
    var nodes = YAHOO.util.Selector.query('#moduleList>div'), currMenuBar;
    this.allMenuBars = {};
    for (var i = 0; i < nodes.length; i++) {
      currMenuBar = SUGAR.themes.currMenuBar = new YAHOO.widget.MenuBar(nodes[i].id, {
        autosubmenudisplay: true,
        visible: false,
        hidedelay: 750,
        lazyload: true
      });
      currMenuBar.render();
      this.allMenuBars[nodes[i].id.substr(nodes[i].id.indexOf('_') + 1)] = currMenuBar;
      if (typeof YAHOO.util.Dom.getChildren(nodes[i]) == 'object' && YAHOO.util.Dom.getChildren(nodes[i]).shift().style.display != 'none') {
        oMenuBar = currMenuBar;
      }
    }
    YAHOO.util.Event.onAvailable('subModuleList', IKEADEBUG);
  }, setCurrentTab: function () {
  }
});
YAHOO.util.Event.onDOMReady(SUGAR.themes.loadModuleList, SUGAR.themes, true);

// Custom jQuery for theme
// Script to toggle copyright popup
$("button").click(function () {
  $("#sugarcopy").toggle();
});

var initFooterPopups = function () {
  $("#dialog, #dialog2").dialog({
    autoOpen: false,
    show: {
      effect: "blind",
      duration: 100
    },
    hide: {
      effect: "fade",
      duration: 1000
    }
  });
  $("#powered_by").click(function () {
    $("#dialog").dialog("open");
    $("#overlay").show().css({ "opacity": "0.5" });
  });
  $("#admin_options").click(function () {
    $("#dialog2").dialog("open");
  });
};

// Custom JavaScript for copyright pop-ups
$(function () {
  initFooterPopups();
});

// Back to top animation
$('#backtotop').click(function (event) {
  event.preventDefault();
  $('html, body').animate({ scrollTop: 0 }, 500); // Scroll speed to the top
});

// Tabs jQuery for Admin panel
$(function () {
  var tabs = $("#tabs").tabs();
  tabs.find(".ui-tabs-nav").sortable({
    axis: "x",
    stop: function () {
      tabs.tabs("refresh");
    }
  });
});


// JavaScript fix to remove unrequired classes on smaller screens where sidebar is obsolete
$(window).resize(function () {
  if ($(window).width() < 979) {
    $('#bootstrap-container').removeClass('col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 sidebar main');
  }
  if ($(window).width() > 980 && $('.sidebar').is(':visible')) {
    $('#bootstrap-container').addClass('col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main');
  }
});

// jQuery to toggle sidebar
function loadSidebar() {
  $icon_chevron_left = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"></path></svg>';
  $icon_chevron_right = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/></svg>';

  $('#buttontoggle').click(function () {
    $('body').toggleClass('sidebar-icon-only');

    if ($('body').hasClass('sidebar-icon-only')) {
      $.cookie('sidebartoggle', 'collapsed');
      $('#sidebar').removeClass('expanded-sidebar');
      $('#sidebar').addClass('collapsed-sidebar');
      $(this).html($icon_chevron_right);
    } else {
      $.cookie('sidebartoggle', 'expanded');
      $('#sidebar').removeClass('collapsed-sidebar');
      $('#sidebar').addClass('expanded-sidebar');
      $(this).html($icon_chevron_left);
      $('.nav-current-item').removeClass('hover-open');
      $('body').removeClass('sidebar-visible');
    }
  });

  // Lấy giá trị coolie;
  let val_sidebar_toggle = $.cookie('sidebartoggle');

  if (val_sidebar_toggle == 'collapsed') {
    $('#buttontoggle').html($icon_chevron_right);
    $('body').addClass('sidebar-icon-only');
    $('#sidebar').removeClass('expanded-sidebar');
    $('#sidebar').addClass('collapsed-sidebar');
  }
  else {
    $('#buttontoggle').html($icon_chevron_left);
    $('body').removeClass('sidebar-icon-only');
    $('#sidebar').removeClass('collapsed-sidebar');
    $('#sidebar').addClass('expanded-sidebar');
  }
}

function selectTab(tab) {
  $('#content div.tab-content div.tab-pane-NOBOOTSTRAPTOGGLER').hide();
  $('#content div.tab-content div.tab-pane-NOBOOTSTRAPTOGGLER').eq(tab).show().addClass('active').addClass('in');
};

function changeFirstTab(src) {
  var selected = $(src).attr('id');
  var selectedHtml = $(selected.context).html();
  $('#xstab0').html(selectedHtml);

  var i = $(src).parents('li').index();
  selectTab(parseInt(i));
  return true;
}
// End of custom jQuery


// fix for tab navigation on user profile for SuiteP theme

var getParameterByName = function (name, url) {
  if (!url) url = window.location.href;
  name = name.replace(/[\[\]]/g, "\\$&");
  var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
    results = regex.exec(url);
  if (!results) return null;
  if (!results[2]) return '';
  return decodeURIComponent(results[2].replace(/\+/g, " "));
}
var isUserProfilePage = function () {
  var module = getParameterByName('module');
  if (!module) {
    module = $('#EditView_tabs').closest('form#EditView').find('input[name="module"]').val();
  }
  if (!module) {
    if (typeof module_sugar_grp1 !== "undefined") {
      module = module_sugar_grp1;
    }
  }
  return module == 'Users';
};

var isEditViewPage = function () {
  var action = getParameterByName('action');
  if (!action) {
    action = $('#EditView_tabs').closest('form#EditView').find('input[name="page"]').val();
  }
  return action == 'EditView';
};

var isDetailViewPage = function () {
  var action = getParameterByName('action');
  if (!action) {
    action = action_sugar_grp1;
  }
  return action == 'DetailView';
};

var refreshListViewCheckbox = function (e) {
  $(e).removeClass('glyphicon-check');
  $(e).removeClass('glyphicon-unchecked');
  if ($(e).next().prop('checked')) {
    $(e).addClass('glyphicon-check');
  }
  else {
    $(e).addClass('glyphicon-unchecked');
  }
  $(e).removeClass('disabled')
  if ($(e).next().prop('disabled')) {
    $(e).addClass('disabled')
  }
};

$(function () {
  // Fix for footer position
  if ($('#bootstrap-container footer').length > 0) {
    var clazz = $('#bootstrap-container footer').attr('class');
    $('body').append('<footer class="' + clazz + '">' + $('#bootstrap-container footer').html() + '</footer>');
    $('#bootstrap-container footer').remove();
    initFooterPopups();
  }

  var hideEmptyFormCellsOnTablet = function () {
    if ($(window).width() <= 767) {
      $('div#content div#pagecontent form#EditView div.edit.view table tbody tr td').each(function (i, e) {
        $(e).find('slot').each(function (i, e) {
          if ($(e).html().trim() == '&nbsp;') {
            $(e).html('&nbsp;');
          }
        });
        if ($(e).html().trim() == '<span>&nbsp;</span>') {
          $(e).addClass('hidden');
          $(e).addClass('hiddenOnTablet');
        }
      });
    }
    else {
      $('div#content div#pagecontent form#EditView div.edit.view table tbody tr td.hidden.hiddenOnTablet').each(function (i, e) {
        $(e).removeClass('hidden');
        $(e).removeClass('hiddenOnTablet');
      });
    }
  }

  $(window).click(function () {
    hideEmptyFormCellsOnTablet();
    setTimeout(function () {
      hideEmptyFormCellsOnTablet();
    }, 500);
  });

  $(window).resize(function () {
    hideEmptyFormCellsOnTablet();
  });

  $(window).load(function () {
    hideEmptyFormCellsOnTablet();
  });

  $(document).ready(function () {
    hideEmptyFormCellsOnTablet();
  });

  setTimeout(function () {
    hideEmptyFormCellsOnTablet();
  }, 1500);

  var listViewCheckboxInit = function () {
    var checkboxesInitialized = false;
    var checkboxesInitializeInterval = false;
    var checkboxesCountdown = 100;
    var initializeBootstrapCheckboxes = function () {
      if (!checkboxesInitialized) {
        if ($('.glyphicon.bootstrap-checkbox').length == 0) {
          if (!checkboxesInitializeInterval) {
            checkboxesInitializeInterval = setInterval(function () {
              checkboxesCountdown--;
              if (checkboxesCountdown <= 0) {
                clearInterval(checkboxesInitializeInterval);
                return;
              }
              initializeBootstrapCheckboxes();
            }, 100);
          }
        } else {
          $('.glyphicon.bootstrap-checkbox').each(function (i, e) {
            $(e).removeClass('hidden');
            $(e).next().hide();
            refreshListViewCheckbox(e);
            if (!$(e).hasClass('initialized-checkbox')) {
              $(e).click(function () {
                $(this).next().click();
                refreshListViewCheckbox($(this));
              });
              $(e).addClass('initialized-checkbox');
            }
          });

          $('#selectLink > li > ul > li > a, #selectLinkTop > li > ul > li > a, #selectLinkBottom > li > ul > li > a').click(function (e) {
            e.preventDefault();
            $('.glyphicon.bootstrap-checkbox').each(function (i, e) {
              refreshListViewCheckbox(e);
            });
          });

          checkboxesInitialized = true;
          clearInterval(checkboxesInitializeInterval);
          checkboxesInitializeInterval = false;
        }
      }
    };
    initializeBootstrapCheckboxes();
  };
  setInterval(function () {
    listViewCheckboxInit();
  }, 100);

  // CUSTOME JS
  /**
   * Back to top button
   */
  let backtotop = $('.back-to-top')
  if (backtotop) {
    const toggleBacktotop = () => {
      if (window.scrollY > 100) {
        backtotop.addClass('active')
      } else {
        backtotop.removeClass('active')
      }
    }
    $(window).on('load', toggleBacktotop);
    $(document).on('scroll', toggleBacktotop);
  }

  //Open submenu on hover in compact sidebar mode and horizontal menu mode
  $(document).on('mouseenter', '.sidebar-horizontal .nav-item', function () {
    $(this).addClass('active');
    $(this).children('.sub-menu').addClass('show');
    $(this).children('.nav-link').removeClass('collapsed');
  });

  $(document).on('mouseleave', '.sidebar-horizontal .nav-item', function () {
    $(this).removeClass('active');
    $(this).children('.sub-menu').removeClass('show');
    $(this).children('.nav-link').addClass('collapsed');
  });

  // MENU MOBILE
  (function ($) {
    'use strict';
    $(function () {
      $('[data-toggle="offcanvas"]').on("click", function () {
        $('.sidebar-offcanvas').toggleClass('active')
        $('.sidebar-horizontal-mobile').toggleClass('active')
      });
    });

    $(document).click(function(event) {
        // Kiểm tra xem người dùng click vào phần tử nào
        // Nếu không phải là sidebar hoặc nút toggleButton, ẩn sidebar đi
        if (!$(event.target).closest('#sidebar, .menu-mobile-icon').length) {
            $('#sidebar').removeClass('active');
        }
    });

  })(jQuery);

  //Open submenu on hover in compact sidebar mode and horizontal menu mode
  // $(document).on('mouseenter mouseleave', '#buttontoggle', function (ev) {
  //   var body = $('body');
  //   var sidebarIconOnly = body.hasClass("sidebar-icon-only");
  //   var sidebarFixed = body.hasClass("sidebar-fixed");

  //   if (!('ontouchstart' in document.documentElement)) {
  //     if (sidebarIconOnly) {

  //       var $menuItem = $('.sidebar-current .nav-item');
  //       if (ev.type === 'mouseenter') {
  //         $menuItem.addClass('hover-open')
  //         body.addClass('sidebar-visible');
  //       } else {
  //         $menuItem.removeClass('hover-open')
  //         // body.removeClass('sidebar-visible');
  //       }

  //     } else {

  //       if (ev.type === 'mouseenter') {
  //         body.removeClass('sidebar-icon-only');
  //       }

  //     }

  //   }
  // });

  // IMPORT
  // WHEN USER IMPORT FILE
  // let input_file = $("#vcard_file");
  // let name_file  = $("#file__input-name-imported")
  // input_file.on("change", () => {
  //     let imported_file = document.querySelector("input[type=file]").files[0];
  //     name_file.text(imported_file.name);
  // })

  let input_userfile = $("#userfile");
  let name_userfile_imported = $("#name_file_imported")
  input_userfile.on("change", () => {
    let userfile_imported = document.querySelector("input[type=file]").files[0];
    name_userfile_imported.text(userfile_imported.name);
  })

  $('#remove_file_import').on('click', function () {
    input_userfile.val('');
    name_userfile_imported.text('Not selected file');
  });

});

$(document).ready(function () {
  // WAITING LOADING
  $(document).on('click', '.button-action, .save-popup-dialog', function () {
    $('.container-waiting').show();
  });

  // CLOSE WARNING
  $('.toast-close').on('click', function () {
    $('.toast-warning').removeClass('active');
  });

  // MODAL SUCCESS AND ERROR
  $(document).on('click', '.modal-overlay, .btn-modal-close', function () {
    $("#modal-container").addClass('out');
    setTimeout(function () {
      $("#modal-container").removeClass('out active');
      $("#modal-content").html('');
    }, 1000);

    $('body').removeClass('modal-active');
  });

  $(document).on('click', '.modal-overlay.reload, .btn-modal-close.reload', function () {
    setTimeout(function () {
      location.reload();
    }, 1200);
  });

  // Viết hoa danh từ riêng - call
  $('#voiceip-name').on('input', function () {
    let last_name = $(this).val();
    let formattedName = formatName(last_name);
    $(this).val(formattedName);
  });

  $("#popup__voiceip--wrap").draggable({
    stop: function( event, ui ) {
      $(this).removeClass('start');
      $(this).addClass('stop');
    },
    start: function(event, ui) {
      $(this).removeClass('stop');
      $(this).addClass('start');
      $(this).addClass('draggable');
    }
  });
  

  // EC_TONGHOP
  $(document).on("click", "#btnSearch_cancel", function() {
    $('form[name="search_form"]').removeClass('active');
    $('.overlay-mobile').slideUp(300);
  });

  $(document).on("click", "#filter_report", function() {
    $('form[name="search_form"]').addClass('active');
    $('.overlay-mobile').slideDown(300);
  });


});

function showModalNotify(type_modal, text_modal, text_description = '') {
  let view_detail = '';
  if (text_description.length > 0) {
    view_detail = `<a onclick="show_error_description()" style="text-decoration:underline; cursor:pointer;">Xem chi tiết</a>
      <div class="description p-1" style="display:none">
        ${text_description}
      </div>
    `;
  }
  let html_error = `<div id="modal-error" class="modal-main modal-error">
                    <div class="modal-header">
                        <div class="icon-box">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
                                <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                            </svg>
                        </div>	
                        <h4 class="modal-title w-100">Error</h4>
                    </div>
                    <div class="modal-body" style="text-align:center;">
                        <p class="modal-text">${text_modal}</p>
                        ${view_detail}
                    </div>
                    <div class="modal-footer">
                        <button class="btn-modal-close">OK</button>
                    </div>
                  </div>`;

  let html_success = `<div id="modal-success" class="modal-main modal-success">
                      <div class="modal-header">
                        <div class="icon-box">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-check-lg" viewBox="0 0 16 16">
                                <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z"/>
                            </svg>
                        </div>	
                        <h4 class="modal-title w-100">Thành Công</h4>
                      </div>
                      <div class="modal-body">
                          <p class="modal-text">${text_modal}</p>
                      </div>
                      <div class="modal-footer">
                          <button class="btn-modal-close">OK</button>
                      </div>
                  </div>`;

  let html_warning = `<div id="modal-warning" class="modal-main modal-warning">
                      <div class="modal-header">
                        <div class="icon-box">
                          <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-exclamation-lg" viewBox="0 0 16 16">
                            <path d="M7.005 3.1a1 1 0 1 1 1.99 0l-.388 6.35a.61.61 0 0 1-1.214 0L7.005 3.1ZM7 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0"/>
                          </svg>
                        </div>	
                        <h4 class="modal-title w-100">Chú ý</h4>
                      </div>
                      <div class="modal-body">
                          <p class="modal-text">${text_modal}</p>
                      </div>
                      <div class="modal-footer">
                          <button class="btn-modal-close">OK</button>
                      </div>
                  </div>`;

  $('#modal-container').addClass('active');
  $('body').addClass('modal-active');

  if (type_modal == 0 || type_modal == -1 || type_modal == 400 || type_modal == 'error') {
    $('#modal-content').append(html_error);
  } else if (type_modal == 1 || type_modal == 200 || type_modal == 'success') {
    $('#modal-content').append(html_success);
  } else if (type_modal == 2 || type_modal == 'warning'){
    $('#modal-content').append(html_warning);
  } else {
    let text_warning = 'type modal không xác định!';
    showToastWarning(text_warning);
  }
}

function show_error_description() {
  if ($('#modal-error .modal-body .description').css('display') == 'none')
    $('#modal-error .modal-body .description').show();
  else
    $('#modal-error .modal-body .description').hide();
}

function showToastWarning(text_warning) {

  $('.container-waiting').hide();

  $('.toast-warning').addClass('active');
  $('.toast-warning #toast-content').text(text_warning);
  $('.toast-warning .progress-bar').animate({ width: "100%" }, 3000);
  setTimeout(function () {
    $(".toast-warning").removeClass('active');
  }, 4000);

  return false;
}

// Chặn submit form từ Edit view
function preventSubmit() {
  // Chỗ này lỗi để dừng sự kiện submit
  if ($('#Loremipsumdolorsitamet').val().length > 0) { return false; }
  return false;
}

// Hàm đếm ngược và reload trang
function countdownAndReload(seconds) {
  // Hiển thị giá trị đầu tiên
  $('#count-down').html(seconds + 's');

  // Đặt interval để đếm ngược
  var interval = setInterval(function () {
    seconds--;

    // Hiển thị giá trị còn lại
    $('#count-down').html(seconds + 's');

    // Kiểm tra nếu đếm ngược đã kết thúc
    if (seconds <= 0) {
      // Hủy interval
      clearInterval(interval);

      // Reload trang
      location.reload();
    }
  }, 1000); // Mỗi giây

  if (seconds == 0) {
    location.reload();
  }
}

function formatName(name) {
  // split name 
  let words = name.split(' ');

  // convert to "Xxx"
  let formattedWords = words.map(function (word) {
    return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
  });

  // concat letter
  let formattedName = formattedWords.join(' ');

  return formattedName;
}

