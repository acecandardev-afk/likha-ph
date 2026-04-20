export function initAdminSalesForm() {
    const saleForm = document.getElementById('saleForm');
    const itemsContainer = document.getElementById('saleItems');
    const addBtn = document.getElementById('addItemBtn');
    const totalDisplay = document.getElementById('totalAmountDisplay');
    if (!saleForm || !itemsContainer || !addBtn || !totalDisplay) return;

    let index = 1;

    function formatPeso(value) {
        return '₱' + Number(value || 0).toFixed(2);
    }

    function recalc() {
        let total = 0;
        itemsContainer.querySelectorAll('.sale-item').forEach(row => {
            const select = row.querySelector('.product-select');
            const qtyInput = row.querySelector('.quantity-input');
            const subtotalDisplay = row.querySelector('.subtotal-display');
            const option = select.options[select.selectedIndex];
            const price = Number(option?.dataset?.price || 0);
            const stock = Number(option?.dataset?.stock || 0);
            const qty = Number(qtyInput.value || 0);

            if (stock > 0) qtyInput.max = stock;
            if (qty > stock && stock > 0) qtyInput.value = stock;

            const subtotal = price * Number(qtyInput.value || 0);
            subtotalDisplay.value = formatPeso(subtotal);
            total += subtotal;
        });
        totalDisplay.value = formatPeso(total);
    }

    function bindRow(row) {
        row.querySelector('.product-select').addEventListener('change', recalc);
        row.querySelector('.quantity-input').addEventListener('input', recalc);
        row.querySelector('.remove-item').addEventListener('click', function () {
            row.remove();
            recalc();
            const rows = itemsContainer.querySelectorAll('.sale-item');
            if (rows.length === 1) rows[0].querySelector('.remove-item').disabled = true;
        });
    }

    addBtn.addEventListener('click', function () {
        const first = itemsContainer.querySelector('.sale-item');
        const clone = first.cloneNode(true);
        clone.querySelectorAll('input, select').forEach(el => {
            if (el.name?.includes('[product_id]')) {
                el.name = `items[${index}][product_id]`;
                el.value = '';
            } else if (el.name?.includes('[quantity]')) {
                el.name = `items[${index}][quantity]`;
                el.value = 1;
            } else if (el.classList.contains('subtotal-display')) {
                el.value = '₱0.00';
            }
        });
        clone.querySelector('.remove-item').disabled = false;
        itemsContainer.appendChild(clone);
        bindRow(clone);
        index++;
        recalc();
    });

    bindRow(itemsContainer.querySelector('.sale-item'));
    recalc();
}
