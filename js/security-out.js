document.addEventListener('DOMContentLoaded', function () {
    var itemDetailModal = document.getElementById('itemDetailModal');
    if (itemDetailModal) {
        itemDetailModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var content = button.getAttribute('data-bs-content');
            var gatepassNo = button.getAttribute('data-bs-gatepass');
            var modalBody = itemDetailModal.querySelector('#itemDetailModalBody');
            if (modalBody) {
                modalBody.textContent = content;
            }
            var modalTitle = itemDetailModal.querySelector('#itemDetailModalLabel');
            if (modalTitle) {
                modalTitle.textContent = 'Item Details - Gatepass #' + gatepassNo;
            }
        });
    }
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', e => {
            if (!confirm('Are you sure you want to log OUT movement?')) {
                e.preventDefault();
            }
        });
    });
});