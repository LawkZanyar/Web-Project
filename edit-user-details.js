// Handles edit and delete user actions using GET/POST to edit-user-details.php
const params = new URLSearchParams(window.location.search);
const username = params.get('username');
const form = document.getElementById('edit-form');
const nameInput = document.getElementById('edit-name');
const usernameInput = document.getElementById('edit-username');
const emailInput = document.getElementById('edit-email');
const passwordInput = document.getElementById('edit-password');
const roleSelect = document.getElementById('edit-role');
const saveBtn = document.getElementById('save-btn');
const deleteBtn = document.getElementById('delete-btn');
const messageDiv = document.getElementById('edit-user-message');

async function loadUser() {
    try {
        const response = await fetch(`edit-user-details.php?username=${encodeURIComponent(username)}`);
        const data = await response.json();
        if (data.success && data.user) {
            nameInput.value = data.user.name;
            usernameInput.value = data.user.username;
            emailInput.value = data.user.email;
            roleSelect.value = data.user.role;
            // Set the hidden original username field
            const originalUsernameInput = document.getElementById('original-username');
            if (originalUsernameInput) {
                originalUsernameInput.value = data.user.username;
            }
        } else {
            messageDiv.textContent = 'User not found.';
            messageDiv.style.color = 'red';
            form.querySelectorAll('input,select,button').forEach(el => el.disabled = true);
        }
    } catch (error) {
        messageDiv.textContent = 'An error occurred while loading user.';
        messageDiv.style.color = 'red';
        if (error && error.message) {
            messageDiv.textContent += ' ' + error.message;
        }
        form.querySelectorAll('input,select,button').forEach(el => el.disabled = true);
    }
}

if (form && username) loadUser();

// Always try to set the original username from the username input if not loaded by loadUser
const originalUsernameInput = document.getElementById('original-username');
if (originalUsernameInput && !originalUsernameInput.value) {
    originalUsernameInput.value = usernameInput.value;
}

if (saveBtn) saveBtn.onclick = async function() {
    // Trim and validate inputs
    const name = nameInput.value.trim();
    const newUsername = usernameInput.value.trim();
    const email = emailInput.value.trim();
    const role = roleSelect.value;
    // Get the original username from the hidden field
    const originalUsernameInput = document.getElementById('original-username');
    let usernameVal = originalUsernameInput ? originalUsernameInput.value : '';
    // If still empty, fallback to username input
    if (!usernameVal) usernameVal = usernameInput.value.trim();
    // Client-side validation
    if (!name || !newUsername || !email || !role || !usernameVal) {
        messageDiv.textContent = 'Please fill all required fields!';
        messageDiv.style.color = 'red';
        return;
    }
    const password = passwordInput.value;
    const payload = { username: usernameVal, name, newUsername, email, password, role, action: 'save' };
    try {
        const resp = await fetch('edit-user-details.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        let text = await resp.text();
        let result;
        try {
            result = JSON.parse(text);
        } catch (e) {
            messageDiv.textContent = 'Server error: ' + text;
            messageDiv.style.color = 'red';
            return;
        }
        if (result.success) {
            messageDiv.textContent = 'Changes saved!';
            messageDiv.style.color = 'green';
            setTimeout(() => window.location.href = 'edit-user.html', 1200);
        } else {
            messageDiv.textContent = result.message || 'Failed to save changes.';
            messageDiv.style.color = 'red';
        }
    } catch (error) {
        messageDiv.textContent = 'An error occurred while saving.';
        messageDiv.style.color = 'red';
        if (error && error.message) {
            messageDiv.textContent += ' ' + error.message;
        }
    }
};

if (deleteBtn) deleteBtn.onclick = async function() {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        try {
            const resp = await fetch(`edit-user-details.php?username=${encodeURIComponent(username)}&action=delete`);
            const result = await resp.json();
            if (result.success) {
                alert('User "' + username + '" has been deleted.');
                window.location.href = 'admin.html';
            } else {
                messageDiv.textContent = result.message || 'Failed to delete user.';
                messageDiv.style.color = 'red';
            }
        } catch (error) {
            messageDiv.textContent = 'An error occurred while deleting.';
            messageDiv.style.color = 'red';
            if (error && error.message) {
                messageDiv.textContent += ' ' + error.message;
            }
        }
    }
};
