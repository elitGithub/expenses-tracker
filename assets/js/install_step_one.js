const databaseInfo = {
    sql_type: '',
    sql_server: '',
    sql_port: '',
    root_user: '',
    sql_user: '',
    root_password: '',
    sql_password: '',
    sql_db: '',
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
                databaseInfo[name] = inputValue;
            }
        });

        if (currentStep < 3) {
            switchFormSection(currentStep, currentStep + 1);
            currentStep++;
        } else {
            if (databaseInfo.useSameUser) {
                databaseInfo.sql_user = databaseInfo.root_user;
                databaseInfo.sql_password = databaseInfo.root_password;
            }
            console.log('Final step - implement submission or next action here.');
            console.log(databaseInfo); // For debugging, to see the filled object
        }
    });


    function switchFormSection (fromStep, toStep) {
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
//
// document.addEventListener('DOMContentLoaded', function () {
//     const stepDiv = document.getElementById('step');
//     const databasesInfo = document.getElementById('available-databases').getAttribute('data-databases');
//     const supportedDatabases = JSON.parse(databasesInfo);
//
//     // Create the step header
//     const h3 = document.createElement('h3');
//     h3.className = 'mb-3';
//     h3.textContent = 'Step 1/4: Database setup';
//     stepDiv.appendChild(h3);
//
//     // Server selection
//     createSelectRow(stepDiv, 'Server:', 'sql_type', 'Please choose your preferred database...', supportedDatabases);
//
//     // DB Host/Socket
//     createInputRow(stepDiv, 'Host/Socket:', 'sql_server', 'text', 'e.g. 127.0.0.1', true);
//
//     // Port
//     createInputRow(stepDiv, 'Port:', 'sql_port', 'number', '', true);
//
//     createUserPassRow(stepDiv, 'User for database creation:', 'root_user', true);
//     createUserPassRow(stepDiv, 'Password for database creation:', 'root_password', true, true);
//
//     createUserPassRow(stepDiv, 'User for system operations:', 'sql_user', true);
//     createUserPassRow(stepDiv, 'Password for system operations:', 'sql_password', true, true);
//
//     createCheckboxRow(stepDiv, 'useSameUser', 'cursor-pointer',  'Use the same user for system operations and database creation');
//     // Database name
//
//     createInputRow(stepDiv, 'Database:', 'sql_db', 'text', '', true, true);
//     // Table prefix
//
//     createInputRow(stepDiv, 'Table prefix:', 'sqltblpre', 'text', 'Optional', false);
//     // Checkbox for using the same user
//
//     // Handle checkbox for using the same user for system and database creation
//     document.getElementById('useSameUser').addEventListener('change', function () {
//         const isChecked = this.checked
//         if (isChecked) {
//             document.getElementById('sql_user').classList.add('d-none');
//             document.getElementById('sql_password').classList.add('d-none');
//
//         }
//         // Logic to duplicate or separate user/password inputs for system usage based on checkbox
//     })
//
//     // Checkbox for custom database creation
//     createCheckboxRow(stepDiv, 'customDbCreation', 'cursor-pointer', 'I want to create my own db or I have an existing db');
//
//     // Show/hide database inputs based on the customDbCreation checkbox
//     document.getElementById('customDbCreation').addEventListener('change', function () {
//         const isChecked = this.checked
//         const dbInputs = document.getElementById('dbdatafull')
//
//         if (isChecked) {
//             dbInputs.classList.remove('d-none');
//         } else {
//             dbInputs.classList.add('d-none');
//         }
//     })
//
//     function createSelectRow (parent, labelText, id, placeholder, options) {
//         const rowDiv = document.createElement('div')
//         rowDiv.className = 'row mb-2'
//
//         const label = document.createElement('label')
//         label.className = 'col-sm-3 col-form-label'
//         label.setAttribute('for', id)
//         label.textContent = labelText
//
//         const colDiv = document.createElement('div')
//         colDiv.className = 'col-sm-9'
//
//         const select = document.createElement('select')
//         select.name = select.id = id
//         select.className = 'form-select'
//         select.required = true
//
//         const defaultOption = document.createElement('option')
//         defaultOption.selected = defaultOption.disabled = true
//         defaultOption.value = ''
//         defaultOption.textContent = placeholder
//         select.appendChild(defaultOption)
//         for (const db in options) {
//             const option = document.createElement('option')
//             option.value = db
//             option.textContent = `${ db.toUpperCase() } - ${ options[db][1] }`
//             select.appendChild(option)
//         }
//
//         colDiv.appendChild(select)
//         rowDiv.appendChild(label)
//         rowDiv.appendChild(colDiv)
//         parent.appendChild(rowDiv)
//
//         // Optional: Add event listener to select for database-specific inputs
//     }
//
//     function createInputRow (parent, labelText, id, type, placeholder, required, hideInitially = false, returnRowDiv = false) {
//         const rowDiv = document.createElement('div')
//         rowDiv.className = 'row mb-2'
//         if (hideInitially) rowDiv.style.display = 'none' // Hide if necessary
//
//         const label = document.createElement('label')
//         label.className = 'col-sm-3 col-form-label'
//         label.setAttribute('for', id)
//         label.textContent = labelText
//
//         const colDiv = document.createElement('div')
//         colDiv.className = 'col-sm-9'
//
//         const input = document.createElement('input')
//         input.type = type
//         input.name = input.id = id
//         input.className = 'form-control'
//         input.placeholder = placeholder
//         if (required) input.required = true
//
//         colDiv.appendChild(input)
//         rowDiv.appendChild(label)
//         rowDiv.appendChild(colDiv)
//         parent.appendChild(rowDiv)
//
//         if (returnRowDiv) {
//             return rowDiv // Return the row div if needed for further manipulation
//         }
//     }
//
//     function createUserPassRow (parent, labelText, id, required, isPassword = false) {
//         const rowDiv = createInputRow(parent, labelText, id, isPassword ? 'password' : 'text', '', required, false, true) // Updated to capture rowDiv and indicate password field
//
//         if (isPassword) {
//             const colDiv = rowDiv.querySelector('.col-sm-9')
//             const input = colDiv.querySelector(`input#${ id }`)
//
//             // Create the eye toggle span
//             const toggleSpan = document.createElement('span')
//             toggleSpan.className = 'show-password input-group-text'
//
//             // Create the eye icon
//             const eyeIcon = document.createElement('i')
//             eyeIcon.className = 'fa fa-eye'
//             toggleSpan.appendChild(eyeIcon)
//
//             // Wrap input in an input group div and append the toggle span
//             const inputGroupDiv = document.createElement('div')
//             inputGroupDiv.className = 'input-group'
//             colDiv.replaceChild(inputGroupDiv, input)
//             inputGroupDiv.appendChild(input)
//             inputGroupDiv.appendChild(toggleSpan)
//
//             // Toggle password visibility
//             toggleSpan.addEventListener('click', function () {
//                 if (input.type === 'password') {
//                     input.type = 'text'
//                     eyeIcon.className = 'fa fa-eye-slash'
//                 } else {
//                     input.type = 'password'
//                     eyeIcon.className = 'fa fa-eye'
//                 }
//             })
//         }
//     }
//
//     function createCheckboxRow (parent, id, cssClass, labelText) {
//         const rowDiv = document.createElement('div')
//         rowDiv.className = 'row mb-2'
//
//         const colDiv = document.createElement('div')
//         colDiv.className = 'col-sm-9 offset-sm-3'
//
//         const checkbox = document.createElement('input')
//         checkbox.type = 'checkbox'
//         checkbox.name = checkbox.id = id
//         checkbox.className = 'form-check-input'
//
//         const label = document.createElement('label')
//         label.className = 'form-check-label'
//         label.setAttribute('for', id)
//         label.textContent = labelText
//
//         colDiv.appendChild(checkbox)
//         colDiv.appendChild(label)
//         rowDiv.appendChild(colDiv)
//         parent.appendChild(rowDiv)
//     }
// })
