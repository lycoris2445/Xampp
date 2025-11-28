-- Bảng 1: Lưu thông tin cá nhân
-- Bảng này sẽ là trung tâm, lưu thông tin chính của chủ sở hữu portfolio.
CREATE TABLE personal_info (
    id SERIAL PRIMARY KEY,                          -- ID tự động tăng
    full_name VARCHAR(255) NOT NULL,                -- Họ tên
    email VARCHAR(255) UNIQUE NOT NULL,             -- Email (duy nhất)
    phone VARCHAR(20),                              -- Điện thoại
    address TEXT,                                   -- Địa chỉ
    summary TEXT                                    -- Mô tả ngắn về bản thân
);

-- Bảng 2: Lưu kinh nghiệm làm việc
CREATE TABLE experience (
    id SERIAL PRIMARY KEY,
    person_id INT NOT NULL,                         -- Khóa ngoại liên kết với personal_info
    company_name VARCHAR(255) NOT NULL,             -- Tên công ty
    position VARCHAR(255) NOT NULL,                 -- Vị trí
    start_date DATE,                                -- Ngày bắt đầu
    end_date DATE,                                  -- Ngày kết thúc (NULL nếu vẫn đang làm)
    description TEXT,                               -- Mô tả công việc
    
    -- Tạo liên kết khóa ngoại
    FOREIGN KEY (person_id) REFERENCES personal_info(id) ON DELETE CASCADE
);

-- Bảng 3: Lưu thông tin học vấn
CREATE TABLE education (
    id SERIAL PRIMARY KEY,
    person_id INT NOT NULL,                         -- Khóa ngoại liên kết với personal_info
    school_name VARCHAR(255) NOT NULL,              -- Tên trường
    major VARCHAR(255),                             -- Chuyên ngành
    start_date DATE,                                -- Ngày bắt đầu học
    end_date DATE,                                  -- Ngày kết thúc/tốt nghiệp
    
    -- Tạo liên kết khóa ngoại
    FOREIGN KEY (person_id) REFERENCES personal_info(id) ON DELETE CASCADE
);

-- Bảng 4: Lưu danh sách kỹ năng
CREATE TABLE skills (
    id SERIAL PRIMARY KEY,
    person_id INT NOT NULL,                         -- Khóa ngoại liên kết với personal_info
    skill_name VARCHAR(100) NOT NULL,               -- Tên kỹ năng
    proficiency_level VARCHAR(50),                  -- Mức độ thành thạo (VD: 'Advanced', 'Intermediate', '80%')
    
    -- Tạo liên kết khóa ngoại
    FOREIGN KEY (person_id) REFERENCES personal_info(id) ON DELETE CASCADE
);

-- Ghi chú: ON DELETE CASCADE nghĩa là nếu bạn xóa một người trong 'personal_info',
-- tất cả kinh nghiệm, học vấn, và kỹ năng liên quan cũng sẽ tự động bị xóa.
-- Bắt đầu một Transaction để đảm bảo toàn vẹn dữ liệu
BEGIN;

-- Sử dụng Common Table Expression (CTE)
-- 1. Thêm vào bảng 'personal_info' VÀ lấy ID vừa được tạo ra
WITH new_person AS (
    INSERT INTO personal_info (
        full_name, 
        email, 
        phone, 
        address, 
        summary
    )
    VALUES (
        'Trần Thị B', -- Thay bằng tên thật của bạn
        'sinhvien.tmdt@example.com', -- Thay bằng email thật
        '0901234567', -- Thay bằng SĐT thật
        '123 Đường ABC, Quận 1, TP. Hồ Chí Minh', -- Thay bằng địa chỉ thật
        'Là một sinh viên thương mại điện tử năng động, ham học hỏi với mong muốn áp dụng kiến thức vào các dự án thực tế. Có khả năng tự học tốt, tư duy logic và kỹ năng làm việc nhóm hiệu quả. Mong muốn tìm kiếm cơ hội để phát triển bản thân và đóng góp vào sự thành công của tổ chức.'
    )
    -- Lấy ID của hàng vừa được thêm vào
    RETURNING id
)

-- 2. Thêm vào bảng 'experience', sử dụng ID từ CTE 'new_person'
-- (Bảng 'new_experience' không thực sự cần thiết, nhưng giúp rõ ràng)
, new_experience AS (
    INSERT INTO experience (
        person_id, 
        company_name, 
        position, 
        start_date, 
        end_date, 
        description
    )
    SELECT 
        id, -- Đây là ID lấy từ 'new_person'
        'Công ty TNHH ABC',
        'Thực tập sinh Digital Marketing',
        '2024-06-01',
        '2024-09-01',
        -- Sử dụng E'' để PostgreSQL hiểu ký tự xuống dòng \n
        E'Tham gia xây dựng chiến lược marketing online cho sản phẩm.\nQuản lý nội dung trên các nền tảng mạng xã hội.\nPhân tích dữ liệu và tối ưu hóa chiến dịch quảng cáo.'
    FROM new_person
)

-- 3. Thêm vào bảng 'education', sử dụng ID từ CTE 'new_person'
, new_education AS (
    INSERT INTO education (
        person_id, 
        school_name, 
        major, 
        start_date, 
        end_date
    )
    SELECT 
        id, -- Đây là ID lấy từ 'new_person'
        'Trường Đại học Kinh tế TP. Hồ Chí Minh',
        'Thương mại Điện tử',
        '2021-09-01',
        NULL -- 'Hiện tại' có nghĩa là ngày kết thúc là NULL
    FROM new_person
)

-- 4. Thêm nhiều hàng vào bảng 'skills', sử dụng ID từ CTE 'new_person'
INSERT INTO skills (person_id, skill_name, proficiency_level)
VALUES
    ((SELECT id FROM new_person), 'SEO/SEM', 'Thành thạo'),
    ((SELECT id FROM new_person), 'Google Analytics', 'Thành thạo'),
    ((SELECT id FROM new_person), 'Photoshop', 'Trung bình'),
    ((SELECT id FROM new_person), 'Tiếng Anh', 'Khá');

-- Kết thúc và xác nhận Transaction
COMMIT;