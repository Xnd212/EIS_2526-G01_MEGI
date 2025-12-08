// deleteitem.js - Handle item deletion with confirmation

document.addEventListener('DOMContentLoaded', function() {
  const deleteBtn = document.getElementById('deleteItemBtn');
  const deletePopup = document.getElementById('delete-popup');
  const cancelDeleteBtn = document.getElementById('cancel-delete');
  const confirmDeleteBtn = document.getElementById('confirm-delete');

  if (!deleteBtn) return; // Exit if delete button doesn't exist (not the owner)

  let itemIdToDelete = null;

  // Show delete confirmation popup
  deleteBtn.addEventListener('click', function() {
    itemIdToDelete = this.getAttribute('data-item-id');
    deletePopup.style.display = 'block';
  });

  // Cancel deletion
  cancelDeleteBtn.addEventListener('click', function() {
    deletePopup.style.display = 'none';
    itemIdToDelete = null;
  });

  // Confirm deletion
  confirmDeleteBtn.addEventListener('click', function() {
    if (!itemIdToDelete) return;

    // Disable button to prevent double-clicks
    confirmDeleteBtn.disabled = true;
    confirmDeleteBtn.textContent = 'Deleting...';

    // Send delete request
    fetch('delete_item.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'item_id=' + encodeURIComponent(itemIdToDelete)
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Redirect to items page after successful deletion
        window.location.href = 'myitems.php';
      } else {
        alert('Error deleting item: ' + (data.message || 'Unknown error'));
        confirmDeleteBtn.disabled = false;
        confirmDeleteBtn.textContent = 'Delete';
        deletePopup.style.display = 'none';
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('An error occurred while deleting the item.');
      confirmDeleteBtn.disabled = false;
      confirmDeleteBtn.textContent = 'Delete';
      deletePopup.style.display = 'none';
    });
  });

  // Close popup when clicking outside
  deletePopup.addEventListener('click', function(e) {
    if (e.target === deletePopup) {
      deletePopup.style.display = 'none';
      itemIdToDelete = null;
    }
  });
});