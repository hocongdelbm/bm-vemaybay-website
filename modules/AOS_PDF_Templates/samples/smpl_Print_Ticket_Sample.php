<?php

require_once __DIR__ . '/../TemplateSampleService.php';

class smpl_Print_Ticket_Sample
{
    public function getType()
    {
        return 'EC_Flight_Bookings';
    }
        
    public function getBody()
    {
        global $locale;
        return '<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-14" width="100%">
        <tbody>
            <tr>
                <td>
                    <table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" style="color: #000000; width: 900px; " width="900">
                        <tbody>
                            <tr>
                                <td class="column column-1" style="font-weight: 400; text-align: left; vertical-align: top; border: 0px; " width="100%">
                                    <table border="0" cellpadding="0" cellspacing="0" class="text_block block-1" style="word-break: break-word; " width="100%">
                                        <tbody><tr>
                                            <td class="pad" style=" padding: 10px 10px 10px 25px;">
                                                <div style="font-family: sans-serif">
                                                    <div class="" style=" font-size: 12px; font-family:Arial, Helvetica, sans-serif; color: #232323; line-height: 1.2; ">
                                                        <p style=" margin: 0; font-size: 13px; line-height: 20px; ">
                                                            <span style="font-size: 16px;font-weight: 600; text-transform: capitalize;">Thông tin hành khách</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody></table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <table align="center" border="0" cellpadding="0" cellspacing="0" class="row-15" width="100%">
        <tbody>
            <tr>
                <td>
                    <table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content" style="color: #000000; width: 900px; padding: 0 5px 10px;font-size: 14px;" width="900">
                        <thead>
                            <tr>
                                <th width="35%" align="left" style="font-weight:bold;border:1px solid #ccc; padding: 10px 7px;text-align:center;">TÊN HÀNH KHÁCH</th>
                                <th width="20%" align="left" style="font-weight:bold;border:1px solid #ccc; padding: 10px 7px;text-align:center;">MÃ ĐẶT CHỖ</th>
                                <th width="45%" align="left" style="font-weight:bold;border:1px solid #ccc; padding: 10px 7px;text-align:center;">HÀNH LÝ KÝ GỬI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td align="left" style="border:1px solid #ccc; padding: 10px 7px;">NGUYEN HAI HUNG</td>
                                <td align="center" style="border:1px solid #ccc; padding: 10px 7px;"></td>
                                <td align="left" style="border:1px solid #ccc; padding: 10px 7px;">Thêm 20kg ký gửi (Lượt đi) - Thêm 30kg ký gửi (Lượt về)</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <table align="center" border="0" cellpadding="0" cellspacing="0" class="row-14" width="100%">
        <tbody>
            <tr>
                <td>
                    <table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" style="color: #000000; width: 900px; " width="900">
                        <tbody>
                            <tr>
                                <td class="column column-1" style="font-weight: 400; text-align: left; vertical-align: top; border: 0px; " width="100%">
                                    <table border="0" cellpadding="0" cellspacing="0" class="text_block block-1" style="word-break: break-word; " width="100%">
                                        <tbody><tr>
                                            <td class="pad" style=" padding:10px 10px 10px 25px;">
                                                <div style="font-family: sans-serif">
                                                    <div class="" style=" font-size: 12px; font-family:Arial, Helvetica, sans-serif; color: #232323; line-height: 1.2; ">
                                                        <p style=" margin: 0; font-size: 13px; line-height: 20px; ">
                                                            <span style="font-size: 16px;font-weight: 600; text-transform: capitalize;">Thông tin hành trình</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody></table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <table align="center" border="0" cellpadding="0" cellspacing="0" class="row-15" width="100%">
        <tbody>
            <tr>
                <td>
                    <table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content" style="color: #000000; width: 900px; padding: 0 5px 10px; font-size: 14px;" width="900">
                        <thead>
                            <tr>
                                <th width="25%" class="text-center" style="font-weight:bold;border:1px solid #ccc; padding: 10px 7px;">Ngày giờ bay</th>
                                <th width="20%" class="text-center" style="font-weight:bold;border:1px solid #ccc; padding: 10px 7px;">Hãng bay</th>
                                <th width="15%" class="text-center" style="font-weight:bold;border:1px solid #ccc; padding: 10px 7px;">Mã chuyến</th>
                                <th width="20%" class="text-center" style="font-weight:bold;border:1px solid #ccc; padding: 10px 7px;">Điểm đi</th>
                                <th width="20%" class="text-center" style="font-weight:bold;border:1px solid #ccc; padding: 10px 7px;">Điểm đến</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td align="left" style="border:1px solid #ccc; padding: 10px 7px;">
                                    <span style="font-weight:bold;">12/08/2023</span>&nbsp;<span style="font-weight:bold;">14:10</span> - <span style="font-weight:bold;">15:30</span>
                                </td>
                                <td align="left" style="border:1px solid #ccc; padding: 10px 7px;">VietJet Air</td>
                                <td align="center" style="border:1px solid #ccc; padding: 10px 7px;">VJ644</td>
                                <td align="left" style="border:1px solid #ccc; padding: 10px 7px;">Ho Chi Minh (SGN)</td>
                                <td align="left" style="border:1px solid #ccc; padding: 10px 7px;">Da Nang (DAD)</td>
                            </tr>
                            <tr>
                                <td align="left" style="border:1px solid #ccc; padding: 10px 7px;">
                                    <span style="font-weight:bold;">15/08/2023</span>&nbsp;<span style="font-weight:bold;">14:45</span> - <span style="font-weight:bold;">16:10</span>
                                </td>
                                <td align="left" style="border:1px solid #ccc; padding: 10px 7px;">VietJet Air</td>
                                <td align="center" style="border:1px solid #ccc; padding: 10px 7px;">VJ637</td>
                                <td align="left" style="border:1px solid #ccc; padding: 10px 7px;">Da Nang (DAD)</td>
                                <td align="left" style="border:1px solid #ccc; padding: 10px 7px;">Ho Chi Minh (SGN)</td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>';
    }

    public function getHeader()
    {
        return '<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-1" width="100%">
        <tbody>
            <tr>
                <td>
                    <table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content" style=color: #000000; width: 900px;" width="900">
                        <tbody>
                            <tr>
                                <td class="column column-1" style="font-weight: 400;text-align: left;padding-bottom: 5px;padding-top: 5px;vertical-align: middle;border: 0px;" width="35%">
                                    <table border="0" cellpadding="0" cellspacing="0" class="image_block block-1" style="" width="100%">
                                        <tbody><tr>
                                            <td class="pad" style="padding-left: 25px;width: 100%;padding-right: 0px;">
                                                <div align="left" class="alignment" style="line-height: 10px">
                                                    <img alt="tcb" src="https://bm.vemaybay.website/include/images/mail/bm-tcb-logo.png" style="display: block;height: auto; border: 0; width: 69px; max-width: 100%;" title="tcb" width="69">
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody></table>
                                </td>
                                <td class="column column-2" style="font-weight: 400; text-align: left; padding-bottom: 5px; padding-top: 5px; vertical-align: middle; border: 0px; " width="30%">
                                    <table border="0" cellpadding="10" cellspacing="0" class="text_block block-1" style=word-break: break-word; " width="100%">
                                        <tbody><tr>
                                            <td class="pad">
                                                <div style="font-family: sans-serif">
                                                    <div class="" style=" font-size: 12px; font-family:Arial, Helvetica, sans-serif; color: #000; line-height: 1.5; font-weight: 600;">
                                                        <p style=" margin: 0; font-size: 14px; text-align: center; ">
                                                            <span style="font-size: 20px;font-weight:bold; text-transform:uppercase;">TÌM CHUYẾN BAY</span><br>
                                                            <span style="font-size: 16px;font-weight:bold; text-transform:uppercase;">TN23080910</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody></table>
                                </td>
                                <td class="column column-3" style="font-weight: 400; text-align: left; padding-bottom: 5px; padding-top: 5px; vertical-align: middle; border: 0px; " width="35%">
                                    <table border="0" cellpadding="10" cellspacing="0" class="text_block block-1" style=word-break: break-word; " width="100%">
                                        <tbody><tr>
                                            <td class="pad" style="padding-bottom: 5px;">
                                                <div style="font-family: sans-serif">
                                                    <div class="" style="font-size: 14px; font-family:Arial, Helvetica, sans-serif; line-height: 1.5; ">
                                                        <p style="margin: 0; text-align: right; ">
                                                            <span style="color: #000"><strong>Công ty TNHH TM Travelpass</strong></span>
                                                        </p>
                                                        <p style="margin: 0; text-align: right; ">
                                                            <span style="color: #000"><strong>Hỗ trợ: 1900 63 6060</strong></span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody></table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>';
    }

    public function getFooter()
    {
        global $locale;
        return '<table align="center" border="0" cellpadding="0" cellspacing="0" class="row-36" width="100%">
        <tbody>
            <tr>
                <td>
                    <table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" style="color: #000; width: 900px; " width="900">
                        <tbody>
                            <tr>
                                <td class="column column-1" style="font-weight: 400; color:#000; text-align: left; vertical-align: top; border: 0px; " width="100%">
                                    <table border="0" cellpadding="0" cellspacing="0" class="image_block block-2" style="" width="100%">
                                        <tbody><tr>
                                            <td class="pad" style=" width: 100%; padding-right: 0px; padding-left: 0px; padding-top: 15px; ">
                                                <span style="font-size: 16px; font-style: italic; font-weight: 600;">Lưu ý:</span>
                                                <ul style="line-height: 25px; margin: 0px;list-style-type: square;padding: 10px; color: #000;">
                                                    <li>
                                                        Quý khách cần kiểm tra thông tin kỹ càng trước khi ra sân bay. Họ tên hành khách, hành lý, ngày giờ bay, điểm đi - đến.
                                                    </li>
                                                    <li>
                                                        Vui lòng có mặt tại sân bay trước giờ khởi hành <b>120 phút</b> (Lễ, Tết trước 150 - 180 phút), tránh việc đi trễ sẽ mất vé. Xin ghi nhớ: Đến sân bay cần phải làm thủ tục tại quầy.
                                                    </li>
                                                    <li>
                                                        <b>Hành khách từ 14 tuổi trở lên phải có giấy tờ tùy thân: CCCD, hộ chiếu còn hạn sử dụng.</b> Trường hợp không có các giấy tờ trên, đi bằng giấy xác nhận nhân thân, có dấu giáp lai của cơ quan Công An phường, xã. Hành khách dưới 14 tuổi đi bằng giấy khai sinh bản chính.
                                                    </li>
                                                    <li>
                                                        Giấy tờ tuỳ thân khi ra sân bay phải là bản chính.
                                                    </li>
                                                    <li>
                                                        <i>Phải kiểm tra kỹ lưỡng: Tên hành khách, ngày giờ bay, điểm đi, điểm đến, hành lý ký gởi, số điện thoại đăng ký khi mua vé.</i> Nên rà soát tới lui nhiều lần!
                                                    </li>
                                                    <li>
                                                        Luôn mở điện thoại để nhận thông tin từ hãng hoặc nhân viên hỗ trợ. <b>Quý khách cần đảm bảo thông tin chính xác</b> so với giấy tờ tùy thân. Sử dụng số điện thoại chính và gmail để liên lạc.
                                                    </li>
                                                    <li>
                                                        <i>Vé khuyến mãi không hoàn đổi! Xin lưu ý điều này.</i> Vé máy bay giá rẻ khuyến mãi thường không hoàn đổi.
                                                    </li>
                                                    <li>
                                                        Mọi sai sót về sau đều dẫn đến mất vé hoặc phí đổi (điều chỉnh booking).
                                                    </li>
                                                    <li>
                                                        <b>Vé khứ hồi</b> nếu quý khách không bay chặng đi <b>phải thông báo với chúng tôi trước ngày bay đầu tiên</b> để có thể sử dụng chặng về. Trong mọi trường hợp, việc thay đổi hành trình đều phải thông báo trước khi bắt đầu. Tốt nhất là sử dụng điện thoại kèm email.
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                    </tbody></table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <table align="center" border="0" cellpadding="0" cellspacing="0" class="row-36" width="100%">
        <tbody>
            <tr>
                <td>
                    <table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" style="color: #000000; width: 900px; " width="900">
                        <tbody>
                            <tr>
                                <td class="column column-1" style="font-weight: 400; text-align: left; padding-bottom: 5px; padding-top: 10px; vertical-align: top; border: 0px; " width="100%">
                                    <table border="0" cellpadding="0" cellspacing="0" class="image_block block-2" style="" width="100%">
                                        <tbody><tr>
                                            <td class="pad" style=" padding-bottom: 5px; width: 100%; padding-right: 0px; padding-left: 0px; ">
                                                <div align="center" class="alignment" style="line-height: 10px">
                                                    <img alt="Công ty TNHH TM Travelpass" src="https://bm.vemaybay.website/include/images/mail/bm-tcb-logo.png" style="display: block;height: auto;border: 0;width: 83px;max-width: 100%;" title="Công ty TNHH TM Travelpass" width="83">
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody></table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <table align="center" border="0" cellpadding="0" cellspacing="0" class="row-36" width="100%">
        <tbody>
            <tr>
                <td>
                    <table align="center" border="0" cellpadding="0" cellspacing="0" class="row-content stack" style="color: #858585; width: 900px; " width="900">
                        <tbody>
                            <tr>
                                <td class="column column-1" style="font-weight: 400; text-align: left; vertical-align: top; border: 0px; " width="100%">
                                    <table border="0" cellpadding="0" cellspacing="0" class="image_block block-2" style="" width="100%">
                                        <tbody><tr>
                                            <td class="pad" style=" padding-bottom: 15px; width: 100%; padding-right: 0px; padding-left: 0px; ">
                                                <div align="center" class="alignment" style="line-height: 10px">
                                                    <p style=" margin: 0; font-size: 12px; line-height: 20px;">
                                                        Công ty TNHH TM Travelpass, 119 Nguyễn Thượng Hiền, P.6, Q. Bình Thạnh
                                                    </p>
                                                    <p style=" margin: 0; font-size: 12px; line-height: 20px;">
                                                        MST: 0317103646 &nbsp;&nbsp;|&nbsp;&nbsp;Tel: 1900 63 6060 - 0919 330 802 - 0947 954 666&nbsp;&nbsp;|&nbsp;&nbsp;Email: info@timchuyenbay.com
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody></table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>';
    }
}
