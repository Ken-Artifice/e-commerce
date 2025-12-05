<?php
/**
 * Category Helper Functions
 * Provides grouped category structure for the craft system
 */

/**
 * Get all categories grouped by type (Material or Purpose)
 * @param mysqli $conn Database connection
 * @return array Grouped categories array
 */
function getGroupedCategories($conn) {
    // Define category groups
    $materialCategories = [
        'Fiber and Textile Crafts',
        'Wood Crafts',
        'Ceramics and Glass Crafts',
        'Metal Crafts',
        'Paper Crafts',
        'Natural Material Crafts'
    ];
    
    $purposeCategories = [
        'Functional Crafts',
        'Decorative Crafts',
        'Fashion Accessories'
    ];
    
    // Get all categories from database
    $result = $conn->query("SELECT * FROM categories ORDER BY name");
    $allCategories = [];
    while ($row = $result->fetch_assoc()) {
        $allCategories[$row['name']] = $row;
    }
    
    // Group categories
    $grouped = [
        'material' => [],
        'purpose' => []
    ];
    
    foreach ($materialCategories as $catName) {
        if (isset($allCategories[$catName])) {
            $grouped['material'][] = $allCategories[$catName];
        }
    }
    
    foreach ($purposeCategories as $catName) {
        if (isset($allCategories[$catName])) {
            $grouped['purpose'][] = $allCategories[$catName];
        }
    }
    
    return $grouped;
}

/**
 * Render grouped category dropdown options
 * @param mysqli $conn Database connection
 * @param int $selected_id Currently selected category ID
 * @param bool $include_all_option Include "All Categories" option
 * @return string HTML options
 */
function renderGroupedCategoryOptions($conn, $selected_id = 0, $include_all_option = false) {
    $grouped = getGroupedCategories($conn);
    $html = '';
    
    if ($include_all_option) {
        $html .= '<option value="">All Categories</option>';
    }
    
    // Categories by Material
    if (!empty($grouped['material'])) {
        $html .= '<optgroup label="Categories by Material">';
        foreach ($grouped['material'] as $cat) {
            $selected = ($selected_id == $cat['id']) ? 'selected' : '';
            $html .= sprintf(
                '<option value="%d" %s>%s</option>',
                $cat['id'],
                $selected,
                htmlspecialchars($cat['name'])
            );
        }
        $html .= '</optgroup>';
    }
    
    // Categories by Purpose
    if (!empty($grouped['purpose'])) {
        $html .= '<optgroup label="Categories by Purpose">';
        foreach ($grouped['purpose'] as $cat) {
            $selected = ($selected_id == $cat['id']) ? 'selected' : '';
            $html .= sprintf(
                '<option value="%d" %s>%s</option>',
                $cat['id'],
                $selected,
                htmlspecialchars($cat['name'])
            );
        }
        $html .= '</optgroup>';
    }
    
    return $html;
}

