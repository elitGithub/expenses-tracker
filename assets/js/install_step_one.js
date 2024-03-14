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
