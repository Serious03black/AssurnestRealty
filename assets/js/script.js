// script.js - Basic client-side enhancements for the real estate app
// (Nothing fancy — just useful small improvements)

// 1. Confirm before updating property status (prevents accidental clicks)
document.addEventListener('DOMContentLoaded', function () {
    const statusForms = document.querySelectorAll('form[action][method="POST"]');

    statusForms.forEach(form => {
        // Only target status update forms (they have a select + button)
        if (form.querySelector('select[name="status"]')) {
            form.addEventListener('submit', function (e) {
                const newStatus = form.querySelector('select[name="status"]').value;
                const currentStatus = form.closest('tr')?.querySelector('td:nth-child(7)')?.textContent.trim() || 'unknown';

                if (newStatus !== currentStatus) {
                    const confirmed = confirm(`Are you sure you want to change status from "${currentStatus}" to "${newStatus}"?`);
                    if (!confirmed) {
                        e.preventDefault();
                    }
                }
            });
        }

        // Optional: Confirm sale assignment
        if (form.querySelector('select[name="user_id"]')) {
            form.addEventListener('submit', function (e) {
                const selectedUser = form.querySelector('select[name="user_id"]').options[form.querySelector('select[name="user_id"]').selectedIndex].text;
                const confirmed = confirm(`Assign this sold property to ${selectedUser}? This cannot be undone easily.`);
                if (!confirmed) {
                    e.preventDefault();
                }
            });
        }
    });

    // 2. Simple table search/filter (for property lists)
    const searchInput = document.getElementById('table-search');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const filter = searchInput.value.toLowerCase();
            const rows = document.querySelectorAll('table tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }

    // 3. Highlight current user's row in leaderboard (if username is known)
    const currentUser = document.querySelector('meta[name="current-username"]')?.content;
    if (currentUser) {
        document.querySelectorAll('table tr td').forEach(cell => {
            if (cell.textContent.trim() === currentUser) {
                cell.parentElement.style.backgroundColor = '#e8f4fd';
                cell.parentElement.style.fontWeight = 'bold';
            }
        });
    }

    // 4. Auto-refresh dashboard every 60 seconds (optional - remove if not wanted)
    // setTimeout(() => location.reload(), 60000);
});

// Optional helper: Format price with commas (Indian style)
function formatPrice(num) {
    return Number(num).toLocaleString('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });
}

// Example usage: Call on page load if you have price cells with class="price"
document.querySelectorAll('.price').forEach(el => {
    const value = parseFloat(el.textContent.replace(/[^0-9.-]+/g,""));
    if (!isNaN(value)) {
        el.textContent = formatPrice(value);
    }
});