// Backend API Configuration
// Change this URL to point to your deployed backend
const API_BASE_URL = 'http://14.139.187.229:8081/jan2026/spic741/roadmithra';

// Helper to build backend API URLs
// Usage: apiUrl('road_backend/customer/cart.php') => 'http://14.139.187.229:8081/jan2026/spic741/roadmithra/road_backend/customer/cart.php'
function apiUrl(path) {
    // Remove leading ../ or ./ from path
    path = path.replace(/^(\.\.\/)+/, '').replace(/^\.\//, '');
    return API_BASE_URL + '/' + path;
}
