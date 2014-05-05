Dados de teste
==============

### Informações gerais

As imagens contidas neste diretório e eu seus subdiretórios foram adquiridas em
http://www.mobly.com.br/ e são de sua propriedade, não compartilhando a mesma
licença de distribuição deste projeto. Seu uso aqui é meramente ilustrativo e
não tem nenhuma relação com as informações originais distribuídas pela empresa.

A maior parte dos textos da base dados de teste é baseada no famoso Lorem Ipsum,
um texto aleatório utilizado na indústria tipográfica e de impressão. Mais
informações sobre o assunto em http://lipsum.com/.

Todos os dados da base de dados de teste são meramente fictícios e não
representam a realidade.


### Instalação

Siga os passos a seguir na ordem indicada:

* Crie uma base de dados vazia e configure seu acesso em /application/configs/application.ini
* Crie as tabelas importando o arquivo /setup_files/database_tables.sql;
* Importe o arquivo /setup_files/test_data/db_entries.sql na mesma base de dados;
* Copie a pasta "products" em /setup_files/test_data/ dentro de /files


### Outras informações

A senha do usuário é um hash MD5. Apesar de não ser a maneira mais indicada para
hash de senhas, é assim utilizada apenas para simplificar a inserção de
registros diretamente no MySQL usando "MD5('senha')" nas queries. A senha
de testes de todos os usuários é <b>"senha"</b>. Os usuários de teste são
<b>"usuario1"</b>, <b>"usuario2"</b>, <b>"usuario3"</b> e <b>"usuario4"</b>.
O <b>"usuario3"</b> está inicialmente inativo.

Se o endereço de e-mail do usuário no banco de dados for válido, ele receberá um
e-mail com o resumo de seu pedido em sua caixa de entrada após sua realização.

Os produtos podem ser cadastrados em qualquer nível de hierarquia de categorias
e podem pertencer a várias categorias ao mesmo tempo. Uma categoria pai também
herda todos os produtos das categorias filhas. Um bom exemplo é o produto de ID
72 na base de dados de teste.


