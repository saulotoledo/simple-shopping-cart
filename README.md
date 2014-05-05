Simple Shopping Cart
====================

Carrinho de compras de teste em ZF1.

Para o correto funcionamento da aplicação, é necessário instalar as dependências
pelo composer, executando <b>php composer.phar install</b> na raiz da
aplicação. Note que o composer.phar não é distribuído aqui e deve ser
adquirido em https://getcomposer.org/

O script de banco de dados está em /setup_files. Dados de teste e instruções de
instalação, vide /setup_files/test_data/README.md.

Suporte a envio de e-mail com SMTP pode ser ativado configurando o valor "1"
para mail.smtp.enabled em /application/configs/application.ini e preenchendo
corretamente as outras configurações de SMTP do servidor desejado. Neste mesmo
arquivo a base de dados deve ser configurada.
