  const mp = new MercadoPago("APP_USR-9237cffa-5ad4-4056-956b-20d62d1d0dab");

    (async function getIdentificationTypes() {
      try {
        const identificationTypes = await mp.getIdentificationTypes();
        const identificationTypeElement = document.getElementById('form-checkout__identificationType');

        createSelectOptions(identificationTypeElement, identificationTypes);
      } catch (e) {
        return console.error('Error getting identificationTypes: ', e);
      }
    })();
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

    document.getElementById('form-checkout').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitButton = document.getElementById('submit-button');
        submitButton.disabled = true;
        submitButton.textContent = 'Processando...';
        
        try {
            const formData = {
                payerFirstName: document.getElementById('form-checkout__payerFirstName').value,
                payerLastName: document.getElementById('form-checkout__payerLastName').value,
                email: document.getElementById('form-checkout__email').value,
                identificationType: document.getElementById('form-checkout__identificationType').value,
                identificationNumber: document.getElementById('form-checkout__identificationNumber').value,
                transactionAmount: document.getElementById('transactionAmount').value,
                description: document.getElementById('description').value
            };

            const response = await fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }

            // Esconde o formulário e mostra o resultado
            document.getElementById('form-checkout').classList.add('hidden');
            document.getElementById('pix-result').classList.remove('hidden');
            
            // Preenche os dados do PIX
            document.getElementById('pix-link').href = data.ticket_url;
            document.getElementById('qr-code-img').src = `data:image/jpeg;base64,${data.qr_code_base64}`;
            document.getElementById('pix-code').value = data.qr_code;
            
        } catch (error) {
            alert('Erro: ' + error.message);
            console.error(error);
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Pagar';
        }
    });

    function copyPixCode() {
        const input = document.getElementById('pix-code');
        input.select();
        Document.execCommand('copy');
        alert('Código PIX copiado!');
    }
