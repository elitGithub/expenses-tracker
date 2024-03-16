document.addEventListener('DOMContentLoaded', () => {
    const useMyOwnUserSystem = document.getElementById('useMyOwnUserSystem');
    const createMyOwnUserManagementControl =  Array.from(document.getElementsByClassName('create-my-own-user-control'));

    useMyOwnUserSystem.addEventListener('change', function () {
        const isChecked = this.checked;
        if (isChecked) {
            createMyOwnUserManagementControl.map(el => el.classList.remove('d-none'));
        } else {
            createMyOwnUserManagementControl.map(el => el.classList.add('d-none'));
        }
    });
});
