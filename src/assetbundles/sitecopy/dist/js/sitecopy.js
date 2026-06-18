document.addEventListener('change', function(e) {
  if (e.target.name !== 'sitecopy[targets][]') return;

  var sitecopyDiv = e.target.closest('.sitecopy');
  if (!sitecopyDiv) return;

  var elementId = parseInt(sitecopyDiv.dataset.elementId);
  var sourceSiteId = parseInt(sitecopyDiv.dataset.sourceSiteId);
  if (!elementId || !sourceSiteId) return;

  var warningEl = sitecopyDiv.querySelector('#sitecopy-unavailable-warning');
  if (!warningEl) return;

  var selectedTargets = Array.from(
    sitecopyDiv.querySelectorAll('input[type="checkbox"]:checked')
  ).filter(function(el) { return el.name === 'sitecopy[targets][]'; })
   .map(function(el) { return parseInt(el.value); });

  if (selectedTargets.length === 0) {
    warningEl.classList.add('hidden');
    return;
  }

  fetch(Craft.getActionUrl('site-copy-x/api/check-site-availability'), {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': Craft.csrfTokenValue },
    body: JSON.stringify({ elementId: elementId, sourceSiteId: sourceSiteId }),
  })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (!data.success) return;

      var availableIds = data.availableSites.map(function(s) { return s.id; });
      var unavailableSelected = selectedTargets.filter(function(id) { return !availableIds.includes(id); });

      if (unavailableSelected.length > 0) {
        var names = data.unavailableSites
          .filter(function(s) { return unavailableSelected.includes(s.id); })
          .map(function(s) { return s.name; })
          .join(', ');
        warningEl.textContent = Craft.t('site-copy-x', 'Warning: the element does not exist on the following sites and will not be copied: {sites}', { sites: names });
        warningEl.classList.remove('hidden');
      } else {
        warningEl.classList.add('hidden');
      }
    })
    .catch(function(e) { console.error('sitecopy availability check failed', e); });
});

function toggleSitecopyTargets(source) {
  const sitecopyDiv = source.closest('.sitecopy');
  const checkboxes = sitecopyDiv.querySelectorAll('.sitecopy-options input[type="checkbox"]');
  const isChecked = source.checked;

  for (let i = 1, n = checkboxes.length; i < n; i++) {
    checkboxes[i].checked = isChecked;
  }
}

function updateSitecopyToggleAll(source) {
  const sitecopyDiv = source.closest('.sitecopy');
  const toggleAll = sitecopyDiv.querySelector('[id$="sitecopy-toggle-all"]');

  if (toggleAll) {
    const checkboxes = sitecopyDiv.querySelectorAll('.sitecopy-options input[type="checkbox"]');
    toggleAll.checked = Array.from(checkboxes).every((checkbox) => checkbox.checked);
  }
}

