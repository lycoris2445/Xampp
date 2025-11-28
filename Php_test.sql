-- Tạo Database (nếu chưa có)
CREATE DATABASE IF NOT EXISTS Php_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE Php_test;

-- Bảng 1: Lưu thông tin cá nhân
CREATE TABLE IF NOT EXISTS personal_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    summary TEXT
) ENGINE=InnoDB;

-- Bảng 2: Lưu kinh nghiệm làm việc
CREATE TABLE IF NOT EXISTS experience (
    id INT AUTO_INCREMENT PRIMARY KEY,
    person_id INT NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    position VARCHAR(255) NOT NULL,
    start_date DATE,
    end_date DATE,
    description TEXT,
    FOREIGN KEY (person_id) REFERENCES personal_info(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng 3: Lưu thông tin học vấn
CREATE TABLE IF NOT EXISTS education (
    id INT AUTO_INCREMENT PRIMARY KEY,
    person_id INT NOT NULL,
    school_name VARCHAR(255) NOT NULL,
    major VARCHAR(255),
    start_date DATE,
    end_date DATE,
    FOREIGN KEY (person_id) REFERENCES personal_info(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng 4: Lưu danh sách kỹ năng
CREATE TABLE IF NOT EXISTS skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    person_id INT NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    proficiency_level VARCHAR(50), -- Ví dụ: '80' (để dùng cho progress bar)
    label VARCHAR(50),             -- Ví dụ: 'Thành thạo'
    FOREIGN KEY (person_id) REFERENCES personal_info(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bắt đầu Transaction để thêm dữ liệu mẫu
START TRANSACTION;

-- 1. Thêm vào bảng personal_info
INSERT INTO personal_info (full_name, email, phone, address, summary)
VALUES (
    'Nguyễn Quỳnh Trang',
    'nguyenquynhtrang@example.com',
    '0912 345 678',
    '456 Đường XYZ, Quận 3, TP. Hồ Chí Minh',
    'Là một sinh viên thương mại điện tử năng động, ham học hỏi với mong muốn áp dụng kiến thức vào các dự án thực tế. Có khả năng tự học tốt, tư duy logic và kỹ năng làm việc nhóm hiệu quả.'
);

-- Lấy ID vừa tạo lưu vào biến @person_id
SET @person_id = LAST_INSERT_ID();

-- 2. Thêm vào bảng experience
INSERT INTO experience (person_id, company_name, position, start_date, end_date, description)
VALUES 
(@person_id, 'Công ty TNHH ABC', 'Thực tập sinh Digital Marketing', '2024-06-01', '2024-09-01', 'Tham gia xây dựng chiến lược marketing online.\nQuản lý nội dung mạng xã hội.\nPhân tích dữ liệu quảng cáo.');

-- 3. Thêm vào bảng education
INSERT INTO education (person_id, school_name, major, start_date, end_date)
VALUES 
(@person_id, 'Trường Đại học Kinh tế TP. Hồ Chí Minh', 'Thương mại Điện tử', '2021-09-01', NULL);

-- 4. Thêm vào bảng skills (Lưu ý: proficiency_level lưu số % để hiển thị bar)
INSERT INTO skills (person_id, skill_name, proficiency_level, label)
VALUES
    (@person_id, 'SEO/SEM', '85', 'Thành thạo'),
    (@person_id, 'Google Analytics', '80', 'Thành thạo'),
    (@person_id, 'Photoshop', '75', 'Trung bình'),
    (@person_id, 'Tiếng Anh', '70', 'Khá');

COMMIT;