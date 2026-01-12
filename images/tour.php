<?php
require_once 'includes/db.php';

// Get tour ID from URL parameter
$tourId = $_GET['id'] ?? null;

// If no ID provided, redirect to tours page
if (!$tourId) {
    header('Location: tours.php');
    exit;
}

// Get tour details
$tour = getTourById($tourId);

// If tour not found, redirect to tours page
if (!$tour) {
    header('Location: tours.php');
    exit;
}

// Get tour itinerary
$itinerary = getTourItinerary($tourId);

// Get related tours
$relatedTours = getRelatedTours($tour['category'], $tourId, 3);

// Set SEO variables
$pageTitle = $tour['title'] . ' - Travel In Peace | Himachal Tour Package';
$pageDescription = !empty($tour['description']) ? $tour['description'] : 'Experience ' . $tour['title'] . ' with Travel In Peace. Premium Himachal tour package with expert guides and comfortable accommodations.';
$pageKeywords = generateSEOKeywords($tour['title'] . ', ' . $tour['category'] . ', himachal tour package, ' . $tour['title'] . ' tour, travel in peace');

// Include header
include 'includes/header.php';
?>

<div class="tour-detail-header" style="background-image: url('<?= htmlspecialchars($tour['image']) ?>')">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <h1 class="text-white"><?= htmlspecialchars($tour['title']) ?></h1>
                <div class="tour-meta">
                    <span><i class="far fa-clock"></i> <?= htmlspecialchars($tour['duration']) ?></span>
                    <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($tour['location']) ?></span>
                    <span><i class="fas fa-star text-warning"></i> <?= $avgRating ?> (<?= count($reviews) ?> reviews)</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <!-- Tour Content -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h3>Tour Overview</h3>
                    <p><?= nl2br(htmlspecialchars($tour['description'])) ?></p>
                    
                    <div class="tour-highlights mt-4">
                        <h4>Tour Highlights</h4>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($tour['highlights'] as $highlight): ?>
                            <li class="list-group-item"><i class="fas fa-check-circle text-success me-2"></i> <?= htmlspecialchars($highlight) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Itinerary -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h3>Tour Itinerary</h3>
                    
                    <div class="accordion" id="accordionItinerary">
                        <?php foreach ($itinerary as $index => $day): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>">
                                    Day <?= $index + 1 ?>: <?= htmlspecialchars($day['title']) ?>
                                </button>
                            </h2>
                            <div id="collapse<?= $index ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" data-bs-parent="#accordionItinerary">
                                <div class="accordion-body">
                                    <?= nl2br(htmlspecialchars($day['description'])) ?>
                                    
                                    <?php if (!empty($day['meals'])): ?>
                                    <div class="meals mt-3">
                                        <strong>Meals:</strong>
                                        <?php foreach ($day['meals'] as $meal): ?>
                                        <span class="badge bg-light text-dark me-2"><?= htmlspecialchars($meal) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($day['accommodation'])): ?>
                                    <div class="accommodation mt-2">
                                        <strong>Accommodation:</strong> <?= htmlspecialchars($day['accommodation']) ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Tour Map -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h3>Tour Map</h3>
                    <div id="tourMap" style="height: 400px;" class="mt-3"></div>
                </div>
            </div>
            
            <!-- Reviews -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3>Customer Reviews</h3>
                    
                    <?php if (!empty($reviews)): ?>
                    <div class="reviews">
                        <?php foreach ($reviews as $review): ?>
                        <div class="review mb-4">
                            <div class="d-flex">
                                <div class="review-avatar me-3">
                                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($review['name']) ?>&background=random" alt="<?= htmlspecialchars($review['name']) ?>" class="rounded-circle" width="50" height="50">
                                </div>
                                <div class="review-content">
                                    <h5><?= htmlspecialchars($review['name']) ?></h5>
                                    <div class="review-rating mb-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?= $i <= $review['rating'] ? 'text-warning' : 'text-muted' ?>"></i>
                                        <?php endfor; ?>
                                        <span class="ms-2 text-muted"><?= date('M d, Y', strtotime($review['date'])) ?></span>
                                    </div>
                                    <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <p>No reviews yet for this tour.</p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Review Form -->
                    <div class="review-form mt-4">
                        <h4>Leave a Review</h4>
                        <form id="reviewForm" class="needs-validation" novalidate>
                            <input type="hidden" name="tour_id" value="<?= $tourId ?>">
                            <div class="mb-3">
                                <label class="form-label">Your Name</label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Your Email</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Rating</label>
                                <div class="rating-input">
                                    <div class="rating-stars">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" name="rating" id="rating-<?= $i ?>" value="<?= $i ?>" <?= $i === 5 ? 'checked' : '' ?>>
                                        <label for="rating-<?= $i ?>"><i class="fas fa-star"></i></label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Your Review</label>
                                <textarea class="form-control" name="comment" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Review</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Tour Booking Card -->
            <div class="card shadow-sm mb-4 booking-card sticky-top">
                <div class="card-body">
                    <h3 class="price mb-3">From ₹<?= number_format($tour['price']) ?><span class="text-muted small"> / person</span></h3>
                    
                    <form id="tourBookingForm">
                        <input type="hidden" name="tour_id" value="<?= $tourId ?>">
                        <div class="mb-3">
                            <label class="form-label">Select Date</label>
                            <input type="date" class="form-control" name="travel_date" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Number of Travelers</label>
                            <select class="form-select" name="travelers" required>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?> <?= $i === 1 ? 'traveler' : 'travelers' ?></option>
                                <?php endfor; ?>
                                <option value="more">More than 10</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3">Book Now</button>
                    </form>
                    
                    <div class="booking-includes mt-4">
                        <h5>Tour Includes:</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success me-2"></i> <?= $tour['accommodation_included'] ? 'Accommodation' : 'No accommodation' ?></li>
                            <li><i class="fas fa-check text-success me-2"></i> <?= $tour['meals_included'] ? 'Meals as per itinerary' : 'No meals' ?></li>
                            <li><i class="fas fa-check text-success me-2"></i> Transportation</li>
                            <li><i class="fas fa-check text-success me-2"></i> Guide services</li>
                            <li><i class="fas fa-check text-success me-2"></i> Entry fees</li>
                        </ul>
                    </div>
                    
                    <div class="booking-contact mt-4">
                        <p>Need help with your booking?</p>
                        <a href="tel:+918627873362" class="btn btn-outline-primary w-100"><i class="fas fa-phone-alt me-2"></i> +91 8627873362 / 7559775470</a>
                    </div>
                </div>
            </div>
            
            <!-- Related Tours -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4>Related Tours</h4>
                    
                    <?php foreach ($relatedTours as $relatedTour): ?>
                    <div class="related-tour mb-3">
                        <div class="row g-0">
                            <div class="col-4">
                                <img src="<?= htmlspecialchars($relatedTour['image']) ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($relatedTour['title']) ?>">
                            </div>
                            <div class="col-8 ps-3">
                                <h6><?= htmlspecialchars($relatedTour['title']) ?></h6>
                                <div class="small">
                                    <i class="far fa-clock me-1"></i> <?= htmlspecialchars($relatedTour['duration']) ?>
                                </div>
                                <div class="mt-1">
                                    <span class="fw-bold">₹<?= number_format($relatedTour['price']) ?></span>
                                    <a href="tour.php?id=<?= $relatedTour['id'] ?>" class="btn btn-sm btn-outline-primary float-end">View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Map Scripts -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDXQhMYUFuwXR6EnPgx7Q1rHItLdMr2MuA&callback=initMap" defer></script>
<script>
function initMap() {
    // Tour map initialization
    const mapPoints = <?= json_encode($tour['map_points'] ?? []) ?>;
    
    // Create map centered at first point or fallback to Shimla
    const center = mapPoints.length > 0 
        ? { lat: mapPoints[0].lat, lng: mapPoints[0].lng } 
        : { lat: 31.1048, lng: 77.1734 };
    
    const map = new google.maps.Map(document.getElementById('tourMap'), {
        zoom: 10,
        center: center
    });
    
    // Add markers for each point
    const bounds = new google.maps.LatLngBounds();
    const infoWindow = new google.maps.InfoWindow();
    
    mapPoints.forEach((point, index) => {
        const marker = new google.maps.Marker({
            position: { lat: point.lat, lng: point.lng },
            map: map,
            title: point.name,
            label: (index + 1).toString()
        });
        
        bounds.extend(marker.getPosition());
        
        marker.addListener('click', () => {
            infoWindow.setContent(`<div><strong>${point.name}</strong><p>${point.description || ''}</p></div>`);
            infoWindow.open(map, marker);
        });
    });
    
    // If we have multiple points, fit the map to show all markers
    if (mapPoints.length > 1) {
        map.fitBounds(bounds);
    }
    
    // Draw path between points
    if (mapPoints.length > 1) {
        const path = mapPoints.map(point => ({ lat: point.lat, lng: point.lng }));
        const tourPath = new google.maps.Polyline({
            path: path,
            geodesic: true,
            strokeColor: '#4F46E5',
            strokeOpacity: 1.0,
            strokeWeight: 3
        });
        
        tourPath.setMap(map);
    }
}
</script>

<?php
include 'includes/footer.php';
?>
