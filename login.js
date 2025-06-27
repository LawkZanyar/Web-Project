// Handles login form submission using POST to login.php
const messageDiv = document.getElementById('message');
document.querySelector('.form-wrapper').addEventListener('submit', async function(event) {
    event.preventDefault();
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    try {
        const response = await fetch('login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });
        const data = await response.json();
        if (data.success) {
            messageDiv.textContent = 'Login successful!';
            messageDiv.style.display = 'block';
            messageDiv.style.backgroundColor = '#d4edda';
            messageDiv.style.color = '#155724';
            messageDiv.style.border = '1px solid #c3e6cb';
            // Force reflow to ensure message is visible before redirect
            void messageDiv.offsetWidth;
            setTimeout(() => {
                window.location.href = 'Home.html';
            }, 1200);
        } else {
            messageDiv.textContent = data.message || 'Login failed. Please check your credentials.';
            messageDiv.style.display = 'block';
            messageDiv.style.backgroundColor = '#f8d7da';
            messageDiv.style.color = '#721c24';
            messageDiv.style.border = '1px solid #f5c6cb';
        }
    } catch (error) {
        messageDiv.textContent = 'An error occurred. Please try again later.';
        messageDiv.style.display = 'block';
        messageDiv.style.backgroundColor = '#f8d7da';
        messageDiv.style.color = '#721c24';
        messageDiv.style.border = '1px solid #f5c6cb';
    }
});

window.addEventListener('load', function() {
    const loader = document.getElementById('loader-overlay');
    if (loader) loader.classList.add('hide');
});
