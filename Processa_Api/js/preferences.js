  // Inicializa o SDK do Mercado Pago
        const mp = new MercadoPago('APP_USR-9237cffa-5ad4-4056-956b-20d62d1d0dab', {
            locale: 'pt-BR'
        });

        // Configurações compartilhadas
        const bricksBuilder = mp.bricks();
        let paymentId = null;
        let preferenceId = null;

        // Função para renderizar o Payment Brick
        const renderPaymentBrick = async () => {
            const settings = {
                initialization: {
                    amount: document.getElementById('valor_payment')?.value || 100,
                    preferenceId: document.getElementById('preference_id')?.value || null,
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
                        console.log('Payment Brick ready');
                        
                        // Se houver preferenceId, mostra o link de pagamento
                        if (settings.initialization.preferenceId) {
                            fetch(`/get_payment_link?id=${settings.initialization.preferenceId}`)
                                .then(response => response.json())
                                .then(data => {
                                    if (data.init_point) {
                                        const container = document.getElementById('paymentLinkContainer');
                                        const link = document.getElementById('paymentLink');
                                        link.href = data.init_point;
                                        link.textContent = data.init_point;
                                        container.style.display = 'block';
                                    }
                                });
                        }
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
                                if (response.id) {
                                    paymentId = response.id;
                                    document.getElementById('toggleStatusBrick').style.display = 'block';
                                }
                                resolve();
                            })
                            .catch((error) => {
                                console.error('Payment error:', error);
                                reject();
                            });
                        });
                    },
                    onError: (error) => {
                        console.error('Payment Brick error:', error);
                    },
                },
            };
            
            window.paymentBrickController = await bricksBuilder.create(
                "payment",
                "paymentBrick_container",
                settings
            );
        };

        // Função para renderizar o Status Screen Brick
        const renderStatusScreenBrick = async () => {
            if (!paymentId) return;
            
            const settings = {
                initialization: {
                    paymentId: paymentId,
                },
                callbacks: {
                    onReady: () => {
                        console.log('Status Screen Brick ready');
                    },
                    onError: (error) => {
                        console.error('Status Screen Brick error:', error);
                    },
                },
            };
            
            window.statusScreenBrickController = await bricksBuilder.create(
                'statusScreen',
                'statusScreenBrick_container',
                settings
            );
            
            document.getElementById('statusScreenBrick_container').style.display = 'block';
        };

        // Controle para mostrar/ocultar o Status Screen Brick
        document.getElementById('toggleStatusBrick').addEventListener('click', () => {
            const statusContainer = document.getElementById('statusScreenBrick_container');
            if (statusContainer.style.display === 'none') {
                renderStatusScreenBrick();
            } else {
                statusContainer.style.display = 'none';
                if (window.statusScreenBrickController) {
                    window.statusScreenBrickController.unmount();
                }
            }
        });

        // Inicializa o Payment Brick quando o DOM estiver pronto
        document.addEventListener('DOMContentLoaded', () => {
            renderPaymentBrick();
            
            // Oculta o botão de status inicialmente
            document.getElementById('toggleStatusBrick').style.display = 'none';
        });

        // Se houver parâmetros na URL para payment_id ou preference_id
        const urlParams = new URLSearchParams(window.location.search);
        const urlPaymentId = urlParams.get('payment_id');
        const urlPreferenceId = urlParams.get('preference_id');
        
        if (urlPaymentId) {
            paymentId = urlPaymentId;
            document.getElementById('toggleStatusBrick').style.display = 'block';
            renderStatusScreenBrick();
        }
        
        if (urlPreferenceId) {
            preferenceId = urlPreferenceId;
        }