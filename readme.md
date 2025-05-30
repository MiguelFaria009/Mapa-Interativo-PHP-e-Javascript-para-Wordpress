Mapa Interativo de Lotes para WordPress
Este é um plugin WordPress que fornece um shortcode [lotes_mapa_interativo] para criar um mapa interativo de lotes imobiliários. O mapa exibe uma imagem de fundo com ícones arrastáveis que representam lotes, permitindo que administradores ajustem suas posições diretamente no frontend e salvem as alterações via AJAX. Os lotes podem ter três estados: Disponível, Reservado ou Comprado, com informações adicionais exibidas em tooltips.
Funcionalidades

Exibe até 190 lotes de um Custom Post Type (lotes) em um mapa interativo.
Permite arrastar e ajustar a posição dos ícones dos lotes (apenas para usuários logados).
Suporta ajuste fino das posições com teclas de seta (para usuários logados).
Exibe tooltips com informações do lote (Quadra, Lote, Comprador, se aplicável).
Permite que administr WPrds salvem as posições dos lotes via AJAX usando um botão "Publicar Posições".
Estilização responsiva com cores específicas para cada estado do lote.

Requisitos

WordPress 5.0 ou superior.
Um Custom Post Type chamado lotes configurado no WordPress.
Metadados _status_do_lote (ex.: disponivel, reservado, comprado) e _nome_do_comprador (opcional) associados aos lotes.
Uma imagem de mapa (formato BMP, PNG ou JPG) hospedada no servidor WordPress.

Instalação

Faça o download do arquivo lotes-mapa-interativo.php deste repositório.
Coloque o arquivo na pasta /wp-content/plugins/ do seu site WordPress.
Ative o plugin no painel de administração do WordPress (Plugins > Plugins Instalados > Ativar).
Certifique-se de que o Custom Post Type lotes está registrado e possui os metadados necessários.

Configuração

Custom Post Type:

Crie um Custom Post Type chamado lotes (pode usar um plugin como Custom Post Type UI ou código personalizado).
Adicione metadados _status_do_lote e _nome_do_comprador aos posts (pode usar Advanced Custom Fields ou código personalizado).


Imagem do Mapa:

Faça upload da imagem do mapa para a biblioteca de mídia do WordPress.
Atualize a URL da imagem no código do plugin (procure por img src no arquivo lotes-mapa-interativo.php).


Uso do Shortcode:

Adicione o shortcode [lotes_mapa_interativo] em uma página ou post onde deseja exibir o mapa.



Como Usar

Para visitantes: O mapa exibe ícones sobre uma imagem de fundo, com tooltips mostrando informações como Quadra, Lote e, para lotes comprados, o nome do comprador.
Para usuários logados: Os ícones são arrastáveis, permitindo reposicionamento no mapa.
Para administradores:
Um botão "Publicar Posições" aparece no topo do mapa.
Clique em um ícone para selecioná-lo e use as teclas de seta para ajustes finos.
Clique em "Publicar Posições" para salvar as posições dos ícones via AJAX.



Estrutura do Código

Shortcode [lotes_mapa_interativo]:
Usa WP_Query para buscar até 190 lotes.
Gera HTML com uma imagem de fundo e ícones arrastáveis.
Inclui CSS para estilização e JavaScript para interatividade (drag-and-drop, tooltips, ajustes com teclado).


Função AJAX:
Ação save_lotes_positions salva as posições dos ícones no banco de dados (opção lotes_positions).
Inclui verificação de nonce para segurança e restrição a administradores.



Personalização

Imagem do Mapa: Substitua a URL da imagem no código pela sua própria imagem (ex.: wp-content/uploads/seu-mapa.jpg).
Estilização: Edite o CSS inline no arquivo para ajustar cores, tamanhos ou outros estilos.
Lógica de Quadras: A função getQuadraAndLote mapeia slugs de lotes (ex.: lote-1, lote-1-2) para Quadra e Lote. Ajuste os limites de quadras conforme necessário.

Contribuindo

Faça um fork deste repositório.
Crie uma branch para suas alterações (git checkout -b minha-alteracao).
Faça commit das suas alterações (git commit -m "Descrição da alteração").
Envie para o repositório remoto (git push origin minha-alteracao).
Crie um Pull Request.

Licença
Este projeto está licenciado sob a Licença MIT.
Contato
Para suporte ou dúvidas, abra uma issue neste repositório ou entre em contato com o desenvolvedor.
