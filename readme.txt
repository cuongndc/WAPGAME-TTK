phiên bản upupw7.2 Thực hiện lệnh s1 để bắt đầu tất cả

1. Sử dụng phiên bản upupw7.2 để thêm máy chủ ảo và đặt tên miền chính thành localhost. Đường dẫn là thư mục gốc của xunxian

Ví dụ, E: / phpwork / xunxian

Sau đó vào thư mục upupw để tìm tệp cấu hình. Con đường của tôi là:

E: \ UPUPW_NP7.2_64 \ Nginx \ conf \ vhosts.conf

Thay đổi vhosts.conf và đặt bản ghi cuối cùng để nghe 80; thay đổi cổng chẳng hạn như 8002

2. Nhập 7 trên bảng upupw, đặt lại mật khẩu gốc và đặt nó thành root

3. Nhập localhost / pmd. Nhập tài khoản và mật khẩu làm người chủ và thêm cơ sở dữ liệu sau khi nhập, được gọi là trò chơi

4. Nhập cơ sở dữ liệu, chọn tệp cơ sở dữ liệu / gamme.sql trong thư mục xunxian

5. Kiểm tra hai file db.config.php và db.config.sample.php trong thư mục / config Xunxian, mở chúng bằng công cụ Notepad ++ để kiểm tra cấu hình bên trong có đúng không.

Sửa đổi theo yêu cầu

6. bảng upupw nhập rr để khởi động lại dịch vụ nginx

7. Trình duyệt cục bộ truy cập http: // localhost: 8002 / (8002 được cấu hình bởi Upupw bây giờ trỏ đến thư mục gốc của Xunxian)

8. Nhập tài khoản và mật khẩu là admin123456 để đăng nhập, và bạn có thể tạo vai trò sau khi nhập. Bạn cũng có thể đăng ký để đăng nhập vào các tài khoản khác

Vào cơ sở dữ liệu tìm bảng Userinfo, đặt trường sức mạnh của người dùng mới đăng ký là god, và bạn có thể đăng nhập vào giao diện quản trị viên

9.http: // localhost: 8002 / admin / admin.php là trang nền quản lý

Nhắc nhở: Sau khi thử nghiệm, chương trình đã bán xong, hoan nghênh bạn tiếp tục phát triển với công nghệ.

Happy Source Code Network (18746.com) là mạng mã nguồn game word chuyên nghiệp, cung cấp các loại mã nguồn game word, mã nguồn wap game, mã nguồn game bùn, mã nguồn game H5 và các nguồn download khác. Đây chỉ là để kỷ niệm kỷ nguyên trò chơi chữ đã từng đồng hành cùng chúng ta.