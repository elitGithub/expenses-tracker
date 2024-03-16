document.addEventListener('DOMContentLoaded', () => {
    const useMyOwnUserSystem = document.getElementById('useMyOwnUserSystem');
    const createMyOwnUserManagementControl =  Array.from(document.getElementsByClassName('create-my-own-user-control'));
    const useManagementSelect = document.getElementById('user_management');

    useMyOwnUserSystem.addEventListener('change', function () {
        const isChecked = this.checked;
        if (isChecked) {
            createMyOwnUserManagementControl.map(el => el.classList.add('d-none'));
        } else {
            createMyOwnUserManagementControl.map(el => el.classList.remove('d-none'));
        }
    });

    useManagementSelect.addEventListener('change', (event) => {
        console.log(event.target.value);
    });
});
