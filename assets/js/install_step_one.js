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

document.addEventListener('DOMContentLoaded', () => {
    const sqlTypeSelect = document.getElementById('sql_type');
    const rootPassword = document.getElementById('root_password');
    const dbSqlLite = document.getElementById('dbsqlite');
    const toggleSqlPassword = document.getElementById('toggleSqlPassword');
    const showSqlPassword = document.getElementById('showSqlPassword');
    const showRootPassWord = document.getElementById('showRootPassWord');
    const toggleRootPassword = document.getElementById('toggleRootPassword');
    const sqlUserControl = Array.from(document.getElementsByClassName('sql_user_control'));
    const createMyOwnDbControl = Array.from(document.getElementsByClassName('create-my-own-db-control'));
    const sqlPassword = document.getElementById('sql_password');
    const useSameUser = document.getElementById('useSameUser');
    const createMyOwnDb = document.getElementById('createMyOwnDb');


    const stepButton = document.getElementById('step-button');

    let currentStep = 1;
    let currentFormSection = 1;

    stepButton.addEventListener('click', function (event) {
        event.preventDefault(); // Prevent form submission

        if (!validateCurrentStep(currentStep)) {
            alert('Please fill in all required fields.');
            return;
        }

        // Save current form inputs to the databaseInfo object
        const currentFormSection = document.querySelector(`[data-step="${currentStep}"]`);
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

        if (currentStep < 3) {
            switchFormStep(currentStep, currentStep + 1);
            currentStep++;
        } else {
            if (databaseInfo.useSameUser) {
                databaseInfo.sql_user = databaseInfo.root_user;
                databaseInfo.sql_password = databaseInfo.root_password;
            }
            console.log('Final step - implement submission or next action here.');
            console.log(databaseInfo); // For debugging, to see the filled object
            sessionStorage.setItem('databaseInfo', JSON.stringify(databaseInfo));
            const currentFormStep = document.querySelector(`[data-form-step=${currentFormSection}]`);
            const nextFormStep = document.querySelector(`[data-form-step=${++currentFormSection}]`);
            console.log(currentFormStep);
            console.log(nextFormStep);
            // switchFormSection(currentFormStep);
        }
    });


    useSameUser.addEventListener('change', function () {
        const isChecked = this.checked;
        if (isChecked) {
            sqlUserControl.map(el => el.classList.add('d-none'));
        } else {
            sqlUserControl.map(el => el.classList.remove('d-none'));
        }
        databaseInfo.useSameUser = isChecked;
    });

    createMyOwnDb.addEventListener('change', function () {
        const isChecked = this.checked;
      if (isChecked) {
        createMyOwnDbControl.map(el => el.classList.remove('d-none'));
      } else {
        createMyOwnDbControl.map(el => el.classList.add('d-none'));
      }
    });

    sqlTypeSelect.addEventListener('change', (ev) => {
        databaseInfo.sql_type = ev.target.value;
        if (ev.target.value === 'sqlite3') {
            dbSqlLite.classList.remove('d-none');
            dbSqlLite.classList.add('d-block');
        } else {
            dbSqlLite.classList.remove('d-block');
            dbSqlLite.classList.add('d-none');
        }
    });

    toggleSqlPassword.addEventListener('click', () => {
        if (sqlPassword.type === 'password') {
            sqlPassword.type = 'text';
            showSqlPassword.className = 'fa fa-eye-slash';
        } else {
            sqlPassword.type = 'password';
            showSqlPassword.className = 'fa fa-eye';
        }
    });

    toggleRootPassword.addEventListener('click', () => {
        if (rootPassword.type === 'password') {
            rootPassword.type = 'text';
            showRootPassWord.className = 'fa fa-eye-slash';
        } else {
            rootPassword.type = 'password';
            showRootPassWord.className = 'fa fa-eye';
        }
    });
});