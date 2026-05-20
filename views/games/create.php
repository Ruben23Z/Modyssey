<?php $pageTitle = 'Adicionar Jogo — Modyssey'; ?>
<?php require __DIR__ . '/../layout/header.php'; ?>

<main>
    <div class="container" style="max-width:640px;">

        <div class="page-header">
            <div>
                <h1>Adicionar Jogo</h1>
                <p class="text-muted">Preenche os dados do jogo.</p>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error mb-24">
                <span class="alert-icon">&#9888;</span>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>


        <div class="card">
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/games/store" enctype="multipart/form-data" novalidate
                      style="display:flex;flex-direction:column;gap:20px;">
                    <input type="hidden" id="rawg_image_url" name="rawg_image_url">
                    <div class="form-group" style="position: relative;">
                        <label for="rawg_search">Pesquisar no RAWG Video Games</label>
                        <div style="display: flex; gap: 8px;">

                            <input
                                    type="text"
                                    id="rawg_search"
                                    name="rawg"
                                    placeholder="Ex: The Elder Scrolls V: Skyrim"
                                    maxlength="150"
                                    style="flex: 1;"
                            >
                            <button type="button" id="btn_rawg_search" class="btn btn-secondary">Pesquisar</button>

                            <div id="rawg_suggestions" class="rawg-suggestions-container" style="display: none"></div>


                            <div id="rawg_preview_container"
                                 style="display: none; align-items: center; gap: 12px; margin-top: 10px; padding: 10px; background: #252836; border: 1px solid #3b3e51; border-radius: 6px;">
                                <img id="rawg_preview" src="" alt="Capa RAWG"
                                     style="width: 60px; height: 80px; object-fit: cover; border-radius: 4px;">
                                <div>
                                    <span class="text-success"
                                          style="font-weight: 600; display: block; font-size: 14px;">Capa importada do RAWG!</span>
                                    <button type="button" id="btn_remove_rawg" class="btn btn-xs btn-danger"
                                            style="margin-top: 4px; padding: 2px 8px; font-size: 12px;">Remover
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                        <div class="form-group">
                            <label for="name">Nome do Jogo *</label>
                            <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                    placeholder="Ex: The Elder Scrolls V: Skyrim"
                                    required
                                    maxlength="150"
                            >
                        </div>

                        <div class="form-group">
                            <label for="image">Imagem do Jogo *</label>
                            <div class="file-input-wrapper">
                                <input type="file" id="image" name="image" accept="image/*" required>
                            </div>
                            <span class="form-hint">JPEG, PNG ou WebP. Máx. 5 MB.</span>
                        </div>

                        <div style="display:flex;gap:10px;">
                            <button type="submit" class="btn btn-primary">Guardar Jogo</button>
                            <a href="<?= BASE_URL ?>/games" class="btn btn-ghost">Cancelar</a>
                        </div>

                </form>
            </div>
        </div>

    </div>
</main>


<style>
    .rawg-suggestions-container {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: #1f2029;
        border: 1px solid #3b3e51;
        border-radius: 8px;
        max-height: 250px;
        overflow-y: auto;
        padding: 8px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        z-index: 999;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
        margin-top: 4px;
    }

    .rawg-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.1s ease;
        color: #f1f2f3;
    }

    .rawg-item:hover {
        background: #3b3e51;
        transform: translateX(4px);
    }

    .rawg-item img {
        width: 40px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
    }

    .rawg-item-name {
        font-weight: 500;
        font-size: 14px;
    }
</style>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('rawg_search');
        const searchButton = document.getElementById('btn_rawg_search');
        const suggestionsContainer = document.getElementById('rawg_suggestions');
        const nameInput = document.getElementById('name');
        const imageUrlInput = document.getElementById('rawg_image_url');
        const fileInput = document.getElementById('image');
        const previewContainer = document.getElementById('rawg_preview_container');
        const previewImage = document.getElementById('rawg_preview');
        const removeRawgBtn = document.getElementById('btn_remove_rawg');
        const apiKey = '2d6e235208924b31a1c0901b8858a96f';
        searchButton.addEventListener('click', () => {
            const query = searchInput.value.trim();
            if (query.length < 2) {
                alert('Por favor, introduza pelo menos 2 caracteres para pesquisar.');
                return;
            }


            searchButton.disabled = true;
            searchButton.textContent = "A pesquisar...";
            suggestionsContainer.innerHTML = '<div style="padding: 10px; color: #a5a6b0;">A carregar resultados...</div>';
            suggestionsContainer.style.display = 'block';


            const url = `https://api.rawg.io/api/games?key=${apiKey}&search=${encodeURIComponent(query)}&page_size=5`;


            fetch(url).then(res => {
                if (!res.ok) throw new Error('Erro ao comunicar com a API do RAWG.');
                return res.json();
            }).then(data => {
                suggestionsContainer.innerHTML = "";
                if (!data.results || data.results.length === 0) {
                    suggestionsContainer.innerHTML = '<div style="padding: 10px; color: #a5a6b0;">Nenhum jogo encontrado.</div>';
                    return;
                }


                data.results.forEach(game => {
                    const item = document.createElement("div");
                    item.className = "rawg-item";

                    const imgUrl = game.background_image || 'https://via.placeholder.com/120x150?text=Sem+Capa';

                    item.innerHTML = `
                        <img src="${imgUrl}" alt="${game.name}">
                        <div class="rawg-item-name">${game.name}</div>
                    `;

                    item.addEventListener("click", () => {
                        nameInput.value = game.name;
                        imageUrlInput.value = game.background_image || '';
                        if (game.background_image) {
                            previewImage.src = game.background_image;
                            previewContainer.style.display = 'flex';

                            // Remove a obrigatoriedade do upload de ficheiro local
                            fileInput.removeAttribute('required');
                            fileInput.disabled = true;
                        } else {
                            previewContainer.style.display = 'none';
                            fileInput.setAttribute('required', '');
                            fileInput.parentElement.style.opacity = '1';
                        }
                        // Esconde a lista de sugestões
                        suggestionsContainer.style.display = 'none';
                    });
                    suggestionsContainer.appendChild(item);


                });
            })
                .catch(err => {
                    suggestionsContainer.innerHTML = `<div style="padding: 10px; color: #ff4d4f;">${err.message}</div>`;
                })
                .finally(() => {
                    searchButton.disabled = false;
                    searchButton.textContent = 'Pesquisar';
                });
        });
        // Fechar sugestões ao clicar fora do contentor
        document.addEventListener('click', (e) => {
            if (!suggestionsContainer.contains(e.target) && e.target !== searchButton && e.target !== searchInput) {
                suggestionsContainer.style.display = 'none';
            }
        });
        // Ação do botão remover
        removeRawgBtn.addEventListener('click', () => {
            imageUrlInput.value = '';
            previewContainer.style.display = 'none';
            fileInput.setAttribute('required', '');
            fileInput.parentElement.style.opacity = '1';
        });
    });
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
