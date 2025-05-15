<?php
/**
 * Generate an image with user's initials
 * 
 * @param string $first_name First name of the user
 * @param string $last_name Last name of the user
 * @param int $size Size of the image in pixels (default: 200)
 * @return string HTML/CSS data for the initials
 */
function generate_initials_image($first_name, $last_name, $size = 200) {
    // Get initials
    $initials = strtoupper(substr($first_name, 0, 1) . substr($last_name, 0, 1));
    if (empty($initials)) {
        $initials = "U"; // Default to "U" for User if no name is provided
    }
    
    // Generate background color based on name (for consistent color per user)
    $hash = md5($first_name . $last_name);
    $r = hexdec(substr($hash, 0, 2));
    $g = hexdec(substr($hash, 2, 2));
    $b = hexdec(substr($hash, 4, 2));
    
    // Make sure the background color isn't too light (for contrast with white text)
    $r = ($r % 156) + 50;
    $g = ($g % 156) + 50;
    $b = ($b % 156) + 50;
    
    $bgcolor = sprintf("#%02x%02x%02x", $r, $g, $b);
    
    // For API calls, we'll return a URL to a service that generates avatars
    return "https://ui-avatars.com/api/?name=" . urlencode($first_name . '+' . $last_name) . 
           "&background=" . urlencode(substr($bgcolor, 1)) . 
           "&color=fff&size=" . $size;
}

/**
 * Get the profile image for a user
 * 
 * @param string $profile_photo Stored profile photo filename
 * @param string $first_name First name of the user
 * @param string $last_name Last name of the user
 * @param int $size Size of the image in pixels (default: 200)
 * @return string Image URL
 */
function get_profile_image($profile_photo, $first_name, $last_name, $size = 200) {
    // Check if the user has a custom profile photo
    if (!empty($profile_photo) && $profile_photo !== 'default.jpg') {
        // First check if the file exists in the expected location
        $root_path = $_SERVER['DOCUMENT_ROOT'] . '/Trusticle/assets/images/profiles/' . $profile_photo;
        $user_path = $_SERVER['DOCUMENT_ROOT'] . '/Trusticle/user/assets/images/profiles/' . $profile_photo;
        
        // If the image exists in user/assets, use that path
        if (file_exists($user_path)) {
            return '../assets/images/profiles/' . $profile_photo;
        }
        
        // Default to the root assets path
        return '../../assets/images/profiles/' . $profile_photo;
    }
    
    // Generate and return initials image URL
    return generate_initials_image($first_name, $last_name, $size);
}
?> 