const addUserForm = document.querySelector('.form-wrapper');
if (addUserForm) {
    addUserForm.addEventListener('submit', async function(event) {
        event.preventDefault();
        const fullname = document.getElementById('fullname').value;
        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        let role = document.getElementById('role').value;
        
        // Convert role to lowercase before sending
        role = role.toLowerCase();
        
        const payload = { fullname, username, email, password, role };
        try {
            const response = await fetch('add-user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await response.json();
            if (data.success) {
                alert('User created successfully!');
                addUserForm.reset();
            } else {
                alert(data.message || 'Failed to create user.');
            }
        } catch (error) {
            alert('An error occurred.');
        }
    });
}