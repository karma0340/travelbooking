<?php
/**
 * Tour Sections Include
 * Contains Tour Categories and Places to Visit sections
 * Can be included in multiple pages
 */
?>

<!-- Categories Section -->
<section id="categories" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <span class="text-primary fw-bold text-uppercase letter-spacing-1">Find Your Style</span>
            <h2 class="display-5 fw-bold">Tour Categories</h2>
        </div>
        
        <div class="position-relative">
            <!-- Navigation Buttons -->
            <button class="btn btn-primary rounded-circle shadow slider-btn slider-prev position-absolute start-0 top-50 translate-middle-y z-3 d-none d-lg-flex" onclick="scrollSlider(-1)" style="width: 50px; height: 50px; align-items: center; justify-content: center; margin-left: -25px;">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="btn btn-primary rounded-circle shadow slider-btn slider-next position-absolute end-0 top-50 translate-middle-y z-3 d-none d-lg-flex" onclick="scrollSlider(1)" style="width: 50px; height: 50px; align-items: center; justify-content: center; margin-right: -25px;">
                <i class="fas fa-chevron-right"></i>
            </button>
            
            <!-- Slider Container -->
            <div class="d-flex overflow-auto gap-4 py-4 px-2 slider-container snap-x" id="categorySlider" style="scroll-behavior: smooth; -ms-overflow-style: none; scrollbar-width: none;">
                
                <?php
                // Fetch categories from DB
                $tourCategories = [];
                if (function_exists('getCategories')) {
                    $tourCategories = getCategories(true);
                }
                
                // Fallback if DB is empty
                if (empty($tourCategories)) {
                    $tourCategories = [
                        [
                            'name' => 'Adventure', 
                            'slug' => 'adventure',
                            'description' => 'Trekking, paragliding, and river rafting for thrill-seekers.',
                            'icon' => 'fa-mountain',
                            'image' => 'images/placeholder/adventure-tours.png',
                            'color' => 'primary'
                        ],
                        [
                            'name' => 'Family', 
                            'slug' => 'family',
                            'description' => 'Comfortable itineraries with kid-friendly activities.',
                            'icon' => 'fa-users',
                            'image' => 'images/placeholder/family-tours.png',
                            'color' => 'success'
                        ],
                        [
                            'name' => 'Honeymoon', 
                            'slug' => 'honeymoon',
                            'description' => 'Romantic getaways with luxury stays and special experiences.',
                            'icon' => 'fa-heart',
                            'image' => 'images/placeholder/honeymoon-tours.png',
                            'color' => 'danger'
                        ],
                        [
                            'name' => 'Spiritual', 
                            'slug' => 'spiritual',
                            'description' => 'Discover inner peace at ancient Himalayan monasteries & temples.',
                            'icon' => 'fa-om',
                            'image' => 'images/placeholder/spiritual-tours.png',
                            'color' => 'warning'
                        ],
                        [
                            'name' => 'Group Tours', 
                            'slug' => 'group',
                            'description' => 'Bonfires, camping, and unforgettable memories with friends.',
                            'icon' => 'fa-users-cog',
                            'image' => 'images/placeholder/group-tours.png',
                            'color' => 'info'
                        ],
                        [
                            'name' => 'Nature', 
                            'slug' => 'nature',
                            'description' => 'Immerse yourself in lush valleys, forests, and untouched wilderness.',
                            'icon' => 'fa-leaf',
                            'image' => 'images/placeholder/nature-tours.png',
                            'color' => 'success'
                        ]
                    ];
                }
                
                $delay = 100;
                foreach ($tourCategories as $cat):
                    $colorClass = isset($cat['color']) ? $cat['color'] : 'primary';
                ?>
                <!-- <?php echo htmlspecialchars($cat['name']); ?> -->
                <div class="col-10 col-md-5 col-lg-4 flex-shrink-0 snap-center" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
                    <a href="tours.php?category=<?php echo htmlspecialchars($cat['slug']); ?>#tours" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm hover-lift tour-category-card overflow-hidden">
                            <div class="category-img-wrapper" style="height: 220px; overflow: hidden;">
                                <img src="<?php echo htmlspecialchars($cat['image']); ?>" class="w-100 h-100 object-fit-cover transition-transform" alt="<?php echo htmlspecialchars($cat['name']); ?>" loading="lazy">
                            </div>
                            <div class="card-body p-4 text-center">
                                <div class="icon-circle mb-3 mx-auto text-<?php echo $colorClass; ?> bg-light" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                    <i class="fas <?php echo ($cat['slug'] == 'adventure') ? 'fa-mountain' : htmlspecialchars($cat['icon']); ?> fs-4"></i>
                                </div>
                                <h3 class="h5 card-title fw-bold"><?php echo htmlspecialchars($cat['name']); ?></h3>
                                <p class="card-text text-muted small"><?php echo htmlspecialchars($cat['description']); ?></p>
                            </div>
                        </div>
                    </a>
                </div>
                <?php 
                    $delay += 100;
                endforeach; 
                ?>

            </div>
        </div>
    </div>
</section>

<!-- Places to Visit Section -->
<section id="places" class="py-5">
    <div class="container">
        <div class="row mb-5 align-items-end">
            <div class="col-lg-8" data-aos="fade-right">
                <span class="text-primary fw-bold text-uppercase letter-spacing-1">Explore Destinations</span>
                <h2 class="display-5 fw-bold">Places to Visit</h2>
                <p class="text-muted mb-0" style="max-width: 600px;">Discover the breathtaking beauty of Himachal Pradesh's most stunning destinations.</p>
            </div>
            <div class="col-lg-4 text-lg-end" data-aos="fade-left">
                <a href="tours.php" class="btn btn-outline-primary">View All Destinations <i class="fas fa-arrow-right ms-2"></i></a>
            </div>
        </div>
        
        <div class="row g-4">
            <?php 
            // Fallback: If DB is empty, use sample places data
            if (empty($tours)) {
                 $places = [
                    [
                        'id' => 101,
                        'title' => 'Shimla',
                        'description' => 'The Queen of Hills offers colonial charm, scenic beauty, and pleasant weather. Explore Mall Road, Ridge, and Jakhu Temple.',
                        'image' => 'images/placeholder/shimla.jpg',
                        'badge' => 'Popular'
                    ],
                    [
                        'id' => 102,
                        'title' => 'Manali',
                        'description' => 'A paradise for adventure lovers and nature enthusiasts. Experience snow-capped mountains, Solang Valley, and Rohtang Pass.',
                        'image' => 'images/placeholder/manali.jpg',
                        'badge' => 'Trending'
                    ],
                    [
                        'id' => 103,
                        'title' => 'Spiti Valley',
                        'description' => 'The cold desert mountain valley with ancient monasteries, stunning landscapes, and unique Tibetan culture.',
                        'image' => 'images/placeholder/spiti.jpg',
                        'badge' => 'Adventure'
                    ],
                    [
                        'id' => 104,
                        'title' => 'Dharamshala',
                        'description' => 'Home to the Dalai Lama and Tibetan culture. Visit McLeodGanj, Bhagsu Waterfall, and peaceful monasteries.',
                        'image' => 'images/placeholder/spiritual-tours.png',
                        'badge' => 'Spiritual'
                    ],
                    [
                        'id' => 105,
                        'title' => 'Kasol',
                        'description' => 'The mini Israel of India in Parvati Valley. Perfect for trekking, camping, and experiencing hippie culture.',
                        'image' => 'images/placeholder/adventure-tours.png',
                        'badge' => 'Offbeat'
                    ],
                    [
                        'id' => 106,
                        'title' => 'Dalhousie',
                        'description' => 'A charming hill station with colonial architecture, pine forests, and the beautiful Khajjiar meadows.',
                        'image' => 'images/placeholder/honeymoon-tours.png',
                        'badge' => 'Romantic'
                    ]
                ];
            } else {
                // Use tours data as places
                $places = $tours;
            }

            if (empty($places)): ?>
            <div class="col-12 text-center">
                <div class="alert alert-info py-4 rounded-3 shadow-sm border-0">
                    <i class="fas fa-info-circle me-2 fs-5 align-middle"></i> No destinations found. Please check back later.
                </div>
            </div>
            <?php else: ?>
                <?php 
                $i = 0;
                $defaultPlaceImages = [
                    'shimla' => 'images/placeholder/shimla.jpg', 
                    'manali' => 'images/placeholder/manali.jpg', 
                    'spiti' => 'images/placeholder/spiti.jpg',
                    'dharamshala' => 'images/placeholder/spiritual-tours.png',
                    'kasol' => 'images/placeholder/adventure-tours.png',
                    'dalhousie' => 'images/placeholder/honeymoon-tours.png'
                ];
                $placeKeyMap = array_keys($defaultPlaceImages);
                
                foreach ($places as $place): 
                    // Allow both local paths and URLs
                    $imageUrl = !empty($place['image']) ? $place['image'] : $defaultPlaceImages[$placeKeyMap[$i % count($placeKeyMap)]];
                    $i++;
                ?>
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?php echo ($i % 3) * 100; ?>">
                    <div class="card h-100 border-0 shadow-sm hover-lift tour-card overflow-hidden">
                        <a href="tours.php?location=<?php echo urlencode($place['title']); ?>#tours" class="text-decoration-none">
                            <div class="tour-image position-relative" style="height: 280px;">
                                <img src="<?php echo htmlspecialchars($imageUrl); ?>" class="w-100 h-100 object-fit-cover" alt="<?php echo htmlspecialchars($place['title']); ?>" loading="lazy">
                                <div class="card-img-overlay p-3 d-flex flex-column justify-content-between">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <?php if (!empty($place['badge'])): ?>
                                        <span class="badge bg-primary shadow-sm rounded-pill px-3 py-2"><?php echo htmlspecialchars($place['badge']); ?></span>
                                        <?php endif; ?>
                                        <button class="btn btn-light btn-sm rounded-circle shadow-sm favorite-btn" title="Add to Wishlist"><i class="far fa-heart text-danger"></i></button>
                                    </div>
                                </div>
                            </div>
                        </a>
                        <div class="card-body p-4 d-flex flex-column">
                            <a href="tours.php?location=<?php echo urlencode($place['title']); ?>#tours" class="text-decoration-none">
                                <h3 class="h4 fw-bold mb-3"><?php echo htmlspecialchars($place['title']); ?></h3>
                            </a>
                            <p class="text-muted mb-4 flex-grow-1" style="line-height: 1.6;"><?php echo htmlspecialchars($place['description']); ?></p>
                            
                            <div class="mt-auto">
                                <a href="book.php?destination=<?php echo urlencode($place['title']); ?>" class="btn btn-primary w-100 rounded-pill py-2">
                                    <i class="fas fa-calendar-check me-2"></i>Book Now
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
