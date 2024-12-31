<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Birthday Card Generator</title>
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

    <div class="preloader-overlay">
        <div class="preloader">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="loading-text">Processing your request...</div>
        </div>
    </div>
    
    <div class="container mt-5">
        <div class="mb-4">
            <h1 class="text-center">Birthday Card Generator</h1>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Card Details</h5>
                        <form id="cardForm">
                            <div class="mb-3">
                                <label for="name" class="form-label">Recipient's Name</label>
                                <input type="text" class="form-control" id="name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Recipient's Email</label>
                                <input type="email" class="form-control" id="email">
                            </div>
                            
                            <div class="mb-3">
                                <label for="customNote" class="form-label">Additional Message (Optional)</label>
                                <textarea class="form-control" id="customNote" rows="3"></textarea>
                            </div>
                            
                            <button type="button" class="btn btn-primary" onclick="previewCard()">Preview</button>
                            <button type="button" class="btn btn-success" onclick="downloadCard()">Download</button>
                            <button type="button" class="btn btn-info" onclick="sendEmail()">Send Email</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Preview</h5>
                        <div id="previewArea" class="preview-box">
                            <div class="birthday-card">
                                <div class="card-design">
                                    <i class="design-icon"></i>
                                </div>
                                <div class="card-content">
                                    Your card preview will appear here...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
