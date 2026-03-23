function filterRegisteredUsersTable() {
    const input = document.getElementById('searchRegisteredUsers');
    const filter = input.value.toUpperCase();
    const table = document.getElementById('registeredUsersTable');
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const match = Array.from(cells).some(cell => 
            cell.textContent.toUpperCase().includes(filter)
        );
        row.style.display = match ? '' : 'none';
    });
}

function filterWalkInUsersTable() {
    const input = document.getElementById('searchWalkInUsers');
    const filter = input.value.toUpperCase();
    const table = document.getElementById('walkInUsersTable');
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const match = Array.from(cells).some(cell => 
            cell.textContent.toUpperCase().includes(filter)
        );
        row.style.display = match ? '' : 'none';
    });
}
