<?php
function generateXLSTemplate($data, $from_date, $to_date) {
$xls = "<html xmlns:o=\"urn:schemas-microsoft-com:office:office\"
xmlns:x=\"urn:schemas-microsoft-com:office:excel\"
xmlns=\"http://www.w3.org/TR/REC-html40\">

<head>
<meta http-equiv=Content-Type content=\"text/html; charset=windows-1252\">
<meta name=ProgId content=Excel.Sheet>
<meta name=Generator content=\"Microsoft Excel 12\">
<link rel=File-List href=\"baocaobanhang_files/filelist.xml\">
<style id=\"baocaobanhang_10306_Styles\">
<!--table
	{mso-displayed-decimal-separator:\"\.\";
	mso-displayed-thousand-separator:\"\,\";}
.xl6310306
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:general;
	vertical-align:middle;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl6410306
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:general;
	vertical-align:middle;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl6510306
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:\"\@\";
	text-align:center;
	vertical-align:middle;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl6610306
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:italic;
	text-decoration:none;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:general;
	vertical-align:middle;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl6710306
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:\"\#\,\#\#0_\)\;\\\(\#\,\#\#0\\\)\";
	text-align:general;
	vertical-align:middle;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl6810306
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:400;
	font-style:normal;
	text-decoration:none;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:\"\#\,\#\#0_\)\;\\\(\#\,\#\#0\\\)\";
	text-align:center;
	vertical-align:middle;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl6910306
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:red;
	font-size:20.0pt;
	font-weight:700;
	font-style:normal;
	text-decoration:none;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:General;
	text-align:general;
	vertical-align:middle;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:nowrap;}
.xl7010306
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:700;
	font-style:normal;
	text-decoration:none;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:\"\@\";
	text-align:center;
	vertical-align:middle;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl7110306
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:red;
	font-size:10.0pt;
	font-weight:700;
	font-style:normal;
	text-decoration:none;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:\"\@\";
	text-align:center;
	vertical-align:middle;
	border:.5pt solid windowtext;
	mso-background-source:auto;
	mso-pattern:auto;
	white-space:normal;}
.xl7210306
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:700;
	font-style:normal;
	text-decoration:none;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:\"\@\";
	text-align:center;
	vertical-align:middle;
	border:.5pt solid windowtext;
	background:#93CDDD;
	mso-pattern:black none;
	white-space:normal;}
.xl7310306
	{padding-top:1px;
	padding-right:1px;
	padding-left:1px;
	mso-ignore:padding;
	color:black;
	font-size:10.0pt;
	font-weight:700;
	font-style:normal;
	text-decoration:none;
	font-family:Arial, sans-serif;
	mso-font-charset:0;
	mso-number-format:\"\@\";
	text-align:center;
	vertical-align:middle;
	border:.5pt solid windowtext;
	background:#FAC090;
	mso-pattern:black none;
	white-space:normal;}
-->
</style>
</head>

<body>
<!--[if !excel]>&nbsp;&nbsp;<![endif]-->
<!--The following information was generated by Microsoft Office Excel's Publish
as Web Page wizard.-->
<!--If the same item is republished from Excel, all information between the DIV
tags will be replaced.-->
<!----------------------------->
<!--START OF OUTPUT FROM EXCEL PUBLISH AS WEB PAGE WIZARD -->
<!----------------------------->

<div id=\"baocaobanhang_10306\" align=center x:publishsource=\"Excel\">

<table border=0 cellpadding=0 cellspacing=0 width=2398 class=xl6310306 style='border-collapse:collapse;table-layout:fixed;width:1800pt'>

 <col class=xl6310306 width=44 style='mso-width-source:userset; mso-width-alt:1609; width:33pt'>
 <col class=xl6310306 width=109 span=3 style='mso-width-source:userset; mso-width-alt:3986;width:82pt'>
 <col class=xl6310306 width=112 style='mso-width-source:userset;mso-width-alt:4096;width:84pt'>
 <col class=xl6310306 width=74 style='mso-width-source:userset;mso-width-alt:2706;width:56pt'>
 <col class=xl6310306 width=120 style='mso-width-source:userset;mso-width-alt:4388;width:90pt'>
 <col class=xl6310306 width=100 span=6 style='mso-width-source:userset;mso-width-alt:3657;width:75pt'>
 <col class=xl6310306 width=140 style='mso-width-source:userset;mso-width-alt:5120;width:105pt'>
 <col class=xl6310306 width=120 style='mso-width-source:userset;mso-width-alt:4388;width:90pt'>
 <col class=xl6310306 width=100 span=6 style='mso-width-source:userset;mso-width-alt:3657;width:75pt'>
 <col class=xl6310306 width=140 style='mso-width-source:userset;mso-width-alt:5120;width:105pt'>
 <col class=xl6310306 width=121 style='mso-width-source:userset;mso-width-alt:4425;width:91pt'>
 
 <tr height=35 style='height:26.25pt'>
  <td height=35 class=xl6910306 colspan=5 width=371 style='height:26.25pt;width:279pt'>BÁO CÁO BÁN HÀNG</td>
  <td class=xl6310306 width=112 style='width:84pt'></td>
  <td class=xl6310306 width=74 style='width:56pt'></td>
  <td class=xl6310306 width=120 style='width:90pt'></td>
  <td class=xl6310306 width=100 style='width:75pt'></td>
  <td class=xl6310306 width=100 style='width:75pt'></td>
  <td class=xl6310306 width=100 style='width:75pt'></td>
  <td class=xl6310306 width=100 style='width:75pt'></td>
  <td class=xl6310306 width=100 style='width:75pt'></td>
  <td class=xl6310306 width=100 style='width:75pt'></td>
  <td class=xl6310306 width=140 style='width:105pt'></td>
  <td class=xl6310306 width=120 style='width:90pt'></td>
  <td class=xl6310306 width=100 style='width:75pt'></td>
  <td class=xl6310306 width=100 style='width:75pt'></td>
  <td class=xl6310306 width=100 style='width:75pt'></td>
  <td class=xl6310306 width=100 style='width:75pt'></td>
  <td class=xl6310306 width=100 style='width:75pt'></td>
  <td class=xl6310306 width=100 style='width:75pt'></td>
  <td class=xl6310306 width=140 style='width:105pt'></td>
  <td class=xl6310306 width=121 style='width:91pt'></td>
 </tr>
 
 <tr height=19 style='height:14.25pt'>
  <td height=19 class=xl6610306 colspan=4 style='height:14.25pt'>
  	Từ ngày <span style='mso-spacerun:yes'>  </span>".$from_date."<span style='mso-spacerun:yes'>  </span>
	Đến ngày <span style='mso-spacerun:yes'>  </span>".$to_date."
  </td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
 </tr>
 
 <tr height=19 style='height:14.25pt'>
  <td height=19 class=xl6310306 style='height:14.25pt'></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
  <td class=xl6310306></td>
 </tr>
 
 <tr height=30 style='mso-height-source:userset;height:22.5pt'>
  <td height=30 class=xl7010306 width=44 style='height:22.5pt;width:33pt'>&nbsp;</td>
  <td class=xl7010306 width=109 style='border-left:none;width:82pt'>&nbsp;</td>
  <td class=xl7010306 width=109 style='border-left:none;width:82pt'>&nbsp;</td>
  <td class=xl7010306 width=109 style='border-left:none;width:82pt'>&nbsp;</td>
  <td class=xl7010306 width=112 style='border-left:none;width:84pt'>&nbsp;</td>
  <td class=xl7010306 width=74 style='border-left:none;width:56pt'>&nbsp;</td>
  <td colspan=9 class=xl7210306 width=860 style='border-left:none;width:645pt'>Doanh thu bao gồm thuế</td>
  <td colspan=9 class=xl7310306 width=860 style='border-left:none;width:645pt'>Chi phí</td>
  <td rowspan=3 class=xl7210306 width=121 style='width:91pt'>Lãi gộp chưa VAT</td>
 </tr>
 
 <tr height=40 style='mso-height-source:userset;height:30.0pt'>
  <td height=40 class=xl7010306 width=44 style='height:30.0pt;border-top:none;width:33pt'>STT</td>
  <td class=xl7010306 width=109 style='border-top:none;border-left:none;width:82pt'>Số vé</td>
  <td class=xl7010306 width=109 style='border-top:none;border-left:none;width:82pt'>Hành trình</td>
  <td class=xl7010306 width=109 style='border-top:none;border-left:none;width:82pt'>Booking</td>
  <td class=xl7010306 width=112 style='border-top:none;border-left:none;width:84pt'>Phiếu thu</td>
  <td class=xl7010306 width=74 style='border-top:none;border-left:none;width:56pt'>Số lượng</td>
  <td class=xl7010306 width=120 style='border-top:none;border-left:none;width:90pt'>Đơn giá bán</td>
  <td class=xl7010306 width=100 style='border-top:none;border-left:none;width:75pt'>Thuế VAT</td>
  <td class=xl7010306 width=100 style='border-top:none;border-left:none;width:75pt'>Phí Admin</td>
  <td class=xl7010306 width=100 style='border-top:none;border-left:none;width:75pt'>Phí dịch vụ</td>
  <td class=xl7010306 width=100 style='border-top:none;border-left:none;width:75pt'>Phí sân bay</td>
  <td class=xl7010306 width=100 style='border-top:none;border-left:none;width:75pt'>Phí giao vé</td>
  <td class=xl7010306 width=100 style='border-top:none;border-left:none;width:75pt'>Phí hành lý</td>
  <td class=xl7010306 width=100 style='border-top:none;border-left:none;width:75pt'>Phí hoàn đổi</td>
  <td class=xl7010306 width=140 style='border-top:none;border-left:none;width:105pt'>Thành tiền</td>
  <td class=xl7010306 width=120 style='border-top:none;border-left:none;width:90pt'>Đơn giá cơ bản</td>
  <td class=xl7010306 width=100 style='border-top:none;border-left:none;width:75pt'>Phí sân bay</td>
  <td class=xl7010306 width=100 style='border-top:none;border-left:none;width:75pt'>Phí Admin</td>
  <td class=xl7010306 width=100 style='border-top:none;border-left:none;width:75pt'>Thuế VAT</td>
  <td class=xl7010306 width=100 style='border-top:none;border-left:none;width:75pt'>Phí giao vé</td>
  <td class=xl7010306 width=100 style='border-top:none;border-left:none;width:75pt'>Phí hành lý</td>
  <td class=xl7010306 width=100 style='border-top:none;border-left:none;width:75pt'>Phí hoàn đổi</td>
  <td class=xl7010306 width=100 style='border-top:none;border-left:none;width:75pt'>Chiết khấu</td>
  <td class=xl7010306 width=140 style='border-top:none;border-left:none;width:105pt'>Thành tiền</td>
 </tr>
 
 <tr height=40 style='mso-height-source:userset;height:30.0pt'>
  <td height=40 class=xl7010306 width=44 style='height:30.0pt;border-top:none;width:33pt'>&nbsp;</td>
  <td class=xl7010306 width=109 style='border-top:none;border-left:none;width:82pt'>&nbsp;</td>
  <td class=xl7010306 width=109 style='border-top:none;border-left:none;width:82pt'>&nbsp;</td>
  <td class=xl7010306 width=109 style='border-top:none;border-left:none;width:82pt'>&nbsp;</td>
  <td class=xl7010306 width=112 style='border-top:none;border-left:none;width:84pt'>&nbsp;</td>
  <td class=xl7010306 width=74 style='border-top:none;border-left:none;width:56pt'>&nbsp;</td>
  <td class=xl7010306 width=120 style='border-top:none;border-left:none;width:90pt'>&nbsp;</td>
  <td class=xl7110306 width=100 style='border-top:none;border-left:none;width:75pt'>(Đầu ra)</td>
  <td class=xl7110306 width=100 style='border-top:none;border-left:none;width:75pt'>(Đầu ra)</td>
  <td class=xl7110306 width=100 style='border-top:none;border-left:none;width:75pt'>(Lãi gộp có VAT)</td>
  <td class=xl7110306 width=100 style='border-top:none;border-left:none;width:75pt'>(Hãng HK)</td>
  <td class=xl7110306 width=100 style='border-top:none;border-left:none;width:75pt'>(Phí khác)</td>
  <td class=xl7110306 width=100 style='border-top:none;border-left:none;width:75pt'>(Hành lý)</td>
  <td class=xl7110306 width=100 style='border-top:none;border-left:none;width:75pt'>(Phí hoàn đổi)</td>
  <td class=xl7110306 width=140 style='border-top:none;border-left:none;width:105pt'>(Thực thu)</td>
  <td class=xl7110306 width=120 style='border-top:none;border-left:none;width:90pt'>(Đơn giá)</td>
  <td class=xl7110306 width=100 style='border-top:none;border-left:none;width:75pt'>(Hãng HK)</td>
  <td class=xl7110306 width=100 style='border-top:none;border-left:none;width:75pt'>(Hãng HK)</td>
  <td class=xl7110306 width=100 style='border-top:none;border-left:none;width:75pt'>(Đầu vào)</td>
  <td class=xl7110306 width=100 style='border-top:none;border-left:none;width:75pt'>(Phí khác)</td>
  <td class=xl7110306 width=100 style='border-top:none;border-left:none;width:75pt'>(Hành lý)</td>
  <td class=xl7110306 width=100 style='border-top:none;border-left:none;width:75pt'>(Phí hoàn đổi)</td>
  <td class=xl7110306 width=100 style='border-top:none;border-left:none;width:75pt'>(Resale)</td>
  <td class=xl7110306 width=140 style='border-top:none;border-left:none;width:105pt'>(Thực thu)</td>
 </tr>";
 
 
 $xls .= $data;
 
 
 $xls .= "<![if supportMisalignedColumns]>
 <tr height=0 style='display:none'>
  <td width=44 style='width:33pt'></td>
  <td width=109 style='width:82pt'></td>
  <td width=109 style='width:82pt'></td>
  <td width=109 style='width:82pt'></td>
  <td width=112 style='width:84pt'></td>
  <td width=74 style='width:56pt'></td>
  <td width=120 style='width:90pt'></td>
  <td width=100 style='width:75pt'></td>
  <td width=100 style='width:75pt'></td>
  <td width=100 style='width:75pt'></td>
  <td width=100 style='width:75pt'></td>
  <td width=100 style='width:75pt'></td>
  <td width=100 style='width:75pt'></td>
  <td width=100 style='width:75pt'></td>
  <td width=140 style='width:105pt'></td>
  <td width=120 style='width:90pt'></td>
  <td width=100 style='width:75pt'></td>
  <td width=100 style='width:75pt'></td>
  <td width=100 style='width:75pt'></td>
  <td width=100 style='width:75pt'></td>
  <td width=100 style='width:75pt'></td>
  <td width=100 style='width:75pt'></td>
  <td width=100 style='width:75pt'></td>
  <td width=140 style='width:105pt'></td>
  <td width=121 style='width:91pt'></td>
 </tr>
 <![endif]>
</table>

</div>


<!----------------------------->
<!--END OF OUTPUT FROM EXCEL PUBLISH AS WEB PAGE WIZARD-->
<!----------------------------->
</body>

</html>";
 	
	return $xls;
}
