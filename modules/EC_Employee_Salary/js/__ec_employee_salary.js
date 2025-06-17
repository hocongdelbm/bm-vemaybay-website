var zEle, xthf = [];
xAddEventListener(window, 'load',
  function() {
    xAddEventListener(document, 'keydown', docOnKeydown);
    xAddEventListener(document, 'mousewheel', docOnMousewheel);
  }, false
);

function docOnMousewheel(ne)
{
  var ev = ne || window.event;
  if (ev.ctrlKey) {
    repaint();
    xConsole.log('docOnMousewheel: ' + xZoomFactor());////////
  }
}

function docOnKeydown(ne)
{
  var ev = ne || window.event;
  if (ev.ctrlKey) {
    if (ev.keyCode == 187 || ev.keyCode == 107 || ev.keyCode == 189 || ev.keyCode == 109 || ev.keyCode == 48 || ev.keyCode == 96) {
      repaint();
      xConsole.log('docOnKeydown: ' + xZoomFactor());////////
    }
  }
}


function xZoomFactor()
{
  var ie, ie7, ie8up, factor = 1, rect, physicalW, logicalW;

  if (ie7) {
    if (document.body.getBoundingClientRect) {
      // rect is only in physical pixel size in IE before version 8 
      rect = document.body.getBoundingClientRect ();
      physicalW = rect.right - rect.left;
      logicalW = document.body.offsetWidth;
      // the zoom level is always an integer percent value
      factor = Math.round ((physicalW / logicalW) * 100) / 100;
    }
  }
  else if (ie8up) {
    factor = Math.round((screen.deviceXDPI / screen.logicalXDPI) * 100);
  }
  else { // non-IE
    factor = xZoom(document.body);
  }
  return factor;
}
function repaint()
{
  for (var i = 0; i < xthf.length; ++i) {
    xthf[i].paint();
  }
}

function xZoom(e, z)
{
  if (xDef(e.style.MozTransform)) {
    if (z) e.style.MozTransform = 'scale(' + z + ')';
    else z = parseFloat(e.style.MozTransform.substr(6));
    xConsole.log('xZoom: ' + z + ', ' + xGetComputedStyle(e, '-moz-transform'));
  }
  else if (xDef(e.style.zoom)) {
    if (z) e.style.zoom = z;
    else z = parseFloat(e.style.zoom);
    xConsole.log('xZoom: ' + z + ', ' + xGetComputedStyle(e, 'zoom'));
  }
  if (isNaN(z)) z = 1.0; // ???
  return z;
}

xAddEventListener(window, 'load',
  function() {
    xthf[0] = new xTableHeaderFixed('list view', window);
  }, false
);

// bảng thu nhập
xAddEventListener(window, 'load',
  function() {
    xthf[1] = new xTableHeaderFixed('extramoney_tbl', window);
  }, false
);

// bảng tính lương
xAddEventListener(window, 'load',
  function() {
    xthf[1] = new xTableHeaderFixed('employee_salary_tbl', window);
  }, false
);
