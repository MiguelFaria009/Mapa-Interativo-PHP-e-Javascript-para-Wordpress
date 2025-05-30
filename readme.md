
# Plugin Mapa Interativo de Lotes para WordPress

Este é um plugin WordPress que permite criar um **Mapa Interativo de Lotes Imobiliários** usando um shortcode simples. Ideal para construtoras, loteadoras e imobiliárias que desejam exibir de forma visual a disponibilidade dos lotes em um empreendimento.

## 🔥 Funcionalidades

- 📍 **Mapa Interativo:** Exibe até 190 lotes sobre uma imagem de mapa.
- 🔄 **Posicionamento Dinâmico:** Ícones dos lotes podem ser arrastados no frontend (apenas para usuários logados).
- 🎯 **Ajuste Fino:** Permite mover os ícones utilizando as teclas de seta (usuários logados).
- 🏷️ **Tooltips Informativos:** Mostra informações como:
  - Quadra
  - Lote
  - Status (Disponível, Reservado ou Comprado)
  - Nome do comprador (se aplicável)
- 💾 **Salvar Posições via AJAX:** Administradores podem salvar a posição dos ícones diretamente no frontend.
- 🖌️ **Estilo Responsivo:** Cores específicas para cada status dos lotes.

## ✅ Requisitos

- WordPress 5.0 ou superior.
- Custom Post Type (CPT) chamado **lotes**.
- Metadados:
  - `_status_do_lote` (valores: `disponivel`, `reservado`, `comprado`).
  - `_nome_do_comprador` (opcional).

## 🛠️ Instalação

1. Faça o download do arquivo `lotes-mapa-interativo.php`.
2. Envie para a pasta `/wp-content/plugins/` do seu site WordPress.
3. Ative no painel WordPress em **Plugins > Plugins Instalados**.

## ⚙️ Configuração

### 1️⃣ Custom Post Type

- Crie um CPT chamado **lotes** (use o plugin "Custom Post Type UI" ou código próprio).
- Adicione os metadados `_status_do_lote` e `_nome_do_comprador` (via plugin "Advanced Custom Fields" ou código).

### 2️⃣ Imagem do Mapa

- Faça upload da imagem do mapa (formato `.bmp`, `.png` ou `.jpg`) na Biblioteca de Mídia.
- Atualize a URL da imagem no código do plugin procurando por:

```html
<img src="URL-DA-SUA-IMAGEM" ... />
```

### 3️⃣ Uso do Shortcode

- Utilize o shortcode:

```plaintext
[lotes_mapa_interativo]
```

Em qualquer página ou post onde deseja exibir o mapa.

## 🚀 Como Usar

### Para Visitantes

- Visualizam o mapa com ícones e tooltips contendo informações dos lotes.

### Para Usuários Logados

- Podem arrastar os ícones para reposicionamento no mapa.
- Podem usar as teclas de seta para ajustes precisos.

### Para Administradores

- Visualizam o botão **"Publicar Posições"**.
- Após ajustes no mapa, clique no botão para salvar as posições via AJAX.

## 🗂️ Estrutura do Código

- **Shortcode `[lotes_mapa_interativo]`:**
  - Usa `WP_Query` para buscar até 190 lotes.
  - Renderiza HTML com imagem de fundo e ícones posicionáveis.
  - Inclui CSS inline e JavaScript para:
    - Drag & Drop
    - Tooltips
    - Ajuste via teclado

- **Função AJAX:**
  - Ação `save_lotes_positions` salva as posições no banco (via `update_option('lotes_positions')`).
  - Verificação de nonce e restrição a administradores para segurança.

## 🎨 Personalização

- **Imagem do mapa:** Atualize a URL da imagem no HTML do plugin.
- **Estilização:** Edite os estilos no CSS inline do arquivo.
- **Lógica de quadras:** A função `getQuadraAndLote()` converte slugs (ex.: `lote-1`, `lote-1-2`) para quadra e lote. Altere os limites de quadras conforme seu empreendimento.

## 🤝 Contribuindo

1. Faça um fork deste repositório.
2. Crie uma branch (`git checkout -b minha-alteracao`).
3. Commit (`git commit -m "Descrição da alteração"`).
4. Push (`git push origin minha-alteracao`).
5. Abra um Pull Request.

## 📜 Licença

Este projeto está licenciado sob a [Licença MIT](https://opensource.org/licenses/MIT).

## 📞 Contato

Para suporte ou dúvidas, abra uma *issue* neste repositório ou entre em contato com o desenvolvedor (Miguel Faria).
