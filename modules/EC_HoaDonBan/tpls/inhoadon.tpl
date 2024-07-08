{literal}
<style>
	#content{margin:0 !important; padding:0 !important;}
	.wrap_all{
		border:1px solid #000000;
		padding:1px;
		height:930px;
		width:650px;
		font-family:"Times New Roman", Times, serif;
		font-size:10pt;
		color:#000000;
	}
	.tbl_inside{
		font-family:"Times New Roman", Times, serif;
		font-size:10pt;
		width:93%;
		margin:0 auto;
		color:#000000;
	}
	.tbl_inside tr:not(:first-child){
		line-height:22px;
	}
	.tbl_details{
		font-family:"Times New Roman", Times, serif;
		font-size:10pt;
		border-collapse:collapse;
		width:596px;
		line-height:16px !important;
		color:#000000;
	}
	.tbl_details td{
		border:1px solid #000000;
		padding:2px;
	}
	.tbl_signatures{
		width:100%;
		font-family:"Times New Roman", Times, serif;
		font-size:10pt;
		color:#000000;
		margin-top:8px;
	}
	.invoice_footer{
		font-family:"Times New Roman", Times, serif;
		font-size:9pt;
		font-style:italic;
		margin:0 auto;
		text-align:center;
		color:#000000;
		margin-top:4px;
		line-height:13px;
	}
</style>
{/literal}

	<div style="height:930px; width:650px; page-break-after:always;">
	<div class="wrap_all">
        <table class="tbl_inside" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td width="25%"><img src="custom/themes/default/images/vmb_invoice_logo.jpg" border="0" style="width:140px; height:100px;" /></td>
                <td width="50%" style="text-align:center;">
                    <label style="font-weight:bold; font-size:15pt;">HOÁ ĐƠN GIÁ TRỊ GIA TĂNG</label>
                    <br /><label style="font-style:italic;">Liên 1: Lưu</label>
                    <br /><label style="font-style:italic;">{$INV_DATE}</label>
                </td>
                <td width="25%" style="padding-left:15px;">
                    Mẫu số: 01GTKT2/001
                    <br />Ký hiệu: {$INV_CODE}
                    <br />Số: <label style="font-weight:bold;">{$INV_NUM}</label>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="border-top:1px solid #000;">
                    Đơn vị bán hàng: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {$COM_NAME}
                    <br />Mã số thuế: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <label style="font-weight:bold;">{$COM_TAXCODE}</label>
                    <br />Địa chỉ: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {$COM_ADDRESS}
                    <br />Điện thoại: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {$COM_TEL} - 0932 802 802 - {$COM_MOBILE}
                    <br />Website: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {$COM_WEBSITE} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Mail: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {$COM_EMAIL}
                </td>
            </tr>
            <tr>
                <td colspan="3" style="border-top:1px solid #000;">
                    Họ tên người mua hàng: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {if $CONTACT==''}..................................................................................................................{else} {$CONTACT} {/if}
                    <br />Tên đơn vị: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {if $ACC_NAME==''}........................................................................................................................................{else} {$ACC_NAME} {/if}
                    <br />Mã số thuế: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {if $ACC_TAXCODE==''} ................................... {else} {$ACC_TAXCODE} {/if}
                    <br />Địa chỉ: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {if $ACC_ADDRESS==''} .............................................................................................................................................{else} {$ACC_ADDRESS} {/if}
                    <br />Hình thức thanh toán: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {if $PAYMENT_TYPE==''} ...................................{else} {$PAYMENT_TYPE} {/if}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Số tài khoản: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ...................................
                    <br />
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <table class="tbl_details" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="text-align:center; font-weight:bold; width:30px;">STT</td>
                            <td style="text-align:center; font-weight:bold; width:275px;">Tên hàng hóa, dịch vụ</td>
                            <td style="text-align:center; font-weight:bold; width:72px;">Đơn vị tính</td>
                            <td style="text-align:center; font-weight:bold; width:59px;">Số lượng</td>
                            <td style="text-align:center; font-weight:bold; width:71px;">Đơn giá</td>
                            <td style="text-align:center; font-weight:bold; width:89px;">Thành tiền</td>
                        </tr>
                        <tr>
                            <td style="text-align:center; font-weight:bold; line-height:12px !important;">A</td>
                            <td style="text-align:center; font-weight:bold; line-height:12px !important;">B</td>
                            <td style="text-align:center; font-weight:bold; line-height:12px !important;">C</td>
                            <td style="text-align:center; font-weight:bold; line-height:12px !important;">1</td>
                            <td style="text-align:center; font-weight:bold; line-height:12px !important;">2</td>
                            <td style="text-align:center; font-weight:bold; line-height:12px !important;">3 = 1 x 2</td>
                        </tr>
                        
                        {$DATA}
                        
                        <tr>
                            <td style="height:{$NO_DATA_HEIGHT}px; position:relative;">
                                <div style="position:absolute; width:593px; height:{$NO_DATA_HEIGHT}px; top:0px;">
                                	<svg style="width:100%; height:100%;">
                                        <line x1="0" y1="0" x2="100%" y2="100%" style="stroke:rgb(0,0,0);stroke-width:1" />
                                    </svg>
                                </div>
                    		</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        
                        
                        <tr>
                            <td colspan="2">&nbsp;</td>
                            <td colspan="4" align="right"><span style="float:left;">Cộng tiền hàng: </span><label style="font-weight:bold;">{$TOTAL_AMT}</label></td>
                        </tr>
                        <tr>
                            <td colspan="2">Thuế suất thuế GT &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <label style="font-weight:bold;">{$TAX_PERCENT}%</label></td>
                            <td colspan="4" align="right"><span style="float:left;">Tiền thuế GTGT: </span><label style="font-weight:bold;">{$TOTAL_TAX_AMT}</label></td>
                        </tr>
                        <tr>
                            <td colspan="2">&nbsp;</td>
                            <td colspan="4" align="right"><span style="float:left;">Tổng cộng tiền thanh toán:</span><label style="font-weight:bold;">{$TOTAL_PAY_AMT}</label></td>
                        </tr>
                        <tr>
                            <td colspan="6">Số tiền viết bằng chữ: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <label style="font-weight:bold; font-style:italic;">{$TOTAL_PAY_AMT_IN_WORDS}.</label></td>
                        </tr>
                        
                    </table><!-- END TABLE DETAILS -->
                    
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <table class="tbl_signatures" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td width="30%" style="text-align:center;" valign="top">
                                <p style="font-weight:bold;">Người mua hàng</p>
                                <p style="font-style:italic;">(Ký, ghi rõ họ, tên)</p>
                                <br />
                                <div style="font-family:Arial, Helvetica, sans-serif; font-size:10pt; padding:5px; border:1px solid #000000; margin:0 auto; width:177px; height:20px;">BÁN HÀNG QUA ĐIỆN THOẠI</div>
                            </td>
                            <td width="40%">&nbsp;</td>
                            <td width="30%" style="text-align:center;" valign="top">
                                <p style="font-weight:bold;">Người bán hàng</p>
                                <p style="font-style:italic;">(Ký, ghi rõ họ, tên)</p>
                            </td>
                        </tr>
                    </table><!-- END TABLE SIGNATURES -->
                </td>
            </tr>
        </table>
		</div><!-- END WRAP ALL -->
    
    	<div class="invoice_footer">
        	(Cần kiểm tra, đối chiếu khi lập, giao, nhận hóa đơn)
			<br />
			In tại {$COM_NAME} - MST: {$COM_TAXCODE}
			<br />In bởi Chương trình MISA SME.NET 2012
        </div>
        
    </div>
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    <div style="height:930px; width:650px;">
	<div class="wrap_all">
        <table class="tbl_inside" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td width="25%"><img src="custom/themes/default/images/vmb_invoice_logo.jpg" border="0" style="width:140px; height:100px;" /></td>
                <td width="50%" style="text-align:center;">
                    <label style="font-weight:bold; font-size:15pt;">HOÁ ĐƠN GIÁ TRỊ GIA TĂNG</label>
                    <br /><label style="font-style:italic;">Liên 2: Giao cho người mua</label>
                    <br /><label style="font-style:italic;">{$INV_DATE}</label>
                </td>
                <td width="25%" style="padding-left:15px;">
                    Mẫu số: 01GTKT2/001
                    <br />Ký hiệu: {$INV_CODE}
                    <br />Số: <label style="font-weight:bold;">{$INV_NUM}</label>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="border-top:1px solid #000;">
                    Đơn vị bán hàng: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {$COM_NAME}
                    <br />Mã số thuế: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <label style="font-weight:bold;">{$COM_TAXCODE}</label>
                    <br />Địa chỉ: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {$COM_ADDRESS}
                    <br />Điện thoại: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {$COM_TEL} - 0932 802 802 - {$COM_MOBILE}
                    <br />Website: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {$COM_WEBSITE} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Mail: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {$COM_EMAIL}
                </td>
            </tr>
            <tr>
                <td colspan="3" style="border-top:1px solid #000;">
                    Họ tên người mua hàng: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {if $CONTACT==''}..................................................................................................................{else} {$CONTACT} {/if}
                    <br />Tên đơn vị: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {if $ACC_NAME==''}........................................................................................................................................{else} {$ACC_NAME} {/if}
                    <br />Mã số thuế: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {if $ACC_TAXCODE==''} ................................... {else} {$ACC_TAXCODE} {/if}
                    <br />Địa chỉ: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {if $ACC_ADDRESS==''} .............................................................................................................................................{else} {$ACC_ADDRESS} {/if}
                    <br />Hình thức thanh toán: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {if $PAYMENT_TYPE==''} ...................................{else} {$PAYMENT_TYPE} {/if}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Số tài khoản: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ...................................
                    <br />
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <table class="tbl_details" border="0" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="text-align:center; font-weight:bold; width:30px;">STT</td>
                            <td style="text-align:center; font-weight:bold; width:275px;">Tên hàng hóa, dịch vụ</td>
                            <td style="text-align:center; font-weight:bold; width:72px;">Đơn vị tính</td>
                            <td style="text-align:center; font-weight:bold; width:59px;">Số lượng</td>
                            <td style="text-align:center; font-weight:bold; width:71px;">Đơn giá</td>
                            <td style="text-align:center; font-weight:bold; width:89px;">Thành tiền</td>
                        </tr>
                        <tr>
                            <td style="text-align:center; font-weight:bold; line-height:12px !important;">A</td>
                            <td style="text-align:center; font-weight:bold; line-height:12px !important;">B</td>
                            <td style="text-align:center; font-weight:bold; line-height:12px !important;">C</td>
                            <td style="text-align:center; font-weight:bold; line-height:12px !important;">1</td>
                            <td style="text-align:center; font-weight:bold; line-height:12px !important;">2</td>
                            <td style="text-align:center; font-weight:bold; line-height:12px !important;">3 = 1 x 2</td>
                        </tr>
                        
                        {$DATA}
                        
                        <tr>
                            <td style="height:{$NO_DATA_HEIGHT}px; position:relative;">
                                <div style="position:absolute; width:593px; height:{$NO_DATA_HEIGHT}px; top:0px;">
                                	<svg style="width:100%; height:100%;">
                                        <line x1="0" y1="0" x2="100%" y2="100%" style="stroke:rgb(0,0,0);stroke-width:1" />
                                    </svg>
                                </div>
                    		</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        
                        
                        <tr>
                            <td colspan="2">&nbsp;</td>
                            <td colspan="4" align="right"><span style="float:left;">Cộng tiền hàng: </span><label style="font-weight:bold;">{$TOTAL_AMT}</label></td>
                        </tr>
                        <tr>
                            <td colspan="2">Thuế suất thuế GT &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <label style="font-weight:bold;">{$TAX_PERCENT}%</label></td>
                            <td colspan="4" align="right"><span style="float:left;">Tiền thuế GTGT: </span><label style="font-weight:bold;">{$TOTAL_TAX_AMT}</label></td>
                        </tr>
                        <tr>
                            <td colspan="2">&nbsp;</td>
                            <td colspan="4" align="right"><span style="float:left;">Tổng cộng tiền thanh toán:</span><label style="font-weight:bold;">{$TOTAL_PAY_AMT}</label></td>
                        </tr>
                        <tr>
                            <td colspan="6">Số tiền viết bằng chữ: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <label style="font-weight:bold; font-style:italic;">{$TOTAL_PAY_AMT_IN_WORDS}.</label></td>
                        </tr>
                        
                    </table><!-- END TABLE DETAILS -->
                    
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <table class="tbl_signatures" cellpadding="0" cellspacing="0" border="0">
                        <tr>
                            <td width="30%" style="text-align:center;" valign="top">
                                <p style="font-weight:bold;">Người mua hàng</p>
                                <p style="font-style:italic;">(Ký, ghi rõ họ, tên)</p>
                                <br />
                                <div style="font-family:Arial, Helvetica, sans-serif; font-size:10pt; padding:5px; border:1px solid #000000; margin:0 auto; width:177px; height:20px;">BÁN HÀNG QUA ĐIỆN THOẠI</div>
                            </td>
                            <td width="40%">&nbsp;</td>
                            <td width="30%" style="text-align:center;" valign="top">
                                <p style="font-weight:bold;">Người bán hàng</p>
                                <p style="font-style:italic;">(Ký, ghi rõ họ, tên)</p>
                            </td>
                        </tr>
                    </table><!-- END TABLE SIGNATURES -->
                </td>
            </tr>
        </table>
		</div><!-- END WRAP ALL -->
    
    	<div class="invoice_footer">
        	(Cần kiểm tra, đối chiếu khi lập, giao, nhận hóa đơn)
			<br />
			In tại {$COM_NAME} - MST: {$COM_TAXCODE}
			<br />In bởi Chương trình MISA SME.NET 2012
        </div>
        
    </div>