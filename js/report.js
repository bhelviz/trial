document.addEventListener('DOMContentLoaded', function () {
    const clickables = document.querySelectorAll('.kpi-clickable');
    const container = document.getElementById('reportDetailsContainer');
    const titleSpan = document.getElementById('reportDetailsTitle');
    const loadingDiv = document.getElementById('reportDetailsLoading');
    const contentDiv = document.getElementById('reportDetailsContent');
    const closeBtn = document.getElementById('closeDetailsBtn');
    let dataTableInstance = null;

    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            container.style.display = 'none';
        });
    }

    clickables.forEach(function (el) {
        el.addEventListener('click', function () {
            const owner = el.getAttribute('data-owner');
            const type = el.getAttribute('data-type');
            const title = el.getAttribute('data-title');

            // Set title and show container
            titleSpan.textContent = title;
            container.style.display = 'block';
            loadingDiv.style.display = 'block';
            contentDiv.style.display = 'none';

            // Scroll container into view smoothly
            container.scrollIntoView({ behavior: 'smooth', block: 'start' });

            // Fetch list via AJAX
            fetch(`get_report_details.php?owner=${encodeURIComponent(owner)}&type=${encodeURIComponent(type)}`)
                .then(response => response.text())
                .then(html => {
                    loadingDiv.style.display = 'none';
                    contentDiv.style.display = 'block';
                    contentDiv.innerHTML = html;

                    // Initialize simple-datatables on the dynamic table
                    const tableEl = document.getElementById('reportDetailsTable');
                    if (tableEl && typeof simpleDatatables !== 'undefined') {
                        if (dataTableInstance) {
                            dataTableInstance.destroy();
                        }
                        dataTableInstance = new simpleDatatables.DataTable(tableEl);
                    }
                })
                .catch(err => {
                    loadingDiv.style.display = 'none';
                    contentDiv.style.display = 'block';
                    contentDiv.innerHTML = '<div class="alert alert-danger">Failed to load detailed report list.</div>';
                    console.error('Error fetching details:', err);
                });
        });
    });
});
