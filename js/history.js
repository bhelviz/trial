document.addEventListener('DOMContentLoaded', function () {
    var itemTrailModal = document.getElementById('itemTrailModal');
    if (itemTrailModal) {
        itemTrailModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var gatepassNo = button.getAttribute('data-bs-gatepass');
            var description = button.getAttribute('data-bs-description');
            var qty = button.getAttribute('data-bs-qty');
            var trail = button.getAttribute('data-bs-trail');
            
            var modalTitle = itemTrailModal.querySelector('#itemTrailModalLabel');
            var modalDesc = itemTrailModal.querySelector('#modalItemDescription');
            var modalQty = itemTrailModal.querySelector('#modalItemQty');
            var modalTrail = itemTrailModal.querySelector('#modalItemTrail');
            
            if (modalTitle) {
                modalTitle.textContent = 'Item Movement Trail - Gatepass #' + gatepassNo;
            }
            if (modalDesc) {
                modalDesc.textContent = description;
            }
            if (modalQty) {
                modalQty.textContent = qty;
            }
            if (modalTrail) {
                modalTrail.textContent = trail;
            }
        });
    }

    var gatepassTrailModal = document.getElementById('gatepassTrailModal');
    if (gatepassTrailModal) {
        gatepassTrailModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var gpslno = button.getAttribute('data-bs-gatepass');
            
            var modalTitle = gatepassTrailModal.querySelector('#gatepassTrailModalLabel');
            if (modalTitle) {
                modalTitle.textContent = 'Gatepass Lifecycle Trail - Gatepass #' + gpslno;
            }
            
            var loadingDiv = gatepassTrailModal.querySelector('#gatepassTrailLoading');
            var contentDiv = gatepassTrailModal.querySelector('#gatepassTrailContent');
            
            if (loadingDiv) loadingDiv.style.display = 'block';
            if (contentDiv) contentDiv.style.display = 'none';
            
            fetch('get_gatepass_trail.php?gpslno=' + gpslno)
                .then(response => response.json())
                .then(data => {
                    if (loadingDiv) loadingDiv.style.display = 'none';
                    if (contentDiv) {
                        contentDiv.style.display = 'block';
                        
                        if (data.error) {
                            contentDiv.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
                            return;
                        }
                        
                        var html = '<ul class="gp-timeline">';
                        data.events.forEach(function(ev) {
                            var badgeClass = ev.type || 'created';
                            var iconHtml = '<i class="fas fa-circle"></i>';
                            
                            if (badgeClass === 'created') iconHtml = '<i class="fas fa-plus-circle"></i>';
                            else if (badgeClass === 'recommended') iconHtml = '<i class="fas fa-check"></i>';
                            else if (badgeClass === 'approved') iconHtml = '<i class="fas fa-thumbs-up"></i>';
                            else if (badgeClass === 'security_movement') iconHtml = '<i class="fas fa-shield-alt"></i>';
                            else if (badgeClass === 'return_requested') iconHtml = '<i class="fas fa-undo"></i>';
                            else if (badgeClass === 'return_recommended') iconHtml = '<i class="fas fa-clipboard-check"></i>';
                            else if (badgeClass === 'return_approved') iconHtml = '<i class="fas fa-check-double"></i>';
                            else if (badgeClass === 'return_security_movement') iconHtml = '<i class="fas fa-exchange-alt"></i>';
                            else if (badgeClass === 'rejected' || badgeClass === 'return_rejected') iconHtml = '<i class="fas fa-times-circle"></i>';
                            
                            var timeStr = ev.time ? ' <span class="gp-timeline-time float-end"><i class="far fa-clock me-1"></i>' + ev.time + '</span>' : ' <span class="gp-timeline-time float-end text-warning"><i class="fas fa-hourglass-half me-1"></i>Pending</span>';
                            
                            html += '<li class="gp-timeline-item">';
                            html += '  <div class="gp-timeline-badge ' + badgeClass + '">' + iconHtml + '</div>';
                            html += '  <div class="gp-timeline-content">';
                            html += timeStr;
                            html += '    <div class="gp-timeline-title">' + ev.title + '</div>';
                            html += '    <div class="gp-timeline-details">' + ev.details + '</div>';
                            if (ev.remark) {
                                html += '    <div class="gp-timeline-remark"><strong>Remark:</strong> ' + ev.remark + '</div>';
                            }
                            html += '  </div>';
                            html += '</li>';
                        });
                        html += '</ul>';
                        contentDiv.innerHTML = html;
                    }
                })
                .catch(err => {
                    if (loadingDiv) loadingDiv.style.display = 'none';
                    if (contentDiv) {
                        contentDiv.style.display = 'block';
                        contentDiv.innerHTML = '<div class="alert alert-danger">Failed to fetch lifecycle trail.</div>';
                    }
                    console.log(err);
                });
        });
    }
});