jQuery(document).ready(function ($) {



    // 2. Search Form Submission
    $('#tr-search-form').on('submit', function (e) {
        e.preventDefault();

        // Show results section
        $('#tr-results-section').fadeIn();

        // Scroll to results
        $('html, body').animate({
            scrollTop: $("#tr-results-section").offset().top - 50
        }, 800);

        // Here you would typically make an AJAX call to fetch results
        // For now, we just show the skeleton loader as per the design
        console.log('Searching for availability...');

        // Example: Simulate loading results after 2 seconds
        /*
        setTimeout(function() {
            $('#tr-results-container').html('<p>Resultados cargados...</p>');
        }, 2000);
        */
    });

});
