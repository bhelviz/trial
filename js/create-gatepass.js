document.addEventListener('DOMContentLoaded', function () {
    const itemsContainer = document.getElementById('itemsContainer');
    const addItemBtn = document.getElementById('addItemBtn');
    if (!itemsContainer || !addItemBtn) return;
    const maxItems = 25;

    function updateItemIndices() {
        const rows = itemsContainer.querySelectorAll('.item-row');
        rows.forEach((row, index) => {
            const num = index + 1;

            const descInput = row.querySelector('.item-desc');
            const descLabel = row.querySelector('.item-desc-label');
            if (descInput) {
                descInput.id = `desc_${num}`;
            }
            if (descLabel) {
                descLabel.setAttribute('for', `desc_${num}`);
                descLabel.textContent = `${num}. Item Description`;
            }

            const qtyInput = row.querySelector('.item-qty');
            const qtyLabel = row.querySelector('.item-qty-label');
            if (qtyInput) {
                qtyInput.id = `qty_${num}`;
            }
            if (qtyLabel) {
                qtyLabel.setAttribute('for', `qty_${num}`);
                qtyLabel.textContent = `${num}. Item Quantity`;
            }

            const uomInput = row.querySelector('.item-uom');
            const uomLabel = row.querySelector('.item-uom-label');
            if (uomInput) {
                uomInput.id = `uom_${num}`;
            }
            if (uomLabel) {
                uomLabel.setAttribute('for', `uom_${num}`);
                uomLabel.textContent = `${num}. UOM`;
            }

            if (num === 1) {
                if (descInput) descInput.required = true;
                if (qtyInput) qtyInput.required = true;
                if (uomInput) uomInput.required = true;
            } else {
                if (descInput) descInput.required = false;
                if (qtyInput) qtyInput.required = false;
                if (uomInput) uomInput.required = false;
            }

            const removeBtn = row.querySelector('.remove-item-btn');
            if (removeBtn) {
                if (rows.length > 1) {
                    removeBtn.style.display = 'block';
                } else {
                    removeBtn.style.display = 'none';
                }
            }
        });

        if (rows.length >= maxItems) {
            addItemBtn.style.display = 'none';
        } else {
            addItemBtn.style.display = 'inline-block';
        }
    }

    addItemBtn.addEventListener('click', function () {
        const rows = itemsContainer.querySelectorAll('.item-row');
        if (rows.length < maxItems) {
            const firstRow = rows[0];
            const newRow = firstRow.cloneNode(true);

            const descInput = newRow.querySelector('.item-desc');
            const qtyInput = newRow.querySelector('.item-qty');
            const uomInput = newRow.querySelector('.item-uom');
            if (descInput) descInput.value = '';
            if (qtyInput) qtyInput.value = '';
            if (uomInput) uomInput.value = '';

            const removeBtn = newRow.querySelector('.remove-item-btn');
            if (removeBtn) {
                removeBtn.addEventListener('click', function () {
                    newRow.remove();
                    updateItemIndices();
                });
            }

            itemsContainer.appendChild(newRow);
            updateItemIndices();
        }
    });

    const initialRemoveBtn = itemsContainer.querySelector('.remove-item-btn');
    if (initialRemoveBtn) {
        initialRemoveBtn.addEventListener('click', function () {
            const row = this.closest('.item-row');
            row.remove();
            updateItemIndices();
        });
    }

    // Dynamic min limits for date and return date inputs
    const dateInput = document.getElementById('date');
    const returnDateInput = document.getElementById('returnDate');
    if (dateInput && returnDateInput) {
        const todayStr = new Date().toISOString().split('T')[0];
        dateInput.min = todayStr;
        
        function updateReturnDateMin() {
            const baseDateVal = dateInput.value || todayStr;
            let baseDate = new Date(baseDateVal);
            baseDate.setDate(baseDate.getDate() + 1);
            const yyyy = baseDate.getFullYear();
            const mm = String(baseDate.getMonth() + 1).padStart(2, '0');
            const dd = String(baseDate.getDate()).padStart(2, '0');
            returnDateInput.min = `${yyyy}-${mm}-${dd}`;
            
            if (returnDateInput.value && returnDateInput.value <= dateInput.value) {
                returnDateInput.value = '';
            }
        }
        
        dateInput.addEventListener('change', updateReturnDateMin);
        updateReturnDateMin();
    }

    updateItemIndices();

    const form = document.forms.f1;

    if (form) {
        form.addEventListener('submit', (e) => {
            if (!confirm('Are you sure you want to create this Material Gatepass?')) {
                e.preventDefault();
            }
        });
    }
});