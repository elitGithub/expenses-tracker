const form = document.getElementById('expenses-tracker-setup-form');
const steps = Array.from(document.getElementsByClassName('stepIndicator'));
const goBackInstall = document.getElementById('goBackInstall');
let currentSection = 1;
let currentStep = 1;
// Initialize form state
let formState = {
    databaseInfo: {
        sql_type: '',
        sql_server: '',
        sql_port: '',
        root_user: '',
        sql_user: '',
        root_password: '',
        sql_password: '',
        sql_db: 'expense_tracker',
        createMyOwnDb: 'off',
        table_prefix: 'expense_',
        useSameUser: false
    },
    cacheData: {
        user_management: '',
        redis_port: 6379,
        redis_password: '',
        redis_host: '',
        memcache_host: '',
        memcache_user: '',
        memcache_port: 11211,
    },
    userdata: {}
};

goBackInstall?.addEventListener('click', (ev) => {
    ev.preventDefault();
    window.history.back();
});


document.getElementById('show-setup-form').addEventListener('click', showForm);
function updateVisibility() {

    console.log(currentSection);
    document.querySelectorAll('[data-form-section]').forEach(section => {
        const sectionNum = parseInt(section.getAttribute('data-form-section'), 10);
        section.classList.toggle('d-none', sectionNum !== currentSection);
    });

    document.querySelectorAll('[data-step]').forEach(step => {
        const stepSection = parseInt(step.closest('[data-form-section]').getAttribute('data-form-section'), 10);
        const stepNum = parseInt(step.getAttribute('data-step'), 10);
        step.classList.toggle('d-none', stepSection !== currentSection || stepNum !== currentStep);
    });

    // Update button visibility
    document.getElementById('prevBtn').classList.toggle('d-none', currentSection === 1 && currentStep === 1);
}

function updateFormState() {
    const currentInputs = document.querySelector(`[data-form-section="${currentSection}"] [data-step="${currentStep}"]`).querySelectorAll('input, select, textarea');
    currentInputs.forEach(input => {
        // Adjust this logic based on how your formState keys are structured and related to input names
        // check if the input has data-default-* attribute and set the value to that, otherwise set it to empty value
        formState[input.name] = input.getAttribute('data-default-value') || input.value;
    });
}

function nextStep() {
    const nextSectionElement = document.querySelector(`[data-form-section="${currentSection + 1}"]`);
    const currentSectionElement = document.querySelector(`[data-form-section="${currentSection}"]`);
    const nextStepElement = currentSectionElement ? currentSectionElement.querySelector(`[data-step="${currentStep + 1}"]`) : null;

    if (!validateCurrentStep(currentStep)) {
        alert('Please fill all the required fields');
        return;
    }

    if (nextStepElement || nextSectionElement) {
        nextStepElement ? currentStep++ : handleNextSection(nextSectionElement);
        return updateVisibility();
    }

    form.submit();
    updateVisibility();
    return; // End the navigation if there are no more sections/steps
}

function handleNextSection(nextSectionElement) {
    const INITIAL_STEP = 1;

    steps.forEach((step, index) => {
        if (index < currentSection) {
            step.classList.remove('active');
            step.classList.add('done');
            return;
        }

        step.classList.remove('done');
        step.classList.toggle('active', index === currentSection);
    });
    currentSection++;
    currentStep = INITIAL_STEP; // Reset to the first step of the new section
}

const handlePreviousSection = (currentSection) => {
    steps.forEach((step, index) => {
        console.log(step, index, currentSection);
        step.classList.remove('active');
        step.classList.remove('done');
        step.classList.toggle('active', index === currentSection - 1);

        if (index < currentSection) {
            step.classList.add('done');
        }

        if (index === currentSection - 1) {
            step.classList.remove('done');
        }
    });

    currentStep = 1; // Reset to the first step of the previous section
}

function prevStep() {
    if (currentSection > 1) {
        // Move to the previous section and find its last step
        currentSection--;
        const steps = document.querySelectorAll(`[data-form-section="${currentSection}"] [data-step]`);
        currentStep = steps.length; // Assumes steps are sequentially ordered
        handlePreviousSection(currentSection);

        return updateVisibility();
    }

    currentStep--;
    updateVisibility();
}

// Event listeners
document.getElementById('nextBtn').addEventListener('click', () => {
    updateFormState(); // Capture current form inputs
    nextStep(); // Move forward
});

document.getElementById('prevBtn').addEventListener('click', () => {
    prevStep(); // Move backward
});

function showForm() {
    form.classList.remove('d-none');
    console.log(form);
    document.getElementById('pre-install-instructions').classList.add('d-none');
    steps[0].classList.add('active');
}

function validateCurrentStep(step) {
    let isValid = true;
    const currentSection = document.querySelector(`[data-step="${step}"]`); // Simplified the selector here

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
