<?php
require_once 'config/database.php';

// Get all templates
$db = new Database();
$conn = $db->connect();
$stmt = $conn->prepare("SELECT * FROM templates WHERE is_active = 1 ORDER BY created_at DESC");
$stmt->execute();
$templates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Birthday Card Templates</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .navbar {
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Message Templates</h1>
            <div>
                <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#uploadModal">Upload New Template</button>
                <button class="btn btn-success me-2" onclick="applySelectedTemplates()">Apply Selected</button>
            </div>
        </div>

        <!-- Error/Success Alerts -->
        <div id="errorAlert" class="alert alert-danger" style="display: none;"></div>
        <div id="successAlert" class="alert alert-success" style="display: none;"></div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="message-templates template-container">
                            <?php foreach ($templates as $template): ?>
                            <div class="template-card">
                                <div class="birthday-card" style="background-image: url('<?php echo htmlspecialchars($template['path']); ?>')">
                                    <div class="card-design">
                                        <i class="design-icon design-<?php echo htmlspecialchars($template['design']); ?>"></i>
                                    </div>
                                    <div class="card-content">
                                        <?php echo nl2br(htmlspecialchars($template['message'])); ?>
                                    </div>
                                </div>
                                <div class="template-select">
                                    <input type="radio" name="template" value="<?php echo $template['id']; ?>" 
                                           data-message="<?php echo htmlspecialchars($template['message']); ?>"
                                           data-background="<?php echo htmlspecialchars($template['background']); ?>"
                                           data-design="<?php echo htmlspecialchars($template['design']); ?>">
                                    <label><?php echo htmlspecialchars($template['name']); ?></label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden template selection input -->
        <input type="hidden" id="selectedMessage" value="">

        <!-- Upload Modal -->
        <div class="modal fade" id="uploadModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload New Template</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="templateUploadForm" enctype="multipart/form-data" method="post">
                            <div class="mb-3">
                                <label for="templateName" class="form-label">Template Name</label>
                                <input type="text" class="form-control" id="templateName" name="name" required>
                                <small class="form-text text-muted">A descriptive name for your template</small>
                            </div>

                            <div class="mb-3">
                                <label for="templateMessage" class="form-label">Message</label>
                                <textarea class="form-control" id="templateMessage" name="message" rows="3" required></textarea>
                                <small class="form-text text-muted">Use [NAME], [Name], or [name] as a placeholder for recipient's name</small>
                            </div>

                            <div class="mb-3">
                                <label for="templateImage" class="form-label">Background Image</label>
                                <input type="file" class="form-control" id="templateImage" name="image" accept="image/*" required>
                                <small class="form-text text-muted">Max size: 2MB. Supported formats: JPG, PNG, GIF</small>
                            </div>

                            <div class="mb-3">
                                <label for="templateDesign" class="form-label">Design Icon</label>
                                <select class="form-control" id="templateDesign" name="design" required>
                                    <option value="">Select Icon</option>
                                    <option value="stars">Stars ⭐</option>
                                    <option value="balloons">Balloons 🎈</option>
                                    <option value="confetti">Confetti 🎊</option>
                                    <option value="cake">Cake 🎂</option>
                                    <option value="gifts">Gifts 🎁</option>
                                </select>
                            </div>

                            <!-- Preview section -->
                            <div class="mb-3">
                                <label class="form-label">Preview</label>
                                <div id="templatePreview" class="template-preview">
                                    <div class="preview-placeholder">
                                        Upload an image to see preview
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="uploadTemplate()">Upload</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/templates.js"></script>
    <script src="assets/js/card-preview.js"></script>
</body>
</html>
