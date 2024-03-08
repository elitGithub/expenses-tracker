document.addEventListener('DOMContentLoaded', () => {
    const formHeader = Array.from(document.getElementsByClassName('form-header'));
    const steps = Array.from(document.getElementsByClassName('stepIndicator'));
    // Simple JavaScript to toggle form visibility
    document.getElementById('show-setup-form').addEventListener('click', function() {
        document.getElementById('expenses-tracker-setup-form').classList.remove('d-none');
        document.getElementById('pre-install-instructions').classList.add('d-none');
        steps[0].classList.add('active');
    });

    formHeader.map(el => {
        el.addEventListener('click', (e) => {
            console.log(e.target)
        });
    });

    steps.map(el => {
        el.addEventListener('click', (e) => {
            steps.map(step => step.classList.remove('active'));
            e.target.classList.add('active');
        });
    });
});
