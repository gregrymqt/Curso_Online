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

  // Função para renderizar o Status Screen Brick
  const renderStatusScreenBrick = async (bricksBuilder) => {
    const settings = {
      initialization: {
        paymentId: 'id', // id do pagamento a ser mostrado
      },
      callbacks: {
        onReady: () => {
          /*
            Callback chamado quando o Brick estiver pronto.
            Aqui você pode ocultar loadings do seu site, por exemplo.
          */
        },
        onError: (error) => {
          // callback chamado para todos os casos de erro do Brick
          console.error(error);
        },
      },
    };
    window.statusScreenBrickController = await bricksBuilder.create(
      'statusScreen',
      'statusScreenBrick_container',
      settings,
    );  
  };
    // Verifica se bricksBuilder está disponível antes de chamar
  if (typeof bricksBuilder !== 'undefined') {
    renderStatusScreenBrick(bricksBuilder);
  } else {
    console.error('bricksBuilder não está definido');
  }
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