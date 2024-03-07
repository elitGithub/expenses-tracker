document.addEventListener('DOMContentLoaded', () => {
    // Simple JavaScript to toggle form visibility
    document.getElementById('show-setup-form').addEventListener('click', function() {
        document.getElementById('expenses-tracker-setup-form').classList.remove('d-none');
        document.getElementById('pre-install-instructions').classList.add('d-none');
    });

    const formHeader = document.getElementsByClassName('form-header');
    const steps = document.getElementsByClassName('stepIndicator');
    console.log(steps);
    Array.from(formHeader).map(el => {
        el.addEventListener('click', (e) => {});
    });

    Array.from(steps).map(el => {
        el.addEventListener('click', (e) => {
            Array.from(steps).map(step => step.classList.remove('active'));
            e.target.classList.add('active');
        });
    });
});
