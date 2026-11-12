<?php

namespace Database\Seeders;

use App\Models\ChatAutoReply;
use Illuminate\Database\Seeder;

class ChatAutoReplySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->replies() as $index => $item) {
            ChatAutoReply::updateOrCreate(
                ['code' => $item['code']],
                array_merge($item, [
                    'priority' => $item['priority'] ?? (100 - $index),
                    'is_active' => true,
                ])
            );
        }

        $this->command->info('Đã nạp '.count($this->replies()).' mẫu trả lời tự động cho chat CSKH.');
    }

    private function replies(): array
    {
        return [
            // ===== Đơn hàng =====
            [
                'code' => 'ORDER_HOW',
                'group_name' => 'order',
                'question' => 'Làm sao để đặt hàng?',
                'keywords' => ['đặt hàng', 'mua hàng', 'cách đặt', 'order', 'làm sao mua'],
                'answer' => "Bạn chọn sản phẩm, bấm Thêm vào giỏ, sau đó vào Giỏ hàng và bấm Thanh toán. "
                    ."Điền địa chỉ nhận hàng, chọn phương thức thanh toán rồi xác nhận. "
                    ."Đơn hàng sẽ được xác nhận trong vòng 15 phút.",
            ],
            [
                'code' => 'ORDER_STATUS',
                'group_name' => 'order',
                'question' => 'Tôi muốn kiểm tra đơn hàng của mình',
                'keywords' => ['kiểm tra đơn', 'xem đơn', 'trạng thái đơn', 'đơn của tôi', 'tra cứu đơn'],
                'answer' => "Bạn vào mục Đơn hàng của tôi để xem toàn bộ đơn và trạng thái hiện tại. "
                    ."Mỗi đơn có mã bắt đầu bằng SF, bạn đọc mã này khi cần liên hệ để được tra cứu nhanh hơn.",
            ],
            [
                'code' => 'ORDER_ETA',
                'group_name' => 'order',
                'question' => 'Đơn hàng của tôi bao giờ được giao?',
                'keywords' => ['bao giờ giao', 'khi nào giao', 'thời gian giao', 'bao lâu giao', 'bao giờ'],
                'answer' => "Đơn nội thành Cần Thơ thường được giao trong 2 giờ. "
                    ."Đơn các tỉnh thành khác từ 1 đến 3 ngày làm việc. "
                    ."Bạn sẽ nhận thông báo khi đơn chuyển sang trạng thái đang giao.",
            ],
            [
                'code' => 'ORDER_CANCEL',
                'group_name' => 'order',
                'question' => 'Tôi muốn hủy đơn hàng',
                'keywords' => ['hủy đơn', 'không mua nữa', 'cancel', 'hủy hàng'],
                'answer' => "Bạn vào Đơn hàng của tôi, chọn đơn cần hủy và bấm Hủy đơn. "
                    ."Chỉ hủy được khi đơn chưa chuyển sang trạng thái đang giao. "
                    ."Nếu đơn đã giao cho đơn vị vận chuyển, vui lòng bấm Gặp nhân viên để được hỗ trợ.",
            ],
            [
                'code' => 'ORDER_ADDRESS',
                'group_name' => 'order',
                'question' => 'Tôi muốn đổi địa chỉ nhận hàng',
                'keywords' => ['đổi địa chỉ', 'sửa địa chỉ', 'thay địa chỉ', 'địa chỉ nhận hàng'],
                'answer' => "Bạn vào Tài khoản, mục Sổ địa chỉ để thêm hoặc sửa địa chỉ. "
                    ."Nếu đơn đã đặt và chưa giao, hãy bấm Gặp nhân viên để được cập nhật trực tiếp.",
            ],
            [
                'code' => 'ORDER_GUEST',
                'group_name' => 'order',
                'question' => 'Có thể đặt hàng mà không cần tài khoản không?',
                'keywords' => ['không cần tài khoản', 'không đăng nhập', 'đặt hàng không tài khoản'],
                'answer' => "Bạn có thể thêm sản phẩm vào giỏ khi chưa đăng nhập, nhưng cần đăng nhập để hoàn tất thanh toán. "
                    ."Đăng nhập giúp bạn theo dõi đơn, tích điểm và lưu địa chỉ cho lần sau.",
            ],
            [
                'code' => 'ORDER_MISSING',
                'group_name' => 'order',
                'question' => 'Đơn hàng của tôi bị thiếu món',
                'keywords' => ['thiếu món', 'thiếu hàng', 'giao thiếu', 'thiếu sản phẩm'],
                'answer' => "Xin lỗi bạn vì sự bất tiện này. Bạn vui lòng gửi mã đơn và ảnh kiện hàng khi nhận, "
                    ."nhân viên sẽ kiểm tra và gửi bù hoặc hoàn tiền phần thiếu trong 24 giờ.",
            ],

            // ===== Vận chuyển =====
            [
                'code' => 'SHIP_FEE',
                'group_name' => 'shipping',
                'question' => 'Phí giao hàng là bao nhiêu?',
                'keywords' => ['phí ship', 'phí giao hàng', 'tiền ship', 'phí vận chuyển', 'ship bao nhiêu', 'ship', 'phí giao'],
                'answer' => "Phí giao hàng mặc định là 30.000 đồng cho mỗi đơn. "
                    ."Đơn từ 1.000.000 đồng được miễn phí giao hàng toàn quốc. "
                    ."Riêng khu vực Ninh Kiều và Cái Răng của Cần Thơ được miễn phí giao hàng.",
            ],
            [
                'code' => 'SHIP_FREE',
                'group_name' => 'shipping',
                'question' => 'Khi nào được miễn phí giao hàng?',
                'keywords' => ['miễn phí ship', 'freeship', 'free ship', 'miễn phí giao hàng'],
                'answer' => "Bạn được miễn phí giao hàng khi tổng đơn từ 1.000.000 đồng, "
                    ."hoặc khi địa chỉ nhận thuộc nội thành Cần Thơ gồm khu vực Ninh Kiều và Cái Răng.",
            ],
            [
                'code' => 'SHIP_PROVINCE',
                'group_name' => 'shipping',
                'question' => 'Có giao hàng ngoài Cần Thơ không?',
                'keywords' => ['giao tỉnh', 'ngoại thành', 'toàn quốc', 'giao đi tỉnh', 'tỉnh khác', 'giao hàng ngoài', 'tỉnh thành', 'giao hàng ra'],
                'answer' => "SFood giao hàng toàn quốc. Đơn nội thành Cần Thơ giao trong 2 giờ, "
                    ."đơn các tỉnh từ 1 đến 3 ngày làm việc.",
            ],
            [
                'code' => 'SHIP_HOLIDAY',
                'group_name' => 'shipping',
                'question' => 'Có giao hàng vào ngày lễ không?',
                'keywords' => ['ngày lễ', 'chủ nhật', 'tết', 'cuối tuần'],
                'answer' => "Cửa hàng vẫn nhận đơn vào ngày lễ và chủ nhật, "
                    ."nhưng thời gian giao có thể chậm hơn dự kiến từ nửa ngày đến một ngày.",
            ],
            [
                'code' => 'SHIP_INSPECT',
                'group_name' => 'shipping',
                'question' => 'Có được kiểm tra hàng trước khi thanh toán không?',
                'keywords' => ['kiểm tra hàng', 'đồng kiểm', 'xem hàng', 'kiểm hàng'],
                'answer' => "Bạn được kiểm tra ngoại quan và số lượng khi nhận hàng. "
                    ."Với hàng tươi sống, bạn nên kiểm tra ngay trước mặt nhân viên giao để được xử lý nhanh nếu có vấn đề.",
            ],

            // ===== Thanh toán =====
            [
                'code' => 'PAY_METHODS',
                'group_name' => 'payment',
                'question' => 'Có những phương thức thanh toán nào?',
                'keywords' => ['thanh toán', 'phương thức', 'trả tiền', 'chuyển khoản', 'quét mã'],
                'answer' => "SFood hỗ trợ hai phương thức: thanh toán khi nhận hàng (COD) và quét mã QR chuyển khoản qua PayOS. "
                    ."Với PayOS, đơn được xác nhận tự động ngay sau khi chuyển khoản thành công.",
            ],
            [
                'code' => 'PAY_PENDING',
                'group_name' => 'payment',
                'question' => 'Đã chuyển khoản mà đơn vẫn chưa xác nhận',
                'keywords' => ['đã chuyển khoản', 'chưa xác nhận', 'đã thanh toán', 'chuyển tiền rồi'],
                'answer' => "Hệ thống thường cập nhật trong vòng 1 đến 2 phút. "
                    ."Nếu sau 10 phút đơn vẫn ở trạng thái chờ thanh toán, bạn gửi mã đơn và ảnh chụp giao dịch "
                    ."để nhân viên đối chiếu thủ công.",
            ],
            [
                'code' => 'PAY_COD',
                'group_name' => 'payment',
                'question' => 'Thanh toán khi nhận hàng có mất thêm phí không?',
                'keywords' => ['cod', 'thanh toán khi nhận', 'trả tiền mặt', 'tiền mặt'],
                'answer' => "Thanh toán khi nhận hàng không phát sinh thêm phí. Bạn chỉ trả đúng số tiền hiển thị trên đơn.",
            ],
            [
                'code' => 'PAY_INVOICE',
                'group_name' => 'payment',
                'question' => 'Tôi muốn xuất hóa đơn',
                'keywords' => ['hóa đơn', 'xuất hóa đơn', 'vat', 'hóa đơn đỏ'],
                'answer' => "SFood chưa hỗ trợ xuất hóa đơn giá trị gia tăng trên website. "
                    ."Bạn vui lòng bấm Gặp nhân viên để được hướng dẫn trường hợp cụ thể.",
            ],

            // ===== Sản phẩm =====
            [
                'code' => 'PROD_ORIGIN',
                'group_name' => 'product',
                'question' => 'Sản phẩm có nguồn gốc ở đâu?',
                'keywords' => ['nguồn gốc', 'xuất xứ', 'ở đâu', 'vietgap', 'trồng ở đâu'],
                'answer' => "Phần lớn rau củ của SFood đến từ Đà Lạt và các vùng trồng đạt chuẩn VietGAP. "
                    ."Thông tin nguồn gốc được ghi rõ trong mô tả từng sản phẩm.",
            ],
            [
                'code' => 'PROD_FRESH',
                'group_name' => 'product',
                'question' => 'Sản phẩm bảo quản được bao lâu?',
                'keywords' => ['bảo quản', 'hạn sử dụng', 'để được bao lâu', 'tươi không', 'hết hạn'],
                'answer' => "Hàng tươi được nhập trong ngày và giao ngay. Rau củ nên dùng trong 3 đến 5 ngày và bảo quản ngăn mát. "
                    ."Thịt cá nên dùng trong 2 ngày hoặc cấp đông nếu chưa dùng hết.",
            ],
            [
                'code' => 'PROD_BULK',
                'group_name' => 'product',
                'question' => 'Có bán theo số lượng lớn không?',
                'keywords' => ['số lượng lớn', 'mua sỉ', 'bán sỉ', 'bán buôn', 'mua nhiều', 'nhà hàng'],
                'answer' => "Bạn có thể chọn quy cách lớn hơn ở phần chọn quy cách. "
                    ."Với đơn số lượng lớn cho nhà hàng hoặc bếp ăn tập thể, vui lòng bấm Gặp nhân viên để được báo giá riêng.",
            ],
            [
                'code' => 'PROD_OUTSTOCK',
                'group_name' => 'product',
                'question' => 'Sản phẩm hết hàng khi nào có lại?',
                'keywords' => ['hết hàng', 'còn hàng', 'nhập lại', 'bao giờ có hàng'],
                'answer' => "Bạn bấm vào sản phẩm để xem các quy cách còn hàng. "
                    ."Nếu toàn bộ quy cách đều hết, bạn để lại lời nhắn cho nhân viên, cửa hàng sẽ thông báo khi có hàng trở lại.",
            ],
            [
                'code' => 'PROD_PRICE',
                'group_name' => 'product',
                'question' => 'Giá trên web đã gồm thuế chưa?',
                'keywords' => ['giá', 'thuế', 'phí ẩn', 'giá đã gồm'],
                'answer' => "Giá hiển thị là giá bán lẻ cuối cùng, đã bao gồm thuế. "
                    ."Ngoài giá sản phẩm, đơn hàng chỉ có thể phát sinh phí giao hàng như chính sách đã công bố.",
            ],

            // ===== Khuyến mãi & điểm thưởng =====
            [
                'code' => 'PROMO_COLLECT',
                'group_name' => 'promo',
                'question' => 'Làm sao để nhận mã giảm giá?',
                'keywords' => ['mã giảm giá', 'voucher', 'coupon', 'nhận mã', 'khuyến mãi'],
                'answer' => "Bạn vào mục Trạm khuyến mãi để xem các mã đang phát hành và bấm Nhận mã. "
                    ."Mã đã nhận được lưu trong Ví coupon ở trang Tài khoản.",
            ],
            [
                'code' => 'PROMO_INVALID',
                'group_name' => 'promo',
                'question' => 'Mã giảm giá của tôi không dùng được',
                'keywords' => ['mã không dùng được', 'lỗi mã', 'mã lỗi', 'báo lỗi', 'coupon lỗi', 'mã hết hạn', 'không áp được', 'không dùng được'],
                'answer' => "Có bốn nguyên nhân thường gặp: mã đã hết hạn, đã hết lượt sử dụng, "
                    ."đơn chưa đạt giá trị tối thiểu, hoặc giỏ hàng không có sản phẩm thuộc phạm vi áp dụng. "
                    ."Bạn kiểm tra lại điều kiện hiển thị trên thẻ mã.",
            ],
            [
                'code' => 'PROMO_STACK',
                'group_name' => 'promo',
                'question' => 'Có được dùng nhiều mã cùng lúc không?',
                'keywords' => ['nhiều mã', 'cộng dồn', 'hai mã', 'dùng chung mã'],
                'answer' => "Mỗi đơn hàng chỉ áp dụng được một mã giảm giá. Bạn nên chọn mã có lợi nhất cho đơn hiện tại.",
            ],
            [
                'code' => 'PROMO_POINTS',
                'group_name' => 'promo',
                'question' => 'Điểm thưởng được tính như thế nào?',
                'keywords' => ['điểm thưởng', 'tích điểm', 'điểm', 'reward'],
                'answer' => "Khi đơn được giao thành công, bạn nhận số điểm bằng 1 phần trăm giá trị đơn hàng. "
                    ."Mỗi điểm tương đương 1 đồng và được trừ trực tiếp khi thanh toán đơn sau.",
            ],
            [
                'code' => 'PROMO_FLASH',
                'group_name' => 'promo',
                'question' => 'Flash sale diễn ra khi nào?',
                'keywords' => ['flash sale', 'giờ vàng', 'săn sale', 'giá sốc'],
                'answer' => "Chương trình giờ vàng được hiển thị trên trang chủ với đồng hồ đếm ngược và thanh tiến độ số lượng. "
                    ."Số lượng dành cho giờ vàng có hạn nên bạn nên đặt sớm.",
            ],

            // ===== Đổi trả & khiếu nại =====
            [
                'code' => 'POLICY_RETURN',
                'group_name' => 'policy',
                'question' => 'Chính sách đổi trả như thế nào?',
                'keywords' => ['đổi trả', 'hoàn hàng', 'trả hàng', 'chính sách đổi'],
                'answer' => "Với hàng tươi sống, bạn phản hồi trong vòng 2 giờ sau khi nhận. "
                    ."Với hàng khô và đồ hộp, thời hạn là 7 ngày kể từ ngày nhận. "
                    ."Sản phẩm cần còn nguyên bao bì và có ảnh chụp làm bằng chứng.",
            ],
            [
                'code' => 'POLICY_DAMAGED',
                'group_name' => 'policy',
                'question' => 'Hàng bị dập, hư thì xử lý sao?',
                'keywords' => ['hàng hư', 'dập', 'bị dập', 'thối', 'hỏng', 'hư hàng', 'hỏng hàng', 'không tươi'],
                'answer' => "Bạn chụp ảnh sản phẩm và gửi kèm mã đơn. Nhân viên sẽ đổi mới hoặc hoàn tiền phần bị lỗi. "
                    ."Trường hợp do vận chuyển, cửa hàng chịu toàn bộ chi phí phát sinh.",
            ],
            [
                'code' => 'POLICY_REFUND',
                'group_name' => 'policy',
                'question' => 'Bao lâu thì được hoàn tiền?',
                'keywords' => ['hoàn tiền', 'lâu chưa hoàn', 'refund', 'chưa nhận tiền'],
                'answer' => "Sau khi yêu cầu được duyệt, tiền được hoàn trong 1 đến 3 ngày làm việc. "
                    ."Với thanh toán chuyển khoản, tiền về đúng tài khoản đã dùng để thanh toán.",
            ],
            [
                'code' => 'POLICY_COMPLAINT',
                'group_name' => 'policy',
                'question' => 'Tôi muốn khiếu nại thái độ nhân viên',
                'keywords' => ['khiếu nại', 'phản ánh', 'thái độ', 'góp ý'],
                'answer' => "Cửa hàng xin lỗi vì trải nghiệm chưa tốt. Bạn gửi nội dung phản ánh qua trang Liên hệ "
                    ."kèm mã đơn và thời điểm xảy ra, bộ phận chăm sóc khách hàng sẽ phản hồi trong 24 giờ.",
            ],

            // ===== Tài khoản & kỹ thuật =====
            [
                'code' => 'ACC_PASSWORD',
                'group_name' => 'account',
                'question' => 'Tôi quên mật khẩu',
                'keywords' => ['quên mật khẩu', 'không đăng nhập được', 'mất mật khẩu', 'đổi mật khẩu'],
                'answer' => "Bạn bấm Quên mật khẩu ở trang đăng nhập, nhập email đã đăng ký và làm theo hướng dẫn trong thư. "
                    ."Liên kết đặt lại mật khẩu có hiệu lực trong 60 phút.",
            ],
            [
                'code' => 'ACC_GOOGLE',
                'group_name' => 'account',
                'question' => 'Đăng nhập bằng Google được không?',
                'keywords' => ['google', 'gmail', 'đăng nhập google'],
                'answer' => "Được. Ở trang đăng nhập có nút Đăng nhập bằng Google. "
                    ."Lần đầu sử dụng, hệ thống tự tạo tài khoản từ thông tin Google của bạn.",
            ],
            [
                'code' => 'ACC_PHONE',
                'group_name' => 'account',
                'question' => 'Làm sao đổi số điện thoại?',
                'keywords' => ['đổi số điện thoại', 'sửa thông tin', 'cập nhật tài khoản', 'đổi email'],
                'answer' => "Bạn vào Tài khoản, mục Thông tin cá nhân để cập nhật họ tên, số điện thoại và ảnh đại diện. "
                    ."Email dùng để đăng nhập nên không đổi trực tiếp trên trang này.",
            ],
            [
                'code' => 'ACC_EMAIL',
                'group_name' => 'account',
                'question' => 'Tôi không nhận được email đặt lại mật khẩu',
                'keywords' => ['không nhận được email', 'mail không tới', 'không thấy thư'],
                'answer' => "Bạn kiểm tra thư mục thư rác và đảm bảo nhập đúng email đã đăng ký. "
                    ."Nếu vẫn không nhận được sau 5 phút, bạn bấm Gặp nhân viên để được hỗ trợ.",
            ],
            [
                'code' => 'ACC_BUG',
                'group_name' => 'account',
                'question' => 'Website bị lỗi, không thêm được vào giỏ',
                'keywords' => ['lỗi', 'không thêm được', 'đứng trang', 'bị treo', 'không bấm được'],
                'answer' => "Bạn thử tải lại trang và xóa bộ nhớ đệm trình duyệt. "
                    ."Nếu vẫn lỗi, bạn gửi ảnh chụp màn hình cho nhân viên để bộ phận kỹ thuật kiểm tra.",
            ],

            // ===== Chào hỏi & mặc định =====
            [
                'code' => 'GEN_GREETING',
                'group_name' => 'general',
                'question' => 'Xin chào',
                'keywords' => ['xin chào', 'chào', 'hello', 'hi', 'alo'],
                'answer' => "Xin chào bạn, SFood rất vui được hỗ trợ. "
                    ."Bạn cần tư vấn về đơn hàng, sản phẩm, giao hàng hay khuyến mãi?",
            ],
            [
                'code' => 'GEN_THANKS',
                'group_name' => 'general',
                'question' => 'Cảm ơn',
                'keywords' => ['cảm ơn', 'thank', 'cám ơn', 'ok cảm ơn'],
                'answer' => "Rất vui vì đã giúp được bạn. Nếu cần thêm thông tin, bạn cứ nhắn cho SFood nhé.",
            ],
            [
                'code' => 'GEN_HUMAN',
                'group_name' => 'general',
                'question' => 'Tôi muốn gặp nhân viên',
                'keywords' => ['gặp nhân viên', 'nhân viên tư vấn', 'người thật', 'tư vấn viên', 'gọi điện', 'gặp người'],
                'answer' => "Mình đã chuyển cuộc trò chuyện tới nhân viên chăm sóc khách hàng. "
                    ."Thời gian phản hồi trung bình từ 5 đến 10 phút trong khung giờ 8 giờ đến 21 giờ.",
            ],
            [
                'code' => 'GEN_OFFHOURS',
                'group_name' => 'general',
                'question' => 'Ngoài giờ làm việc',
                'keywords' => ['mấy giờ làm việc', 'giờ mở cửa', 'ngoài giờ', 'làm việc'],
                'answer' => "SFood hỗ trợ trực tuyến từ 8 giờ đến 21 giờ hằng ngày. "
                    ."Ngoài khung giờ này, bạn để lại nội dung, nhân viên sẽ phản hồi ngay khi mở cửa vào 8 giờ sáng hôm sau.",
            ],
            [
                'code' => 'FALLBACK',
                'group_name' => 'general',
                'question' => 'Câu hỏi khác',
                'keywords' => [],
                'answer' => "Cảm ơn bạn đã nhắn cho SFood. Mình chưa hiểu rõ câu hỏi này. "
                    ."Bạn có thể chọn một trong các chủ đề gợi ý bên dưới, hoặc bấm Gặp nhân viên để được hỗ trợ trực tiếp.",
            ],
        ];
    }
}
