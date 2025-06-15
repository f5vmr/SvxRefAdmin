// Check localStorage for theme preference and apply it
window.onload = function () {
    if (localStorage.getItem('darkMode') === 'enabled') {
        document.body.classList.add('dark-mode');
    } else {
        document.body.classList.add('dark-mode');  // Default to dark mode
    }
};

// Function to toggle dark mode (You can still toggle it in JS code if needed)
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    
    // Save the preference to localStorage
    if (document.body.classList.contains('dark-mode')) {
        localStorage.setItem('darkMode', 'enabled');
    } else {
        localStorage.setItem('darkMode', 'disabled');
    }
}
