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
