<?php
/**
 * Plugin Name: Mapa Interativo de Lotes
 * Plugin URI: https://github.com/MiguelFaria009/Mapa-Interativo-PHP-e-Javascript-para-Wordpress/
 * Description: Um plugin WordPress que cria um mapa interativo para gerenciar lotes imobiliários com o shortcode [lotes_mapa_interativo].
 * Version: 1.0.0
 * Author: Miguel Faria
 * Author URI: https://comerciodosite.com.br/
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: lotes-mapa-interativo
 */

// Evitar acesso direto ao arquivo
if (!defined('ABSPATH')) {
    exit;
}

// Definir constantes configuráveis
define('LOTES_MAPA_IMAGE_URL', '/wp-content/uploads/seu-mapa.jpg'); // Substitua pela URL da sua imagem
define('LOTES_MAX_POSTS', 190); // Número máximo de lotes a serem exibidos

/**
 * Registra o shortcode [lotes_mapa_interativo]
 *
 * @return string HTML do mapa interativo
 */
function lotes_mapa_interativo_shortcode() {
    // Query para buscar os lotes
    $args = array(
        'post_type' => 'lotes',
        'posts_per_page' => LOTES_MAX_POSTS,
        'orderby' => 'menu_order',
        'order' => 'ASC',
    );
    $lotes_query = new WP_Query($args);

    if (!$lotes_query->have_posts()) {
        return '<p>Nenhum lote encontrado.</p>';
    }

    // Verificar permissões do usuário
    $is_logged_in = is_user_logged_in();
    $is_admin = current_user_can('manage_options');

    // Carregar posições salvas
    $saved_positions = get_option('lotes_positions', []);
    if (!is_array($saved_positions)) {
        $saved_positions = [];
    }

    ob_start();
    ?>

    <div class="lotes-mapa-container" style="position: relative; width: 100%; max-width: 1200px; margin: 0 auto;">
        <!-- Imagem do mapa como fundo -->
        <img src="<?php echo esc_url(LOTES_MAPA_IMAGE_URL); ?>" alt="Mapa de Lotes" style="width: 100%; height: auto; display: block;" id="mapa-lotes">

        <!-- Container para os ícones dos lotes -->
        <div id="lotes-icons" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></div>

        <?php if ($is_admin) : ?>
            <!-- Botão para salvar posições (visível apenas para admins) -->
            <button id="publish-positions" style="position: absolute; top: 10px; left: 10px; z-index: 20; padding: 5px 10px; cursor: pointer;">Publicar Posições</button>
        <?php endif; ?>
    </div>

    <style>
        .lotes-mapa-container {
            position: relative;
        }
        .lote-icon {
            position: absolute;
            width: 1.8%;
            height: 2.5%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: <?php echo $is_logged_in ? 'move' : 'default'; ?>;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 5px;
            color: #ffffff;
            font-size: 0.5vw;
            font-weight: bold;
            text-align: center;
            line-height: 1;
            padding: 2px;
            box-sizing: border-box;
            user-select: none;
            z-index: 10;
        }
        .lote-icon.selected {
            outline: 2px solid #00f; /* Highlight para o ícone selecionado */
        }
        .lote-icon.comprado {
            background-color: #2ecc71 !important;
        }
        .lote-icon.reservado {
            background-color: #f39c12 !important;
        }
        .lote-icon.disponivel {
            background-color: #e74c3c !important;
        }
        .lote-icon:hover {
            transform: scale(1.2);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }
        .lote-tooltip {
            position: absolute;
            background-color: #333;
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 15;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
        }
        .lote-icon:hover .lote-tooltip {
            opacity: 1;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mapa = document.getElementById('mapa-lotes');
            const container = document.getElementById('lotes-icons');
            const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
            const isAdmin = <?php echo $is_admin ? 'true' : 'false'; ?>;
            const savedPositions = <?php echo wp_json_encode($saved_positions); ?>;

            // Dados dos lotes
            const lotesData = [
                <?php
                while ($lotes_query->have_posts()) {
                    $lotes_query->the_post();
                    $slug = get_post_field('post_name', get_the_ID());
                    $status = get_post_meta(get_the_ID(), '_status_do_lote', true);
                    $comprador = get_post_meta(get_the_ID(), '_nome_do_comprador', true);
                    $status = !empty($status) ? strtolower(trim($status)) : 'disponivel';
                    $comprador = !empty($comprador) ? esc_js($comprador) : '';
                    echo "{ id: '" . esc_js($slug) . "', status: '" . esc_js($status) . "', comprador: '" . $comprador . "' },";
                }
                wp_reset_postdata();
                ?>
            ];

            // Variável para rastrear o ícone selecionado
            let selectedIcon = null;

            /**
             * Mapeia o slug do lote para Quadra e Lote
             * @param {string} slug - Slug do lote (ex.: lote-1, lote-1-2)
             * @returns {Object} - Objeto com quadra e loteNumber
             */
            function getQuadraAndLote(slug) {
                const parts = slug.split('-');
                let quadra, loteNumber;

                if (parts.length === 2) {
                    quadra = 1;
                    loteNumber = parseInt(parts[1]);
                } else if (parts.length === 3) {
                    loteNumber = parseInt(parts[1]);
                    quadra = parseInt(parts[2]);
                } else {
                    return { quadra: 0, loteNumber: 0 };
                }

                const quadraLimits = {
                    1: 16, 2: 12, 3: 14, 4: 15, 5: 16, 6: 16, 7: 16, 8: 16,
                    9: 16, 10: 8, 11: 10, 12: 16, 13: 19
                };

                if (loteNumber < 1 || !quadraLimits[quadra] || loteNumber > quadraLimits[quadra]) {
                    return { quadra: 0, loteNumber: 0 };
                }

                return { quadra, loteNumber };
            }

            /**
             * Cria um ícone para o lote
             * @param {Object} lote - Dados do lote (id, status, comprador)
             * @param {number} initialX - Posição inicial X (%)
             * @param {number} initialY - Posição inicial Y (%)
             */
            function createLoteIcon(lote, initialX = 0, initialY = 0) {
                const icon = document.createElement('div');
                icon.className = `lote-icon ${lote.status}`;
                icon.setAttribute('data-id', lote.id);

                const saved = savedPositions.find(pos => pos.id === lote.id);
                icon.style.left = saved ? `${saved.x}%` : `${initialX}%`;
                icon.style.top = saved ? `${saved.y}%` : `${initialY}%`;

                if (isLoggedIn) {
                    icon.draggable = true;
                }

                // Definir texto com base no status
                if (lote.status === 'disponivel') {
                    icon.textContent = 'Disponível';
                } else if (lote.status === 'reservado') {
                    icon.textContent = 'RESERVADO';
                    icon.style.backgroundColor = '#f39c12';
                } else if (lote.status === 'comprado') {
                    icon.textContent = 'COMPRADO';
                    icon.style.backgroundColor = '#2ecc71';
                } else {
                    icon.textContent = 'ERRO';
                    icon.style.backgroundColor = '#ff0000';
                }

                // Adicionar tooltip
                const tooltip = document.createElement('div');
                tooltip.className = 'lote-tooltip';
                const { quadra, loteNumber } = getQuadraAndLote(lote.id);
                let tooltipText = quadra === 0 ? 'Erro no Lote' : `Quadra ${quadra} Lote ${loteNumber}`;
                if (lote.status === 'comprado' && lote.comprador) {
                    tooltipText += `\nComprador: ${lote.comprador}`;
                }
                tooltip.textContent = tooltipText;
                icon.appendChild(tooltip);

                // Eventos de drag-and-drop
                if (isLoggedIn) {
                    icon.addEventListener('dragstart', function(e) {
                        e.dataTransfer.setData('text/plain', lote.id);
                    });

                    icon.addEventListener('dragend', function(e) {
                        const rect = container.getBoundingClientRect();
                        let x = ((e.clientX - rect.left) / rect.width) * 100;
                        let y = ((e.clientY - rect.top) / rect.height) * 100;
                        x = Math.max(0, Math.min(x, 100 - 1.8));
                        y = Math.max(0, Math.min(y, 100 - 2.5));
                        icon.style.left = `${x}%`;
                        icon.style.top = `${y}%`;
                    });

                    icon.addEventListener('click', function(e) {
                        if (selectedIcon) {
                            selectedIcon.classList.remove('selected');
                        }
                        selectedIcon = icon;
                        selectedIcon.classList.add('selected');
                        e.stopPropagation();
                    });
                }

                container.appendChild(icon);
            }

            // Inicializar ícones com posições iniciais
            const cols = 20;
            lotesData.forEach((lote, index) => {
                const saved = savedPositions.find(pos => pos.id === lote.id);
                if (saved) {
                    createLoteIcon(lote, saved.x, saved.y);
                } else {
                    const row = Math.floor(index / cols);
                    const col = index % cols;
                    const initialX = 2 + (col * 4.5);
                    const initialY = 2 + (row * 4.5);
                    createLoteIcon(lote, initialX, initialY);
                }
            });

            // Configurar drag-and-drop no container
            if (isLoggedIn) {
                container.addEventListener('dragover', function(e) {
                    e.preventDefault();
                });

                container.addEventListener('drop', function(e) {
                    e.preventDefault();
                });

                // Ajuste fino com teclado
                document.addEventListener('keydown', function(e) {
                    if (!selectedIcon) return;
                    let x = parseFloat(selectedIcon.style.left);
                    let y = parseFloat(selectedIcon.style.top);
                    const step = 0.5;

                    switch (e.key) {
                        case 'ArrowUp':
                            y = Math.max(0, y - step);
                            break;
                        case 'ArrowDown':
                            y = Math.min(100 - 2.5, y + step);
                            break;
                        case 'ArrowLeft':
                            x = Math.max(0, x - step);
                            break;
                        case 'ArrowRight':
                            x = Math.min(100 - 1.8, x + step);
                            break;
                        default:
                            return;
                    }

                    selectedIcon.style.left = `${x}%`;
                    selectedIcon.style.top = `${y}%`;
                });

                // Desselecionar ícone ao clicar fora
                document.addEventListener('click', function(e) {
                    if (selectedIcon && !selectedIcon.contains(e.target)) {
                        selectedIcon.classList.remove('selected');
                        selectedIcon = null;
                    }
                });
            }

            // Configurar botão de publicar (apenas para admins)
            if (isAdmin) {
                const publishButton = document.getElementById('publish-positions');
                if (publishButton) {
                    publishButton.addEventListener('click', function() {
                        const positions = [];
                        const icons = container.querySelectorAll('.lote-icon');
                        icons.forEach(icon => {
                            const id = icon.getAttribute('data-id');
                            const x = parseFloat(icon.style.left);
                            const y = parseFloat(icon.style.top);
                            if (id && !isNaN(x) && !isNaN(y)) {
                                positions.push({ id, x, y });
                            }
                        });

                        // Enviar posições via AJAX
                        fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: new URLSearchParams({
                                'action': 'save_lotes_positions',
                                'positions': JSON.stringify(positions),
                                'nonce': '<?php echo esc_js(wp_create_nonce('save_lotes_positions_nonce')); ?>'
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Posições salvas com sucesso!');
                            } else {
                                alert('Erro ao salvar posições: ' + (data.data || 'Erro desconhecido'));
                            }
                        })
                        .catch(error => {
                            console.error('Erro:', error);
                            alert('Erro ao salvar posições.');
                        });
                    });
                }
            }
        });
    </script>

    <?php
    return ob_get_clean();
}
add_shortcode('lotes_mapa_interativo', 'lotes_mapa_interativo_shortcode');

/**
 * Função AJAX para salvar as posições dos lotes
 */
function save_lotes_positions_callback() {
    // Verificar nonce
    if (!check_ajax_referer('save_lotes_positions_nonce', 'nonce', false)) {
        wp_send_json_error('Nonce inválido.');
        wp_die();
    }

    // Verificar permissões
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permissão insuficiente.');
        wp_die();
    }

    // Validar e salvar posições
    if (isset($_POST['positions']) && !empty($_POST['positions'])) {
        $positions = json_decode(stripslashes($_POST['positions']), true);
        if (is_array($positions) && !empty($positions)) {
            // Sanitizar posições
            $sanitized_positions = array_map(function($pos) {
                return [
                    'id' => sanitize_text_field($pos['id']),
                    'x' => floatval($pos['x']),
                    'y' => floatval($pos['y'])
                ];
            }, $positions);
            update_option('lotes_positions', $sanitized_positions);
            wp_send_json_success('Posições salvas.');
        } else {
            wp_send_json_error('Dados inválidos.');
        }
    } else {
        wp_send_json_error('Nenhuma posição enviada.');
    }

    wp_die();
}
add_action('wp_ajax_save_lotes_positions', 'save_lotes_positions_callback');
?>
