<?php
/**
 * Database-related functions for Shimla Air Lines
 * This file contains functions for loading and manipulating JSON data files
 */

// Define data directory
define('DATA_DIR', __DIR__ . '/../data/');

/**
 * Ensure data directory exists
 */
function initDataDirectory() {
    if (!file_exists(DATA_DIR)) {
        mkdir(DATA_DIR, 0777, true);
    }
}

/**
 * Load data from a JSON file
 * 
 * @param string $filename The name of the JSON file to load
 * @return array The data from the JSON file
 */
function loadJsonData($filename) {
    initDataDirectory();
    $filePath = DATA_DIR . $filename;
    
    if (file_exists($filePath)) {
        $data = json_decode(file_get_contents($filePath), true);
        return is_array($data) ? $data : [];
    }
    
    return [];
}

/**
 * Save data to a JSON file
 * 
 * @param string $filename The name of the JSON file to save to
 * @param array $data The data to save
 * @return bool True if successful, false otherwise
 */
function saveJsonData($filename, $data) {
    initDataDirectory();
    $filePath = DATA_DIR . $filename;
    
    return file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
}

/**
 * Get tours data
 * 
 * @param string|null $destinationFilter Optional destination to filter by
 * @return array Array of tours
 */
function getTours($destinationFilter = null) {
    $tours = loadJsonData('tours.json');
    
    if (empty($tours)) {
        // Initialize with default data if empty
        $tours = [
            [
                'id' => 1,
                'title' => 'Shimla Adventure',
                'description' => 'Experience the queen of hills with our curated tour package including Mall Road, Ridge, and surrounding areas.',
                'duration' => '3 Days',
                'price' => 5999,
                'destination' => 'shimla',
                'image' => 'https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?q=80&w=2070&auto=format&fit=crop',
                'badge' => 'Best Seller',
                'rating' => 4.8,
                'features' => ['4-Star Stay', 'Meals', 'Transport', 'Guided Tours']
            ],
            [
                'id' => 2,
                'title' => 'Manali Escape',
                'description' => 'Enjoy the beauty and adventure of Manali with visits to Solang Valley, Rohtang Pass, and local attractions.',
                'duration' => '4 Days',
                'price' => 7499,
                'destination' => 'manali',
                'image' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?q=80&w=2070&auto=format&fit=crop',
                'badge' => 'Popular',
                'rating' => 4.9,
                'features' => ['3-Star Stay', 'Meals', 'Transport', 'Adventure Activities']
            ],
            [
                'id' => 3,
                'title' => 'Spiti Valley Trek',
                'description' => 'Explore the cold desert mountain valley of Spiti with stays in local villages and monastery visits.',
                'duration' => '7 Days',
                'price' => 12999,
                'destination' => 'spiti',
                'image' => 'https://images.unsplash.com/photo-1593181629936-11c609125b3c?q=80&w=1974&auto=format&fit=crop',
                'badge' => 'Adventure',
                'rating' => 4.7,
                'features' => ['Homestay', 'Meals', 'Transport', 'Guide', 'Permits']
            ]
        ];
        
        // Save the default data
        saveJsonData('tours.json', $tours);
    }
    
    // Apply destination filter if provided
    if ($destinationFilter) {
        $tours = array_filter($tours, function($tour) use ($destinationFilter) {
            return isset($tour['destination']) && strtolower($tour['destination']) === strtolower($destinationFilter);
        });
    }
    
    return $tours;
}

/**
 * Get all available tours
 * @return array Array of tour data or empty array if none found
 */
function getAllTours() {
    $tours = loadJsonData('tours.json');
    
    if (!$tours) {
        // Return default tours if the file doesn't exist or is empty
        return [
            [
                'id' => 'shimla-adventure',
                'title' => 'Shimla Adventure',
                'price' => '12999'
            ],
            [
                'id' => 'manali-escape',
                'title' => 'Manali Escape',
                'price' => '14999'
            ],
            [
                'id' => 'dharamshala-retreat',
                'title' => 'Dharamshala Retreat',
                'price' => '13499'
            ],
            [
                'id' => 'spiti-valley-trek',
                'title' => 'Spiti Valley Trek',
                'price' => '18999'
            ],
            [
                'id' => 'leh-ladakh-expedition',
                'title' => 'Leh Ladakh Expedition',
                'price' => '21999'
            ],
            [
                'id' => 'custom-tour',
                'title' => 'Custom Tour',
                'price' => 'Custom'
            ]
        ];
    }
    
    return $tours;
}

/**
 * Get vehicles data
 * 
 * @return array Array of vehicles
 */
function getVehicles() {
    $vehicles = loadJsonData('vehicles.json');
    
    if (empty($vehicles)) {
        // Initialize with default data if empty
        $vehicles = [
            [
                'id' => 1,
                'name' => 'Desire',
                'description' => 'Comfortable sedan for small groups',
                'seats' => 4,
                'bags' => 2,
                'image' => 'https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?q=80&w=1000&auto=format&fit=crop',
                'features' => ['AC', 'Bluetooth', 'Power Windows', 'USB Charging']
            ],
            [
                'id' => 2,
                'name' => 'Innova Crysta',
                'description' => 'Premium SUV for family trips',
                'seats' => 7,
                'bags' => 4,
                'image' => 'https://images.unsplash.com/photo-1609941897813-21d1c6636190?q=80&w=1000&auto=format&fit=crop',
                'features' => ['AC', 'Entertainment System', 'Comfortable Seating', 'Ample Luggage Space', 'USB Charging']
            ],
            [
                'id' => 3,
                'name' => 'Ertiga',
                'description' => 'Spacious MUV for group travel',
                'seats' => 7,
                'bags' => 3,
                'image' => 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?q=80&w=1000&auto=format&fit=crop',
                'features' => ['AC', 'Power Windows', 'Comfortable Seating', 'USB Charging']
            ],
            [
                'id' => 4,
                'name' => 'Tempo Traveler',
                'description' => 'For larger groups and tours',
                'seats' => 12,
                'bags' => 10,
                'image' => 'https://images.unsplash.com/photo-1580273916550-e323be2ae537?q=80&w=1000&auto=format&fit=crop',
                'features' => ['AC', 'Entertainment System', 'Push Back Seats', 'Ample Luggage Space', 'USB Charging']
            ]
        ];
        
        // Save the default data
        saveJsonData('vehicles.json', $vehicles);
    }
    
    return $vehicles;
}

/**
 * Add a booking to the bookings data
 * 
 * @param array $bookingData Booking data to add
 * @return int|boolean The ID of the new booking if successful, false otherwise
 */
function addBooking($bookingData) {
    $bookings = loadJsonData('bookings.json');
    
    // Generate a new ID
    $maxId = 0;
    foreach ($bookings as $booking) {
        if ($booking['id'] > $maxId) {
            $maxId = $booking['id'];
        }
    }
    
    // Add the new booking
    $bookingData['id'] = $maxId + 1;
    $bookingData['created_at'] = date('Y-m-d H:i:s');
    $bookingData['status'] = 'pending';
    
    $bookings[] = $bookingData;
    
    if (saveJsonData('bookings.json', $bookings)) {
        return $bookingData['id'];
    }
    
    return false;
}
