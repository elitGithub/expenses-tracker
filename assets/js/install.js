const databaseInfo = {
    sql_type: '',
    sql_server: '',
    sql_port: '',
    root_user: '',
    sql_user: '',
    root_password: '',
    sql_password: '',
    sql_db: 'expense_tracker',
    sqltblpre: '',
    useSameUser: false
};
let formInnerStep = 1;
let currentFormStep = 1;

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('show-setup-form').addEventListener('click', showForm);
    document.getElementById('show-setup-form').addEventListener('keydown', (event) => {
        console.log(event.key);
        if (event.key === 'Enter') {
            showForm();
        }
    });
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');

    prevBtn.addEventListener('click', function (event) {
        if (formInnerStep === 1) {
            return;
        }
        event.preventDefault();
        const currentFormSection = document.querySelector(`[data-step="${ formInnerStep }"]`);
        const inputs = currentFormSection.querySelectorAll('input, select'); // Get all inputs and selects in the current section
        switchFormStep(formInnerStep, formInnerStep - 1);
        --formInnerStep;
    });
    nextBtn.addEventListener('click', moveForward);
});

function showForm() {
    const steps = Array.from(document.getElementsByClassName('stepIndicator'));
    document.getElementById('expenses-tracker-setup-form').classList.remove('d-none');
    document.getElementById('pre-install-instructions').classList.add('d-none');
    steps[0].classList.add('active');
}

function moveForward(event) {
    event.preventDefault();
    console.log(formInnerStep);
    if (!validateCurrentStep(formInnerStep)) {
        alert('Please fill in all required fields.');
        return;
    }

    // Save current form inputs to the databaseInfo object
    const currentFormSection = document.querySelector(`[data-step="${ formInnerStep }"]`);
    const inputs = currentFormSection.querySelectorAll('input, select'); // Get all inputs and selects in the current section

    inputs.forEach(input => {
        const { name, value, type, checked } = input; // Destructure for easier access

        // For checkboxes, use checked state; for others, use value
        // This allows for flexibility if you add checkboxes later
        const inputValue = type === 'checkbox' ? checked : value;

        if (name && databaseInfo.hasOwnProperty(name)) {
            databaseInfo[name] = inputValue || databaseInfo[name];
        }
    });

    if (formInnerStep < 3) {
        switchFormStep(formInnerStep, formInnerStep + 1);
        formInnerStep++;
    } else {
        if (databaseInfo.useSameUser) {
            databaseInfo.sql_user = databaseInfo.root_user;
            databaseInfo.sql_password = databaseInfo.root_password;
        }
        console.log('Final step - implement submission or next action here.');
        console.log(databaseInfo); // For debugging, to see the filled object
        sessionStorage.setItem('databaseInfo', JSON.stringify(databaseInfo));
        switchFormSection(currentFormStep, ++currentFormStep);
        formInnerStep = 1;
    }
}

function switchFormSection(currentStep, nextStep) {
    const currentFormSectionStep = document.querySelector(`[data-form-step="${ currentStep }"]`);
    const nextFormStep = document.querySelector(`[data-form-step="${ nextStep }"]`);
    const steps = Array.from(document.getElementsByClassName('stepIndicator'));
    const formHeader = Array.from(document.getElementsByClassName('form-header'));
    formHeader.map(el => el.classList.remove('active'));

    if (currentFormSectionStep) {
        steps[--currentStep].classList.remove('active');
        currentFormSectionStep.classList.add('d-none');
        steps[nextStep].classList.remove('active');
    }

    if (nextFormStep) {
        nextFormStep.classList.remove('d-none');
        steps[--nextStep].classList.add('active');
    }
}

function switchFormStep(fromStep, toStep) {
    const fromStepEl = document.querySelectorAll(`[data-step="${ fromStep }"]`)[0];
    const toStepEl = document.querySelectorAll(`[data-step="${ toStep }"]`)[0];
    fromStepEl.classList.add('d-none');
    toStepEl.classList.remove('d-none');
}

function validateCurrentStep(step) {
    let isValid = true;
    const currentSection = document.querySelector(`[data-step="${ step }"]`); // Simplified the selector here

    currentSection.querySelectorAll('input[required], select[required]').forEach(function (input) {
        // Check if the input itself or any of its parents have the 'd-none' class
        let isHidden = input.classList.contains('d-none') || input.closest('.d-none');

        // Only validate if the input is not hidden
        if (!isHidden && !input.value.trim()) {
            isValid = false;
        }
    });

    return isValid;
}
