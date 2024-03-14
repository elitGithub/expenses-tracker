document.addEventListener('DOMContentLoaded', () => {
    const formHeader = Array.from(document.getElementsByClassName('form-header'));
    const steps = Array.from(document.getElementsByClassName('stepIndicator'));
    // Simple JavaScript to toggle form visibility

    document.getElementById('show-setup-form').addEventListener('click', function () {
        document.getElementById('expenses-tracker-setup-form').classList.remove('d-none');
        document.getElementById('pre-install-instructions').classList.add('d-none');
        steps[0].classList.add('active');
    });
});


function switchFormSection(currentStep, nextStep) {
    const currentStepDiv = document.getElementById(`step${currentStep}`);
    const nextStepDiv = document.getElementById(`step${nextStep}`);

    // Hide current step
    if (currentStepDiv) {
        currentStepDiv.classList.add('d-none');
    }

    // Show next step
    if (nextStepDiv) {
        nextStepDiv.classList.remove('d-none');
    }
}


function switchFormStep (fromStep, toStep) {
    const fromStepEl = document.querySelectorAll(`[data-step="${ fromStep }"]`)[0];
    const toStepEl = document.querySelectorAll(`[data-step="${ toStep }"]`)[0];
    fromStepEl.classList.add('d-none');
    toStepEl.classList.remove('d-none');
}

function validateCurrentStep(step) {
    let isValid = true;
    const currentSection = document.querySelector(`[data-step="${step}"]`); // Simplified the selector here

    currentSection.querySelectorAll('input[required], select[required]').forEach(function(input) {
        // Check if the input itself or any of its parents have the 'd-none' class
        let isHidden = input.classList.contains('d-none') || input.closest('.d-none');

        // Only validate if the input is not hidden
        if (!isHidden && !input.value.trim()) {
            isValid = false;
        }
    });

    return isValid;
}