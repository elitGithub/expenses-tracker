document.addEventListener('DOMContentLoaded', () => {
  const formHeader = document.getElementsByClassName('form-header');
  const steps = document.getElementsByClassName('stepIndicator');
  console.log(steps);
  Array.from(formHeader).map(el => {
    el.addEventListener('click', (e) => {});
  });

  Array.from(steps).map(el => {
    el.addEventListener('click', (e) => {
      Array.from(steps).map(step => step.classList.remove('active'));
      e.target.classList.add('active');
    });
  });
});