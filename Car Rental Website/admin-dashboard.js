// admin-dashboard.js
// Only enable AJAX admin flows when the admin UI is explicitly pointing to api/cars.php.
// If your dashboard forms post to process_car.php (server-side), this script will not intercept them.

(function () {
  'use strict';

  function formToFormData(form) {
    return new FormData(form);
  }

  function flash(msg, isError) {
    if (isError) alert('Error: ' + msg);
    else alert(msg);
  }

  // If the Add form action points to api/cars.php then enable AJAX, otherwise do nothing.
  const addForm = document.getElementById('add-car-form');
  if (addForm && addForm.getAttribute('action') && addForm.getAttribute('action').indexOf('api/cars.php') !== -1) {
    addForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const fd = formToFormData(addForm);
      fd.set('action', 'add');
      fetch('api/cars.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(r => r.json())
        .then(json => {
          if (!json.success) return flash(json.message || 'Failed', true);
          flash(json.message || 'Added');
          window.location.reload();
        })
        .catch(err => flash(err.message || 'Network error', true));
    });
  }

  // Edit forms only if their action targets api/cars.php
  document.addEventListener('submit', function (e) {
    const form = e.target;
    if (!form.classList.contains('edit-car-form')) return;
    if (!(form.getAttribute('action') && form.getAttribute('action').indexOf('api/cars.php') !== -1)) return; // do not intercept if server-side handler used
    e.preventDefault();
    const fd = formToFormData(form);
    fd.set('action', 'update');
    fetch('api/cars.php', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(r => r.json())
      .then(json => {
        if (!json.success) return flash(json.message || 'Failed', true);
        flash(json.message || 'Updated');
        window.location.reload();
      })
      .catch(err => flash(err.message || 'Network error', true));
  });

  // Delete: only intercept if delete button has data-api="true" attribute (otherwise use process_car.php GET link)
  document.addEventListener('click', function (e) {
    const el = e.target.closest('.delete-car-btn');
    if (!el) return;
    if (el.getAttribute('data-api') !== 'true') return; // do not intercept server-side delete links
    e.preventDefault();
    if (!confirm('Delete this car?')) return;
    const id = el.getAttribute('data-id');
    const csrf = document.querySelector('input[name="csrf_token"]') ? document.querySelector('input[name="csrf_token"]').value : '';
    const fd = new FormData();
    fd.set('action', 'delete');
    fd.set('id', id);
    fd.set('csrf_token', csrf);
    fetch('api/cars.php', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(r => r.json())
      .then(json => {
        if (!json.success) return flash(json.message || 'Failed', true);
        flash(json.message || 'Deleted');
        window.location.reload();
      })
      .catch(err => flash(err.message || 'Network error', true));
  });

})();