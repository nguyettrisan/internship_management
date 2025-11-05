document.addEventListener('DOMContentLoaded', function() {
    const entryDateInput = document.getElementById('entry_date');
    const monthsStayInput = document.getElementById('months_stay');
    const returnDateInput = document.getElementById('return_date');

    function calculateReturnDate() {
        const entryDateValue = entryDateInput.value;
        const monthsStayValue = parseInt(monthsStayInput.value, 10);

        if (entryDateValue && !isNaN(monthsStayValue) && monthsStayValue >= 0) {
            const entryDate = new Date(entryDateValue);
            entryDate.setMonth(entryDate.getMonth() + monthsStayValue);

            const year = entryDate.getFullYear();
            const month = (entryDate.getMonth() + 1).toString().padStart(2, '0');
            const day = entryDate.getDate().toString().padStart(2, '0');

            returnDateInput.value = `${year}-${month}-${day}`;
        } else {
            returnDateInput.value = '';
        }
    }

    entryDateInput.addEventListener('change', calculateReturnDate);
    monthsStayInput.addEventListener('input', calculateReturnDate);
});