
// Download Data Popup Functionality
document.addEventListener('DOMContentLoaded', function() {
    const downloadBtn = document.getElementById('download-data-btn');
    const downloadPopup = document.getElementById('download-popup');
    const closePopupBtn = document.getElementById('close-download-popup');
    const overlay = document.getElementById('download-overlay');

    if (downloadBtn) {
        downloadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            downloadPopup.classList.add('active');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }

    if (closePopupBtn) {
        closePopupBtn.addEventListener('click', function() {
            downloadPopup.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function() {
            downloadPopup.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && downloadPopup.classList.contains('active')) {
            downloadPopup.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    // Close popup after clicking download option
    const downloadOptions = document.querySelectorAll('.download-option-btn');
    downloadOptions.forEach(function(option) {
        option.addEventListener('click', function() {
            setTimeout(function() {
                downloadPopup.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }, 500);
        });
    });
});
