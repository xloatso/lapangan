const form = document.getElementById('orderForm');
if (form) {
    const modal = document.getElementById('confirmModal');
    const money = value => 'Rp ' + value.toLocaleString('id-ID');
    document.querySelectorAll('[data-filter]').forEach(button => button.addEventListener('click', () => {
        document.querySelectorAll('[data-filter]').forEach(other => {
            const active = other === button;
            other.setAttribute('aria-pressed', String(active));
            other.classList.toggle('bg-blue-600', active);
            other.classList.toggle('text-white', active);
            other.classList.toggle('bg-white', !active);
            other.classList.toggle('text-gray-600', !active);
        });
        document.querySelectorAll('.court-card').forEach(card => {
            card.style.display = button.dataset.filter === 'all' || card.dataset.category === button.dataset.filter ? 'flex' : 'none';
        });
    }));
    function review() {
        const invalid = [...form.querySelectorAll('input, select')].find(input => !input.checkValidity());
        if (invalid) {
            if (invalid.closest('.court-card')) document.querySelector('[data-filter="all"]').click();
            invalid.reportValidity();
            return;
        }
        if (!form.elements.customer_name.value.trim()) {
            form.elements.customer_name.focus();
            return;
        }
        const list = document.getElementById('modalItemList');
        list.replaceChildren();
        let total = 0;
        const hour = Number(form.elements.start_time.value.slice(0, 2));
        const selected = [...form.querySelectorAll('.item-qty')].filter(input => Number(input.value) > 0);
        if (!selected.length) { alert('Pilih minimal satu lapangan dengan durasi lebih dari 0.'); return; }
        if (selected.some(input => hour + Number(input.value) > 22)) {
            alert('Semua reservasi harus selesai paling lambat pukul 22.00.'); return;
        }
        for (const input of selected) {
            const duration = Number(input.value);
            const subtotal = duration * Number(input.dataset.price);
            total += subtotal;
            const item = document.createElement('li');
            item.textContent = `${input.dataset.name} / ${duration} jam (${form.elements.start_time.value}-${String(hour + duration).padStart(2, '0')}:00) - ${money(subtotal)}`;
            list.append(item);
        }
        document.getElementById('modalName').textContent = form.elements.customer_name.value.trim();
        document.getElementById('modalPhone').textContent = form.elements.customer_phone.value;
        document.getElementById('modalSchedule').textContent = `${form.elements.booking_date.value}, mulai ${form.elements.start_time.value}`;
        document.getElementById('modalTotalPrice').textContent = money(total);
        modal.showModal();
    }
    document.getElementById('reviewOrder').addEventListener('click', review);
    document.getElementById('cancelOrder').addEventListener('click', () => modal.close());
    let submitting = false;
    form.addEventListener('submit', event => {
        if (!modal.open) { event.preventDefault(); review(); return; }
        if (submitting) { event.preventDefault(); return; }
        submitting = true;
        modal.querySelector('[type="submit"]').disabled = true;
    });
}
