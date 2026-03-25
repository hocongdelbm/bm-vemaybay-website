<div style="visibility: hidden" id="{{ $source }}_popup_div"></div>
<script type="text/javascript">
  function show_{{$source}}(event)
  {literal}
  {

  		var callback =	{
  			success: function(data) {
  				eval('result = ' + data.responseText);
  				if(typeof result != 'Undefined') {
  				    names = new Array();
  				    output = '';
  				    count = 0;
                      for(var i in result) {
                          if(count == 0) {
  	                        detail = 'Showing first result <p>';
  	                        for(var field in result[i]) {
  	                            detail += '<b>' + field + ':</b> ' + result[i][field] + '<br>';
  	                        }
  	                        output += detail + '<p>';
                          }
                          count++;
                      }
                  {/literal}
  					cd = new CompanyDetailsDialog("{{$source}}_popup_div", output, event.clientX, event.clientY);
  			    {literal}
  					cd.setHeader("Found " + count + (count == 1 ? " result" : " results"));
  					cd.display();
  				} else {
  				    alert("Unable to retrieve information for record");
  				}
  			},

  			failure: function(data) {

  			}
  		}

  {/literal}

  url = 'index.php?module=Connectors&action=DefaultSoapPopup&source_id={{$source}}&module_id={{$module}}&record_id={$fields.id.value}&mapping={{$mapping}}';
  var cObj = YAHOO.util.Connect.asyncRequest('POST', url, callback);

  {literal}
  }
  {/literal}
</script>
