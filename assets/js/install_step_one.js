document.addEventListener('DOMContentLoaded', function () {
    const stepDiv = document.getElementById('step') // Assuming 'step' is the ID of your target div
    const databasesInfo = document.getElementById('available-databases').getAttribute('data-databases');
    const supportedDatabases = JSON.parse(databasesInfo);

    // Create the step header
    const h3 = document.createElement('h3')
    h3.className = 'mb-3'
    h3.textContent = 'Step 1/4: Database setup'
    stepDiv.appendChild(h3)

    // Server selection
    createSelectRow(stepDiv, 'Server:', 'sql_type', 'Please choose your preferred database ...', supportedDatabases)

    // DB Host/Socket
    createInputRow(stepDiv, 'Host/Socket:', 'sql_server', 'text', 'e.g. 127.0.0.1', true)

    // Port
    createInputRow(stepDiv, 'Port:', 'sql_port', 'number', '', true)

    // User for database
    createUserPassRow(stepDiv, 'User:', 'sql_user', true)

    // Password for database
    createUserPassRow(stepDiv, 'Password:', 'sql_password', true, true)

    // Database name
    createInputRow(stepDiv, 'Database:', 'sql_db', 'text', '', true, true)

    // Table prefix
    createInputRow(stepDiv, 'Table prefix:', 'sqltblpre', 'text', 'Optional', false)

    // Checkbox for using the same user
    createCheckboxRow(stepDiv, 'useSameUser', 'Use the same user for system and creation')

    // Handle checkbox for using the same user for system and database creation
    document.getElementById('useSameUser').addEventListener('change', function () {
        const isChecked = this.checked
        // Logic to duplicate or separate user/password inputs for system usage based on checkbox
    })

    // Checkbox for custom database creation
    createCheckboxRow(stepDiv, 'customDbCreation', 'I want to create my own db or I have an existing db')

    // Show/hide database inputs based on the customDbCreation checkbox
    document.getElementById('customDbCreation').addEventListener('change', function () {
        const isChecked = this.checked
        const dbInputs = document.getElementById('dbdatafull')
        if (isChecked) {
            dbInputs.style.display = 'block'
        } else {
            dbInputs.style.display = 'none'
        }
    })

    function createSelectRow (parent, labelText, id, placeholder, options) {
        const rowDiv = document.createElement('div')
        rowDiv.className = 'row mb-2'

        const label = document.createElement('label')
        label.className = 'col-sm-3 col-form-label'
        label.setAttribute('for', id)
        label.textContent = labelText

        const colDiv = document.createElement('div')
        colDiv.className = 'col-sm-9'

        const select = document.createElement('select')
        select.name = select.id = id
        select.className = 'form-select'
        select.required = true

        const defaultOption = document.createElement('option')
        defaultOption.selected = defaultOption.disabled = true
        defaultOption.value = ''
        defaultOption.textContent = placeholder
        select.appendChild(defaultOption)
        for (const db in options) {
            const option = document.createElement('option')
            option.value = db
            option.textContent = `${ db.toUpperCase() } - ${ options[db][1] }`
            select.appendChild(option)
        }

        colDiv.appendChild(select)
        rowDiv.appendChild(label)
        rowDiv.appendChild(colDiv)
        parent.appendChild(rowDiv)

        // Optional: Add event listener to select for database-specific inputs
    }

    function createInputRow (parent, labelText, id, type, placeholder, required, hideInitially = false, returnRowDiv = false) {
        const rowDiv = document.createElement('div')
        rowDiv.className = 'row mb-2'
        if (hideInitially) rowDiv.style.display = 'none' // Hide if necessary

        const label = document.createElement('label')
        label.className = 'col-sm-3 col-form-label'
        label.setAttribute('for', id)
        label.textContent = labelText

        const colDiv = document.createElement('div')
        colDiv.className = 'col-sm-9'

        const input = document.createElement('input')
        input.type = type
        input.name = input.id = id
        input.className = 'form-control'
        input.placeholder = placeholder
        if (required) input.required = true

        colDiv.appendChild(input)
        rowDiv.appendChild(label)
        rowDiv.appendChild(colDiv)
        parent.appendChild(rowDiv)

        if (returnRowDiv) {
            return rowDiv // Return the row div if needed for further manipulation
        }
    }

    function createUserPassRow(parent, labelText, id, required, isPassword = false) {
        const rowDiv = createInputRow(parent, labelText, id, isPassword ? 'password' : 'text', '', required, false, true); // Capturing rowDiv

        if (isPassword) {
            const colDiv = rowDiv.querySelector('.col-sm-9');
            const input = colDiv.querySelector(`input#${id}`);

            // Create the eye toggle span
            const toggleSpan = document.createElement('span');
            toggleSpan.className = 'input-group-text font-awesome-icon';

            // Start with eye icon (assuming eye icon's Unicode is &#xf06e;)
            toggleSpan.innerHTML = '&#xf06e;'; // Use the correct Unicode value

            // Wrap input in an input group div and append the toggle span
            const inputGroupDiv = document.createElement('div');
            inputGroupDiv.className = 'input-group';
            colDiv.replaceChild(inputGroupDiv, input);
            inputGroupDiv.appendChild(input);
            inputGroupDiv.appendChild(toggleSpan);

            // Event listener to toggle password visibility
            toggleSpan.addEventListener('click', function() {
                if (input.type === 'password') {
                    input.type = 'text';
                    toggleSpan.innerHTML = '&#xf070;'; // Use the correct Unicode value for eye-slash
                } else {
                    input.type = 'password';
                    toggleSpan.innerHTML = '&#xf06e;'; // Use the correct Unicode value for eye
                }
            });
        }
    }

    function createSQLiteInputRow(parent) {
        // Assuming you've captured the default path in a hidden input with ID 'sqlite_default_path'
        const defaultPath = document.getElementById('sqlite_default_path').value;

        createInputRow(parent, 'SQLite database file:', 'sql_sqlitefile', 'text', defaultPath, true);
    }



    function createCheckboxRow (parent, id, labelText) {
        const rowDiv = document.createElement('div')
        rowDiv.className = 'row mb-2'

        const colDiv = document.createElement('div')
        colDiv.className = 'col-sm-9 offset-sm-3'

        const checkbox = document.createElement('input')
        checkbox.type = 'checkbox'
        checkbox.name = checkbox.id = id
        checkbox.className = 'form-check-input'

        const label = document.createElement('label')
        label.className = 'form-check-label'
        label.setAttribute('for', id)
        label.textContent = labelText

        colDiv.appendChild(checkbox)
        colDiv.appendChild(label)
        rowDiv.appendChild(colDiv)
        parent.appendChild(rowDiv)
    }
})
