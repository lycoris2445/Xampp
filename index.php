<?php
// --- 1. CẤU HÌNH KẾT NỐI DATABASE ---
$host = 'localhost';
$dbname = 'Php_test';
$user = 'root';
$pass = '';

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('<div class="alert alert-danger">Lỗi kết nối CSDL: ' . $e->getMessage() . '</div>');
}

// --- 2. CÁC HÀM XỬ LÝ DỮ LIỆU (HELPER FUNCTIONS) ---
function getCV($conn) {
    // Lấy thông tin cá nhân (người đầu tiên tìm thấy)
    $stmt = $conn->query("SELECT * FROM personal_info LIMIT 1");
    $personalInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$personalInfo) return null; // Chưa có dữ liệu cá nhân

    $id = $personalInfo['id'];

    // Lấy các bảng con theo person_id
    $exp = $conn->prepare("SELECT * FROM experience WHERE person_id = ?");
    $exp->execute([$id]);
    
    $edu = $conn->prepare("SELECT * FROM education WHERE person_id = ?");
    $edu->execute([$id]);

    $ski = $conn->prepare("SELECT * FROM skills WHERE person_id = ?");
    $ski->execute([$id]);

    return [
        'personalInfo' => $personalInfo,
        'experience' => $exp->fetchAll(PDO::FETCH_ASSOC),
        'education' => $edu->fetchAll(PDO::FETCH_ASSOC),
        'skills' => $ski->fetchAll(PDO::FETCH_ASSOC)
    ];
}

function addContent($conn, $table, $data) {
    $columns = implode(", ", array_keys($data));
    $placeholders = ":" . implode(", :", array_keys($data));
    $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    $stmt = $conn->prepare($query);
    $stmt->execute($data);
}

function updateContent($conn, $table, $data, $id) {
    $setClause = "";
    foreach ($data as $key => $value) {
        $setClause .= "$key = :$key, ";
    }
    $setClause = rtrim($setClause, ", ");
    $query = "UPDATE $table SET $setClause WHERE id = :id";
    $stmt = $conn->prepare($query);
    $data['id'] = $id;
    $stmt->execute($data);
}

function deleteContent($conn, $table, $id) {
    $query = "DELETE FROM $table WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->execute(['id' => $id]);
}

// --- 3. XỬ LÝ REQUEST TỪ FORM (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Xử lý Thêm Kinh Nghiệm
    if (isset($_POST['action']) && $_POST['action'] == 'add_experience') {
        $data = [
            'person_id' => $_POST['person_id'],
            'company_name' => $_POST['company_name'],
            'position' => $_POST['position'],
            'start_date' => $_POST['start_date'],
            'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
            'description' => $_POST['description']
        ];
        addContent($conn, 'experience', $data);
        header("Location: index.php"); // Load lại trang để tránh gửi lại form
        exit();
    }

    // Xử lý Sửa Kinh Nghiệm
    if (isset($_POST['action']) && $_POST['action'] == 'edit_experience') {
        $id = $_POST['id'];
        $data = [
            'company_name' => $_POST['company_name'],
            'position' => $_POST['position'],
            'start_date' => $_POST['start_date'],
            'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
            'description' => $_POST['description']
        ];
        updateContent($conn, 'experience', $data, $id);
        header("Location: index.php");
        exit();
    }

    // Xử lý Xóa Kinh Nghiệm
    if (isset($_POST['action']) && $_POST['action'] == 'delete_experience') {
        $id = $_POST['id'];
        deleteContent($conn, 'experience', $id);
        header("Location: index.php");
        exit();
    }
}

// --- 4. LẤY DỮ LIỆU HIỂN THỊ RA HTML ---
$cvData = getCV($conn);
$person = $cvData['personalInfo'];
$experiences = $cvData['experience'];
$educations = $cvData['education'];
$skills = $cvData['skills'];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CV - <?php echo htmlspecialchars($person['full_name'] ?? 'Portfolio'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light py-4">

    <div class="container">
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-body p-4 p-md-5">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center mb-3 mb-md-0">
                        <img src="data:image/jpeg;base64,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" alt="Ảnh đại diện" class="profile-avatar">
                    </div>
                    <div class="col-md-9 text-center text-md-start">
                        <h1 class="display-6 fw-bold"><?php echo htmlspecialchars($person['full_name']); ?></h1>
                        <p class="text-muted fs-5">Sinh viên thương mại điện tử</p>
                        <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-3 mt-3">
                            <span class="badge bg-white text-dark border"><i class="fas fa-envelope me-2 text-primary"></i><?php echo htmlspecialchars($person['email']); ?></span>
                            <span class="badge bg-white text-dark border"><i class="fas fa-phone me-2 text-success"></i><?php echo htmlspecialchars($person['phone']); ?></span>
                            <span class="badge bg-white text-dark border"><i class="fas fa-map-marker-alt me-2 text-danger"></i><?php echo htmlspecialchars($person['address']); ?></span>
                        </div>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-top">
                    <h5 class="text-uppercase text-primary fs-6 fw-bold mb-2">Giới thiệu</h5>
                    <p class="text-secondary mb-0"><?php echo nl2br(htmlspecialchars($person['summary'])); ?></p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                
                <div class="card shadow-sm border-0 mb-4 h-100">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h4 class="h5 mb-0 text-primary fw-bold"><i class="fas fa-briefcase me-2"></i>Kinh nghiệm làm việc</h4>
                        <button class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#addExperienceModal">
                            <i class="fas fa-plus"></i> Thêm
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if (count($experiences) > 0): ?>
                            <?php foreach ($experiences as $exp): ?>
                                <div class="mb-4 position-relative group-item-hover">
                                    <div class="d-flex flex-column flex-md-row justify-content-between mb-2">
                                        <div>
                                            <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($exp['position']); ?></h6>
                                            <p class="text-muted small mb-2"><i class="far fa-building me-1"></i><?php echo htmlspecialchars($exp['company_name']); ?></p>
                                        </div>
                                        <span class="badge bg-light text-secondary border align-self-start">
                                            <?php echo date('m/Y', strtotime($exp['start_date'])); ?> - 
                                            <?php echo $exp['end_date'] ? date('m/Y', strtotime($exp['end_date'])) : 'Hiện tại'; ?>
                                        </span>
                                    </div>
                                    <p class="small text-secondary"><?php echo nl2br(htmlspecialchars($exp['description'])); ?></p>
                                    
                                    <div class="mt-2">
                                        <button class="btn btn-sm btn-link text-warning p-0 me-2 btn-edit-exp" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editExperienceModal"
                                            data-id="<?php echo $exp['id']; ?>"
                                            data-company="<?php echo htmlspecialchars($exp['company_name']); ?>"
                                            data-position="<?php echo htmlspecialchars($exp['position']); ?>"
                                            data-start="<?php echo $exp['start_date']; ?>"
                                            data-end="<?php echo $exp['end_date']; ?>"
                                            data-desc="<?php echo htmlspecialchars($exp['description']); ?>"
                                        >
                                            <i class="fas fa-edit"></i> Sửa
                                        </button>

                                        <form method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa không?');">
                                            <input type="hidden" name="action" value="delete_experience">
                                            <input type="hidden" name="id" value="<?php echo $exp['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-link text-danger p-0">
                                                <i class="fas fa-trash-alt"></i> Xóa
                                            </button>
                                        </form>
                                    </div>
                                    <hr class="text-muted opacity-25">
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted text-center">Chưa có dữ liệu kinh nghiệm.</p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h4 class="h5 mb-0 text-primary fw-bold"><i class="fas fa-graduation-cap me-2"></i>Học vấn</h4>
                    </div>
                    <div class="card-body">
                        <?php foreach ($educations as $edu): ?>
                         <div class="mb-3">
                            <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($edu['major']); ?></h6>
                            <p class="text-muted small mb-1"><?php echo htmlspecialchars($edu['school_name']); ?></p>
                            <span class="badge bg-info text-dark">
                                <?php echo date('Y', strtotime($edu['start_date'])); ?> - 
                                <?php echo $edu['end_date'] ? date('Y', strtotime($edu['end_date'])) : 'Hiện tại'; ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 py-3">
                        <h4 class="h5 mb-0 text-primary fw-bold"><i class="fas fa-tools me-2"></i>Kỹ năng</h4>
                    </div>
                    <div class="card-body">
                        <?php foreach ($skills as $skill): 
                            // Xử lý thanh progress bar (nếu dữ liệu không phải số thì mặc định 50%)
                            $percent = is_numeric($skill['proficiency_level']) ? $skill['proficiency_level'] : 50;
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-semibold small"><?php echo htmlspecialchars($skill['skill_name']); ?></span>
                                <span class="small text-muted"><?php echo htmlspecialchars($skill['proficiency_level']); ?>%</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $percent; ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addExperienceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Thêm Kinh nghiệm mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_experience">
                        <input type="hidden" name="person_id" value="<?php echo $person['id']; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Tên công ty</label>
                            <input type="text" class="form-control" name="company_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Vị trí / Chức vụ</label>
                            <input type="text" class="form-control" name="position" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Ngày bắt đầu</label>
                                <input type="date" class="form-control" name="start_date">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Ngày kết thúc</label>
                                <input type="date" class="form-control" name="end_date">
                                <small class="text-muted">Để trống nếu vẫn đang làm</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô tả công việc</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Lưu lại</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editExperienceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Cập nhật Kinh nghiệm</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_experience">
                        <input type="hidden" name="id" id="edit_id"> 
                        
                        <div class="mb-3">
                            <label class="form-label">Tên công ty</label>
                            <input type="text" class="form-control" name="company_name" id="edit_company" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Vị trí / Chức vụ</label>
                            <input type="text" class="form-control" name="position" id="edit_position" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Ngày bắt đầu</label>
                                <input type="date" class="form-control" name="start_date" id="edit_start">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Ngày kết thúc</label>
                                <input type="date" class="form-control" name="end_date" id="edit_end">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Mô tả công việc</label>
                            <textarea class="form-control" name="description" id="edit_desc" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Cập nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var editButtons = document.querySelectorAll('.btn-edit-exp');
            var editModal = document.getElementById('editExperienceModal');

            editButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    // Lấy dữ liệu từ data attribute của nút bấm
                    var id = this.getAttribute('data-id');
                    var company = this.getAttribute('data-company');
                    var position = this.getAttribute('data-position');
                    var start = this.getAttribute('data-start');
                    var end = this.getAttribute('data-end');
                    var desc = this.getAttribute('data-desc');

                    // Điền vào form trong modal
                    document.getElementById('edit_id').value = id;
                    document.getElementById('edit_company').value = company;
                    document.getElementById('edit_position').value = position;
                    document.getElementById('edit_start').value = start;
                    document.getElementById('edit_end').value = end;
                    document.getElementById('edit_desc').value = desc;
                });
            });
        });
    </script>
</body>
</html>