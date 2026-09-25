// js/main.js

document.addEventListener('DOMContentLoaded', function() {
    // Dynamische Validierung der Check-In-Formularfelder
    const visitorIdInput = document.getElementById('visitor_id');
    const firstNameInput = document.getElementById('first_name');
    const lastNameInput = document.getElementById('last_name');

    function toggleRequiredFields() {
        if (visitorIdInput.value.trim() !== '') {
            firstNameInput.required = false;
            lastNameInput.required = false;
        } else {
            firstNameInput.required = true;
            lastNameInput.required = true;
        }
    }

    // Initial Check
    toggleRequiredFields();

    // Event Listener für Änderungen im visitor_id Feld
    visitorIdInput.addEventListener('input', toggleRequiredFields);

    // Besucherdetails-Modal-Funktionalität (optional)
    const viewDetailsButtons = document.querySelectorAll('.view-details');
    viewDetailsButtons.forEach(function(button) {
        button.addEventListener('click', function(){
            var visitorId = this.getAttribute('data-id');
            // AJAX-Anfrage, um Besucherdetails zu laden
            fetch('details.php?id=' + visitorId)
                .then(response => response.text())
                .then(data => {
                    document.querySelector('#visitorDetailsModal .modal-body').innerHTML = data;
                    // Öffnen des Modals mit Bootstrap 5
                    var myModal = new bootstrap.Modal(document.getElementById('visitorDetailsModal'));
                    myModal.show();
                })
                .catch(error => {
                    console.error('Fehler beim Laden der Besucherdetails:', error);
                    alert('Fehler beim Laden der Besucherdetails.');
                });
        });
    });
});
