function toggleWishlist(btn, itemType, itemId, itemName) {
    // Basic animation/feedback
    const icon = btn.querySelector('i');
    const originalClass = icon.className;
    icon.className = 'spinner-border spinner-border-sm';

    // FormData for the request
    const formData = new FormData();
    formData.append('item_type', itemType);
    formData.append('item_id', itemId);
    formData.append('item_name', itemName);

    // Determine the path to the toggle_wishlist.php
    // Since this script is included from different folders, we need to handle paths carefully.
    // Most search pages are in subfolders like flights/, hotels/, etc.
    // toggle_wishlist.php is in user/
    // If the current page is in a subfolder (flights, hotels, etc.), then the path is ../user/toggle_wishlist.php
    // If it's in the root (index.php), then it's user/toggle_wishlist.php
    
    let path = '../user/toggle_wishlist.php';
    if (window.location.pathname.endsWith('index.php') || window.location.pathname.endsWith('/')) {
        path = 'user/toggle_wishlist.php';
    }

    fetch(path, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.login_required) {
            alert('Please login to add items to your wishlist.');
            // Determine login path
            let loginPath = '../user/login.html';
            if (window.location.pathname.endsWith('index.php') || window.location.pathname.endsWith('/')) {
                loginPath = 'user/login.html';
            }
            window.location.href = loginPath;
            return;
        }

        if (data.success) {
            if (data.action === 'added') {
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-danger');
                icon.className = 'bi bi-heart-fill';
            } else {
                btn.classList.remove('btn-danger');
                btn.classList.add('btn-outline-secondary');
                icon.className = 'bi bi-heart';
            }
        } else {
            alert(data.message || 'Something went wrong.');
            icon.className = originalClass;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Could not update wishlist. Please check your connection.');
        icon.className = originalClass;
    });
}
