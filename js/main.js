// js/main.js

$(document).ready(function(){
    // Beispiel: Öffnen des Besucherdetails-Modals
    $('.view-details').on('click', function(){
        var visitorId = $(this).data('id');
        // AJAX-Anfrage, um Besucherdetails zu laden
        $.ajax({
            url: 'details.php',
            method: 'GET',
            data: { id: visitorId },
            success: function(response){
                $('#visitorDetailsModal .modal-body').html(response);
                $('#visitorDetailsModal').modal('show');
            }
        });
    });
});
