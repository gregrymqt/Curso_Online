document.addEventListener('DOMContentLoaded', function() {
  const mp = new MercadoPago("APP_USR-9237cffa-5ad4-4056-956b-20d62d1d0dab");

  // Verifica se o formulário existe antes de executar
  const form = document.getElementById('form-checkout');
  if (!form) return;

  (async function getIdentificationTypes() {
    try {
      const identificationTypes = await mp.getIdentificationTypes();
      const identificationTypeElement = document.getElementById('form-checkout__identificationType');
      
      if (!identificationTypeElement) {
        console.error('Elemento não encontrado! Verifique se o formulário está visível.');
        return;
      }
      
    createSelectOptions(identificationTypeElement, identificationTypes);
  } catch (e) {
    return console.error('Error getting identificationTypes: ', e);
  }
})();
});

function createSelectOptions(elem, options, labelsAndKeys = { label: "name", value: "id" }) {
  const { label, value } = labelsAndKeys;
  elem.options.length = 0;
  const tempOptions = document.createDocumentFragment();
  options.forEach(option => {
    const optValue = option[value];
    const optLabel = option[label];
    const opt = document.createElement('option');
    opt.value = optValue;
    opt.textContent = optLabel;
    tempOptions.appendChild(opt);
  });
  elem.appendChild(tempOptions);
}