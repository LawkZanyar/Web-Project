// Handles new application form submission using POST to new-form.php
document.addEventListener('DOMContentLoaded', function() {
    const newForm = document.querySelector('.form-wrapper');
    if (newForm) {
        newForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            const title = document.getElementById('title').value.trim();
            const description = document.getElementById('description').value.trim();

            if (!title) {
                alert('Title is required!');
                return;
            }

            try {
                const response = await fetch('new-form.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ title, description })
                });
                const data = await response.json();
                if (data.success) {
                    alert('Application submitted successfully!');
                    newForm.reset();
                } else {
                    alert(data.message || 'Failed to submit application.');
                }
            } catch (error) {
                console.error('Submission error:', error);
                alert('Network error. Please try again.');
            }
        });
    }
});
