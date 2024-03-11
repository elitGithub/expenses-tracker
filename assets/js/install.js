document.addEventListener('DOMContentLoaded', () => {
    const formHeader = Array.from(document.getElementsByClassName('form-header'));
    const steps = Array.from(document.getElementsByClassName('stepIndicator'));
    const stepButton = document.getElementById('step-button');


    // Simple JavaScript to toggle form visibility
    document.getElementById('show-setup-form').addEventListener('click', function() {
        document.getElementById('expenses-tracker-setup-form').classList.remove('d-none');
        document.getElementById('pre-install-instructions').classList.add('d-none');
        steps[0].classList.add('active');
    });

    stepButton.addEventListener('click', (ev) => {
        console.log(ev)
    });

    let currentStep = 1;

    stepButton.addEventListener("click", function(event) {
        event.preventDefault(); // Prevent form submission
        if (!validateCurrentStep(currentStep)) {
            alert("Please fill in all required fields.");
            return;
        }

        if (currentStep < 3) {
            switchFormSection(currentStep, currentStep + 1);
            currentStep++;
        } else {
            // Placeholder for final form submission or next action
            console.log("Final step - implement submission or next action here.");
        }
    });

    function switchFormSection(fromStep, toStep) {
        document.querySelector(`.step[data-step="${fromStep}"]`).classList.add("d-none");
        document.querySelector(`.step[data-step="${toStep}"]`).classList.remove("d-none");
    }

    function validateCurrentStep(step) {
        let isValid = true;
        const currentSection = document.querySelector(`.step[data-step="${step}"]`);
        currentSection.querySelectorAll("input[required], select[required]").forEach(function(input) {
            if (!input.value.trim()) isValid = false;
        });
        return isValid;
    }
});
