const mp = new MercadoPago('APP_USR-9237cffa-5ad4-4056-956b-20d62d1d0dab');
const bricksBuilder = mp.bricks();

const renderPaymentBrick = async (bricksBuilder) => {
    const settings = {
        initialization: {
            /*
             "amount" é o valor total a ser pago por todos os meios de pagamento
           com exceção da Conta Mercado Pago e Parcelamento sem cartão de crédito, que tem seu valor de processamento determinado no backend através do "preferenceId"
            */
            amount: parseFloat($("#valor_payment").val()),
            preferenceId: $("#preference_id").val(),          
        },
        customization: {
            paymentMethods: {
                ticket: "all",
                bankTransfer: "all",
                creditCard: "all",
                prepaidCard: "all",
                debitCard: "all",
                mercadoPago: "all",
            },
        },
        callbacks: {
            onReady: () => {
                /*
                 Callback chamado quando o Brick estiver pronto.
                 Aqui você pode ocultar loadings do seu site, por exemplo.
                */
            },
            onSubmit: ({ selectedPaymentMethod, formData }) => {
    return new Promise((resolve, reject) => {
        fetch("", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(formData),
        })
        .then((response) => response.json())
        .then((response) => {
            if (!response.id) {
                throw new Error('ID de pagamento não recebido');
            }

            const renderStatusScreenBrick = async (bricksBuilder) => {
                const settings = {
                    initialization: {
                        paymentId: response.id, // ID correto da resposta
                    },
                    callbacks: {
                        onReady: () => {
                            console.log('Status Screen pronto');
                        },
                        onError: (error) => {
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
            renderStatusScreenBrick(bricksBuilder);
            resolve();
        })
        .catch((error) => {
            console.error('Erro:', error);
            reject();
        });
    });
},
            onError: (error) => {
                // callback chamado para todos os casos de erro do Brick
                console.error(error);
            },
        },
    };
    window.paymentBrickController = await bricksBuilder.create(
        "payment",
        "paymentBrick_container",
        settings
    );
};
renderPaymentBrick(bricksBuilder);