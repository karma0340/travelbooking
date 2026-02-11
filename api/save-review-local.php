<?php
header('Content-Type: application/json');
require_once '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = getDbConnection();
    
    // Sanitize Inputs
    $name = strip_tags(trim($_POST['name']));
    $text = strip_tags(trim($_POST['review_text']));
    $rating = intval($_POST['rating']);
    
    if (empty($name) || empty($text) || $rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Please fill all fields']);
        exit;
    }
    
    // Default image is empty (will trigger initials fallback), override if Google PFP provided
    $imagePath = ''; 
    if (!empty($_POST['google_pfp']) && filter_var($_POST['google_pfp'], FILTER_VALIDATE_URL)) {
        $imagePath = $_POST['google_pfp'];
    }
    
    // Insert into Database
    // Note: google_id will be NULL for local reviews, which is fine
    $stmt = $conn->prepare("INSERT INTO customer_reviews (name, rating, review_text, image_path, created_at, status) VALUES (?, ?, ?, ?, NOW(), 'approved')");
    $stmt->bind_param("siss", $name, $rating, $text, $imagePath);
    
    if ($stmt->execute()) {
        // Generate HTML for the new review so JS can inject it dynamically
        $initials = strtoupper(substr($name, 0, 1));
        
        // Define clean stars HTML
        $starsHtml = '';
        for($i=1; $i<=5; $i++) {
            $class = $i <= $rating ? '' : 'text-muted opacity-25';
            $starsHtml .= '<i class="fas fa-star '.$class.'"></i>';
        }
        
        // Image HTML logic
        if (!empty($imagePath)) {
             $imgHtml = '<img src="'.htmlspecialchars($imagePath).'" alt="'.htmlspecialchars($name).'" class="rounded-circle avatar-img" style="width: 50px; height: 50px; object-fit: cover; display: block;" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';">
                         <div class="avatar-initials bg-primary text-white rounded-circle align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 1.2rem; display: none;">'.$initials.'</div>';
        } else {
             $imgHtml = '<div class="avatar-initials bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 1.2rem;">'.$initials.'</div>';
        }

        $html = '
        <div class="col-md-6 col-lg-4 new-review-item" style="opacity: 0; transition: opacity 1s;">
            <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="testimonial-avatar me-3">
                                '.$imgHtml.'
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
                                    '.htmlspecialchars($name).'
                                </h6>
                                <small class="text-muted">Just now</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3 text-warning">
                        '.$starsHtml.'
                    </div>
                    
                    <p class="mb-0 text-muted review-text">
                        <i class="fas fa-quote-left me-2 opacity-50"></i>
                        '.nl2br(htmlspecialchars($text)).'
                        <i class="fas fa-quote-right ms-2 opacity-50"></i>
                    </p>
                </div>
            </div>
        </div>';

        echo json_encode(['success' => true, 'message' => 'Review saved successfully', 'html' => $html]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
