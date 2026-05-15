// Sidebar Toggle
document.getElementById('menuToggle').addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('collapsed');
});

// Search Filter Logic
function performSearch() {
    const name = document.getElementById('doctorSearchInput').value.toLowerCase();
    const specialty = document.getElementById('specialtyFilter').value;
    const cards = document.querySelectorAll('.doctor-card');

    cards.forEach(card => {
        const cardName = card.getAttribute('data-name').toLowerCase();
        const cardSpec = card.getAttribute('data-specialty');
        
        const isNameMatch = cardName.includes(name);
        const isSpecMatch = (specialty === 'all' || cardSpec === specialty);

        card.style.display = (isNameMatch && isSpecMatch) ? 'block' : 'none';
    });
}

// Modal Control
const modal = document.getElementById('bookingModal');
function openBookingModal(name) {
    document.getElementById('modalDoctorName').innerText = "Book Appointment: " + name;
    modal.style.display = "block";
}
function closeBookingModal() { modal.style.display = "none"; }
function confirmBooking() { alert("Booking Confirmed!"); closeBookingModal(); }

window.onclick = (e) => { if(e.target == modal) closeBookingModal(); }