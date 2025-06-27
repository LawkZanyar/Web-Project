// Handles user search form using GET to edit-user.php
const editUserForm = document.querySelector('.form-wrapper');
const searchInput = document.getElementById('search-user');
if (editUserForm && searchInput) {
    editUserForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const value = searchInput.value.trim().toLowerCase();
        const params = new URLSearchParams({ query: value });
        try {
            const response = await fetch(`edit-user.php?${params.toString()}`);
            const data = await response.json();
            let editFields = document.getElementById('edit-fields');
            if (editFields) editFields.remove();
            if (data.success && data.user) {
                editFields = document.createElement('div');
                editFields.id = 'edit-fields';
                editFields.innerHTML = `
                    <div class="form-group">
                        <label>User found:</label>
                        <a href="edit-user-details.html" style="color:#4f46e5;text-decoration:underline;cursor:pointer;">
                            ${data.user.username} (${data.user.email})
                        </a>
                    </div>
                `;
                editUserForm.appendChild(editFields);
            } else {
                alert(data.message || 'User not found!');
            }
        } catch (error) {
            alert('An error occurred.');
        }
    });
}
